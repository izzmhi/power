<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../database/dbconfig.php';
require 'config.php';
$shop = isset($_SESSION['shop']) ? $_SESSION['shop'] : '';
// === Error Logging ===
function log_error($message) {
    // fix path: __DIR__ + relative path
    $logFile = __DIR__ . '/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}
// === Validate Request ===
if (!isset($_GET['paystackpaid'])) {
    log_error("No 'paystackpaid' parameter in request.");
    exit();
}
$reference_id = mysqli_real_escape_string($con, $_GET['paystackpaid']);
// === Validate Session Data ===
$required_session_keys = [
    'network', 'beneficiary', 'email',
    'user_id', 'agent_name', 'agent_telephone', 'agent_email', 'shop', 'shop_name'
];
foreach ($required_session_keys as $key) {
    if (!isset($_SESSION[$key])) {
        log_error("Missing session data: $key (Ref: $reference_id)");
        header("Location: /$shop/success.php?error=" . urlencode("Session expired: $key") . "&reference=$reference_id");
        exit();
    }
}
// === Assign Variables ===
$network          = !empty($_SESSION['network']) ? mysqli_real_escape_string($con, $_SESSION['network']) : 'MTN';
$recipient_number = mysqli_real_escape_string($con, $_SESSION['beneficiary']);
$customer_email   = mysqli_real_escape_string($con, $_SESSION['email']);
$agent_id         = mysqli_real_escape_string($con, $_SESSION['user_id']);
$agent_name       = mysqli_real_escape_string($con, $_SESSION['agent_name']);
$agent_tel        = mysqli_real_escape_string($con, $_SESSION['agent_telephone']);
$agent_email      = mysqli_real_escape_string($con, $_SESSION['agent_email']);
$shop             = mysqli_real_escape_string($con, $_SESSION['shop']);
$shop_name        = mysqli_real_escape_string($con, $_SESSION['shop_name']);
$account_type     = "Agent";
$purpose          = "Order";
$mode             = "PAYSTACK";
$origin           = "SHOP";

/**
 * Timezone / date
 */
date_default_timezone_set('Africa/Accra');
$order_time = date('H:i:s A');
$order_date = date('l, F d, Y');
$real_date  = date('Y-m-d');

/**
 * Verify Paystack transaction
 */
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference_id),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache"
    ],
]);
$paystack_response = curl_exec($curl);
$curl_err = curl_error($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($curl_err) {
    redirect_error("cURL Error: $curl_err", $reference_id);
}

$decoded = json_decode($paystack_response, true);
if (!is_array($decoded) || !isset($decoded['status']) || $decoded['status'] !== true || !isset($decoded['data'])) {
    redirect_error("Transaction verification failed", $reference_id);
}

$transaction = $decoded['data'];
$email_from_paystack = $transaction['customer']['email'] ?? null;
$status_from_paystack = strtolower($transaction['status'] ?? '');

/* Validate email and status */
if ($email_from_paystack !== $customer_email) {
    redirect_error("Email mismatch", $reference_id);
}
if ($status_from_paystack !== 'success') {
    redirect_error("Transaction not successful", $reference_id);
}

/* Compute amounts: amount from Paystack is in pesewas (kobo-like), integer */
$amount_paid_pesewas = intval($transaction['amount'] ?? 0);
if ($amount_paid_pesewas <= 0) {
    redirect_error("Invalid transaction amount", $reference_id);
}
$amount_in_ghs = $amount_paid_pesewas / 100.0;
$paystack_fee = round($amount_in_ghs * 0.0195, 2);
$customer_price = round($amount_in_ghs - $paystack_fee, 2);

/**
 * Prevent duplicate orders
 */
$check = $con->prepare("SELECT reference_id FROM special_offers_orders WHERE reference_id = ? LIMIT 1");
if (!$check) redirect_error("Database error (prepare) while checking duplicates", $reference_id);
$check->bind_param("s", $reference_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    redirect_error("Duplicate order", $reference_id);
}
$check->close();

/**
 * Fetch agent pricing (with margin flexibility)
 */
$low = $customer_price - 1;
$high = $customer_price + 1;

$stmt = $con->prepare("SELECT package, agent_price, original FROM agents_prices_special WHERE network = ? AND agent_id = ? AND amount BETWEEN ? AND ?");
if (!$stmt) redirect_error("Database error (prepare) while fetching pricing", $reference_id);
$stmt->bind_param("ssdd", $network, $agent_id, $low, $high);
$stmt->execute();
$stmt->bind_result($package, $agent_price, $original_price);
$hasData = $stmt->fetch();
$stmt->close();

if (!$hasData) {
    redirect_error("No pricing data found.", $reference_id);
}

// === Fetch Agent Wallet ===
$agentQuery = $con->prepare("SELECT wallet FROM agents WHERE user_id = ? LIMIT 1");
$agentQuery->bind_param("s", $agent_id);
$agentQuery->execute();
$result = $agentQuery->get_result();
if ($result->num_rows === 0) {
    header("Location: /$shop/success.php?error=" . urlencode("Agent account not found.") . "&reference=$reference_id");
    exit();
}
$wallet = (float)$result->fetch_assoc()['wallet'];
$agentQuery->close();

// === Shop Profit ===
$shopQuery = $con->prepare("SELECT profit, balance FROM shop WHERE user_id = ? LIMIT 1");
$shopQuery->bind_param("s", $agent_id);
$shopQuery->execute();
$result = $shopQuery->get_result();
if ($result->num_rows === 0) {
    header("Location: /$shop/success.php?error=" . urlencode("Shop not found.") . "&reference=$reference_id");
    exit();
}
$shopData = $result->fetch_assoc();
$shopQuery->close();

// === Compute balances ===
$profit = ($customer_price) - $agent_price;
$new_profit = $shopData['profit'] + $profit;
$new_balance = $shopData['balance'] + $profit;
$admin_profit = $agent_price - $original_price;

// === Check activations & routing ===
$shopActivation = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM shop_activation LIMIT 1"));
$agentAT = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM agent_mtnbig_activation LIMIT 1"));
$apiSwitch = mysqli_fetch_assoc(mysqli_query($con, "SELECT mtn_big_time FROM api_switch LIMIT 1"));

if (
    !$shopActivation || $shopActivation['status'] !== 'ACTIVATED' ||
    !$agentAT || $agentAT['status'] !== 'ACTIVATED' ||
    !$apiSwitch || !in_array($apiSwitch['mtn_big_time'], ['API', 'EXCEL'])
) {
    header("Location: /$shop/success.php?error=" . urlencode("MTN service or routing config error") . "&reference=$reference_id");
    exit();
}

if ($apiSwitch['mtn_big_time'] === 'API') {
    $medium      = "API";
    $order_status = 'Pending';
    $super_agent_email = "azumahnbalino1@gmail.com";
    $data = [
        'super_agent_email' => $super_agent_email,
        'agent_email' => $agent_email,
        'recipient_number' => $recipient_number,
        'network' => $network,
        'gig' => $package,
        'client_email' => $agent_email,
        'price' => $agent_price, // actual amount
        'reference_id' => $reference_id,
    ];
    $jsonData = json_encode($data);
    require_once __DIR__ . '/../configs/pmn_provider.php';
    $url = pmn_api_url('https://api.dataworksgh.com/api/power.php', 'mtn_big_time');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        log_error("cURL error: " . curl_error($ch));
        curl_close($ch);
        header("Location: /$shop/success.php?error=" . urlencode("Order failed. cURL error") . "&reference=$reference_id");
        exit();
    } else {
        $response = json_decode($response, true);
        curl_close($ch);

// Check the response code
if ($response['code'] == '200') {
  $order_status = 'Pending'; // ensure defined
log_error("API connection (" . $response['code'] . ") for Ref=" . $reference_id);
// === Insert into orders_agent ===
log_error("About to insert into special_offers_orders (Ref=$reference_id)");
$stmt = $con->prepare("INSERT INTO special_offers_orders(reference_id, agent_id, agent_name, agent_tel, agent_email, customer_email, beneficiary, 
                network, gig, price, order_time, order_date, real_date, order_status, mode, account_type, original, profit, medium,origin)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
if (!$stmt) {
    log_error("Prepare failed (orders_agent): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("sssssssssdssssssdsss",
    $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $customer_email,
                $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode, $account_type,
                $original_price, $admin_profit, $medium,$origin )) 
                {
        log_error("Bind failed (orders_agent): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (orders_agent): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Inserted into orders_agent OK (Ref=$reference_id). Affected: " . $stmt->affected_rows);
    }
    $stmt->close();
}

// === Log Transaction (agents_transactions) ===
log_error("About to insert into agents_transactions (Ref=$reference_id)");
$stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    log_error("Prepare failed (agents_transactions): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("ssssddss", $reference_id, $agent_id, $agent_name, $agent_email, $agent_price, $wallet, $real_date, $purpose)) {
        log_error("Bind failed (agents_transactions): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (agents_transactions): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Inserted into agents_transactions OK (Ref=$reference_id). Affected: " . $stmt->affected_rows);
    }
    $stmt->close();
}

// === Update Shop ===
log_error("About to update shop (Ref=$reference_id)");
$stmt = $con->prepare("UPDATE shop SET profit = ?, balance = ? WHERE user_id = ?");
if (!$stmt) {
    log_error("Prepare failed (shop update): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("dds", $new_profit, $new_balance, $agent_id)) {
        log_error("Bind failed (shop update): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (shop update): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Shop updated OK (Ref=$reference_id). Affected: " . $stmt->affected_rows);
    }
    $stmt->close();
}

// === All done: redirect to success AFTER SMS and DB ops ===
log_error("All insertion and SMS successful OK (Ref=$reference_id)");
$success_message = "Order successfully made.";
header("Location: /$shop/success.php?success=" . urlencode($success_message) . "&reference=" . urlencode($reference_id));
exit();
    } 
    else {
        $error = "MTN API returned error: Code {$response_code} Message: {$response_msg}";
        log_error("MTN AP error: $error (Ref: $reference_id)");
        header("Location: /$shop/success.php?error=" . urlencode($error) . "&reference=$reference_id");
        exit();
    }
}
} 

else {
    // EXCEL path
    $order_status = 'Pending';
    $medium       = "EXCEL";
log_error("About to insert into special_offers_orders (Ref=$reference_id)");
$stmt = $con->prepare("INSERT INTO special_offers_orders(reference_id, agent_id, agent_name, agent_tel, agent_email, customer_email, beneficiary, 
                network, gig, price, order_time, order_date, real_date, order_status, mode, account_type, original, profit, medium,origin)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
if (!$stmt) {
    log_error("Prepare failed (orders_agent): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("sssssssssdssssssdsss",
    $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $customer_email,
                $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode, $account_type,
                $original_price, $admin_profit, $medium,$origin )) 
                {
        log_error("Bind failed (orders_agent): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (orders_agent): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Inserted into orders_agent OK (Ref=$reference_id). Affected: " . $stmt->affected_rows);
    }
    $stmt->close();
}

// === Log Transaction (agents_transactions) ===
log_error("About to insert into agents_transactions (Ref=$reference_id)");
$stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    log_error("Prepare failed (agents_transactions): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("ssssddss", $reference_id, $agent_id, $agent_name, $agent_email, $agent_price, $wallet, $real_date, $purpose)) {
        log_error("Bind failed (agents_transactions): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (agents_transactions): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Inserted into agents_transactions OK (Ref=$reference_id). Affected: " . $stmt->affected_rows);
    }
    $stmt->close();
}

// === Update Shop ===
log_error("About to update shop (Ref=$reference_id)");
$stmt = $con->prepare("UPDATE shop SET profit = ?, balance = ? WHERE user_id = ?");
if (!$stmt) {
    log_error("Prepare failed (shop update): " . $con->error . " (Ref=$reference_id)");
} else {
    if (!$stmt->bind_param("dds", $new_profit, $new_balance, $agent_id)) {
        log_error("Bind failed (shop update): " . $stmt->error . " (Ref=$reference_id)");
    } elseif (!$stmt->execute()) {
        log_error("Execute failed (shop update): " . $stmt->error . " (Ref=$reference_id)");
    } else {
        log_error("Shop updated OK (Ref=$reference_id). Affected: " . $stmt->affected_rows); 
    }
    $stmt->close();
}

// === All done: redirect to success AFTER SMS and DB ops ===
log_error("All insertion and SMS successful OK (Ref=$reference_id)");
$success_message = "Order successfully made.";
header("Location: /$shop/success.php?success=" . urlencode($success_message) . "&reference=" . urlencode($reference_id));
exit();
}
?>
