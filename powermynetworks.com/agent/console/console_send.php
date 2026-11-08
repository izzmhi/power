<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
       $_SESSION['wallet'] = $rows['wallet'];
    }
}
		$first_name = $_SESSION['first_name'];
		$last_name = $_SESSION['last_name'];
		$email = $_SESSION['email'];
		$telephone = $_SESSION['telephone'];
		
$errors = array();
$success = array();

if (isset($_POST['send'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $errors = []; // Initialize the errors array

    // Initialize bulk data session array
    if (!isset($_SESSION['bulk_data'])) {
        $_SESSION['bulk_data'] = [];
    }

    // Validate and store bulk data from the form
    foreach ($_POST['gig'] as $index => $package) {
        $recipient = $_POST['recipient'][$index];
        $price = 0.00; // Placeholder for individual package price if needed
        $network = "AT";
        $console_id = "12647834";
        $console_name = "CONSOLE " . $last_name;

        if (empty($package)) {
            $errors["gig$index"] = "Gig " . ($index + 1) . " is required";
        }
        if (empty($recipient)) {
            $errors["recipient$index"] = "Recipient number " . ($index + 1) . " is required";
        }

        // If no errors for this package-recipient pair, add it to bulk data
        if (empty($errors["gig$index"]) && empty($errors["recipient$index"])) {
            $_SESSION['bulk_data'][] = [
                'package' => mysqli_real_escape_string($con, $package),
                'recipient' => mysqli_real_escape_string($con, $recipient),
                'price' => $price,
                'network' => $network,
                'console_id' => $console_id,
                'console_name' => $console_name
            ];
        }
    }

    // Proceed only if no errors
    if (empty($errors)) {
        foreach ($_SESSION['bulk_data'] as $data) {
            $package = $data['package'];
            $recipient = $data['recipient'];
            $price = $data['price'];
            $network = $data['network'];
            $console_id = $data['console_id'];
            $console_name = $data['console_name'];

            // Check wallet balance
            $select = "SELECT wallet, credit FROM agents WHERE user_id='$agent_id'";
            $sql = mysqli_query($con, $select);
            $row = mysqli_fetch_assoc($sql);

            if ($row['wallet'] < $price) {
                $errors["purchase"] = 'Purchase failed. Insufficient wallet balance.';
                break;
            }

            // Generate a unique reference ID
            $last_2_chars = substr($recipient, -2);
            $first_five_chars = substr($recipient, 0, 5);
            $seconds = date('His');
            $reference_id = $first_five_chars . $seconds . $last_2_chars;

            // API endpoint URL
            $url = 'https://www.value4moni.com/api/v1/inititate_transaction';
            $data = [
                "API_Key" => "REDACTED",
                "Receiver" => $recipient,
                "Volume" => $package,
                "Reference" => $reference_id,
                "Package_Type" => $network
            ];

            // Initialize cURL session
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            // Handle the response
            if ($response === false) {
                $errors["api"] = 'API call failed: ' . curl_error($ch);
                break;
            } else {
                $walletf = $row['wallet'] - $price;
                $creditf = $row['credit'] - $price;
                $order_id = uniqid('order_', true); // Generate a unique order ID
                $order_time = date('H:i:s');
                $order_date = date('Y-m-d');
                $real_date = date('Y-m-d H:i:s');
                $order_status = 'Completed';
                $purpose = "Data Purchase";

                // Insert order into database
                $stmt = $con->prepare("INSERT INTO console_orders (order_id, console_id, console_name, network, agent_name, agent_email, agent_tel, recipient, package, price, order_time, order_date, real_date, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssssdssss", $order_id, $console_id, $console_name, $network, $agent_name, $agent_email, $agent_tel, $recipient, $package, $price, $order_time, $order_date, $real_date, $order_status);

                if ($stmt->execute()) {
                    // Update agent's wallet and credit
                    $update_stmt = $con->prepare("UPDATE consoles SET wallet = ?, credit = ? WHERE user_id = ?");
                    $update_stmt->bind_param("dds", $walletf, $creditf, $agent_id);
                    $update_stmt->execute();

                    // Log transaction
                    $trans_stmt = $con->prepare("INSERT INTO console_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $trans_stmt->bind_param("sssssdddss", $reference_id, $order_id, $agent_id, $agent_name, $agent_email, $price, $walletf, $creditf, $real_date, $purpose);
                    $trans_stmt->execute();

                    // Send confirmation emails
                    $agent_message = "You have purchased $network $package worth GHS$price.00 for $recipient. Your new wallet balance is GHS$walletf.00.";
                    sendEmail($agent_email, "Data Purchase Confirmation", $agent_message);
                    $admin_email = "codecraftnetwork1@gmail.com";
                    $admin_message = "$agent_name purchased $network $package for $recipient.";
                    sendEmail($admin_email, "Agent Data Purchase", $admin_message);

                    $success = "Order successfully placed.";
                    header("Location: index.php?success=" . urlencode($success) . "&reference_id=" . urlencode($reference_id));
                    exit();
                } else {
                    $errors["db"] = "Failed to insert order: " . $stmt->error;
                    break;
                }
            }
        }

        if (!empty($errors)) {
            header("Location: index.php?error=" . urlencode(json_encode($errors)));
            exit();
        }

        // Clear bulk data session after successful processing
        unset($_SESSION['bulk_data']);
    } else {
        header("Location: index.php?error=" . urlencode(json_encode($errors)));
        exit();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
  <?php include 'includes/head.php'; ?> 
<body>
  <!-- ======= Header ======= -->
 <?php include 'includes/header.php'; ?> 
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
   <?php include 'includes/sidebar.php'; ?> 
  <!-- End Sidebar-->
  <main id="main" class="main">
    <section class="section dashboard">
      <div class="row">
          
        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">
            <!-- Recent Sales -->
            <div class="col-12">
  				<div class="row gutters">
					<div class="col-lg-6 col-sm-12 col-12">
		<div class="card h-100">
    <div class="card-header">
        <h4>Confirmation</h4>
    </div>
    <div class="card-body">
        <?php foreach ($_GET['bulk'] as $data): ?>
            <div class="row mb-3">
                <div class="col-xl-12 col-md-12 col-12">
                    <div class="row">
                        <div>
                            <h4><?php echo htmlspecialchars($data['network']); ?></h4>
                        </div>   
                    </div>
                    <div class="row">
                        <div>
                            <span class="text-info">Recipient: <?php echo htmlspecialchars($data['recipient']); ?></span>
                        </div>   
                    </div>
                    <div class="row">
                        <div>
                            <span class="text-info">Package: <?php echo htmlspecialchars($data['package']); ?> GB</span>
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <span class="text-info">Price: GHS <?php echo htmlspecialchars($data['price']); ?></span>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="priceForm">
            <div class="row gutters">
                <div class="col-sm-12">
                    <div class="text-right">
                        <button type="submit" name="send" class="btn btn-block btn-primary">
                            Confirm & Send Data <i class="fa fa-phone-square" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
						</div>
					</div>
                  
                </div>

                </div>
            </div><!-- End Recent Sales -->

             </div>
          </div><!-- End Left side columns -->

       </div>
    </section>

  </main><!-- End #main -->
 <?php include 'includes/footer.php'; ?> 
  <!-- ======= Footer ======= -->
 
  <!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.min.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
</body>
</html>