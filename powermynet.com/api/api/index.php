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
           $agent_api = $data['agent_api'];
        $recipient_number = $data['recipient_number'];
        $network = $data['network'];
        $gig = $data['gig'];
        $real_packages = $data['real_package'];
        $reference_id = $data['reference_id'];

        // Date and time generation
        date_default_timezone_set('GMT');
        $order_time = date('H:i:s A');
        $order_date = date('l, F d, Y');
        $real_date = date('Y-m-d');

        $status = "Crediting successful";
        $mode = "API";
        $purpose = "Data Purchase";
        
            $selects = "SELECT * FROM agent_at_activation";
                $sqls = mysqli_query($con, $selects);
                    if ($sqls && mysqli_num_rows($sqls) > 0) {
                    while ($row = mysqli_fetch_array($sqls)) {
                        if($row['status']=="ACTIVATED"){

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
                $agent_tel = $row['telephone'];
                $agent_email = $row['email'];
                $agent_name = $first_name . " " . $last_name;

                $stmt->close();

                // Fetch price from the prices table
                $stmt = $con->prepare("SELECT amount, real_package FROM prices WHERE network = ? AND package = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $network, $gig);
                    $stmt->execute();
                    $stmt->bind_result($price,$real_package);
                    $stmt->fetch();
                    $stmt->close();

                    if ($price !== null) {
                        
                    if ($wallet < $price) {
                            http_response_code(404);
                            echo json_encode(['message' => 'Admin is having low wallet balance']);
                        } else {
                        
                        // Prepare the SOAP request
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
                            http_response_code(500);
                            echo json_encode(["message" => "cURL Error: " . curl_error($ch)]);
                            curl_close($ch);
                            exit;
                        }

                        // Parse the SOAP XML response
                        libxml_use_internal_errors(true);
                        $xml = simplexml_load_string($response);

                        // Check if XML parsing was successful
                        if ($xml === false) {
                            http_response_code(500);
                            echo json_encode(["message" => "Error: Unable to parse SOAP response"]);
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
                                    
                                       // Insert into api_orders
                            $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdsssss", $reference_id,$reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price, $order_time, $order_date, $real_date, $status, $mode);
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

                                    // Database insertion successful, send SMS notification
                                    sendSmsNotification($recipient_number, $gig, $response_msg, true);
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

    
                                } else {
                                    // Database insertion failed
                                    http_response_code(500);
                                    echo json_encode(['message' => 'Order Failed. Failed to insert into database: ' . $insertordersItems->error]);

                                }
                                $insertordersItems->close();
                            } else {
                                http_response_code(500);
                                echo json_encode(['message' => 'Order Failed. Unable to prepare order insertion statement: ' . $con->error]);
                            }
                        } else {
                            // SOAP response indicates failure
                            http_response_code(500);
                            echo json_encode(['message' => "Order Failed: " . htmlspecialchars($response_msg)]);
                        }
                        curl_close($ch);
                        }
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
        
        
                        }
                        else{
                            http_response_code(404);
                            echo json_encode(['message' => 'Account is out of stock']);
                            
                        }
                    }
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
        "senderid" => "CodeCraft",
        "messages" => [
            [
                "recipient" => $recipient_number,
                "message" => "You have received a new bundle allocation of {$gig} GB."
            ]
        ]
    ];

    $ch_sms = curl_init('https://api.moolre.com/open/sms/send');
    curl_setopt($ch_sms, CURLOPT_POST, true);
    curl_setopt($ch_sms, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch_sms, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-VASKEY: REDACTED'
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