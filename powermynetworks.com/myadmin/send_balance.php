<?php
// SOAP request XML
$soap_request = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:tem="http://tempuri.org/">
    <soapenv:Header/>
    <soapenv:Body>
        <tem:FlexiShareBalanceCheck>
            <tem:username>CodeCraftNetwork</tem:username>
            <tem:password>REDACTED</tem:password>
            <tem:dealerMsisdn>233276420033</tem:dealerMsisdn>
        </tem:FlexiShareBalanceCheck>
    </soapenv:Body>
</soapenv:Envelope>
XML;

// SOAP endpoint
$url = "http://41.215.168.146:443/FlexiShareBundles.asmx";

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: text/xml; charset=utf-8',
    'SOAPAction: "http://tempuri.org/FlexiShareBalanceCheck"'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);

// Execute cURL request
$response = curl_exec($ch);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch);
} else {
    // Output raw SOAP response for debugging
    echo "SOAP Response:<br><pre>";
    echo htmlspecialchars($response);
    echo "</pre>";

    // Parse the response to extract useful information
    $xml = simplexml_load_string($response);

    // Use namespaces to parse the response correctly
    $namespaces = $xml->getNamespaces(true);
    $body = $xml->children($namespaces['soap'])->Body->children($namespaces[''])->FlexiShareBalanceCheckResponse->FlexiShareBalanceCheckResult;

    // Extract values from the response
    $response_code = (string) $body->ApiResponse->ResponseCode;
    $response_msg = (string) $body->ApiResponse->ResponseMsg;
    $expire_time = (string) $body->ExpireTime;
    $data_balance = (string) $body->DataBalance;

    // Display the parsed information
    echo "<h3>Parsed Response:</h3>";
    echo "Response Code: $response_code<br>";
    echo "Response Message: $response_msg<br>";
    echo "Expire Time: $expire_time<br>";
    echo "Data Balance: $data_balance MB<br>";
}

curl_close($ch);
?>
