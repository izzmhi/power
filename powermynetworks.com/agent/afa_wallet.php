<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

include'../database/dbconfig.php';
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
       $_SESSION['agent_email'] = $rows['email'];
       $_SESSION['agent_telephone'] = $rows['telephone'];
       $_SESSION['wallet'] = $rows['wallet'];
    }
}


if (isset($_POST['submit'])) {
    // Sanitize session data
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['agent_email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['agent_telephone']);
    $fullnames = $_SESSION['fullnames'];
    $telephone = $_SESSION['telephone'];
    $email = $_SESSION['email'];
    $id_type = $_SESSION['id_type'];
    $id_number = $_SESSION['id_number'];
    $location = $_SESSION['location'];
    $dob = $_SESSION['dob'];
    $region = $_SESSION['region'];
    $occupation = $_SESSION['occupation'];
    $img_front = $_SESSION['img_front'];
    $img_back = $_SESSION['img_back'];
    $register_status = "Pending";
    $account_type = "Agent";
    
    date_default_timezone_set('GMT');
    $register_time = date('H:i:s A');
    $register_date = date('l, F d, Y');
    $real_date = date('Y-m-d');
    $purpose = "AFA";
    $mode = "Wallet";
    $origin = "SITE";
    
    // Validate inputs
    if (empty($fullnames)) {
        $error = "Fullname is required";
        header("Location: afa_wallet.php?afa_error=" . urlencode($error));
        exit();
    }
    if (empty($telephone)) {
        $error = "Telephone is required";
        header("Location: afa_wallet.php?afa_error=" . urlencode($error));
        exit();
    }

    // Generate reference ID
    $last_2_chars = substr($telephone, -2);
    $first_five_chars = substr($telephone, 0, 5);
    $seconds = date('His');
    $reference_id = $first_five_chars . $seconds . $last_2_chars;
    
        // === Fetch AFA price ===
    $selects = "SELECT amount, original FROM afa_prices WHERE target='Agent' LIMIT 1";
    $sqls = mysqli_query($con, $selects);

    if (mysqli_num_rows($sqls) > 0) {
        $rows = mysqli_fetch_array($sqls);
        $price     = floatval($rows['amount']);
        $original  = floatval($rows['original']);
    } else {
        header("Location: afa.php?afa_error=" . urlencode("Pricing not found"));
        exit();
    }

    // Check wallet balance
    $select = "SELECT wallet FROM agents WHERE user_id='$agent_id'";
    $sql = mysqli_query($con, $select);
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_array($sql);
        $wallet = $row['wallet'];

        // Assuming price is a variable that should be defined, e.g., $price = 100;
        if ($wallet < $price) {
            $error = 'Purchase failed. You do not have sufficient wallet balance';
            header("Location: afa_wallet.php?afa_error=" . urlencode($error));
            exit();
        }
        
        $wallet_balance = $wallet - $price;
        $profit = $price - $original;

    $selects = "SELECT afa FROM api_switch";
    $sqls = mysqli_query($con, $selects);
    $checks = mysqli_num_rows($sqls) > 0;
    if ($checks) {
        while ($rows = mysqli_fetch_array($sqls)) {
        if($rows['afa']=="API"){
            $status = "Pending";
            $medium = "API";  
            
$agent_api = "REDACTED";            
// API data preparation
$data = [
    'agent_api' => $agent_api,
    'fullname' => $fullnames,
    'telephone' => $telephone,
    'email' => $email,
    'id_type'=> $id_type,
    'id_number' => $id_number,
    'location' => $location,
    'dob' => $dob,
    'region' => $region,
    'reference_id' => $reference_id,
    'client_email' => $agent_email,
];
$jsonData = json_encode($data);
require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.codecraftnetwork.com/api/afa.php');
// cURL setup and execution
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
$response = curl_exec($ch);
// Get HTTP response code BEFORE closing cURL
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// Decode JSON response
$decoded = json_decode($response, true);
// Get message from response body (if available)
$response_message = $decoded['message'] ?? 'Unknown response';
if ($http_code == 200) {

        $insertquery = "INSERT INTO afa_registration (reference_id, agent_id, agent_name, agent_email, fullname, telephone, id_type, id_number, location, region, dob, register_time,register_date,real_date,register_status,email,account_type,price,agent_price,mode,profit) 
                        VALUES ('$reference_id', '$agent_id', '$agent_name', '$agent_email', '$fullnames', '$telephone', '$id_type', '$id_number', '$location', '$region', '$dob','$register_time','$register_date','$real_date','$register_status','$email','$account_type','$original','$price','$mode','$profit')";
        $iquery = mysqli_query($con, $insertquery);
        if ($iquery) {
            // Insert into `agents_transactions`
            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssddss", $reference_id, $agent_id, $agent_name, $agent_email, $price, $wallet_balance, $real_date, $purpose);
            $insert = $stmt->execute();
            if ($insert) {
                // Update wallet and credit
                $stmt = $con->prepare("UPDATE agents SET wallet = ? WHERE user_id = ?");
                $stmt->bind_param("ds", $wallet_balance, $agent_id);
                if ($stmt->execute()) {
                    // Send email
                    $mail = new PHPMailer(true);
                    try {
                    $mail->isSMTP();
                    $mail->Host = 'de102.aveshost.net';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@powermynetworks.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
                    $mail->addAddress($agent_email);
                        $mail->isHTML(true);
                        $mail->Subject = "AFA Registration";
                        $mail->Body = "
                        <html>
                        <body>
                            <p>Dear Admin,</p>
                            <p>$fullnames has registered for the AFA Bundle</p>
                            <p>Have a great day!</p>
                        </body>
                        </html>";
                        $mail->send();

                        $success = "AFA Registration Successful";
                        header("Location: afa.php?success=" . urlencode($success));
                        exit();
                    } catch (Exception $e) {
                        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        header("Location: afa_wallet.php?error=" . urlencode($error));
                        exit();
                    }
                } else {
                    $error = "Failed to update wallet: " . $stmt->error;
                    header("Location: afa_wallet.php?error=" . urlencode($error));
                    exit();
                }
            } else {
                $error = "Failed to insert into transactions: " . $stmt->error;
                header("Location: afa_wallet.php?error=" . urlencode($error));
                exit();
            }
        } else {
            $error = "Failed to register";
            header("Location: afa_wallet.php?afa_error=" . urlencode($error));
            exit();
        }

            } else {
        header("Location: afa_wallet.php?error=" . urlencode("[$http_code] $response_message"));
        exit();
        }
        }
        else{
// ==============================
// PREPARE VARIABLES
// ==============================
$status = "Pending";
$medium = "EXCEL";

$img_front = $_SESSION['img_front'] ?? '';
$img_back  = $_SESSION['img_back'] ?? '';

// ==============================
// START TRANSACTION
// ==============================
$con->begin_transaction();

try {

    // ==============================
    // INSERT INTO afa_registration
    // ==============================
    $stmt = $con->prepare("INSERT INTO afa_registration 
    (reference_id, agent_id, agent_name, agent_email, fullname, telephone, id_number, location, region, dob, occupation,register_time, register_date, real_date, register_status, 
    account_type, price, original, profit, mode, medium, origin, img_front, img_back)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    } 

    $stmt->bind_param(
        "ssssssssssssssssdsssssss",
        $reference_id,
        $agent_id,
        $agent_name,
        $agent_email,
        $fullnames,
        $telephone,
        $id_number,
        $location,
        $region,
        $dob,
        $occupation,
        $register_time,
        $register_date,
        $real_date,
        $status,
        $account_type,
        $price,
        $original,
        $profit,
        $mode,
        $medium,
        $origin,
        $img_front,
        $img_back
    );

    if (!$stmt->execute()) {
        throw new Exception("Insert afa_registration failed: " . $stmt->error);
    }

    $stmt->close();

    // ==============================
    // INSERT INTO TRANSACTIONS
    // ==============================
    $stmt = $con->prepare("INSERT INTO agents_transactions 
    (reference_id, user_id, agent_name, email, amount, wallet, trans_date, purpose)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Prepare transaction failed");
    }

    $stmt->bind_param(
        "ssssddss",
        $reference_id,
        $agent_id,
        $agent_name,
        $agent_email,
        $price,
        $wallet_balance,
        $real_date,
        $purpose
    );

    if (!$stmt->execute()) {
        throw new Exception("Insert transaction failed");
    }

    $stmt->close();

    // ==============================
    // UPDATE WALLET
    // ==============================
    $stmt = $con->prepare("UPDATE agents SET wallet=? WHERE user_id=?");

    if (!$stmt) {
        throw new Exception("Prepare wallet failed");
    }

    $stmt->bind_param("ds", $wallet_balance, $agent_id);

    if (!$stmt->execute()) {
        throw new Exception("Wallet update failed");
    }

    $stmt->close();

    // ==============================
    // COMMIT TRANSACTION
    // ==============================
    $con->commit();

    // ==============================
    // SEND EMAIL
    // ==============================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'de102.aveshost.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@powermynetworks.com';
        $mail->Password = 'REDACTED';
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;

        $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
        $mail->addAddress("azumahnbalino1@gmail.com");

        $mail->isHTML(true);
        $mail->Subject = "AFA Registration";
        $mail->Body = "
        <p>Dear Admin,</p>
        <p>$fullnames has registered for the AFA Bundle</p>";

        $mail->send();

    } catch (Exception $e) {
        // Don't break flow if email fails
    }

    header("Location: afa.php?success=" . urlencode("AFA Registration Successful"));
    exit();

} catch (Exception $e) {

    // 🔥 ROLLBACK EVERYTHING
    $con->rollback();

    header("Location: afa_wallet.php?error=" . urlencode($e->getMessage()));
    exit();
}
        }
        }
    }

    } else {
        $error = "Agent Account not found";
        header("Location: afa_wallet.php?afa_error=" . urlencode($error));
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
	<head>
		<!--Head-->
		<?php include 'parts/head.php'; ?> 
	</head>
	 <body>
		<!-- Page wrapper start -->
		<div class="page-wrapper">
			<!-- Sidebar wrapper start -->
			<nav id="sidebar" class="sidebar-wrapper">
			<!-- side-->
			<?php $page = 'wallet' ?>
			<?php include 'parts/side.php'; ?> 
			</nav>
			<!-- Sidebar wrapper end -->
			<!-- Page content start  -->
			<div class="page-content">
				<!-- Header start -->
				<header class="header">
					<!-- header-->
					<?php include 'parts/header.php'; ?> 
				</header>
				<!-- Header end -->
				<!-- Page header start -->
				<div class="page-header">
						<!-- Nav-->
						<?php include 'parts/nav.php'; ?> 
				</div>
				<!-- Page header end -->
				<!-- Main container start -->
				<div class="main-container">
				    
				    <!-- Full Screen Modal for Mobile -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content rounded-3 shadow-lg">
      
      <!-- Modal Header -->
      <div class="modal-header bg-info text-white d-flex justify-content-between">
        <h5 class="modal-title" id="exampleModalLabel">
          <i class="fa fa-money mr-2"></i> Enter Amount
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <!-- Modal Body -->
      <form action="processing.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <div class="form-group">
            <label for="price" class="font-weight-bold">
              Amount <small class="text-muted">(Minimum GHS 10)</small>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-cedi-sign"></i></span>
              </div>
              <input type="number" class="form-control form-control-lg" id="price" name="price" min="10" step="1" placeholder="Enter amount" required>
            </div>
            <small class="form-text text-muted mt-2">Ensure the amount is not less than GHS 10.</small>
          </div>
        </div>
        
        <!-- Modal Footer -->
<div class="modal-footer border-0 p-3 d-flex flex-column gap-2">
    <button type="button" class="btn btn-danger border w-100 mb-2" data-dismiss="modal">
        <i class="fa fa-times"></i> Cancel
    </button>
    <button type="submit" name="submit_amount" class="btn btn-info w-100">
        <i class="fa fa-check"></i> Continue
    </button>
</div>

      </form>
    </div>
  </div>
</div>

<!-- Custom CSS for Fullscreen on Mobile -->
<style>
.modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
}

.modal-footer {
    position: sticky;
    bottom: 0;
    background: #fff;
    border-top: 1px solid #ddd;
    z-index: 100;
}
@media (max-width: 768px) {
  .modal-dialog {
    max-width: 100%;
    margin: 0;
    height: 100%;
  }
  .modal-content {
    height: 100%;
    border-radius: 0;
  }
  .modal-body {
    overflow-y: auto;
  }
}
</style>
				    
				    <?php 
						if(isset($_GET['error'])){
					?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                        <div><?php echo $_GET['error']; ?></div>
                        </div>
                    <?php } ?> 
                    
                    		<?php	
                    		 include '../database/dbconfig.php';
						$select = "SELECT * FROM afa_prices WHERE target='Agent'";
                        $sql = mysqli_query($con, $select);
                        if (mysqli_num_rows($sql) > 0) {
                            while ($row = mysqli_fetch_array($sql)) {
                             $price = $row['amount'];
						?>

<div class="row gutters">
    <div class="col-lg-6 col-sm-12 col-12 mx-auto">
        <div class="card h-100 shadow-lg border-0 profile-card">
            <!-- Header -->
             <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <?php
                include '../database/dbconfig.php';
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $select = "SELECT * FROM agents WHERE user_id='$user_id'";
                $sql = mysqli_query($con, $select);
                if ($sql && mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                <h4 class="text-white mb-0"><i class="fa fa-shopping-cart"></i> Order Placement</h4>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                        <?php
                    }
                }
                ?>
            </div>

            <!-- Body -->
            <div class="card-body pb-5 flex-grow-1">
                <?php
                $fields = [
                    "Fullname" => $_SESSION['fullnames'],
                    "Telephone" => $_SESSION['telephone'],
                    "ID Type" => $_SESSION['id_type'],
                    "ID Number" => $_SESSION['id_number'],
                    "Location" => $_SESSION['location'],
                    "Region" => $_SESSION['region'],
                    "DOB" => $_SESSION['dob'],
                    "Email" => $_SESSION['email']
                ];

                foreach ($fields as $label => $value) {
                    echo '<div class="row mb-3">
                            <div class="col-6 text-muted">'.$label.'</div>
                            <div class="col-6 text-right font-weight-bold">'.$value.'</div>
                          </div>
                          <hr>';
                }
                ?>

                <!-- Wallet Status -->
                <?php
                $sql = mysqli_query($con, $select);
                if ($sql && mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $wallet = $row['wallet'];
                        if ($price > $wallet) {
                            echo '<div class="alert alert-danger mt-3">
                                    <i class="fa fa-exclamation-circle"></i> Your balance is low. Top up your wallet to proceed.
                                  </div>';
                        }
                    }
                }
                ?>
            </div>

            <!-- Sticky Footer Button -->
            <div class="sticky-footer p-3">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="fullname" value="<?php echo $_SESSION['fullnames']; ?>" readonly>
                    <input type="hidden" name="telephone" value="<?php echo $_SESSION['telephone']; ?>" readonly>
                    <input type="hidden" name="id_type" value="<?php echo $_SESSION['id_type']; ?>" readonly>
                    <input type="hidden" name="id_number" value="<?php echo $_SESSION['id_number']; ?>" readonly>
                    <input type="hidden" name="location" value="<?php echo $_SESSION['location']; ?>" readonly>
                    <input type="hidden" name="region" value="<?php echo $_SESSION['region']; ?>" readonly>
                    <input type="hidden" name="dob" value="<?php echo $_SESSION['dob']; ?>" readonly>
                    <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>" readonly>
                    
                    <button type="submit" name="submit" class="btn btn-info btn-lg btn-block" id="btnSpinner">
                        <i class="fa fa-paper-plane"></i> Confirm and Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
}
}
?>

<style>
/* Full-screen card on mobile */
@media (max-width: 768px) {
    .profile-card {
        height: 100vh;
        border-radius: 0;
        display: flex;
        flex-direction: column;
    }

    .profile-card .card-body {
        flex-grow: 1;
        overflow-y: auto;
        padding-bottom: 100px; /* Prevent button overlap */
    }

    .sticky-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }
}
</style>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
						
		<div class="loading-overlay" id="spinner" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
<!--spinner start-->

      <!--at spinner-->
    <script>
        $(document).ready(function() {
    $('#btnSpinner').on('click', function() {
        // Show the spinner
        $('#spinner').show();
    });
    });
    </script>

	<style>
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0, 0, 0, 0.1);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .loading-text {
            margin-top: 10px;
            font-size: 18px;
            color: #007bff;
        }
    </style>

	</div>
		</div>
			<!-- Page content end -->
	</div>

	<!-- Required jQuery first, then Bootstrap Bundle JS -->
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/moment.js"></script>
	<!-- Rating JS -->
	<script src="vendor/rating/raty.js"></script>
	<script src="vendor/rating/raty-custom.js"></script>
	<!-- Main JS -->
	<script src="js/main.js"></script>
	</body>
</html>