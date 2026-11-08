<?php
$agent_api = "REDACTED";            
// API data preparation
$data = [
    'agent_api' => $agent_api,
    'recipient_number' => "0275317847",
    'network' => "AT",
    'gig' => 1,
    'reference_id' => "sd4",
];
$jsonData = json_encode($data);
require_once __DIR__ . '/configs/pmn_provider.php';
$url = pmn_api_url('https://api.codecraftnetwork.com/api/initiate.php');
// cURL setup and execution
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
$response = curl_exec($ch);
// Get HTTP response code BEFORE closing cURL
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// Decode JSON response
$decoded = json_decode($response, true);
// Get message from response body (if available)
$response_message = $decoded['message'] ?? 'Unknown response';
if ($http_code == 200) {
    echo "Order Successful: [$http_code] $response_message";
} else {
        echo "Order Failed: [$http_code] $response_message";
    }

?>