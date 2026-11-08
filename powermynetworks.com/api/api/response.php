<?php
// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set response content type to JSON
header('Content-Type: application/json');

// Include your database configuration
include '../../database/dbconfig.php';

// Initialize the response array
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the incoming JSON data
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['reference_id']) && isset($input['agent_api'])) {
        $reference = mysqli_real_escape_string($con, $input['reference_id']);
        $agent_api = mysqli_real_escape_string($con, $input['agent_api']);

        // Validate the agent
        $agentQuery = "SELECT * FROM agents WHERE api = '$agent_api'";
        $agentResult = mysqli_query($con, $agentQuery);

        if (mysqli_num_rows($agentResult) > 0) {
            // Check if the order exists for this agent
            $orderQuery = "SELECT * FROM orders_agent WHERE reference_id = '$reference'";
            $orderResult = mysqli_query($con, $orderQuery);

            if (mysqli_num_rows($orderResult) > 0) {
                $row = mysqli_fetch_assoc($orderResult);

                $response['status'] = 'success';
                $response['order_details'] = [
                    'beneficiary' => $row['beneficiary'],
                    'network' => $row['network'],
                    'gig' => $row['gig'],
                    'price' => $row['price'],
                    'order_date' => $row['order_date'],
                    'order_time' => $row['order_time'],
                    'order_status' => $row['order_status']
                ];
            } else {
                $response['status'] = 'error';
                $response['message'] = 'No order found for this reference ID.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid agent API.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Reference ID and agent API are required.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

// Close the database connection
mysqli_close($con);

// Return the JSON response
echo json_encode($response);
?>
