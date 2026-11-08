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
    // Debugging: Check if session variable is set
    if (isset($_GET['admin_email'])) {
        $admin_email = mysqli_real_escape_string($con, $_GET['admin_email']);
        $network = "MTN";
        $status = "Pending";
        // Query the database to retrieve the order details based on the agent's email
        $select = "SELECT * FROM api_orders WHERE super_agent_email = '$admin_email' AND network='$network' AND order_status='$status' ORDER BY id DESC";
        $result = mysqli_query($con, $select);
        
        // Check if the query returned any rows
        if (mysqli_num_rows($result) > 0) {
            // Prepare the response data
            $response['status'] = 'success';
            $response['order_details'] = array();
            
            // Fetch all order details and add them to the response array
            while ($row = mysqli_fetch_assoc($result)) {
                $response['order_details'][] = array(
                    'order_id' => $row['order_id'],
                    'reference_id' => $row['reference_id'],
                    'agent_name' => $row['agent_name'],
                    'network' => $row['network'],
                    'gig' => $row['gig'],
                    'price' => $row['price'],
                    'beneficiary' => $row['beneficiary'],
                    'customer_email' => $row['customer_email'],
                    'order_date' => $row['order_date'],
                    'order_time' => $row['order_time'],
                    'order_status' => $row['order_status'],
                    'mode' => $row['mode'],
                    'agent_price' => $row['agent_price']
                );
            }
        } else {
            // No order found for the given email
            $response['status'] = 'error';
            $response['message'] = 'No order found for this email.';
        }
    } else {
        // Session variable 'agent_email' is missing
        $response['status'] = 'error';
        $response['message'] = 'Session agent email is required.';
    }
} else {
    // Invalid request method
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method. Use GET.';
}

// Close the database connection
mysqli_close($con);

// Return the JSON response
echo json_encode($response);
?>
