<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];
$success = [];

include '../database/dbconfig.php';

if (isset($_GET['successfullypaid'])) {
    // Sanitize and validate input data
    $network = filter_var($_GET['network'], FILTER_SANITIZE_STRING);
    $gig = filter_var($_GET['gig'], FILTER_SANITIZE_STRING);
    $price = filter_var($_GET['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $customer_email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $recipient_number = filter_var($_GET['beneficiary'], FILTER_SANITIZE_STRING);
    $real_package = filter_var($_GET['real_package'], FILTER_SANITIZE_STRING);
    
    if (!$customer_email || !$recipient_number || !$network || !$gig || !$price || !$real_package) {
        $error = "Fields are empty";
        header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
        exit();
    } else {
        
            // API endpoint URL
    $url = 'https://www.value4moni.com/api/v1/inititate_transaction';
    // Data to send to the API
    $data = array(
        "API_Key" => "REDACTED",
        "Receiver" => $recipient_number,
        "Volume" => $real_package,
        "Reference" => "7iKB9ycGZCZ724mPjP19",
        "Package_Type" => $network
    );
    // Initialize cURL session
    $ch = curl_init($url);
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the content type to JSON
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode data as JSON
    // Execute the cURL request
    $response = curl_exec($ch);
    // Check if any error occurred
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Handle the response from the API
        echo 'Response from API: ' . $response;
    }
    // Close cURL session
    curl_close($ch);
        
        
        
        $super_agent_email = "owusuebenezer377@gmail.com";
        $super_agent_name = "EBENEZER MESRENYAME";

            // API data preparation
        $data = [
            'super_agent_name' => $super_agent_name,
            'super_agent_email' => $super_agent_email,
            'agent_id' => $agent_id,
            'agent_name' => $agent_name,
            'agent_tel' => $agent_tel,
            'agent_email' => $agent_email,
            'recipient_number' => $recipient_number,
            'network' => $network,
            'gig' => $gig,
            'price' => $price,
            'customer_email' => $customer_email,
        ];
        $jsonData = json_encode($data);
        require_once __DIR__ . '/../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.dataworksgh.com/api/index.php');

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

        if (curl_errno($ch)) {
            $error = "cURL error: " . curl_error($ch);
            curl_close($ch);
            echo $error;
            exit();
        } else {
            $response = json_decode($response, true);
            curl_close($ch);

            if (isset($response['message'])) {
                $success_message = $response['message'];

                // Prepare order details
                $status = "Pending";
                date_default_timezone_set('GMT');
                $order_time = date('H:i:s A');
                $order_date = date('l, F d, Y');
                $real_date = date('Y-m-d');
                $order_id = bin2hex(random_bytes(8));
                $reference_id = bin2hex(random_bytes(8));

                // Insert order into the database using prepared statements
                $stmt = $con->prepare("INSERT INTO orders (order_id, reference_id, email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("ssssssdssss", $order_id, $reference_id, $customer_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status);
                    if ($stmt->execute()) {
                       header("Location: success.php?success=" . urlencode($success_message) . "&email=" . urlencode($customer_email));
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: " . $con->error;
                    header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
                }
            } else {
                $error = "API response does not contain a message";
                  header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
            }
        }
        
    }
} else {
    $error = "Network parameter is missing";
    header("Location: success.php?error=" . urlencode($error));
}

?>


                        // Prepare order details
                $status = "Pending";
                date_default_timezone_set('GMT');
                $order_time = date('H:i:s A');
                $order_date = date('l, F d, Y');
                $real_date = date('Y-m-d');
                $order_id = bin2hex(random_bytes(8));
                // Insert order into the database using prepared statements
                $stmt = $con->prepare("INSERT INTO orders (order_id, reference_id, email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("ssssssdssss", $order_id, $reference_id, $customer_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status);
                    if ($stmt->execute()) {
                       header("Location: success.php?success=" . urlencode($success_message) . "&email=" . urlencode($customer_email));
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: " . $con->error;
                    header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
                }