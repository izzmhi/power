<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database/dbconfig.php';
require 'config.php';

// === Error Logging ===
function log_error($message) {
    $logFile = __DIR__ . '/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

// === Log Script Start ===
log_error("=== Paystack Verification Started ===");

// === Validate Request ===
if (!isset($_GET['paystackpaid'])) {
    log_error("No 'paystackpaid' parameter in request.");
    exit();
}

$reference_id = mysqli_real_escape_string($con, $_GET['paystackpaid']);
log_error("Reference ID: $reference_id");

// === Validate Session Data ===
$required_session_keys = [
    'email', 'expected_amount', 'user_id', 'agent_name', 'agent_telephone', 
    'agent_email', 'shop', 'shop_name', 'fullnames', 'telephone', 
    'id_type', 'id_number', 'location', 'dob', 'region'
];

foreach ($required_session_keys as $key) {
    if (!isset($_SESSION[$key])) {
        log_error("Missing session data: $key (Ref: $reference_id)");
        header("Location: success.php?error=" . urlencode("Session expired: $key") . "&reference=$reference_id");
        exit();
    }
}

// === Assign Variables ===
$customer_email  = mysqli_real_escape_string($con, $_SESSION['email']);
$expected_amount = floatval($_SESSION['expected_amount']); // in pesewas
$agent_id        = $_SESSION['user_id'];
$agent_name      = $_SESSION['agent_name'];
$agent_tel       = $_SESSION['agent_telephone'];
$agent_email     = $_SESSION['agent_email'];
$shop            = $_SESSION['shop'];
$shop_name       = $_SESSION['shop_name'];
$account_type    = "Agent";
$purpose         = "Order";
$mode            = "PAYSTACK";
$origin          = "SHOP";

$fullname   = $_SESSION['fullnames'];
$telephone  = $_SESSION['telephone'];
$email      = $_SESSION['email'];
$id_type    = $_SESSION['id_type'];
$id_number  = $_SESSION['id_number'];
$location   = $_SESSION['location'];
$dob        = $_SESSION['dob'];
$region     = $_SESSION['region'];

log_error("Session data validated successfully for Agent ID: $agent_id");

// === Timezone ===
date_default_timezone_set('GMT');
$order_time = date('H:i:s A');
$order_date = date('l, F d, Y');
$real_date  = date('Y-m-d');

// === VERIFY TRANSACTION WITH PAYSTACK ===
log_error("Starting Paystack verification for Ref: $reference_id");
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference_id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache"
    ],
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    log_error("cURL Error: $err (Ref: $reference_id)");
    die("Verification failed.");
}

$responseData = json_decode($response, true);
if (!isset($responseData['status']) || !$responseData['status']) {
    log_error("Invalid Paystack response (Ref: $reference_id): " . $response);
    header("Location: success.php?error=" . urlencode("Transaction verification failed") . "&reference=$reference_id");
    exit();
}

$transaction = $responseData['data'];
$amount_paid = floatval($transaction['amount']); // in pesewas
$email_from_paystack = $transaction['customer']['email'] ?? null;
$status_from_paystack = strtolower($transaction['status'] ?? '');

log_error("Paystack returned amount: $amount_paid | expected: $expected_amount | status: $status_from_paystack");

// === VALIDATION ===
if ($email_from_paystack !== $customer_email) {
    log_error("Email mismatch. Paystack: $email_from_paystack | Session: $customer_email");
    header("Location: success.php?error=" . urlencode("Email mismatch") . "&reference=$reference_id");
    exit();
}

if ($status_from_paystack !== 'success') {
    log_error("Transaction not successful. Status: $status_from_paystack");
    header("Location: success.php?error=" . urlencode("Transaction not successful") . "&reference=$reference_id");
    exit();
}

// === Allow small variation ===
if (abs($amount_paid - $expected_amount) > 3) {
    log_error("Amount mismatch detected. Expected=$expected_amount, Paid=$amount_paid (Ref=$reference_id)");
    header("Location: success.php?error=" . urlencode("Amount mismatch. Possible tampering.") . "&reference=$reference_id");
    exit();
}

// === Prevent Duplicate Orders ===
$check = $con->prepare("SELECT reference_id FROM afa_registration WHERE reference_id = ? LIMIT 1");
$check->bind_param("s", $reference_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    log_error("Duplicate order detected: $reference_id");
    header("Location: success.php?error=" . urlencode("Duplicate order") . "&reference=$reference_id");
    exit();
}
$check->close();

$customer_price = $expected_amount / 100;

// === Fetch Agent Pricing ===
$stmt = $con->prepare("SELECT agent_price, original_price FROM agents_prices_afa WHERE agent_id = ?");
$stmt->bind_param("s", $agent_id);
$stmt->execute();
$stmt->bind_result($agent_price, $original_price);
$hasData = $stmt->fetch();
$stmt->close();

if (!$hasData) {
    log_error("No pricing data found for Agent ID: $agent_id");
    header("Location: success.php?error=" . urlencode("No pricing data found.") . "&reference=$reference_id");
    exit();
}

// === Fetch Agent Wallet ===
$agentQuery = $con->prepare("SELECT wallet FROM agents WHERE user_id = ?");
$agentQuery->bind_param("s", $agent_id);
$agentQuery->execute();
$result = $agentQuery->get_result();
if ($result->num_rows === 0) {
    log_error("Agent not found: $agent_id");
    header("Location: success.php?error=" . urlencode("Agent account not found.") . "&reference=$reference_id");
    exit();
}
$wallet = $result->fetch_assoc()['wallet'];
$agentQuery->close();

// === Shop Profit ===
$shopQuery = $con->prepare("SELECT profit, balance FROM shop WHERE user_id = ?");
$shopQuery->bind_param("s", $agent_id);
$shopQuery->execute();
$result = $shopQuery->get_result();
if ($result->num_rows === 0) {
    log_error("Shop not found for agent: $agent_id");
    header("Location: success.php?error=" . urlencode("Shop not found.") . "&reference=$reference_id");
    exit();
}
$shopData = $result->fetch_assoc();
$shopQuery->close();

// === Compute Balances ===
$profit = $customer_price - $agent_price;
$new_profit = $shopData['profit'] + $profit;
$new_balance = $shopData['balance'] + $profit;
$admin_profit = $agent_price - $original_price;

log_error("Profit calculated: profit=$profit | new_balance=$new_balance | admin_profit=$admin_profit");

// === Check Activation ===
$shop = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM shop_activation"));
$agentMTN = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM afa_activation"));
if (!$shop || $shop['status'] !== 'ACTIVATED' || !$agentMTN || $agentMTN['status'] !== 'ACTIVATED') {
    log_error("Activation error - Shop: {$shop['status']} | AFA: {$agentMTN['status']}");
    header("Location: success.php?error=" . urlencode("AFA service or routing config error") . "&reference=$reference_id");
    exit();
}

// === Insert Order ===
$register_status = 'Pending';
$medium = "EXCEL";

$stmt = $con->prepare("INSERT INTO afa_registration(reference_id, agent_id, agent_name, agent_email, fullname, telephone, id_type, id_number, location, region, 
dob, register_time, register_date, real_date, register_status, email, account_type, price, customer_price, mode, customer_email, profit, medium, origin)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param("sssssssssssssssssdssssss",
    $reference_id, $agent_id, $agent_name, $agent_email, $fullname, $telephone,
    $id_type, $id_number, $location, $region, $dob, $order_time, $order_date,
    $real_date, $register_status, $email, $account_type, $agent_price, $customer_price,
    $mode, $customer_email, $admin_profit, $medium, $origin
);
if ($stmt->execute()) {
    log_error("Inserted order for Ref: $reference_id successfully.");
} else {
    log_error("Failed to insert order for Ref: $reference_id - " . $stmt->error);
}
$stmt->close();

// === Log Transaction ===
$stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, trans_date, purpose) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssddss", $reference_id, $reference_id, $agent_id, $agent_name, $agent_email, $agent_price, $wallet, $real_date, $purpose);
if ($stmt->execute()) {
    log_error("Transaction logged successfully for Ref: $reference_id");
} else {
    log_error("Failed to log transaction: " . $stmt->error);
}
$stmt->close();

// === Update Shop ===
$stmt = $con->prepare("UPDATE shop SET profit = ?, balance = ? WHERE user_id = ?");
$stmt->bind_param("dds", $new_profit, $new_balance, $agent_id);
if ($stmt->execute()) {
    log_error("Shop updated successfully for Agent: $agent_id");
} else {
    log_error("Failed to update shop for Agent: $agent_id - " . $stmt->error);
}
$stmt->close();

log_error("=== Paystack Order Completed Successfully for Ref: $reference_id ===");

$success_message = "Order successfully made.";
header("Location: success.php?success=" . urlencode($success_message) . "&reference=" . urlencode($reference_id));
exit();

?>
