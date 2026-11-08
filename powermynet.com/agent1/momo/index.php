<?php
// Load credentials
$keys = json_decode(file_get_contents('keys.json'), true);
$userId = $keys['user_id'];
$apiKey = $keys['api_key'];
$subscriptionKey = "400cba8326fc4352a3f8ef710938c35b";

// Step 1: Generate Token
$tokenUrl = "https://sandbox.momodeveloper.mtn.com/disbursement/token/";
$authHeader = base64_encode("$userId:$apiKey");

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic $authHeader",
        "Ocp-Apim-Subscription-Key: $subscriptionKey"
    ]
]);
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    die("❌ Failed to get access token. HTTP $httpCode. Response: $tokenResponse");
}

echo "✅ Access Token: $accessToken<br><br>";

// Step 2: Do a Transfer
$transferUrl = "https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer";
$referenceId = uniqid();

$transferData = [
    "amount" => "1000.00",
    "currency" => "EUR",
    "externalId" => "35464545678",
    "payee" => [
        "partyIdType" => "MSISDN",
        "partyId" => "233554226398" // Replace with test number
    ],
    "payerMessage" => "Salary",
    "payeeNote" => "Salary payment"
];

$ch = curl_init($transferUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $accessToken",
        "X-Reference-Id: $referenceId",
        "X-Target-Environment: sandbox",
        "Ocp-Apim-Subscription-Key: $subscriptionKey",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($transferData)
]);

$transferResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Output the results
if ($curlError) {
    echo "❌ cURL Error: $curlError";
} elseif ($httpCode === 202) {
    echo "✅ Transfer initiated successfully!<br>";
    echo "Response: $transferResponse<br>";
} else {
    echo "❌ Transfer failed. HTTP Status Code: $httpCode<br>";
    echo "Response: $transferResponse<br>";
}
