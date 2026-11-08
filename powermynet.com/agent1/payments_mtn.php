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
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
       $_SESSION['wallet'] = $rows['wallet'];
    }
}

$errors = array();
$success = array();

if (isset($_GET['paystackpaid'])) {
    // Sanitize and validate input data
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    $gig = mysqli_real_escape_string($con, $_SESSION['agig']);
    $price = mysqli_real_escape_string($con, $_SESSION['aprice']);
    $network = mysqli_real_escape_string($con, $_SESSION['anetwork']);
    $recipient_number = mysqli_real_escape_string($con, $_SESSION['abeneficiary']);
    $mode = "PayStack";
    $agent_name = $first_name . " " . $last_name;
    $customer_email = "NONE";
    $purpose = "Order Payment";
    // Order details
    $status = "Pending";
    date_default_timezone_set('GMT');
    $order_time = date('H:i:s A');
    $order_date = date('l, F d, Y');
     $real_date = date('Y-m-d');
    $date4 = date('ymd');
    $time = date('his');
    $to = bin2hex(random_bytes(2));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to . $date4 . $tok . $time;
    $reference = mysqli_real_escape_string($con, $_GET['paystackpaid']);
    
    if (!$agent_email || !$recipient_number || !$network || !$gig || !$price) {
        $errors[] = 'Invalid input. Please check your data.';
    } else {
        
                // Get the last five characters
    $last_2_chars = substr($recipient_number, -2);
    $first_five_chars = substr($recipient_number, 0, 5);
    $seconds = date('His');
    $reference_id = $first_five_chars.$seconds.$last_2_chars;
        
        $super_agent_email = "owusuebenezer377@gmail.com";
        $super_agent_name = "EBENEZER MESRENYAME";

            // API data preparation
        $data = [
            'super_agent_name' => $super_agent_name,
            'super_agent_email' => $super_agent_email,
            'agent_id' => $agent_id,
            'agent_name' => $agent_name,
            'agent_tel' => $agent_tel,
            'agent_email' => $agent_email,
            'recipient_number' => $recipient_number,
            'network' => $network,
            'gig' => $gig,
            'price' => $price,
            'customer_email' => $customer_email,
            'reference_id' => $reference_id,
        ];
        $jsonData = json_encode($data);
        require_once __DIR__ . '/../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.dataworksgh.com/api/index.php');
        
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

            if (curl_errno($ch)) {
                $errors[] = "cURL error: " . curl_error($ch);
                curl_close($ch);
                header("Location: orders.php?error=" . urlencode($errors[0]));
                exit();
            } else {
            $response = json_decode($response, true);
            curl_close($ch);

            if (isset($response['message'])) {
                $success_message = $response['message'];

                // Prepare order details
                $status = "Pending";
                date_default_timezone_set('GMT');
                $order_time = date('H:i:s A');
                $order_date = date('l, F d, Y');
                $real_date = date('Y-m-d');
                $order_id = bin2hex(random_bytes(8));
                
                // Insert order into the database using prepared statements
                $stmt = $con->prepare("INSERT INTO orders_agent (order_id, reference_id,agent_id,agent_name,agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status,mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("sssssssssdsssss", $order_id, $reference_id,$agent_id,$agent_name,$agent_tel, $agent_email, $recipient_number, $network, $gig, $price, $order_time, $order_date, $real_date, $status,$mode);
                    if ($stmt->execute()) {
                       header("Location: api_orders.php?success=$success_message");
                       exit();
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: api_orders.php?error=$error");
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: " . $con->error;
                    header("Location: api_orders.php?error=$error");
                }
            } else {
                $error = "API response does not contain a message";
                  header("Location: api_orders.php?error=$error");
               }
            }
        }
    // Close database connection
    $con->close();
    }
?>