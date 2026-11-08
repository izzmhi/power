<?php
session_start();

// =====================================
// ERROR DISPLAY (DEV ONLY - REMOVE ON LIVE IF NEEDED)
// =====================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =====================================
// CUSTOM FILE LOGGER
// =====================================
$logFile = realpath(__DIR__ . '/../../../') . '/shop_error.log';

function log_shop($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $msg" . PHP_EOL, FILE_APPEND);
}

// =====================================
// BUILD BASE URL FOR REDIRECTS
// =====================================
$scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$url         = $scheme . "://" . $host . $request_uri;

$parts  = parse_url($url);
$scheme = $parts['scheme'] ?? 'https';
$host   = $parts['host'] ?? '';
$path   = isset($parts['path']) ? dirname($parts['path']) : '';
$base   = rtrim($scheme . '://' . $host . $path, '/');

// log base url
log_shop("Computed BASE URL: {$base}");

// =====================================
// SESSION CHECK
// =====================================
if (!isset($_SESSION['user_id'])) {
    log_shop("SESSION MISSING: user_id not set. Redirecting to base {$base}");
    header('Location: ' . $base);
    exit();
}

// =====================================
// LOAD DEPENDENCIES
// =====================================
include '../../../database/dbconfig.php';       // $con
require '../../../configs/paystack_config.php'; // PAYSTACK_SECRET_KEY

// Application base URL for redirects (prefer session base if set)
$appBase = isset($_SESSION['url']) ? rtrim($_SESSION['url'], '/') : $base;
log_shop("App Base URL for redirects: {$appBase}");
log_shop("=== NEW PAYSTACK VERIFICATION SESSION STARTED ===");

// =====================================
// MAIN LOGIC - HANDLE FORM SUBMIT
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Sanitize inputs
    $reference_id = mysqli_real_escape_string($con, $_POST['reference'] ?? '');
    $email        = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $network_req  = mysqli_real_escape_string($con, $_POST['network'] ?? '');

    log_shop("INPUT RECEIVED: reference_id={$reference_id}, email={$email}, network_input={$network_req}");

    // Basic validation
    if ($reference_id === '') {
        $err = "Reference ID is required";
        log_shop("ERROR: {$err}");
        header("Location: {$appBase}/order/?error=" . urlencode($err));
        exit();
    }

    if ($email === '') {
        $err = "Email is required";
        log_shop("ERROR: {$err}");
        header("Location: {$appBase}/order/?error=" . urlencode($err));
        exit();
    }

    log_shop("Basic input validation passed.");

    // =====================================
    // CALL PAYSTACK VERIFY ENDPOINT
    // =====================================
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
        header("Location: {$appBase}/order/?error=" . urlencode("Payment verification error. Try again or contact support."));
        exit();
    }

    log_shop("Paystack RAW Response: {$response}");

    $responseData = json_decode($response, true);

    if (!is_array($responseData) || !isset($responseData['status'])) {
        log_shop("ERROR: Invalid JSON response from Paystack: " . print_r($responseData, true));
        header("Location: {$appBase}/order/?error=" . urlencode("Invalid response from payment gateway."));
        exit();
    }

    if ($responseData['status'] !== true) {
        log_shop("ERROR: Paystack status=false: " . print_r($responseData, true));
        header("Location: {$appBase}/order/?error=" . urlencode("Transaction not found or already processed."));
        exit();
    }

    $trx = $responseData['data'] ?? null;
    if (!$trx) {
        log_shop("ERROR: Missing data node in Paystack response.");
        header("Location: {$appBase}/order/?error=" . urlencode("Transaction data missing."));
        exit();
    }

    log_shop("Transaction Data: " . print_r($trx, true));

    // =====================================
    // VALIDATE EMAIL
    // =====================================
    $paystackEmail = $trx['customer']['email'] ?? '';
    if (strtolower($paystackEmail) !== strtolower($email)) {
        log_shop("SECURITY ALERT: Email mismatch. Expected={$email}, Paystack={$paystackEmail}");
        header("Location: {$appBase}/order/?error=" . urlencode("Unauthorized transaction detected."));
        exit();
    }

    log_shop("Email validation passed: {$email}");

    // =====================================
    // VALIDATE TRANSACTION STATUS
    // =====================================
    $trxStatus = $trx['status'] ?? 'unknown';
    if (in_array($trxStatus, ['failed', 'abandoned'], true)) {
        log_shop("ERROR: Invalid transaction status from Paystack: {$trxStatus}");
        header("Location: {$appBase}/order/?error=" . urlencode("Payment failed or abandoned."));
        exit();
    }

    log_shop("Transaction status OK: {$trxStatus}");

    // =====================================
    // AMOUNT COMPUTATION (3% DEDUCTION)
    // =====================================
    $amountKobo     = $trx['amount'] ?? 0;         // Paystack in pesewas/kobo
    $originalAmount = $amountKobo / 100.0;         // Convert to GHS
    $deduction      = round($originalAmount * 0.03, 2);  // 3% fee
    $finalAmount    = round($originalAmount - $deduction, 2);

    log_shop("Amount processing: original={$originalAmount}, deduction={$deduction}, final={$finalAmount}");

    // =====================================
    // METADATA EXTRACTION
    // =====================================
    $beneficiary = $networkMeta = $bundle = '';

    // Log raw metadata
    $rawMeta = $trx['metadata'] ?? [];
    log_shop("RAW METADATA: " . json_encode($rawMeta));

    // 1) Direct keys
    if (!empty($rawMeta['beneficiary'])) $beneficiary = $rawMeta['beneficiary'];
    if (!empty($rawMeta['network']))     $networkMeta = $rawMeta['network'];
    if (!empty($rawMeta['bundle']))      $bundle      = $rawMeta['bundle'];

    // 2) custom_fields fallback
    if (!empty($rawMeta['custom_fields']) && is_array($rawMeta['custom_fields'])) {
        foreach ($rawMeta['custom_fields'] as $field) {
            $vname = strtolower($field['variable_name'] ?? '');
            $value = $field['value'] ?? '';

            if ($vname === 'beneficiary' && $beneficiary === '') $beneficiary = $value;
            if ($vname === 'network' && $networkMeta === '')     $networkMeta = $value;
            if ($vname === 'bundle' && $bundle === '')           $bundle      = $value;
        }
    }

    $beneficiary = trim($beneficiary);
    $networkMeta = trim($networkMeta);
    $bundle      = trim($bundle);

    log_shop("EXTRACTED METADATA: beneficiary={$beneficiary}, networkMeta={$networkMeta}, bundle={$bundle}");

    // If the posted network is empty, fallback to metadata network
    if ($network_req === '' && $networkMeta !== '') {
        $network_req = $networkMeta;
        log_shop("Network input empty, using network from metadata: {$network_req}");
    }

    // Normalize for routing
    $network_req_upper = strtoupper(trim($network_req));
    log_shop("Final network used for routing: {$network_req_upper}");

    // =====================================
    // STORE IN SESSION
    // =====================================
    $_SESSION['network']     = $networkMeta ?: $network_req_upper;
    $_SESSION['gig']         = $bundle;
    $_SESSION['price']       = $finalAmount;
    $_SESSION['email']       = $email;
    $_SESSION['beneficiary'] = $beneficiary;

    log_shop("SESSION UPDATED: network={$_SESSION['network']}, bundle={$_SESSION['gig']}, price={$_SESSION['price']}, email={$_SESSION['email']}, beneficiary={$_SESSION['beneficiary']}");

    // =====================================
    // REDIRECT BASED ON NETWORK
    // =====================================
    $targetFile = null;

    switch ($network_req_upper) {
        case 'MTN':
            $targetFile = 'order_mtn.php';
            break;
        case 'AT':
            $targetFile = 'order_at.php';
            break;
        case 'TELECEL':
        case 'VODAFONE': // if Telecel/Vodafone
            $targetFile = 'order_telecel.php';
            break;
        case 'AT BIG':
            $targetFile = 'order_atbig.php';
            break;
        case 'MTN BIG':
            $targetFile = 'order_mtnbig.php';
            break;
        default:
            log_shop("ERROR: No matching redirect for network '{$network_req_upper}'.");
            header("Location: {$appBase}/order/?error=" . urlencode("Invalid network configuration ({$network_req_upper})."));
            exit();
    }

    log_shop("Redirecting to {$targetFile} with reference {$reference_id}");
    header("Location: {$appBase}/{$targetFile}?paystackpaid=" . urlencode($reference_id));
    exit();
}

// If reached without POST/submit
log_shop("NO VALID POST SUBMISSION DETECTED. Ending verification session.");
log_shop("=== PAYSTACK VERIFICATION SESSION ENDED ===");
?>
