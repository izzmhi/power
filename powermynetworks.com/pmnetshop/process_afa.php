<?php
// ==============================
// SESSION CONFIGURATION
// ==============================
$cookie_lifetime = 24 * 60 * 60; // 24 hours
session_set_cookie_params([
    'lifetime' => $cookie_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', $cookie_lifetime);
session_start();
// ==============================
// ERROR REPORTING
// ==============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ==============================
// INCLUDES / CONFIG
// ==============================
include '../database/dbconfig.php'; // Database connection ($con)
require 'config.php';               // PAYSTACK_SECRET_KEY
// ==============================
// LOGGING FUNCTION
// ==============================
$logFile = __DIR__ . '/afa_process_error.log';
function log_shop($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $msg" . PHP_EOL, FILE_APPEND);
}
// ==============================
// HELPER FUNCTION
// ==============================
function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
                 || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
// ==============================
// SESSION CHECK
// ==============================
if (!isset($_SESSION['user_id'])) {
    log_shop("SESSION MISSING: user_id not set. Redirecting to /");
    header('Location: /');
    exit();
}
    $reference_id = mysqli_real_escape_string($con, $_GET['reference_id'] ?? '');
    $email        = mysqli_real_escape_string($con, $_GET['email'] ?? '');
    log_shop("INPUT RECEIVED: reference_id={$reference_id}, email={$email}");
    // Basic validation
    if ($reference_id === '') {
        $err = "Reference ID is required";
        log_shop("ERROR: {$err}");
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode($err));
        exit();
    }
    if ($email === '') {
        $err = "Email is required";
        log_shop("ERROR: {$err}");
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode($err));
        exit();
    }
    log_shop("Basic input validation passed.");
    // ==============================
    // CALL PAYSTACK VERIFY ENDPOINT
    // ==============================
    log_shop("Calling Paystack verify for reference: {$reference_id}");
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference_id),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ],
    ]);
    $response = curl_exec($curl);
    $errCurl  = curl_error($curl);
    curl_close($curl);
    if ($errCurl) {
        log_shop("CRITICAL: cURL Error from Paystack: {$errCurl}");
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Payment verification error. Try again or contact support."));
        exit();
    }
    log_shop("Paystack RAW Response: {$response}");
    $responseData = json_decode($response, true);
    if (!is_array($responseData) || !isset($responseData['status'])) {
        log_shop("ERROR: Invalid JSON response from Paystack: " . print_r($responseData, true));
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Invalid response from payment gateway."));
        exit();
    }
    if ($responseData['status'] !== true) {
        log_shop("ERROR: Paystack status=false: " . print_r($responseData, true));
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Transaction not found or already processed."));
        exit();
    }
    $trx = $responseData['data'] ?? null;
    if (!$trx) {
        log_shop("ERROR: Missing data node in Paystack response.");
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Transaction data missing."));
        exit();
    }
    log_shop("Transaction Data: " . print_r($trx, true));
    // ==============================
    // VALIDATE EMAIL
    // ==============================
    $paystackEmail = $trx['customer']['email'] ?? '';
    if (strtolower($paystackEmail) !== strtolower($email)) {
        log_shop("SECURITY ALERT: Email mismatch. Expected={$email}, Paystack={$paystackEmail}");
        header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Unauthorized transaction detected."));
        exit();
    }
    log_shop("Email validation passed: {$email}");
    // ==============================
    // VALIDATE TRANSACTION STATUS
    // ==============================
    $trxStatus = $trx['status'] ?? 'unknown';
if ($trxStatus !== 'success') {
    log_shop("ERROR: Transaction not successful: {$trxStatus}");
    header("Location: /{$_SESSION['shop']}/reorder.php?error=" . urlencode("Payment not successful."));
    exit();
}
    log_shop("Transaction status OK: {$trxStatus}");
    // ==============================
    // AMOUNT COMPUTATION (3% DEDUCTION)
    // ==============================
    $amountKobo     = $trx['amount'] ?? 0;
    $originalAmount = $amountKobo / 100.0;
    $deduction      = round($originalAmount * 0.03, 2);
    $finalAmount    = round($originalAmount - $deduction, 2);
    log_shop("Amount processing: original={$originalAmount}, deduction={$deduction}, final={$finalAmount}");
    // ==============================
    // METADATA EXTRACTION
    // ==============================
$fullname = $ghana_card = $location = $region = $occupation = $dob = '';
$beneficiary = '';

$rawMeta = $trx['metadata'] ?? [];

if (!empty($rawMeta['beneficiary'])) $beneficiary = $rawMeta['beneficiary'];
if (!empty($rawMeta['fullname'])) $fullname = $rawMeta['fullname'];
if (!empty($rawMeta['ghana_card'])) $ghana_card = $rawMeta['ghana_card'];
if (!empty($rawMeta['location'])) $location = $rawMeta['location'];
if (!empty($rawMeta['region'])) $region = $rawMeta['region'];
if (!empty($rawMeta['occupation'])) $occupation = $rawMeta['occupation'];
if (!empty($rawMeta['dob'])) $dob = $rawMeta['dob'];

if (!empty($rawMeta['custom_fields']) && is_array($rawMeta['custom_fields'])) {
    foreach ($rawMeta['custom_fields'] as $field) {
        $vname = strtolower($field['variable_name'] ?? '');
        $value = $field['value'] ?? '';

        if ($vname === 'beneficiary' && $beneficiary === '') $beneficiary = $value;
        if ($vname === 'fullname' && $fullname === '') $fullname = $value;
        if ($vname === 'ghana_card' && $ghana_card === '') $ghana_card = $value;
        if ($vname === 'location' && $location === '') $location = $value;
        if ($vname === 'region' && $region === '') $region = $value;
        if ($vname === 'occupation' && $occupation === '') $occupation = $value;
        if ($vname === 'dob' && $dob === '') $dob = $value;
    }
}
    $beneficiary = trim($beneficiary);
    $fullname = trim($fullname);
    $ghana_card      = trim($ghana_card);
    $location = trim($location);
    $region = trim($region);
    $occupation      = trim($occupation);
    $dob      = trim($dob);
    log_shop("EXTRACTED METADATA: beneficiary={$beneficiary}, fullname={$fullname}, ghana_card={$ghana_card}");
    // ==============================
    // STORE IN SESSION
    // ==============================
    $_SESSION['fullname']     = $fullname;
    $_SESSION['id_number']         = $ghana_card;
    $_SESSION['location']     = $location;
    $_SESSION['region']         = $region;
    $_SESSION['occupation']     = $occupation;
    $_SESSION['dob']         = $dob;
    $_SESSION['price']       = $finalAmount;
    $_SESSION['email']       = $email;
    $_SESSION['beneficiary'] = $beneficiary;
    log_shop("SESSION UPDATED: fullname={$_SESSION['fullname']}, id_number={$_SESSION['id_number']}, price={$_SESSION['price']}, email={$_SESSION['email']}, beneficiary={$_SESSION['beneficiary']}");
    // ==============================
    // REDIRECT BASED ON NETWORK
    // ==============================
    $shop = $_SESSION['shop'];
        $targetFile = "/$shop/order_afa.php";
    log_shop("Redirecting to {$targetFile} with reference {$reference_id}");
    header("Location: {$targetFile}?paystackpaid=" . urlencode($reference_id));
    exit();
// ==============================
// NO POST SUBMISSION
// ==============================
log_shop("NO VALID POST SUBMISSION DETECTED. Ending verification session.");
log_shop("=== PAYSTACK VERIFICATION SESSION ENDED ===");
?>
