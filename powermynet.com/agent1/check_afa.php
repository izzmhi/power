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


$agent_api = "REDACTED";
$reference_id = $_GET['reference_id'] ?? '';

require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.codecraftnetwork.com/api/response_afa.php');
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
    header("Location: afa.php?success=" . urlencode($success));
    exit();
} else {
    $error = "Error: " . $result['message'];
    header("Location: afa.php?error=" . urlencode($error));
    exit();
}



?>