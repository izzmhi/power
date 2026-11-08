<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['email'])) {
    header('Location: ../../../');
    exit();
}

include '../../../../database/dbconfig.php';
$email = mysqli_real_escape_string($con, $_GET['email']);
    $verified = "True";
$errors  = array();
// Prepare the SQL statement
$selectquery = "SELECT * FROM account_agents WHERE email = ?";
$stmt = $con->prepare($selectquery);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    
            // Update the account as verified
        $updatequery = "UPDATE account_agents SET verified='$verified' WHERE email='$email' LIMIT 1";
        $uquery = mysqli_query($con, $updatequery);
        if ($uquery) {
    header('Location: ../password/?query=' . urlencode($email));
    exit();
        } else {
    $errors['registration'] = "Failed to verify account";
}
} else {
    $errors['registration'] = "Failed to verify account";
}
// Optionally handle errors (e.g., log them, display them to the user)
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}
// Close the statement and connection
$stmt->close();
$con->close();
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