<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require_once 'sms_config.php';
include 'waec_dbconfig.php';
require_once 'config.php'; // PAYSTACK_PUBLIC_KEY, PAYSTACK_SECRET_KEY

/* ----------------- LOGGING ----------------- */
function log_to_file($filename, $message, $context = [])
{
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0777, true);
    $file = $log_dir . '/' . $filename;
    $timestamp = date('Y-m-d H:i:s');
    $context_json = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    file_put_contents($file, "[$timestamp] $message " . ($context_json ? " | Context: $context_json" : "") . PHP_EOL, FILE_APPEND);
}
function log_error($message, $context = []) { log_to_file('payments.log', $message, $context); }
function log_sms($message, $context = []) { log_to_file('sms.log', $message, $context); }

/* ----------------- INPUT VALIDATION ----------------- */
if (isset($_POST['submit'])) {

$user_telephone = trim($_POST['telephone']);
$purchased_date = trim($_POST['purchased_date']);
if ($user_telephone === '' || $purchased_date === '') {
    log_error("Empty telephone or date", compact('user_telephone','purchased_date'));
    header("Location: success.php?error=" . urlencode("Invalid input data."));
    exit();
}

/* ============================================================
   1️⃣ FETCH TRANSACTION FROM PAYSTACK USING TELEPHONE + DATE
   ============================================================ */
$verified = false;
$transaction = null;
$paystack_reference_id = null;
$start_time = time();

do {
    $url = "https://api.paystack.co/transaction?perPage=100&page=1";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
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
        log_error("cURL error fetching Paystack data", ['error' => $err]);
        continue;
    }

    $responseData = json_decode($response, true);
    if (!isset($responseData['data']) || !is_array($responseData['data'])) {
        log_error("Invalid Paystack response", ['response' => $response]);
        continue;
    }

    foreach ($responseData['data'] as $tx) {
        $paid_at = isset($tx['paid_at']) ? date('Y-m-d', strtotime($tx['paid_at'])) : '';
        $status = $tx['status'] ?? '';
        $meta_tel = '';
        $meta_target = '';
        $meta_qty = 1;

        if (!empty($tx['metadata']['custom_fields'])) {
            foreach ($tx['metadata']['custom_fields'] as $f) {
                if ($f['variable_name'] === 'telephone') $meta_tel = $f['value'];
                if ($f['variable_name'] === 'target') $meta_target = $f['value'];
                if ($f['variable_name'] === 'quantities') $meta_qty = (int)$f['value'];
            }
        }

        // ✅ Match telephone and purchase date
        if ($paid_at === $purchased_date && $meta_tel === $user_telephone && $status === 'success') {
            $transaction = $tx;
            $paystack_reference_id = $tx['reference']; // 🔥 Paystack unique transaction reference
            $verified = true;
            break 2; // stop both loops
        }
    }

} while ((time() - $start_time) < 300); // Retry up to 5 mins

if (!$verified || !$transaction || !$paystack_reference_id) {
    log_error("You have not make any transaction for", compact('user_telephone', 'purchased_date'));
    header("Location: success.php?error=" . urlencode("Payment not found for $user_telephone on $purchased_date."));
    exit();
}

log_error("Paystack transaction matched", ['reference' => $paystack_reference_id]);

/* ============================================================
   2️⃣ EXTRACT METADATA FROM PAYSTACK
   ============================================================ */
$metadata = $transaction['metadata']['custom_fields'] ?? [];
$customer = $telephone = $target = '';
$qty = 1;

foreach ($metadata as $field) {
    if ($field['variable_name'] === 'customer') $customer = $field['value'];
    if ($field['variable_name'] === 'telephone') $telephone = $field['value'];
    if ($field['variable_name'] === 'quantities') $qty = (int)$field['value'];
    if ($field['variable_name'] === 'target') $target = strtoupper($field['value']);
}

$email = $transaction['customer']['email'] ?? ($telephone . '@gmail.com');
$qty = max(1, $qty);
$target = $target;

log_error("Metadata extracted", compact('customer', 'telephone', 'email', 'qty', 'target'));

/* ============================================================
   3️⃣ VERIFY AMOUNT
   ============================================================ */
$amount_in_pesewas = intval($transaction['amount']);
$amount_in_ghs = $amount_in_pesewas / 100.0;

$stmt = $con->prepare("SELECT original, amount FROM prices WHERE target=? LIMIT 1");
$stmt->bind_param("s", $target);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    log_error("Price config missing", ['target' => $target]);
    header("Location: success.php?error=" . urlencode("No price configuration for $target."));
    exit();
}

$row = $res->fetch_assoc();
$original_price = floatval($row['original']);
$db_price = floatval($row['amount']);

// DISCOUNT: If qty > 5, reduce unit price by 2 GHS
if ($qty > 4) {
    $effective_unit_price = max($db_price - 2, 0);
    $effective_original = max($original_price - 2, 0);
} else {
    $effective_unit_price = $db_price;
    $effective_original= $original_price;
}
$total_amount = $effective_unit_price * $qty;
$total_original = $effective_original * $qty;
if ($total_amount <= 0) {
    log_error("Invalid total calculation", ['qty' => $qty, 'unit_price' => $effective_unit_price]);
    header("Location: success.php?error=" . urlencode("Invalid calculation"));
    exit();
}
/* ============================================================
   5. VERIFY AMOUNT MATCH (±1 GHS tolerance)
   ============================================================ */
$min_allowed = $total_amount - 2;
$max_allowed = $total_amount + 2;
if ($amount_after_fee < $min_allowed || $amount_after_fee > $max_allowed) {
    log_error("Amount mismatch", [
        'expected_total' => $total_amount,
        'received' => $amount_after_fee,
        'qty' => $qty,
        'customer' => $customer
    ]);
    $error = "Invalid payment amount. Expected ₵$expected_total, but received ₵$amount_after_fee.";
    header("Location: success.php?error=" . urlencode($error) . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

/* ============================================================
   4️⃣ DUPLICATE CHECK
   ============================================================ */
$check = $con->prepare("SELECT reference_id FROM payments WHERE reference_id=? AND trans_date=?");
$check->bind_param("ss", $paystack_reference_id, $purchased_date);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    log_error("Duplicate payment detected", ['reference' => $paystack_reference_id]);
    header("Location: success.php?error=" . urlencode("Order already processed."));
    exit();
}

/* ============================================================
   5️⃣ FETCH RANDOM CODES FROM THE RIGHT PACKAGE (TARGET)
   ============================================================ */
$code_status = 'FRESH';
$code_query = $con->prepare("SELECT * FROM codes WHERE status=? AND package=? ORDER BY RAND() LIMIT ?");
$code_query->bind_param("ssi", $code_status, $target, $qty);
$code_query->execute();
$codes_res = $code_query->get_result();

if ($codes_res->num_rows < $qty) {
    log_error("Insufficient codes", ['needed' => $qty, 'available' => $codes_res->num_rows]);
    header("Location: success.php?error=" . urlencode("Not enough fresh codes available for $target. Contact Admin"));
    exit();
}

/* ============================================================
   6️⃣ DB TRANSACTION + SMS
   ============================================================ */
$con->begin_transaction();
try {
    $sent_codes = [];
$unit_amount = $amount_after_fee / $qty;       // amount per code
$unit_profit = $unit_amount - $effective_original;  // profit per code

while ($code_row = $codes_res->fetch_assoc()) {
    $serial_code = $code_row['serial_code'];
    $pin_code    = $code_row['pin_code'];
    $code_reference_id = $code_row['reference_id'];

    $stmt = $con->prepare("INSERT INTO payments(reference_id, customer, telephone, email, amount, profit, trans_date, trans_time, purpose, serial_code, pin_code)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss",
        $paystack_reference_id,
        $customer,
        $telephone,
        $email,
        $unit_amount,
        $unit_profit,
        $trans_date,
        $trans_time,
        $purpose,
        $serial_code,
        $pin_code
    );
    $stmt->execute();

    // Mark code as used
    $upd = $con->prepare("UPDATE codes SET status='USED' WHERE reference_id=?");
    $upd->bind_param("s", $code_reference_id);
    $upd->execute();

    // ADD TO SENT CODES ARRAY (move here)
    $sent_codes[] = [
        'serial' => $serial_code,
        'pin'    => $pin_code
    ];
}

    /* ============================================================
       SEND ONE BATCH SMS
       ============================================================ */
    $sms_message  = "WASSCE / NOVDEC / SSCE RESULT CHECKER\n";
    $sms_message .= "Website: ghana.waecdirect.org\n";
    $sms_message .= "Qty: {$qty}\n";
    $sms_message .= "-------------------------\n";
    foreach ($sent_codes as $i => $c) {
        $line = $i + 1;
        $sms_message .= "{$line}) PIN: {$c['pin']}\n";
        $sms_message .= "    SERIAL: {$c['serial']}\n";
    }
    // SMS payload
    $sms_payload = [
        "type" => 1,
        "senderid" => $sender_id,
        "messages" => [
            [
                "recipient" => $telephone,
                "message"   => $sms_message
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
    $sms_code     = curl_getinfo($ch_sms, CURLINFO_HTTP_CODE);
    $sms_error    = curl_error($ch_sms);
    curl_close($ch_sms);
    // LOG RESULT
    log_sms("BATCH SMS SENT", [
        'recipient' => $telephone,
        'http_code' => $sms_code,
        'response'  => $sms_response ?: 'No response',
        'error'     => $sms_error ?: 'None',
        'codes_sent'=> count($sent_codes)
    ]);
    // FINALLY COMMIT EVERYTHING (ONCE)
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
    log_error("Transaction rolled back", ['error' => $e->getMessage()]);
    header("Location: success.php?error=" . urlencode("Database or SMS error: " . $e->getMessage()) . "&reference_id=" . urlencode($paystack_reference_id));
    exit();
}

}
?>



<!DOCTYPE html>
<html lang="en">
  <head>
	<?php include 'parts/head.php'; ?> 
  </head>
<body>
    <link  rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
<section id="header" class="clearfix cd-secondary-nav">
	<?php $page = 'home' ?>
	<?php include 'parts/header.php'; ?> 
	</section>

<section id="spec">
 <div class="container">
  <div class="row">
   <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h3 class="mgt"><i class="fa fa-play"></i> CARDS <span class="col_1">RETRIEVAL</span></h3>
	</div>
   </div>
  </div>
 </div>
</section>

<section id="popular">
 <div class="container">
  <div class="row">
   <div class="popular_1 clearfix">

<div class="col-sm-12 col-12 p-4" style="border: 1px solid #dee2e6;">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
          <br>
          <?php
          if(isset($_GET['error'])){
             ?>
             <div class="alert alert-danger shadow-sm p-3 mb-4 mt-2 pt-3 rounded" style="border-left: 6px solid red; background: #e8f9ee;">
                 <?php echo $_GET['error']; ?>
                 </div>
                 <?php
          }
          ?>
          <br>
    <h4 class="card-header text-white fw-bold">Fill the details to retrieve the cards</h4>
<!-- ======= PAYMENT FORM ======= -->
  <form id="payForm" action="" method="POST" enctype="multipart/form-data">
      <br>
    <!-- Full Name -->
    <div class="mb-3">
      <label class="fw-bold">Date of Purchased</label>
      <input type="date" id="date" name="purchased_date" class="form-control" required>
    </div>

    <!-- Telephone -->
    <div class="mb-3">
      <label class="fw-bold">Telephone</label>
      <input type="tel" id="telephone" name="telephone" class="form-control" required>
    </div>
    <!-- Amount -->
    <br>
    <br>
    <!-- Submit -->
    <div class="mb-3 mt-4">
         <button type="submit" name="submit" class="btn btn-success btn-block w-100 fw-bold py-2" >RETRIEVE NOW</button>
    </div>
  </form>
  <br>

      </div>
    </div>
  </div>
</div>

   </div>
 
  </div>
 </div>
</section>
	<?php include 'parts/footer.php'; ?> 

</body>
 
</html>
