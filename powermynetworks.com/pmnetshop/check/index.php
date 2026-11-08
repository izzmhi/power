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
// Debug or enable error visibility
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database/dbconfig.php';
// Construct the current full URL from the address bar
   function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
                 || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host     = $_SERVER['HTTP_HOST'];   // Domain name
    $request  = $_SERVER['REQUEST_URI']; // Path and query string
    return $protocol . $host . $request;
}
// Example usage
$url = getFullUrl();
$errors = array();
if (isset($_POST['submit'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference']);
    $network = mysqli_real_escape_string($con, $_POST['network']);
    // Check for empty telephone
    if (empty($reference_id)) {
        $errors['reference_id'] = "Reference ID is required";
    } else {
        if($network=="MTN" || $network=="TELECEL" || $network=="AT"){
        $stmt = $con->prepare("SELECT * FROM orders_agent WHERE reference_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        header("Location: " . $_SESSION['url'] . "/success.php?reference=" . urlencode($reference_id) . "&network=" . urlencode($network));
            exit(); 
        } else {
            $errors['telephone'] = "Order not found!";
        }
        $stmt->close();
        }
        elseif($network=="AFA"){
        $stmt = $con->prepare("SELECT * FROM afa_registration WHERE reference_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header("Location: " . $_SESSION['url'] . "/success.php?reference=" . urlencode($reference_id) . "&network=" . urlencode($network));
            exit(); 
        } else {
            $errors['telephone'] = "Order not found!";
        }
        $stmt->close();
        }
        elseif($network=="BIG TIME"){
        $stmt = $con->prepare("SELECT * FROM special_offers_orders WHERE reference_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        header("Location: " . $_SESSION['url'] . "/success.php?reference=" . urlencode($reference_id) . "&network=" . urlencode($network));
            exit(); 
        } else {
            $errors['telephone'] = "Order not found!";
        }
        $stmt->close();
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="language" content="en">
    <title><?php echo $_SESSION['shop_name']; ?></title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="76x76" href="/my/assets/img/Power.png">
    <link rel="icon" type="image/png" href="/my/assets/img/Power.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <!-- Nucleo Icons -->
    <link href="/my/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/my/assets/css/nucleo-svg.css" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    <!-- Soft UI Dashboard CSS -->
    <link id="pagestyle" href="/my/assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />

    <!-- Optional JS -->
    <script src="/my/assets/js/jquery-2.1.1.min.js"></script>
    <script src="/my/assets/js/bootstrap.min.js"></script>
    <script src="/my/assets/js/custom.js"></script>
</head>

<body class="">
  <div class="container position-sticky z-index-sticky top-0">
  </div>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                <a href="<?php echo $_SESSION['url'] ?>" class="btn bg-gradient-info mt-4 mb-0">Back</a>
                  <h3 class="font-weight-bolder text-info text-gradient">Check Order Status</h3>
                  <h6 class="font-weight-bolder text-info text-gradient">AT Orders are Instant</h6>
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
                    <label>Network/Offer</label>
                    <div class="mb-3">
                      <select class="form-control" name="network" required>
                          <option value="">Select Network/Package</option>
                          <option value="AT">AT</option>
                          <option value="MTN">MTN</option>
                          <option value="TELECEL">TELECEL</option>
                          <option value="BIG TIME">AT/MTN BIG TIME</option>
                          <option value="AFA">MTN AFA</option>
                      </select>
                    </div>
                    <label>Reference ID</label>
                    <div class="mb-3">
                      <input type="text" class="form-control" placeholder="Reference ID" aria-label="Email" required name="reference">
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
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('/my/assets/img/curved-images/curved6.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
  <script src="/my/assets/js/core/popper.min.js"></script>
  <script src="/my/assets/js/core/bootstrap.min.js"></script>
  <script src="/my/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="/my/assets/js/plugins/smooth-scrollbar.min.js"></script>
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
  <script src="/my/assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>