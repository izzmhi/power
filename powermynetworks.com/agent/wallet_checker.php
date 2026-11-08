<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Redirect if session expired
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

function log_sms($title, $data = []) {
    $logFile = __DIR__ . "/sms_log.log"; // You can rename or change folder
    $time = date('Y-m-d H:i:s');
    $logData = "[" . $time . "] " . $title . " => " . json_encode($data) . PHP_EOL;

    file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
}

// --- Load DB Connections ---
include '../database/dbconfig.php';    // Main Agents DB (wallet, accounts, transactions)
include 'waec_dbconfig.php';          // WAEC DB (prices, codes, payments)
include '../sms_config.php';

// --------------------------------------------------------
// LOAD AGENT DETAILS FROM MAIN DB
// --------------------------------------------------------
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$queryAgent = mysqli_query($con, "SELECT * FROM agents WHERE user_id='$user_id'");

if (mysqli_num_rows($queryAgent) == 0) {
    header("Location: wallet_checker.php?error=" . urlencode("Agent not found"));
    exit();
}

$agentData = mysqli_fetch_assoc($queryAgent);

// Store session for later use
$_SESSION['first_name'] = $agentData['first_name'];
$_SESSION['last_name']  = $agentData['last_name'];
$_SESSION['email']      = $agentData['email'];
$_SESSION['telephone']  = $agentData['telephone'];

if (isset($_POST['submit'])) {
    // Sanitize input
    $agent_id      = $user_id;
    $agent_name    = $agentData['first_name'] . " " . $agentData['last_name'];
    $agent_email   = $agentData['email'];
    $agent_tel     = $agentData['telephone'];
    $qty           = intval($_POST['qty']);
    $telephone     = mysqli_real_escape_string($waec_con, $_POST['telephone']);
    $reference_id  = mysqli_real_escape_string($waec_con, $_POST['reference_id']);
    $mode    = "Wallet";
    $purpose = "WASSCE Checker Purchase";
    date_default_timezone_set('GMT');
    $trans_time = date('H:i:s A');
    $trans_date = date('Y-m-d');
    // Validate inputs
    if ($qty < 1 || empty($telephone) || empty($reference_id)) {
        header("Location: wallet_checker.php?error=" . urlencode("Invalid submission"));
        exit();
    }
    // --------------------------------------------------------
    // DUPLICATE ORDER CHECK — WAEC DB
    // --------------------------------------------------------
    $checkDup = $waec_con->prepare("SELECT reference_id FROM payments WHERE reference_id=? LIMIT 1");
    $checkDup->bind_param("s", $reference_id);
    $checkDup->execute();
    if ($checkDup->get_result()->num_rows > 0) {
        header("Location: wallet_checker.php?error=" . urlencode("Duplicate order") . "&reference_id=" . urlencode($reference_id));
        exit();
    }
    // --------------------------------------------------------
    // FETCH PRICE FROM WAEC DB
    // --------------------------------------------------------
    $priceQuery  = mysqli_query($waec_con, "SELECT amount, original FROM prices WHERE target='WASSCE' LIMIT 1");
    $priceRow    = mysqli_fetch_assoc($priceQuery);
    if (!$priceRow) {
        header("Location: wallet_checker.php?error=" . urlencode("Pricing unavailable"));
        exit();
    }
    $unit_price      = floatval($priceRow['amount']);    // selling price per code
    $original_price  = floatval($priceRow['original']);  // cost price per code
    // --------------------------------------------------------
    // CALCULATE DISCOUNT & TOTAL
    // --------------------------------------------------------
    if ($qty >= 5) {
        $effective_unit_price = max($unit_price - 2, 0); // discounted unit price
    } else {
        $effective_unit_price = $unit_price;
    }
    $total_amount = $effective_unit_price * $qty;
    if ($total_amount <= 0) {
        header("Location: wallet_checker.php?error=" . urlencode("Invalid calculation"));
        exit();
    }
    // profit per code & total profit
    $profit_per_code = $effective_unit_price - $original_price;
    $total_profit    = $profit_per_code * $qty;
    // --------------------------------------------------------
    // CHECK WALLET FROM MAIN DB
    // --------------------------------------------------------
    $walletBalance = floatval($agentData['wallet']);
    if ($walletBalance < $total_amount) {
        header("Location: wallet_checker.php?error=" . urlencode("Insufficient wallet balance"));
        exit();
    }
    // --------------------------------------------------------
    // FETCH RANDOM FRESH CODES FROM WAEC DB
    // --------------------------------------------------------
    $status = "FRESH";
    $codeStmt = $waec_con->prepare("SELECT * FROM codes WHERE status=? ORDER BY RAND() LIMIT ?");
    $codeStmt->bind_param("si", $status, $qty);
    $codeStmt->execute();
    $codesResult = $codeStmt->get_result();
    if ($codesResult->num_rows < $qty) {
        header("Location: wallet_checker.php?error=" . urlencode("Not enough codes"));
        exit();
    }
    // --------------------------------------------------------
    // BEGIN TRANSACTIONS (BOTH DATABASES MUST STAY CONSISTENT)
    // --------------------------------------------------------
    $con->begin_transaction();
    $waec_con->begin_transaction();

    try {
        $sent_codes = [];

        // 1) Insert payment rows (one per code) & mark codes used (WAEC DB)
        while ($codeData = $codesResult->fetch_assoc()) {

            $serial_code = $codeData['serial_code'];
            $pin_code    = $codeData['pin_code'];
            $code_ref_id = $codeData['reference_id'];

            // Insert into WAEC payments table: amount & profit are per-code
            $stmtPay = $waec_con->prepare("INSERT INTO payments 
                (reference_id, customer, telephone, email, amount, profit, trans_date, trans_time, purpose, serial_code, pin_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmtPay->bind_param(
                "ssssdsissss",
                $reference_id,
                $agent_name,
                $telephone,
                $agent_email,
                $effective_unit_price,   // per-code amount
                $profit_per_code,        // per-code profit
                $trans_date,
                $trans_time,
                $purpose,
                $serial_code,
                $pin_code
            );
            if (!$stmtPay->execute()) {
                throw new Exception("WAEC payment insert failed: " . $stmtPay->error);
            }

            // Mark WAEC code used
            $stmtUpdateCode = $waec_con->prepare("UPDATE codes SET status='USED' WHERE reference_id=?");
            $stmtUpdateCode->bind_param("s", $code_ref_id);
            if (!$stmtUpdateCode->execute()) {
                throw new Exception("Failed to update code status: " . $stmtUpdateCode->error);
            }

            // Collect codes for SMS
            $sent_codes[] = ["serial" => $serial_code, "pin" => $pin_code];
        }

        // 2) Deduct wallet ONCE by the total_amount (MAIN DB)
        $walletBalanceAfter = $walletBalance - $total_amount;

        $stmtWallet = $con->prepare("UPDATE agents SET wallet=? WHERE user_id=?");
        $stmtWallet->bind_param("ds", $walletBalanceAfter, $agent_id);
        if (!$stmtWallet->execute()) {
            throw new Exception("Wallet update failed: " . $stmtWallet->error);
        }

        // 3) Insert ONE transaction log (MAIN DB)
        $stmtTrans = $con->prepare("
            INSERT INTO agents_transactions(
                reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtTrans->bind_param(
            "ssssdsss",
            $reference_id,
            $agent_id,
            $agent_name,
            $agent_email,
            $total_amount,
            $walletBalanceAfter,
            $trans_date,
            $purpose
        );
        if (!$stmtTrans->execute()) {
            throw new Exception("Transaction record failed: " . $stmtTrans->error);
        }
        // -------------------------
        // 4) SEND ONE BATCH SMS
        // -------------------------
        $sms_message  = "WASSCE / NOVDEC / SSCE RESULT CHECKER\n";
        $sms_message .= "Website: ghana.waecdirect.org\n";
        $sms_message .= "Qty: {$qty}\n";
        $sms_message .= "-------------------------\n";
        foreach ($sent_codes as $index => $c) {
            $num = $index + 1;
            $sms_message .= "{$num}) PIN: {$c['pin']}\n";
            $sms_message .= "    SERIAL: {$c['serial']}\n";
        }
        // Prepare SMS payload
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
        // Log SMS result
        log_sms("BATCH SMS SENT", [
            'recipient' => $telephone,
            'http_code' => $sms_code,
            'response'  => $sms_response ?: 'No response',
            'error'     => $sms_error ?: 'None',
            'codes_sent'=> count($sent_codes)
        ]);
        // Finalize DB transactions
        $waec_con->commit();
        $con->commit();
        header("Location: transactions.php?success=" . urlencode("Order successful — " . count($sent_codes) . " code(s) delivered") . "&r=" . urlencode($reference_id));
        exit();
    } catch (Exception $ex) {
        $waec_con->rollback();
        $con->rollback();
        header("Location: wallet_checker.php?error=" . urlencode("Failed: " . $ex->getMessage()));
        exit();
    }
}

$con->close();
$waec_con->close();
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
			<?php $page = 'wallet' ?>
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
				    
					<!-- Row start -->

								<?php 
								if(isset($_GET['error'])){
								?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <div><?php echo $_GET['error']; ?></div>
                                   </div>
                                <?php } ?> 
                                
   <?php
include '../database/dbconfig.php';

if (isset($_POST['wassce_Wallet'])) {

    // Get form inputs
    $qty       = intval($_POST['qty']);
    $telephone = mysqli_real_escape_string($con, $_POST['telephone']);
        // Generate reference ID
    $last_2_chars = substr($telephone, -2);
    $first_five_chars = substr($telephone, 0, 5);
    $seconds = date('His');
    $month = date('md');
    $reference_id = $first_five_chars . $month . $seconds . $last_2_chars;
    // Fetch unit price
    $getPrice  = mysqli_query($con, "SELECT amount FROM checker_price WHERE product='WASSCE'");
    $rowPrice  = mysqli_fetch_assoc($getPrice);
    $amount    = floatval($rowPrice['amount']);
    // ==== CALCULATE TOTAL ====  
    if ($qty >= 5) {
        $discount_price = $amount - 2;
        if ($discount_price < 0) { $discount_price = 0; } // Safety
        $total = $discount_price * $qty;
    } else {
        $total = $amount * $qty;
    }
    // Save total for next page (if needed)
    $_SESSION['aprice'] = $total;

?>
<!-- ORDER SUMMARY DISPLAY -->
<div class="row gutters">
    <div class="col-lg-6 col-sm-12 col-12 mx-auto">
        <div class="card h-100 shadow-lg border-0 order-card">

            <!-- Header -->
            <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <h4 class="text-white mb-0"><i class="fa fa-shopping-cart"></i> Order Placement</h4>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <!-- Body -->
            <div class="card-body pb-5 flex-grow-1">

                <div class="row mb-3">
                    <div class="col-6 text-muted">Quantity</div>
                    <div class="col-6 text-right font-weight-bold"><?php echo $qty; ?></div>
                </div>
                <hr>

                <div class="row mb-3">
                    <div class="col-6 text-muted">Telephone</div>
                    <div class="col-6 text-right font-weight-bold"><?php echo $telephone; ?></div>
                </div>
                <hr>

                <div class="row mb-3">
                    <div class="col-6 text-muted">Unit Price</div>
                    <div class="col-6 text-right font-weight-bold text-success">
                        GHS <?php echo number_format($amount, 2); ?>
                    </div>
                </div>
                <hr>

                <?php if ($qty > 10) { ?>
                <div class="alert alert-success text-center">
                    Bulk Discount Applied: GHS 2.00 off per item!
                </div>
                <?php } ?>

                <div class="row mb-3">
                    <div class="col-6 text-muted">Total Amount</div>
                    <div class="col-6 text-right font-weight-bold text-primary">
                        GHS <?php echo number_format($total, 2); ?>
                    </div>
                </div>
                <hr>

                <!-- Check Wallet Balance -->
                <?php
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $queryUser = mysqli_query($con, "SELECT wallet FROM agents WHERE user_id='$user_id'");
                
                if (mysqli_num_rows($queryUser) > 0) {
                    $uData = mysqli_fetch_assoc($queryUser);
                    $wallet = floatval($uData['wallet']);

                    if ($total > $wallet) {
                        echo '<div class="alert alert-danger mt-3">
                                <i class="fa fa-exclamation-circle"></i> Insufficient balance. Please top up.
                              </div>';
                    } else {
                ?>

                <!-- Final Submit Form -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="qty" value="<?php echo $qty; ?>">
                    <input type="hidden" name="telephone" value="<?php echo $telephone; ?>">
                    <input type="hidden" name="reference_id" value="<?php echo $reference_id; ?>" readonly >
                    
                    <button type="submit" name="submit" class="btn btn-info btn-lg btn-block" id="btnSpinner">
                        <i class="fa fa-paper-plane"></i> Confirm and Order
                    </button>
                </form>

                <?php
                    }
                }
                ?>

            </div>
        </div>
    </div>
</div>

<?php
} else {
    echo "<script>window.location='index.php';</script>";
}
?>

                                

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
						
		<div class="loading-overlay" id="spinner" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
<!--spinner start-->
      <script>
        $(document).ready(function() {
    $('#btnSpinner').on('click', function() {
        // Show the spinner
        $('#spinner').show();
    });
    });
    </script>
		<style>
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
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }
        .loading-text {
            margin-top: 10px;
            font-size: 18px;
            color: #007bff;
        }
    </style>
		</div>
		</div>
			<!-- Page content end -->
		</div>
		
		<!-- Page wrapper end -->
		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>