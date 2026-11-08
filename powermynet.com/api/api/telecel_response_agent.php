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
    if (isset($_GET['client_email'])) {
        $client_email = mysqli_real_escape_string($con, $_GET['client_email']);
        $network = "Telecel";
        $select = "SELECT * FROM orders_agent WHERE client_email = '$client_email' AND network='$network' ORDER BY id DESC";
        $result = mysqli_query($con, $select);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'success';
            $response['order_details'] = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $response['order_details'][] = array(
                    'reference_id' => $row['reference_id'],
                    'gig' => $row['gig'],
                    'price' => $row['price'],
                    'client_email'=>$row['client_email'],
                    'beneficiary' => $row['beneficiary'],
                    'network'=>$row['network'],
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
