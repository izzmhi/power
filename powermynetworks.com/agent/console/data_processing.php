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

// Check if an AJAX request is made
if (isset($_GET['action']) && $_GET['action'] == 'getRemainingData') {
    // Example remaining GB value (you could replace this with real data from a database or session)
    $remainingGB = 51.00; // Replace with dynamic calculation if needed
    // Return JSON response
    echo json_encode(['remainingGB' => $remainingGB]);
    exit; // Stop further script execution for AJAX request
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
    }  
}

$agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$select = "SELECT * FROM console_interest WHERE agent_id=?";

if ($stmt = $con->prepare($select)) {
    $stmt->bind_param("s", $agent_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Check console status
            if ($row['console_status'] !== "APPROVED") {
                $error = "Your Console Interest Request has not been approved yet.";
                header("Location: index.php?error=" . urlencode($error));
                exit;
            }
        } else {
            $error = "No Console Interest Request found for your account.";
            header("Location: index.php?error=" . urlencode($error));
            exit;
        }
    } else {
        $error = "Failed to execute query. Please try again later.";
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
} else {
    $error = "Failed to prepare the statement. Please try again later.";
    header("Location: index.php?error=" . urlencode($error));
    exit;
}

$errors = array();
$success = array();
if (isset($_POST['submit'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $gig = mysqli_real_escape_string($con, $_POST['gig']);
    $recipient_number = mysqli_real_escape_string($con, $_POST['recipient_number']);
    $real_package = mysqli_real_escape_string($con, $_POST['real_package']);
    $console_id = mysqli_real_escape_string($con, $_SESSION['console_id']);
    $console_name = mysqli_real_escape_string($con, $_SESSION['console_name']);

    // Validation checks
    if (empty($gig)) $errors['gig'] = "Gig is not selected";
    if (empty($recipient_number)) $errors['beneficiary'] = "Recipient number is required";
    if (empty($console_id)) $errors['console_id'] = "Console id is not found";
    if (empty($console_name)) $errors['console_name'] = "Console not found";

    if (empty($errors)) {
        
        // Order details
    $status = "Crediting Successful";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
    $real_date = date('Y-m-d');

        $select = "SELECT * FROM consoles WHERE agent_id=?";
        if ($stmt = $con->prepare($select)) {
            $stmt->bind_param("s", $agent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['package'] < $gigs) {
                    $errors["Purchase"] = 'Failed to send. You do not have enough balance.';
                            $error = "Failed to send. You do not have enough balance.";
                           header("Location: data_processing.php?error=" . urlencode($error));
                           exit;
                } else {
                        $package = $row['package'];
                        $used_package = $row['used_package'];
                        $console_real_package = $row['real_package'];

// Generate reference ID
$last_2_chars = substr($recipient_number, -2);
$first_five_chars = substr($recipient_number, 0, 5);
$seconds = date('His');
$month = date('md');
$reference_id = $first_five_chars . $month . $seconds . $last_2_chars;


$soap_request = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
    <soapenv:Header/>
    <soapenv:Body>
        <tem:FlexiIshareBundle>
            <tem:username>CodeCraftNetwork</tem:username>
            <tem:password>REDACTED</tem:password>
            <tem:dealerMsisdn>233276420033</tem:dealerMsisdn>
            <tem:recipientMsisdn>{$recipient_number}</tem:recipientMsisdn>
            <tem:transactionId>{$reference_id}</tem:transactionId>
            <tem:sharedBundle>{$real_package}</tem:sharedBundle>
        </tem:FlexiIshareBundle>
    </soapenv:Body>
</soapenv:Envelope>
XML;

$url = "http://41.215.168.146:443/FlexiShareBundles.asmx"; // API endpoint

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: text/xml; charset=utf-8',
    'SOAPAction: "http://tempuri.org/FlexiIshareBundle"'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);

// Execute the cURL request and capture the response
$response = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
        $error = "Order Failed. cURL Error: " . curl_error($ch);
        header("Location: data_processing.php?error=" . urlencode($error));
            curl_close($ch);
        exit;
}

// Parse the SOAP XML response
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);

// Check if XML parsing was successful
if ($xml === false) {
    echo "Error: Unable to parse SOAP response.";
        $error = "Order Failed. Error: Unable to parse SOAP response.";
        header("Location: data_processing.php?error=" . urlencode($error));
    foreach(libxml_get_errors() as $error) {
        echo "Error Code: " . $error->code . " - " . $error->message ;
            $error = "Order Failed. Error Code: " . $error->code . " - " . $error->message . "Contact Admin for help";
            header("Location: data_processing.php?error=" . urlencode($error));
                exit;
        
    }
    curl_close($ch);
    exit;
}

// Extract the error code and message from the response
$namespace = "http://tempuri.org/";  // SOAP namespace
$body = $xml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body;
$flexi_response = $body->children($namespace)->FlexiIshareBundleResponse->FlexiIshareBundleResult->ApiResponse;

$response_code = (string)$flexi_response->ResponseCode; // Extract ResponseCode
$response_msg = (string)$flexi_response->ResponseMsg;   // Extract ResponseMsg

// Check the response code
if ($response_code == '200') {
    if ($insertordersItems = $con->prepare("INSERT INTO orders_at (reference_id, beneficiary, gig, real_package, order_time, order_date, real_date, order_status, error_code, message, client_email) VALUES (?,?,?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
    $insertordersItems->bind_param("sssssssssss", $reference_id, $recipient_number, $gig, $real_package, $order_time, $order_date, $real_date, $status, $response_code, $response_msg, $agent_email);

    if ($insertordersItems->execute()) {
        // Send SMS notification
        $smsData = [
            "type" => 1,
            "senderid" => "CodeCraft",
            "messages" => [
                [
                    "recipient" => $recipient_number,
                    "message" => "You have received a new bundle allocation of {$gig} GB."
                ]
            ]
        ];

        $ch_sms = curl_init('https://api.moolre.com/open/sms/send');
        curl_setopt($ch_sms, CURLOPT_POST, true);
        curl_setopt($ch_sms, CURLOPT_POSTFIELDS, json_encode($smsData));
        curl_setopt($ch_sms, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-VASKEY: REDACTED'
        ]);
        curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);
        $sms_response = curl_exec($ch_sms);
        $sms_http_code = curl_getinfo($ch_sms, CURLINFO_HTTP_CODE);
        curl_close($ch_sms);

        if ($sms_http_code == 200) {
        
                        // Insert into console_orders table
                        $stmt = $con->prepare("INSERT INTO console_orders (order_id, console_id, console_name, agent_id,agent_name, agent_email, agent_tel, recipient, package, order_time, order_date, real_date,order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                        $stmt->bind_param("sssssssssssss", $reference_id,$console_id,$console_name,$agent_id,$agent_name,$agent_email,$agent_tel,$recipient_number,$gig,$order_time,$order_date,$real_date,$status);
                        
                        if ($stmt->execute()) {
                            // Update package balance
                            $current_package = $package - $gig;
                            $current_used_package = $used_package + $gig;
                            $current_real_package = $console_real_package - $real_package;

                            $stmt = $con->prepare("UPDATE consoles SET package = ?, used_package = ?, real_package = ? WHERE agent_id = ?");
                            $stmt->bind_param("ssss", $current_package, $current_used_package, $current_real_package, $agent_id);

                            if ($stmt->execute()) {
                                // Insert into console_transactions table
                                $stmt = $con->prepare("INSERT INTO console_transactions(trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date) VALUES (?,?,?,?,?,?,?,?,?)");
                                                        $stmt->bind_param("sssssssss", $reference_id,$console_id,$console_name,$agent_id,$agent_name,$agent_email,$agent_tel,$gig,$real_date);

                                if ($stmt->execute()) {
                        $success = "Order sent successfully.";
                        header("Location: index.php?success=$success&reference_id=$reference_id");
                        exit();
                                    
                                
                                } else {
                                    $error = "Failed to insert into transactions: " . $stmt->error;
                                    header("Location: data_processing.php?error=" . urlencode($error));
                                    exit();
                                }
                            } else {
                                $error = "Failed to update console: " . $stmt->error;
                                header("Location: data_processing.php?error=" . urlencode($error));
                                exit();
                            }
                        } else {
                            $error = "Failed to purchase package: " . $stmt->error;
                            header("Location: data_processing.php?error=" . urlencode($error));
                            exit();
                        }

        } 
        
       else {
            $error = "Order Failed. Failed to send SMS. Response: $sms_response";
            header("Location: data_processing.php?error=" . urlencode($error));
                exit;
        }

    } else {
        $error = "Order Failed. Failed to insert into database: " . $insertordersItems->error;
        header("Location: data_processing.php?error=" . urlencode($error));
            exit;
    }
    $insertordersItems->close();
} else {
    $error = "Order Failed. Unable to prepare order insertion statement: " . $con->error;
    header("Location: data_processing.php?error=" . urlencode($error));
    exit;
}  
    echo "success: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
} else {
    $error = "Order Failed: " . htmlspecialchars($response_msg);
    header("Location: data_processing.php?error=" . urlencode($error));
    exit;
           }
    // Close cURL session
    curl_close($ch);

                }
            } else {
                $error = "Agent information not found.";
                header("Location: index.php?error=" . urlencode($error));
                exit();
            }
            $stmt->close();
        } else {
            $error = "Database error: unable to prepare SELECT statement.";
            header("Location: data_processing.php?error=" . urlencode($error));
            exit();
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
    <section class="section dashboard">
       <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-12">
            <div class="row">

            <!-- Recent Sales -->
            <div class="col-12">
                <div class="card recent-sales overflow-auto">
                   <div class="card-body">
                      <h5 class="card-title">Order Confirmation</h5>
                      
                                          <?php
                    if(isset($_GET['new_success'])){
                        ?>
                        <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['new_success']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['new_console_error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red"> <?php echo $_GET['new_console_error']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['new_console_transaction_error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red"> <?php echo $_GET['new_console_transaction_error']; ?> </div>
                        <?php
                    }
                    ?>
                  
                  	<?php if(count($errors)>0): ?>
                       <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                           <?php foreach($errors as $error): ?>
                               <div><?php echo $error; ?></div>
                           <?php endforeach; ?>
                       </div>
                    <?php endif; ?> 
                    
                           	<?php if(isset($_GET['error'])){
                           	?>
                            <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                           <?php echo $_GET['error']; ?>
                       </div>
                    <?php  
                         }
                    ?> 

            <div class="row">
                <div class="col-xl-6 col-md-12 col-12">
                    
    <?php
    include '../../database/dbconfig.php';               
    $user_id  = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $query = "select * from console_interest where agent_id='$user_id' ";
    $sql=mysqli_query($con,$query);
    if(mysqli_num_rows($sql)>0){
        while($row = mysqli_fetch_array($sql))  
            {
                if($row['console_status']=="PENDING"){
                    ?>
                           <div class="row" >
                               <h3>CONSOLE request status is <strong class="text-primary">PENDING</strong></h3>
                               <div>You have already requested for a CONSOLE. Don't do anything for now as Admin is reviewing your eligibility to get a CONSOLE. Contact Admin if the process is taking long</div>
                            </div>
                    <?php
                }
    else if($row['console_status']=="APPROVED"){
        ?>

        <?php
         $gig = $_SESSION['agig'];
         $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
         $select = "SELECT package FROM consoles WHERE agent_id=?";
        if ($stmt = $con->prepare($select)) {
            $stmt->bind_param("s", $agent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['package'] < $gig) {
                    ?>
                      <h3>You do not have enough Concole balance.</h3>
                      <?php
                } else {
                    ?>
                      
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body p-3 text-white" style="font-size:20px;">
                            <div class="row">
                                 <h6 class="text-white" style="font-weight:bolder;">Wait for order to complete before closing this page</h6>
                            </div>
                            <hr>
                            <div class="row">
                                <div>
                                    <span style="font-size:12px;">Number</span>
                                    <input type="text" name="recipient_number" class="form-control" value="<?php echo $_SESSION['arecipient_number']; ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div>
                                    <span style="font-size:12px;">Package (Gig)</span>
                                    <input type="text" name="gig" class="form-control" value="<?php echo $_SESSION['agig'];?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div>
                                    <input type="hidden" name="real_package" class="form-control" value="<?php echo $_SESSION['areal_package'];?>" readonly>
                                </div>
                            </div>

                        </div>
                        <hr>
                                 <!-- Spinner -->
          <div class="loading-overlay" id="spinner_send" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
          </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6 text-right">
                                    <button type="submit" name="submit" class="btn btn-primary bg-white text-primary btn-block" id="btn_send" >Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                      
                      <?php
                }
            }
        }
 
    }
                
                else if($row['console_status']=="REJECTED"){
                   ?>
                    <div class="row" >
                               <h3>CONSOLE request status is <strong class="text-danger">REJECTED</strong></h3>
                               <div>Your console requested is rejected by the Admin. Contact Admin on what to do next</div>
                            </div>
                       <?php
               }
                    
            }
    }
    ?>

                </div>
            </div>

        </div>
    </div>
    
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