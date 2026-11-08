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
include '../database/dbconfig.php';
require 'config.php';
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
$shopHome = dirname($url);
if (!isset($_SESSION['shop'])) {
    // Redirect to homepage if shop is invalid
    header("Location: $shopHome");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
  	<?php include 'parts/head.php'; ?> 
</head>

    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
	<?php include 'parts/header.php'; ?> 
        </nav>
        <!-- Masthead-->
        <header class="masthead bg-primary text-white text-center">
            <div class="container d-flex align-items-center flex-column">
                <!-- Masthead Avatar Image-->
                <!-- Masthead Heading-->
                <p class="masthead-subheading font-weight-light mb-0">Order again after payment</p>
                <p class="masthead-subheading font-weight-light mb-0">Follow the steps to order again without paying again. Previous paystack reference is required</p>
            </div>
        </header>
        
        <!-- Contact Section-->
        <section class="page-section" id="contact">
            <div class="container">
                <!-- Contact Section Heading-->
                <p class="text-center text-uppercase text-secondary">Re-Order</p>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                        <?php
                        if(isset($_GET['error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                        <div><?php echo $_GET['error']; ?></div>
                        </div>
                        <?php 
                        }
                        ?> 
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <form id="contactForm" action="/my/<?php echo $_SESSION['shop']; ?>/process.php" method="POST" enctype="multipart/form-data">
                            <!-- Name input-->
<div class="form-floating mb-3">
    <select class="form-control form-select bg-light border-0 shadow-sm rounded-4 px-3 py-3" 
            name="network" required>
        <option value="">Select Network/Package</option>
        <option value="AT">AT</option>
        <option value="MTN">MTN</option>
        <option value="TELECEL">TELECEL</option>
        <option value="BIG TIME">AT/MTN BIG TIME</option>
        <option value="AFA">MTN AFA</option>
    </select>
    <label for="name">Network</label>
</div>

                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" name="reference" required />
                                <label for="name">Reference ID</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" name="email" required />
                                <label for="email">Email address</label>
                            <!-- Submit Button-->
                            <br>
                            <button class="btn btn-primary btn-xl" id="submitButton" type="submit" >Re-Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer-->
   <?php include 'parts/footer.php'; ?> 

          
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="assets/js/scripts.js"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
