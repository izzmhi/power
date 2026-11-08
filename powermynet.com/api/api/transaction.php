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
        $agent_customer_name = $data['customer_name'];
        $agent_customer_tel = $data['customer_tel'];
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
                $agent_email = $row['email'];
                $agent_tel = $row['telephone'];
                $agent_name = $first_name . " " . $last_name;
                $stmt->close();
                
        $stmt = $con->prepare("SELECT * FROM orders_agent WHERE reference_id = ?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            http_response_code(400); // Sets HTTP response code
             echo json_encode([
                 'status' => http_response_code(), // Fetch the code
                 'message' => 'Order is sent already.'
             ]);
        } else {
                
                
                // Fetch price from the prices table
                $stmt = $con->prepare("SELECT amount, original FROM prices WHERE network = ? AND package = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $network, $gig);
                    $stmt->execute();
                    $stmt->bind_result($price,$original_price);
                    $stmt->fetch();
                    $stmt->close();
                    if ($price !== null) {

//MTN
if($network=="MTN"){
    $selects = "SELECT * FROM agent_mtn_activation";
        $sqls = mysqli_query($con, $selects);
            if ($sqls && mysqli_num_rows($sqls) > 0) {
                while ($row = mysqli_fetch_array($sqls)) {
                    if($row['status']=="ACTIVATED"){
                            
        $selectt = "SELECT * from api_switch";
            $sqll = mysqli_query($con, $selectt);
               $checkk = mysqli_num_rows($sqll) > 0;
                   if ($checkk) {
                       while ($row_switch = mysqli_fetch_array($sqll)) {
                          if($row_switch['mtn']=="API"){
                         $medium = "API";
                        $status = "Pending";
                        
        $super_agent_email = "codecraftnetwork1@gmail.com";
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
                            $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdssssssd", $reference_id,$reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price);
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
                http_response_code(401);
                echo json_encode(['message' => 'Account is out of stock']);
            }
        }
    }
     }
   // MTN Ends
   

  //AT     
elseif ($network == "AT") {
    $selects = "SELECT * FROM agent_at_activation";
    $sqls = mysqli_query($con, $selects);
    if ($sqls && mysqli_num_rows($sqls) > 0) {
        while ($row = mysqli_fetch_array($sqls)) {
            if ($row['status'] == "ACTIVATED") {
                $selectt = "SELECT * FROM api_switch";
                $sqll = mysqli_query($con, $selectt);
                if ($sqll && mysqli_num_rows($sqll) > 0) {
                    while ($row_switch = mysqli_fetch_array($sqll)) {

                        if ($row_switch['at'] == "API") {
                            $medium = "API";
                            $status = "Crediting successful";

                            $soap_request = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
    <soapenv:Header/>
    <soapenv:Body>
        <tem:FlexiIshareBundle>
            <tem:username>CodeCraftNetwork</tem:username>
            <tem:password>REDACTED</tem:password>
            <tem:dealerMsisdn>233276420033</tem:dealerMsisdn>
            <tem:recipientMsisdn>{$recipient_number}</tem:recipientMsisdn>
            <tem:transactionId>{$reference_id}</tem:transactionId>
            <tem:sharedBundle>{$real_package}</tem:sharedBundle>
        </tem:FlexiIshareBundle>
    </soapenv:Body>
</soapenv:Envelope>
XML;

                            $url = "http://41.215.168.146:443/FlexiShareBundles.asmx";
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Content-Type: text/xml; charset=utf-8',
                                'SOAPAction: "http://tempuri.org/FlexiIshareBundle"'
                            ]);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
                            $response = curl_exec($ch);

                            if (curl_errno($ch)) {
                                curl_close($ch);
                                http_response_code(500);
                                echo json_encode(["message" => "cURL Error: " . curl_error($ch)]);
                                exit;
                            }

                            libxml_use_internal_errors(true);
                            $xml = simplexml_load_string($response);
                            curl_close($ch);

                            if ($xml === false) {
                                http_response_code(500);
                                echo json_encode(["message" => "SOAP parse error"]);
                                exit;
                            }

                            $namespace = "http://tempuri.org/";
                            $body = $xml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body;
                            $flexi_response = $body->children($namespace)->FlexiIshareBundleResponse->FlexiIshareBundleResult->ApiResponse;

                            $response_code = (string)$flexi_response->ResponseCode;
                            $response_msg  = (string)$flexi_response->ResponseMsg;

                            if ($response_code == '200') {
                                // Insert orders_at
                                $insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig, real_package, order_time, order_date, real_date, order_status, error_code, message, client_name,client_email,client_customer_name,client_customer_tel) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                if ($insertordersItems) {
                                    $insertordersItems->bind_param("ssssssssssssss", $reference_id, $recipient_number, $gig, $real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_name, $agent_email, $agent_customer_name, $agent_customer_tel);
                                    if ($insertordersItems->execute()) {
                                        $insertordersItems->close();

                                        // Insert orders_agent
                                        $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                                        if ($stmt) {
                                            $stmt->bind_param("sssssssssdssssssd", $reference_id, $reference_id, $user_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode, $account_type, $original_price);
                                            if ($stmt->execute()) {
                                                $stmt->close();
                                                $walletf = $wallet - $price;
                                                $creditf = $credit - $price;

                                                // Update agent wallet
                                                $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE email = ?");
                                                if ($stmt) {
                                                    $stmt->bind_param("dds", $walletf, $creditf, $agent_email);
                                                    if ($stmt->execute()) {
                                                        $stmt->close();

                                                        // Insert transaction
                                                        $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,trans_id,user_id,agent_name,email,amount,wallet,credit,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?,?,?)");
                                                        if ($stmt) {
                                                            $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $user_id, $agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                                                            if ($stmt->execute()) {
                                                                // Success!
                                                                sendSmsNotification($recipient_number, $gig, $response_msg, true);
                                                                http_response_code(200);
                                                                echo json_encode(['status' => 200, 'message' => 'Successful']);
                                                                exit;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        http_response_code(500);
                                        echo json_encode(['message' => 'Failed to insert into orders_at: ' . $insertordersItems->error]);
                                        exit;
                                    }
                                } else {
                                    http_response_code(500);
                                    echo json_encode(['message' => 'Failed to prepare orders_at statement: ' . $con->error]);
                                    exit;
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode(['message' => "API returned error: $response_msg"]);
                                exit;
                            }
                        }

                        elseif ($row_switch['at'] == "EXCEL") {
                            $medium = "Excel";
                            $status = "Pending";

                            $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdssssssd", $reference_id, $reference_id, $user_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode, $account_type, $original_price);
                                if ($stmt->execute()) {
                                    $stmt->close();
                                    $walletf = $wallet - $price;
                                    $creditf = $credit - $price;

                                    $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE email = ?");
                                    if ($stmt) {
                                        $stmt->bind_param("dds", $walletf, $creditf, $agent_email);
                                        if ($stmt->execute()) {
                                            $stmt->close();
                                            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,trans_id,user_id,agent_name,email,amount,wallet,credit,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?,?,?)");
                                            if ($stmt) {
                                                $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $user_id, $agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                                                if ($stmt->execute()) {
                                                    http_response_code(200);
                                                    echo json_encode(['status' => 200, 'message' => 'Successful']);
                                                    exit;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            http_response_code(500);
                            echo json_encode(['message' => 'EXCEL processing failed']);
                            exit;
                        }
                    }
                }
            } else {
                http_response_code(403);
                echo json_encode(['message' => 'AT not activated for this agent']);
                exit;
            }
        }
    }
}

       
       //TELECEL
//TELECEL
elseif($network=="TELECEL"){
    $networks = "Telecel";
    $selects = "SELECT * FROM agent_telecel_activation";
        $sqls = mysqli_query($con, $selects);
            if ($sqls && mysqli_num_rows($sqls) > 0) {
                while ($row = mysqli_fetch_array($sqls)) {
                    if($row['status']=="ACTIVATED"){
                         $status = "Pending";
                         $medium = "API";
                        $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original,customer_email) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdssssssds", $reference_id,$reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$networks,$gig,$price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price,$client_email);
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
    
          	// Send SMS
    $smsData = [
        "type" => 1,
        "senderid" => "CodeCraft",
        "messages" => [
            [
                "recipient" => $recipient_number,
                "message" => "Order for {$gig} GB has been placed. You will receive it shortly."
            ]
        ]
    ];
    $ch = curl_init('https://api.moolre.com/open/sms/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-VASKEY: REDACTED'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        // Log or process successful response
            http_response_code(200); // Sets HTTP response code
            echo json_encode([
                'status' => http_response_code(), // Fetch the code
                'message' => 'Successful'
            ]);
            exit();
    } else {
        $errors['msg'] = "Failed to send SMS! Response: $response";
    }
                                                  
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
                    else{
                    http_response_code(401);
                    echo json_encode(['message' => 'Account is out of stock']);
                    }
                }
            }    
       }
       
       else{
            http_response_code(555);
            echo json_encode(['message' => 'Network Not found']);
       }
    }
 
                } else {
                    http_response_code(401);
                    echo json_encode(['message' => 'Price not found for a package and network: ' . $con->error]);
                }
                
        }
    
            } else {
                http_response_code(401);
                echo json_encode(['message' => 'Agent not found']);
            }
        } else {
            http_response_code(600);
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