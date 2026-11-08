<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

$agent_email = "azumahnbalino1@gmail.com";
$agent_api = "REDACTED";
$network = $_GET['network'] ?? '';
$reference_id = $_GET['reference_id'] ?? '';

if ($network === "MTN") {
    // Code for MTN
require_once __DIR__ . '/../configs/pmn_provider.php';
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
    $success = "Order status: $orderStatus";
    header("Location: special_orders.php?success=" . urlencode($success));
    exit();
} else {
    $error = "Error: " . $result['message'];
    header("Location: special_orders.php?error=" . urlencode($error));
    exit();
}    
    
   // Code for AT    
} 
elseif ($network === "AT") {
$url = pmn_api_url('https://api.codecraftnetwork.com/api/response_big_time.php');
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
    header("Location: special_orders.php?success=" . urlencode($success));
    exit();
} else {
    $error = "Error: " . $result['message'];
    header("Location: special_orders.php?error=" . urlencode($error));
    exit();
}
   
} 


else {
    header("Location: special_orders.php?success=Network is not found");
    exit();
}


?>