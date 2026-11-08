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
$selects = "select * from account_agents where user_id='$user_id' ";
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
    }
}


$success = array();
$errors = array();

if (isset($_POST['generate'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $date = date('ymd');
    $time = date('his');
    // Generate 6 random special characters for each variable
    $specialChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890|?';
    function generateSpecialCharacters($specialChars, $length = 6) {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $specialChars[rand(0, strlen($specialChars) - 1)];
        }
        return $result;
    }
    $c1 = generateSpecialCharacters($specialChars);
    $c2 = generateSpecialCharacters($specialChars);
    $c3 = generateSpecialCharacters($specialChars);
    $c4 = generateSpecialCharacters($specialChars);
    $c5 = generateSpecialCharacters($specialChars);
    // Combine into the API key
    $api = $date . $time . "-" . $c1 . "-" . $c2 . "-" . $c3 . "-" . $c4 . "-" . $c5;
    // Update the database
    $update = "UPDATE agents SET api='$api' WHERE user_id='$user_id'";
    $uquery = mysqli_query($con, $update);
    if ($uquery) {
        $success = "API Key Generated Successfully";
        header("Location: settings.php?success=$success");
        exit();
    } else {
        $error = "API Key generation failed.";
        header("Location: settings.php?error=$error");
        exit();
    }
}


if (isset($_POST['sms_request'])) {
    session_start(); // Ensure session started
    $user_id = $_SESSION['user_id'] ?? '';
    $first_name = $_SESSION['first_name'] ?? '';
    $last_name = $_SESSION['last_name'] ?? '';
    $agent_email = $_SESSION['email'] ?? '';
    $agent_tel = $_SESSION['telephone'] ?? '';
    $agent_name = $first_name . ' ' . $last_name;
    $sender_id = strtoupper(trim($_POST['sender_id'] ?? ''));
    $real_date = date('Y-m-d');
    $request_status = 'PENDING';
    $activeness = 'INACTIVE';

    if (empty($sender_id)) {
        header("Location: settings.php?error=Sender Name is required");
        exit();
    }

    // Prepared SELECT
    $stmt = $con->prepare("SELECT * FROM agents_sms WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: settings.php?error=You already have a SENDER ID");
        exit();
    }

    // Prepared INSERT
    $insert = $con->prepare("INSERT INTO agents_sms (user_id, agent_name, email, telephone, sender_id, request_status, request_date, activeness) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssssss", $user_id, $agent_name, $agent_email, $agent_tel, $sender_id, $request_status, $real_date, $activeness);

    if ($insert->execute()) {
        header("Location: settings.php?success=Request made successfully");
    } else {
        header("Location: settings.php?error=Request failed");
    }
    exit();
}


if (isset($_POST['sms_pay'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $sender_id = mysqli_real_escape_string($con, $_POST['sender_id']);
    $year = mysqli_real_escape_string($con, $_POST['pay_year']);
    $month = mysqli_real_escape_string($con, $_POST['pay_month']);
    $pay_date = date('Y-m-d');
    $status = 'PAID';
    $amount = 25;
    $purpose = "SMS Payment";

    if (empty($sender_id)) {
        header("Location: settings.php?error=Sender Name is required");
        exit;
    }

    $select = "SELECT wallet, credit FROM agents WHERE user_id='$user_id'";
    $sql = mysqli_query($con, $select);

    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $wallet = $row['wallet'];
        $credit = $row['credit'];

        if ($wallet < $amount) {
            header("Location: settings.php?error=Insufficient wallet balance");
            exit;
        }

        // Generate reference ID
        $reference_id = substr($agent_tel, 0, 5) . date('mdHis') . substr($agent_tel, -2);

        // Step 1: Update Payment Record
        $update = "UPDATE agents_sms_payments 
                   SET status='$status', pay_date='$pay_date', amount='$amount' 
                   WHERE user_id='$user_id' AND year='$year' AND month='$month'";
        if (!mysqli_query($con, $update)) {
            header("Location: settings.php?error=Payment update failed");
            exit;
        }

        // Step 2: Deduct from Wallet and Credit
        $walletf = $wallet - $amount;
        $creditf = $credit - $amount;

        $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
        $stmt->bind_param("dds", $walletf, $creditf, $user_id);
        if (!$stmt->execute()) {
            header("Location: settings.php?error=Failed to update wallet balance");
            exit;
        }

        // Step 3: Record the Transaction
        $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $user_id, $agent_name, $agent_email, $amount, $walletf, $creditf, $pay_date, $purpose);
        if (!$stmt->execute()) {
            header("Location: settings.php?error=Failed to record transaction");
            exit;
        }

        // Step 4: Update Activeness Status
        $active = "ACTIVE";
        $update = "UPDATE agents_sms SET activeness='$active' WHERE user_id='$user_id'";
        if (!mysqli_query($con, $update)) {
            header("Location: settings.php?error=Failed to update SMS activeness");
            exit;
        }

            header("Location: settings.php?success=Payment made successfully");
            exit;

    } else {
        header("Location: settings.php?error=Agent record not found");
        exit;
    }
}


if(isset($_POST['submit_pass'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $old_password = mysqli_real_escape_string($con, $_SESSION['old_password']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_pasword']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

     if (empty($new_password)) {
        $errors_pass['student']="New Password is required";
    }
     else if (empty($old_password)) {
        $errors['level']="Old Password is required";
    }
     else if (empty($confirm_password)) {
        $errors['program']="Confirm new password is required";
    }
     else if (strlen($new_password) < 8) {
        $errors['passwordrepeat']="Password must be at least 8 characters";
    }  
    else if ($new_password!==$confirm_password) {
        $errors['password']="password do not match";
    }
    
    else{
        
  $update = "update account_agents set password='$confirm_password' where user_id= '$user_id' ";
  $uquery = mysqli_query($con,$update);

    if($uquery){
      $success['produce']="Password is changed successfully";
      }
   
   else{
      $errors['produce']="Password update failed";
       }
      }
}


if(isset($_POST['submit_shop'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $shops = mysqli_real_escape_string($con, $_POST['shop']);
   
    if (empty($shops)) {
        $errors['shop']="A shop is required";
    }
   else{
        $shop = strtolower($shops);
    // Check if account exists with the provided details
    $selectquery = "SELECT * FROM account_agents WHERE user_id='$user_id' AND shop='$shop' ";
    $select = mysqli_query($con, $selectquery);
    if ($select && mysqli_num_rows($select) > 0) {
              $errors['produce']="A shop name is already taken";
    }
    else {

        $update = "update account_agents set shop='$shop' where user_id= '$user_id' ";
        $uquery = mysqli_query($con,$update);
       if($uquery){
           
           $updates = "update agents set shop='$shop' where user_id= '$user_id' ";
           $uquerys = mysqli_query($con,$updates);
           if($uquerys){
           $success['produce']="Your shop name is created successfully";
           }
           else{
           $errors['produce']="Failed to create a shop";
           }
    
       }
       else{
      $errors['produce']="Failed to create a shop name";
       }
    }
  }
}

if(isset($_POST['unique'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $telephone = mysqli_real_escape_string($con, $_SESSION['telephone']);
    
    // Generate unique ID
    $agent_name = $first_name . $last_name;
    $first_five_letters = substr($agent_name, 0, 5);
    $last_three_digits = substr($telephone, -3);
    $unique_id = $first_five_letters . $last_three_digits;

    // Check if account exists with the provided details
    $selectquery = "SELECT * FROM account_agents WHERE user_id='$user_id' AND unique_id='$unique_id' ";
    $select = mysqli_query($con, $selectquery);
    if ($select && mysqli_num_rows($select) > 0) {
             $errors['produce']="A unique ID is already taken";
    }
    else {
    $update = "update account_agents set unique_id='$unique_id' where user_id= '$user_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
       $updates = "update agents set unique_id='$unique_id' where user_id= '$user_id' ";
       $uquerys = mysqli_query($con,$updates);
       if($uquerys){
       $success['produce']="Your Unique ID is created successfully";
       }
       else{
      $errors['produce']="Failed to create your unique name/ID";
       }
        
       }
       else{
      $errors['produce']="Failed to create your unique ID";
       }
    }
}

if(isset($_POST['paystack'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $paystack_key = mysqli_real_escape_string($con, $_POST['paystack_key']);

    // Check if account exists with the provided details
    $selectquery = "SELECT * FROM account_agents WHERE user_id='$user_id' AND paystack='$paystack_key' ";
    $select = mysqli_query($con, $selectquery);
    if ($select && mysqli_num_rows($select) > 0) {
              $errors['produce']="This Paystack Key already exist";
    }
    else {

       $update = "update account_agents set paystack='$paystack_key' where user_id= '$user_id' ";
       $uquery = mysqli_query($con,$update);
       if($uquery){
            $updates = "update agents set paystack='$paystack_key' where user_id= '$user_id' ";
            $uquerys = mysqli_query($con,$updates);
            if($uquerys){
            $success['produce']="Your Paystack Key is added successfully";
            }
            else{
            $errors['produce']="Failed to add a Paystack Key";
            }
       }
       else{
      $errors['produce']="Failed to add a Key";
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
			<?php $page = 'settings' ?>
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

			<div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-6">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="help.php" class="text-success" style="font-size: 1rem; text-align:center;"><i class="fa fa-info-circle" aria-hidden="true"></i> Price list</a>

						</div>
					</div>
				</div>

			</div>
			
			
			
                <?php if(count($success)>0): ?>
                                   <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                   <?php foreach($success as $succes): ?>
                                   <div><?php echo $succes; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 

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
 <!--                               
      <div class="container mt-5">
        <h3>Bootstrap Radio Switches Example</h3>
        <form>
            <div class="form-check form-switch">
                <input class="form-check-input" type="radio" name="toggleSwitch" id="option1" value="on" checked>
                <label class="form-check-label" for="option1">Option 1 (ON)</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="radio" name="toggleSwitch" id="option2" value="off">
                <label class="form-check-label" for="option2">Option 2 (OFF)</label>
            </div>
        </form>
    </div>
     -->                           
				<div class="row gutters">
            
<div class="col-lg-6 col-sm-12 col-12">
    <div class="card h-100">
        <div class="card-header">
            <h3 class="card-title">API</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row gutters">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="apiKey">Your API Key</label>
                            <?php
                            include '../database/dbconfig.php';
                            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                            $select = "SELECT api FROM agents WHERE user_id='$user_id'";
                            $sql = mysqli_query($con, $select);
                            if (mysqli_num_rows($sql) > 0) {
                                while ($row = mysqli_fetch_array($sql)) {
                                    $api = $row['api'];
                                    if (empty($api)) {
                                        echo '<input type="text" class="form-control" name="keys" readonly placeholder="No API Key generated yet">';
                                    } else {
                                        echo '
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="apiKey" value="' . $api . '" readonly>
                                            <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                                <i class="fa fa-clipboard" aria-hidden="true"></i> Copy
                                            </button>
                                        </div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="col-sm-6 text-end">
                        <div class="loading-overlay" id="spinner" style="display: none;">
                            <div class="spinner"></div>
                            <div class="loading-text">Please Wait...</div>
                        </div>
                        <button type="submit" name="generate" class="btn btn-success w-100" id="spin">
                            <i class="fa fa-refresh" aria-hidden="true"></i> Generate API Key
                        </button>
                    </div>

                    <div class="col-sm-6 text-start">
                        <a href="api_docs.php" class="btn btn-primary w-100">
                            <i class="fa fa-code" aria-hidden="true"></i> Go To API DOCS
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const inputField = document.getElementById("apiKey");
    inputField.select();
    inputField.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");
    alert("API Key copied to clipboard!");
}
</script>

						
						<!-- Modal -->
                        <div class="modal fade" id="smsexampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">BULK SMS Subscription Payment</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              	<form action="" method="POST" enctype="multipart/form-data">
                              <div class="modal-body">
                                    <div class="row gutters">
										<div class="col-sm-12 col-12">
                                              <h5>Payment for the month of <?php echo date('F') .", ".date('Y') ?></h5>
										</div>
									</div>
                                
                                	<div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">SENDER ID</label>
												
												<?php
												    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                                    // Select query
                                                    $select = "SELECT * FROM agents_sms WHERE user_id='$user_id'";
                                                    $sql = mysqli_query($con, $select);
                                                    // Check if there are any rows
                                                    if (mysqli_num_rows($sql) > 0) {
                                                        // Fetch each row
                                                        while ($row = mysqli_fetch_array($sql)) {
                                                        ?>
                                                       <input type="text" class="form-control" name="sender_id" value="<?php echo $row['sender_id'] ?>" readonly>
                                                       <?php
                                                        }
                                                    }
												?>

											</div>
										</div>
									</div>
                                
                                    <div class="row gutters">
										<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Year</label>
												<input type="text" class="form-control" name="pay_year" id="fullName" value="<?php echo date('Y'); ?>" readonly>
											</div>
										</div>
										<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Month</label>
												<input type="text" class="form-control" name="pay_month" id="fullName" value="<?php echo date('F'); ?>" readonly>
											</div>
										</div>
									</div>
									
									<div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">Amount</label>
												<input type="text" class="form-control" name="amount" id="fullName" value="25" readonly>
											</div>
										</div>
									</div>
                                
                                </div>
                                <div class="modal-footer">
                                <div class="loading-overlay" id="spinner1" style="display: none;">
                                    <div class="spinner"></div>
                                    <div class="loading-text">Please Wait...</div>
                                </div>
                                  
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" name="sms_pay" class="btn btn-primary" id="spin1">Confirm Payment</button>
                              </div>
                              </form>
                            </div>
                          </div>
                        </div>
						
						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<h4 class="card-title">Bulk SMS</h4>
								</div>
								<div class="card-body">
								<span>As an Agent having your own website, you are allowed to subscribe to get your own personalized SMS Sender ID which will allow messages sent to your clients appear in your name</span>
								<br>
								<hr>
								<form action="" method="POST" enctype="multipart/form-data">
									<div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
                                                    <?php
                                                    include '../database/dbconfig.php';
                                                    // Sanitize user input
                                                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                                    // Select query
                                                    $select = "SELECT * FROM agents_sms WHERE user_id='$user_id'";
                                                    $sql = mysqli_query($con, $select);
                                                    // Check if there are any rows
                                                    if (mysqli_num_rows($sql) > 0) {
                                                        // Fetch each row
                                                        while ($row = mysqli_fetch_array($sql)) {
                                                            
                                                            if ($row['request_status'] == "REJECTED") {
                                                                ?>
                                                        	<h6>Your SMS Request for SENDER NAME <strong class="text-primary"><?php echo $row['sender_id']; ?></strong> is <strong class="text-danger">REJECTED</strong></h6>
                                                                <?php
                                                                }
                                                            elseif ($row['request_status'] == "APPROVED"){
                                                                $_SESSION['sender_id'] = $row['sender_id']
                                                                ?>
                                                                <h6>Your SMS Request for SENDER NAME <strong class="text-primary"><?php echo $row['sender_id']; ?></strong> is <strong class="text-primary">APPROVED</strong></h6>
                                                                <br>
                                                                <h6>Your SENDER NAME <strong class="text-primary"><?php echo $row['sender_id']; ?></strong> is <strong class="text-secondary"><?php echo $row['activeness']; ?></strong></h6>
                                                                <br>
                                                                
                                                                
                                            <?php
                                				$current_year = date('Y');
                                				$current_month = date('F');
												    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                                    // Select query
                                                    $select = "SELECT * FROM agents_sms_payments WHERE user_id='$user_id' AND year = '$current_year' AND month='$current_month'";
                                                    $sql = mysqli_query($con, $select);
                                                    // Check if there are any rows
                                                    if (mysqli_num_rows($sql) > 0) {
                                                        // Fetch each row
                                                        while ($row = mysqli_fetch_array($sql)) {
                                                            if($row['status']=="PAID"){
                                                                ?>
                                                                 <h6>You have paid for the month of <?php echo date('F') .", ".date('Y') ?></h6>
                                                                 <?php
                                                            }
                                                            else{
                                                                ?>
                                                                <h6>You have not pay for the month of <?php echo date('F') .", ".date('Y') ?></h6>
                                                                <!-- Button trigger modal -->
                                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#smsexampleModal"> Pay with Wallet </button>
                                                                <?php
                                                            }
                                                        ?>
                                                      
                                                       <?php
                                                        }
                                                    }
												?>
                                                        
                                                        <?php
                                                                
                                                    }      
 
                                                            elseif ($row['request_status'] == "PENDING"){
                                                                ?>
                                                                <h6>Your SMS Request for SENDER NAME <strong class="text-primary"><?php echo $row['sender_id']; ?></strong> is <strong class="text-info">PENDING</strong></h6>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    else{
                                                        ?>

                                            <div class="form-group">
                                                <h5>Request a SMS Sender Name</h5>
												<label for="phone">Enter a SENDER NAME (It must be 10 letters and not seperated)</label>
												<label for="phone">NB: Seperated names will be merged</label>
												<h6>It will take three (3) days for your SENDER Name to be Approved</h6>
												<input type="text" class="form-control" name="sender_id" placeholder="Enter a SENDER NAME here" required>
											</div>
										
	                                        <div class="spinner-overlay" id="spinner" style="display:none;">
                                                <div class="spinner">
                                                    <span class="spinner-border spinner-border-lg" role="status" aria-hidden="true"></span>
                                                    <p>Please Wait...</p>
                                                </div>
                                            </div>
											
										<div class="col-sm-12">
											<div class="text-right">
												<button type="submit" name="sms_request" class="btn btn-success" id="spin">Request</button>
											</div>
										</div>
										
											<?php
                                                    }
                                                    ?>
											</div>

										</div>
									
									</div>
								</form>
									
								</div>
							</div>
						</div>
					</div>
					
					<script>
// JavaScript function to copy the API key to the clipboard
function copyToClipboard() {
    const inputField = document.getElementById('apiKey');
    inputField.select(); // Select the input field's content
    inputField.setSelectionRange(0, 99999); // For mobile compatibility
    document.execCommand('copy'); // Copy the selected text to the clipboard
    
    // Optional: Show a message to the user
    alert('API Key copied to clipboard!');
}
</script>
                                

				<div class="row gutters mt-4">
						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Change Password</div>
								</div>
								<div class="card-body">
								    
								<form action="" method="POST" enctype="multipart/form-data">
									<div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">Old Password</label>
												<input type="password" class="form-control" name="old_password" id="fullName" placeholder="Enter the old password">
											</div>
											<div class="form-group">
												<label for="eMail">New Password (It must be 8 characters)</label>
												<input type="password" class="form-control" name="new_password" id="eMail" placeholder="Enter the new password">
											</div>
											<div class="form-group">
												<label for="phone">Confirm Password (It must be 8 characters)</label>
												<input type="password" class="form-control" name="confirm_password" id="phone" placeholder="Confirm new password">
											</div>
										</div>
									
										<div class="col-sm-12">
										    
											<div class="text-right">
											    <div class="spinner" id="spinner_pass" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
												<button type="submit" name="submit_pass" class="btn btn-success" id="passBtn">Change Password</button>
											</div>
										</div>
									</div>
									</form>
									
								</div>
							</div>
						</div>
					</div>


               <div class="footer">
    	           <?php include 'parts/footer.php'; ?> 
               </div>
			</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		
<script>
    $(document).ready(function () {
        $('#spin1').on('click', function () {
            // Show the spinner
            $('#spinner1').fadeIn();
        });
    });
</script>
    
<script>
    $(document).ready(function () {
        $('#spin').on('click', function () {
            // Show the spinner
            $('#spinner').fadeIn();
        });
    });
</script>
			
<!--spinner start-->
<style>
/* Ensure the spinner covers the full page */
.loading-overlay {
    display: none; /* Initially hidden */
    position: fixed; /* Fixed positioning for full-page overlay */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay for visibility */
    z-index: 9999; /* Ensure it is above all other elements */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner animation styling */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(0, 0, 0, 0.1);
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Loading text styling */
.loading-text {
    margin-top: 10px;
    font-size: 18px;
    color: #007bff;
}
</style>

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