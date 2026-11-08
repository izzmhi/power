<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database/dbconfig.php';
require_once 'sms_config.php'; // Include the config file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Extract data
        $agent_api = $data['agent_api'];
        $recipient_number = $data['recipient_number'];
        $gig = $data['gig'];
        $reference_id = $data['reference_id'];
        $client_email = $data['client_email'];
         $real_package = $gig."000";
        // Date and time generation
        date_default_timezone_set('GMT');
        $order_time = date('H:i:s A');
        $order_date = date('l, F d, Y');
        $real_date = date('Y-m-d');
        $mode = "API";
        $purpose = "Data Purchase";
        $account_type = "Agent";
        $customer_email="NONE";
        $network = "ATBIG";
         
         // Generate internal reference
$first_five_chars = substr($reference_id, 0, 5);
$last_2_chars = substr($reference_id, -2);
$rand1 = bin2hex(random_bytes(8));
$rand2 = bin2hex(random_bytes(10));
$reference = $first_five_chars . $last_2_chars . $rand1;
$order_id = $rand2.$first_five_chars . $last_2_chars;
         
        // Check if the agent exists
        $stmt = $con->prepare("SELECT * FROM agents WHERE api = ?");
        if ($stmt) {
            $stmt->bind_param("s", $agent_api);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $wallet = $row['wallet'];
                $credit = $row['credit'];
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
                $user_id = $row['user_id'];
                $_SESSION['user_id'] = $user_id;
                $agent_email = $row['email'];
                $agent_tel = $row['telephone'];
                $agent_name = $first_name . " " . $last_name;
                $stmt->close();
                
        $stmt = $con->prepare("SELECT * FROM special_offers_orders WHERE reference_id = ?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            http_response_code(400); // Sets HTTP response code
            echo json_encode([
            'status' => http_response_code(), // Fetch the code
            'message' => 'Order is sent already.'
            ]);
        } else {
                $target = "Agent";
                // Fetch price from the prices table
                $stmt = $con->prepare("SELECT amount, original FROM special_offers WHERE package = ? AND target = ? ");
                if ($stmt) {
                    $stmt->bind_param("ss", $gig, $target);
                    $stmt->execute();
                    $stmt->bind_result($price,$original_price);
                    $stmt->fetch();
                    $stmt->close();
                    if ($price !== null) {
                        if ($wallet < $price) {
                            http_response_code(401); // Sets HTTP response code
                                echo json_encode([
                                    'status' => http_response_code(), // Fetch the code
                                    'message' => 'Admin is having low wallet balance'
                                    ]);
                                exit();
                        } else {
   
                       $medium = "API";
                        $status = "Pending";

    $selects = "SELECT * FROM special_all_activation";
        $sqls = mysqli_query($con, $selects);
            if ($sqls && mysqli_num_rows($sqls) > 0) {
                while ($row = mysqli_fetch_array($sqls)) {
                    if($row['status']=="ACTIVATED"){

                            // Insert into api_orders
                            $stmt = $con->prepare("INSERT INTO special_offers_orders(order_id, reference, reference_id, agent_id, agent_name, agent_tel, agent_email, customer_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode, account_type, original,client_email) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssssdssssssds", $order_id, $reference, $reference_id, $user_id, $agent_name, $agent_tel, $agent_email, $customer_email, $recipient_number, $network, $gig, $price, $order_time, $order_date,$real_date,$status,$mode,$account_type,$original_price,$client_email);
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
                                            http_response_code(200); // Sets HTTP response code
                                             echo json_encode([
                                                 'status' => http_response_code(), // Fetch the code
                                                 'message' => 'Successful'
                                             ]);
                                             exit();

                                                } else {
                                                    http_response_code(500);
                                                    echo json_encode(['message' => 'Failed to insert transaction: ' . $stmt->error]);
                                                }
                                                $stmt->close();
                                            } else {
                                                http_response_code(501);
                                                echo json_encode(['message' => 'Failed to prepare transaction statement: ' . $con->error]);
                                            }
                                        } else {
                                            http_response_code(502);
                                            echo json_encode(['message' => 'Failed to update wallet and credit: ' . $stmt->error]);
                                        }
                                    } else {
                                        http_response_code(503);
                                        echo json_encode(['message' => 'Failed to prepare wallet update statement: ' . $con->error]);
                                    }
                                } else {
                                    http_response_code(508);
                                    echo json_encode(['message' => 'Failed to insert order: ' . $stmt->error]);
                                }
                            } else {
                                http_response_code(505);
                                echo json_encode(['message' => 'Failed to prepare order insertion statement: ' . $con->error]);
                            }

                    }
                    else{
                        http_response_code(402);
                        echo json_encode(['message' => 'Account is out of stock']);
                    }
                }
            }
            }              
          
        } else {
            http_response_code(403);
            echo json_encode(['message' => 'Price not found for a package and network: ' . $con->error]);
        }
        }
        }
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Agent not found']);
    }
    } else {
        http_response_code(506);
        echo json_encode(['message' => 'Failed to prepare agent selection statement: ' . $con->error]);
    }
    } else {
        http_response_code(507);
        echo json_encode(['message' => 'Invalid JSON']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
 
// Close the database connection
$con->close();

// Function to send SMS
function sendSmsNotification($recipient_number, $gig, $message, $isSuccess) {
    $status = $isSuccess ? "Crediting successful" : "Crediting failed";
   $smsData = [
        "type" => 1,
        "senderid" => SMS_SENDER_ID,
        "messages" => [
            [
                "recipient" => $recipient_number,
                "message" => "You have received a new bundle allocation of {$gig} GB."
            ]
        ]
    ];

    $ch_sms = curl_init(SMS_API_URL);
    curl_setopt($ch_sms, CURLOPT_POST, true);
    curl_setopt($ch_sms, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch_sms, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-VASKEY: ' .SMS_API_KEY
    ]);
    curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);

    $sms_response = curl_exec($ch_sms);
    $sms_http_code = curl_getinfo($ch_sms, CURLINFO_HTTP_CODE);
    curl_close($ch_sms);

    if ($sms_http_code != 200) {
        error_log("Failed to send SMS: " . $sms_response);
    }
}

?>