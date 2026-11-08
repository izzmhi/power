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

$errors = array();
$success = array();

if(isset($_POST['new_console'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $console_name = "CONSOLE " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

        // Check if console exists with the provided details
    $selectquery = "SELECT * FROM console_interest WHERE agent_id='$user_id' ";
    $select = mysqli_query($con, $selectquery);
    if ($select && mysqli_num_rows($select) > 0) {
             $errors['produce']="A requet has been sent to the Admin for this Console. Admin will respond to your request shortly";
    }
    else{ 
    date_default_timezone_set('GMT');
    $order_times = date('H: i: s: A');
    $order_dates = date('l, F d, Y');
    $register_date = date('Y-m-d');
    $date4 = date('ymd');
    $time  = date('his');
    $to = bin2hex(random_bytes(3));
    $tok = bin2hex(random_bytes(2));
    $console_id = $to.$date4.$tok.$time;
    $console_status="PENDING";
                $stmt = $con->prepare("INSERT INTO console_interest (console_id,console_name,agent_id,agent_name,agent_email,agent_tel,register_date,console_status) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssssss", $console_id,$console_name,$agent_id,$agent_name,$agent_email,$agent_tel,$register_date,$console_status);
                $iquery = $stmt->execute();
                if ($iquery) {
                    
             $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress('owusuebenezer377@gmail.com');
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Console Interest| CONSOLE";
                    $mail->Body = "
                    <html>
               <body>
                   <div class='container-xxl bg-white p-0'>
                   <div class='container-xxl position-relative p-0'>
                     <br>
                 <p>Dear Admin,</p>
                 <p> Agent $agent_name is interested in a Console</p>
                    <p>Have a great day!</p>
                         <!-- Footer Start -->
                   <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                       <div class='container'>
                           <div class='copyright'>
                               <div class='row'>
                                   <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                       &copy; <a class='border-bottom'>CODECRAFTNETWORK </a>, All Right Reserved. 
                                   </div>
                                   <div class='col-md-6 text-center text-md-end'>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   <!-- Footer End -->
               </body>
                    </html>";

                    $mail->send();
                    $success['produce']="Status changed successfully";
                } catch (Exception $e) {
                    $errors['mail_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }    

                        $success = "Your request has been sent to the admin. You will be notified when Admin Approved your request.";
                        header("Location: index.php?success=$success");
                        exit();
                } else {
                    $errors['insert'] = "Failed to send request: " . $stmt->error;
                }
           }
    	}

if(isset($_POST['at_send_single'])){
$agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
$last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
$agent_name = $first_name . " " . $last_name;
$agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
$agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$recipient_number = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$console_id = filter_input(INPUT_POST, 'console_id', FILTER_SANITIZE_SPECIAL_CHARS);
$console_name = filter_input(INPUT_POST, 'console_name', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = $gig."000";
    if (empty($gig)){
        $errors = "Gig is not selected";
        header("location:index.php?error=$errors");
    }
    if (empty($recipient_number)){
        $errors = "Recipient number is required";
        header("location:index.php?error=$errors");
    }
    if (empty($console_id)){
        $errors = "Console id is not found";
        header("location:index.php?error=$errors");
    }
    if (empty($console_name)){
        $errors = "Console not found";
        header("location:index.php?error=$errors");
    }
    else{
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
                   header("Location: index.php?error=" . urlencode($error));
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
            <tem:username>{$config['username']}</tem:username>
            <tem:password>{$config['password']}</tem:password>
            <tem:dealerMsisdn>{$config['dealerMsisdn']}</tem:dealerMsisdn>
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
        header("Location: index.php?error=" . urlencode($error));
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
        header("Location: index.php?error=" . urlencode($error));
    foreach(libxml_get_errors() as $error) {
        echo "Error Code: " . $error->code . " - " . $error->message ;
            $error = "Order Failed. Error Code: " . $error->code . " - " . $error->message . "Contact Admin for help";
            header("Location: index.php?error=" . urlencode($error));
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
                            header("Location: index.php?error=" . urlencode($error));
                            exit();
                        }
                    } else {
                        $error = "Failed to update console: " . $stmt->error;
                        header("Location: index.php?error=" . urlencode($error));
                        exit();
                    }
                } else {
                    $error = "Failed to purchase package: " . $stmt->error;
                    header("Location: index.php?error=" . urlencode($error));
                    exit();
                }
            } 
        
       else {
            $error = "Order Failed. Failed to send SMS. Response: $sms_response";
            header("Location: index.php?error=" . urlencode($error));
                exit;
        }

    } else {
        $error = "Order Failed. Failed to insert into database: " . $insertordersItems->error;
        header("Location: index.php?error=" . urlencode($error));
            exit;
    }
    $insertordersItems->close();
} else {
    $error = "Order Failed. Unable to prepare order insertion statement: " . $con->error;
    header("Location: index.php?error=" . urlencode($error));
    exit;
}  
    echo "success: Error Code " . htmlspecialchars($response_code) . " - " . htmlspecialchars($response_msg);
} else {
    $error = "Order Failed: " . htmlspecialchars($response_msg);
    header("Location: index.php?error=" . urlencode($error));
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
            header("Location: index.php?error=" . urlencode($error));
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

<!--AirtelTigo-->
<!--Bulk Send data Modal -->
<div class="modal fade " id="atBSendModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog " role="document">
<div class="modal-content bg-primary">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Send Bulk Data</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data">
    <div class="modal-body">

     <div class="form-group ">
		<label for="fullName text-white">Number of Recipients</label>
		<input type="number" class="form-control" id="agent_total" name="total" required>
	</div>

	
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="at_send_bulk" class="btn btn-light btn-block" id="setBtn">Send Data</button>
    </div>
</form>

    </div>
  </div>
</div>

<!--Single Send data Modal -->
<div class="modal fade " id="atSSendModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog bg-primary" role="document">
    <div class="modal-content bg-primary">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Send Data to Single Customer</h5>
        <button type="button text-white" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="send_data.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div>
            <input type="hidden" class="form-control" name="network" value="AT" readonly>
          </div>
            <div>
                <?php
                include'../../database/dbconfig.php';
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $query = "SELECT * FROM consoles WHERE agent_id='$user_id'";
                $sql = mysqli_query($con, $query);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $package = $row['package'];
                        $used_package = $row['used_package'];
                            ?>
                                <input type="hidden" name="console_id" class="form-control" value="<?php echo $row['console_id'];?>" readonly >
                                <input type="hidden" name="console_name" class="form-control" value="<?php echo $row['console_name'];?>" readonly >
                            <?php
                    }
                }
                ?>
            </div>
          
          
          <div id="at-send-container">
            <div class="mb-3">
              <label class="text-white">Enter Gig</label>
              <input type="text" name="gig" class="form-control" id="gigat" placeholder="Enter Gig here" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" onkeypress="return allowNumbersOnly(event);">
       
            </div>
            <div class="mb-3">
              <label class="text-white">Recipient number</label>
              <input type="tel" class="form-control" id="atrecipient" placeholder="Recipient number" name="beneficiary" maxlength="10" title="Please enter a valid 10-digit AT number" required>
            </div>
            <div class="mb-3">
              <label class="text-white">Repeat Recipient number</label>
              <input type="tel" class="form-control" id="atrepeat" placeholder="Repeat Recipient number" name="beneficiary_repeat" maxlength="10" title="Please enter a valid 10-digit number" required>
            </div>
            <div class="mb-3">
              <div id="atmessage" style="display: none; color: red;">Recipient numbers do not match</div>
              <div id="atmessagegreen" style="display: none; color: white;">Recipient numbers match</div>
            </div>
          </div>
          <!-- Spinner -->
<div class="loading-overlay" id="spinner_send" style="display:none;">
    <div class="spinner"></div>
    <div class="loading-text">Please Wait...</div>
</div>
        </div>
        <div class="modal-footer" id="footer_at_send" style="display:none;">
          <button type="submit" name="send_single" class="btn btn-light btn-block" id="btn_send">Send Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function allowNumbersOnly(event) {
    const charCode = event.which || event.keyCode;

    // Allow only numbers 1-9
    if (charCode >= 48 && charCode <= 57) { // ASCII for '1' to '9'
        return true;
    }

    // Block other keys
    return false;
}
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  /* Loader overlay styles */
  #loaderOverlay {
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
  /* Loader spinner styles */
  .loader {
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
  #loaderText {
    margin-top: 10px;
    font-size: 1.2em;
    color: #333;
  }
</style>
    <script>
        $(document).ready(function() {
    $('#btn_send').on('click', function() {
        // Show the spinner
        $('#spinner_send').show();
    });
    });
    </script>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    var recipientInput = document.getElementById('atrecipient');
    var repeatInput = document.getElementById('atrepeat');
    var message = document.getElementById('atmessage');
    var messageg = document.getElementById('atmessagegreen');
    var footer = document.getElementById('footer_at_send');

    function validatePhoneNumber(input) {
      // Enforce digit-only input and limit to 10 digits
      input.value = input.value.replace(/\D/g, '').slice(0, 10);
    }

    function checkRecipientMatch() {
      // Apply digit-only formatting to ensure both values are processed correctly
      validatePhoneNumber(recipientInput);
      validatePhoneNumber(repeatInput);

      // Check if both fields are exactly 10 digits and match
      if (recipientInput.value.length === 10 && repeatInput.value.length === 10 && recipientInput.value === repeatInput.value) {
        // Numbers match, show success message
        message.style.display = 'none';
        messageg.style.display = 'block';
        footer.style.display = 'block';
      } else {
        // Numbers do not match or are not yet 10 digits
        message.style.display = 'block';
        messageg.style.display = 'none';
        footer.style.display = 'none';
      }
    }

    // Attach event listeners to validate as user types
    recipientInput.addEventListener('input', checkRecipientMatch);
    repeatInput.addEventListener('input', checkRecipientMatch);
  });
</script>



    <section class="section dashboard">
       <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-12">
            <div class="row">
            <!-- Recent Sales -->
            <div class="col-12">
                <div class="card recent-sales overflow-auto">
                   <div class="card-body">
                 <?php
                    if(isset($_GET['success'])){
                        ?>
                        <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red"> <?php echo $_GET['error']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['new_success'])){
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
        
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$query = "SELECT * FROM consoles WHERE agent_id='$user_id'";
$sql = mysqli_query($con, $query);

if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_array($sql)) {
        if ($row['console_status'] == "BLOCKED") {
            ?>
            <div class="row">
                <h3>Your CONSOLE is <strong class="text-warning">BLOCKED</strong></h3>
                <div>Admin has BLOCKED your CONSOLE. Contact Admin to find out what to do next</div>
            </div>
            <?php
        } elseif ($row['console_status'] == "CANCELLED") {
            ?>
            <div class="row">
                <h3>Your CONSOLE is <strong class="text-danger">CANCELLED</strong></h3>
                <div>Admin has CANCELLED your CONSOLE. Contact Admin to find out what to do next</div>
            </div>
            <?php
        } elseif ($row['console_status'] == "REJECTED") {
            ?>
            <div class="row">
                <h3>CONSOLE request status is <strong class="text-danger">REJECTED</strong></h3>
                <div>Your console request was rejected by the Admin. Contact Admin for further instructions.</div>
            </div>
            <?php
        } else {
            // Display console information for other statuses
            ?>
            <div class="row">
                <div class="col-xl-6 col-md-12 col-12">
                    <div class="card bg-primary text-white mb-4">
                        <div class="modal-header">
                        </div>
                        <div class="card-body p-3 text-white" style="font-size:20px;">
                            <div class="row">
                                <h4 class="text-white" style="font-weight:bolder;"><?php echo $row['console_name'] ?></h4>
                            </div>
                            <div class="row">
                                <div>
                                    <span id="data-usage"><?php echo $row['used_package']; ?>GB / <?php echo $row['fixed_package']; ?>GB</span>
                                    | <span id="battery-icon"><i class="fa fa-battery-full" aria-hidden="true"></i></span>
                                </div>
                            </div>
                            <div class="row">
                                <div>
                                    <span style="font-size:10px; color:violet;">
                                        GHS<?php echo $row['purchase_amount']; ?> |
                                        Purchased Date: <?php echo $row['purchase_date']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            
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
   <div class="row bg-light rounded shadow-sm">
    <h3 class="mb-1 mt-1 text-dark text-center"> Renew Console </h3>
    <hr class="my-3">
    <div class="row fw-bold text-primary mb-2">
        <div class="col-6">
            📦 Package
        </div>
        <div class="col-6 text-end">
            💰 Price
        </div>
    </div>
    
    <?php
        $select = "SELECT * FROM console_prices ORDER BY package ASC";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        if ($check) {
            while ($row = mysqli_fetch_array($sql)) {
    ?>
        <div class="row align-items-center border-bottom">
            <div class="col-6">
                <span class="text-dark" style="font-size:15px;">📦 <?php echo $row['real_package'] ?></span>
            </div>
            <div class="col-6 text-end">
                <span class="text-success fw-bold" style="font-size:15px;">GHS <?php echo number_format($row['amount'], 2) ?></span>
            </div>
        </div>
    <?php
            }
        } else {
    ?>
        <div class="text-danger mt-3">🚫 No Console Price found</div>
    <?php
        }
    ?>
</div>
            <?php
        }
    }
}
?>

                            
                        </div>
                        <hr>
                        
                        <?php
                        
                                        // Check customer_mtn_activation status
                $consoleActivationStatus = mysqli_query($con, "SELECT * FROM console_activation");
                $consoleActivationRow = mysqli_fetch_array($consoleActivationStatus);
                
                if ($consoleActivationRow['status'] == "DEACTIVATED") {
                    echo '<div class="col-lg-12 col-12" style="margin-bottom:10px;">OUT OF STOCK</div>';
                } else {
                   ?>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="button" class="btn btn-primary bg-white text-primary btn-block" data-toggle="modal" data-target="#atSSendModal">Send Single Data <i class="fa fa-phone-square" aria-hidden="true"></i></button>
                                </div>
                                <!--
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-primary bg-white text-primary btn-block" data-toggle="modal" data-target="#atBSendModal">Send Bulk Data <i class="fa fa-phone-square" aria-hidden="true"></i></button>
                                </div>
                                -->
                            </div>
                        </div>

                   <?php
                }
                        
                
                        ?>

                    </div>
                </div>
            </div>
            <?php
        }
    }
} else {
    // If no console found for the user, provide an option to buy a console
    ?>
   <div class="row bg-light p-4 rounded shadow-sm">
    <h5 class="mb-3">
        ✅ Your CONSOLE is <strong class="text-success">APPROVED</strong>
    </h5>
    <div class="mb-1">
        💳 <strong>CONSOLE Payment</strong> is only through <strong>MoMo</strong> 📱
    </div>
    <div class="mb-3">
        📞 Contact Admin to Pay and have your CONSOLE loaded.
    </div>
    
    <hr class="my-3">

    <div class="row fw-bold text-primary mb-2">
        <div class="col-6">
            📦 Package
        </div>
        <div class="col-6 text-end">
            💰 Price
        </div>
    </div>
    
    <?php
        $select = "SELECT * FROM console_prices ORDER BY package ASC";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        if ($check) {
            while ($row = mysqli_fetch_array($sql)) {
    ?>
        <div class="row align-items-center border-bottom py-2">
            <div class="col-6">
                <span class="text-dark">📦 <?php echo $row['real_package'] ?></span>
            </div>
            <div class="col-6 text-end">
                <span class="text-success fw-bold">GHS <?php echo number_format($row['amount'], 2) ?></span>
            </div>
        </div>
    <?php
            }
        } else {
    ?>
        <div class="text-danger mt-3">🚫 No Console Price found</div>
    <?php
        }
    ?>
</div>

    <?php
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
                
                else{
                    ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <h6>Express your Interest to get CONSOLE. Admin will review your request and respond to you shortly</h6>
                           <div class="row" >
                               <div class="col-sm-4">
                                   <button type="submit" name="new_console" class="btn btn-primary btn-block" >Click to Express Interest</button>
                               </div>
                            </div>
                    </form>
                    <?php
                }
                ?>
         
                <?php
                }
            }
            else{
                ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <h6>Express your Interest to get CONSOLE. Admin will review your request and respond to you shortly</h6>
                           <div class="row" >
                               <div class="col-sm-4">
                                   <button type="submit" name="new_console" class="btn btn-primary btn-block" >Click to Express Interest</button>
                               </div>
                            </div>
                    </form>
				<?php
            }
        ?>
                    

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
<script>
function updateBatteryIcon() {
    let dataUsageText = document.getElementById('data-usage').textContent;
    let [remainingGBText, totalGBText] = dataUsageText.split(" / ");
    let remainingGB = parseFloat(remainingGBText.replace("GB", "").trim());
    let totalGB = parseFloat(totalGBText.replace("GB", "").trim());
    let percentage = (remainingGB / totalGB) * 100;
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
    document.getElementById('battery-icon').innerHTML = iconHTML;
}

function checkRecipientNumbers() {
    const recipientNumber = document.getElementById('atrecipient').value;
    const repeatNumber = document.getElementById('atrepeat').value;
    const atMessage = document.getElementById('atmessage');
    const atMessageGreen = document.getElementById('atmessagegreen');
    const footerAtSend = document.getElementById('footer_at_send');
    if (!pattern.test(recipientNumber)) {
        atMessage.style.display = 'none';
        atMessageGreen.style.display = 'none';
        footerAtSend.style.display = 'none';
    } else if (recipientNumber !== repeatNumber) {
        atMessage.style.display = 'block';
        atMessageGreen.style.display = 'none';
        footerAtSend.style.display = 'none';
    } else {
        atMessage.style.display = 'none';
        atMessageGreen.style.display = 'block';
        footerAtSend.style.display = 'block';
    }
}

// Call updateBatteryIcon to initialize and set it to update at intervals
updateBatteryIcon();
setInterval(updateBatteryIcon, 5000);

// Add event listeners to check recipient numbers on input
document.getElementById('atrecipient').addEventListener('input', checkRecipientNumbers);
document.getElementById('atrepeat').addEventListener('input', checkRecipientNumbers);
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