<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require_once 'configs/config.php'; // contains PAYSTACK_SECRET_KEY, SMS_API_URL, SMS_API_KEY, SMS_SENDER_ID
require_once 'configs/sms_config.php';
include 'database/waec_dbconfig.php';

/* ============================================================
   GLOBAL LOGGING FUNCTIONS
   ============================================================ */
function log_error($message, $context = [])
{
    log_to_file('payments.log', $message, $context);
}

function log_sms($message, $context = [])
{
    log_to_file('sms.log', $message, $context);
}

function log_to_file($filename, $message, $context = [])
{
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $file = $log_dir . '/' . $filename;
    $timestamp = date('Y-m-d H:i:s');
    $context_json = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $entry = "[$timestamp] $message " . ($context_json ? " | Context: $context_json" : "") . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND);
}

/* ============================================================
   INITIAL VALIDATION
   ============================================================ */
if (!isset($_GET['reference']) || empty($_GET['reference'])) {
    log_error("Missing Paystack reference", $_GET);
    die("No payment reference provided.");
}

$paystack_reference_id = htmlspecialchars($_GET['reference']);
$purpose    = "WASSCE";
$trans_date = date('Y-m-d');
$trans_time = date('H:i:s');

log_error("Verification started", ['reference' => $paystack_reference_id]);

/* ============================================================
   1. VERIFY WITH PAYSTACK (RETRY UP TO 5 MINUTES)
   ============================================================ */
$verified = false;
$transaction = null;
$start_time = time();

do {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($paystack_reference_id),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache"
        ]
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        log_error("cURL error during Paystack verify", ['error' => $err]);
        sleep(5);
        continue;
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['data']['status']) && $responseData['data']['status'] === 'success') {
        $transaction = $responseData['data'];
        $verified = true;
        break;
    }

    log_error("Verification pending", [
        'reference' => $paystack_reference_id,
        'attempt' => round((time() - $start_time) / 10),
        'status' => $responseData['data']['status'] ?? 'none'
    ]);

    sleep(10); // retry after 10s

} while ((time() - $start_time) < 300); // retry up to 5 mins

if (!$verified || !$transaction) {
    log_error("Verification failed after retries", ['reference' => $paystack_reference_id]);
    header("Location: success.php?error=" . urlencode("Payment not confirmed yet. Please refresh later or contact support.") . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

log_error("Verification success", ['reference' => $paystack_reference_id]);

/* ============================================================
   2. EXTRACT METADATA
   ============================================================ */
$metadata = $transaction['metadata']['custom_fields'] ?? [];
$customer = $telephone = '';
$qty = 1;

foreach ($metadata as $field) {
    if ($field['variable_name'] === 'customer') $customer = $field['value'];
    elseif ($field['variable_name'] === 'telephone') $telephone = $field['value'];
    elseif ($field['variable_name'] === 'quantities') $qty = (int)$field['value'];
}

$email = $transaction['customer']['email'] ?? ($telephone . '@gmail.com');
$qty = max(1, intval($qty));

log_error("Metadata extracted", compact('customer', 'telephone', 'email', 'qty'));

/* ============================================================
   3. COMPUTE PAYMENT DETAILS
   ============================================================ */
$amount_in_pesewas = intval($transaction['amount']);
$amount_in_ghs     = $amount_in_pesewas / 100.0;
$paystack_fee      = round($amount_in_ghs * 0.02, 2);
$amount_after_fee  = round($amount_in_ghs - $paystack_fee, 2);

/* ============================================================
   4. FETCH PRICING FROM DB
   ============================================================ */
$target = "WASSCE";
$stmt = $con->prepare("SELECT original, amount FROM prices WHERE target=? LIMIT 1");
$stmt->bind_param("s", $target);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    log_error("Price config missing", ['target' => $target]);
    header("Location: success.php?error=" . urlencode("Price configuration not found.") . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

$row = $res->fetch_assoc();
$original_price = floatval($row['original']);
$db_price       = floatval($row['amount']);

/* ============================================================
   5. VERIFY AMOUNT MATCH (±1 GHS tolerance)
   ============================================================ */
$expected_total = $qty * $db_price;
$min_allowed = $expected_total - 1;
$max_allowed = $expected_total + 1;

if ($amount_in_ghs < $min_allowed || $amount_in_ghs > $max_allowed) {
    log_error("Amount mismatch", [
        'expected_total' => $expected_total,
        'received' => $amount_in_ghs,
        'qty' => $qty,
        'customer' => $customer
    ]);
    $error = "Invalid payment amount. Expected ₵$expected_total, but received ₵$amount_in_ghs.";
    header("Location: success.php?error=" . urlencode($error) . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

/* ============================================================
   6. DUPLICATE CHECK
   ============================================================ */
$check = $con->prepare("SELECT reference_id FROM payments WHERE reference_id=?");
$check->bind_param("s", $paystack_reference_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    log_error("Duplicate payment detected", ['reference' => $paystack_reference_id]);
    header("Location: success.php?error=" . urlencode("Payment already processed.") . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

/* ============================================================
   7. FETCH MULTIPLE RANDOM CODES
   ============================================================ */
$code_status = 'FRESH';
$code_query = $con->prepare("SELECT * FROM codes WHERE status=? ORDER BY RAND() LIMIT ?");
$code_query->bind_param("si", $code_status, $qty);
$code_query->execute();
$codes_res = $code_query->get_result();

if ($codes_res->num_rows < $qty) {
    log_error("Insufficient codes", ['needed' => $qty, 'available' => $codes_res->num_rows]);
    $error = "Not enough fresh codes available (needed $qty).";
    header("Location: success.php?error=" . urlencode($error) . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

/* ============================================================
   8. DB TRANSACTION + SMS SENDING
   ============================================================ */
$con->begin_transaction();

try {
    $sent_codes = [];

    while ($code_row = $codes_res->fetch_assoc()) {
        $serial_code       = $code_row['serial_code'];
        $pin_code          = $code_row['pin_code'];
        $code_reference_id = $code_row['reference_id'];

        // INSERT PAYMENT
        $stmt = $con->prepare("INSERT INTO payments(reference_id, customer, telephone, email, amount, profit, trans_date, trans_time, purpose, serial_code, pin_code)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $profit = ($qty * $amount_after_fee) - ($qty * $original_price);
        $stmt->bind_param("sssssssssss",
            $paystack_reference_id,
            $customer,
            $telephone,
            $email,
            $amount_after_fee,
            $profit,
            $trans_date,
            $trans_time,
            $purpose,
            $serial_code,
            $pin_code
        );
        $stmt->execute();

        // UPDATE CODE STATUS
        $upd = $con->prepare("UPDATE codes SET status='USED' WHERE reference_id=?");
        $upd->bind_param("s", $code_reference_id);
        $upd->execute();

        log_error("Payment and code updated", [
            'reference' => $paystack_reference_id,
            'serial' => $serial_code,
            'pin' => $pin_code
        ]);

        /* ============================================================
           SEND SMS USING MOOLRE API
           ============================================================ */
        $sms_message = "WASSCE/NOVDEC/SSCE Checker\n"
            . "Website: ghana.waecdirect.org\n"
            . "PIN: {$pin_code}\nSerial: {$serial_code}\n"
            . "Ref: {$paystack_reference_id}\nThank you, {$customer}!";

        $sms_payload = [
            "type" => 1,
            "senderid" => $sender_id,  // from sms_config.php
            "messages" => [
                [
                    "recipient" => $telephone,
                    "message" => $sms_message
                ]
            ]
        ];

        $ch_sms = curl_init($api_url);
        curl_setopt($ch_sms, CURLOPT_POST, true);
        curl_setopt($ch_sms, CURLOPT_POSTFIELDS, json_encode($sms_payload));
        curl_setopt($ch_sms, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-VASKEY: ' . $api_key
        ]);
        curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);

        $sms_response = curl_exec($ch_sms);
        $sms_code = curl_getinfo($ch_sms, CURLINFO_HTTP_CODE);
        $sms_error = curl_error($ch_sms);
        curl_close($ch_sms);

        // Log every SMS event
        log_sms("SMS API called", [
            'recipient' => $telephone,
            'http_code' => $sms_code,
            'response' => $sms_response ?: 'No response',
            'error' => $sms_error ?: 'None',
            'payload' => $sms_payload
        ]);

        $sent_codes[] = [
            'serial' => $serial_code,
            'pin' => $pin_code,
            'sms_status' => $sms_code
        ];
    }

    $con->commit();

    log_error("Transaction committed successfully", [
        'reference' => $paystack_reference_id,
        'codes_sent' => count($sent_codes),
        'codes' => $sent_codes
    ]);

    header("Location: success.php?success=" . urlencode("Payment successful! " . count($sent_codes) . " code(s) sent via SMS.") . "&reference_id=" . urlencode($paystack_reference_id));
    exit();

} catch (Exception $e) {
    $con->rollback();
    log_error("Transaction rolled back due to error", ['error' => $e->getMessage()]);
    header("Location: success.php?error=" . urlencode("Database or SMS error: " . $e->getMessage()) . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="asset/images/Power.png" />
    <title>Payment Verification | POWERMYNET</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500&display=swap" rel="stylesheet">
    <script src="js/jquery-2.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <style>
      body {
        font-family: 'Rajdhani', sans-serif;
        background: #f7f9fb;
      }
      .loader-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 250px;
      }
      .spinner {
        width: 70px;
        height: 70px;
        border: 6px solid #ccc;
        border-top: 6px solid #198754;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      .alert {
        border-left: 6px solid;
        padding: 15px;
        font-size: 16px;
        margin-top: 25px;
        border-radius: 10px;
      }
      .alert-success { border-color: #28a745; background: #d4edda; color: #155724; }
      .alert-danger { border-color: #dc3545; background: #f8d7da; color: #721c24; }
      .section-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
      }
    </style>
  </head>

  <body>

    <!-- Header Section -->
    <section id="header" class="clearfix cd-secondary-nav">
      <nav class="navbar nav_t">
        <div class="container">
          <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand col_3" href="index.html">
              <img src="asset/images/Power.png" style="width:35px; height:35px;"> POWERMYNET
            </a>
          </div>
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
              <li><a class="m_tag active_tab" href="index.html">Home</a></li>
              <li><a class="m_tag" href="#">About</a></li>
              <li><a class="m_tag" href="#">Shop</a></li>
              <li><a class="m_tag" href="#">Agents</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </section>

    <!-- Page Title -->
    <section id="spec">
      <div class="container text-center mt-5">
        <h3 class="fw-bold"><i class="fa fa-play"></i> ORDER <span class="text-success">CONFIRMATION</span></h3>
      </div>
    </section>

    <!-- Main Content -->
    <section id="verification">
      <div class="container my-5">
        <div class="row justify-content-center">
          <div class="col-md-6 section-box text-center">

            <!-- Loader -->
            <div id="loader" class="loader-container">
              <div class="spinner"></div>
            </div>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
              <div class="alert alert-success" id="successMessage">
                <i class="fa fa-check-circle"></i>
                <strong>Success!</strong> <?php echo htmlspecialchars($_GET['success']); ?>
              </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger" id="errorMessage">
                <i class="fa fa-exclamation-circle"></i>
                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </section>

    <script>
      // Hide loader automatically once success or error message is present
      $(document).ready(function() {
        if ($('#successMessage').length || $('#errorMessage').length) {
          $('#loader').hide();
        } else {
          // If no message, show loader for a short period
          $('#loader').show();
        }
      });
    </script>

    <!-- Footer -->
    <section id="footer_b" class="mt-5">
      <div class="container text-center">
        <p class="text-muted mb-0">© 2025 POWERMYNET. All Rights Reserved | Designed by Tyler</p>
      </div>
    </section>

  </body>
</html>
