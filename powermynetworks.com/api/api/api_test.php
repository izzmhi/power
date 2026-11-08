<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: application/json');

// Set HTTP response code
http_response_code(200);

// Return JSON response
echo json_encode([
    'status' => http_response_code(), // This will return 200
    'message' => 'Successful'
]);
?>
