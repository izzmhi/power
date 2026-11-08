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
        $gig = $data['gig'];
        $real_package = $data['real_package'];
        $reference_id = $data['reference_id'];

        // Date and time generation
        date_default_timezone_set('GMT');
        $order_time = date('H:i:s A');
        $order_date = date('l, F d, Y');
        $real_date = date('Y-m-d');

        $status = "Pending";
        $mode = "API";
        $purpose = "Data Purchase";
        
         $account_type = "Agent";

        // Check if the agent exists
        $stmt = $con->prepare("SELECT * FROM agents WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $agent_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $wallet = $row['wallet'];
                $credit = $row['credit'];
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
                $user_id = $row['user_id'];
                $agent_email = $row['email'];
                $agent_tel = $row['telephone'];
                $agent_name = $first_name . " " . $last_name;

                $stmt->close();

                // Fetch price from the prices table
                $stmt = $con->prepare("SELECT amount,original FROM prices WHERE network = ? AND package = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $network, $gig);
                    $stmt->execute();
                    $stmt->bind_result($price,$original_price);
                    $stmt->fetch();
                    $stmt->close();

                    if ($price !== null) {

                            $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdssssssd", $reference_id,$reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price);
                                if ($stmt->execute()) {
                                    $stmt->close();
                                $walletf = $wallet - $price;
                                $creditf = $credit - $price;
                                $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE email = ?");
                                if ($stmt) {
                                    $stmt->bind_param("dds", $walletf, $creditf, $agent_email);
                                    if ($stmt->execute()) {
                                        $stmt->close();
                                        // Insert transaction into agents_transactions
                                        $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,trans_id,user_id,agent_name,email,amount,wallet,credit,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?,?,?)");
                                        if ($stmt) {
                                            $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $user_id,$agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                                            if ($stmt->execute()) {

                                       http_response_code(200);
                                       echo json_encode(['message' => '200']);
                                           
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
                        curl_close($ch);
                    } else {
                        http_response_code(404);
                        echo json_encode(['message' => 'Price not found for the selected package and network']);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Failed to prepare price selection statement: ' . $con->error]);
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