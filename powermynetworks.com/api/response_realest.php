<?php
file_put_contents(__DIR__ . '/debug_start.log', "Script started at " . date('H:i:s') . "\n", FILE_APPEND);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// === LOG FILE PATH ===
$logFile = __DIR__ . '/api_response.log';
function log_event($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $logFile);
}

// Log start
log_event("=== API test started ===");

// === Validate Request Method ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_event("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'status' => 'error',
        'error_code' => 405,
        'message' => 'Invalid request method. Use POST.'
    ]);
    exit;
}

// === Accept both JSON and form POST ===
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (empty($data)) $data = $_POST; // fallback to normal POST

log_event("Received data: " . json_encode($data));

// === Test response ===
$response = [
    'status'  => 'success',
    'code'    => 200,
    'message' => 'API test successful'
];

// === Cleanup and respond ===
log_event("Final Response: " . json_encode($response));
log_event("=== End Request ===\n");

if (isset($con)) mysqli_close($con);

echo json_encode($response);
exit;
?>
