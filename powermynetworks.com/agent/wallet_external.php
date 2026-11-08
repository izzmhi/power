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
    $gig = 1;
    $price = 10;
    $network = "MTN";
    $recipient_number = "0554226398";
    $mode = "Wallet";
    $purpose = "Order Payment";
    $customer_email = "NONE";
    // Order details
    $status = "Pending";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');

            // If the wallet balance is sufficient, deduct the price and update the wallet balance
        $agent_api = "REDACTED";

                // Get the last five characters
        $last_2_chars = substr($recipient_number, -2);
        $first_five_chars = substr($recipient_number, 0, 5);
        $seconds = date('His');
        $reference_id = $first_five_chars.$seconds.$last_2_chars;

            // API data preparation
        $data = [
            'agent_api' => $agent_api,
            'recipient_number' => $recipient_number,
            'network' => $network,
            'gig' => $gig,
            'reference_id' => $reference_id,
        ];
        $jsonData = json_encode($data);
        $url = 'https://api.verdeaccess.com/api/transaction.php';
        
            // cURL setup and execution
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Get HTTP status code

if (curl_errno($ch)) {
    $errors[] = "cURL error: " . curl_error($ch);
    curl_close($ch);
    header("Location: wallet_external.php?error=" . urlencode($errors[0]));
    exit();
} else {
    $response = json_decode($response, true);
    curl_close($ch);

    if (isset($response['message'])) {
        $success_message = $response['message'];

        // Include HTTP status code in the redirect URL
        header("Location: wallet_external.php?success=$success_message&reference_id=$reference_id&http_code=$http_code");
        exit();
    } else {
        $error = "API response does not contain a message";
        header("Location: wallet_external.php?error=$error&http_code=$http_code");
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

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 
                                
                                        <?php if(isset($_GET['success'])){
                                   ?>
                                   <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                   <div><?php echo $_GET['success']; ?></div>
                                   </div>
                                  <?php 
                                 }
                                ?> 
                               <?php if(isset($_GET['error'])){
                                   ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <div><?php echo $_GET['error']; ?></div>
                                   </div>
                                  <?php 
                                 }
                                ?> 
                                
              <h3>Payment</h3>

				<div class="row gutters">

						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">
									<?php
            include '../database/dbconfig.php';
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                    ?>
                      <div class="text-success" style="font-size:20px; font-weight:bolder;">Wallet Balance: <i class="fa fa-credit-card-alt" ></i> GHS <?php echo number_format($row['wallet'], 2); ?></div>
                    <hr>
                <?php
                }
              }
            ?>  
									
									</div>
								</div>
								<div class="card-body">
								    
			<?php
            include '../database/dbconfig.php';
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                $_SESSION['wallet'] = $row['wallet'];
                }
              }
            ?> 
	
				
                    <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
                    <div class="row gutters">
				     
                    <br>

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