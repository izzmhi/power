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
if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    // Validate user input
     if (empty($email)) {
        $error = "Email is required";
        header("Location: index.php?error=$error");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email";
        header("Location: index.php?error=$error");
    } 
    if (empty($errors)) {
        // Check if account already exists
        $selectquery = "SELECT * FROM admin WHERE email = '$email' ";
        $select = mysqli_query($con, $selectquery);
        if ($select) {
            // Send verification email
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
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Password Reset | Verification";
                    $mail->Body = "
                    <html>
                <body>
                   <div class='container-xxl bg-white p-0'>
                   <div class='container-xxl position-relative p-0'>
                     <br>
                 <p>Dear Partner,</p>
                  <p>Account Recovery</p>
                  <p> Click or copy the link to verify your account.</p>
                  <p> Verification link. http://myadmin.powermynetworks.com/auth/forgotten/verify?email=$email</p>
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
                       $success['success'] = "Account found. Check your email to verify your account";  
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
            else {
                $error = "Account not found";
                header("Location: index.php?error=$error");
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
              <h1 class="text-white mb-2 mt-5">Administrator Account Recovery</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row mt-lg-n10 mt-md-n11 mt-n10">
          <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
            <div class="card z-index-0">
              <div class="card-header text-center pt-4">
                <h5>Enter your Email</h5>
              </div>
              <?php if(isset($_GET['success']))
              {
              ?>
              <div class="alert alert-success" role="alert" style="border-left:5px solid green; color:white">
              <div><?php echo $_GET['success']; ?></div>
              </div>
              <?php 
              }
              ?> 
              
              <?php if(isset($_GET['error']))
              {
              ?>
              <div class="alert alert-danger" role="alert" style="border-left:5px solid red; color:white">
              <div><?php echo $_GET['error'] ?></div>
              </div>
              <?php
              }
              ?> 
                    
              <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
 
                  <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" aria-label="Email" aria-describedby="email-addon">
                  </div>
                   
                  <div class="text-center">
                      <div class="spinner" id="spinner_new" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
                    <button type="submit" name="submit" class="btn bg-gradient-dark w-100 my-4 mb-2" id="submitBtn">Send</button>
                  </div>
                  <p class="text-sm mt-3 mb-0">Already have an account? <a href="../login" class="text-dark font-weight-bolder">Sign in</a></p>
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