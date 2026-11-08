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
// Remove the filename from the URL
$shopHome = dirname($url);
if (!isset($_SESSION['shop'])) {
    // Redirect to homepage if shop is invalid
    header("Location: $shopHome");
    exit();
}

// Access session data safely
$network     = $_SESSION['network'] ?? '';
$package     = $_SESSION['package'] ?? '';
$email       = $_SESSION['email'] ?? '';
$beneficiary = $_SESSION['beneficiary'] ?? '';
$network = "AT";
$agent_id = $_SESSION['user_id'];
$package = mysqli_real_escape_string($con, $_SESSION['package']);
$select = "SELECT amount 
           FROM agents_prices_packages 
           WHERE agent_id='$agent_id' 
             AND network='$network' 
             AND package='$package'
           LIMIT 1";
$sql = mysqli_query($con, $select);
if (mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);
    $price = $row['amount'];
    $_SESSION['price']=$row['amount'];
} else {
    die("Price not found $package$network");
}
$amount = (int)($price * 100);
$_SESSION['amount']=$amount;

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
                <p class="masthead-subheading font-weight-light mb-0">Use Re-Order on the menu when payments go through but order fails</p>
                <p class="masthead-subheading font-weight-light mb-0">Wait for the payment processing to complete before closing it</p>
            </div>
        </header>
        
        <!-- Contact Section-->
        <section class="page-section" id="contact">
            <div class="container">
                <!-- Contact Section Heading-->
                <p class="text-center text-uppercase text-secondary">Order Confirmation</p>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">

                        <form id="contactForm">
                            <!-- Name input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $_SESSION['network'] ?>" readonly/>
                                <label for="name">Network</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="<?php echo $_SESSION['package'] ?> GB" readonly/>
                                <label for="name">Package</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" value="GHS <?php echo $_SESSION['price'] ?>" readonly/>
                                <label for="name">Price</label>
                            </div>
                            <!-- Phone number input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="text" value="<?php echo $_SESSION['beneficiary'] ?>" readonly />
                                <label for="phone">Recipient number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" value="<?php echo $_SESSION['email'] ?>" readonly />
                                <label for="email">Email address</label>
                            </div>

                            <script src="https://js.paystack.co/v1/inline.js"></script>
                            <div class="d-none" id="submitErrorMessage"><div class="text-center text-danger mb-3">Error sending message!</div></div>
                            <!-- Submit Button-->
                            <button class="btn btn-primary btn-xl" id="submitButton" type="button" onclick="payPageWithPaystack()">PAY</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer-->
 <?php include 'parts/footer.php'; ?> 
 
<script>
function payPageWithPaystack() {
    const shop = "<?php echo isset($_SESSION['shop']) ? $_SESSION['shop'] : ''; ?>";
    const email = "<?php echo $_SESSION['email']; ?>";
    const network = "<?php echo $_SESSION['network']; ?>";
    const packageName = "<?php echo $_SESSION['package']; ?>";
    const beneficiary = "<?php echo $_SESSION['beneficiary']; ?>";
    const amount = <?php echo $amount; ?>;
    const paystackKey = "<?php echo PAYSTACK_PUBLIC_KEY; ?>";

    if (!shop || !email) {
        // If session expired, redirect to homepage
        window.location.href = `/my/`;
        return;
    }

    var handler = PaystackPop.setup({
        key: paystackKey,
        email: email,
        amount: amount,
        currency: "GHS",
        metadata: {
            custom_fields: [
                { display_name: "Email", variable_name: "email", value: email },
                { display_name: "Beneficiary", variable_name: "beneficiary", value: beneficiary },
                { display_name: "Network", variable_name: "network", value: network },
                { display_name: "Bundle", variable_name: "bundle", value: packageName },
                { display_name: "Amount", variable_name: "amount", value: amount }
            ]
        },
        callback: function(response) {
            const referenceEncoded = encodeURIComponent(response.reference);
            window.location.href = `/my/${shop}/payments_at.php?paystackpaid=${referenceEncoded}`;
        },
        onClose: function() {
            alert('Payment popup closed');
        }
    });

    handler.openIframe();
}
</script>
          
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="assets/js/scripts.js"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
