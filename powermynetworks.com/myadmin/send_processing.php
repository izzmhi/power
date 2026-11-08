<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../database/dbconfig.php';

date_default_timezone_set('GMT');
$order_time = date('H:i:s A');
$order_date = date('l, F d, Y');
$real_date = date('Y-m-d');
$date4 = date('ymd');
$time = date('his');
$to = bin2hex(random_bytes(2));
$tok = bin2hex(random_bytes(2));
$order_id = $to . $date4 . $tok . $time;
$order_status = "Delivered";

$recipient = '0270068633'; // Example telephone number
$package = '60';          // Example package
$gig = '60';              // Example gigabyte value

// Generate reference ID
$last_2_chars = substr($recipient, -2);
$first_five_chars = substr($recipient, 0, 5);
$seconds = date('His');
$month = date('md');
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars;


$soap_request = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
    <soapenv:Header/>
    <soapenv:Body>
        <tem:FlexiIshareBundle>
            <tem:username>CodeCraftNetwork</tem:username>
            <tem:password>REDACTED</tem:password>
            <tem:dealerMsisdn>233276420033</tem:dealerMsisdn>
            <tem:recipientMsisdn>{$recipient}</tem:recipientMsisdn>
            <tem:transactionId>{$reference_id}</tem:transactionId>
            <tem:sharedBundle>{$package}</tem:sharedBundle>
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
    curl_close($ch);
    exit; // Stop further execution if there's an error
}

// Parse the SOAP XML response
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);

// Check if XML parsing was successful
if ($xml === false) {
    echo "Error: Unable to parse SOAP response.";
    foreach(libxml_get_errors() as $error) {
        echo "Error Code: " . $error->code . " - " . $error->message . "<br>";
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
    if ($insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig, order_time, order_date, real_date, order_status, error_code, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
    $insertordersItems->bind_param("sssssssss", $reference_id, $recipient, $gig, $order_time, $order_date, $real_date, $order_status, $response_code, $response_msg);

    if ($insertordersItems->execute()) {
        // Send SMS notification
        $smsData = [
            "type" => 1,
            "senderid" => "CodeCraft",
            "messages" => [
                [
                    "recipient" => $recipient,
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
            $success = "Order saved and SMS sent successfully!";
            header("Location: send.php?success=" . urlencode($success));
                exit;
        } else {
            $error = "Failed to send SMS. Response: $sms_response";
            header("Location: send.php?error=" . urlencode($error));
                exit;
        }

    } else {
        $error = "Failed to insert into database: " . $insertordersItems->error;
        header("Location: send.php?error=" . urlencode($error));
            exit;
    }
    $insertordersItems->close();
} else {
    $error = "Unable to prepare order insertion statement: " . $con->error;
    header("Location: send.php?error=" . urlencode($error));
    exit;
}  
    
    echo "success: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
    
} else {

    $error = "Failed: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
    header("Location: send.php?error=" . urlencode($error));
    exit;
}

// Close the cURL connection
curl_close($ch);
?>

