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
        
        // Check activation status
$checkStatus = "SELECT status FROM agent_telecel_activation LIMIT 1";
$result = mysqli_query($con, $checkStatus);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if ($row['status'] !== "ACTIVATED") {
        // If not activated, stop the order process
        http_response_code(407);
        echo json_encode(['message' => 'Account is out of stock']);
        exit;
    }
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
                $stmt = $con->prepare("SELECT amount,original FROM prices WHERE network = ? AND package = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $network, $gig);
                    $stmt->execute();
                    $stmt->bind_result($price,$original_price);
                    $stmt->fetch();
                    $stmt->close();
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
            http_response_code(200);
            echo json_encode(['message' => '200']);
    } else {
        $errors['msg'] = "Failed to send SMS! Response: $response";
    }
                                                  
        } else {
            http_response_code(201);
            echo json_encode(['message' => 'Failed to insert transaction: ' . $stmt->error]);
        }
        $stmt->close();
        } else {
            http_response_code(202);
            echo json_encode(['message' => 'Failed to prepare transaction statement: ' . $con->error]);
        }
        } else {
            http_response_code(203);
                    echo json_encode(['message' => 'Failed to update wallet and credit: ' . $stmt->error]);
            }
            } else {
                http_response_code(204);
                echo json_encode(['message' => 'Failed to prepare wallet update statement: ' . $con->error]);
            }
        } else {
            http_response_code(205);
            echo json_encode(['message' => 'Failed to insert order: ' . $stmt->error]);
        }
            } else {
                http_response_code(206);
                echo json_encode(['message' => 'Failed to prepare order insertion statement: ' . $con->error]);
            }

        } else {
            http_response_code(207);
            echo json_encode(['message' => 'Failed to prepare price selection statement: ' . $con->error]);
        }
            } else {
                http_response_code(208);
                echo json_encode(['message' => 'Agent not found']);
            }
        } else {
            http_response_code(209);
            echo json_encode(['message' => 'Failed to prepare agent selection statement: ' . $con->error]);
        }

    } else {
        // Error or unexpected empty table
        http_response_code(500);
        echo json_encode(['message' => 'Activation status not found']);
        exit;
    }
        

  } else {
    http_response_code(501);
    echo json_encode(['message' => 'Invalid JSON']);
}
} else {
    http_response_code(502);
    echo json_encode(['message' => 'Method Not Allowed']);
}

$con->close();
?>