<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
       $_SESSION['wallet'] = $rows['wallet'];
    }
}
$errors = array();
$success = array();

if (isset($_POST['submit'])) {
    require '../database/dbconfig.php';

    $agent_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $last_name = $_SESSION['last_name'];
    $agent_name = $first_name . " " . $last_name;
    $agent_email = $_SESSION['email'];
    $agent_tel = $_SESSION['telephone'];

    // Arrays from form submission
    $gigs = $_POST['gig'];
    $prices = $_POST['price'];
    $originals = $_POST['original'];
    $recipient_numbers = $_POST['beneficiary'];
    $selection = $_POST['selection'];
    $total = $_POST['total'];

    $mode = "Wallet";
    $purpose = "Order Payment";
    $account_type = "Agent";

    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');

    // Validate recipient numbers
    foreach ($recipient_numbers as $number) {
        if (strlen(trim($number)) !== 10) {
            unset($_SESSION['excel_data']);
            header("Location: bulk.php?error=Every recipient number must be exactly 10 characters long.");
            exit();
        }
    }

if ($selection == "MTN") {
    $status = "Pending";
    $errors = [];
    $success_messages = [];
    $network = "MTN";

    // Process Orders
    for ($i = 0; $i < count($recipient_numbers); $i++) {
        $recipient_number = mysqli_real_escape_string($con, $recipient_numbers[$i]);
        $gig = mysqli_real_escape_string($con, $gigs[$i]);
        $price = mysqli_real_escape_string($con, $prices[$i]);
        $original = mysqli_real_escape_string($con, $originals[$i]);

        // Generate unique IDs
        $reference_id = substr($recipient_number, 0, 5) . date('His') . bin2hex(random_bytes(7));
        $order_id = date('ymd') . bin2hex(random_bytes(2)) . date('his') . bin2hex(random_bytes(5));

        // Fetch package details
        $stmt = $con->prepare("SELECT original, real_package FROM prices WHERE network = ? AND package = ?");
        $stmt->bind_param("ss", $network, $gig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Price not found for $gig on $network.";
            $stmt->close();
            continue;
        }
        $row = $result->fetch_assoc();
        $original_price = $row['original'];
        $real_package = $row['real_package'];
        $stmt->close();

        // Fetch agent details
        $stmt = $con->prepare("SELECT wallet, credit FROM agents WHERE user_id = ?");
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Agent not found.";
            $stmt->close();
            continue;
        }
        $agent = $result->fetch_assoc();
        $wallet = $agent['wallet'];
        $credit = $agent['credit'];
        $stmt->close();

        if ($wallet < $price) {
            $errors[] = "Insufficient balance for $recipient_number.";
            continue;
        }

        // Fetch API switch
        $stmt = $con->prepare("SELECT mtn FROM api_switch");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Unable to fetch API switch.";
            $stmt->close();
            continue;
        }
        $row_switch = $result->fetch_assoc();
        $stmt->close();

        if ($row_switch['mtn'] == "API") {
            $medium = "API";
            $super_agent_email = "codecraftnetwork1@gmail.com";

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
                $errors[] = "cURL error: " . curl_error($ch);
            } else {
                $response = json_decode($response, true);
                if (isset($response['message'])) {
                    $success_messages[] = "Order successful for $recipient_number.";
                }
            }
            curl_close($ch);
        } else {
            $medium = "Excel";
        }

        // Insert order into database
        $stmt = $con->prepare("INSERT INTO orders_agent 
            (order_id, reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) 
            VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssdssssssd", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $medium,$account_type,$original_price);

        if ($stmt->execute()) {
            // Update agent balance
            $wallet -= $price;
            $credit -= $price;
            $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $wallet, $credit, $agent_id);
            $stmt->execute();

            // Insert transaction record
            $stmt = $con->prepare("INSERT INTO agents_transactions 
                (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $wallet, $credit, $real_date, $purpose);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'mail.codecraftnetwork.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'admin@codecraftnetwork.com';
                $mail->Password = 'REDACTED';
                $mail->SMTPSecure = "ssl";
                $mail->Port = 465;

                // Recipients
                $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                $mail->addAddress($agent_email);
                $mail->isHTML(true);
                $mail->Subject = "Data Purchase | Using Wallet";
                $mail->Body = "<p>Dear Agent,</p><p>You bought $network $gig Gig bundle worth GHS$price for $recipient_number. Your Wallet Balance is GHS$wallet.</p>";
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Email not sent to $agent_email.";
            }

            $success_messages[] = "Order successful for $recipient_number.";
        } else {
            $errors[] = "Database error for $recipient_number.";
        }
    }

    // Store messages in session
    unset($_SESSION['excel_data']);
    $_SESSION['errors'] = $errors;
    $_SESSION['success_messages'] = $success_messages;

    // Redirect after all processing is complete
    if (!empty($errors)) {
        header("Location: bulk.php"); // Redirect errors to bulk.php
    } else {
        header("Location: api_orders.php?success=Orders placed successfully"); // Redirect success to orders.php
    }
    exit();
}

//AT
if ($selection == "AT") {
    $status = "Crediting Successful";
    $errors = [];
    $success_messages = [];
    $network = "AT";

    // Process Orders
    for ($i = 0; $i < count($recipient_numbers); $i++) {
        $recipient_number = mysqli_real_escape_string($con, $recipient_numbers[$i]);
        $gig = mysqli_real_escape_string($con, $gigs[$i]);
        $price = mysqli_real_escape_string($con, $prices[$i]);
        $original = mysqli_real_escape_string($con, $originals[$i]);

        // Generate unique IDs
        $reference_id = substr($recipient_number, 0, 5) . date('His') . bin2hex(random_bytes(7));
        $order_id = date('ymd') . bin2hex(random_bytes(2)) . date('his') . bin2hex(random_bytes(5));

        // Fetch package details
        $stmt = $con->prepare("SELECT original, real_package FROM prices WHERE network = ? AND package = ?");
        $stmt->bind_param("ss", $network, $gig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Price not found for $gig on $network.";
            $stmt->close();
            continue;
        }
        $row = $result->fetch_assoc();
        $original_price = $row['original'];
        $real_package = $row['real_package'];
        $stmt->close();

        // Fetch agent details
        $stmt = $con->prepare("SELECT wallet, credit FROM agents WHERE user_id = ?");
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Agent not found.";
            $stmt->close();
            continue;
        }
        $agent = $result->fetch_assoc();
        $wallet = $agent['wallet'];
        $credit = $agent['credit'];
        $stmt->close();

        if ($wallet < $price) {
            $errors[] = "Insufficient balance for $recipient_number.";
            continue;
        }

        // Fetch API switch
        $stmt = $con->prepare("SELECT at FROM api_switch");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Unable to fetch API switch.";
            $stmt->close();
            continue;
        }
        $row_switch = $result->fetch_assoc();
        $stmt->close();

        if ($row_switch['at'] == "API") {
            $medium = "API";
            
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

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://tempuri.org/FlexiIshareBundle"'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
            $response = curl_exec($ch);
            curl_close($ch);

            // Parse the response
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            if ($xml === false) {
                $errors[] = "Order Failed. Unable to parse SOAP response.";
                continue;
            }

            $namespace = "http://tempuri.org/";
            $body = $xml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body;
            $flexi_response = $body->children($namespace)->FlexiIshareBundleResponse->FlexiIshareBundleResult->ApiResponse;
            $response_code = (string)$flexi_response->ResponseCode;
            $response_msg = (string)$flexi_response->ResponseMsg;

            if ($response_code != '200') {
                $errors[] = "Order Failed: " . htmlspecialchars($response_msg);
                continue;
            }
        } else {
            $medium = "Excel";
        }

        // Insert order into database
        $stmt = $con->prepare("INSERT INTO orders_at 
            (reference_id, beneficiary, gig, real_package, order_time, order_date, real_date, order_status, error_code, message, client_email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $reference_id, $recipient_number, $gig, $real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_email);

        if ($stmt->execute()) {
                        $stmt->close();

            // Send SMS
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
            curl_close($ch_sms);

            if ($sms_response === false) {
                $errors[] = "Failed to send SMS for $recipient_number.";
            }
            
            $success_messages[] = "Order successful for $recipient_number.";
            
                    // Insert order into database
        $stmt = $con->prepare("INSERT INTO orders_agent 
            (order_id, reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) 
            VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssdssssssd", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price);

        if ($stmt->execute()) {
            // Update agent balance
            $wallet -= $price;
            $credit -= $price;
            $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $wallet, $credit, $agent_id);
            $stmt->execute();

            // Insert transaction record
            $stmt = $con->prepare("INSERT INTO agents_transactions 
                (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $wallet, $credit, $real_date, $purpose);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                $mail->addAddress($agent_email);
                $mail->isHTML(true);
                $mail->Subject = "Data Purchase | Using Wallet";
                $mail->Body = "<p>Dear Agent,</p><p>You bought $network $gig Gig bundle worth GHS$price for $recipient_number. Your Wallet Balance is GHS$wallet.</p>";
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Email not sent to $agent_email.";
            }

            $success_messages[] = "Order successful for $recipient_number.";
        } else {
            $errors[] = "Database error for $recipient_number.";
        }
    
    }

    }

    // Store messages in session and redirect
    unset($_SESSION['excel_data']);
    $_SESSION['errors'] = $errors;
    $_SESSION['success_messages'] = $success_messages;

    if (!empty($errors)) {
        unset($_SESSION['excel_data']);
        header("Location: bulk.php"); // Redirect errors to bulk.php
    } else {
        unset($_SESSION['excel_data']);
        header("Location: orders.php?success=Orders placed successfully"); // Redirect success to orders.php
    }
    exit();
}

//ATBIG
if ($selection == "TELECEL") {
    $status = "Pending";
    $errors = [];
    $success_messages = [];
    $network = "Telecel";
    $target = "Agent";

    // Process Orders
    for ($i = 0; $i < count($recipient_numbers); $i++) {
        $recipient_number = mysqli_real_escape_string($con, $recipient_numbers[$i]);
        $gig = mysqli_real_escape_string($con, $gigs[$i]);
        $price = mysqli_real_escape_string($con, $prices[$i]);
        $original = mysqli_real_escape_string($con, $originals[$i]);

        // Generate unique IDs
        $reference_id = substr($recipient_number, 0, 5) . date('His') . bin2hex(random_bytes(7));
        $order_id = date('ymd') . bin2hex(random_bytes(2)) . date('his') . bin2hex(random_bytes(5));

        // Fetch package details
        $stmt = $con->prepare("SELECT original, real_package FROM special WHERE network = ? AND package = ? AND target = ?");
        $stmt->bind_param("sss", $network, $gig, $target);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Price not found for $gig on $network.";
            continue;
        }
        $row = $result->fetch_assoc();
        $original_price = $row['original'];
        $real_package = $row['real_package'];
        $stmt->close();

        // Fetch agent details
        $stmt = $con->prepare("SELECT wallet, credit FROM agents WHERE user_id = ?");
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Agent not found.";
            continue;
        }
        $agent = $result->fetch_assoc();
        $wallet = $agent['wallet'];
        $credit = $agent['credit'];
        $stmt->close();

        if ($wallet < $price) {
            $errors[] = "Insufficient balance for $recipient_number.";
            continue;
        }

        // Fetch API switch
        $stmt = $con->prepare("SELECT telecel FROM api_switch");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Unable to fetch API switch.";
            continue;
        }
        $row_switch = $result->fetch_assoc();
        $stmt->close();

        if ($row_switch['telecel'] == "API") {
            $medium = "API";
   

        } else {
            $medium = "Excel";
        }

        // Insert order into database
        $stmt = $con->prepare("INSERT INTO orders_agent 
            (order_id, reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) 
            VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssdssssssd", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price);

        if ($stmt->execute()) {
            // Update agent balance
            $wallet -= $price;
            $credit -= $price;
            $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $wallet, $credit, $agent_id);
            $stmt->execute();

            // Insert transaction record
            $stmt = $con->prepare("INSERT INTO agents_transactions 
                (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $wallet, $credit, $real_date, $purpose);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                $mail->addAddress($agent_email);
                $mail->isHTML(true);
                $mail->Subject = "Data Purchase | Using Wallet";
                $mail->Body = "<p>Dear Agent,</p><p>You bought $network $gig Gig bundle worth GHS$price for $recipient_number. Your Wallet Balance is GHS$wallet.</p>";
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Email not sent to $agent_email.";
            }

            $success_messages[] = "Order successful for $recipient_number.";
        } else {
            $errors[] = "Database error for $recipient_number.";
        }
    }

    // Store messages in session and redirect
$_SESSION['errors'] = $errors;
$_SESSION['success_messages'] = $success_messages;

if (!empty($errors)) {
    unset($_SESSION['excel_data']);
    header("Location: bulk.php"); // Redirect errors to bulk.php
} else {
    unset($_SESSION['excel_data']);
    header("Location: orders.php?success=Orders placed successfully"); // Redirect success to orders.php
}
exit();
}

//ATBIG
if ($selection == "ATBIG") {
    $status = "Pending";
    $errors = [];
    $success_messages = [];
    $network = "AT";
    $target = "Agent";

    // Process Orders
    for ($i = 0; $i < count($recipient_numbers); $i++) {
        $recipient_number = mysqli_real_escape_string($con, $recipient_numbers[$i]);
        $gig = mysqli_real_escape_string($con, $gigs[$i]);
        $price = mysqli_real_escape_string($con, $prices[$i]);
        $original = mysqli_real_escape_string($con, $originals[$i]);

        // Generate unique IDs
        $reference_id = substr($recipient_number, 0, 5) . date('His') . bin2hex(random_bytes(7));
        $order_id = date('ymd') . bin2hex(random_bytes(2)) . date('his') . bin2hex(random_bytes(5));

        // Fetch package details
        $stmt = $con->prepare("SELECT original, real_package FROM special_offers WHERE network = ? AND package = ? AND target = ?");
        $stmt->bind_param("sss", $network, $gig, $target);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Price not found for $gig on $network.";
            continue;
        }
        $row = $result->fetch_assoc();
        $original_price = $row['original'];
        $real_package = $row['real_package'];
        $stmt->close();

        // Fetch agent details
        $stmt = $con->prepare("SELECT wallet, credit FROM agents WHERE user_id = ?");
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Agent not found.";
            continue;
        }
        $agent = $result->fetch_assoc();
        $wallet = $agent['wallet'];
        $credit = $agent['credit'];
        $stmt->close();

        if ($wallet < $price) {
            $errors[] = "Insufficient balance for $recipient_number.";
            continue;
        }

        // Fetch API switch
        $stmt = $con->prepare("SELECT at_big_time FROM api_switch");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Unable to fetch API switch.";
            continue;
        }
        $row_switch = $result->fetch_assoc();
        $stmt->close();

        if ($row_switch['at_big_time'] == "API") {
            $medium = "API";
   

        } else {
            $medium = "Excel";
        }

        // Insert order into database
        $stmt = $con->prepare("INSERT INTO orders_agent 
            (order_id, reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode,account_type,original) 
            VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssdssssssd", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price);

        if ($stmt->execute()) {
            // Update agent balance
            $wallet -= $price;
            $credit -= $price;
            $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $wallet, $credit, $agent_id);
            $stmt->execute();

            // Insert transaction record
            $stmt = $con->prepare("INSERT INTO agents_transactions 
                (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $wallet, $credit, $real_date, $purpose);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                $mail->addAddress($agent_email);
                $mail->isHTML(true);
                $mail->Subject = "Data Purchase | Using Wallet";
                $mail->Body = "<p>Dear Agent,</p><p>You bought $network $gig Gig bundle worth GHS$price for $recipient_number. Your Wallet Balance is GHS$wallet.</p>";
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Email not sent to $agent_email.";
            }

            $success_messages[] = "Order successful for $recipient_number.";
        } else {
            $errors[] = "Database error for $recipient_number.";
        }
    }

    // Store messages in session and redirect
$_SESSION['errors'] = $errors;
$_SESSION['success_messages'] = $success_messages;

if (!empty($errors)) {
    unset($_SESSION['excel_data']);
    header("Location: bulk.php"); // Redirect errors to bulk.php
} else {
    unset($_SESSION['excel_data']);
    header("Location: special_orders.php?success=Orders placed successfully"); // Redirect success to orders.php
}
exit();
}

    
  
    unset($_SESSION['excel_data']);
    header("Location: orders.php?success=Orders placed successfully");
    exit();
}

$con->close();
?>