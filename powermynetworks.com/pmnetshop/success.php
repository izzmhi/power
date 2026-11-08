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

// Redirect to base URL if user not logged in
if (!isset($_GET['reference']) || !isset($_SESSION['user_id'])) {
    header('Location: ' . $url);
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
        <header class="masthead bg-primary text-white text-center" style="height:70px;">
            <div class="container d-flex align-items-center flex-column" style="margin-top:-50px;">
                <!-- Masthead Avatar Image-->
                <!-- Masthead Heading-->
                <p class="masthead-subheading font-weight-light mb-0">ORDER STATUS</p>
                                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
            </div>
        </header>

        <!-- Contact Section-->
        <section class="page-section" id="contact">
            <div class="container" style="margin-top:-80px;">
                <?php
                    if(isset($_GET['success'])){
                        ?>
                         <p class="text-center text-uppercase text-secondary text-success"><?php echo $_GET['success'] ?></p>
                         <p class="text-center text-uppercase text-secondary text-success">Reference ID: <?php echo $_GET['reference'] ?></p>
                         <p class="bg-danger text-white p-2">Take Note: The reference ID is also in the Email sent to you by Paystack. It is in as Reference or Transaction ID</p>
                         <p class="bg-danger text-white p-2">Take Note: You will use the reference ID to Check Order Status or to Order Again when there is an error after payment</p>
                        <?php
                    }
                        else if(isset($_GET['error'])){
                        ?>
                          <p class="text-center text-uppercase text-secondary text-danger"><?php echo $_GET['error']; ?></p>
                          <p class="text-center text-uppercase text-secondary text-danger">Reference ID: <?php echo $_GET['reference'] ?></p>
                           <p class="bg-danger text-white p-2">Take Note: The reference ID is also in the Email sent to you by Paystack. It is in as Reference or Transaction ID</p>
                         <p class="bg-danger text-white p-2">Take Note: You will use the reference ID to Check Order Status or to Order Again when there is an error after payment</p>
                        <?php
                    }
                ?>
                  
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        
                        <?php
    if(isset($_GET['network'])){
    
    include '../database/dbconfig.php';
    $reference = $_GET['reference'];
    $medium = $_GET['medium'];
    if($_GET['network']=='MTN'){
        if($medium=="EXCEL"){
                      $select = "SELECT * FROM orders_agent WHERE reference_id='$reference' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                        <form id="contactForm">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['network'] ?>" readonly />
                                <label for="name">Network</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['gig'] ?> GB" readonly />
                                <label for="email">Package</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" value="<?php echo $row['beneficiary'] ?>" readonly />
                                <label for="phone">Recipient number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['order_date']. " - ". $row['order_time'] ?>" readonly />
                                <label for="name">Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['order_status'] ?>" readonly />
                                <label for="email">Order Status</label>
                            </div>
                        </form>
	
                 <?php
                }
              }
              else {
                   // Display error message if the email parameter is not provided
                   echo "<div>Order Reference id is required.</div>";
                  }  
        }
        else{
            
        }
        
    }
                
        elseif($_GET['network']=='AT'){
            $select = "SELECT * FROM orders_agent WHERE reference_id='$reference' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                                  <form id="contactForm">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['network'] ?>" readonly />
                                <label for="name">Network</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['gig'] ?> GB" readonly />
                                <label for="email">Package</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" value="<?php echo $row['beneficiary'] ?>" readonly />
                                <label for="phone">Recipient number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['order_date']. " - ". $row['order_time'] ?>" readonly />
                                <label for="name">Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['order_status'] ?>" readonly />
                                <label for="email">Order Status</label>
                            </div>
                        </form>
					
                 <?php
                }
              }
              else {
                   // Display error message if the email parameter is not provided
                   echo "<div>Order Reference id is required.</div>";
                  }
    }
                
        elseif($_GET['network']=='TELECEL'){
            $select = "SELECT * FROM orders_agent WHERE reference_id='$reference' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                                  <form id="contactForm">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['network'] ?>" readonly />
                                <label for="name">Network</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['gig'] ?> GB" readonly />
                                <label for="email">Package</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" value="<?php echo $row['beneficiary'] ?>" readonly />
                                <label for="phone">Recipient number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['order_date']. " - ". $row['order_time'] ?>" readonly />
                                <label for="name">Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['order_status'] ?>" readonly />
                                <label for="email">Order Status</label>
                            </div>
                        </form>
                 <?php
                }
              }
              else {
                   // Display error message if the email parameter is not provided
                   echo "<div>Order Reference id is required.</div>";
                  }
                }

        elseif($_GET['network']=='BIG TIME'){
            $select = "SELECT * FROM special_offers_orders WHERE reference_id='$reference' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                                  <form id="contactForm">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['network'] ?>" readonly />
                                <label for="name">Network</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['gig'] ?> GB" readonly />
                                <label for="email">Package</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" value="<?php echo $row['beneficiary'] ?>" readonly />
                                <label for="phone">Recipient number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $row['order_date']. " - ". $row['order_time'] ?>" readonly />
                                <label for="name">Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $row['order_status'] ?>" readonly />
                                <label for="email">Order Status</label>
                            </div>
                        </form>
					
                 <?php
                }
              }
              else {
                   // Display error message if the email parameter is not provided
                   echo "<div>Order Reference id is required.</div>";
                  }
                }
                
            elseif($_GET['network']=='AFA'){

            $select = "SELECT * FROM afa_registration WHERE reference_id='$reference' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
           <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-user text-secondary me-2"></i>
              <strong>Fullname</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['fullname'] ?>
            </div>
          </div>

          <!-- Package -->
          <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-phone text-secondary me-2"></i>
              <strong>Telephone</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['telephone'] ?>
            </div>
          </div>

          <!-- Price -->
          <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-id-card-o text-secondary me-2"></i>
              <strong>ID Number</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['id_number'] ?>
            </div>
          </div>

          <!-- Recipient Number -->
          <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-map-marker text-secondary me-2"></i>
              <strong>Location</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['location'] ?>
            </div>
          </div>
          
        <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-location-arrow text-secondary me-2"></i>
              <strong>Region</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['region'] ?>
            </div>
          </div>
          
        <div class="row mb-3 align-items-center">
            <div class="col-6">
              <i class="fas fa-box text-secondary me-2"></i>
              <strong>Date of Birth</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['dob'] ?>
            </div>
          </div>

          <!-- Email -->
          <div class="row mb-4 align-items-center">
            <div class="col-6">
              <i class="fas fa-envelope text-secondary me-2"></i>
              <strong>Email</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['email'] ?>
            </div>
          </div>
        <div class="row mb-4 align-items-center">
            <div class="col-6">
              <i class="fa fa-calendar-o" aria-hidden="true"></i>
              <strong>Date</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['register_date']. " - ". $row['register_time'] ?>
            </div>
          </div> 
          
					<hr>
		  <div class="row mb-4 align-items-center">
            <div class="col-6">
              <i class="fa fa-star" aria-hidden="true"></i>
              <strong>Order Status</strong>
            </div>
            <div class="col-6 text-end">
              <?php echo $row['register_status'] ?>
            </div>
          </div>
                 <?php
                }
              }
              else {
                   // Display error message if the email parameter is not provided
                   echo "<div>Order Reference id is required.</div>";
                  }
                }
                }else{
                    
                }
                ?>

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
