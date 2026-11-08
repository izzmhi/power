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

require 'config.php';
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
if (isset($_POST['check'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    $paystack_reference_id = mysqli_real_escape_string($con, $_POST['paystack_reference_id']);
    $amounts = mysqli_real_escape_string($con, $_POST['amount']);
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $telephone = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $agent_name = $first_name . " " . $last_name;
    $purpose = "RE-Top-up";
    $trans_date = date('Y-m-d');
    
    $old_pay_status = "Incomplete";
    $new_pay_status = "Complete";

// Prepare and execute SQL to check for transaction
$check_stmt = $con->prepare("SELECT amount FROM payments WHERE reference_id = ? AND user_id=?");
$check_stmt->bind_param("ss", $reference_id, $agent_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Fetch the result and get the pay amount
    $check_stmt->bind_result($pay_amount);
    $check_stmt->fetch();
    $amount = $pay_amount; 
        // Query to aggregate and rank agents by total orders and sum of order amounts
    $selectquery = " SELECT * from agents_transactions where reference_id='$paystack_reference_id' AND email='$email'";
    $select = mysqli_query($con, $selectquery);
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
            $walletf = $wallet + $amount;
            $stmt = $con->prepare("UPDATE agents SET amount=?, wallet = ? WHERE user_id = ?");
            $stmt->bind_param("dds", $amount,$walletf, $agent_id);
            $uquery = $stmt->execute();
            if ($uquery) {
                $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,user_id,agent_name,email,amount,wallet,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssddss", $paystack_reference_id,$agent_id,$agent_name,$email,$amount,$walletf,$trans_date,$purpose);
                $iquery = $stmt->execute();
                if ($iquery) {
                $stmt = $con->prepare("UPDATE payments SET payment_status=? WHERE reference_id=? AND user_id=?");
                 $stmt->bind_param("sss", $new_pay_status, $reference_id, $agent_id); // Correct order of parameters
                 $pquery = $stmt->execute();
                 if ($pquery) {
                        $success = "Wallet top-up successful";
                        header("Location: wallet.php?success=$success&reference_id=$paystack_reference_id");
                 } else {
                     $error = "Payment update failed: " . $stmt->error;
                     header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
                 }

                } else {
                    $error = "Failed to add to transaction history: " . $stmt->error;
                    header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
                    exit();
                }
            } else {
                $error = "Wallet top-up update failed: " . $stmt->error;
                header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
                exit();
            }
        } else {
            $error = "Agent Account not found!";
            header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
            exit();
        }
    }
}

else {
    // Transaction not found
    $error = "Transaction not found";
    header("Location: wallet.php?error=$error&reference_id=$paystack_reference_id");
    exit();
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
				    
<!-- Full Screen Modal for Mobile -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content rounded-3 shadow-lg">
      
      <!-- Modal Header -->
      <div class="modal-header bg-info text-white d-flex justify-content-between">
        <h5 class="modal-title" id="exampleModalLabel">
          <i class="fa fa-money mr-2"></i> Enter Amount
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <!-- Modal Body -->
      <form action="processing.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <div class="form-group">
            <label for="price" class="font-weight-bold">
              Amount <small class="text-muted">(Minimum GHS 10)</small>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-cedi-sign"></i></span>
              </div>
              <input type="number" class="form-control form-control-lg" id="price" name="price" min="10" step="1" placeholder="Enter amount" required>
            </div>
            <small class="form-text text-muted mt-2">Ensure the amount is not less than GHS 10.</small>
          </div>
        </div>
        
        <!-- Modal Footer -->
<div class="modal-footer border-0 p-3 d-flex flex-column gap-2">
    <button type="button" class="btn btn-danger border w-100 mb-2" data-dismiss="modal">
        <i class="fa fa-times"></i> Cancel
    </button>
    <button type="submit" name="submit_amount" class="btn btn-info w-100">
        <i class="fa fa-check"></i> Continue
    </button>
</div>

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS for Fullscreen on Mobile -->
<style>
.modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
}

.modal-footer {
    position: sticky;
    bottom: 0;
    background: #fff;
    border-top: 1px solid #ddd;
    z-index: 100;
}
@media (max-width: 768px) {
  .modal-dialog {
    max-width: 100%;
    margin: 0;
    height: 100%;
  }
  .modal-content {
    height: 100%;
    border-radius: 0;
  }
  .modal-body {
    overflow-y: auto;
  }
}
</style>

				    
				    <?php
				    if(isset($_GET['success'])){
				        unset($_SESSION['reference_id']);
				        unset($_SESSION['amount']);
				        ?>
				            <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                               <?php echo $_GET['success']; ?>  | This is your reference id: <?php echo $_GET['reference_id']; ?>
                            </div>
				        <?php
				    }
				    else if(isset($_GET['error'])){
				    ?>
				        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                             <?php echo $_GET['error']; ?> | This is your reference id: <?php echo $_GET['reference_id']; ?>
                        </div>
				        <?php
				    }
				    ?>

				<div class="row gutters">
					<div class="col-lg-6 col-sm-12 col-12 mb-3">
						<div class="card h-100" style="border-radius: 15px;">
							<div class="card-header bg-info">
								<div class="card-title">
                                     <h5 class="text-white">Wallet Top-up</h5>
								</div>
							</div>
							
							<?php 
							if(isset($_SESSION['amount']) && isset($_SESSION['reference_id'])) {
							    
							    if(!isset($_GET['success'])){
							    ?>
							      	<div class="card-body">
								    <h6>Please wait for the payment processing to complete before closing it</h6>
								    <h6>Amount: GHS <?php echo $_SESSION['amount']; ?></h6>
								    <h6>Reference <?php echo $_SESSION['reference_id']; ?></h6>
                                <form action="" method="POST" enctype="multipart/form-data" id="priceForm">
                                    <div class="row gutters">
                                        <div class="col-sm-12">
                                            <div class="text-left">
                                                <script src="https://js.paystack.co/v1/inline.js"></script>
                                                <button type="button" name="sub" class="btn btn-primary" onClick="submitPriceAndPay()" id="add">Pay</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
							</div>
							<?php
							    }
							    else{
							        
							        ?>
							<div class="card-body">
							       <h6>Click to top up wallet</h6>
							   </div>
							<?php
							    }
							    

							}
							else{
							?>
							   <div class="card-body">
							       <h6>Click to top up wallet</h6>
							   </div>
							   
							
							<?php
							}
							
							?>
							
						</div>
					</div>
					
					<div class="col-lg-6 col-sm-12 col-12">
						<div class="card h-100" style="border-radius: 15px;">
							<div class="card-header bg-success">
								<div class="card-title text-white">Top Up Failure (Incase your wallet top up fails, use this space. <strong>You will not pay for it again</strong>)</div>
							</div>
								<div class="card-body">
								    <br>
                                  <h6>Search Transaction by Paystack Reference ID</h6>
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-sm-6 col-8">
                                                    <input type="text" class="form-control" name="reference_id" id="reference_id" placeholder="Reference id" required>
                                             </div>
                                             <div class="col-sm-6 col-4">
                                                    <button class="btn btn-primary" type="submit">Search</button>
                                             </div>
                                        </div>
                                    </form>


<?php
$email = $_SESSION['email'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['reference_id'])) {
    // Get the user-inputted reference ID
    $reference_id = htmlspecialchars($_POST['reference_id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,  // Use the stored key
            "Cache-Control: no-cache",
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        // Decode JSON response
        $responseData = json_decode($response, true);
        if ($responseData['status']) {
            $transaction = $responseData['data'];
            $metadataTransactionID = $transaction['metadata']['custom_fields'][5]['value'] ?? null;

            if ($transaction['customer']['email'] == $email) {
                // Only display results if the status is not "abandoned" or "failed"
                if (!in_array($transaction['status'], ['abandoned', 'failed'])) {
                    $originalAmount = $transaction['amount'] / 100;  // Convert amount from kobo to naira
                    $deduction = $originalAmount * 0.03;  // Deduct 3%
                    $finalAmount = $originalAmount - $deduction;
                    $finalAmountFormatted = number_format($finalAmount, 2);

                    // Get the transaction date and compare with current date
                    $transactionDate = new DateTime($transaction['paid_at']);
                    $currentDate = new DateTime();
                    if ($transactionDate <= $currentDate) {

                        ?>

                        <!-- Form to display transaction reference and amount details -->
                        <form class="mt-2" action="" method="POST" enctype="multipart/form-data">
                            <div class="row gutters">
                                <div class="col-sm-12 col-12">
                                    <div class="form-group">
                                        <input type="hidden" class="form-control" value="<?php echo htmlspecialchars($metadataTransactionID); ?>" name="reference_id" readonly required>
                                        <input type="hidden" class="form-control" value="<?php echo htmlspecialchars($transaction['reference']); ?>" name="paystack_reference_id" readonly required>
                                        <input type="hidden" class="form-control" value="<?php echo $finalAmountFormatted; ?>" name="amount" readonly required>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="text-right">
                                        <button type="submit" name="check" class="btn btn-block btn-info">Try Top Up Again</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <?php
                    } else {
                        echo "Transaction status is '" . htmlspecialchars($transaction['status']) . "'. No details available.";
                    }
                } else {
                    echo "Transaction status is '" . htmlspecialchars($transaction['status']) . "'. No details available.";
                }
            } else {
                echo "Failed: You cannot use somebody else's reference ID.";
            }

        } else {
            echo "Reference ID not found: " . htmlspecialchars($responseData['message']);
        }
    }
}
?>


							</div>
						</div>
					</div>
					
				</div>
		    </div>
			<!-- Page content end -->

		</div>
		
	<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
</div>	
		
<?php
if (isset($_SESSION['amount']) && isset($_SESSION['reference_id'])) {
$base_amount = $_SESSION['amount']; // what you want to receive
$fee_percent = 0.0195; // 1.95%
$extra_fee = 0.5; // flat fee

// Add Paystack charges to customer
$final_amount = ($base_amount + $extra_fee) / (1 - $fee_percent);

$_SESSION['final_amount'] = round($final_amount, 2); // round to 2 decimals
?>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
    function submitPriceAndPay() {
        payPageWithPaystack();
    }
    function payPageWithPaystack() {
        // Ensure amount is retrieved from PHP
        const priceInput = parseFloat("<?php echo isset($_SESSION['amount']) ? $_SESSION['amount'] : 0; ?>");
        const transactionID = "<?php echo $_SESSION['reference_id']; ?>"; // Your custom transaction ID
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
                    { display_name: "Transaction ID", variable_name: "transaction_id", value: transactionID } // Your custom transaction ID
                ]
            },
            callback: function(response) {
                const paystackReference = response.reference; // Paystack's reference ID

                // Redirect to processing page with both references
                window.location.href = `wallet_top_up_processing.php?transaction_id=${encodeURIComponent(transactionID)}&paystack_ref=${encodeURIComponent(paystackReference)}`;
            },
            onClose: function() {
                alert('Payment window closed');
            }
        });
        handler.openIframe();
    }
    </script>

<?php
}
?>

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