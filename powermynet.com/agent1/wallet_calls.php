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
    $mins = mysqli_real_escape_string($con, $_POST['mins']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $recipient_number = mysqli_real_escape_string($con, $_POST['beneficiary']);
    $medium = "Excel";
    $purpose = "Order Payment";
    // Order details
    $status = "Pending";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');
    $date4 = date('ymd');
    $time = date('his');
    $to = bin2hex(random_bytes(2));
    $tok = bin2hex(random_bytes(2));

                            // Check if the agent exists
    if (!$agent_email || !$recipient_number || !$mins || !$price) {
        $errors[] = 'Invalid input. Please check your data.';
    } else {
        
    $selects = "SELECT * FROM call_offers WHERE mins='$mins'";
    $sqls = mysqli_query($con, $selects);
    $checks = mysqli_num_rows($sqls) > 0;
    if ($checks) {
        while ($rows = mysqli_fetch_array($sqls)) {

    $select = "SELECT * FROM agents WHERE user_id='$agent_id'";
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
                // Prepare order details

            $wallet = $row['wallet'];
            $credit = $row['credit'];
            // Insert order into database
            if ($insertordersItems = $con->prepare("INSERT INTO call_orders (reference_id,agent_id,agent_name,agent_tel,agent_email,beneficiary,mins,price,order_time,order_date,real_date,order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")) {
                $insertordersItems->bind_param("sssssssdssss", $reference_id,$agent_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$mins,$price,$order_time,$order_date,$real_date,$status);
                if ($insertordersItems->execute()) {
                    $walletf = $wallet - $price;
                    $creditf = $credit - $price;
                    if ($stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?")) {
                        $stmt->bind_param("dds", $walletf, $creditf, $agent_id);
                        if ($stmt->execute()) {
                            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) VALUES (?,?,?,?,?,?,?,?,?,?)");
                            $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $agent_id, $agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                            $iquery = $stmt->execute();
                            if ($iquery) {
                                $success = "Order made successfuly";
                                header("Location: calls.php?success=$success");
                                exit();
                            } else {
                                        $error = "Failed to insert into transactions:: " . $stmt->error;
                                        header("Location: wallet_calls.php?error=$error");
                            }
                        } else {
                                    $error = "Failed to update wallet: " . $stmt->error;
                                    header("Location: wallet_calls.php?error=$error");
                        }
                    } else {
                                $error = "Database error: unable to prepare wallet update statement: " . $stmt->error;
                                header("Location: wallet_calls.php?error=$error");
                    }
                } else {
                            $error = "Failed to purchase package: " . $stmt->error;
                            header("Location: wallet_calls.php?error=$error");
                }
                        $insertordersItems->close();
            } else {
                        $error = "unable to prepare order insertion statement: " . $stmt->error;
                        header("Location: wallet_calls.php?error=$error");
            }

    // Close database connection
    $con->close();
        }
      }
     }
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
				    
					<!-- Row start -->

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 
                                
				<div class="row gutters">
					<div class="col-lg-6 col-sm-12 col-12">
						<div class="card h-100" style="border-radius: 25px;">
							<div class="card-header bg-info">
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
                    <div class="text-warning"><?php echo $row['first_name']." ".$row['last_name']; ?></div>
                    <br>
                      <div class="text-white" style="font-size:15px; font-weight:bolder;"><i class="fa fa-credit-card-alt" ></i> GHS <?php echo number_format($row['wallet'], 2); ?></div>
                      <br>
                <?php
                }
              }
            ?>  
									
	    			</div>
				</div>
				<div class="card-body">

				    <div class="row">
						<div class="col-sm-6 col-6">
						   <h6>Mins</h6>
					    </div>
					    	<div class="col-sm-6 col-6 float-right">
						    <h5><?php echo $_SESSION['amins'] ?>Mins</h5>
					    </div>
					</div>
					<hr>
					<div class="row">
						<div class="col-sm-6 col-6">
						   <h6>Price</h6>
					    </div>
					    	<div class="col-sm-6 col-6 float-right">
						    <h5>GHS <?php echo $_SESSION['aprice'] ?></h5>
					    </div>
					</div>
					<hr>
					<div class="row">
						<div class="col-sm-6 col-6">
						   <h6>Recipient Number</h6>
					    </div>
					    	<div class="col-sm-6 col-6 float-right">
						    <h5><?php echo $_SESSION['abeneficiary'] ?></h5>
					    </div>
					</div>
					<hr>

					 </div>
					 
					 			<?php
            include '../database/dbconfig.php';
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM agents WHERE user_id='$user_id' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                $wallet = $row['wallet'];
                $price=$_SESSION['aprice'];
                if($price>$wallet){
                    ?>
                    <div class="p-2 bg-danger text-white">Your Balance is low. Top up wallet to proceed to purchase package with yout wallet</div>
                    <?php
                }
                else{
                    ?>
                    <form action="" method="POST" enctype="multipart/form-data" >
                    <div class="row gutters">
				     <input type="hidden" class="form-control" id="wallet" name="wallet" value="<?php echo  $_SESSION['wallet'] ?>" readonly required>
                     <input type="hidden" class="form-control" id="price" name="price" value="<?php echo $_SESSION['aprice'] ?>" readonly>
                    <input type="hidden" class="form-control" name="mins" value="<?php echo $_SESSION['amins'] ?>" readonly>
                     <input type="hidden" class="form-control" name="beneficiary" value="<?php echo $_SESSION['abeneficiary'] ?>" readonly>
                    <div class="row">
                   <div class="col-sm-12">
                        <button type="submit" name="submit" class="btn btn-info btn-block btn-lg" id="btnSpinner">Confirm and Send</button>
                    </div>
                  </div>
           </form>
                    <?php
                }
                }
              }
            ?> 
				
								</div>
							</div>
						</div>

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
		
			<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
</div>
    
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