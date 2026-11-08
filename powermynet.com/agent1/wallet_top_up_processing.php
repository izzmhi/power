<?php
session_name("AGENT_PORTAL_SESSION"); 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
// Include the configuration file
require_once 'config.php';
include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
    $_SESSION['first_name'] = $rows['first_name'];
    $_SESSION['last_name']= $rows['last_name'];
    $_SESSION['email'] = $rows['email'];
    $_SESSION['telephone'] = $rows['telephone'];
    $_SESSION['wallet'] = $rows['wallet'];
    }
}
	$first_name = $_SESSION['first_name'];
	$last_name = $_SESSION['last_name'];
	$email = $_SESSION['email'];
	$telephone = $_SESSION['telephone'];
	
$errors = array();
$success = array();
// Process POST data
if (isset($_GET['transaction_id'])) {
    // Sanitize and validate input data
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $telephone = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $agent_name = $first_name . " " . $last_name;
    $purpose = "Top-up";
    $trans_date = date('Y-m-d');
$reference_id = htmlspecialchars($_GET['transaction_id']);
$paystack_reference_id = htmlspecialchars($_GET['paystack_ref']);
$email = $_SESSION['email'];  // Check if session email exists
$old_pay_status = "Incomplete";
$new_pay_status = "Complete";

// Prepare and execute SQL to check for transaction
$check_stmt = $con->prepare("SELECT amount FROM payments WHERE reference_id = ? AND payment_status = ? AND user_id=?");
$check_stmt->bind_param("sss", $reference_id, $old_pay_status,$agent_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Fetch the result and get the pay amount
    $check_stmt->bind_result($pay_amount);
    $check_stmt->fetch();
    $pay_amount = $pay_amount;  
    
   // 2) Verify with Paystack
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
        header("Location: wallet.php?error=" . urlencode("cURL Error: " . $err) . "&reference_id=" . urlencode($paystack_reference_id));
        exit();
    }

    $responseData = json_decode($response, true);
    if (!isset($responseData['status']) || !$responseData['status'] || !isset($responseData['data'])) {
        header("Location: wallet.php?error=" . urlencode("Invalid Paystack response. Do Manual Top up") . "&reference_id=" . urlencode($paystack_reference_id));
        exit();
    }

    $transaction = $responseData['data'];

    // 3) Ensure success
    if ($transaction['status'] !== 'success') {
        header("Location: wallet.php?error=" . urlencode("Transaction not successful: " . $transaction['status']) . "&reference_id=" . urlencode($paystack_reference_id));
        exit();
    }

    // 4) Match reference via metadata
    $metadataTransactionID = null;
    if (isset($transaction['metadata']['custom_fields']) && is_array($transaction['metadata']['custom_fields'])) {
        foreach ($transaction['metadata']['custom_fields'] as $field) {
            if (!empty($field['value']) && stripos($field['variable_name'] ?? '', 'transaction') !== false) {
                $metadataTransactionID = trim($field['value']);
                break;
            }
        }
    }
    if (!$metadataTransactionID || $metadataTransactionID !== $reference_id) {
        header("Location: wallet.php?error=" . urlencode("Reference mismatch. Do Manual Top up") . "&reference_id=" . urlencode($paystack_reference_id));
        exit();
    }

    // 5) Confirm same email
    $txEmail = $transaction['customer']['email'] ?? '';
    if (strtolower($txEmail) !== strtolower($email)) {
        header("Location: wallet.php?error=" . urlencode("Unauthorized transaction. Do Manual Top up") . "&reference_id=" . urlencode($paystack_reference_id));
        exit();
    }

    // 6) Use amount from Paystack and deduct 2%
    $amount_paid_pesewas = intval($transaction['amount']); // e.g. 10000 = 100.00 GHS
    $amount_in_ghs = $amount_paid_pesewas / 100.0;
    $paystack_fee = round($amount_in_ghs * 0.0195, 2); // 2% charge
    $amount_after_fee = round($amount_in_ghs - $paystack_fee, 2);

    // Query to aggregate and rank agents by total orders and sum of order amounts
    $selectquery = " SELECT * from agents_transactions where reference_id='$reference_id'";
    $select = mysqli_query($con, $selectquery);
    if (!$select) {
    die("Query failed: " . mysqli_error($con));
    }
    if (mysqli_num_rows($select) > 0) {
    $error = "Wallet already top-up";
    header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
    exit();
    }else {
        $stmt = $con->prepare("SELECT * FROM agents WHERE user_id=?");
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $wallet = $row['wallet'];
            $walletf = round($wallet + $amount_after_fee, 2);
                $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,user_id,agent_name,email,amount,wallet,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssddss", $paystack_reference_id,$agent_id,$agent_name,$email,$amount_after_fee,$walletf,$trans_date,$purpose);
                $iquery = $stmt->execute();
                if ($iquery) {
            $stmt = $con->prepare("UPDATE agents SET amount=?, wallet = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $amount_after_fee,$walletf, $agent_id);
            $uquery = $stmt->execute();
            if ($uquery) {
                 $stmt = $con->prepare("UPDATE payments SET payment_status=? WHERE reference_id=? AND user_id=?");
                 $stmt->bind_param("sss", $new_pay_status, $reference_id, $agent_id); // Correct order of parameters
                 $pquery = $stmt->execute();
                 if ($pquery) {
                    unset($_SESSION['reference_id']);
                    unset($_SESSION['amount']);
                    
                    $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.powermynetworks.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@powermynetworks.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
                    $mail->addAddress($email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "WALLET TOP-UP";
                    $mail->Body = "
                    <html>
                        <body>
                            <div class='container-xxl bg-white p-0'>
                            <div class='container-xxl position-relative p-0'>
                              <br>
                          <p>Dear Agent,</p>
                          <p>You have top up your wallet with GHS $amount_after_fee.</p>
                          <p>Your current wallet balance is GHS $walletf</p>
                            <p>Have a great day!</p>
                                  <!-- Footer Start -->
                            <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                            <div class='container'>
                                <div class='copyright'>
                                    <div class='row'>
                                        <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                            &copy; <a class='border-bottom'>POWERMYNET </a>, All Right Reserved.
                                        </div>
                                        <div class='col-md-6 text-center text-md-end'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer End -->
                       </body>
                    </html>";

                    $mail->send();
                    $success = "Wallet top-up successful";
                    header("Location: wallet.php?success=$success&reference_id=$paystack_reference_id");
                    exit();
                } catch (Exception $e) {
                    $errors['mail_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                    
                 } else {
                     $error = "Payment update failed:. Do Manual Top up " . $stmt->error;
                     header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
                 }
                    
                } 
                else {
                $error = "Wallet top-up update failed:. Do Manual Top up " . $stmt->error;
                header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
            }
                }
                else {
                    $error = "Failed to add to transaction history:. Do Manual Top up " . $stmt->error;
                    header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
                }
            
        } else {
            $error = "Agent Account not found!. Do Manual Top up";
            header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
        }
    }

}
}
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
				  
        <!-- Spinner -->
        <div class="loading-overlay" id="loading-overlay">
            <div class="spinner"></div>
            <div class="loading-text">Processing...</div>
        </div>

<!-- Spinner CSS styling -->
    <style>
/* Ensure the spinner is visible while loading */
.loading-overlay {
    display: flex; /* Show by default */
    position: fixed; /* Keep it in place */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
    z-index: 9999; /* Ensure it is above all content */
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    animation: fadeOut 3s forwards; /* Fades out the spinner after 3 seconds */
    backdrop-filter: blur(5px); /* Apply blur effect to background */
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

/* Fade out effect */
@keyframes fadeOut {
    0% { opacity: 1; }
    100% { opacity: 0; display: none; }
}
</style>


				</div>
			</div>
		</div>

		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>
		<!-- Daterange -->
		<script src="vendor/daterange/daterange.js"></script>
		<script src="vendor/daterange/custom-daterange.js"></script>
		<!-- Chartist JS -->
		<script src="vendor/chartist/js/chartist.min.js"></script>
		<script src="vendor/chartist/js/chartist-tooltip.js"></script>
		<script src="vendor/chartist/js/custom/threshold/threshold.js"></script>
		<script src="vendor/chartist/js/custom/bar/bar-chart-orders.js"></script>
		<!-- jVector Maps -->
		<script src="vendor/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
		<script src="vendor/jvectormap/world-mill-en.js"></script>
		<script src="vendor/jvectormap/gdp-data.js"></script>
		<script src="vendor/jvectormap/custom/world-map-markers2.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>

	</body>
</html>
