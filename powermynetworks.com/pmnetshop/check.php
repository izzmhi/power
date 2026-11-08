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
// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../database/dbconfig.php';
require 'config.php';
function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
                || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
$url = getFullUrl();
$shopHome = dirname($url);
if (!isset($_SESSION['shop'])) {
    // Redirect to homepage if shop is invalid
    header("Location: $shopHome");
    exit();
}
$errors = [];
// Handle Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reference_id = trim($_POST['reference'] ?? "");
    $network      = trim($_POST['network'] ?? "");

    if (empty($reference_id)) {
        $errors['reference'] = "Reference ID is required";
    }

    if (empty($network)) {
        $errors['network'] = "Please select network";
    }

    if (empty($errors)) {

        // Map network to table
        switch ($network) {
            case "MTN":
            case "AT":
            case "TELECEL":
                $table = "orders_agent";
                break;

            case "AFA":
                $table = "afa_registration";
                break;

            case "BIG TIME":
                $table = "special_offers_orders";
                break;

            default:
                $table = "";
        }

        if (!empty($table)) {

            $stmt = $con->prepare("SELECT * FROM {$table} WHERE reference_id = ?");
            $stmt->bind_param("s", $reference_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                // Fetch row properly
                $row = $result->fetch_assoc();
                $medium = $row['medium'] ?? "";

                // Redirect to success page
                header("Location: /" . urlencode($_SESSION['shop']) . 
                    "/success.php?reference=" . urlencode($reference_id) .
                    "&network=" . urlencode($network) .
                    "&medium=" . urlencode($medium)
                );
                exit;

            } else {
                $errors['reference'] = "Order not found!";
            }

            $stmt->close();
        }
    }
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
                <p class="masthead-subheading font-weight-light mb-0">Check Order Status</p>
                <p class="masthead-subheading font-weight-light mb-0">AT Orders are Instant</p>
                  <h5 class="masthead-subheading font-weight-light mb-0">Support  <strong>number-<?php echo $_SESSION['agent_telephone']; ?></strong></h5>
            </div>
        </header>
        
        <!-- Contact Section-->
        <section class="page-section" id="contact">
            <div class="container" style="margin-top:-80px;">
                    <?php
                        if(isset($_GET['error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                        <div><?php echo $_GET['error']; ?></div>
                        </div>
                        <?php 
                        }
                        ?> 
                <!-- Contact Section Heading-->
                <p class="text-center text-uppercase text-secondary">Status Check</p>
                <p class="bg-danger text-white p-2">Take Note: The reference ID is in the Email sent to you by Paystack after payment. It is in as Reference or Transaction ID</p>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <form id="contactForm" action="" method="POST" enctype="multipart/form-data">
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

                        
                            <!-- Phone number input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="text" name="reference" required />
                                <label for="phone">Reference ID (In the email received from Paystack)</label>
                            </div>
                            <br>
                            <!-- Submit Button-->
                            <button class="btn btn-primary btn-xl" id="submitButton" type="submit" >Check</button>
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
