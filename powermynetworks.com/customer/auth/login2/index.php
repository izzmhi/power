<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'database/dbconfig.php';

$errors = array();

if (isset($_POST['submit'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    // Check for empty telephone
    if (empty($reference_id)) {
        $errors['telephone'] = "Order ID is required";
    } else {
        $stmt = $con->prepare("SELECT * FROM orders WHERE reference_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header("Location: ../../success1.php?reference_id=$reference_id");
            exit(); 
        } else {
            $errors['reference_id'] = "Account not found!";
        }
        $stmt->close();
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="language" content="en">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/code.png">
  <link rel="icon" type="image/png" href="../../assets/img/code.png">
  <title>CodeCraftNetwork | Affordable Data Packages in Ghana | MTN, Telecel & AirtelTigo</title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />
  <!-- Nepcha Analytics (nepcha.com) -->
  <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
  <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
</head>

<body class="">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg blur blur-rounded top-0 z-index-3 shadow position-absolute my-3 py-2 start-0 end-0 mx-4">
           <div class="container">
      <a style="font-size:size:30px;" class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="../../index.php"><img src="../../../assets/img/code.png"   style="width:50px;" > CODECRAFTNETWORK </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon mt-2"> <span class="navbar-toggler-bar bar1"></span>  <span class="navbar-toggler-bar bar2"></span> <span class="navbar-toggler-bar bar3"></span> </span> </button>
          </div>
        </nav>
        <!-- End Navbar -->
      </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                  <h3 class="font-weight-bolder text-info text-gradient">Welcome back</h3>
                </div>
                	<?php if(count($errors)>0): ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                        <?php foreach($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?> 
                <div class="card-body">
                  <form role="form" action="" method="POST" enctype="multipart/form-data">
                    <label>Order ID</label>
                    <div class="mb-3">
                      <input type="text" class="form-control" placeholder="Order ID" aria-label="Email" aria-describedby="email-addon" name="reference_id">
                    </div>
                    <div class="text-center">
                      <button type="submit" name="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Sign in</button>
                    </div>
                  </form>
                </div>

              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('assets/img/curved-images/curved6.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <footer class="footer py-5">
   <div class="container">
        <div class="row">
    </div>
        <div class="row"> <div class="col-8 mx-auto text-center mt-1"> <p class="mb-0 text-secondary">  Copyright © <script> document.write(new Date().getFullYear())  </script> CODECRAFTNETWORK </p> </div>      </div>
    </div>
  </footer>
  <!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <!--   Core JS Files   -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
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
  <script src="assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>