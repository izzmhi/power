<?php
// Make sessions last 24 hours
$cookie_lifetime = 24 * 60 * 60;
session_set_cookie_params([
    'lifetime' => $cookie_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', $cookie_lifetime);
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include'../database/dbconfig.php';
   function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
                 || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host     = $_SERVER['HTTP_HOST'];   // Domain name
    $request  = $_SERVER['REQUEST_URI']; // Path and query string
    return $protocol . $host . $request;
}

// Example usage
$url = getFullUrl();

    $query = "SELECT id,user_id,agent_name,email,telephone,activeness,status,shop,shop_full,url FROM shop WHERE url = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $url);
    $stmt->execute();
    $stmt->bind_result($id,$user_id,$agent_name,$email,$telephone,$activeness,$status,$shop,$shop_name,$url);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['agent_name'] = $agent_name;
    $_SESSION['agent_email'] = $email;
    $_SESSION['agent_telephone'] = $telephone;
    $_SESSION['activeness'] = $activeness;
    $_SESSION['status'] = $status;
    $_SESSION['shop_id'] = $id;
    $_SESSION['shop'] = $shop;
    $_SESSION['shop_name'] = $shop_name;
    $_SESSION['url'] = $url;
    
// Now session is restored or shop is invalid
if (!isset($_SESSION['shop'])) {
    // Redirect to homepage if shop is invalid
    header("Location: $url");
    exit();
}

if (isset($_POST['afa'])) {

    $fullname     = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_SPECIAL_CHARS);
    $beneficiary  = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_number    = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_SPECIAL_CHARS);
    $location     = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
    $region       = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS);
    $occupation   = filter_input(INPUT_POST, 'occupation', FILTER_SANITIZE_SPECIAL_CHARS);
    $email        = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $dob          = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_SPECIAL_CHARS);

    $errors = [];

    if (empty($fullname)) {
        $errors['fullname'] = "Fullname is required";
    } elseif (empty($id_number)) {
        $errors['id_number'] = "Ghana card number is required";
    } elseif (empty($beneficiary)) {
        $errors['beneficiary'] = "Recipient number is required";
    }

    // ==============================
    // IMAGE VALIDATION FUNCTION
    // ==============================
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

    // ==============================
    // PROCESS ONLY IF NO FORM ERRORS
    // ==============================
    if (empty($errors)) {

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

        $_SESSION['fullname']   = $fullname;
        $_SESSION['id_number']  = $id_number;
        $_SESSION['location']   = $location;
        $_SESSION['beneficiary']= $beneficiary;
        $_SESSION['region']     = $region;
        $_SESSION['occupation'] = $occupation;
        $_SESSION['dob']        = $dob;
        $_SESSION['email']      = $email;

        header("Location: /{$_SESSION['shop']}/pay_afa.php");
        exit();
    }
}

if(isset($_POST['mtn'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
   if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
		$_SESSION['package'] = $package;
		$_SESSION['network'] = $network;
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
header("Location: /{$_SESSION['shop']}/pay.php");
exit();
    }
}
	
if(isset($_POST['at'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
		$_SESSION['package'] = $package;
		$_SESSION['network'] = "AT";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
        header("location:/{$_SESSION['shop']}/pay_at.php");
	}
}
	
if(isset($_POST['telecel'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
		$_SESSION['package'] = $package;
		$_SESSION['network'] = "TELECEL";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
        header("location:/{$_SESSION['shop']}/pay_telecel.php");
	}
}
	
//--Special offers
if(isset($_POST['special'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
		$_SESSION['package'] = $package;
		$_SESSION['network'] = "AT";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
			  
        header("location:/{$_SESSION['shop']}/pay_special.php");
	}
}
	
	//--Special offers
if(isset($_POST['special_mtn'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
		$_SESSION['package'] = $package;
		$_SESSION['network'] = "MTN";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
        header("location:/{$_SESSION['shop']}/pay_mtn_big.php");
	}
}
	
if(isset($_POST['afa'])){
    $fullnames = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_SPECIAL_CHARS);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_number = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_SPECIAL_CHARS);
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
    if (empty($errors)) { // Proceed only if there are no errors
        $_SESSION['fullnames'] = $fullnames;
        $_SESSION['telephone'] = $telephone;
        $_SESSION['id_type'] = "Ghana Card";
        $_SESSION['id_number'] = $id_number;
        $_SESSION['location'] = $location;
        $_SESSION['dob'] = $dob;
        $_SESSION['region'] = $region;
        $_SESSION['email'] = $email;
        $_SESSION['price'] = $price;
        header("Location: /{$_SESSION['shop']}/pay_afa.php");
        exit(); 
    } else {
        // Optionally, store errors in session to display back on the form page
        $_SESSION['errors'] = $errors;
    }
}
	
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>HOME | <?php echo $_SESSION['shop_name']; ?></title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="images/Power.png" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="assets/css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="<?php echo $_SESSION['url'] ?>"><?php echo $_SESSION['shop_name']; ?></a>
                <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu<i class="fas fa-bars"></i> </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="<?php echo $_SESSION['url'] ?>">Home</a></li>
                        <li class="nav-item mx-0 mx-lg-1">
                            <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/<?php echo $_SESSION['shop']; ?>/check.php">Check Status</a>
                        </li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="/<?php echo $_SESSION['shop']; ?>/reorder.php">Order Again</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead bg-primary text-white text-center" style="height:50px;">
            <div class="container d-flex align-items-center flex-column" style="margin-top:-50px;">
                <p class="masthead-subheading font-weight-light mb-0">Tap on a package to buy</p>
                <h5 class="masthead-subheading font-weight-light mb-0">Support  <strong>number-<?php echo $_SESSION['agent_telephone']; ?></strong></h5>
                <p class="bg-danger text-white p-2">Take Note: Complaint should be made within 24 hours after order delivered but not received.</p>
            </div>
        </header>
      
        <!-- MTN Modal-->
        <div class="portfolio-modal modal fade" id="mtnModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Hidden Network -->
          <input type="hidden" name="network" value="MTN" readonly>

          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listmtn" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i>Select a Package (GIG)
            </label>
            <select required name="package" class="form-select" id="listmtn">
              <option value="">-Select a Package-</option>
              <?php
                include '../database/dbconfig.php';
                $network = "MTN";
                $agent_id = $_SESSION['user_id'];
                $select = "SELECT amount,package FROM agents_prices_packages WHERE agent_id='$agent_id' AND network='$network' ORDER BY CAST(package AS UNSIGNED)";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        echo "<option value='{$row['package']}'>{$row['package']}GIG - GHS{$row['amount']}</option>";
                    }
                }
              ?>
            </select>
          </div>

          <!-- Price & Email & Recipient Fields -->
          <div class="row" id="mtn-price-container">
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
              </label>
              <input type="email" class="form-control" placeholder="e.g. example@mail.com" name="email" required>
            </div>
            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-phone me-2 text-secondary"></i>Recipient Number
              </label>
              <input type="tel" class="form-control" id="mtn_recipient" maxlength="10"
                pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0551234567" name="beneficiary"
                oninput="mtnvalidatePhoneNumber(this)" required>
              <small class="text-muted fst-italic">* Bundle sent to a wrong number is non-refundable</small>
            </div>
            <!-- Confirm Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-check-double me-2 text-secondary"></i>Confirm Recipient Number
              </label>
              <input type="tel" class="form-control" id="mtn_repeat" placeholder="Re-enter recipient number"
                name="repeat" required>
              <small id="mtn_matchMessage" class="form-text"></small>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="mtn" class="btn btn-primary w-100" id="mtnBtn">
            <i class="fas fa-paper-plane me-2"></i>PROCEED
          </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal">
                <i class="fas fa-xmark fa-fw"></i>
                Close Window
            </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- telecel Modal-->
        <div class="portfolio-modal modal fade" id="telecelModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Hidden Network -->
          <input type="hidden" name="network" value="TELECEL" readonly>

          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listmtn" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i>Select a Package (GIG)
            </label>
            <select required name="package" class="form-select" id="listtelecel">
              <option value="">-Select a Package-</option>
              <?php
                include '../database/dbconfig.php';
                $network = "TELECEL";
                $agent_id = $_SESSION['user_id'];
                $select = "SELECT amount,package FROM agents_prices_packages WHERE agent_id='$agent_id' AND network='$network' ORDER BY CAST(package AS UNSIGNED)";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        echo "<option value='{$row['package']}'>{$row['package']}GIG - GHS{$row['amount']}</option>";
                    }
                }
              ?>
            </select>
          </div>

          <!-- Price & Email & Recipient Fields -->
           <div class="row" id="telecel-price-container">

            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
              </label>
              <input type="email" class="form-control" placeholder="e.g. example@mail.com" name="email" required>
            </div>

            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-phone me-2 text-secondary"></i>Recipient Number
              </label>
              <input type="tel" class="form-control" id="telecel_recipient" maxlength="10"
                pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0201234567" name="beneficiary"
                required>
              <small class="text-muted fst-italic">* Bundle sent to a wrong number is non-refundable</small>
            </div>

            <!-- Confirm Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-check-double me-2 text-secondary"></i>Confirm Recipient Number
              </label>
              <input type="tel" class="form-control" id="telecel_repeat" placeholder="Re-enter recipient number"
                name="repeat" required>
              <small id="telecel_matchMessage" class="form-text"></small>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="telecel" class="btn btn-primary w-100" id="telecelBtn">
            <i class="fas fa-paper-plane me-2"></i>PROCEED
          </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal">
                <i class="fas fa-xmark fa-fw"></i>
                Close Window
            </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- AT Modal-->
        <div class="portfolio-modal modal fade" id="atModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Hidden Network -->
          <input type="hidden" name="network" value="AT" readonly>
          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listat" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i>Select a Package (GIG)
            </label>
            <select required name="package" class="form-select" id="listat">
              <option value="">-Select a Package-</option>
              <?php
                include '../database/dbconfig.php';
                $network = "AT";
                $agent_id = $_SESSION['user_id'];
                $select = "SELECT amount,package FROM agents_prices_packages WHERE agent_id='$agent_id' AND network='$network' ORDER BY CAST(package AS UNSIGNED)";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        echo "<option value='{$row['package']}'>{$row['package']}GIG - GHS{$row['amount']}</option>";
                    }
                }
              ?>
            </select>
          </div>

          <!-- Price & Email & Recipient Fields -->
          <div class="row" id="at-price-container">
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
              </label>
              <input type="email" class="form-control" placeholder="e.g. example@mail.com" name="email" required>
            </div>

            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-phone me-2 text-secondary"></i>Recipient Number
              </label>
              <input type="tel" class="form-control" id="at_recipient" maxlength="10"
                pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0261234567" name="beneficiary"
                required>
              <small class="text-muted fst-italic">* Bundle sent to a wrong number is non-refundable</small>
            </div>

            <!-- Confirm Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-check-double me-2 text-secondary"></i>Confirm Recipient Number
              </label>
              <input type="tel" class="form-control" id="at_repeat" placeholder="Re-enter recipient number"
                name="repeat" required>
              <small id="at_matchMessage" class="form-text"></small>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="at" class="btn btn-primary w-100" id="atBtn">
            <i class="fas fa-paper-plane me-2"></i>PROCEED
          </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-xmark fa-fw"></i> Close Window </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- AT BIG Modal-->
        <div class="portfolio-modal modal fade" id="atbigModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Hidden Network -->
          <input type="hidden" name="network" value="AT" readonly>

          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listspecial" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i>Select a Package (GIG)
            </label>
            <select required name="package" class="form-select" id="listspecial">
              <option value="">-Select a Package-</option>
              <?php
              include '../database/dbconfig.php';
              $agent_id = $_SESSION['user_id'];
              $network = "AT";
              $select = "SELECT DISTINCT * FROM agents_prices_special WHERE agent_id = '$agent_id' AND network='$network' ORDER BY CAST(package AS UNSIGNED)";
              $sql = mysqli_query($con, $select);
              if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_array($sql)) {
             echo "<option value='{$row['package']}'>{$row['package']}GIG - GHS{$row['amount']}</option>";
                }
              }
              ?>
            </select>
          </div>

          <!-- Price & Email & Recipient Fields -->
           <div class="row" id="special-price-container">
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
              </label>
              <input type="email" class="form-control" placeholder="e.g. example@mail.com" name="email" required>
            </div>

            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-phone me-2 text-secondary"></i>Recipient Number
              </label>
              <input type="tel" class="form-control" id="special_recipient" maxlength="10"
                pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0271234567" name="beneficiary"
                required>
              <small class="text-muted fst-italic">* Bundle sent to a wrong number is non-refundable</small>
            </div>

            <!-- Confirm Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-check-double me-2 text-secondary"></i>Confirm Recipient Number
              </label>
              <input type="tel" class="form-control" id="special_repeat" placeholder="Re-enter recipient number"
                name="repeat" required>
              <small id="special_matchMessage" class="form-text"></small>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="special" class="btn btn-primary w-100" id="specialBtn">
            <i class="fas fa-paper-plane me-2"></i>PROCEED
          </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal">
                <i class="fas fa-xmark fa-fw"></i>
                Close Window
            </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
          <!-- MTN BIG Modal-->
        <div class="portfolio-modal modal fade" id="mtnbigModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Hidden Network -->
          <input type="hidden" name="network" value="MTN" readonly>

          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listspecialmtn" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i>Select a Package (GIG)
            </label>
            <select required name="package" class="form-select" id="listspecialmtn">
              <option value="">-Select a Package-</option>
              <?php
              include '../database/dbconfig.php';
              $agent_id = $_SESSION['user_id'];
              $network = "MTN";
              $select = "SELECT DISTINCT * FROM agents_prices_special WHERE agent_id = '$agent_id' AND network='$network' ORDER BY CAST(package AS UNSIGNED)";
              $sql = mysqli_query($con, $select);
              if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_array($sql)) {
                   echo "<option value='{$row['package']}'>{$row['package']}GIG - GHS{$row['amount']}</option>";
                }
              }
              ?>
            </select>
          </div>

          <!-- Price & Email & Recipient Fields -->
       <div class="row" id="specialmtn-price-container">
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-envelope me-2 text-secondary"></i>Email Address</label>
              <input type="email" class="form-control" placeholder="e.g. example@mail.com" name="email" required>
            </div>

            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-phone me-2 text-secondary"></i>Recipient Number</label>
              <input type="tel" class="form-control" id="specialmtn_recipient" maxlength="10"
                pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0271234567" name="beneficiary" required>
              <small class="text-muted fst-italic">* Bundle sent to a wrong number is non-refundable</small>
            </div>

            <!-- Confirm Recipient Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-check-double me-2 text-secondary"></i>Confirm Recipient Number
              </label>
              <input type="tel" class="form-control" id="specialmtn_repeat" placeholder="Re-enter recipient number"
                name="repeat" required>
              <small id="specialmtn_matchMessage" class="form-text"></small>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="special_mtn" class="btn btn-primary w-100" id="specialmtnBtn">
            <i class="fas fa-paper-plane me-2"></i>PROCEED
          </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal">
                <i class="fas fa-xmark fa-fw"></i>
                Close Window
            </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
                 <!-- AFA-->
        <div class="portfolio-modal modal fade" id="afaModal" tabindex="-1" aria-labelledby="portfolioModal1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
       <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body py-4 px-4">
          <!-- Package Selection -->
          <div class="mb-3">
            <label for="listspecialmtn" class="form-label">
              <i class="fas fa-database me-2 text-secondary"></i> Price: GHS 
                <?php
              include '../database/dbconfig.php';
              $agent_id = $_SESSION['user_id'];
              $select = "SELECT amount FROM agents_prices_afa WHERE agent_id = '$agent_id' ";
              $sql = mysqli_query($con, $select);
              if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_array($sql)) {
                   echo $row['amount'];
                }
              }
              ?>
            </label>
          </div>

          <!-- Price & Email & Recipient Fields -->
       <div class="row">
                      <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Email</label>
              <input type="email" class="form-control" placeholder="Enter your Email" name="email" required>
            </div>
           
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Fullname</label>
              <input type="text" class="form-control" placeholder="Enter your fullname" name="fullname" required>
            </div>

            <!-- Recipient Number -->
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-phone me-2 text-secondary"></i>Phone Number</label>
              <input type="tel" class="form-control" maxlength="10" pattern="\d{10}" title="Enter a valid 10-digit number" placeholder="e.g. 0271234567" name="beneficiary" required>
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Ghana ID Number</label>
              <input type="text" class="form-control" placeholder="Ghana card number" name="id_number" required>
            </div>
 
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Location / Town</label>
              <input type="text" class="form-control" placeholder="location or town" name="location" required>
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Region</label>
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
 
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Occupation</label>
              <input type="text" class="form-control" placeholder="occupation" name="occupation" required>
            </div>
            
                        <!-- Email -->
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-check-double me-2 text-secondary"></i>Date of Birth</label>
              <input type="date" class="form-control" placeholder="Date of Birth" name="dob" required>
            </div>
            
  <div class="mb-3">
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
            
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer justify-content-between px-4 pb-4">
          <!-- Submit Button -->
          <button type="submit" name="afa" class="btn btn-primary w-100" id="afaBtn"><i class="fas fa-paper-plane me-2"></i>PROCEED </button>
        </div>
      </form>
            <!-- Portfolio Modal - Text-->
            <button class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-xmark fa-fw"></i>Close Window </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        
        <!-- Portfolio Section-->
        <section class="page-section portfolio" id="portfolio">
                       <div class="container" style="margin-top:-80px;">
                
<?php
include '../database/dbconfig.php';
$agent_id = $_SESSION['user_id'];

$showWarning = false;

// -------------------------------
// 1. CHECK FIRST TABLE
// -------------------------------
$packagesQuery = "SELECT agent_price, amount FROM agents_prices_packages WHERE agent_id='$agent_id'";
$packagesResult = mysqli_query($con, $packagesQuery);

if (mysqli_num_rows($packagesResult) > 0) {
    while ($row = mysqli_fetch_assoc($packagesResult)) {

        if ($row['agent_price'] > $row['amount']) {
            $showWarning = true;
            break; // stop checking
        }
    }
}

// -------------------------------
// 2. CHECK SECOND TABLE (ONLY IF NO WARNING YET)
// -------------------------------
if (!$showWarning) {

    $specialQuery = "SELECT agent_price, amount FROM agents_prices_special WHERE agent_id='$agent_id'";
    $specialResult = mysqli_query($con, $specialQuery);

    if (mysqli_num_rows($specialResult) > 0) {
        while ($row = mysqli_fetch_assoc($specialResult)) {

            if ($row['agent_price'] > $row['amount']) {
                $showWarning = true;
                break;
            }
        }
    }
}

// -------------------------------
// 3. IF WARNING FOUND → SHOW IT ONLY ONCE
// -------------------------------
if ($showWarning) {
?>
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-12 col-12 mb-5">
            <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                WARNING
            </h5>
            <p class="text-center">There is a price change. Contact support for help.</p>
        </div>
    </div>
<?php
    exit(); // STOP HERE – DO NOT PROCEED TO STOCK CHECK
}

// ----------------------------------------------------
// 2. Now check ACTIVATION (only if no price warning)
// ----------------------------------------------------

$activationStatus = mysqli_query($con, "SELECT * FROM activation LIMIT 1");
if ($activationStatus && mysqli_num_rows($activationStatus) > 0) {

    $actRow = mysqli_fetch_assoc($activationStatus);

    if ($actRow['status'] === "DEACTIVATED") {
?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                   ALL PRODUCTS ARE OUT OF STOCK
                </h5>
            </div>
        </div>
<?php
        exit(); // stop — no further processing
    }
}

// Check shop activation now
$bigAct = mysqli_query($con, "SELECT * FROM shop_activation LIMIT 1");
if ($bigAct && mysqli_num_rows($bigAct) > 0) {

    $bigRow = mysqli_fetch_assoc($bigAct);

    if ($bigRow['status'] === "DEACTIVATED") {
?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    OUT OF STOCK
                </h5>
            </div>
        </div>
<?php
        exit();
    }
}
?>
    <!-- Portfolio Grid Items-->
    <div class="row justify-content-center">
        <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM agent_mtn_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    MTN
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                       <!-- MTN-->
                    <div class="col-md-6 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> MTN</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#mtnModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/MTN.png" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
    <?php
    }
    ?>
    
        <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM agent_mtnbig_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    MTN BIGTIME
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                        <!-- MTN BIG-->
                 <div class="col-md-6 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> MTN BigTime</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#mtnbigModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/MTN.png" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
    <?php
    }
    ?>
    
        <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM agent_telecel_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    TELECEL
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                       <!-- TELECEL-->
                 <div class="col-md-6 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> Telecel</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#telecelModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/Telecel.jpg" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
    <?php
    }
    ?>
    
        <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM agent_at_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    AT
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                       <!-- AT -->
                 <div class="col-md-4 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> AT iShare</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#atModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/AT.jpg" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
    <?php
    }
    ?>
    
            <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM special_all_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    AT BIGTIME
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                      <!-- AT SPECIAL-->
                 <div class="col-md-4 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> AT BigTime</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#atbigModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/AT.jpg" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
                
    <?php
    }
    ?>
    
                <?php
    $mtnActivationStatus = mysqli_query($con, "SELECT * FROM afa_activation");
    $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
    if ($mtnActivationRow['status'] == "DEACTIVATED") {
    ?>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12 col-12 mb-5">
                <h5 class="masthead-subheading font-weight-light mb-0 text-white bg-danger rounded text-center w-100 py-2">
                    MTN AFA
                </h5>
                <p class="text-center">OUT OF STOCK</p>
            </div>
        </div>
<?php
    } else {
    ?>
                      <!-- AFA-->
                 <div class="col-md-4 col-lg-4 col-6 mb-5 ">
                       <p class="masthead-subheading font-weight-light mb-0 text-white bg-dark rounded text-center w-100 py-2"> MTN AFA</p>
                        <div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#afaModal">
                            <div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/AFA.jpeg" style="height:150px; width:100%;" alt="..." />
                        </div>
                    </div>
                </div>
    <?php
    }
    ?>
    

            </div>
        </section>
        <!-- Footer-->
        <footer class="footer text-center">
            <div class="container">
                <div class="row">
                    <!-- Footer Location-->
                    <!-- Footer About Text-->
                    <div class="col-lg-12 col-12">
                        <h4 class="text-uppercase">PMNETSHOP</h4>
                        <div class="col-lg-12 col-12"> <h6>Support number: <?php echo $_SESSION['agent_telephone']; ?></h6> </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Copyright Section-->
        <div class="copyright py-4 text-center text-white">
            <div class="container"><small>Copyright &copy; Design by Tyler</small></div>
        </div>
 
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    $('#mtnBtn').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
         <!--telecel spinner-->
    <script>
        $(document).ready(function() {
    $('#telecelBtn').on('click', function() {
        // Show the spinner
        $('#spinner_telecel').show();
    });
    });
    </script>
         <!--at spinner-->
    <script>
        $(document).ready(function() {
    $('#atBtn').on('click', function() {
        // Show the spinner
        $('#spinner_at').show();
    });
    });
    </script>
    
        <script>
        $(document).ready(function() {
    $('#specialBtn').on('click', function() {
        // Show the spinner
        $('#spinner_special').show();
    });
    });
    </script>
    
            <script>
        $(document).ready(function() {
    $('#afaBtn').on('click', function() {
        // Show the spinner
        $('#spinner_afa').show();
    });
    });
    </script>
    
    
    <script>
      function mtnvalidatePhoneNumber(input) {
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Check if the input is less than 10 digits
    if (input.value.length < 10) {
        input.setCustomValidity("Please enter a valid 10-digit number.");
    } else {
        input.setCustomValidity(""); // Reset the error message
    }
    }
    </script>    
    
<script>
    document.getElementById('mtn_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('mtn_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('mtn_matchMessage');
        const proceedButton = document.getElementById('mtn_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
            proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('at_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('at_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('at_matchMessage');
        const proceedButton = document.getElementById('at_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('telecel_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('telecel_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('telecel_matchMessage');
        const proceedButton = document.getElementById('telecel_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('special_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('special_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('special_matchMessage');
        const proceedButton = document.getElementById('special_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>       

<script>
    document.getElementById('specialmtn_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('specialmtn_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('specialmtn_matchMessage');
        const proceedButton = document.getElementById('specialmtn_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script> 
          
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="assets/js/scripts.js"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
