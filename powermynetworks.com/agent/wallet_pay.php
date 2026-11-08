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
    $real_package = mysqli_real_escape_string($con, $_POST['real_package']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $network = mysqli_real_escape_string($con, $_POST['network']);
    $recipient_number = mysqli_real_escape_string($con, $_POST['beneficiary']);
    $mode = "Wallet";
    $purpose = "Order Payment";
    $customer_email = "NONE";
    
    // Order details
    $status = "Delivered";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');
    $date4 = date('ymd');
    $time = date('his');
    $to = bin2hex(random_bytes(2));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to . $date4 . $tok . $time;

    if (!$agent_email || !$recipient_number || !$network || !$gig || !$price) {
        $errors[] = 'Invalid input. Please check your data.';
    } else {
        
$select = "SELECT wallet FROM agents WHERE user_id='$agent_id'";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;
if ($check) {
    while ($row = mysqli_fetch_array($sql)) {
        if ($row['wallet'] < $price) {
            // If the wallet balance is less than the purchase price, an error message is added
            $errors["Purchase"] = 'Purchase failed. You do not have sufficient wallet balance';
        } else {
            
                         // Get the last five characters
    $last_2_chars = substr($recipient_number, -2);
    $first_five_chars = substr($recipient_number, 0, 5);
    $seconds = date('His');
    $reference_id = $first_five_chars.$seconds.$last_2_chars;
    
            // API endpoint URL
    $url = 'https://www.value4moni.com/api/v1/inititate_transaction';
    // Data to send to the API
    $data = array(
        "API_Key" => "REDACTED",
        "Receiver" => $recipient_number,
        "Volume" => $real_package,
        "Reference" => $reference_id,
        "Package_Type" => $network
    );
    // Initialize cURL session
    $ch = curl_init($url);
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the content type to JSON
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode data as JSON
    // Execute the cURL request
    $response = curl_exec($ch);
    // Check if any error occurred
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Handle the response from the API
                // Insert order into the database using prepared statements
      
                                 // Check if the agent exists
    if ($stmt = $con->prepare("SELECT * FROM agents WHERE user_id=?")) {
        $stmt->bind_param("s", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $wallet = $row['wallet'];
            $credit = $row['credit'];

            // Insert order into database
            if ($insertordersItems = $con->prepare("INSERT INTO orders_agent (order_id,reference_id,agent_id,agent_name,agent_tel,agent_email,beneficiary,network,gig,price, order_time, order_date, real_date, order_status, mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                $insertordersItems->bind_param("sssssssssdsssss", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode);

                if ($insertordersItems->execute()) {
                    $walletf = $wallet - $price;
                    $creditf = $credit - $price;

                    if ($stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?")) {
                        $stmt->bind_param("dds", $walletf, $creditf, $agent_id);
                        if ($stmt->execute()) {
                     
                            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                            $iquery = $stmt->execute();

                            if ($iquery) {
                                
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress('codecraftnetwork1@gmail.com');
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Data Purchase | Using Wallet";
                    $mail->Body = "
                    <html>
                <body>
                   <div class='container-xxl bg-white p-0'>
                   <div class='container-xxl position-relative p-0'>
                     <br>
                  <p>Dear Admin,</p>
                 <p> Agent, $agent_name has bought $network $gig bundle worth GHS$price.00 for $recipient_number.</p>
                    <p>Have a great day!</p>
                         <!-- Footer Start -->
                   <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                       <div class='container'>
                           <div class='copyright'>
                               <div class='row'>
                                   <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                       &copy; <a class='border-bottom'>CODECRAFTNETWORK </a>, All Right Reserved. 
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
                    $success = "Order made successfuly.";
                                    header("Location: orders.php?success=$success&reference_id=$reference_id");
                                    exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

                            } 
                            
                            
                            else {
                                    $error = "Failed to insert into transactions:: " . $stmt->error;
                                   header("Location: orders.php?error=" . urlencode($error));
                            }
                        } else {
                                       $error = "Failed to update wallet: " . $stmt->error;
                                       header("Location: orders.php?error=" . urlencode($error));
                        }
                    } else {
                                          $error = "Database error: unable to prepare wallet update statement: " . $stmt->error;
                                         header("Location: orders.php?error=" . urlencode($error));
                    }
                } else {
                                            $error = "Failed to purchase package: " . $stmt->error;
                                            header("Location: orders.php?error=" . urlencode($error));
                }
                                              $insertordersItems->close();
            } else {
                                              $error = "unable to prepare order insertion statement: " . $stmt->error;
                                             header("Location: orders.php?error=" . urlencode($error));
            }
        } else {
                $error = "Agent information not found: " . $stmt->error;
                header("Location: orders.php?error=" . urlencode($error));
        }
        $stmt->close();
    } else {
                $error = "unable to prepare agent retrieval statement:: " . $stmt->error;
                header("Location: orders.php?error=" . urlencode($error));
    }

    }
    // Close cURL session
    curl_close($ch);
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

					<!-- Row start -->

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 
                                
              <h3>Payment</h3>

				<div class="row gutters">

						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Order Details</div>
								</div>
								<hr>
								<div class="card-body">
								    
		  <?php
            include '../database/dbconfig.php';
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                 <div style="font-size:20px; font-weight:bolder;">Wallet Balance: GHS <?php echo number_format($row['wallet'], 2); ?></div>
                      <hr>
                <?php
                }
              }
            ?> 
       
				    <div class="row gutters">
						<div class="col-sm-12 col-12 mb-3">
						   <h4>Network</h4>
						    <div style="font-size:17px"><?php echo $_SESSION['anetwork'] ?></div>
					    </div>
					    <div class="col-sm-12 col-12 mb-3">
						   <h4>Gig</h4>
						    <div style="font-size:17px"><?php echo $_SESSION['agig'] ?>GIG</div>
					    </div>
					    <div class="col-sm-12 col-12 mb-3">
						   <h4>Price</h4>
						    <div style="font-size:17px">GHS <?php echo $_SESSION['aprice'] ?></div>
					    </div>
					    <div class="col-sm-12 col-12 mb-3">
						   <h4>Recipient Number</h4>
						    <div style="font-size:17px"><?php echo $_SESSION['abeneficiary'] ?></div>
					    </div>
					 </div>
				
                    <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
                    <div class="row gutters">
				     <input type="hidden" class="form-control" id="wallet" name="wallet" value="<?php echo  $_SESSION['wallet'] ?>" readonly required>
                     <input type="hidden" class="form-control" id="price" name="price" value="<?php echo $_SESSION['aprice'] ?>" readonly>
                     <div id="balanceMessage" style="display: none; color: red;">Your Balance is low. Top up wallet to proceed to purchase package with yout wallet</div>
                     <br>
                     <br>
                    <input type="hidden" class="form-control" name="gig" value="<?php echo $_SESSION['agig'] ?>" readonly>
                    <input type="hidden" class="form-control" name="real_package" value="<?php echo $_SESSION['areal_package'] ?>" readonly>
                     <input type="hidden" class="form-control" name="network" value="<?php echo $_SESSION['anetwork'] ?>" readonly>
                     <input type="hidden" class="form-control" name="beneficiary" value="<?php echo $_SESSION['abeneficiary'] ?>" readonly>
                    <br>
                   
                    <div class="form-group mt-2" id="check_balance">
                       <label for="paymentConfirmation">
                          <input type="checkbox" id="paymentConfirmation" value="check" required>
                           Select to confirm payment
                     </label>
                    </div>
                    <hr>
                    
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var wallet = parseFloat(document.getElementById('wallet').value);
            var price = parseFloat(document.getElementById('price').value);
            var balanceMessage = document.getElementById('balanceMessage');
            var checkBalance = document.getElementById('check_balance');
            var spinm = document.getElementById('spinm');

            if (wallet < price) {
                balanceMessage.style.display = 'block';
                checkBalance.style.display = 'none'; // Hide the checkbox div
                spinm.style.display = 'none';
            } else {
                balanceMessage.style.display = 'none';
                checkBalance.style.display = 'block'; // Show the checkbox div if the wallet is sufficient
                spinm.style.display = 'block';
            }
        });
    </script>
    
<div id="status-container">
    <!-- The status message or button will be loaded here -->
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function checkStatus() {
        $.ajax({
            url: 'orders_activation.php', // The PHP file that will return the status
            method: 'GET',
            success: function(response) {
                $('#status-container').html(response);
            }
        });
    }

    // Check the status every 5 seconds (5000 milliseconds)
    setInterval(checkStatus, 2000);

    // Optionally, you can call it once to load the initial status
    checkStatus();
</script>
									
									
									</div>
                               </form>

								</div>
							</div>
						</div>
						
		<div class="loading-overlay" id="spinner" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
<!--spinner start-->

						
					</div>

  <script>
        document.getElementById('paymentForm').addEventListener('submit', function(event) {
            var spinner = document.getElementById('spinner');
            var submitButton = document.querySelector('button[type="submit"]');
            
            spinner.style.display = 'flex';

        });
    </script>

			
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