<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database/dbconfig.php';
$config = require 'at_config.php';

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
    
    $required_fields = ['agent_api', 'recipient_number', 'network', 'gig', 'reference_id'];
    foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
            echo json_encode([
            'message' => "Missing required field: $field", 'code',
            'code' => 400
        ]);
        exit;
    }
    }
    
    // Date and time generation
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');
    $status = "Crediting successful";
    $trans_desc = "ORDER";
    $account_type = "Agent";
    $real_package = $gig."000";
    // Check activation status
    $checkStatus = "SELECT status FROM agent_at_activation LIMIT 1";
    $result = mysqli_query($con, $checkStatus);

    if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row['status'] !== "ACTIVATED") {
        // If not activated, stop the order process
            http_response_code(404);
            echo json_encode([
            'message' => 'Order Failed. Account is out of stock',
            'code' => 404
        ]);
        exit;
    }
    else{
        
    // Check if the agent exists
    $stmt = $con->prepare("SELECT * FROM agents WHERE api = ?");
    if ($stmt) {
        $stmt->bind_param("s", $agent_api);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        
    $console_status = "ACTIVE";
    $select = "SELECT * FROM consoles WHERE agent_id = ? AND console_status = ?";
    if ($stmt = $con->prepare($select)) {
    $stmt->bind_param("ss", $user_id, $console_status);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['package'] < $gig) {
        http_response_code(404);
        echo json_encode([
            'message' => 'Order Failed. Admin do not have enough balance',
            'code' => 404
        ]);
        exit;
        
        } else {
        $agent_name = $row['agent_name'];
        $user_id = $row['agent_id'];
        $agent_email = $row['agent_email'];
        $agent_tel = $row['agent_tel'];
        $package = $row['package'];
        $used_package = $row['used_package'];
        $console_real_package = $row['real_package'];
        $console_id = $row['console_id'];
        $console_name = $row['console_name'];
                    
                // Prepare the SOAP request
$soap_request = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
    <soapenv:Header/>
    <soapenv:Body>
        <tem:FlexiIshareBundle>
            <tem:username>{$config['username']}</tem:username>
            <tem:password>{$config['password']}</tem:password>
            <tem:dealerMsisdn>{$config['dealerMsisdn']}</tem:dealerMsisdn>
            <tem:recipientMsisdn>{$recipient_number}</tem:recipientMsisdn>
            <tem:transactionId>{$reference_id}</tem:transactionId>
            <tem:sharedBundle>{$real_package}</tem:sharedBundle>
        </tem:FlexiIshareBundle>
    </soapenv:Body>
</soapenv:Envelope>
XML;
                        $url = "http://41.215.168.146:443/FlexiShareBundles.asmx"; // API endpoint

                        // Initialize cURL
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: text/xml; charset=utf-8',
                            'SOAPAction: "http://tempuri.org/FlexiIshareBundle"'
                        ]);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
                        // Execute the cURL request and capture the response
                        $response = curl_exec($ch);
                        // Check for cURL errors
                        if (curl_errno($ch)) {
                            http_response_code(404);
                                echo json_encode([
                                    'message' => 'Order Failed. SOAP cURL Error: ',
                                    'code' => 404
                                ]);
                                exit;
                            curl_close($ch);
                        }
                        // Parse the SOAP XML response
                        libxml_use_internal_errors(true);
                        $xml = simplexml_load_string($response);
                        // Check if XML parsing was successful
                        if ($xml === false) {
                            http_response_code(404);
                                echo json_encode([
                                    'message' => 'Order Failed. Error: Unable to parse SOAP response',
                                    'code' => 404
                            ]);
                            curl_close($ch);
                            exit;
                        }
                        // Extract the error code and message from the response
                        $namespace = "http://tempuri.org/";  // SOAP namespace
                        $body = $xml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body;
                        $flexi_response = $body->children($namespace)->FlexiIshareBundleResponse->FlexiIshareBundleResult->ApiResponse;
                        $response_code = (string)$flexi_response->ResponseCode; // Extract ResponseCode
                        $response_msg = (string)$flexi_response->ResponseMsg;   // Extract ResponseMsg
                        // Check the response code
                        if ($response_code == '200') {
                            // Insert the order into the database
                            if ($insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig, real_package, order_time, order_date, real_date, order_status, error_code, message, client_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                                $insertordersItems->bind_param("sssssssssss", $reference_id, $recipient_number, $gig, $real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_email);
                                if ($insertordersItems->execute()) {
                                    
// Insert into console_orders table
$stmt = $con->prepare("INSERT INTO console_orders (order_id, console_id, console_name, agent_id, agent_name, agent_email, agent_tel, recipient, package, order_time, order_date, real_date, order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("sssssssssssss", $reference_id, $console_id, $console_name, $user_id, $agent_name, $agent_email, $agent_tel, $recipient_number, $gig, $order_time, $order_date, $real_date, $status);

if ($stmt->execute()) {
    // Update package balance
    $current_package = $package - $gig;
    $current_used_package = $used_package + $gig;
    $current_real_package = $console_real_package - $real_package;
    $stmt = $con->prepare("UPDATE consoles SET package = ?, used_package = ?, real_package = ? WHERE agent_id = ?");
    $stmt->bind_param("ssss", $current_package, $current_used_package, $current_real_package, $user_id);

    if ($stmt->execute()) {
        // Insert into console_transactions table
        $stmt = $con->prepare("INSERT INTO console_transactions (trans_id, console_id, console_name, agent_id, agent_name, agent_email, agent_tel, package, trans_date, trans_desc) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssss", $reference_id, $console_id, $console_name, $user_id, $agent_name, $agent_email, $agent_tel, $gig, $real_date, $trans_desc);

        if ($stmt->execute()) {
            // ✅ Database insertion successful
            http_response_code(200);
            echo json_encode([
                'message' => 'Crediting successful',
                'code' => 200
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'message' => 'Order Failed. Failed to insert transaction:',
                'code' => 500
            ]);
        }
    } else {
        http_response_code(501);
        echo json_encode([
            'message' => 'Order Failed. Failed to update wallet and credit:',
            'code' => 501
        ]);
    }
} else {
    http_response_code(502);
    echo json_encode([
        'message' => 'Order Failed. Failed to insert order:',
        'code' => 502
    ]);
}


                                } else {
                                    // Database insertion failed
                                    http_response_code(404);
                                    echo json_encode([
                                        'message' => 'Order Failed. Failed to insert into database',
                                        'code' => 404
                                    ]);
                                }
                                $insertordersItems->close();
                            } else {
                                http_response_code(404);
                                echo json_encode([
                                    'message' => 'Order Failed. Unable to prepare order insertion statement',
                                    'code' => 404
                                ]);
                            }
                        } else {
                            // SOAP response indicates failure
                            http_response_code(404);
                            echo json_encode([
                                'message' => 'Order Failed. SOAP response indicates failure',
                                'code' => 404
                            ]);
                        }
                        curl_close($ch);
                    } 
            }
            else {
            http_response_code(404);
            echo json_encode([
            'message' => 'Order Failed. Admin Account is not Active',
            'code' => 404
            ]);
                }
            }
            else {
                http_response_code(400);
                echo json_encode([
                'message' => 'Order Failed. Admin Account Active Database query failed',
                'code' => 400
            ]);
            }
                
        } else {
            http_response_code(404);
            echo json_encode([
                'message' => 'Order Failed. Admin Account does not exist',
                'code' => 404
            ]);
            }
              }
            else {
            http_response_code(400);
            echo json_encode([
                'message' => 'Order Failed. Failed to prepare admin account statement',
                'code' => 400
            ]);
            }
    }

    } else {
        http_response_code(404);
        echo json_encode([
            'message' => 'Order Failed. Activation status not found',
            'code' => 404
        ]);
    }
        
    } else {
    http_response_code(503);
        echo json_encode([
            'message' => 'Order Failed. Invalid JSON',
            'code' => 503
        ]);
    }
} else {
    http_response_code(503);
    echo json_encode([
        'message' => 'Order Failed. Method Not Allowed',
        'code' => 503
    ]);
}

// Close the database connection
$con->close();

?>