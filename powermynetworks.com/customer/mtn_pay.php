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
    $agent_name = "CUSTOMER";
    $agent_tel = "NONE";
    $agent_email = "NONE";
        $mode = "PAYSTACK";
    if (!$customer_email || !$recipient_number || !$network || !$gig || !$price) {
        $error = "Fields are empty";
        header("Location: success.php?error=" . urlencode($error) . "&email=" . urlencode($customer_email));
        exit();
    } else {
        $super_agent_email = "codecraftnetwork1@gmail.com";
        $super_agent_name = "EBENEZER MESRENYAME";
        
                    // Get the last five characters
    $last_2_chars = substr($recipient_number, -2);
    $first_five_chars = substr($recipient_number, 0, 5);
    $seconds = date('His');
    $reference_id = $first_five_chars.$seconds.$last_2_chars;

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
            $error = "cURL error: " . curl_error($ch);
            curl_close($ch);
            echo $error;
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
                $stmt = $con->prepare("INSERT INTO orders_agent(order_id,reference_id,agent_id,agent_name,agent_tel,agent_email,beneficiary,network,gig,price,order_time,order_date,real_date,order_status,mode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("sssssssssdsssss", $reference_id,$reference_id,$agent_id,$agent_name,$agent_tel,$agent_email,$recipient_number,$network,$gig,$price,$order_time,$order_date,$real_date,$status,$mode);
                    if ($stmt->execute()) {
                       header("Location: success.php?success=" . urlencode($success_message) . "&telephone=" . urlencode($recipient_number));
                       exit();
                    } else {
                        $error = "Failed to insert order: " . $stmt->error;
                       header("Location: success.php?error=" . urlencode($error) . "&telephone=" . urlencode($recipient_number));
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare database statement: " . $con->error;
                    header("Location: success.php?error=" . urlencode($error) . "&telephone=" . urlencode($recipient_number));
                }
            } else {
                $error = "API response does not contain a message";
                  header("Location: success.php?error=" . urlencode($error) . "&telephone=" . urlencode($recipient_number));
            }
        }
    }
} else {
    $error = "Network parameter is missing";
    header("Location: success.php?error=" . urlencode($error));
}

?>
