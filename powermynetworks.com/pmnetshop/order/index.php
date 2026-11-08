<?php
session_start();
// Construct the current full URL from the address bar
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host   = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI']; // includes path and query string
$url = $scheme . "://" . $host . $request_uri;
// Parse the URL
$parts = parse_url($url);
// Build the base URL (remove file name and query string)
$scheme = isset($parts['scheme']) ? $parts['scheme'] : 'https';
$host   = isset($parts['host']) ? $parts['host'] : '';
$path   = isset($parts['path']) ? dirname($parts['path']) : '';
$base = rtrim($scheme . '://' . $host . $path, '/');
// Redirect to base URL if user not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base);
    exit();
}
// Debug or enable error visibility
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="language" content="en">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/images/code.png">
  <link rel="icon" type="image/png" href="../assets/images/code.png">
  <title><?php echo $_SESSION['shop_name']; ?></title>
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
  </div>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
        
          <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                    
                <a href="<?php echo $_SESSION['url'] ?>" class="btn bg-gradient-info mt-4 mb-0">Back</a>
                  <h3 class="font-weight-bolder text-info text-gradient">Re-Order</h3>
                  <h6 class="font-weight-bolder text-info text-gradient">Order again after payment</h6>
                  <p>Have you made payment and order didn't go through?</p>
                  <p>Follow the steps to order again without paying again. Previous paystack reference is required</p>
                </div>
                <div class="card-body">
                        <?php
                        if(isset($_GET['error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                        <div><?php echo $_GET['error']; ?></div>
                        </div>
                        <?php 
                        }
                        ?> 
                    
                   <form role="form" action="process.php" method="POST" enctype="multipart/form-data">
                    <label>Reference ID</label>
                    <div class="mb-3">
                      <input type="text" class="form-control" placeholder="Reference ID" aria-label="Email" required name="reference">
                    </div>
                    <label>Email used to make payment</label>
                    <div class="mb-3">
                      <input type="email" class="form-control" placeholder="Email" aria-label="Email" required name="email">
                    </div>
                    <label>Network/Offer</label>
                    <div class="mb-3">
                      <select class="form-control" name="network" required>
                          <option value="">Select Network/Package</option>
                          <option value="MTN">MTN</option>
                          <option value="AT">AT</option>
                          <option value="TELECEL">TELECEL</option>
                          <option value="MTN BIG">MTN BIG TIME</option>
                          <option value="AT BIG">AT BIG TIME</option>
                          <option value="AFA">MTN AFA</option>
                      </select>
                    </div>
                    <div class="text-center">
                      <button type="submit" name="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Check</button>
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