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
        $network = $data['network'];
        $gig = $data['gig'];
        $reference_id = $data['reference_id'];
        // Date and time generation
        date_default_timezone_set('GMT');
        $order_time = date('H:i:s A');
        $order_date = date('l, F d, Y');
        $real_date = date('Y-m-d');
        $mode = "API";
        $purpose = "Data Purchase";
         $account_type = "Agent";
         
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
                // Fetch price from the prices table
                $stmt = $con->prepare("SELECT amount, real_package,original FROM prices WHERE network = ? AND package = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $network, $gig);
                    $stmt->execute();
                    $stmt->bind_result($price,$real_package,$original_price);
                    $stmt->fetch();
                    $stmt->close();
                    if ($price !== null) {
                        if ($wallet < $price) {
                            http_response_code(402); // Sets HTTP response code
                                echo json_encode([
                                    'status' => http_response_code(), // Fetch the code
                                    'message' => 'Admin is having low wallet balance'
                                    ]);
                                exit();
                        } else {
   
//MTN
//MTN
if($network=="MTN"){
    $selects = "SELECT * FROM agent_mtn_activation";
        $sqls = mysqli_query($con, $selects);
            if ($sqls && mysqli_num_rows($sqls) > 0) {
                while ($row = mysqli_fetch_array($sqls)) {
                    if($row['status']=="ACTIVATED"){
                             $status = "Pending";
                            
        $selectt = "SELECT * from api_switch";
            $sqll = mysqli_query($con, $selectt);
               $checkk = mysqli_num_rows($sqll) > 0;
                   if ($checkk) {
                       while ($row_switch = mysqli_fetch_array($sqll)) {
                          if($row_switch['mtn']=="API"){
                         $medium = "API";
                        $status = "Pending";
                        
              $super_agent_email = "owusuebenezer377@gmail.com";

                // Get the last five characters
        $last_2_chars = substr($recipient_number, -2);
        $first_five_chars = substr($recipient_number, 0, 5);
        $seconds = date('His');
        $reference_id = $first_five_chars.$seconds.$last_2_chars;

            // API data preparation
        $data = [
            'super_agent_email' => $super_agent_email,
            'agent_email' => $agent_email,
            'recipient_number' => $recipient_number,
            'network' => $network,
            'gig' => $gig,
            'price' => $price,
            'reference_id' => $reference_id,
        ];
        $jsonData = json_encode($data);
        require_once __DIR__ . '/../../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.dataworksgh.com/api/index.php', 'mtn');
        
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
                $errors[] = "cURL error: " . curl_error($ch);
                curl_close($ch);
                header("Location: api_orders.php?error=" . urlencode($errors[0]));
                exit();
            } else {
            $response = json_decode($response, true);
            curl_close($ch);

            if (isset($response['message'])) {
                $success_message = $response['message'];
         
                                     // Insert into api_orders
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
                                    http_response_code(504);
                                    echo json_encode(['message' => 'Failed to insert order: ' . $stmt->error]);
                                }
                            } else {
                                http_response_code(505);
                                echo json_encode(['message' => 'Failed to prepare order insertion statement: ' . $con->error]);
                            }
            } else {
                  http_response_code(506);
                echo json_encode(['message' => 'Main MTN API response does not contain a message']);
            }
        }
    }
        //Excel
           elseif($row_switch['mtn']=="EXCEL"){
                   $status = "Pending";
            // Insert order into database
                                    // Insert into api_orders
                            $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdsssss", $reference_id,$reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price, $order_time, $order_date, $real_date, $status, $mode);
                                if ($stmt->execute()) {
                                    $stmt->close();
                                $walletf = $wallet - $price;
                                $creditf = $credit - $price;
                                $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE api = ?");
                                if ($stmt) {
                                    $stmt->bind_param("dds", $walletf, $creditf, $agent_api);
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
                                http_response_code(504);
                                echo json_encode(['message' => 'Failed to insert order: ' . $stmt->error]);
                            }
                        } else {
                            http_response_code(505);
                            echo json_encode(['message' => 'Failed to prepare order insertion statement: ' . $con->error]);
                        }
           }
     }
}
                            
                    }
                    else{
                        http_response_code(400);
                        echo json_encode(['message' => 'Account is out of stock']);
                    }
                }
            }
       }
   // MTN Ends

       else{
            http_response_code(555);
            echo json_encode(['message' => 'Network Not found']);
       }

    }              
}

                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Price not found for a package and network: ' . $con->error]);
                }
            } else {
                http_response_code(402);
                echo json_encode(['message' => 'Agent not found']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to prepare agent selection statement: ' . $con->error]);
        }
   
    } else {
        http_response_code(504);
        echo json_encode(['message' => 'Invalid JSON']);
    }
} else {
    http_response_code(504);
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