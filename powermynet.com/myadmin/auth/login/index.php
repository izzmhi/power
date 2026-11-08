<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database/dbconfig.php';
$errors = array();
if (isset($_POST['submit'])) {
    // Fetch and sanitize user input
    $password = $_POST['password'];  // Password should not be escaped as it's hashed
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $keepLoggedIn = isset($_POST['keep']) ? true : false;
    // Validate input
    $errors = [];
    if (empty($email)) {
        $error = "Email is required";
           header("Location: index.php?error=$error");
    }
    if (empty($password)) {
        $error = "Password is required";
           header("Location: index.php?error=$error");
    }
    if (empty($errors)) {
        // Check if the user exists
        $stmt = $con->prepare("SELECT * FROM admin WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $row['password'])) {
                $verified = $row['verified'];
                $user_id = $row['user_id'];
                if ($verified === "false") {
                    $error = "Your account is not verified. Check your email to verify your account";
                       header("Location: index.php?error=$error");
                } else{
                    // Start session and regenerate session ID
                    $_SESSION['user_id'] = $user_id;
                    session_regenerate_id(true);
                    // Remember user if 'Keep me logged in' is checked
                    if ($keepLoggedIn) {
                        setcookie('user_id', $user_id, time() + (86400 * 30), "/", "", true, true); // Secure cookie
                    }
                        $_SESSION['user_id'] = $user_id;
                        if(!$_SESSION['user_id']){
                    $error = "User not sessioned!";
                   header("Location: index.php?error=$error");
                        }
                        else{
                            header('Location: ../../');
                        exit();
                    $stmt_check->close(); 
                        }

                }
            }
            else {
                $error = "Incorrect password!";
                   header("Location: index.php?error=$error");
            }
        } else {
            $error = "Account not found!";
               header("Location: index.php?error=$error");
        }
        $stmt->close();
    }
    
        // Clear password data from memory
    unset($_POST['password']);
    unset($password);
}

// Auto-login using cookie if session is not set
if (isset($_COOKIE['user_id']) && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    header('Location: ../../');
    exit();
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
              <h1 class="text-white mb-2 mt-5">Administrator Login</h1>
            
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
            
            <?php
            if(isset($_GET['success'])){
                ?>
                 <div class="alert alert-success" role="alert" style="border-left:5px solid green; color:white">
                     Verification is successful. Log in your account
                </div>
                <?php
            }
            else{
                
            }
            ?>
                <?php if(isset($_GET['error']))
                {
                ?>
                <div class="alert alert-danger" role="alert" style="border-left:5px solid red; color:white">
                <div><?php echo $_GET['error']; ?></div>
                </div>
                <?php 
                }
                ?> 
                
              <div class="card-body">
               <form action="" method="POST" enctype="multipart/form-data">
                    <label>Email</label>
                    <div class="mb-3">
                      <input type="email" class="form-control" name="email" placeholder="Email" aria-label="Email" aria-describedby="email-addon">
                    </div>
                    <label>Password</label>
                    <div class="mb-3">
                      <input type="password" class="form-control" name="password" placeholder="Password" aria-label="Password" aria-describedby="password-addon">
                    </div>
                      <div class="mb-3">
                   <input type="checkbox" id="keepLoggedIn" name="keep" value="1">
                   <label for="keepLoggedIn">Keep me Logged in</label>
               </div>

                    <div class="text-center">
                        <div class="spinner" id="spinner_new" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
                      <button type="submit" name="submit" class="btn bg-gradient-info w-100 mt-4 mb-0" id="submitBtn">Sign in</button>
                    </div>
                    <a href="../forgotten">Forgotten Password?</a>
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
  
  <!--Modal-->
  
</body>

</html>