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
include 'config.php';  
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

$errors = array();
$success = array();

if (isset($_POST['submit'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $gig = mysqli_real_escape_string($con, $_POST['gig']);
    $network = mysqli_real_escape_string($con, $_POST['network']);
    $recipient_number = mysqli_real_escape_string($con, $_POST['beneficiary']);
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    $mode = "Wallet";
    $purpose = "Order";
    $customer_email = "NONE";
    $account_type = "Agent";
    
    $origin = "SITE";

    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');

    if (!$reference_id || !$recipient_number || !$network || !$gig) {
        $error = "Invalid input. Please check your data.";
        header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
        exit();
    } else {
    $target = "AGENT";
    $stmt = $con->prepare("SELECT amount, original FROM prices WHERE network = ? AND package = ? AND target=?");
        if (!$stmt) {
            $error = "Failed to prepare price lookup statement.";
            log_error($error, $agent_id, $agent_name, $agent_email);
            header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
            exit();
        }
        $stmt->bind_param("sss", $network, $gig, $target);
        $stmt->execute();
        $stmt->bind_result($price, $original_price);
        $stmt->fetch();
        $stmt->close();

        if (empty($price) || $price <= 3) {
            $error = "Price not found for this package and network.";
            log_error($error, $agent_id, $agent_name, $agent_email);
            header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
            exit();
        }
        
    $select = "SELECT * FROM agents WHERE user_id='$agent_id'";
    $sql = mysqli_query($con, $select);
    $check = mysqli_num_rows($sql) > 0;
    if ($check) {
    while ($row = mysqli_fetch_array($sql)) {
        if ($row['wallet'] < $price) {
            // If the wallet balance is less than the purchase price, an error message is added
            $error = "Purchase failed. You do not have sufficient wallet balance";
            header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
            exit();
        } else {

    $wallet = $row['wallet'];
    $admin_profit = $price - $original_price;
         
    $selectss = "SELECT mtn FROM api_switch";
    $sqlss = mysqli_query($con, $selectss);
    $checkss = mysqli_num_rows($sqlss) > 0;
    if ($checkss) {
        while ($rowss = mysqli_fetch_array($sqlss)) {
        if($rowss['mtn']=="API"){
            $status = "Pending";
            $medium = "API";

$agent_api = 'REDACTED';
require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.dataworksgh.com/api/tyler.php', 'mtn');
$data = [
    'recipient_number' => $recipient_number,
    'gig' => $gig
];
$jsonData = json_encode($data);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $agent_api,
    ],
    CURLOPT_POSTFIELDS     => $jsonData,
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);

$decoded = json_decode($response, true);
// Get message from response body (if available)
$code = $decoded['code'] ?? 'Unknown response';
$message = $decoded['message'] ?? 'Unknown response';
$reference = $decoded['order_id'] ?? 'Unknown response';
if ($code == 200) {
            // Insert order into database
            if ($insertordersItems = $con->prepare("INSERT INTO orders_agent (reference_id,agent_id,agent_name,agent_tel,agent_email,beneficiary,network,gig,price, order_time, order_date, real_date, order_status, mode,account_type,original,profit,medium,origin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?)")) {
                $insertordersItems->bind_param("ssssssssdssssssdsss", $reference, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode,$account_type,$original_price,$admin_profit,$medium,$origin);
                if ($insertordersItems->execute()) {
                    $walletf = $wallet - $price;
                    if ($stmt = $con->prepare("UPDATE agents SET wallet = ? WHERE user_id = ?")) {
                        $stmt->bind_param("ds", $walletf, $agent_id);
                        if ($stmt->execute()) {
                            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssssddss", $reference, $agent_id, $agent_name, $agent_email, $price, $walletf, $real_date, $purpose);
                            $iquery = $stmt->execute();
                            if ($iquery) {
                                $success = "Order made successfuly.";
                                header("Location: my_orders.php?success=$success&reference_id=$reference");
                                exit();
                            } 
                            else {
                                    $error = "Order Failed. Failed to insert into transactions:: " . $stmt->error;
                                   header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
                            }
                        } else {
                                    $error = "Order Failed. Failed to update wallet: " . $stmt->error;
                                    header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
                        }
                        } else {
                                    $error = "Order Failed. Database error: unable to prepare wallet update statement: " . $stmt->error;
                                    header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
                        }
                       } else {
                                    $error = "Order Failed. Failed to purchase package: " . $stmt->error;
                                    header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
                       }
                                    $insertordersItems->close();
                     } else {
                                $error = "Order Failed. unable to prepare order insertion statement: " . $stmt->error;
                                header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
                            }
                    } else {
                header("Location: wallet_mtn_pay.php?error=" . urlencode("$code, $message"));
                exit();
              }
        }
elseif ($rowss['mtn'] == "EXCEL") {
    $status = "Pending";
    $medium = "EXCEL";

    // Begin transaction
    $con->begin_transaction();

    try {
        // Insert order
        $insertOrderSQL = "INSERT INTO orders_agent (reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, 
        order_date, real_date, order_status, mode, account_type, original, profit, medium, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if (!$insertordersItems = $con->prepare($insertOrderSQL)) {
            throw new Exception("Failed to prepare order insertion: " . $con->error);
        }

        $insertordersItems->bind_param("ssssssssdssssssdsss",$reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, 
        $price,$order_time, $order_date, $real_date, $status, $mode, $account_type, $original_price, $admin_profit, $medium, $origin);
        if (!$insertordersItems->execute()) {
            throw new Exception("Failed to execute order insertion: " . $insertordersItems->error);
        }
        // Update wallet
        $walletf = $wallet - $price;
        $updateWalletSQL = "UPDATE agents SET wallet = ? WHERE user_id = ?";
        if (!$stmt = $con->prepare($updateWalletSQL)) {
            throw new Exception("Failed to prepare wallet update: " . $con->error);
        }
        $stmt->bind_param("ds", $walletf, $agent_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update wallet: " . $stmt->error);
        }
        // Insert into transactions
        $transSQL = "INSERT INTO agents_transactions (reference_id, user_id, agent_name,email,amount,wallet,trans_date,purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if (!$stmt = $con->prepare($transSQL)) {
            throw new Exception("Failed to prepare transaction insertion: " . $con->error);
        }
        $stmt->bind_param("ssssddss", $reference_id, $agent_id, $agent_name, $agent_email, $price, $walletf, $real_date, $purpose);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert transaction: " . $stmt->error);
        }

        // Commit all
        $con->commit();

        // Success redirect
        $success = "Order made successfully.";
        header("Location: my_orders.php?success=$success&reference_id=$reference_id");
        exit();

    } catch (Exception $e) {
        $con->rollback();
        $error = "Order Failed: " . $e->getMessage();
        header("Location: wallet_mtn_pay.php?error=" . urlencode($error));
        exit();
          }
         }
        }
       }
     }
    }
  }
}
    // Close database connection
    $con->close();
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
				    
					<!-- Row start -->
								<?php 
								if(isset($_GET['error'])){
								?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <div><?php echo $_GET['error']; ?></div>
                                   </div>
                                <?php } ?> 
                                
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
              <?php
                include '../database/dbconfig.php';
                $package = $_SESSION['apackage'];
                $target = "AGENT";
                $network = $_SESSION['anetwork'];
                $select = "SELECT * FROM prices WHERE network='$network' AND target='$target' AND package='$package'";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $_SESSION['price'] = $row['amount'];
                     ?> 
                <div class="row mb-3">
                    <div class="col-6 text-muted">Network</div>
                    <div class="col-6 text-right font-weight-bold">
                        <?php echo $_SESSION['anetwork'] ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6 text-muted">Package</div>
                    <div class="col-6 text-right font-weight-bold">
                        <?php echo $_SESSION['apackage'] ?> GB
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6 text-muted">Price</div>
                    <div class="col-6 text-right font-weight-bold text-success">
                        GHS <?php echo $row['amount'] ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6 text-muted">Recipient Number</div>
                    <div class="col-6 text-right font-weight-bold">
                        <?php echo $_SESSION['abeneficiary'] ?>
                    </div>
                </div>
                       <?php 
                    } 
                } 
                ?>

                <hr>

                <!-- Wallet Status -->
                <?php
                include '../database/dbconfig.php';
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $wallet = $row['wallet'];
                        $price = $_SESSION['price'];
                        if ($price > $wallet) {
                            echo '<div class="alert alert-danger mt-3">
                                    <i class="fa fa-exclamation-circle"></i> Your balance is low. Top up your wallet to proceed.
                                  </div>';
                        } else {
                            ?>
                            <form action="" method="POST" enctype="multipart/form-data">
                               <input type="hidden" name="reference_id" value="<?php echo $_SESSION['reference_id'] ?>" readonly>
                                <input type="hidden" name="gig" value="<?php echo $_SESSION['apackage'] ?>" readonly>
                                <input type="hidden" name="network" value="<?php echo $_SESSION['anetwork'] ?>" readonly>
                                <input type="hidden" name="beneficiary" value="<?php echo $_SESSION['abeneficiary'] ?>" readonly>
                            <?php
                        }
                    }
                }
                ?>
            </div>

            <!-- Sticky Footer Button -->
           <div class="sticky-footer p-3">
                
                              <?php
                include '../database/dbconfig.php';
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $wallet = $row['wallet'];
                        $price = $_SESSION['price'];
                        if ($price > $wallet) {
                        ?>
                        <div class="alert alert-danger mt-3">
                        <i class="fa fa-exclamation-circle"></i> Your balance is low. Top up your wallet to proceed.
                        </div>
                        <?php
                        } else {
                            ?>
                <button type="submit" name="submit" class="btn btn-info btn-lg btn-block" id="btnSpinner">
                    <i class="fa fa-paper-plane"></i> Confirm and Send
                </button>
                            <?php
                        }
                    }
                }
                ?>
                
            </div>
             </form>
        </div>
    </div>
</div>

<style>
/* Make card full screen on mobile */
@media (max-width: 768px) {
    .order-card {
        height: 100vh;
        border-radius: 0;
        display: flex;
        flex-direction: column;
    }
    .order-card .card-body {
        flex-grow: 1;
        overflow-y: auto;
        padding-bottom: 100px; /* to avoid overlap with sticky button */
    }
    .sticky-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }
}
</style>
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
		
				<script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
    function submitPriceAndPay() {
        payPageWithPaystack();
    }
    function payPageWithPaystack() {
        // Ensure amount is retrieved from PHP
        const priceInput = parseFloat("<?php echo isset($_SESSION['aprice']) ? $_SESSION['aprice'] : 0; ?>");
        // Validate amount before proceeding
        if (!priceInput || priceInput < 1) {
            alert("Amount must not be less than 10 GHS");
            return;
        }
        // Fetch Paystack API key from PHP
        const api = "<?php echo PAYSTACK_PUBLIC_KEY; ?>";
        var handler = PaystackPop.setup({
            key: api,
            email: "<?php echo $_SESSION['email']; ?>",
            amount: priceInput * 100, // Convert to kobo
            currency: "GHS",
            first_name: "<?php echo $_SESSION['first_name']; ?>",
            last_name: "<?php echo $_SESSION['last_name']; ?>",
            telephone: "<?php echo $_SESSION['telephone']; ?>",
            metadata: {
                custom_fields: [
                    { display_name: "First Name", variable_name: "first_name", value: "<?php echo $_SESSION['first_name']; ?>" },
                    { display_name: "Last Name", variable_name: "last_name", value: "<?php echo $_SESSION['last_name']; ?>" },
                    { display_name: "Email", variable_name: "email", value: "<?php echo $_SESSION['email']; ?>" },
                    { display_name: "Telephone", variable_name: "telephone", value: "<?php echo $_SESSION['telephone']; ?>" },
                    { display_name: "Amount", variable_name: "amount", value: priceInput },
                ]
            },
            callback: function(response) {
                const paystackReference = response.reference; // Paystack's reference ID

                // Redirect to processing page with both references
                window.location.href = `wallet_mtn_pay.php?paystack_ref=${encodeURIComponent(paystackReference)}`;
            },
            onClose: function() {
                alert('Payment window closed');
            }
        });
        handler.openIframe();
    }
    </script>
		
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