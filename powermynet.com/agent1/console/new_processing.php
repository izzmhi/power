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

// Process POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['successfullypaid']) && isset($_POST['price'])) {
        $reference_id = htmlspecialchars($_POST['successfullypaid']);
        $package = $_SESSION['package'];
        $real_package = $_SESSION['real_package'];
        $original = $_SESSION['original'];
        $purchase_amount = htmlspecialchars($_POST['price']);
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

        // Validate inputs

    if (empty($purchase_amount)) {
        $error = "There is no price for this console";
        header("Location: console.php?error=$error");
    }
    if (empty($package)) {
        $error = "Console package is not selected";
        header("Location: console.php?error=$error");
    }
    // Proceed if no validation errors
    if (empty($errors)) {
        // Check if console exists with the provided details
        $selectquery = "SELECT * FROM consoles WHERE agent_id='$agent_id'";
        $select = mysqli_query($con, $selectquery);
        if ($select && mysqli_num_rows($select) > 0) {
            $error = "A console for this network exists. You can Renew";
            header("Location: console.php?error=$error");
        } else {
            // Set timezone and dates
            date_default_timezone_set('GMT');
            $order_times = date('H:i:s A');
            $order_dates = date('l, F d, Y');
            $purchase_date = date('Y-m-d');
            $used_package = 0;
            $console_status = "ACTIVE";
            // Check if user has console interests
            $interestQuery = "SELECT * FROM console_interest WHERE agent_id='$agent_id'";
            $interestResult = mysqli_query($con, $interestQuery);
            if (mysqli_num_rows($interestResult) > 0) {
                $row = mysqli_fetch_assoc($interestResult); // Fetch interest data
                $console_id = $row['console_id'];
                $console_name = $row['console_name'];
                // Insert into consoles
                   $stmt = $con->prepare("INSERT INTO consoles (console_id, console_name, agent_id, agent_name, agent_email, agent_tel, package, used_package, purchase_amount, purchase_date, console_status, fixed_package, real_package) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                   $stmt->bind_param("ssssssssdssss", $console_id, $console_name, $agent_id, $agent_name, $agent_email, $agent_tel, $package, $used_package, $purchase_amount, $purchase_date, $console_status, $package, $original);
                   $iquery = $stmt->execute();
                       if ($iquery) {
                        // Insert into console_transactions
                        $stmt = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name, agent_id,agent_name, agent_email, agent_tel, package, trans_date) VALUES (?,?,?,?,?,?,?,?,?)");
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
                          header("Location: console.php?error=$error");
                         }
                  } else {
                  $error = "Failed to purchase console";
                  header("Location: console.php?error=$error");
                }
            }
        }
      }
    }
}
?>