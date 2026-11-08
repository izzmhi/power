<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];
$success = [];

include '../database/dbconfig.php';

if (isset($_GET['paystackpaid'])) {
    // Sanitize and validate input data
      // Sanitize and validate input data
    $gig = mysqli_real_escape_string($con, $_SESSION['gig']);
    $customer_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $real_package = mysqli_real_escape_string($con, $_SESSION['real_package']);
    $price = mysqli_real_escape_string($con, $_SESSION['price']);
    $network = mysqli_real_escape_string($con, $_SESSION['network']);
    $recipient_number = mysqli_real_escape_string($con, $_SESSION['beneficiary']);
    $reference = mysqli_real_escape_string($con, $_GET['paystackpaid']);
    
    $agent_id = "NONE";
    $agent_name = "NONE";
    $agent_tel = "NONE";
    $agent_email = "NONE";
    $mode = "Paystack";

    if (!$customer_email || !$recipient_number || !$network || !$gig || !$price) {
        $error = "Fields are empty";
        header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
        exit();
    } else {
        $super_agent_email = "owusuebenezer377@gmail.com";
        $super_agent_name = "EBENEZER MESRENYAME";

                    // Get the last five characters
               $last_2_chars = substr($recipient_number, -2);
               $first_five_chars = substr($recipient_number, 0, 5);
               $seconds = date('His');
               $reference_id = $first_five_chars.$seconds.$last_2_chars;

                // Prepare order details
                $status = "Pending";
                date_default_timezone_set('GMT');
                $order_time = date('H:i:s A');
                $order_date = date('l, F d, Y');
                $real_date = date('Y-m-d');
                $order_id = bin2hex(random_bytes(8));

                // Insert order into the database using prepared statements
                $stmt = $con->prepare("INSERT INTO special_offers_orders (order_id, reference, reference_id, agent_id, agent_name, agent_tel, agent_email, customer_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status,mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("sssssssssssdsssss", $order_id, $reference,$reference_id,$agent_id, $agent_name, $agent_tel, $agent_email, $customer_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status,$mode);
                    if ($stmt->execute()) {
                        $success = "Order is successful ";
                       header("Location: success_special.php?success=".urlencode($success)."&telephone=".urlencode($recipient_number));
                       exit();
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: success_special.php?error=".urlencode($error)."&telephone=".urlencode($recipient_number));
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: ".$con->error;
                    header("Location: success_special.php?error=" . urlencode($error)."&telephone=".urlencode($recipient_number));
                }
        }
        
    }
 else {
    header("Location: index.php?error=" . urlencode($error));
}

?>
