<?php
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

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

$config = require 'at_config.php';

// Check if an AJAX request is made
if (isset($_GET['action']) && $_GET['action'] == 'getRemainingData') {
    // Example remaining GB value (you could replace this with real data from a database or session)
    $remainingGB = 51.00; // Replace with dynamic calculation if needed
    // Return JSON response
    echo json_encode(['remainingGB' => $remainingGB]);
    exit; // Stop further script execution for AJAX request
}

include'../../database/dbconfig.php';

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
    }  
}

$errors = array();
$success = array();

if(isset($_POST['send_single'])){
$agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
$last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
$agent_name = $first_name . " " . $last_name;
$agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
$agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$recipient_number = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$console_id = filter_input(INPUT_POST, 'console_id', FILTER_SANITIZE_SPECIAL_CHARS);
$console_name = filter_input(INPUT_POST, 'console_name', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = $gig."000";
$trans_desc = "ORDER";
    if (empty($gig)){
        $errors = "Gig is not selected";
        header("location:index.php?error=$errors");
    }
    if (empty($recipient_number)){
        $errors = "Recipient number is required";
        header("location:index.php?error=$errors");
    }
    if (empty($console_id)){
        $errors = "Console id is not found";
        header("location:index.php?error=$errors");
    }
    if (empty($console_name)){
        $errors = "Console not found";
        header("location:index.php?error=$errors");
    }
    else{
        // Order details
    $status = "Crediting Successful";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');

    $select = "SELECT * FROM consoles WHERE agent_id=?";
    if ($stmt = $con->prepare($select)) {
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['package'] < $gigs) {
                $errors["Purchase"] = 'Failed to send. You do not have enough balance.';
                    $error = "Failed to send. You do not have enough balance.";
                   header("Location: index.php?error=" . urlencode($error));
                   exit;
                } else {
                    $package = $row['package'];
                    $used_package = $row['used_package'];
                    $console_real_package = $row['real_package'];

// Generate reference ID
$last_2_chars = substr($recipient_number, -2);
$first_five_chars = substr($recipient_number, 0, 5);
$seconds = date('His');
$month = date('md');
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars;

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
if(curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
        $error = "Order Failed. cURL Error: " . curl_error($ch);
        header("Location: index.php?error=" . urlencode($error));
            curl_close($ch);
        exit;
}

// Parse the SOAP XML response
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);

// Check if XML parsing was successful
if ($xml === false) {
    echo "Error: Unable to parse SOAP response.";
        $error = "Order Failed. Error: Unable to parse SOAP response.";
        header("Location: index.php?error=" . urlencode($error));
    foreach(libxml_get_errors() as $error) {
        echo "Error Code: " . $error->code . " - " . $error->message ;
            $error = "Order Failed. Error Code: " . $error->code . " - " . $error->message . "Contact Admin for help";
            header("Location: index.php?error=" . urlencode($error));
                exit;
        
    }
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
    if ($insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig, real_package, order_time, order_date, real_date, order_status, error_code, message, client_email) VALUES (?,?,?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
    $insertordersItems->bind_param("sssssssssss", $reference_id, $recipient_number, $gig, $real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_email);

    if ($insertordersItems->execute()) {
        // Send SMS notification
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
        if ($sms_http_code == 200) {
        // Insert into console_orders table
        $stmt = $con->prepare("INSERT INTO console_orders (order_id, console_id, console_name, agent_id,agent_name, agent_email, agent_tel, recipient, package, order_time, order_date, real_date,order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssssss", $reference_id,$console_id,$console_name,$agent_id,$agent_name,$agent_email,$agent_tel,$recipient_number,$gig,$order_time,$order_date,$real_date,$status);
        if ($stmt->execute()) {
            // Update package balance
            $current_package = $package - $gig;
            $current_used_package = $used_package + $gig;
            $current_real_package = $console_real_package - $real_package;
            $stmt = $con->prepare("UPDATE consoles SET package = ?, used_package = ?, real_package = ? WHERE agent_id = ?");
            $stmt->bind_param("ssss", $current_package, $current_used_package, $current_real_package, $agent_id);
            if ($stmt->execute()) {
                // Insert into console_transactions table
                $stmt = $con->prepare("INSERT INTO console_transactions(trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date, trans_desc) VALUES (?,?,?,?,?,?,?,?,?,?)");
                                        $stmt->bind_param("ssssssssss", $reference_id,$console_id,$console_name,$agent_id,$agent_name,$agent_email,$agent_tel,$gig,$real_date, $trans_desc);

                if ($stmt->execute()) {
                    $success = "Order sent successfully.";
                    header("Location: index.php?success=$success&reference_id=$reference_id");
                    exit();
                        } else {
                            $error = "Failed to insert into transactions: " . $stmt->error;
                            header("Location: index.php?error=" . urlencode($error));
                            exit();
                        }
                    } else {
                        $error = "Failed to update console: " . $stmt->error;
                        header("Location: index.php?error=" . urlencode($error));
                        exit();
                    }
                } else {
                    $error = "Failed to purchase package: " . $stmt->error;
                    header("Location: index.php?error=" . urlencode($error));
                    exit();
                }
            } 
        
       else {
            $error = "Order Failed. Failed to send SMS. Response: $sms_response";
            header("Location: index.php?error=" . urlencode($error));
                exit;
        }

    } else {
        $error = "Order Failed. Failed to insert into database: " . $insertordersItems->error;
        header("Location: index.php?error=" . urlencode($error));
            exit;
    }
    $insertordersItems->close();
} else {
    $error = "Order Failed. Unable to prepare order insertion statement: " . $con->error;
    header("Location: index.php?error=" . urlencode($error));
    exit;
}  
    echo "success: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
} else {
    $error = "Order Failed: " . htmlspecialchars($response_msg);
    header("Location: index.php?error=" . urlencode($error));
    exit;
           }
    // Close cURL session
    curl_close($ch);

                }
            } else {
                $error = "Agent information not found.";
                header("Location: index.php?error=" . urlencode($error));
                exit();
            }
            $stmt->close();
        } else {
            $error = "Database error: unable to prepare SELECT statement.";
            header("Location: index.php?error=" . urlencode($error));
            exit();
        }
    }
}
?>