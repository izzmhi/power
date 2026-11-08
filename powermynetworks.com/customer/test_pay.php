<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../database/dbconfig.php';
$errors = array();
$success = array();
if (isset($_GET['paystackpaid'])) {

    // Sanitize and validate input data
    $gig = mysqli_real_escape_string($con, $_SESSION['gig']);
    $customer_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $real_package = mysqli_real_escape_string($con, $_SESSION['real_package']);
    $price = mysqli_real_escape_string($con, $_SESSION['price']);
    $network = mysqli_real_escape_string($con, $_SESSION['network']);
    $recipient_number = mysqli_real_escape_string($con, $_SESSION['beneficiary']);
    $reference_id = mysqli_real_escape_string($con, $_GET['paystackpaid']);
    $agent_id = "NONE";
    $agent_name = "CUSTOMER";
    $agent_tel = "NONE";
    $agent_email = "NONE";
    $mode = "PAYSTACK";
    if (!$customer_email || !$recipient_number || !$network || !$gig || !$price) {
        $errors[] = 'Invalid input. Please check your data.';
    } else {

            // Prepare order details
                $status = "Crediting Successful";
                date_default_timezone_set('GMT');
                $order_time = date('H:i:s A');
                $order_date = date('l, F d, Y');
                $real_date = date('Y-m-d');
                
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
if(curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
        $error = "Order Failed. cURL Error: " . curl_error($ch);
        header("Location: success1.php?error=" . urlencode($error));
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
        header("Location: success1.php?error=" . urlencode($error));
    foreach(libxml_get_errors() as $error) {
        echo "Error Code: " . $error->code . " - " . $error->message ;
            $error = "Order Failed. Error Code: " . $error->code . " - " . $error->message . "Contact Admin for help";
            header("Location: success1.php?error=" . urlencode($error));
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
    if ($insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig,real_package, order_time, order_date, real_date, order_status, error_code, message, client_email) VALUES (?,?,?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
    $insertordersItems->bind_param("sssssssssss", $reference_id, $recipient_number, $gig,$real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_email);

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
            

                // Insert order into the database using prepared statements
 $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email,beneficiary,network,gig,price,order_time,order_date,real_date,order_status,mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("sssssssssdsssss", $reference_id,$reference_id,$agent_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price,$order_time,$order_date,$real_date,$status,$mode);
                    if ($stmt->execute()) {
                        $success = "Order is successful.";
                       header("Location: success1.php?success=$success&reference_id=$reference_id");
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: success1.php?error=$error");
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: " . $con->error;
                    header("Location: success1.php?error=$error");
                }



        } 
        
        
        
        
        
        else {
            $error = "Order Failed. Failed to send SMS. Response: $sms_response";
            header("Location: success1.php?error=" . urlencode($error));
                exit;
        }

    } else {
        $error = "Order Failed. Failed to insert into database: " . $insertordersItems->error;
        header("Location: success1.php?error=" . urlencode($error));
            exit;
    }
    $insertordersItems->close();
} else {
    $error = "Order Failed. Unable to prepare order insertion statement: " . $con->error;
    header("Location: success1.php?error=" . urlencode($error));
    exit;
}  
    echo "success: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
} else {
    $error = "Order Failed: " . htmlspecialchars($response_msg);
    header("Location: success1.php?error=" . urlencode($error));
    exit;
           }
    // Close cURL session
    curl_close($ch);  
                
                
                
                

        }
    // Close database connection
    $con->close();
}
?>