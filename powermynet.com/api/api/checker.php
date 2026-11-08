<?php
// api_purchase.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database/dbconfig.php';   // Agents DB
include '../../waec_dbconfig.php';       // WAEC DB
require_once '../../sms_config.php';     // SMS config ($api_url, $api_key, $sender_id)

header('Content-Type: application/json; charset=utf-8');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 405, 'message' => 'Method Not Allowed']);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Invalid JSON']);
    exit;
}

// Required fields
$agent_api    = trim($data['agent_api'] ?? '');
$telephone    = trim($data['telephone'] ?? '');
$customer     = trim($data['customer'] ?? '');
$qty          = max(1, intval($data['qty'] ?? 1));
$reference_id = trim($data['reference_id'] ?? '');
$target       = trim($data['target'] ?? 'WASSCE');

if ($agent_api === '' || $telephone === '' || $customer === '' || $reference_id === '') {
    http_response_code(422);
    echo json_encode(['status' => 422, 'message' => 'Missing required fields']);
    exit;
}

// Normalize phone
$telephone = preg_replace('/\D+/', '', $telephone);
if (strlen($telephone) === 10 && $telephone[0] === '0') {
    $telephone = '233' . substr($telephone, 1);
}

// ------------------ AGENT OPERATIONS (database/dbconfig.php) ------------------
// Find agent
$stmt = $con->prepare("SELECT user_id, first_name, last_name, email, telephone AS agent_tel, wallet FROM agents WHERE api = ? LIMIT 1");
$stmt->bind_param("s", $agent_api);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 404, 'message' => 'Agent not found']);
    exit;
}
$agent = $result->fetch_assoc();
$stmt->close();

$user_id    = $agent['user_id'];
$agent_name = $agent['first_name'].' '.$agent['last_name'];
$agent_email= $agent['email'];
$wallet     = floatval($agent['wallet']);

// ------------------ WAEC OPERATIONS (waec_dbconfig.php) ------------------
// Ensure reference_id is unique
$stmt = $waec_con->prepare("SELECT 1 FROM waec_payments WHERE reference_id=? LIMIT 1");
$stmt->bind_param("s", $reference_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 409, 'message' => 'Reference already used']);
    exit;
}
$stmt->close();

// Get price
$stmt = $waec_con->prepare("SELECT amount, original FROM prices WHERE target=? LIMIT 1");
$stmt->bind_param("s", $target);
$stmt->execute();
$stmt->bind_result($db_price, $original_price);
$stmt->fetch();
$stmt->close();

$db_price = floatval($db_price);
$original_price = floatval($original_price);

// Apply discount
if ($qty > 4) {
    $effective_unit_price = max($db_price - 2, 0);
    $effective_original = max($original_price - 2, 0);
} else {
    $effective_unit_price = $db_price;
    $effective_original = $original_price;
}

$total_amount = $effective_unit_price * $qty;
$total_profit = ($effective_unit_price - $effective_original) * $qty;

// Check wallet
if ($wallet < $total_amount) {
    http_response_code(402);
    echo json_encode(['status' => 402, 'message' => 'Insufficient wallet balance']);
    exit;
}

// ------------------ TRANSACTION ------------------
// Begin transaction (both databases)
$con->begin_transaction();
$waec_con->begin_transaction();

try {
    // Allocate WAEC codes
    $codes_res = $waec_con->query("SELECT serial_code, pin_code, reference_id FROM waec_codes WHERE status='NEW' LIMIT $qty FOR UPDATE");
    if ($codes_res->num_rows < $qty) {
        throw new Exception("Not enough WAEC codes available");
    }

    $allocated_codes = [];
    while ($row = $codes_res->fetch_assoc()) {
        $allocated_codes[] = $row;

        // Mark code as used
        $upd = $waec_con->prepare("UPDATE waec_codes SET status='USED' WHERE reference_id=? LIMIT 1");
        $upd->bind_param("s", $row['reference_id']);
        $upd->execute();
        $upd->close();
    }

    // Insert WAEC payment
    $stmt = $waec_con->prepare("INSERT INTO waec_payments 
        (reference_id, customer, telephone, email, amount, profit, qty, trans_date, trans_time, purpose, origin)
        VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?, ?)");
    $purpose = "WAEC Purchase";
    $origin  = "API";
    $stmt->bind_param("ssssdidss", $reference_id, $customer, $telephone, $agent_email, $total_amount, $total_profit, $qty, $purpose, $origin);
    $stmt->execute();
    $stmt->close();

    // Deduct wallet (agents DB)
    $new_wallet = round($wallet - $total_amount, 2);
    $stmt = $con->prepare("UPDATE agents SET wallet=? WHERE user_id=?");
    $stmt->bind_param("ds", $new_wallet, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log agent transaction
    $stmt = $con->prepare("INSERT INTO agents_transactions 
        (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose)
        VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)");
    $stmt->bind_param("sissdds", $reference_id, $user_id, $agent_name, $agent_email, $total_amount, $new_wallet, $purpose);
    $stmt->execute();
    $stmt->close();

    // Commit both DBs
    $waec_con->commit();
    $con->commit();

    // Prepare SMS
    $sms_message  = "WASSCE RESULT CHECKER\nQty: $qty\n----------------\n";
    foreach ($allocated_codes as $i => $c) {
        $sms_message .= ($i+1).") PIN: {$c['pin_code']} SERIAL: {$c['serial_code']}\n";
    }

    // Send SMS
    $sms_data = [
        'api_key' => $api_key,
        'sender'  => $sender_id,
        'to'      => $telephone,
        'message' => $sms_message
    ];
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_data));
    $sms_response = curl_exec($ch);
    $sms_error = curl_errno($ch) ? curl_error($ch) : null;
    curl_close($ch);

    // Return JSON
    echo json_encode([
        'status' => 200,
        'message' => 'Purchase successful',
        'reference_id' => $reference_id,
        'qty' => $qty,
        'unit_price' => $effective_unit_price,
        'total_amount' => $total_amount,
        'profit' => $total_profit,
        'new_wallet' => $new_wallet,
        'codes' => $allocated_codes,
        'sms_sent' => $sms_error ? false : true,
        'sms_response' => $sms_response,
        'sms_error' => $sms_error
    ]);
    exit;

} catch (Exception $e) {
    $waec_con->rollback();
    $con->rollback();
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => 'Transaction failed: '.$e->getMessage()]);
    exit;
}
?>
