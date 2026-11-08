<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../PHPMailer/Exception.php';
require '../../../PHPMailer/PHPMailer.php';
require '../../../PHPMailer/SMTP.php';
include '../../../database/dbconfig.php';

$errors  = array();
$success  = array();
if (isset($_POST['submit'])) {
    // Sanitize user input
    $first_names = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_names = mysqli_real_escape_string($con, $_POST['last_name']);
    $telephone = mysqli_real_escape_string($con, $_POST['tel']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    // Input validation
    if (empty($first_names)) {
        $errors['first_name'] = "First name is required";
    } if (empty($last_names)) {
        $errors['last_name'] = "Last name is required";
    } if (empty($telephone)) {
        $errors['telephone'] = "Telephone is required";
    } if (empty($email)) {
        $errors['email'] = "Email is required";
    } if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['emailvalid'] = "Enter a valid email";
    } if (empty($password)) {
        $errors['password'] = "Password is required";
    } if (empty($confirm_password)) {
        $errors['confirm_password'] = "Confirm Password is required";
    } if (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Password must include capital letters";
    } if (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = "Password must include small letters";
    } if (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Password must include numbers";
    } if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['password'] = "Password must include symbols";
    } if (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    } if ($password !== $confirm_password) {
        $errors['password'] = "Passwords do not match";
    }

    $first_name = strtoupper($first_names);
    $last_name = strtoupper($last_names);

    if (empty($errors)) {
        $register_date = date('Y-m-d');
        $date4 = date('ymd');
        $hashed_password = password_hash($confirm_password, PASSWORD_DEFAULT);
        $user_id = bin2hex(random_bytes(20)) . $date4 . bin2hex(random_bytes(20));
        $verified = "false";

        // ✅ Check for duplicate email
        $select = mysqli_query($con, "SELECT 1 FROM admin WHERE email = '$email' LIMIT 1");
        if (mysqli_num_rows($select) > 0) {
            $errors['email_exist'] = "Account already exists. Go back to login.";
        } else {
            // ✅ Get new verify_code
            $verify_code_result = mysqli_query($con, "SELECT MAX(verify_code) AS max_verify_code FROM admin");
            $row = mysqli_fetch_assoc($verify_code_result);
            $verify_code = $row && $row['max_verify_code'] !== null ? $row['max_verify_code'] + 1 : random_int(100000, 999999);

            // ✅ Insert account
            $insertquery = "INSERT INTO admin (
                user_id, first_name, last_name, email, telephone, password, verified, verify_code, register_date) VALUES 
                ('$user_id', '$first_name', '$last_name', '$email', '$telephone', '$hashed_password', '$verified', '$verify_code', '$register_date')";
            if (mysqli_query($con, $insertquery)) {
                    $mail = new PHPMailer(true);
                    try {
                    $mail->isSMTP();
                    $mail->Host = 'de102.aveshost.net';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@powermynetworks.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
                    $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = "Account Verification";
                        $mail->Body = "
                            <html>
                            <body>
                                <p>Dear Admin,</p>
                                <p>Thanks for creating your account.</p>
                                <p>Click or copy the link to verify your account:</p>
                                <p><a href='https://admin.powermynetworks.com/auth/register/verify?email=$email&verify_code=$verify_code&user_id=$user_id'>
                                    Verify My Account</a></p>
                                <p>Have a great day!</p>
                                <p><strong>POWERMYNET</strong></p>
                            </body>
                            </html>";
                        $mail->send();
                        $success['success'] = "Verification code sent to your email (check inbox/spam).";
                    } catch (Exception $e) {
                        $errors['email_send'] = "Email sending failed: {$mail->ErrorInfo}";
                    }
            } else {
                $errors['registration'] = "Failed to register account.";
            }
        }
    }
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
      <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('../../img/banner.jpg');">
        <span class="mask bg-gradient-dark opacity-6"></span>
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5 text-center mx-auto">
              <h1 class="text-white mb-2 mt-5">Administrator Registration</h1>
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
                    <?php if(count($success)>0): ?>
                    <div class="alert alert-success" role="alert" style="border-left:5px solid green; color:white">
                    <?php foreach($success as $succes): ?>
                    <div><?php echo $succes; ?></div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?> 
                    <?php if(count($errors)>0): ?>
                    <div class="alert alert-danger" role="alert" style="border-left:5px solid red; color:white">
                    <?php foreach($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?> 
              <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                  <div class="mb-3">
                    <input type="text" class="form-control" placeholder="First Name" name="first_name" aria-label="Name" aria-describedby="email-addon">
                      
                  </div>
                   <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Last Name" name="last_name" aria-label="Name" aria-describedby="email-addon">
                      
                  </div>
                  <div class="mb-3">
                    <input type="tel" class="form-control" placeholder="Telephone" name="tel" aria-label="Email" aria-describedby="email-addon">
                  </div>
                  <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" aria-label="Email" aria-describedby="email-addon">
                  </div>
                  <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Password" name="password" aria-label="Password" aria-describedby="password-addon">
                  </div>
                   <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Re-enter Password" name="confirm_password" aria-label="Password" aria-describedby="password-addon">
                  </div>

                  <div class="text-center">
                      <div class="spinner" id="spinner_new" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>     Please Wait...
                              </div>
                    <button type="submit" name="submit" class="btn bg-gradient-dark w-100 my-4 mb-2" id="submitBtn">Sign up</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
   
  </main>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
    $('#submitBtn').on('click', function() {
        // Show the spinner
        $('#spinner_new').show();
    });
    });
    </script>
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