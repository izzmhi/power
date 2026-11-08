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
$select = "select * from agents where user_id='$user_id' ";
$sql=mysqli_query($con,$select);
$check=mysqli_num_rows($sql)>0;
if($check)
{ 
    while($row=mysqli_fetch_array($sql))
    {
    $_SESSION['first_name'] = $row['first_name'];
    $_SESSION['last_name']= $row['last_name'];
    $_SESSION['email'] = $row['email'];
    $_SESSION['telephone'] = $row['telephone'];
    }
}

$errors = array();
$success = array();

//Call Mins
if(isset($_POST['call_wallet'])){
$mins = filter_input(INPUT_POST, 'mins', FILTER_SANITIZE_SPECIAL_CHARS);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($mins)) {
        $errors['package'] = "Mins is not selected";
    }
    if (empty($price)){
        $errors['price'] = "Price is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }

    else{
		$_SESSION['amins'] = $mins;
		$_SESSION['aprice'] = $price;
		$_SESSION['abeneficiary'] = $beneficiary;
        header("location:wallet_calls.php");
    	
      }
	}

///////AFA

if(isset($_POST['afa_Wallet'])){
    $fullnames = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_SPECIAL_CHARS);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_number = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
    $occupation = filter_input(INPUT_POST, 'occupation', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_type = "Ghana Card";
    $errors = []; // Initialize errors array

    if (empty($fullnames)) {
        $errors['fullname'] = "Full name is required";
    }
    if (empty($telephone)) {
        $errors['telephone'] = "Mobile number is required";
    }
    if (empty($region)) {
        $errors['region'] = "Region is required";
    }
    if (empty($location)) {
        $errors['location'] = "Location is required";
    }
    if (empty($dob)) {
        $errors['dob'] = "Date of Birth is required";
    }
    
        function validateImage($file) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== 0) {
            return "Image upload error.";
        }

        if (!in_array($file['type'], $allowed)) {
            return "Only JPG, PNG, WEBP allowed.";
        }

        if ($file['size'] > $max_size) {
            return "Image must be less than 2MB.";
        }

        if (!getimagesize($file['tmp_name'])) {
            return "Invalid image file.";
        }

        return true;
    }

    if (empty($errors)) { // Proceed only if there are no errors
            // Validate images
        $frontCheck = validateImage($_FILES['img_front']);
        $backCheck  = validateImage($_FILES['img_back']);

        if ($frontCheck !== true) {
            die($frontCheck);
        }

        if ($backCheck !== true) {
            die($backCheck);
        }

        // ==============================
        // UPLOAD IMAGES
        // ==============================
        $upload_dir = "../img/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique names
        $img_front_name = time() . "_front_" . uniqid() . ".jpg";
        $img_back_name  = time() . "_back_" . uniqid() . ".jpg";

        // Move files
        move_uploaded_file($_FILES['img_front']['tmp_name'], $upload_dir . $img_front_name);
        move_uploaded_file($_FILES['img_back']['tmp_name'], $upload_dir . $img_back_name);

        // ==============================
        // SAVE TO SESSION
        // ==============================
        $_SESSION['img_front']  = $img_front_name;
        $_SESSION['img_back']   = $img_back_name;
        
        $_SESSION['fullnames'] = $fullnames;
        $_SESSION['telephone'] = $telephone;
        $_SESSION['id_type'] = $id_type;
        $_SESSION['id_number'] = $id_number;
        $_SESSION['location'] = $location;
        $_SESSION['dob'] = $dob;
        $_SESSION['region'] = $region;
        $_SESSION['email'] = $email;
        $_SESSION['occupation'] = $occupation;
        header("Location: afa_wallet.php?fullname=$fullnames&telephone=$telephone&id_type=$id_type&id_number=$id_number&location=$location&dob=$dob&region=$region&email=$email");
        exit(); // Good practice to exit after header redirection
    } else {
        // Optionally, store errors in session to display back on the form page
        $_SESSION['errors'] = $errors;
    }
}

///////MTN
///////MTN
///////MTN
///////MTN

if(isset($_POST['mtn_wallet'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
    // Generate reference ID
$last_2_chars = substr($beneficiary, -2);
$first_five_chars = substr($beneficiary, 0, 3);
$seconds = date('His');
$month   = date('md');
// Generate 4 random alphanumeric characters
$random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4);
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars . $random;

    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }

    else{
		$_SESSION['apackage'] = $package;
		$_SESSION['anetwork'] = $network;
		$_SESSION['abeneficiary'] = $beneficiary;
		$_SESSION['reference_id'] = $reference_id;	  
        header("location:wallet_mtn_pay.php");
    	}
	}
	
///////Telecel

if(isset($_POST['telecel_wallet'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
    // Generate reference ID
$last_2_chars = substr($beneficiary, -2);
$first_five_chars = substr($beneficiary, 0, 3);
$seconds = date('His');
$month   = date('md');
// Generate 4 random alphanumeric characters
$random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4);
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars . $random;
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }
    else{
		$_SESSION['apackage'] = $package;
		$_SESSION['anetwork'] = "TELECEL";
		$_SESSION['abeneficiary'] = $beneficiary;
		$_SESSION['reference_id'] = $reference_id;
        header("location:wallet_telecel_pay.php");
    	}
	}

///////AT

if(isset($_POST['at_wallet'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
        // Generate reference ID
$last_2_chars = substr($beneficiary, -2);
$first_five_chars = substr($beneficiary, 0, 3);
$seconds = date('His');
$month   = date('md');
// Generate 4 random alphanumeric characters
$random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4);
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars . $random;
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }
    else{
		$_SESSION['apackage'] = $package;
		$_SESSION['anetwork'] = "AT";
		$_SESSION['abeneficiary'] = $beneficiary;
		$_SESSION['reference_id'] = $reference_id;
        header("location:wallet_at_pay.php");
    	}
	}

///////special offer AT

if(isset($_POST['special_wallet'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
    // Generate reference ID
$last_2_chars = substr($beneficiary, -2);
$first_five_chars = substr($beneficiary, 0, 3);
$seconds = date('His');
$month   = date('md');
// Generate 4 random alphanumeric characters
$random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4);
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars . $random;
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }
    else{
		$_SESSION['apackage'] = $package;
		$_SESSION['anetwork'] = "AT";
		$_SESSION['abeneficiary'] = $beneficiary;
		$_SESSION['reference_id'] = $reference_id;
        header("location:wallet_special.php");
    	}
	}
	
if(isset($_POST['mtn_big_wallet'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$beneficiary_repeat = filter_input(INPUT_POST, 'beneficiary_repeat', FILTER_SANITIZE_SPECIAL_CHARS);
    // Generate reference ID
$last_2_chars = substr($beneficiary, -2);
$first_five_chars = substr($beneficiary, 0, 3);
$seconds = date('His');
$month   = date('md');
// Generate 4 random alphanumeric characters
$random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4);
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars . $random;
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    if (empty($beneficiary_repeat)){
        $errors['beneficiary_repeat'] = "Repeat Recipient number";
    }
    if ($beneficiary !== $beneficiary_repeat) {
        $errors['password'] = "Recipient number do not match";
    }
    else{
		$_SESSION['apackage'] = $package;
		$_SESSION['anetwork'] = "MTN";
		$_SESSION['abeneficiary'] = $beneficiary;
		$_SESSION['reference_id'] = $reference_id;
        header("location:wallet_mtn_big_pay.php");
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
			<?php $page = 'home' ?>
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
				
				 <!-- Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <h4 class="modal-title">Take Note</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <form class="mt-2" action="" method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="row gutters">
            <div class="col-sm-12 col-12">
                <div class="form-group">
                     <?php
            include '../database/dbconfig.php';
            $select = "SELECT message FROM messages ORDER BY id DESC LIMIT 1 ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                
                ?>
                <div class="p-2 text-info"><?php echo $row['message'] ?></div>
                <?php
                }
              }
              else{
                  ?>
                  <h6>No message</h6>
                  <?php
              }
            ?> 
                </div>
            </div>
        </div>
        <div class="row gutters">
            <div class="col-sm-12 col-12">
                <div class="form-group">
                   
                    <h6>1. Don't order Same number twice! Give at less 15mins Interval.</h6>
                    <h6>2. Check order status for delivery updates. Unless stated otherwise.</h6>
                    <h6>3. If order is pending no need to complain.</h6>
                    <h6>4. You should report when order delivered but not received after 2 hours</h6>
                    <h6>5. Complaints made after end of the day will not be accepted.</h6>
                    <p> 6. Please submit complaints in this format:</p>
                    <p> i. Contact: [ 0536547### ]</p>
                    <p> ii. Network: [ Mtn ]</p>
                    <p> iii. Data volume: [ 1GB ]</p>
                    <p> iv. Time:[12:42pm]</p>
                    <h6>7. Orders sent to wrong numbers are non-refundable</h6>
                    <a href="https://wa.link/sy9x1c" target="_blank">All complaint should be made <img src="../assets/img/WhatsApp.png" alt="WhatsApp" style="width: 20px; height: 20px;"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i> here <i class="fa fa-arrow-circle-left" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>
</div>
			  	
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

<!-- AirtelTigo Package Modal -->
<div class="modal fade" id="atBuyModal" tabindex="-1" role="dialog" aria-labelledby="atBuyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">

      <!-- Modal Header -->
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="atBuyModalLabel">
          <i class="fa fa-globe"></i> AirtelTigo Package Purchase
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body p-4">

          <!-- Network -->
          <input type="hidden" class="form-control" name="network" value="AT" readonly>

          <!-- Package Selection -->
          <div class="form-group">
            <label><i class="fa fa-list"></i> Select a Package (GIG)</label>
            <select required name="package" class="form-control">
              <option value="">Select a package (GIG)</option>
              <?php
                include '../database/dbconfig.php';
                $target = "AGENT";
                $network = "AT";
                $select = "SELECT package,amount FROM prices WHERE network='$network' AND target='$target' ORDER BY CAST(package AS UNSIGNED) ASC";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
              ?>
              <option value="<?php echo $row['package']; ?>"><?php echo $row['package']; ?>GIG - GHS<?php echo $row['amount']; ?></option>
              <?php } } ?>
            </select>
          </div>

          <!-- Price & Recipient Fields -->
          <div id="at-price-container">
            <!-- Recipient Number -->
            <div class="form-group">
              <label><i class="fa fa-phone"></i> Recipient Number</label>
              <input type="tel" class="form-control" id="atrecipient" placeholder="Enter Recipient Number" name="beneficiary" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>
            <!-- Repeat Recipient -->
            <div class="form-group">
              <label><i class="fa fa-repeat"></i> Repeat Recipient Number</label>
              <input type="tel" class="form-control" id="atrepeat" placeholder="Repeat Recipient Number" name="beneficiary_repeat" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>

            <!-- Validation Message -->
            <div class="mb-3 text-center">
              <div id="atmessage" style="display: none; color: red; font-weight: bold;">Recipient numbers do not match</div>
              <div id="atmessagegreen" style="display: none; color: green; font-weight: bold;">Recipient numbers match</div>
            </div>
          </div>

          <!-- Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_at" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x" style="color:#990099;"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer" id="footer_at" style="display:none;">
          <button type="submit" name="at_wallet" class="btn btn-block bg-info text-white" style="font-weight:bold;" id="atBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>

        <div class="text-center py-2">OR</div>
        <!-- Optional Upload Excel Button -->
        <!-- <a href="bulk.php?network=AT" class="btn btn-block btn-info"><i class="fa fa-upload"></i> Upload Excel</a> -->

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>



<!-- Telecel Package Modal -->
<div class="modal fade" id="telecelBuyModal" tabindex="-1" role="dialog" aria-labelledby="telecelBuyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      <!-- Modal Header -->
      <div class="modal-header" style="background:#e60012; color:#fff;">
        <h5 class="modal-title" id="telecelBuyModalLabel">
          <i class="fa fa-globe"></i> Telecel Package Purchase
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body p-4">
          <!-- Network -->
          <input type="hidden" class="form-control" name="network" value="TELECEL" readonly>
          <!-- Package Selection -->
          <div class="form-group">
            <label><i class="fa fa-list"></i> Select a Package (GIG)</label>
            <select required name="package" class="form-control" >
              <option value="">Select a package (GIG)</option>
              <?php
                include '../database/dbconfig.php';
                $target = "AGENT";
                $network = "TELECEL";
                $select = "SELECT * FROM prices WHERE network='$network' AND target='$target' ORDER BY CAST(package AS UNSIGNED) ASC";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                ?>
                    <option value="<?php echo $row['package']; ?>"><?php echo $row['package']; ?>GIG - GHS<?php echo $row['amount']; ?></option>
              <?php } } ?>
            </select>
          </div>

          <!-- Price & Recipient Fields -->
          <div id="telecel-price-container">
            <!-- Recipient Number -->
            <div class="form-group">
              <label><i class="fa fa-phone"></i> Recipient Number</label>
              <input type="tel" class="form-control" id="telecelrecipient" placeholder="Enter Recipient Number" name="beneficiary" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>
            <!-- Repeat Recipient -->
            <div class="form-group">
              <label><i class="fa fa-repeat"></i> Repeat Recipient Number</label>
              <input type="tel" class="form-control" id="telecelrepeat" placeholder="Repeat Recipient Number" name="beneficiary_repeat" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>
            <!-- Validation Message -->
            <div class="mb-3 text-center">
              <div id="telecelmessage" style="display: none; color: red; font-weight: bold;">Recipient numbers do not match</div>
              <div id="telecelmessagegreen" style="display: none; color: green; font-weight: bold;">Recipient numbers match</div>
            </div>
          </div>
          <!-- Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_telecel" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x" style="color:#e60012;"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>
        </div>
        <!-- Footer -->
        <div class="modal-footer" id="footer_telecel" style="display:none;">
          <button type="submit" name="telecel_wallet" class="btn btn-block" style="background:#e60012; color:#fff; font-weight:bold;" id="telecelBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>
        <div class="text-center py-2">OR</div>
        <!-- Optional Upload Excel Button -->
        <!-- <a href="bulk.php?network=TELECEL" class="btn btn-block btn-info"><i class="fa fa-upload"></i> Upload Excel</a> -->
      </form>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

#mtnmessage,
#mtnmessagegreen {
  font-size: 14px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>


<!-- MTN Package Modal -->
<div class="modal fade" id="mtnBuyModal" tabindex="-1" role="dialog" aria-labelledby="mtnBuyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      <!-- Modal Header -->
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="mtnBuyModalLabel">
          <i class="fa fa-signal"></i> MTN Package Purchase
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body p-4">

          <!-- Network -->
          <input type="hidden" class="form-control" name="network" value="MTN" readonly>

          <!-- Package Selection -->
          <div class="form-group">
            <label><i class="fa fa-list"></i> Select a Package (GIG)</label>
            <select required name="package" class="form-control">
              <option value="">Select a package (GIG)</option>
              <?php
                include '../database/dbconfig.php';
                $target = "AGENT";
                $network = "MTN";
                $select = "SELECT * FROM prices WHERE network='$network' AND target='$target' ORDER BY CAST(package AS UNSIGNED) ASC";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
              ?>
                    <option value="<?php echo $row['package']; ?>"><?php echo $row['package']; ?>GIG - GHS<?php echo $row['amount']; ?></option>
              <?php } } ?>
            </select>
          </div>

          <!-- Price & Recipient Fields -->
          <div id="mtn-price-container">
            <!-- Recipient Number -->
            <div class="form-group">
              <label><i class="fa fa-phone"></i> Recipient Number</label>
              <input type="tel" class="form-control" id="mtnrecipient" placeholder="Enter Recipient Number" name="beneficiary" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>
            <!-- Repeat Recipient -->
            <div class="form-group">
              <label><i class="fa fa-repeat"></i> Repeat Recipient Number</label>
              <input type="tel" class="form-control" id="mtnrepeat" placeholder="Repeat Recipient Number" name="beneficiary_repeat" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>
            <!-- Validation Message -->
            <div class="mb-3 text-center">
              <div id="mtnmessage" style="display: none; color: red; font-weight: bold;">Recipient numbers do not match</div>
              <div id="mtnmessagegreen" style="display: none; color: green; font-weight: bold;">Recipient numbers match</div>
            </div>
          </div>
          <!-- Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_mtn" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-warning"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>
        </div>
        <!-- Footer -->
        <div class="modal-footer" id="footer_mtn" style="display:none;">
          <button type="submit" name="mtn_wallet" class="btn btn-warning btn-block text-dark" id="mtnBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>
        <div class="text-center py-2">OR</div>
        <!-- Optional Upload Excel Button -->
        <!-- <a href="bulk.php?network=MTN" class="btn btn-block btn-info"><i class="fa fa-upload"></i> Upload Excel</a> -->

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

.btn-warning {
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  border-radius: 8px;
}

#mtnmessage,
#mtnmessagegreen {
  font-size: 14px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>


<!-- AT Big Time Modal -->
<div class="modal fade" id="specialBuyModal" tabindex="-1" role="dialog" aria-labelledby="specialBuyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">

      <!-- Modal Header -->
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="specialBuyModalLabel">
          <i class="fa fa-signal"></i> AT Big Time Purchase
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body p-4">

          <!-- Network -->
          <input type="hidden" class="form-control" name="network" value="AT" readonly>

          <!-- Package Selection -->
          <div class="form-group">
            <label><i class="fa fa-list"></i> Select a Package (GIG)</label>
            <select required name="package" class="form-control">
              <option value="">Select a package (GIG)</option>
              <?php
                include '../database/dbconfig.php';
                $package = "AT BIG TIME";
                $target = "Agent";
                $select = "SELECT DISTINCT * FROM special_offers WHERE offer_name='$package' AND target='$target' ORDER BY CAST(package AS UNSIGNED) ASC";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
              ?>
                    <option value="<?php echo $row['package']; ?>"><?php echo $row['package']; ?>GIG - GHS<?php echo $row['amount']; ?></option>
              <?php } } ?>
            </select>
          </div>

          <!-- Price & Recipient Fields -->
          <div id="special-price-container">
            <!-- Recipient Number -->
            <div class="form-group">
              <label><i class="fa fa-phone"></i> Recipient Number</label>
              <input type="tel" class="form-control" id="specialrecipient" placeholder="Enter Recipient Number" name="beneficiary" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>

            <!-- Repeat Recipient -->
            <div class="form-group">
              <label><i class="fa fa-repeat"></i> Repeat Recipient Number</label>
              <input type="tel" class="form-control" id="specialrepeat" placeholder="Repeat Recipient Number" name="beneficiary_repeat" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>

            <!-- Validation Message -->
            <div class="mb-3 text-center">
              <div id="specialmessage" style="display: none; color: red; font-weight: bold;">Recipient numbers do not match</div>
              <div id="specialmessagegreen" style="display: none; color: green; font-weight: bold;">Recipient numbers match</div>
            </div>
          </div>

          <!-- Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_special" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-danger"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer" id="footer_special" style="display:none;">
          <button type="submit" name="special_wallet" class="btn btn-info btn-block text-white" id="specialBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>

        <div class="text-center py-2">OR</div>
        <!-- Optional Upload Excel Button -->
        <!-- <a href="bulk.php?network=ATBIG" class="btn btn-block btn-info"><i class="fa fa-upload"></i> Upload Excel</a> -->

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

.btn-danger {
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  border-radius: 8px;
}

#specialmessage,
#specialmessagegreen {
  font-size: 14px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>



<!-- Special MTN Modal -->
<div class="modal fade" id="mtnBigBuyModal" tabindex="-1" role="dialog" aria-labelledby="mtnBigModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">

      <!-- Modal Header -->
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="mtnBigModalLabel">
          <i class="fa fa-signal"></i> MTN Big Time Purchase
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body p-4">

          <!-- Network -->
          <input type="hidden" class="form-control" name="network" value="MTN" readonly>

          <!-- Package Selection -->
          <div class="form-group">
            <label><i class="fa fa-list"></i> Select a Package (GIG)</label>
            <select required name="package" class="form-control">
              <option value="">Select a package (GIG)</option>
              <?php
                include '../database/dbconfig.php';
                $package = "MTN BIG TIME";
                $target = "Agent";
                $select = "SELECT DISTINCT * FROM special_offers WHERE offer_name='$package' AND target='$target' ORDER BY CAST(package AS UNSIGNED) ASC";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
              ?>
                    <option value="<?php echo $row['package']; ?>"><?php echo $row['package']; ?>GIG - GHS<?php echo $row['amount']; ?></option>
              <?php } } ?>
            </select>
          </div>

          <!-- Price & Recipient Fields -->
          <div id="specialmtn-price-container">
            <!-- Recipient Number -->
            <div class="form-group">
              <label><i class="fa fa-phone"></i> Recipient Number</label>
              <input type="tel" class="form-control" id="specialmtnrecipient" placeholder="Enter Recipient Number" name="beneficiary" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>

            <!-- Repeat Recipient -->
            <div class="form-group">
              <label><i class="fa fa-repeat"></i> Repeat Recipient Number</label>
              <input type="tel" class="form-control" id="specialmtnrepeat" placeholder="Repeat Recipient Number" name="beneficiary_repeat" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" required>
            </div>

            <!-- Validation Message -->
            <div class="mb-3 text-center">
              <div id="specialmtnmessage" style="display: none; color: red; font-weight: bold;">Recipient numbers do not match</div>
              <div id="specialmtnmessagegreen" style="display: none; color: green; font-weight: bold;">Recipient numbers match</div>
            </div>
          </div>

          <!-- Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_specialmtn" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-warning"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer" id="footer_specialmtn" style="display:none;">
          <button type="submit" name="mtn_big_wallet" class="btn btn-warning btn-block text-dark" id="specialmtnBtn">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>

        <div class="text-center py-2">OR</div>
        <!-- Optional Upload Excel Button -->
        <!-- <a href="bulk.php?network=MTNBIG" class="btn btn-block btn-info"><i class="fa fa-upload"></i> Upload Excel</a> -->

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

.btn-primary {
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  border-radius: 8px;
}

#specialmtnmessage,
#specialmtnmessagegreen {
  font-size: 14px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>



<!-- AFA Bundle Modal -->
<div class="modal fade" id="AFABuyModal" tabindex="-1" role="dialog" aria-labelledby="AFAModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      
      <!-- Header -->
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="AFAModalLabel">
          <i class="fa fa-gift"></i> MTN AFA
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">

          <!-- Fullname -->
          <div class="form-group">
            <label><i class="fa fa-user"></i> Beneficiary Fullname <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="fullname" placeholder="Enter Full Name" required>
          </div>

          <div class="row">
          <div class="col-6">
            <label><i class="fa fa-envelope"></i> Email</label>
            <input type="email" class="form-control" name="email" placeholder="Enter Email (Optional)">
          </div>

          <!-- Telephone -->
          <div class="col-6">
            <label><i class="fa fa-phone"></i> Phone <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" name="telephone" placeholder="e.g. 024XXXXXXX" required>
          </div>
          </div>
          <br>
          
          <!-- Ghana Card -->
          <div class="form-group">
            <label><i class="fa fa-id-card"></i> Ghana Card Number</label>
            <input type="text" class="form-control" name="id_number" placeholder="Enter Ghana Card No." required>
          </div>
         
         <div class="row">
          <!-- Location -->
          <div class="col-6">
            <label><i class="fa fa-map-marker"></i> Location</label>
            <input type="text" class="form-control" name="location" placeholder="Enter Location" required>
          </div>

          <!-- Region -->
          <div class="col-6">
            <label><i class="fa fa-globe"></i> Region</label>
            <select class="form-control" name="region" required>
              <option value="">Select Region</option>
              <option value="Ahafo">Ahafo</option>
              <option value="Ashanti">Ashanti</option>
              <option value="Bono">Bono</option>
              <option value="Bono East">Bono East</option>
              <option value="Central">Central</option>
              <option value="Eastern">Eastern</option>
              <option value="Greater Accra">Greater Accra</option>
              <option value="North East">North East</option>
              <option value="Northern">Northern</option>
              <option value="Oti">Oti</option>
              <option value="Savannah">Savannah</option>
              <option value="Upper East">Upper East</option>
              <option value="Upper West">Upper West</option>
              <option value="Volta">Volta</option>
              <option value="Western">Western</option>
              <option value="Western North">Western North</option>
            </select> 
          </div>
          </div>
          
          <br>
          
          <!-- Date of Birth -->
          <div class="row">
            <div class="col-6">
            <label><i class="fa fa-calendar"></i> Date of Birth</label>
            <input type="date" class="form-control" name="dob" required>
            </div>
            
            <div class="col-6">
            <label><i class="fa fa-check-double"></i> Occupation</label>
            <input type="text" class="form-control" name="occupation" required>
          </div>
          </div>
          <br>
            <div class="row">
    <div class="col-6">
        <label class="form-label">
            <i class="fas fa-check-double me-2 text-secondary"></i>
            Picture of the front of your Ghana Card
        </label>
        <input type="file" class="form-control" name="img_front" accept="image/*" required>
    </div>

    <div class="col-6">
        <label class="form-label">
            <i class="fas fa-check-double me-2 text-secondary"></i>
            Picture of the back of your Ghana Card
        </label>
        <input type="file" class="form-control" name="img_back" accept="image/*" required>
    </div>
</div>

          <!-- Loading Spinner -->
          <div class="loading-overlay text-center py-3" id="spinner_afa" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-warning"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="submit" name="afa_Wallet" class="btn btn-warning btn-block text-dark" id="afaBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- WASSCE Modal -->
<div class="modal fade" id="wassceBuyModal" tabindex="-1" role="dialog" aria-labelledby="AFAModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      
      <!-- Header -->
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="AFAModalLabel">
          <i class="fa fa-gift"></i> WASSCE Results Checker
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form action="wallet_checker.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">

          <!-- Fullname -->
<div class="form-group">
    <label>Quantities <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="qty" name="qty" autocomplete="off" inputmode="numeric" placeholder="Enter quantity (min 1)" required>
</div>

            <?php
                include '../database/dbconfig.php';
                    $select = "SELECT amount FROM checker_price WHERE product='WASSCE'";
                    $sql    = mysqli_query($con, $select);
                    $row    = mysqli_fetch_assoc($sql);
                    $amount = $row['amount'];
                ?>
        <div class="form-group">
            <label>Price <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="amount" name="amount" value="<?php echo $amount; ?>" readonly>
        </div>

          <!-- Telephone -->
          <div class="form-group">
            <label><i class="fa fa-phone"></i> Number to receive the checker <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" name="telephone" placeholder="e.g. 024XXXXXXX" required>
          </div>

        <div class="form-group">
            <label>Total Price <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="total" name="total" value="0" readonly>
        </div>
  
          <!-- Loading Spinner -->
        <div class="loading-overlay text-center py-3" id="spinner_afa" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-warning"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
        </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="submit" name="wassce_Wallet" class="btn btn-warning btn-block text-dark" id="afaBtnW">
            <i class="fa fa-credit-card"></i> Pay With Wallet
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#qty").on("input", function() {
    let qty = $("#qty").val();

    $.ajax({
        url: "get_price.php",
        type: "POST",
        data: { qty: qty },
        success: function(response) {
            let data = JSON.parse(response);
            // Update amount with calculated total
            $("#amount").val(data.total);
        }
    });
});
</script>


<script>
const qty = document.getElementById("qty");

// Prevent typing non-digits
qty.addEventListener("keydown", function (e) {
    const blocked = ["e", "E", "+", "-", ".", ",", " ", "Tab"];
    if (blocked.includes(e.key)) {
        e.preventDefault();
    }
});

// Prevent paste of invalid characters
qty.addEventListener("paste", function (e) {
    const paste = (e.clipboardData || window.clipboardData).getData("text");
    if (!/^[0-9]+$/.test(paste)) {
        e.preventDefault();
    }
});

// Prevent drag & drop input
qty.addEventListener("drop", e => e.preventDefault());

// Final cleanup + enforce minimum
qty.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value === "" || parseInt(this.value) < 1) {
        this.value = "1";
    }
});
</script>





<!-- Custom CSS for Modal -->
<style>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  font-size: 18px;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

.btn-primary {
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  border-radius: 8px;
}

@media (max-width: 576px) {
  .modal-dialog {
    margin: 15px;
  }
}
</style>
	<!-- Main container start -->
	<div class="main-container">
			
<script>
    function togglePayButton() {
        var priceInput = document.getElementById('priceInput').value;
        var display = document.getElementById('display');

        if (priceInput && parseFloat(priceInput) > 0) {
            display.style.display = 'block';
        } else {
            display.style.display = 'none';
        }
    }

</script>
					
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
                                <?php
include '../database/dbconfig.php';
$user_id = $_SESSION['user_id'] ?? '';
if ($user_id) {
    // Fetch wallet for the logged-in agent
    $stmt = $con->prepare("SELECT wallet FROM agents WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $wallet = floatval($row['wallet']);
        if ($wallet <= 2) {
            ?>
            <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mt-2" role="alert" 
                 style="border-left: 5px solid #ffc107; border-radius: 8px;">
                <div>
                    <i class="fa fa-exclamation-triangle text-danger" aria-hidden="true"></i>
                    <span class="fw-bold text-white ms-2">Your wallet is low (₵<?php echo number_format($wallet, 2); ?>).</span>
                    <br><small>Click "Top up" to load your wallet and continue transactions.</small>
                </div>
            </div>
            <?php
        }
    }
}
?>

<?php
include '../database/dbconfig.php';
$user_id = $_SESSION['user_id'] ?? '';
if ($user_id) {
    // Fetch wallet for the logged-in agent
    $stmt = $con->prepare("SELECT first_name FROM agents WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $first_name = $row['first_name'];
            ?>
            <div class="alert alert-success d-flex align-items-center justify-content-between p-3" role="alert">
                <div>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <span class="fw-bold text-white ms-2">Welcome, <?php echo $first_name; ?>!</span>
                </div>
            </div>
            <?php
    }
}
?>
                                
<style>
  .custom-card {
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
</style>

<div class="row">
  <div class="col-sm-6 col-6">
    <div class="card bg-success custom-card">
    <div class="card-header"><h5 class="card-title text-white">Total Sales</h5></div>
      <div class="card-body d-flex align-items-center">
       <i class="fa fa-money text-white" aria-hidden="true" style="font-size: 32px; margin-right: 15px;display: flex; align-items: center;"></i>
        <div>
            <?php
                            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);

                            // Total sales
                            $query_total = "SELECT SUM(price) AS total_sales 
                                            FROM orders_agent 
                                            WHERE agent_id='$user_id'";
                            $result_total = mysqli_query($con, $query_total);
                            $row_total = mysqli_fetch_assoc($result_total);
                            $total_sales = isset($row_total['total_sales']) ? number_format($row_total['total_sales'], 2) : "0.00";
                            ?>
          <p class="card-text text-white mb-0">GHS <?php echo $total_sales; ?></p>
        </div>
      </div>
    </div>
  </div>
    <div class="col-sm-6 col-6">
    <div class="card bg-success custom-card">
        <div class="card-header"><h5 class="card-title text-white">Month Sales</h5></div>
      <div class="card-body d-flex align-items-center">
          <i class="fa fa-money text-white" aria-hidden="true" style="font-size: 32px; margin-right: 15px;display: flex; align-items: center;"></i>
        <div>
                                        <?php
                            $query_month = "SELECT SUM(price) AS month_sales 
                                            FROM orders_agent 
                                            WHERE agent_id='$user_id' 
                                            AND MONTH(STR_TO_DATE(order_date, '%W, %M %e, %Y')) = MONTH(CURDATE()) 
                                            AND YEAR(STR_TO_DATE(order_date, '%W, %M %e, %Y')) = YEAR(CURDATE())";
                            $result_month = mysqli_query($con, $query_month);
                            $row_month = mysqli_fetch_assoc($result_month);
                            $month_sales = isset($row_month['month_sales']) ? number_format($row_month['month_sales'], 2) : "0.00";
                            ?>
          <p class="card-text text-white mb-0">GHS <?php echo $month_sales; ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-6">
    <div class="card bg-success custom-card">
    <div class="card-header"><h5 class="card-title text-white">Week Sales</h5></div>
      <div class="card-body d-flex align-items-center">
       <i class="fa fa-money text-white" aria-hidden="true" style="font-size: 32px; margin-right: 15px;display: flex; align-items: center;"></i>
        <div>
            <?php
                            // Convert order_date (e.g. Tuesday, October 15, 2024) to DATE
                            $query_week = "SELECT SUM(price) AS week_sales 
                                           FROM orders_agent 
                                           WHERE agent_id='$user_id' 
                                           AND YEARWEEK(STR_TO_DATE(order_date, '%W, %M %e, %Y'), 1) = YEARWEEK(CURDATE(), 1)";
                            $result_week = mysqli_query($con, $query_week);
                            $row_week = mysqli_fetch_assoc($result_week);
                            $week_sales = isset($row_week['week_sales']) ? number_format($row_week['week_sales'], 2) : "0.00";
                            ?>

          <p class="card-text text-white mb-0">GHS <?php echo $week_sales; ?></p>
        </div>
      </div>
    </div>
  </div>
   <div class="col-sm-6 col-6">
    <div class="card bg-success custom-card">
        <div class="card-header"><h5 class="card-title text-white">Today's Sales</h5></div>
      <div class="card-body d-flex align-items-center">
          <i class="fa fa-money text-white" aria-hidden="true" style="font-size: 32px; margin-right: 15px;display: flex; align-items: center;"></i>
        <div>
            <?php
// Get daily sales for the current agent
$query_daily = "SELECT SUM(price) AS daily_sales 
                FROM orders_agent 
                WHERE agent_id='$user_id' 
                AND STR_TO_DATE(order_date, '%W, %M %e, %Y') = CURDATE()";
$result_daily = mysqli_query($con, $query_daily);
$row_daily = mysqli_fetch_assoc($result_daily);
$daily_sales = isset($row_daily['daily_sales']) ? number_format($row_daily['daily_sales'], 2) : "0.00";
?>

          <p class="card-text text-white mb-0">GHS <?php echo $daily_sales; ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

                                

<style>
    .custom-shadow {
    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); /* Soft shadow */
}
    .custom-border {
        border-radius: 20px;
        overflow: hidden; 
}
</style>
					<!-- Row start -->

<div class="row">
<div class="col-sm-12 col-12 mb-0">
             <?php
                include '../database/dbconfig.php';
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $mtnActivationStatus = mysqli_query($con, "SELECT * FROM agent_mtn_activation");
                        $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
                        if ($mtnActivationRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                        ?>
<div class="card shadow-lg border-0 rounded-1" data-toggle="modal" data-target="#mtnBuyModal"
     style="background: linear-gradient(135deg, #b85e0d 0%, #ffc107 100%); color: white; height: 80px;">
    <!-- FORCE horizontal layout -->
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Text -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/MTN.png" alt="MTN Logo" class="rounded-circle"
                     style="height:45px; width:45px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">MTN Data</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                        <?php    
                            
                        }
                    }
                } 
                ?>
    
</div>


<div class="col-sm-12 col-12 mb-1">
    <?php
                include '../database/dbconfig.php';
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $mtnBigActivation = mysqli_query($con, "SELECT * FROM agent_mtnbig_activation");
                        $mtnBigRow = mysqli_fetch_array($mtnBigActivation);
                        if ($mtnBigRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                         ?>
<div class="card shadow-lg border-0 rounded-4" data-toggle="modal" data-target="#mtnBigBuyModal"
     style="background: linear-gradient(135deg, #b85e0d 0%, #ffc107 100%); color: white; height: 80px;">
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/MTN.png" alt="MTN Logo" class="rounded-circle"
                     style="height:45px; width:45px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">MTN Bigtime Data</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                         <?php
                        }
                    }
                } 
                ?>
    
</div>

    <!-- AT Bundle Card -->
<div class="col-sm-12 col-12 mb-1">
     <?php
                include '../database/dbconfig.php';
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $atStatus = mysqli_query($con, "SELECT * FROM agent_at_activation");
                        $atRow = mysqli_fetch_array($atStatus);
                        if ($atRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                            ?>
<div class="card shadow-lg border-0 rounded-4" data-toggle="modal" data-target="#atBuyModal"
     style="background: linear-gradient(135deg, #4b000a 0%, #1a0033 100%); color: white; height: 80px;">
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/AT.jpg" alt="AT Logo" class="rounded-circle"
                     style="height:45px; width:45px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">AT iShare Data</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                            <?php
                        }
                    }
                } 
                ?>
</div>

<!-- AT BigTime Data Card -->
<div class="col-sm-12 col-12 mb-1">
                    <?php
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $bigAct = mysqli_query($con, "SELECT * FROM special_all_activation");
                        $bigRow = mysqli_fetch_array($bigAct);
                        if ($bigRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                           ?>
<div class="card shadow-lg border-0 rounded-4" 
     data-toggle="modal" data-target="#specialBuyModal"
     style="background: linear-gradient(135deg, #4b000a 0%, #1a0033 100%); 
            color: white; height: 80px;">
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/AT.jpg" alt="AT Logo" 
                     class="rounded-circle"
                     style="height:50px; width:50px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">AT BigTime Data</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                           <?php
                        }
                    }
                } 
                ?>

</div>

<div class="col-sm-12 col-12 mb-1">
        <?php
                include '../database/dbconfig.php';
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $telecelStatus = mysqli_query($con, "SELECT * FROM agent_telecel_activation");
                        $telecelRow = mysqli_fetch_array($telecelStatus);
                        if ($telecelRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                            ?>
<div class="card shadow-lg border-0 rounded-4" 
     data-toggle="modal" data-target="#telecelBuyModal"
     style="background: linear-gradient(135deg, #330000 0%, #b30000 100%);
            color: white; height: 80px;">
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/Telecel.jpg" alt="Telecel Logo" 
                     class="rounded-circle"
                     style="height:40px; width:40px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">Telecel Data</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                            <?php
                        }
                    }
                } 
                ?>

</div>


<div class="col-sm-12 col-12 mb-1">
                    <?php
                include '../database/dbconfig.php';
                $activationStatus = mysqli_query($con, "SELECT * FROM activation");
                if (mysqli_num_rows($activationStatus) > 0) {
                    $row = mysqli_fetch_array($activationStatus);
                    if ($row['status'] == "DEACTIVATED") {
                        echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                    } else {
                        $afaStatus = mysqli_query($con, "SELECT * FROM afa_activation");
                        $afaRow = mysqli_fetch_array($afaStatus);
                        if ($afaRow['status'] == "DEACTIVATED") {
                            echo '<span class="badge badge-danger">OUT OF STOCK</span>';
                        } else {
                            ?>
<div class="card shadow-lg border-0 rounded-4" 
     data-toggle="modal" data-target="#AFABuyModal"
     style="background: linear-gradient(135deg, #b85e0d 0%, #ffc107 100%);
            color: white; height: 80px;">
    <div class="card-body p-3 d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/AFA.jpeg" alt="MTN AFA Logo" 
                     class="rounded-circle"
                     style="height:40px; width:40px; object-fit:cover;">
                    <?php
                     include '../database/dbconfig.php';
                        $select = "SELECT * FROM afa_prices WHERE target='Agent'";
                        $sql = mysqli_query($con, $select);
                        if (mysqli_num_rows($sql) > 0) {
                            while ($row = mysqli_fetch_array($sql)) {
                                ?>
                                                <h5 class="ml-3 mb-0 font-weight-bold">MTN AFA (GHS<?php echo $row['amount']; ?>)</h5>
                    
                                <?php
                            }
                        }
                        ?>

            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>
    </div>
</div>

                            <?php
                        }
                    }
                } 
                ?>
  
</div>

<div class="col-sm-12 col-12 mb-1">
 <?php
include '../database/dbconfig.php';
$activationStatus = mysqli_query($con, "SELECT * FROM waec_activation");
if (mysqli_num_rows($activationStatus) > 0) {
    $row = mysqli_fetch_array($activationStatus);
    if ($row['status'] == "DEACTIVATED") {
        ?>
        <div class="card shadow-lg border-0 rounded-4" 
     
     style="background: linear-gradient(135deg, #b85e0d 0%, #ffc107 100%);
            color: white; height: auto;">
    <div class="card-body p-3">

        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/WAEC.png" alt="WAEC Logo" 
                     class="rounded-circle"
                     style="height:40px; width:40px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">WASSCE Results Checker (GHS20.00)</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>

        <p class="mb-0">Get 5 quantities and above for GHS18</p>
        <span class="badge badge-danger">OUT OF STOCK</span>

    </div>
</div>
        <?php
    } else {
        ?>
<div class="card shadow-lg border-0 rounded-4" 
     data-toggle="modal" data-target="#wassceBuyModal"
     style="background: linear-gradient(135deg, #b85e0d 0%, #ffc107 100%);
            color: white; height: auto;">
    <div class="card-body p-3">

        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="../assets/img/WAEC.png" alt="WAEC Logo" 
                     class="rounded-circle"
                     style="height:40px; width:40px; object-fit:cover;">
                <h5 class="ml-3 mb-0 font-weight-bold">WASSCE Results Checker (GHS20.00)</h5>
            </div>
            <!-- Right: Icon -->
            <i class="fa fa-circle fa-lg ml-3" aria-hidden="true"></i>
        </div>

        <p class="mb-0">Get 5 quantities and above for GHS18</p>

    </div>
</div>
        <?php
    } 
}
?>
   

</div>


</div>

<br>
<br>
<br>
<br>
                  
<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <!--mtn spinner-->
    <script>
        $(document).ready(function() {
    $('#specialmtnBtn').on('click', function() {
        // Show the spinner
        $('#spinner_specialmtn').show();
    });
    });
    </script>
   <!--mtn spinner-->
    <script>
        $(document).ready(function() {
    $('#mtnBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#mtnBtnP').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
       <script>
        $(document).ready(function() {
    $('#mtnBtnC').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
    
      <!--telecel spinner-->
    <script>
        $(document).ready(function() {
    $('#telecelBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_telecel').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#telecelBtnP').on('click', function() {
        // Show the spinner
        $('#spinner_telecel').show();
    });
    });
    </script>
       <script>
        $(document).ready(function() {
    $('#telecelBtnC').on('click', function() {
        // Show the spinner
        $('#spinner_telecel').show();
    });
    });
    </script>
    
      <!--at spinner-->
    <script>
        $(document).ready(function() {
    $('#atBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_at').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#atBtnP').on('click', function() {
        // Show the spinner
        $('#spinner_at').show();
    });
    });
    </script>
       <script>
        $(document).ready(function() {
    $('#atBtnC').on('click', function() {
        // Show the spinner
        $('#spinner_at').show();
    });
    });
    </script>
    
          <!--special spinner-->
    <script>
        $(document).ready(function() {
    $('#specialBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_special').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#specialBtnP').on('click', function() {
        // Show the spinner
        $('#spinner_special').show();
    });
    });
    </script>
       <script>
        $(document).ready(function() {
    $('#specialBtnC').on('click', function() {
        // Show the spinner
        $('#spinner_special').show();
    });
    });
    </script>
    
              <!--call spinner-->
    <script>
        $(document).ready(function() {
    $('#callBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_call').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#callBtnP').on('click', function() {
        // Show the spinner
        $('#spinner_call').show();
    });
    });
    </script>
       <script>
        $(document).ready(function() {
    $('#callBtnC').on('click', function() {
        // Show the spinner
        $('#spinner_call').show();
    });
    });
    </script>
    
<script>
    document.getElementById('mtnrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('mtnmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
<script>
    document.getElementById('telecelrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('telecelmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
<script>
    document.getElementById('atrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('atmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
<script>
    document.getElementById('specialrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('specialmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
<script>
    document.getElementById('specialmtnrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('specialmtnmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
    
    <script>
    document.getElementById('callrecipient').addEventListener('input', function (e) {
        var input = e.target.value;
        var errorMessage = document.getElementById('callmessage');

        // Remove any non-digit characters
        input = input.replace(/\D/g, '');

        // Update the input value
        e.target.value = input;

        // Check if the length is exactly 10 digits
        if (input.length !== 10) {
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
</script>
    
<!--mtn-->
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            var recipientInput = document.getElementById('mtnrecipient');
            var repeatInput = document.getElementById('mtnrepeat');
            var message = document.getElementById('mtnmessage');
            var messageg = document.getElementById('mtnmessagegreen');
            var footer = document.getElementById('footer_mtn');

            function checkRecipientMatch() {
               if (recipientInput.value.length < 10 || recipientInput.value !== repeatInput.value) {
                    message.style.display = 'block';
                    messageg.style.display = 'none';
                    footer.style.display = 'none';
                } else {
                    message.style.display = 'none';
                    messageg.style.display = 'block';
                    footer.style.display = 'block';
                }
            }
            // Add event listeners to both input fields
            recipientInput.addEventListener('input', checkRecipientMatch);
            repeatInput.addEventListener('input', checkRecipientMatch);
        });
    </script>
    
    <!--Telecel-->
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            var recipientInput = document.getElementById('telecelrecipient');
            var repeatInput = document.getElementById('telecelrepeat');
            var message = document.getElementById('telecelmessage');
            var messageg = document.getElementById('telecelmessagegreen');
            var footer = document.getElementById('footer_telecel');

              function checkRecipientMatch() {
               if (recipientInput.value.length < 10 || recipientInput.value !== repeatInput.value) {
                    message.style.display = 'block';
                    messageg.style.display = 'none';
                    footer.style.display = 'none';
                } else {
                    message.style.display = 'none';
                    messageg.style.display = 'block';
                    footer.style.display = 'block';
                }
            }
            // Add event listeners to both input fields
            recipientInput.addEventListener('input', checkRecipientMatch);
            repeatInput.addEventListener('input', checkRecipientMatch);
        });
    </script>
    
    <!--AT-->
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            var recipientInput = document.getElementById('atrecipient');
            var repeatInput = document.getElementById('atrepeat');
            var message = document.getElementById('atmessage');
            var messageg = document.getElementById('atmessagegreen');
            var footer = document.getElementById('footer_at');

              function checkRecipientMatch() {
               if (recipientInput.value.length < 10 || recipientInput.value !== repeatInput.value) {
                    message.style.display = 'block';
                    messageg.style.display = 'none';
                    footer.style.display = 'none';
                } else {
                    message.style.display = 'none';
                    messageg.style.display = 'block';
                    footer.style.display = 'block';
                }
            }
            // Add event listeners to both input fields
            recipientInput.addEventListener('input', checkRecipientMatch);
            repeatInput.addEventListener('input', checkRecipientMatch);
        });
    </script>
    
        <!--Special-->
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            var recipientInput = document.getElementById('specialrecipient');
            var repeatInput = document.getElementById('specialrepeat');
            var message = document.getElementById('specialmessage');
            var messageg = document.getElementById('specialmessagegreen');
            var footer = document.getElementById('footer_special');

              function checkRecipientMatch() {
               if (recipientInput.value.length < 10 || recipientInput.value !== repeatInput.value) {
                    message.style.display = 'block';
                    messageg.style.display = 'none';
                    footer.style.display = 'none';
                } else {
                    message.style.display = 'none';
                    messageg.style.display = 'block';
                    footer.style.display = 'block';
                }
            }
            // Add event listeners to both input fields
            recipientInput.addEventListener('input', checkRecipientMatch);
            repeatInput.addEventListener('input', checkRecipientMatch);
        });
    </script>
    
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            var recipientInput = document.getElementById('specialmtnrecipient');
            var repeatInput = document.getElementById('specialmtnrepeat');
            var message = document.getElementById('specialmtnmessage');
            var messageg = document.getElementById('specialmtnmessagegreen');
            var footer = document.getElementById('footer_specialmtn');

              function checkRecipientMatch() {
               if (recipientInput.value.length < 10 || recipientInput.value !== repeatInput.value) {
                    message.style.display = 'block';
                    messageg.style.display = 'none';
                    footer.style.display = 'none';
                } else {
                    message.style.display = 'none';
                    messageg.style.display = 'block';
                    footer.style.display = 'block';
                }
            }
            // Add event listeners to both input fields
            recipientInput.addEventListener('input', checkRecipientMatch);
            repeatInput.addEventListener('input', checkRecipientMatch);
        });
    </script>
    
            <!--Call-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const recipientInput = document.getElementById('callrecipient');
    const repeatInput = document.getElementById('callrepeat');
    const message = document.getElementById('callmessage');
    const messageg = document.getElementById('callmessagegreen');
    const footer = document.getElementById('footer_call');
    function validatePhoneNumber(input) {
        input.value = input.value.replace(/\D/g, '').slice(0, 10);  // Only allows digits, limits to 10 characters
    }
    function checkRecipientMatch() {
        const recipientValid = recipientInput.value.length === 10;
        const repeatValid = repeatInput.value.length === 10;
        if (recipientInput.value !== repeatInput.value) {
            // Numbers do not match
            message.style.display = 'block';
            messageg.style.display = 'none';
            footer.style.display = 'none';
        } else if (!recipientValid || !repeatValid) {
            // Numbers match but are not 10 digits long
            message.style.display = 'none';
            messageg.style.display = 'none';
            footer.style.display = 'none';
        } else {
            // Numbers match and are valid
            message.style.display = 'none';
            messageg.style.display = 'block';
            footer.style.display = 'block';
        }
    }
    recipientInput.addEventListener('input', function() {
        validatePhoneNumber(recipientInput);
        checkRecipientMatch();
    });
    repeatInput.addEventListener('input', function() {
        validatePhoneNumber(repeatInput);
        checkRecipientMatch();
    });
});

</script>

       <script>
        $(document).ready(function() {
    $('#BtnP').on('click', function() {
        // Show the spinner
        $('#spinner_all').show();
    });
    });
    </script>
			
<!--spinner start-->
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
			
		</div>
			<!-- Page content end -->
        </div>
		</div>
		 <style>
    .whatsapp-icon {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        cursor: pointer;
    }
    </style>

		<!-- Page wrapper end -->
		
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
      
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