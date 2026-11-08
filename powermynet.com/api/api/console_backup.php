<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database/dbconfig.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Extract data
        $agent_email = $data['agent_email'];
        $recipient_number = $data['recipient_number'];
        $network = $data['network'];
        $code_network = $data['code_network'];
        $gig = $data['gig'];
        $real_package = $data['real_package'];
        $prices = $data['price']; // Assuming this comes from the API request
        $reference_id = $data['reference_id'];
        // Date and time generation
        date_default_timezone_set('GMT');
        $order_time = date('H:i:s A');
        $order_date = date('l, F d, Y');
        $real_date = date('Y-m-d');
        $status = "Delivered";
        // Check if the agent exists
        $stmt = $con->prepare("SELECT * FROM consoles WHERE agent_email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $agent_email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $package = $row['package'];
                $used_package = $row['used_package'];
                $agent_name = $row['agent_name'];
                $agent_tel = $row['agent_tel'];
                $agent_id = $row['agent_id'];
                $console_name = $row['console_name'];
                $console_id = $row['console_id'];
                $stmt->close();
                        // Insert into api_orders
                        $stmt = $con->prepare("INSERT INTO console_orders(order_id,console_id,console_name,network,agent_id,agent_name,agent_email,agent_tel,recipient,package,order_time,order_date,real_date,order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                        if ($stmt) {
                                $stmt->bind_param("ssssssssssssss", $reference_id,$console_id,$console_name,$network,$agent_id,$agent_name,$agent_email,$agent_tel,$recipient_number,$gig,$order_time,$order_date,$real_date,$status);
                            if ($stmt->execute()) {
                                $stmt->close();
                                $new_package = $package - $gig;
                                $new_used_package = $used_package + $gig;
                                $stmt = $con->prepare("UPDATE consoles SET package = ?, used_package = ? WHERE agent_email = ?");
                                if ($stmt) {
                                    $stmt->bind_param("sss", $new_package, $new_used_package, $agent_email);
                                    if ($stmt->execute()) {
                                        $stmt->close();
                                        // Insert transaction into agents_transactions
                                        $stmt = $con->prepare("INSERT INTO console_transactions (trans_id,console_id,console_name,network,agent_id,agent_name,agent_email,agent_tel,package,trans_date) VALUES (?,?,?,?,?,?,?,?,?,?)");
                                        if ($stmt) {
                                            $stmt->bind_param("ssssssssss", $reference_id,$console_id,$console_name,$network,$agent_id,$agent_name,$agent_email,$agent_tel,$gig,$real_date);
                                            if ($stmt->execute()) {
                                                    // API endpoint URL
                                                     $url = 'https://www.value4moni.com/api/v1/inititate_transaction';
                                                     // Data to send to the API
                                                     $data = array(
                                                         "API_Key" => "REDACTED",
                                                         "Receiver" => $recipient_number,
                                                         "Volume" => $real_package,
                                                         "Reference" => $reference_id,
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
                                                http_response_code(200);
                                                echo json_encode(['message' => 'Data Purchase Successful']);
                                                 }
                                             // Close cURL session
                                             curl_close($ch);
                                                
                                            } else {
                                                http_response_code(500);
                                                echo json_encode(['message' => 'Failed to insert transaction: ' . $stmt->error]);
                                            }
                                            $stmt->close();
                                        } else {
                                            http_response_code(500);
                                            echo json_encode(['message' => 'Failed to prepare transaction statement: ' . $con->error]);
                                        }
                                    } else {
                                        http_response_code(500);
                                        echo json_encode(['message' => 'Failed to update wallet and credit: ' . $stmt->error]);
                                    }
                                } else {
                                    http_response_code(500);
                                    echo json_encode(['message' => 'Failed to prepare wallet update statement: ' . $con->error]);
                                }
                            } else {
                                http_response_code(500);
                                echo json_encode(['message' => 'Failed to insert order: ' . $stmt->error]);
                            }
                        } else {
                            http_response_code(500);
                            echo json_encode(['message' => 'Failed to prepare order insertion statement: ' . $con->error]);
                        }

            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Agent not found']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to prepare agent selection statement: ' . $con->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}

// Close the database connection
$con->close();
?>
