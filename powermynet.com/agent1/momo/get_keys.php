<?php
// === Configuration ===
$baseUrl = "https://sandbox.momodeveloper.mtn.com";
$subscriptionKey = "400cba8326fc4352a3f8ef710938c35b";
$callbackHost = "https://codecraftnetwork.com/agent/momo/index.php";

// === Generate UUID (v4 format) ===
function generateUuidV4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // Version 4
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // Variant
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$uuid = generateUuidV4();

// === Step 1: Create API User ===
$createUserUrl = "$baseUrl/v1_0/apiuser";
$createUserHeaders = [
    "X-Reference-Id: $uuid",
    "Ocp-Apim-Subscription-Key: $subscriptionKey",
    "Content-Type: application/json"
];

$createUserBody = json_encode([
    "providerCallbackHost" => $callbackHost
]);

$ch = curl_init($createUserUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => $createUserBody,
    CURLOPT_HTTPHEADER => $createUserHeaders
]);
$createUserResponse = curl_exec($ch);
$createUserHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$createUserError = curl_error($ch);
curl_close($ch);

if ($createUserError || !in_array($createUserHttpCode, [201, 202])) {
    echo "❌ Failed to create API user.\n";
    echo "Error: " . ($createUserError ?: $createUserResponse) . "\n";
    exit;
}

echo "✅ API User Created: $uuid\n";

// === Step 2: Wait briefly before requesting API key ===
sleep(3);

// === Step 3: Generate API Key ===
$getApiKeyUrl = "$baseUrl/v1_0/apiuser/$uuid/apikey";
$getApiKeyHeaders = [
    "Ocp-Apim-Subscription-Key: $subscriptionKey"
];

$ch = curl_init($getApiKeyUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $getApiKeyHeaders
]);
$getApiKeyResponse = curl_exec($ch);
$getApiKeyHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$getApiKeyError = curl_error($ch);
curl_close($ch);

if ($getApiKeyError || $getApiKeyHttpCode !== 201) {
    echo "❌ Failed to generate API key.\n";
    echo "Error: " . ($getApiKeyError ?: $getApiKeyResponse) . "\n";
    exit;
}

$data = json_decode($getApiKeyResponse, true);
$apiKey = $data['apiKey'] ?? null;

if ($apiKey) {
    echo "✅ API Key Generated: $apiKey\n";

    // Save user ID and API Key to file
    $keyData = [
        'user_id' => $uuid,
        'api_key' => $apiKey
    ];

    file_put_contents('keys.json', json_encode($keyData, JSON_PRETTY_PRINT));
    echo "🔐 Keys saved to keys.json\n";
} else {
    echo "❌ API Key not found in response.\n";
    echo "Response: $getApiKeyResponse\n";
}
?>
