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
$logFile = __DIR__ . '/shop_error.log';
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
    log_shop("SESSION MISSING: user_id not set. Redirecting to /my/");
    header('Location: /my/');
    exit();
}

// ==============================
// MAIN LOGIC - HANDLE FORM SUBMIT
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $reference_id = mysqli_real_escape_string($con, $_POST['reference'] ?? '');
    $email        = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $network_req  = mysqli_real_escape_string($con, $_POST['network'] ?? '');

    log_shop("INPUT RECEIVED: reference_id={$reference_id}, email={$email}, network_input={$network_req}");

    // Basic validation
    if ($reference_id === '') {
        $err = "Reference ID is required";
        log_shop("ERROR: {$err}");
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode($err));
        exit();
    }

    if ($email === '') {
        $err = "Email is required";
        log_shop("ERROR: {$err}");
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode($err));
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
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Payment verification error. Try again or contact support."));
        exit();
    }

    log_shop("Paystack RAW Response: {$response}");
    $responseData = json_decode($response, true);

    if (!is_array($responseData) || !isset($responseData['status'])) {
        log_shop("ERROR: Invalid JSON response from Paystack: " . print_r($responseData, true));
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Invalid response from payment gateway."));
        exit();
    }

    if ($responseData['status'] !== true) {
        log_shop("ERROR: Paystack status=false: " . print_r($responseData, true));
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Transaction not found or already processed."));
        exit();
    }

    $trx = $responseData['data'] ?? null;
    if (!$trx) {
        log_shop("ERROR: Missing data node in Paystack response.");
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Transaction data missing."));
        exit();
    }

    log_shop("Transaction Data: " . print_r($trx, true));

    // ==============================
    // VALIDATE EMAIL
    // ==============================
    $paystackEmail = $trx['customer']['email'] ?? '';
    if (strtolower($paystackEmail) !== strtolower($email)) {
        log_shop("SECURITY ALERT: Email mismatch. Expected={$email}, Paystack={$paystackEmail}");
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Unauthorized transaction detected."));
        exit();
    }

    log_shop("Email validation passed: {$email}");

    // ==============================
    // VALIDATE TRANSACTION STATUS
    // ==============================
    $trxStatus = $trx['status'] ?? 'unknown';
    if (in_array($trxStatus, ['failed', 'abandoned'], true)) {
        log_shop("ERROR: Invalid transaction status from Paystack: {$trxStatus}");
        header("Location: /my/{$_SESSION['shop']}/reorder.php?error=" . urlencode("Payment failed or abandoned."));
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
    $beneficiary = $networkMeta = $bundle = '';
    $rawMeta = $trx['metadata'] ?? [];
    log_shop("RAW METADATA: " . json_encode($rawMeta));

    if (!empty($rawMeta['beneficiary'])) $beneficiary = $rawMeta['beneficiary'];
    if (!empty($rawMeta['network']))     $networkMeta = $rawMeta['network'];
    if (!empty($rawMeta['bundle']))      $bundle      = $rawMeta['bundle'];

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

    if ($network_req === '' && $networkMeta !== '') {
        $network_req = $networkMeta;
        log_shop("Network input empty, using network from metadata: {$network_req}");
    }

    $network_req_upper = strtoupper(trim($network_req));
    log_shop("Final network used for routing: {$network_req_upper}");

    // ==============================
    // STORE IN SESSION
    // ==============================
    $_SESSION['network']     = $networkMeta ?: $network_req_upper;
    $_SESSION['gig']         = $bundle;
    $_SESSION['price']       = $finalAmount;
    $_SESSION['email']       = $email;
    $_SESSION['beneficiary'] = $beneficiary;

    log_shop("SESSION UPDATED: network={$_SESSION['network']}, bundle={$_SESSION['gig']}, price={$_SESSION['price']}, email={$_SESSION['email']}, beneficiary={$_SESSION['beneficiary']}");

    // ==============================
    // REDIRECT BASED ON NETWORK
    // ==============================
    $shop = $_SESSION['shop'];
    switch ($network_req_upper) {
        case 'MTN':
            $targetFile = "/my/$shop/order_mtn.php";
            break;
        case 'AT':
            $targetFile = "/my/$shop/order_at.php";
            break;
        case 'TELECEL':
        case 'VODAFONE':
            $targetFile = "/my/$shop/order_telecel.php";
            break;
        case 'AT BIG':
            $targetFile = "/my/$shop/order_atbig.php";
            break;
        case 'MTN BIG':
            $targetFile = "/my/$shop/order_mtnbig.php";
            break;
        default:
            log_shop("ERROR: No matching redirect for network '{$network_req_upper}'");
            header("Location: /my/$shop/?error=" . urlencode("Invalid network configuration ($network_req_upper)."));
            exit();
    }

    log_shop("Redirecting to {$targetFile} with reference {$reference_id}");
    header("Location: {$targetFile}?reference=" . urlencode($reference_id));
    exit();
}

// ==============================
// NO POST SUBMISSION
// ==============================
log_shop("NO VALID POST SUBMISSION DETECTED. Ending verification session.");
log_shop("=== PAYSTACK VERIFICATION SESSION ENDED ===");
?>
