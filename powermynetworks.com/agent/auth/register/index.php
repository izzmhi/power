<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../../database/dbconfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../PHPMailer/Exception.php';
require '../../../PHPMailer/PHPMailer.php';
require '../../../PHPMailer/SMTP.php';

if (isset($_POST['submit'])) {
    // Fetch and sanitize user input
    $first_names = trim($_POST['first_name']);
    $last_names = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['tel']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $status = "APPROVED";

    // Validation
    $errors = [];

    if (empty($first_names)) $errors[] = "First name is required";
    if (empty($last_names)) $errors[] = "Last name is required";
    if (empty($telephone)) $errors[] = "Telephone is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($confirm_password)) $errors[] = "Confirm Password is required";

    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters long";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must include at least one uppercase letter";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must include at least one lowercase letter";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must include at least one number";
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Password must include at least one special character";

    if (!empty($errors)) {
        header("Location: index.php?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    // Format names
    $first_name = strtoupper($first_names);
    $last_name = strtoupper($last_names);

    // Check if user already exists (Prepared Statement)
    $stmt = mysqli_prepare($con, "SELECT email FROM account_agents WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Account already exists. Go back to login to continue.";
        header("Location: index.php?error=" . urlencode($error));
        exit();
    }
    mysqli_stmt_close($stmt);

    // Prepare user data
    $register_date = date('Y-m-d');
    $date4 = date('ymd');
    $hpassword = password_hash($password, PASSWORD_DEFAULT);
    $cl = bin2hex(random_bytes(10));
    $cld = bin2hex(random_bytes(10));
    $v_code = random_int(100000, 999999);
    $verify_code = $v_code . $cl;
    $user_id = $cl . $date4 . $cld . $v_code;
    $verified = "True";

    // Insert into account_agents (Prepared Statement)
    $stmt = mysqli_prepare($con, "INSERT INTO account_agents (user_id, first_name, last_name, email, telephone, password, verified, verify_code, register_date, status, unique_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $unique_id = 'NOT YET';
    mysqli_stmt_bind_param($stmt, "sssssssssss", $user_id, $first_name, $last_name, $email, $telephone, $hpassword, $verified, $verify_code, $register_date, $status, $unique_id);

    if (!mysqli_stmt_execute($stmt)) {
        $error = "Failed to create account: " . mysqli_error($con);
        header("Location: index.php?error=" . urlencode($error));
        exit();
    }
    mysqli_stmt_close($stmt);

    // Insert into agents (Prepared Statement)
    $amount = 0;
    $wallet = 0;
    $agent_type = 'AGENT';
    $stmt = mysqli_prepare($con, "
        INSERT INTO agents 
        (user_id, first_name, last_name, email, telephone, amount, wallet, agent_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sssssiis", $user_id, $first_name, $last_name, $email, $telephone, $amount, $wallet, $agent_type);

    if (!mysqli_stmt_execute($stmt)) {
        $error = "Failed to create agent portal.";
        header("Location: index.php?error=" . urlencode($error));
        exit();
    }
    mysqli_stmt_close($stmt);

    // Send verification email (same logic)
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
        $mail->isHTML(true);
        $mail->Subject = "Account Registration";
        $mail->Body = "
            <html>
                <body>
                    <p>Dear Partner,</p>
                    <p>Thanks for creating your account.</p>
                    <p>Best regards,<br>POWERMYNET</p>
                </body>
            </html>
        ";
        $mail->send();
        $success = "Registration successful. Login to your account now.";
        header("Location: ../login?success=" . urlencode($success));
    } catch (Exception $e) {
        $error = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
        header("Location: index.php?error=" . urlencode($error));
    }

    // Clear sensitive variables
    unset($_POST['password'], $_POST['confirm_password'], $password, $confirm_password, $hpassword);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
	<!--Head-->
	<?php include '../parts/head.php'; ?> 
</head>
<body class="">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
<!--Head-->
		<?php include '../parts/header.php'; ?> 
  </nav>
  <!-- End Navbar -->
  <main class="main-content  mt-0">
    <section class="min-vh-100 mb-8">
      <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('../../asset/images/banner.jpg');">
        <span class="mask bg-gradient-dark opacity-6"></span>
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5 text-center mx-auto">
              <h1 class="text-white mb-2 mt-5">Partner Registration</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row mt-lg-n10 mt-md-n11 mt-n10">
          <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
            <div class="card z-index-0">
              <div class="card-header text-center pt-4">
                <h5>Fill in your details</h5>
              </div>
                    <?php if(isset($_GET['success'])){
                    ?>
                    <div class="alert alert-success" role="alert" style="border-left:5px solid green; color:white">
                    <div><?php echo $_GET['success']; ?></div>
                    </div>
                    <?php } ?> 
                    
                    <?php if(isset($_GET['error'])){
                    ?>
                    <div class="alert alert-danger" role="alert" style="border-left:5px solid red; color:white">
                    <div><?php echo $_GET['error']; ?></div>
                    </div>
                    <?php } ?> 
                
<div class="card-body">
  <form id="signupForm" action="" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <input type="text" class="form-control" placeholder="First Name" name="first_name" required>
      </div>
      <div class="mb-3">
        <input type="text" class="form-control" placeholder="Last Name" name="last_name" required>
      </div>
      <div class="mb-3">
        <input type="tel" class="form-control" placeholder="Telephone" name="tel" required>
      </div>
      <div class="mb-3">
        <input type="email" class="form-control" placeholder="Email" name="email" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" placeholder="Password" name="password" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" placeholder="Re-enter Password" name="confirm_password" required>
      </div>

<div class="text-center">
  <button type="submit" name="submit" class="btn bg-gradient-info w-100 mt-4 mb-0 d-flex justify-content-center align-items-center" id="submitBtn" style="gap: 8px;">
    <span class="spinner-border spinner-border-sm" id="spinner_new" style="display: none;" role="status" aria-hidden="true"></span>
    <span id="btnText">Sign Up</span>
  </button>
</div>
  </form>
</div>

<script>
    $(document).ready(function() {
       $('#submitBtn').on('click', function() {
        // Show the spinner
        $('#spinner_new').show();
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
          </div>
        </div>
      </div>
    </section>
   
  </main>
  
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>