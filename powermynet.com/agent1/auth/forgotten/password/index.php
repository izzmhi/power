<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../../PHPMailer/Exception.php';
require '../../../../PHPMailer/PHPMailer.php';
require '../../../../PHPMailer/SMTP.php';
include '../../../../database/dbconfig.php';

$errors  = array();
if(isset($_POST['submit'])){
    // Fetch and sanitize user input
    $email = mysqli_real_escape_string($con, $_GET['query']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    // Validate user input
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['emailvalid'] = "Enter a valid email";
    } else if (empty($password)) {
        $errors['password'] = "Password is required";
    } else if (empty($confirm_password)) {
        $errors['confirm_password'] = "Confirm Password is required";
    } else if (!preg_match('/[A-Z]/', $password)) {
        $errors['passwordrepeat'] = "Password must include capital letters";
    } else if (!preg_match('/[a-z]/', $password)) {
        $errors['passwordrepeat'] = "Password must include small letters";
    } else if (!preg_match('/[0-9]/', $password)) {
        $errors['passwordrepeat'] = "Password must include numbers";
    } else if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['passwordrepeat'] = "Password must include symbols and other characters";
    } else if (strlen($password) < 8) {
        $errors['passwordrepeat'] = "Password must be at least 8 characters";
    } else if ($password !== $confirm_password) {
        $errors['password'] = "Passwords do not match";
    }

    if (empty($errors)) {

        // Password hashing
        $hashed_password = password_hash($confirm_password, PASSWORD_DEFAULT);

        // Check if account already exists
        $selectquery = "SELECT * FROM account_agents WHERE email = '$email' ";
        $select = mysqli_query($con, $selectquery);
        if ($select) {
            
            $updatequery = "update account_agents SET password='$hashed_password' WHERE email = '$email' LIMIT 1";
            $iquery = mysqli_query($con,$updatequery);
            if($iquery){
                
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
                 <p>Dear Agent,</p>
                <p> Your password is changed successfully.</p>
                <p> Click or copy the link to log into your account.</p>
                <p>http://agent.powermynetworks.com/auth/login/?email=$email</p>
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
                          header("location:../../login/");
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

         }
    else{
           $errors['verified'] = "Failed to create new Password"; 
        }
        }
        
        else{
             $errors['registration'] = "Account not Found";
        }
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
              <h1 class="text-white mb-2 mt-5">Agent Password Recovery</h1>
            </div>
          </div>
        </div>
      </div>
      
      <div class="container">
        <div class="row mt-lg-n10 mt-md-n11 mt-n10">
          <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
            <div class="card z-index-0">
              <div class="card-header text-center pt-4">
                <h5>Create New Password</h5>
              </div>

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
                    <input type="password" class="form-control" placeholder="Password" name="password" aria-label="Password" aria-describedby="password-addon">
                  </div>
                   <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Re-enter Password" name="confirm_password" aria-label="Password" aria-describedby="password-addon">
                  </div>

<div class="text-center">
  <button type="submit" name="submit" class="btn bg-gradient-info w-100 mt-4 mb-0 d-flex justify-content-center align-items-center" id="submitBtn" style="gap: 8px;">
    <span class="spinner-border spinner-border-sm" id="spinner_new" style="display: none;" role="status" aria-hidden="true"></span>
    <span id="btnText">Sign in</span>
  </button>
</div>
                  <p class="text-sm mt-3 mb-0">Already have an account? <a href="../../login" class="text-dark font-weight-bolder">Set</a></p>
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
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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