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

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

include'../database/dbconfig.php';

$success = array();
$errors = array();

if(isset($_POST['approve'])){
    $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
    $status = "APPROVED";
    $update = "update console_interest set console_status='$status' where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "APPROVED | CONSOLE";
                    $mail->Body = "
                    <html>
                    <body>
                      <p>Dear Agent,</p>
                        <p> Your CONSOLE account request is APPROVED. Contact Admin to LOAD your CONSOLE.</p>
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
                    </body>
                    </html>";
                    $mail->send();
                    $success="Console request approved successfully";
                            header("Location: console.php?success=$success");
                            exit();
                    } catch (Exception $e) {
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    }
           }
    else{
      $error="Failed to approve a console";
                                  header("Location: console.php?error=$error");
                            exit();
    }
    
}


if(isset($_POST['reject'])){
 $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
    $status = "REJECTED";
    $update = "update console_interest set console_status='$status' where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
            $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "REJECTED | CONSOLE";
                    $mail->Body = "
                    <html>
                    <body>
                      <p>Dear Agent,</p>
                        <p> Your CONSOLE account request is REJECTED. Contact Admin on what to do next.</p>
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
                    </body>
                    </html>";
                    $mail->send();
                   $success="Console request rejected successfully";
                            header("Location: console.php?success=$success");
                            exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
            else{
            $error="Failed to reject a console";
                    header("Location: console.php?error=$error");
                    exit();
        }
    }
    
    if(isset($_POST['delete'])){
    $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);

    $update = "delete FROM console_interest where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $mail = new PHPMailer(true);
        try {
            // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
            $mail->addAddress($agent_email);
            // Content
            $mail->isHTML(true);
            $mail->Subject = "CANCELLED | CONSOLE";
            $mail->Body = "
            <html>
            <body>
              <p>Dear Agent,</p>
                <p> Your CONSOLE account request is CANCELLED. Contact Admin on what to do next.</p>
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
            </body>
            </html>";
            $mail->send();
            $success="Console request deleted successfully";
                    header("Location: console.php?success=$success");
                    exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

      }
    else{
      $error="Failed to cancel a console";
        header("Location: console.php?error=$error");
        exit();
    }
}

if(isset($_POST['block'])){
    $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
    $status = "BLOCKED";

    $update = "update consoles set console_status='$status' where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        
        $mail = new PHPMailer(true);
        try {
            // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
            $mail->addAddress($agent_email);
            // Content
            $mail->isHTML(true);
            $mail->Subject = "BLOCKED | CONSOLE";
            $mail->Body = "
            <html>
            <body>
              <p>Dear Agent,</p>
            <p> Your CONSOLE account is BLOCKED. Contact Admin on what to do next.</p>
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
                    </body>
                    </html>";
                    $mail->send();
                    $success="Console blocked successfully";
                    header("Location: console.php?success=$success");
                    exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

      }
    else{
      $error="Failed to block a console";
    header("Location: console.php?error=$error");
    exit();
    }
    
}



if(isset($_POST['cancel'])){
 $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
    $status = "CANCELLED";
    $update = "delete FROM consoles where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $mail = new PHPMailer(true);
        try {
            // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
            $mail->addAddress($agent_email);
            // Content
            $mail->isHTML(true);
            $mail->Subject = "CANCELLED | CONSOLE";
            $mail->Body = "
            <html>
            <body>
              <p>Dear Agent,</p>
                <p> Your CONSOLE account is CANCELLED. Contact Admin on what to do next.</p>
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
            </body>
            </html>";
            $mail->send();
            $success="Console cancelled successfully";
                header("Location: console.php?success=$success");
                exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

      }
    else{
      $error="Failed to cancel a console";
    header("Location: console.php?error=$error");
    exit();
    }
}
    
    
if(isset($_POST['activated'])){
    $c_id = mysqli_real_escape_string($con, $_POST['c_id']);
    $console_id = mysqli_real_escape_string($con, $_POST['console_id']);
    $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
    $status = "ACTIVE";
    $update = "update consoles set console_status='$status' where id = $c_id AND console_id= '$console_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "ACTIVATED | CONSOLE";
                    $mail->Body = "
                    <html>
                    <body>
                      <p>Dear Agent,</p>
                        <p> Your CONSOLE account is ACTIVATED. Contact Admin on what to do next.</p>
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
                    </body>
                    </html>";
                    $mail->send();
                      $success="Console activated successfully";
                        header("Location: console.php?success=$success");
                    exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        else{
        $error="Failed to activate a console";
        header("Location: console.php?error=$error");
        exit();
    }
}
    
    
    
    
if (isset($_POST['load'])) {
    // Sanitize and validate input data
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $agent_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $package = mysqli_real_escape_string($con, $_POST['package']);
        // Validation
    if (empty($agent_id)) {
        header("Location: console.php?error=Select an Agent");
        exit();
    }
    if (empty($package)) {
        header("Location: console.php?error=Select a Package");
        exit();
    }
    else{
    // Set timezone and dates
    date_default_timezone_set('GMT');
    $order_times = date('H:i:s A');
    $order_dates = date('l, F d, Y');
    $console_status = "ACTIVE";
    $purchase_date = date('Y-m-d');
    $date4 = date('dmY');
    $time = date('hms');
    $to = bin2hex(random_bytes(2));
    $tok = bin2hex(random_bytes(2));
    $reference_id = $to . $date4 . $tok . $time;
    $used_package = 0;   
    $trans_desc = "LOAD";

    // Check if package exists
    $selects = $con->prepare("SELECT * FROM console_prices WHERE package=?");
    $selects->bind_param("s", $package);
    $selects->execute();
    $sqls = $selects->get_result();
    if ($sqls->num_rows > 0) {
        $rows = $sqls->fetch_assoc();
        $amount = $rows['amount'];
        $original = $rows['original'];

        // Check if user has console interest
        $select = $con->prepare("SELECT * FROM console_interest WHERE agent_id=?");
        $select->bind_param("s", $agent_id);
        $select->execute();
        $result = $select->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $console_id = $row['console_id'];
            $console_name = $row['console_name'];
            $agent_name = $row['agent_name'];
            $agent_email = $row['agent_email'];
            $agent_tel = $row['agent_tel'];

            // Check if console already exists
            $select2 = $con->prepare("SELECT * FROM consoles WHERE console_id=?");
            $select2->bind_param("s", $console_id);
            $select2->execute();
            $result2 = $select2->get_result();

            if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $console_package = $row2['package'];
            $console_real_package = $row2['real_package'];
            $new_package = $console_package + $package;
            $new_real_package = $console_real_package + $original;
            // Update console data
            $update = $con->prepare("UPDATE consoles SET package = ?, fixed_package = ?, real_package = ?, purchase_amount = ?, purchase_date = ?, used_package = ? WHERE agent_id = ?");
                            $update->bind_param("sssdsss", $new_package, $new_package, $new_real_package, $amount, $purchase_date, $used_package, $agent_id);
            $update_success = $update->execute();
            if ($update_success) {
            // Insert into console_transactions
            $stmt_trans = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date, trans_desc) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt_trans->bind_param("ssssssssss", $reference_id,$console_id, $console_name, $agent_id,$agent_name, $agent_email, $agent_tel, $package, $purchase_date, $trans_desc);
            if ($stmt_trans->execute()) {
                $mail = new PHPMailer(true);
                try {
                // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Console Renew | CONSOLE";
                    $mail->Body = "
                    <html>
                    <body>
                    <p>Dear Agent,</p>
                        <p>You have successfully renewed your console with $console_real_package MB worth GHS$amount.</p>
                        <p>Best regards,<br>CODECRAFTNETWORK</p>
                    </body>
                    </html>";
                    $mail->send();
                    $success = "Console Renewal Successful";
                    header("Location: console.php?success=$success");
                     exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

                    } else {
                        header("Location: console.php?error=Failed to add to console transaction");
                        exit();
                    }
                    
                } else {
                    header("Location: console.php?error=Console renewal failed");
                    exit();
                }
            } 
            

            else {
                $stmt = $con->prepare("INSERT INTO consoles (console_id, console_name,agent_id,agent_name,agent_email,agent_tel,package,used_package,purchase_amount,purchase_date,console_status,fixed_package,real_package) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssssssdssss", $console_id, $console_name, $agent_id, $agent_name, $agent_email, $agent_tel, $package, $used_package, $amount, $purchase_date, $console_status, $package, $original);
                if ($stmt->execute()) {
                    // Insert into console_transactions
                    $stmt_trans = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name, agent_id,agent_name, agent_email, agent_tel, package, trans_date, trans_desc) VALUES (?,?,?,?,?,?,?,?,?,?)");
                    $stmt_trans->bind_param("ssssssssss", $reference_id, $console_id, $console_name, $agent_id, $agent_name, $agent_email, $agent_tel, $package, $purchase_date, $trans_desc);

                    if ($stmt_trans->execute()) {
                        $mail = new PHPMailer(true);
                        try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'codecraftnetwork.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'noreply@codecraftnetwork.com';
                        $mail->Password = 'REDACTED';
                        $mail->SMTPSecure = "ssl";
                        $mail->Port = 465;
                        // Recipients
                        $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
                        $mail->addAddress($agent_email);
                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = "Console Purchase | CONSOLE";
                        $mail->Body = "
                        <html>
                        <body>
                        <p>Dear Agent,</p>
                            <p>You have successfully purchased a $console_real_package MB Console worth GHS$amount</p>
                            <p>Best regards,<br>CODECRAFTNETWORK</p>
                        </body>
                        </html>";
                        $mail->send();
                        $success = "Console Purchase successful";
                        header("Location: console.php?success=$success");
                        exit();
                            } catch (Exception $e) {
                                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                            }
                    } else {
                        header("Location: console.php?error=Failed to add to console transaction");
                        exit();
                    }
                } else {
                    header("Location: console.php?error=Failed to purchase console");
                    exit();
                }
            }
        } else {
            header("Location: console.php?error=Agent has not expressed interest in getting a console");
            exit();
        }
    } else {
        header("Location: console.php?error=Console Package Price not found");
        exit();
    }
}
}
function generateReferenceId($agent_tel) {
    $last_2_chars = substr($agent_tel, -2);
    $first_five_chars = substr($agent_tel, 0, 5);
    $seconds = date('His');
    return $first_five_chars . $seconds . $last_2_chars;
}



if (isset($_POST['deduct'])) {
    // Sanitize and validate input data
    $agent_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
        // Validation
    if (empty($agent_id)) {
        header("Location: console.php?error=Select an Agent");
        exit();
    }
    if (empty($amount)) {
        header("Location: console.php?error=Enter an Amount");
        exit();
    }
    else{
    // Set timezone and dates
    date_default_timezone_set('GMT');
    $order_times = date('H:i:s A');
    $order_dates = date('l, F d, Y');
    $console_status = "ACTIVE";
    $purchase_date = date('Y-m-d');
    $date4 = date('dmY');
    $time = date('hms');
    $to = bin2hex(random_bytes(2));
    $tok = bin2hex(random_bytes(2));
    $reference_id = $to . $date4 . $tok . $time;
    $trans_desc = "DEBIT";
        // Check if console already exists
        $select2 = $con->prepare("SELECT * FROM consoles WHERE agent_id=?");
        $select2->bind_param("s", $agent_id);
        $select2->execute();
        $result2 = $select2->get_result();
        if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $console_package = $row2['package'];
            $console_real_package = $row2['real_package'];
            $new_package = $console_package - $package;
            $new_real_package = $console_real_package - $original;
            // Update console data
            $update = $con->prepare("UPDATE consoles SET package = ?, fixed_package = ?, real_package = ?, purchase_amount = ?, purchase_date = ? = ? WHERE agent_id = ?");
                            $update->bind_param("sssdsss", $new_package, $new_package, $new_real_package, $amount, $purchase_date, $agent_id);
            $update_success = $update->execute();
            if ($update_success) {
            // Insert into console_transactions
            $stmt_trans = $con->prepare("INSERT INTO console_transactions (trans_id,console_id, console_name,agent_id,agent_name, agent_email, agent_tel, package, trans_date, trans_desc) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt_trans->bind_param("ssssssssss", $reference_id,$console_id, $console_name, $agent_id,$agent_name, $agent_email, $agent_tel, $package, $purchase_date, $trans_desc);
            if ($stmt_trans->execute()) {
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CodeCraftNetwork');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Console Debit | CONSOLE";
                    $mail->Body = "
                    <html>
                    <body>
                    <p>Dear Agent,</p>
                        <p>Deduction made successfully</p>
                        <p>Best regards,<br>CODECRAFTNETWORK</p>
                    </body>
                    </html>";
                    $mail->send();
                    $success = "Console Renewal Successful";
                    header("Location: console.php?success=$success");
                     exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

                    } else {
                        header("Location: console.php?error=Failed to add to console transaction");
                        exit();
                    }
                    
                } else {
                    header("Location: console.php?error=Console renewal failed");
                    exit();
                }
            } 
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
			<?php $page = 'console' ?>
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
				
					<!-- Modal Load-->
<div class="modal fade" id="mtnBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Manual Console Load</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body">
            <div class="mb-3">
               <select name="agent_id" class="form-control">
                    <option value="">Select Agent</option>
                    <?php
                    include '../database/dbconfig.php';
                    $status = "APPROVED";
                    $select = "SELECT * FROM console_interest WHERE console_status='$status' ORDER BY agent_name ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['agent_id']; ?>"><?php echo $row['agent_name'] . " - " . $row['console_name']; ?></option>
                        <?php
                    }
                    } else {
                    ?>
                    <option value="">No Agent is Available</option>
                    <?php
                    }
                    ?>
                    </select>
            </div>

                   <select name="package" class="form-control">
                    <option value="">Select Console Package</option>
                    <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT * FROM console_prices ORDER BY real_package";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['package']; ?>"><?php echo $row['real_package'] ?></option>
                        <?php
                    }
                    } else {
                    ?>
                    <option value="">No Console Price found</option>
                    <?php
                    }
                    ?>
                    </select>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_console" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="load" class="btn btn-block btn-primary" id="consoleBtn">Submit</button>
    </div>
</form>

    </div>
  </div>
</div>

					<!-- Modal Deduct-->
<div class="modal fade" id="deductModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Console Deduction</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body">
            <div class="mb-3">
               <select name="agent_id" class="form-control">
                    <option value="">Select Agent</option>
                    <?php
                    include '../database/dbconfig.php';
                    $status = "APPROVED";
                    $select = "SELECT * FROM consoles WHERE console_status='$status' ORDER BY agent_name ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['agent_id']; ?>"><?php echo $row['agent_name'] . " - " . $row['console_name']; ?></option>
                        <?php
                    }
                    } else {
                    ?>
                    <option value="">No Agent is Available</option>
                    <?php
                    }
                    ?>
                    </select>
            </div>
            
            			<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Enter Amount</label>
									<input type="text" class="form-control" name="amount" required>
								</div>
							</div>
						</div>


        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_console" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="deduct" class="btn btn-block btn-primary" id="consoleBtn">Submit</button>
    </div>
</form>

    </div>
  </div>
</div>

	<!--Reject-->
		<div class="modal fade" id="modalRejectForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Reject Console Request</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

					<input type="hidden" class="form-control" id="r_id" name="c_id" readonly required>
	                <input type="hidden" class="form-control" id="r_console_id" name="console_id" readonly required>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="r_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="r_agent_email" name="agent_email" readonly>
								</div>
							</div>
						</div>
						
						<hr>
						  <div class="row px-2">
						      
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
							    <div class="spinner" id="spinner_delete" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="reject" class="btn btn-success btn-block" id="deleteBtn">Reject</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>

	<!--Delete Request-->
		<div class="modal fade" id="modalDeleteForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Delete Console Request</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

					<input type="hidden" class="form-control" id="d_id" name="c_id" readonly required>
	                <input type="hidden" class="form-control" id="d_console_id" name="console_id" readonly required>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="d_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="d_agent_email" name="agent_email" readonly>
								</div>
							</div>
						</div>
						
						<hr>
						  <div class="row px-2">
						      
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
							    <div class="spinner" id="spinner_delete" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="delete" class="btn btn-success btn-block" id="deleteBtn">Delete</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
		
		<!--Approve-->
		<div class="modal fade" id="modalApproveForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Console Approval</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
	                
					<input type="hidden" class="form-control" id="a_id" name="c_id" readonly required>
	                <input type="hidden" class="form-control" id="a_console_id" name="console_id" readonly required>
						
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="a_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="a_agent_email" name="agent_email" readonly>
								</div>
							</div>
						</div>
	
						<hr>
						  <div class="row px-2">
						     
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
							     <div class="spinner" id="spinner_new" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="approve" class="btn btn-success btn-block" id="submitBtn">Approve</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
			
			<!--Block-->
		<div class="modal fade" id="modalBlockForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Block Console</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

							<input type="hidden" class="form-control" id="b_id" name="c_id" readonly required>
	                        <input type="hidden" class="form-control" id="b_console_id" name="console_id" readonly required>

						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="b_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="b_agent_email" name="agent_email" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
										<input type="text" class="form-control" id="b_agent_tel" name="agent_tel" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Remaining Package:</label>
										<input type="text" class="form-control" id="b_package_last" name="package_last" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Amount:</label>
										<input type="text" class="form-control" id="b_purchase_amount" name="purchase_amount" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Date:</label>
										<input type="text" class="form-control" id="b_purchase_date" name="purchase_date" readonly>
								</div>
							</div>
						</div>
						
						<hr>
						  <div class="row px-2">
							<div class="col-sm-12 col-12">
							    <div class="spinner" id="spinner_delete" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="block" class="btn btn-success btn-block" id="deleteBtn">Block</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
		
		<!--Cancel-->
			<div class="modal fade" id="modalCancelForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Cancel and Delete Console</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
					<input type="hidden" class="form-control" id="c_id" name="c_id" readonly required>
	                <input type="hidden" class="form-control" id="c_console_id" name="console_id" readonly required>

						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="c_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="c_agent_email" name="agent_email" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
										<input type="text" class="form-control" id="c_agent_tel" name="agent_tel" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Remaining Package:</label>
										<input type="text" class="form-control" id="c_package_last" name="package_last" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Amount:</label>
										<input type="text" class="form-control" id="c_purchase_amount" name="purchase_amount" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Date:</label>
										<input type="text" class="form-control" id="c_purchase_date" name="purchase_date" readonly>
								</div>
							</div>
						</div>
	
						<hr>
						  <div class="row px-2">
							<div class="col-sm-12 col-12">
							     <div class="spinner" id="spinner_new" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="cancel" class="btn btn-success btn-block" id="submitBtn">Cancel</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
			
					<!--Activated-->
			<div class="modal fade" id="modalActivateForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Activate a Console</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
					<input type="hidden" class="form-control" id="ac_id" name="c_id" readonly required>
	                <input type="hidden" class="form-control" id="ac_console_id" name="console_id" readonly required>
	
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent:</label>
										<input type="text" class="form-control" id="ac_agent_name" name="agent_name" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="ac_agent_email" name="agent_email" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
										<input type="text" class="form-control" id="ac_agent_tel" name="agent_tel" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Remaining Package:</label>
										<input type="text" class="form-control" id="ac_package_last" name="package_last" readonly>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Amount:</label>
										<input type="text" class="form-control" id="ac_purchase_amount" name="purchase_amount" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Purchase Date:</label>
										<input type="text" class="form-control" id="ac_purchase_date" name="purchase_date" readonly>
								</div>
							</div>
						</div>
	
						<hr>
						  <div class="row px-2">
							<div class="col-sm-12 col-12">
							     <div class="spinner" id="spinner_new" style="display: none;">
                               <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                               Please Wait...
                              </div>
								<div class="form-group">
									<button type="submit" name="activated" class="btn btn-success btn-block" id="submitBtn">Activate</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>

				<!-- Main container start -->
				<div class="main-container">
				    
		    <div class="row mb-1">
                <div class="col-lg-4 col-4">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#mtnBuyModal">Buy/Load Console</button>
                </div>
                <div class="col-lg-4 col-4">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#deductModal">Console Deduction</button>
                </div>
            </div>
            <br>
            <a href="console_orders.php" class="btn btn-info">Console Orders</a>
            <a href="console_transactions.php" class="btn btn-info">Console Transactions</a>
            <a href="console_pricing.php" class="btn btn-info">Console Pricing</a>

            <br>
				    
                   <h5>Consoles</h5>
					<!-- Row start -->
					
					             <?php if(count($success)>0): ?>
                                   <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                   <?php foreach($success as $succes): ?>
                                   <div><?php echo $succes; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 
					
					<?php
					if(isset($_GET['success'])){
					    ?>
					    <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
					    <?php
					}
					else if(isset($_GET['error'])){
					    ?>
					    <div class="alert alert-danger" role="alert" style="border-left:5px solid red"><?php echo $_GET['error']; ?> </div>
					    <?php
					}
					?>
<style>
    .nav-block {
    display: block;
    width: 50%;  /* Each nav will take up half of the row */
    text-align: center;
    box-sizing: border-box; /* Ensures padding is included in width */
}

.nav-tabs {
    display: flex;
    width: 100%;
}

.nav-tabs .nav-item {
    flex-grow: 1; /* Each nav will expand to fill the available space */
}
</style>					
<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-agent-tab" data-toggle="tab" href="#nav-agent" role="tab" aria-controls="nav-agent" aria-selected="true">Console Requests</a>
    <a class="nav-item nav-link" id="nav-customers-tab" data-toggle="tab" href="#nav-customers" role="tab" aria-controls="nav-customers" aria-selected="false">Consoles</a>
  </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <!--Requested consoles-->
    <div class="tab-pane fade show active" id="nav-agent" role="tabpanel" aria-labelledby="nav-agent-tab">
    	<div class="row gutters">
			<div class="col-lg-12 col-sm-12 col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Requested Consoles</h2>
					</div>
					<div class="card-body">
                        <div class="tab-content" id="myTabContent">
    
  					<!-- Row start -->
				<div class="row">
					<div class="col-xl-12 col-sm-12 col-12">
						<div class="table-responsive">
							<table id="basicExample" class="table table-sm">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Agent name</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
        include '../database/dbconfig.php';
        $select = "SELECT * FROM console_interest ORDER BY console_status, agent_name ASC";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        $rowNumber = 1;
        if ($check) {
            while ($row = mysqli_fetch_array($sql)) {
    ?>
   <tr>
    <th scope="row"><?php echo $rowNumber; ?></th>
    <td style="text-align:left;display:none;"><?php echo $row['id']; ?></td>
    <td style="text-align:left;display:none;"><?php echo $row['console_id']; ?></td>
    <td style="text-align:left;"><?php echo $row['agent_name']; ?></td>
    <td style="text-align:left;display:none;"><?php echo $row['agent_email']; ?></td>
    <td style="text-align:left;"><?php echo $row['register_date']; ?></td>
    <!-- Status check and display with correct classes -->
    <?php if ($row['console_status'] == "APPROVED") { ?>
        <td style="text-align:left;" class="bg-success text-white"><?php echo $row['console_status']; ?></td>
    <?php } elseif ($row['console_status'] == "PENDING") { ?>
        <td style="text-align:left;" class="bg-warning text-white"><?php echo $row['console_status']; ?></td>
    <?php } elseif ($row['console_status'] == "REJECTED") { ?>
        <td style="text-align:left;" class="bg-danger text-white"><?php echo $row['console_status']; ?></td>
    <?php } ?>
    <td style="text-align:left;">
        <button type="button" style="font-size:11px;" class="btn btn-info btn-sm approvebtn">
            <i class="fa fa-pencil-square" aria-hidden="true"></i> Approve
        </button>
        <button type="button" style="font-size:11px;" class="btn btn-warning btn-sm rejectbtn">
            <i class="fa fa-trash-o" aria-hidden="true"></i> Reject
        </button>
        <button type="button" style="font-size:11px;" class="btn btn-danger btn-sm deletebtn">
            <i class="fa fa-trash-o" aria-hidden="true"></i> Delete
        </button>
    </td>
</tr>
    <?php
                $rowNumber++;
            }
        } else {
    ?>
        <tr>
            <td colspan="8" style="text-align:center; font-weight:bolder">No Console Request</td>
        </tr>
    <?php
        }
    ?>
    </tbody>
</table>

								</div>
						
				</div>
			</div>
       
                        </div>

   				    </div>
				</div>
			</div>
		</div>
    </div>
  
  <!--Consoles-->
    <div class="tab-pane fade" id="nav-customers" role="tabpanel" aria-labelledby="nav-customers-tab">
    	<div class="row gutters">
			<div class="col-lg-12 col-sm-12 col-12">
				<div class="card h-100">
					<div class="card-header">
						<div class="card-title">Purchased Consoles</div>
					</div>
					<div class="card-body">
   
   					<!-- Row start -->
				<div class="row gutters ">
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
								<div class="table-container ">
							   	  <div class="table-responsive">
									<table id="basicExample" class="table custom-table table-sm">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Agent</th>
            <th>Package</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
        include '../database/dbconfig.php';
        $select = "SELECT * FROM consoles ORDER BY id DESC";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        $rowNumber = 1;
        if ($check) {
            while ($row = mysqli_fetch_array($sql)) {
    ?>
        <tr>
            <th scope="row"><?php echo $rowNumber; ?></th>
            <td style="text-align:left;display:none;"><?php echo $row['id']; ?></td>
            <td style="text-align:left;display:none;"><?php echo $row['console_id']; ?></td>
            <td style="text-align:left;"><?php echo $row['agent_name']; ?></td>
            <td style="text-align:left;display:none;"><?php echo $row['agent_tel']; ?></td>
            <td style="text-align:left;display:none;"><?php echo $row['agent_email']; ?></td>
            <td style="text-align:left;"><?php echo $row['package']; ?></td>
            <td style="text-align:left;"><?php echo $row['purchase_amount']; ?></td>
            <td style="text-align:left;"><?php echo $row['purchase_date']; ?></td>
            <?php if ($row['console_status'] == "ACTIVE") { ?>
                <td style="text-align:left;" class="bg-success text-white"><?php echo $row['console_status']; ?></td>
            <?php } else if ($row['console_status'] == "BLOCKED") { ?>
                <td style="text-align:left;" class="bg-warning text-white"><?php echo $row['console_status']; ?></td>
            <?php } else if ($row['console_status'] == "CANCELLED") { ?>
                <td style="text-align:left;" class="bg-danger text-white"><?php echo $row['console_status']; ?></td>
            <?php } ?>
            <td style="text-align:left;">
                <button type="button" style="font-size:11px;" class="btn btn-info btn-sm blockbtn"><i class="fa fa-pencil-square" aria-hidden="true"></i> Block</button>
                <button type="button" style="font-size:11px;" class="btn btn-danger btn-sm cancelbtn"><i class="fa fa-trash-o" aria-hidden="true"></i> Cancel</button>
                <button type="button" style="font-size:11px;" class="btn btn-success btn-sm activatebtn"><i class="fa fa-trash-o" aria-hidden="true"></i> Activate</button>
            </td>
        </tr>
    <?php
                $rowNumber++;
            }
        } else {
    ?>
        <tr>
            <td colspan="8" style="text-align:center; font-weight:bolder">No Console Request</td>
        </tr>
    <?php
        }
    ?>
    </tbody>
</table>

								</div>
							</div>
						</div>

					</div>
				</div>
			</div>

					</div>
				</div>
			</div>
		</div>
    </div>
  
  
  
</div>			
					
			</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
    $(document).ready(function() {
    $('#consoleBtn').on('click', function() {
    // Show the spinner
    $('#spinner_console').show();
    });
    });
    </script>
		
	<script>
    $(document).ready(function() {
    $('#setBtn').on('click', function() {
    // Show the spinner
    $('#spinner_mtn').show();
    });
    });
    </script>
    
     <script>
    $(document).ready(function() {
    $('#mtnupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updatemtn').show();
    });
    });
    </script>
    
    	<script>
    $(document).ready(function() {
    $('#atupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updateat').show();
    });
    });
    </script>
    
     <script>
    $(document).ready(function() {
    $('#telecelupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updatetelecel').show();
    });
    });
    </script>
		

   <script>
        $(document).ready(function () {
            $('.approvebtn').on('click', function () {
                $('#modalApproveForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#a_id').val(data[0]);
                $('#a_console_id').val(data[1]);
                $('#a_agent_name').val(data[2]);
                $('#a_agent_email').val(data[3]);
            });
        });
    </script>

  <script>
        $(document).ready(function () {
            $('.rejectbtn').on('click', function () {
                $('#modalRejectForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#r_id').val(data[0]);
                $('#r_console_id').val(data[1]);
                $('#r_agent_name').val(data[2]);
                $('#r_agent_email').val(data[3]);
            });
        });
    </script>
    
      <script>
        $(document).ready(function () {
            $('.deletebtn').on('click', function () {
                $('#modalDeleteForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#d_id').val(data[0]);
                $('#d_console_id').val(data[1]);
                $('#d_agent_name').val(data[2]);
                $('#d_agent_email').val(data[3]);
            });
        });
    </script>
    
       <script>
        $(document).ready(function () {
            $('.blockbtn').on('click', function () {
                $('#modalBlockForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#b_id').val(data[0]);
                $('#b_console_id').val(data[1]);
                $('#b_agent_name').val(data[2]);
                $('#b_agent_tel').val(data[3]);
                $('#b_agent_email').val(data[4]);
                $('#b_package_last').val(data[5]);
                $('#b_purchase_amount').val(data[6]);
                $('#b_purchase_date').val(data[7]);
            });
        });
    </script>

  <script>
        $(document).ready(function () {
            $('.cancelbtn').on('click', function () {
                $('#modalCancelForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#c_id').val(data[0]);
                $('#c_console_id').val(data[1]);
                $('#c_agent_name').val(data[2]);
                $('#c_agent_tel').val(data[3]);
                $('#c_agent_email').val(data[4]);
                $('#c_package_last').val(data[5]);
                $('#c_purchase_amount').val(data[6]);
                $('#c_purchase_date').val(data[7]);
            });
        });
    </script>
    
      <script>
        $(document).ready(function () {
            $('.activatebtn').on('click', function () {
                $('#modalActivateForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#ac_id').val(data[0]);
                $('#ac_console_id').val(data[1]);
                $('#ac_agent_name').val(data[2]);
                $('#ac_agent_tel').val(data[3]);
                $('#ac_agent_email').val(data[4]);
                $('#ac_package_last').val(data[5]);
                $('#ac_purchase_amount').val(data[6]);
                $('#ac_purchase_date').val(data[7]);
            });
        });
    </script>
    
<!--spinner start-->
     <style>
    .modal-body {
    position: relative; /* Allows absolute positioning of spinner within this container */
}

/* Spinner overlay styling */
.loading-overlay {
    display: none; /* Hide by default */
    position: absolute; /* Position it absolutely within the modal body */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
    z-index: 999; /* Ensure it is above other content */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner styling */
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

/* Loading text styling */
.loading-text {
    margin-top: 10px;
    font-size: 18px;
    color: #007bff;
}
    </style>

		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>
		<!-- Daterange -->
		<script src="vendor/daterange/daterange.js"></script>
		<script src="vendor/daterange/custom-daterange.js"></script>
		<!-- Chartist JS -->
		<script src="vendor/chartist/js/chartist.min.js"></script>
		<script src="vendor/chartist/js/chartist-tooltip.js"></script>
		<script src="vendor/chartist/js/custom/threshold/threshold.js"></script>
		<script src="vendor/chartist/js/custom/bar/bar-chart-orders.js"></script>
		<!-- jVector Maps -->
		<script src="vendor/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
		<script src="vendor/jvectormap/world-mill-en.js"></script>
		<script src="vendor/jvectormap/gdp-data.js"></script>
		<script src="vendor/jvectormap/custom/world-map-markers2.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>