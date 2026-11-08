<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['email']) || !isset($_GET['verify_code']) || !isset($_GET['user_id'])) {
    header('Location: ../../../');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../../../../PHPMailer/Exception.php';
require '../../../../PHPMailer/PHPMailer.php';
require '../../../../PHPMailer/SMTP.php';

include '../../../../database/dbconfig.php';

if(isset($_GET['verify_code'])) {
    $errors = array();

    // Sanitize input
    $email = mysqli_real_escape_string($con, $_GET['email']);
    $verified_code = mysqli_real_escape_string($con, $_GET['verify_code']);
    $user_id = mysqli_real_escape_string($con, $_GET['user_id']);
    $verified = "True";

    // Check if account exists with the provided details
    $selectquery = "SELECT * FROM account_agents WHERE email='$email' AND user_id='$user_id' AND verify_code='$verified_code'";
    $select = mysqli_query($con, $selectquery);

    if ($select && mysqli_num_rows($select) > 0) {
        // Update the account as verified
        $updatequery = "UPDATE account_agents SET verified='$verified' WHERE email='$email' AND user_id='$user_id' AND verify_code='$verified_code' LIMIT 1";
        $uquery = mysqli_query($con, $updatequery);
        if ($uquery) {
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
                    $mail->Subject = "Account Verification";
                    $mail->Body = "
                    <html>
                <body>
                   <div class='container-xxl bg-white p-0'>
                   <div class='container-xxl position-relative p-0'>
                     <br>
                 <p>Dear Agent,</p>
                 <p> Your account is verified. Login to start placing orders</p>
                    <p>Have a great day!</p>
                         <!-- Footer Start -->
                   <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                       <div class='container'>
                           <div class='copyright'>
                               <div class='row'>
                                   <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                       &copy; <a class='border-bottom'>POWERMYNET</a>, All Right Reserved. 
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
                    // Redirect to login with verification message
                    $verify = "Your Account is Verified";
                    header("Location: ../../login/?verify=$verify");
                    exit(); // It's good practice to call exit() after header redirection
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
        } else {
            $errors['verified'] = "Account Verification failed";
        }
    } else {
        $errors['registration'] = "Account not found";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
		<!--Head-->
		<?php include '../../parts/head.php'; ?> 
</head>
<body class="">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
 		<!--Head-->
		<?php include '../../parts/header.php'; ?> 
  </nav>
  <!-- End Navbar -->
  <main class="main-content  mt-0">
    <section class="min-vh-100 mb-8">
      <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('../../../asset/images/banner.jpg');">
        <span class="mask bg-gradient-dark opacity-6"></span>
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5 text-center mx-auto">
              <h1 class="text-white mb-2 mt-5">Account Verification</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row mt-lg-n10 mt-md-n11 mt-n10">
          <div class="col-xl-6 col-lg-5 col-md-7 mx-auto">
            <div class="card z-index-0 p-4">
                <?php if(count($errors)>0): ?>
                <div class="alert alert-danger" role="alert" style="border-left:5px solid red; color:white">
                <?php foreach($errors as $error): ?>
                <div><?php echo $error; ?></div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?> 
            </div>
          </div>
        </div>
      </div>
    </section>
   
  </main>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
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
  <script src="../../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>