<?php
session_start(); // Start the session
// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the response content type to JSON
header('Content-Type: application/json');

// Include your database configuration file
include '../../database/dbconfig.php';

// Initialize an array to store the response
$response = array();

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['api'])) {
        $api = mysqli_real_escape_string($con, $_GET['api']);
        $network = "TELECEL";
        $select = "SELECT * FROM orders_agent WHERE api = '$api' AND network='$telecel' ORDER BY id DESC";
        $result = mysqli_query($con, $select);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'success';
            $response['order_details'] = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $response['order_details'][] = array(
                    'reference_id' => $row['reference_id'],
                    'gig' => $row['gig'],
                    'price' => $row['price'],
                    'beneficiary' => $row['beneficiary'],
                    'order_date' => $row['order_date'],
                    'order_time' => $row['order_time'],
                    'order_status' => $row['order_status'],
                );
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No order found for this email.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Session agent email is required.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method. Use GET.';
}

mysqli_close($con);
echo json_encode($response);
?>
