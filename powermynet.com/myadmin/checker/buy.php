<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include 'database/dbconfig.php';
require_once 'sms_config.php';

/* ============================================================
   LOGGING HELPERS
   ============================================================ */
function log_error($message, $context = []) {
    log_to_file('payments.log', $message, $context);
}
function log_sms($message, $context = []) {
    log_to_file('sms.log', $message, $context);
}
function log_to_file($filename, $message, $context = []) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0777, true);
    $file = $log_dir . '/' . $filename;
    $timestamp = date('Y-m-d H:i:s');
    $context_json = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $entry = "[$timestamp] $message " . ($context_json ? " | Context: $context_json" : "") . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND);
}

/* ============================================================
   HANDLE POST
   ============================================================ */
if (isset($_POST['submit'])) {

    // sanitize inputs
    $target    = trim($_POST['category'] ?? '');
    $customer  = trim($_POST['customer'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $qty       = max(1, intval($_POST['qty'] ?? 1)); // default to 1 if not provided

    // basic validation
    if ($target === '' || $customer === '' || $telephone === '') {
        log_error('Missing required fields', compact('target','customer','telephone','qty'));
        header("Location: buy.php?error=" . urlencode("Please fill all required fields."));
        exit();
    }

    // build email if not provided
    $email = (filter_var($telephone, FILTER_VALIDATE_EMAIL) ? $telephone : ($telephone . '@gmail.com'));

    $trans_date = date('Y-m-d');
    $trans_time = date('H:i:s');

    // get pricing (per-unit)
    $stmt = $con->prepare("SELECT original, amount FROM prices WHERE target=? LIMIT 1");
    if (!$stmt) {
        log_error("Prepare failed (prices select)", ['error' => $con->error]);
        header("Location: buy.php?error=" . urlencode("Server error. Try again later."));
        exit();
    }
    $stmt->bind_param("s", $target);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows === 0) {
        log_error("Price config missing", ['target' => $target]);
        header("Location: buy.php?error=" . urlencode("Price configuration not found."));
        exit();
    }

    $row = $res->fetch_assoc();
    $original_price = floatval($row['original']); // cost
    $db_price       = floatval($row['amount']);   // sell price per code

    // fetch fresh codes: ORDER BY RAND() LIMIT $qty
    $code_status = 'FRESH';
    $code_query = $con->prepare("SELECT * FROM codes WHERE status=? AND package=? ORDER BY RAND() LIMIT ?");
    if (!$code_query) {
        log_error("Prepare failed (codes select)", ['error' => $con->error]);
        header("Location: buy.php?error=" . urlencode("Server error. Try again later."));
        exit();
    }
    // bind params: status (s), package (s), qty (i)
    $code_query->bind_param("ssi", $code_status, $target, $qty);
    $code_query->execute();
    $codes_res = $code_query->get_result();
    $code_query->close();

    if ($codes_res->num_rows < $qty) {
        log_error("Insufficient codes", ['needed' => $qty, 'available' => $codes_res->num_rows, 'package' => $target]);
        $error = "Not enough fresh codes available (needed $qty).";
        header("Location: buy.php?error=" . urlencode($error));
        exit();
    }

    // begin transaction
    $con->begin_transaction();
    try {
        $sent_codes = [];
        $total_inserted = 0;

        // precompute per-code profit
        $profit_per_code = round($db_price - $original_price, 2);

        while ($code_row = $codes_res->fetch_assoc()) {
            $serial_code       = $code_row['serial_code'];
            $pin_code          = $code_row['pin_code'];
            $code_reference_id = $code_row['reference_id']; // unique per code

            // insert into payments (one row per code)
            $stmt_ins = $con->prepare("INSERT INTO payments(reference_id, customer, telephone, email, amount, profit, trans_date, trans_time, purpose, serial_code, pin_code)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_ins) {
                throw new Exception("Prepare failed (insert payment): " . $con->error);
            }

            // store per-code amount and profit
            $amount = $db_price; // per single code
            $profit = $profit_per_code;

            $stmt_ins->bind_param("sssssssssss",
                $code_reference_id,
                $customer,
                $telephone,
                $email,
                $db_price,
                $profit_per_code,
                $trans_date,
                $trans_time,
                $target,
                $serial_code,
                $pin_code
            );

            if (!$stmt_ins->execute()) {
                $err = $stmt_ins->error;
                $stmt_ins->close();
                throw new Exception("Insert payment failed: " . $err);
            }
            $stmt_ins->close();

            // update code status to USED
            $upd = $con->prepare("UPDATE codes SET status='USED' WHERE reference_id=?");
            if (!$upd) {
                throw new Exception("Prepare failed (update code): " . $con->error);
            }
            $upd->bind_param("s", $code_reference_id);
            if (!$upd->execute()) {
                $err = $upd->error;
                $upd->close();
                throw new Exception("Update code failed: " . $err);
            }
            $upd->close();

            log_error("Payment inserted and code marked used", [
                'reference' => $code_reference_id,
                'serial' => $serial_code,
                'pin' => $pin_code,
                'customer' => $customer
            ]);

            // prepare SMS payload
                    $sms_message = "BECE/WASSCE/NOVDEC/ Checker\n"
            . "PIN: {$pin_code}\nSerial: {$serial_code}\n"
            . "Ref: {$code_reference_id}\nThank you, {$customer}!";
            $sms_payload = [
                "type" => 1,
                "senderid" => $sender_id,
                "messages" => [
                    [
                        "recipient" => $telephone,
                        "message" => $sms_message
                    ]
                ]
            ];

            // call SMS API
            $ch_sms = curl_init($api_url);
            curl_setopt($ch_sms, CURLOPT_POST, true);
            curl_setopt($ch_sms, CURLOPT_POSTFIELDS, json_encode($sms_payload));
            curl_setopt($ch_sms, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-API-VASKEY: ' . $api_key
            ]);
            curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);
            $sms_response = curl_exec($ch_sms);
            $sms_http_code = curl_getinfo($ch_sms, CURLINFO_HTTP_CODE);
            $sms_error = curl_error($ch_sms);
            curl_close($ch_sms);

            // Log SMS call
            log_sms("SMS API called", [
                'recipient' => $telephone,
                'http_code' => $sms_http_code,
                'response' => $sms_response ?: 'No response',
                'error' => $sms_error ?: 'None',
                'payload' => $sms_payload
            ]);

            $sent_codes[] = [
                'serial' => $serial_code,
                'pin' => $pin_code,
                'reference' => $code_reference_id,
                'sms_http_code' => $sms_http_code
            ];

            $total_inserted++;
        } // end while

        // commit transaction
        $con->commit();

        log_error("Transaction committed successfully", [
            'package' => $target,
            'codes_sent' => $total_inserted,
            'codes' => $sent_codes
        ]);

        // redirect with success
        $msg = "Payment successful! {$total_inserted} code(s) sent via SMS.";
        header("Location: buy.php?success=" . urlencode($msg));
        exit();

    } catch (Exception $e) {
        $con->rollback();
        log_error("Transaction rolled back due to error", ['error' => $e->getMessage()]);
        header("Location: buy.php?error=" . urlencode("Error processing purchase: " . $e->getMessage()));
        exit();
    }
} // end if submit
?>



<!doctype html>
<html lang="en">
	<head>
		<!--Head-->
		<?php include 'parts/head.php'; ?> 
	</head>
	<body>
		<!-- Page wrapper start -->
		<div class="page-wrapper">
			<!-- Sidebar wrapper start -->
			<nav id="sidebar" class="sidebar-wrapper">
			<!-- side-->
			<?php $page = 'prices' ?>
			<?php include 'parts/side.php'; ?> 
			</nav>
			<!-- Sidebar wrapper end -->
			<!-- Page content start  -->
			<div class="page-content">
				<!-- Header start -->
				<header class="header">
					<!-- header-->
					<?php include 'parts/header.php'; ?> 
				</header>
				<!-- Header end -->
				<!-- Page header start -->
				<div class="page-header">
						<!-- Nav-->
						<?php include 'parts/nav.php'; ?> 
				</div>
				<!-- Page header end -->

				<!-- Main container start -->
				<div class="main-container">
                   <h6>Purchase for someone</h6>
   
					<!-- Row start -->

					<?php
					if(isset($_GET['success'])){
					    ?>
					    <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
					    <?php
					}
					else if(isset($_GET['error'])){
					    ?>
					    <div class="alert alert-danger" role="alert" style="border-left:5px solid red"><?php echo $_GET['error']; ?> </div>
					    <?php
					}
					?>
<style>
    .nav-block {
    display: block;
    width: 50%;  /* Each nav will take up half of the row */
    text-align: center;
    box-sizing: border-box; /* Ensures padding is included in width */
}

.nav-tabs {
    display: flex;
    width: 100%;
}

.nav-tabs .nav-item {
    flex-grow: 1; /* Each nav will expand to fill the available space */
}
</style>					

      		<div class="row gutters">
				<div class="col-lg-12 col-sm-12 col-12">
					<div class="card h-100">
						<div class="card-header">
							<div class="card-title"></div>
						</div>
						<div class="card-body">

 <form action="" method="POST" enctype="multipart/form-data" id="paymentForm" class="p-3 shadow-sm rounded bg-white">

  <!-- Card Type -->
  <div class="mb-3">
    <label for="target" class="form-label fw-bold text-dark">Select Card Type</label>
    <select id="target" name="category" class="form-control" required>
      <option value="">-Select a Card Type-</option>
      <option value="BECE">BECE</option>
      <option value="WASSCE">WASSCE</option>
    </select>
  </div>

  <!-- Customer Name -->
  <div class="mb-3">
    <label for="customer" class="form-label fw-bold text-dark">Customer Name</label>
    <input type="text" id="customer" name="customer" class="form-control" placeholder="Enter your full name" required
      pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
  </div>

  <!-- Telephone -->
  <div class="mb-3">
    <label for="telephone" class="form-label fw-bold text-dark">Telephone</label>
    <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="e.g. 0241234567" required
      pattern="^0\d{9}$" title="Enter a valid 10-digit Ghanaian number starting with 0">
  </div>

  <!-- Submit Button -->
  <div class="d-grid">
    <button type="submit" name="submit" class="btn btn-primary py-2 fw-bold" id="setBtn">
      <i class="fa fa-shopping-cart me-2"></i> Buy Now
    </button>
  </div>

</form>


								</div>
							</div>
						</div>
                    </div>			
			    </div>
			<!-- Page content end -->
		   </div>
		<!-- Page wrapper end -->
		
 <style>
    .modal-body {
    position: relative; /* Allows absolute positioning of spinner within this container */
}

/* Spinner overlay styling */
.loading-overlay {
    display: none; /* Hide by default */
    position: absolute; /* Position it absolutely within the modal body */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
    z-index: 999; /* Ensure it is above other content */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner styling */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(0, 0, 0, 0.1);
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Loading text styling */
.loading-text {
    margin-top: 10px;
    font-size: 18px;
    color: #007bff;
}
    </style>


		

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--spinner start-->


		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>