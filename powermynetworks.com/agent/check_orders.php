<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$logFile = __DIR__ . '/order_status_debug.log';
ini_set('error_log', $logFile);

function log_order($message, array $context = []) {
    $time = date('Y-m-d H:i:s');
    $line = "[$time] $message";
    if (!empty($context)) {
        $line .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES);
    }
    error_log($line . PHP_EOL, 3, __DIR__ . '/order_status_debug.log');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../database/dbconfig.php';

$network = $_GET['network'] ?? '';
$reference_id = $_GET['reference_id'] ?? '';

log_order('Order status check started');

if (empty($reference_id)) {
    log_order('Missing reference_id');
    header("Location: my_orders.php?status=error&reason=missing_reference");
    exit();
}

log_order('Using reference_id', ['reference_id' => $reference_id]);

if ($network === "MTN") {
$pages_api = 'REDACTED';
require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.dataworksgh.com/api/response_regular.php');
$data = [
    'reference_id' => $reference_id,
];
$jsonData = json_encode($data);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $pages_api,
    ],
    CURLOPT_POSTFIELDS     => $jsonData,
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);
$decoded = json_decode($response, true);
// Get message from response body (if available)
$status = strtolower((string)($decoded['status'] ?? ''));
$order_status = $decoded['order_details']['order_status'] ?? 'Processing';
$success = "Order is $order_status";
if ($http_code === 200 && $status === 'success') {
    header("Location: my_orders.php?reference={$reference_id}&success=".urlencode($success));
    exit();
}

    else{
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_order('JSON decode failed', [
            'error' => json_last_error_msg()
        ]);
        header("Location: my_orders.php?reference={$reference_id}&error=error");
        exit();
    }
    }

}






elseif ($network === "MTNBIG") {
    // Code for MTN
$url = pmn_api_url('https://api.dataworksgh.com/api/customer_response.php');
$data = [
    'reference_id' => $reference_id,  // Replace with actual reference ID dynamically
    'agent_email' => $agent_email
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
if ($result['status'] === 'success') {
    $orderStatus = $result['order_details']['order_status'];
    $beneficiary = $result['order_details']['beneficiary'];
    $success = "Order status: for $beneficiary is $orderStatus";
    header("Location: special_orders_mtn.php?success=" . urlencode($success));
    exit();
} else {
    $error = "Error: " . $result['message'];
    header("Location: special_orders_mtn.php?error=" . urlencode($error));
    exit();
}
    
// Code for AT    
} elseif ($network === "AT") {
$url = pmn_api_url('https://api.codecraftnetwork.com/api/response_regular.php');
$data = [
    'reference_id' => $reference_id,  // Replace with actual reference ID dynamically
    'agent_api' => $agent_api
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
if ($result['status'] === 'success') {
    $orderStatus = $result['order_details']['order_status'];
    $success = "Order Status: $orderStatus";
    header("Location: orders.php?success=" . urlencode($success));
    exit();
} else {
    $error = "Error: " . $result['message'];
    header("Location: orders.php?error=" . urlencode($error));
    exit();
}


 // Code for TELECEL    
} 
elseif ($network === "TELECEL") {

    $code_api = "REDACTED";
    $baseUrl  = pmn_api_url('https://api.codecraftnetwork.com/api/response_regular.php');

    // TELECEL uses GET with query parameter
    $url = $baseUrl . '?reference_id=' . urlencode($reference_id);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET        => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'x-api-key: ' . $code_api,
        ],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    log_order('TELECEL API raw response', [
        'url'        => $url,
        'http_code'  => $http_code,
        'curl_error' => $curl_err,
        'raw'        => $response,
    ]);

    /* -------------------------
       TRANSPORT FAILURE
    -------------------------- */
    if ($response === false || $http_code !== 200) {
        header("Location: my_orders.php?reference={$reference_id}&error=api_unreachable");
        exit();
    }

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_order('TELECEL JSON decode failed', [
            'error' => json_last_error_msg(),
            'raw'   => $response,
        ]);
        header("Location: my_orders.php?reference={$reference_id}&error=invalid_json");
        exit();
    }

    $status_ok   = ($decoded['status'] ?? 0) == 200;
    $success_ok  = ($decoded['success'] ?? false) === true;
    $order_status = $decoded['data']['order_status'] ?? 'Processing';

    log_order('TELECEL API decoded', [
        'status'        => $decoded['status'] ?? null,
        'success'       => $decoded['success'] ?? null,
        'order_status'  => $order_status,
    ]);

    /* -------------------------
       FINAL DECISION
    -------------------------- */
    if ($status_ok && $success_ok) {
        $successMsg = "Order is {$order_status}";
        header(
            "Location: my_orders.php?reference={$reference_id}&success=" .
            urlencode($successMsg)
        );
        exit();
    }

    header("Location: my_orders.php?reference={$reference_id}&error=unknown");
    exit();
}


?>