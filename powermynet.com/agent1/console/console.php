<?php
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

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

$config = require 'at_config.php';

// Check if an AJAX request is made
if (isset($_GET['action']) && $_GET['action'] == 'getRemainingData') {
    // Example remaining GB value (you could replace this with real data from a database or session)
    $remainingGB = 51.00; // Replace with dynamic calculation if needed

    // Return JSON response
    echo json_encode(['remainingGB' => $remainingGB]);
    exit; // Stop further script execution for AJAX request
}

include'../../database/dbconfig.php';
$errors = array();
$success = array();

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
    }  
}


if (isset($_POST['new_console'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $package = mysqli_real_escape_string($con, $_POST['package']);
    $purchase_amount = mysqli_real_escape_string($con, $_POST['amount']);
    $real_package = mysqli_real_escape_string($con, $_POST['real_package']);
    $original = mysqli_real_escape_string($con, $_POST['original']);
    // Validate inputs
    if (empty($real_package)) {
        $errors['real_package'] = "Package is not selected";
    }
    if (empty($purchase_amount)) {
        $errors['purchase_amount'] = "Amount is not typed";
    }
    // Proceed if no validation errors
    if (empty($errors)) {
        // Check if console exists with the provided details
        $selectquery = "SELECT * FROM consoles WHERE agent_id='$agent_id' ";
        $select = mysqli_query($con, $selectquery);

        if ($select && mysqli_num_rows($select) > 0) {
            $errors['produce'] = "A console for this network exists. You can Buy Again or Renew";
        } else {
            // Check if user has console interests
            $interestQuery = "SELECT * FROM console_interest WHERE agent_id='$agent_id'";
            $interestResult = mysqli_query($con, $interestQuery);
            if (mysqli_num_rows($interestResult) > 0) {
                $row = mysqli_fetch_assoc($interestResult); // Fetch interest data
                $console_id = $row['console_id'];
                $console_name = $row['console_name'];
                    $_SESSION['console_id'] = $console_id;
                    $_SESSION['console_name'] = $console_name;
                    $_SESSION['package'] = $package;
                    $_SESSION['purchase_amount'] = $purchase_amount;
                    $_SESSION['real_package'] = $real_package;
                    $_SESSION['original'] = $original;
                    header('Location: console_new_payment.php');
                    exit();
            }
        }
    }
  }
  
  if (isset($_POST['renew_console'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $package = mysqli_real_escape_string($con, $_POST['package']);
    $purchase_amount = mysqli_real_escape_string($con, $_POST['amount']);
    $real_package = mysqli_real_escape_string($con, $_POST['real_package']);
    $original = mysqli_real_escape_string($con, $_POST['original']);
    // Validate inputs
    if (empty($real_package)) {
        $errors['real_package'] = "Package is not selected";
    }
    if (empty($purchase_amount)) {
        $errors['purchase_amount'] = "Amount is not typed";
    }
    // Proceed if no validation errors
    if (empty($errors)) {
        // Check if console exists with the provided details
        $selectquery = "SELECT * FROM consoles WHERE agent_id='$agent_id'";
        $select = mysqli_query($con, $selectquery);

        if ($select && mysqli_num_rows($select) > 0) {

            // Check if user has console interests
            $interestQuery = "SELECT * FROM console_interest WHERE agent_id='$agent_id'";
            $interestResult = mysqli_query($con, $interestQuery);
            if (mysqli_num_rows($interestResult) > 0) {
                $row = mysqli_fetch_assoc($interestResult); // Fetch interest data
                $console_id = $row['console_id'];
                $console_name = $row['console_name'];
                    $_SESSION['console_id'] = $console_id;
                    $_SESSION['console_name'] = $console_name;
                    $_SESSION['package'] = $package;
                    $_SESSION['purchase_amount'] = $purchase_amount;
                    $_SESSION['real_package'] = $real_package;
                    $_SESSION['original'] = $original;
                    header('Location: console_renew_payment.php');
                    exit();
      
            }
            
        } else {

            $errors['produce'] = "A console does not exist. Use buy new console";
            
        }
    }
  }
  
  
  
  
  // Process POST data
if (isset($_POST['check'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
        $package = $_SESSION['package'];
        $real_package = $_SESSION['real_package'];
        $original = $_SESSION['original'];
        $purchase_amount = htmlspecialchars($_POST['amount']);
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $agent_name = $first_name . " " . $last_name;
    $purpose = "RE-Load";
    $trans_date = date('Y-m-d');
    
            // Set timezone and dates
            date_default_timezone_set('GMT');
            $order_times = date('H:i:s A');
            $order_dates = date('l, F d, Y');
            $purchase_date = date('Y-m-d');
            $used_package = 0;
            $console_status = "ACTIVE";
            
               // Query to aggregate and rank agents by total orders and sum of order amounts
    $selectquerys = " SELECT * from console_transactions where trans_id='$reference_id' AND agent_email='$agent_email'";
    $selects = mysqli_query($con, $selectquerys);
     if ($selects && mysqli_num_rows($selects) > 0) {
        $error = "You cant use this reference to reload. It is either used to topup wallet or load a console before";
    header("Location: console.php?error=$error&reference_id=$reference_id");
    exit();
    }else {

            // Check if console exists with the provided details
        $selectquery = "SELECT * FROM consoles WHERE agent_id='$agent_id' ";
        $select = mysqli_query($con, $selectquery);
        if ($select && mysqli_num_rows($select) > 0) {
                $row = mysqli_fetch_assoc($select); // Fetch interest data
                $console_id = $row['console_id'];
                $console_name = $row['console_name'];   
                $console_package = $row['package'];
                $console_fixed_package = $row['fixed_package'];
                $console_real_package = $row['real_package'];
            
            $new_package = $console_package + $package;
            $new_real_package = $console_real_package + $original;
            
            $stmt = $con->prepare("UPDATE consoles SET package = ?, fixed_package = ?, real_package = ?, purchase_amount = ?, purchase_date = ?, used_package = ? WHERE agent_id = ?");
            $stmt->bind_param("sssdsss", $new_package, $new_package, $new_real_package, $purchase_amount, $purchase_date, $used_package, $agent_id);
            $uquery = $stmt->execute();
            if ($uquery) {
                
                        $stmt = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date) VALUES (?,?,?,?,?,?,?,?,?)");
                        $stmt->bind_param("sssssssss", $reference_id,$console_id, $console_name, $agent_id,$agent_name, $agent_email, $agent_tel, $package, $purchase_date);
                        $iquerys = $stmt->execute();
                        if ($iquerys) {
                        // Send confirmation email to agent and admin
                            $mail = new PHPMailer(true);
                            try {
                                // SMTP configuration
                                $mail->isSMTP();
                                $mail->Host = 'mail.codecraftnetwork.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'admin@codecraftnetwork.com';
                                $mail->Password = 'REDACTED';
                                $mail->SMTPSecure = "ssl";
                                $mail->Port = 465;
                                // Send email to agent
                                $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                                $mail->addAddress($agent_email);  // Agent email
                                // Agent Email Content
                                $mail->isHTML(true);
                                $mail->Subject = "Console Purchase | CONSOLE";
                                $mail->Body = "
                                    <html>
                                        <body>
                                            <p>Dear {$agent_name},</p>
                                            <p>You have successfully purchased a {$real_package} Console worth GHS{$purchase_amount}</p>
                                            <p>Your Reference ID is <strong>$reference_id</strong></p>
                                            <p>Thank you for your purchase!</p>
                                        </body>
                                    </html>";

                                $mail->send(); // Send email to agent
                                // Clear addresses and set new recipient for the admin
                                $mail->clearAddresses();
                                $mail->addAddress('owusuebenezer377@gmail.com');  // Admin email
                                 // Admin Email Content
                                 $mail->Body = "
                                     <html>
                                         <body>
                                             <p>Dear Admin,</p>
                                             <p>Agent {$agent_name} ({$agent_email}, {$agent_tel}) has purchased a {$real_package} Console worth GHS{$purchase_amount}.00.</p>
                                             <p>Purchase Details:</p>
                                             <ul>
                                                 <li>Console ID: {$console_id}</li>
                                                 <li>Console Name: {$console_name}</li>
                                                 <li>Network: {$network}</li>
                                                 <li>Package: {$real_package}</li>
                                                 <li>Amount: GHS{$purchase_amount}.00</li>
                                                 <li>Purchase Date: {$purchase_date}</li>
                                             </ul>
                                             <p>Please verify and update the records if necessary.</p>
                                         </body>
                                     </html>";
                                 $mail->send(); // Send email to admin
                                 // Redirect after success
                                 $success = "Console Renewal successful";
                                 header("Location: console.php?success=$success&reference_id=$reference_id");
                                 exit();
                             } catch (Exception $e) {
                                 $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                 header("Location: console.php?error=$error&reference_id=$reference_id");
                             }
                         } else {
                          $error = "Failed to add to console transaction";
                          header("Location: console.php?error=$error");
                         }
                
            } else {
                $error = "Console update failed: " . $stmt->error;
                header("Location: console.php?error=$error&reference_id=$reference_id");
                exit();
            }
            
        } 
        
        
        
        
        
        
        
        
        else {
    
                   $stmt = $con->prepare("INSERT INTO consoles (console_id, console_name, agent_id, agent_name, agent_email, agent_tel, package, used_package, purchase_amount, purchase_date, console_status, fixed_package, real_package) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                   $stmt->bind_param("ssssssssdssss", $console_id, $console_name, $agent_id, $agent_name, $agent_email, $agent_tel, $package, $used_package, $purchase_amount, $purchase_date, $console_status, $package, $original);
                   $iquery = $stmt->execute();
                       if ($iquery) {
                        // Insert into console_transactions
                        $stmt = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date) VALUES (?,?,?,?,?,?,?,?,?)");
                        $stmt->bind_param("sssssssss", $reference_id,$console_id, $console_name, $agent_id,$agent_name, $agent_email, $agent_tel, $package, $purchase_date);
                        $iquerys = $stmt->execute();
                        if ($iquerys) {
                        // Send confirmation email to agent and admin
                            $mail = new PHPMailer(true);
                            try {
                                // SMTP configuration
                                $mail->isSMTP();
                                $mail->Host = 'mail.codecraftnetwork.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'admin@codecraftnetwork.com';
                                $mail->Password = 'REDACTED';
                                $mail->SMTPSecure = "ssl";
                                $mail->Port = 465;
                                // Send email to agent
                                $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                                $mail->addAddress($agent_email);  // Agent email
                                // Agent Email Content
                                $mail->isHTML(true);
                                $mail->Subject = "Console Purchase | CONSOLE";
                                $mail->Body = "
                                    <html>
                                        <body>
                                            <p>Dear {$agent_name},</p>
                                            <p>You have successfully purchased a {$real_package} Console worth GHS{$purchase_amount}</p>
                                            <p>Your Reference ID is <strong>$reference_id</strong></p>
                                            <p>Thank you for your purchase!</p>
                                        </body>
                                    </html>";

                                $mail->send(); // Send email to agent
                                // Clear addresses and set new recipient for the admin
                                $mail->clearAddresses();
                                $mail->addAddress('owusuebenezer377@gmail.com');  // Admin email
                                 // Admin Email Content
                                 $mail->Body = "
                                     <html>
                                         <body>
                                             <p>Dear Admin,</p>
                                             <p>Agent {$agent_name} ({$agent_email}, {$agent_tel}) has purchased a {$real_package} Console worth GHS{$purchase_amount}.00.</p>
                                             <p>Purchase Details:</p>
                                             <ul>
                                                 <li>Console ID: {$console_id}</li>
                                                 <li>Console Name: {$console_name}</li>
                                                 <li>Package: {$real_package}</li>
                                                 <li>Amount: GHS{$purchase_amount}.00</li>
                                                 <li>Purchase Date: {$purchase_date}</li>
                                             </ul>
                                             <p>Please verify and update the records if necessary.</p>
                                         </body>
                                     </html>";
                                 $mail->send(); // Send email to admin
                                 // Redirect after success
                                 $success = "Console Purchase successful";
                                 header("Location: console.php?success=$success&reference_id=$reference_id");
                                 exit();
                             } catch (Exception $e) {
                                 $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                 header("Location: console.php?error=$error&reference_id=$reference_id");
                             }
                         } else {
                          $error = "Failed to add to console transaction";
                          header("Location: console.php?error=$error&reference_id=$reference_id");
                         }
                  } else {
                  $error = "Failed to purchase console";
                  header("Location: console.php?error=$error&reference_id=$reference_id");
                }

        }
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

<!-- New Console -->
<div class="modal fade" id="newConsole" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Purchase New Console</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
            <form action="" method="POST" enctype="multipart/form-data">
      <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label style="font-size:14px;"><strong>Select CONSOLE*</strong><br></label>
                    <select required name="console_package" class="form-control" id="list">
                        <option value="">Select a CONSOLE</option>
                     <?php
            include '../../database/dbconfig.php';
            $network="AT";
            $select = "SELECT * FROM console_prices WHERE network='$network' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                <option value="<?php echo $row['amount']; ?>"><?php echo $row['real_package']; ?></option>
                <?php
                }
              }
            ?> 
                    </select>
            </div>
          </div>
      </div>
      
                    <div class="row" id="price-container" style="display: block;">
                         <input type="hidden" class="form-control" name="package" id="gig" readonly>
                        <div class="mb-3" id="price">
                            <label>Price (GHS)</label>
                         <input type="text" class="form-control" name="amount" id="prices" readonly>
                       </div>
                         <input type="hidden" class="form-control" name="real_package" id="rpackage" readonly>
                         <input type="hidden" class="form-control" name="original" id="original" readonly>
                  </div>
      
            </div>
                     <!-- Spinner -->
          <div class="loading-overlay" id="spinner_send" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
          </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" name="new_console" class="btn btn-primary" id="btn_send">Continue to Payment</button>
      </div>
       </form>
    </div>
  </div>
</div>



  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#list').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_at_prices.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON respons
                        if (data.success) {
                            $('#gig').val(data.package_name); // Set the package name
                            $('#prices').val(selectedValue); // Set the selected price (amount)
                            $('#rpackage').val(data.real_package); // Set the real package from the database
                            $('#original').val(data.original); // Set the real package from the database
                            $('#price-container').show();
                        } else {
                            $('#price-container').hide();
                        }
                    }
                });
            } else {
                $('#price-container').hide();
            }
        });
    });
</script>

    <section class="section dashboard">
      <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-12">
              <div class="card recent-sales overflow-auto">
                <div class="card-body">
      				<?php if(count($errors)>0): ?>
                       <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                           <?php foreach($errors as $error): ?>
                               <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                  
                  <!-- Button trigger modal -->

<div class="row">
    <div class="col-sm-8 col-12">
            <div class="card recent-sales overflow-auto">
    <div class="card-body">
        <?php
include '../../database/dbconfig.php';

// Escaping the user_id for security
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);

// Query to check if the user has console interests
$interestQuery = "SELECT * FROM console_interest WHERE agent_id='$user_id'";
$interestResult = mysqli_query($con, $interestQuery);

if (mysqli_num_rows($interestResult) > 0) {
    // Query to fetch console details for the user
    $consoleQuery = "SELECT * FROM consoles WHERE agent_id='$user_id'";
    $consoleResult = mysqli_query($con, $consoleQuery);

    if (mysqli_num_rows($consoleResult) > 0) {
        // Loop through each console entry
        while ($row = mysqli_fetch_array($consoleResult)) {
            ?>
            <div class="row">
                <div class="col-xl-12 col-md-12 col-12">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body p-3 text-white" style="font-size:20px;">
                            <div class="row">
                                 <h2 class="text-white" style="font-weight:bolder;">AT</h2>
                            </div>
                            <div class="row">
                                <div>
                                    <span id="data-usage"><?php echo $row['used_package']; ?>GB / <?php echo $row['fixed_package']; ?>GB</span> | 
                                    <span id="battery-icon"><i class="fa fa-battery-full" aria-hidden="true"></i></span>
                                </div>   
                            </div>
                            <div class="row">
                                <div>
                                    <span style="font-size:10px; color:violet;">GHS<?php echo $row['purchase_amount']; ?> | 
                                    <span style="font-size:10px; color:violet;">Purchased Date: <?php echo $row['purchase_date']; ?></span></span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        
<?php
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$query = "SELECT * FROM consoles WHERE agent_id='$user_id'";
$sql = mysqli_query($con, $query);
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_array($sql)) {
        $fixed_package = $row['fixed_package'];
        $used_package = $row['used_package'];
        $row_package = $row['package'];
        // Calculate if used_package is 70% or more of package
        if ($used_package >= (0.7 * $fixed_package) || $row_package<20) {
            ?>
             <!-- No consoles found for user, prompt to buy a console
            <div class="card-footer">
                <div class="row">
                    <div class="col-6 text-left">
                        <button type="button" class="btn btn-primary bg-white text-primary btn-block" data-bs-toggle="modal" data-bs-target="#renewModal">
                            Renew <i class="fa fa-money" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            -->
            <?php
        }
    }
}
?>

                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <!-- No consoles found for user, prompt to buy a console
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConsole">Buy Console <i class="fa fa-money" aria-hidden="true"></i></button>
        -->
        <?php
    }
} else {
    ?>
     <!-- No consoles found for user, prompt to buy a console
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConsole">Buy Console <i class="fa fa-money" aria-hidden="true"></i></button>
    -->
    <?php
}
?>
    </div>
    </div>
    </div>
    
<!--
    <div class="col-sm-4 col-12">
    <div class="card recent-sales overflow-auto">
        <div class="card-header">
			<div class="card-title" style="font-size:14px;">Console Failure (Incase your console fails, use this space. <strong>You will not pay for it again</strong>)</div>
		</div>
    <div class="card-body">
        <h6>Search Reference ID</h6>
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-sm-7 col-8">
                                                    <input type="text" class="form-control" name="reference_id" id="reference_id" placeholder="Reference id" required>
                                             </div>
                                             <div class="col-sm-5 col-4">
                                                    <button class="btn btn-primary" type="submit">Search</button>
                                             </div>
                                        </div>
                                    </form>


<?php
$email = $_SESSION['email'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['reference_id'])) {
    // Get the user-inputted reference ID
    $reference_id = htmlspecialchars($_POST['reference_id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer REDACTED",
            "Cache-Control: no-cache",
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        // Decode JSON response
        $responseData = json_decode($response, true);
        if ($responseData['status']) {
            $transaction = $responseData['data'];
            if($transaction['customer']['email']==$email){
                            // Only display results if the status is not "abandoned" or "failed"
            if (!in_array($transaction['status'], ['abandoned', 'failed'])) {
                $originalAmount = $transaction['amount'] / 100;
                // Calculate 2% of the original amount
                $deduction = $originalAmount * 0.02;
                // Deduct the 2% from the original amount
                $finalAmount = $originalAmount - $deduction;
                // Format the final amount to 2 decimal places
                $finalAmountFormatted = number_format($finalAmount, 2);
           // Get the transaction date and set the reference date
// Get the transaction date and set the current date as the reference date
$transactionDate = new DateTime($transaction['paid_at']);
$transactionDateFormatted = $transactionDate->format('Y-m-d');
$currentDate = new DateTime(); // This will use the current date

// Convert formatted dates back to DateTime objects for direct comparison
$transactionDateFormatted = new DateTime($transactionDateFormatted);
$currentDateFormatted = new DateTime($currentDate->format('Y-m-d'));

// Determine if the transaction date is on or before the current date
if ($transactionDateFormatted >= $currentDateFormatted) {

                // Display form with transaction reference and adjusted amount
                ?>
                
                <form class="mt-2" action="" method="POST" enctype="multipart/form-data">
                    <div class="row gutters">
                        <div class="col-sm-12 col-12">
                            <div class="form-group">
                                <input type="hidden" class="form-control" value="<?php echo htmlspecialchars($transaction['reference']); ?>" name="reference_id" readonly required>
                                <input type="hidden" class="form-control" value="<?php echo $finalAmountFormatted; ?>" name="amount" readonly required>
                            </div>
                        </div>
                        
                        <div class="col-sm-12">
                            <div class="text-right">
                                <button type="submit" name="check" class="btn btn-block btn-info">Reload Again</button>
                            </div>
                        </div>
                        
                    </div>
                </form>
                <?php
} else {
       echo "Transaction status is '" . htmlspecialchars($transaction['status']) . "'. No details available.";
}

            } else {
                echo "Transaction status is '" . htmlspecialchars($transaction['status']) . "'. No details available.";
            }
                
            }
            else{
               echo "Failed You cannot use somebody's else reference_id.";
            }
            
        } else {
            echo "Reference ID not found: " . htmlspecialchars($responseData['message']);
        }
    }
}
?>
                
    </div>
    </div>
    </div>
    -->
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
    
<!-- Spinner and Overlay Styles -->
<style>
    /* Full-page overlay styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    /* Spinner styles */
    .spinner {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    /* Spinner animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Loader text */
    .loading-text {
        margin-top: 10px;
        font-size: 1.2em;
        color: #333;
    }
</style>

<!-- jQuery and Script to Show Spinner on Button Click -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#btn_send').on('click', function() {
            // Show the spinner overlay
            $('#spinner_send').show();
        });
    });
</script>

  </main><!-- End #main -->
 <?php include 'includes/footer.php'; ?> 
  <!-- ======= Footer ======= -->
 
  <!-- End Footer -->
  
<script>
window.onload = function() {
    function updateBatteryIcon() {
        // Get the text content from the data-usage element
        let dataUsageText = document.getElementById('data-usage').textContent;

        // Split the text based on " / " to separate remainingGB and totalGB
        let [remainingGBText, totalGBText] = dataUsageText.split(" / ");

        // Parse both values as floats, removing 'GB' from each string
        let remainingGB = parseFloat(remainingGBText.replace("GB", "").trim());
        let totalGB = parseFloat(totalGBText.replace("GB", "").trim());

        // Check if parsing was successful
        if (isNaN(remainingGB) || isNaN(totalGB) || totalGB === 0) {
            console.error("Failed to parse data usage values");
            return;
        }

        // Calculate the percentage of data remaining
        let percentage = (remainingGB / totalGB) * 100;
        console.log(`Remaining GB: ${remainingGB}, Total GB: ${totalGB}, Percentage: ${percentage}%`);

        // Choose the appropriate icon based on remaining data
        let iconHTML;
        if (percentage >= 99) {
            iconHTML = '<i class="fa fa-battery-full" aria-hidden="true"></i>';
        } else if (percentage >= 75) {
            iconHTML = '<i class="fa fa-battery-three-quarters" aria-hidden="true"></i>';
        } else if (percentage >= 50) {
            iconHTML = '<i class="fa fa-battery-half" aria-hidden="true"></i>';
        } else if (percentage >= 25) {
            iconHTML = '<i class="fa fa-battery-quarter" aria-hidden="true"></i>';
        } else {
            iconHTML = '<i class="fa fa-battery-empty" aria-hidden="true"></i>';
        }

        // Update the battery icon
        document.getElementById('battery-icon').innerHTML = iconHTML;
    }

    // Initialize and update every 1 seconds
    updateBatteryIcon();
    setInterval(updateBatteryIcon, 1000);
};

</script>

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