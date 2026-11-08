<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if (isset($_POST['submit_amount'])) {
    $amount = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $telephone = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;

    if (empty($amount)) {
            $error = "Amount is required";
            header("Location: wallet.php?error=$error");
            exit();
    } elseif ($amount < 1) { 
            $error = "Top-up amount should not be less than 10 GHS.";
            header("Location: wallet.php?error=$error");
            exit();
    }

    if (empty($errors)) { // Proceed only if there are no errors
        $status = "Incomplete";
        $first = "VT";
        $last_2_chars = substr($telephone, -2);
        $first_five_chars = substr($telephone, 0, 5);
        $seconds = date('His');
        $to = bin2hex(random_bytes(2));
        $reference_id = $first . $first_five_chars . $seconds . $to . $last_2_chars;

        // heck if reference_id already exists
        $check_stmt = $con->prepare("SELECT reference_id FROM payments WHERE reference_id = ?");
        $check_stmt->bind_param("s", $reference_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Reference ID already exists, regenerate a new one
            do {
                $seconds = date('His'); // Refresh timestamp
                $to = bin2hex(random_bytes(2)); // Regenerate unique part
                $tok = bin2hex(random_bytes(2)); // Regenerate unique part
                $reference_id = $first . $first_five_chars . $seconds . $to . $tok;

                // Check again
                $check_stmt->bind_param("s", $reference_id);
                $check_stmt->execute();
                $check_stmt->store_result();
            } while ($check_stmt->num_rows > 0);
        }
        $check_stmt->close();

        // sert after ensuring uniqueness
        $trans_date = date("Y-m-d H:i:s");
        $purpose = "Wallet Top-Up";

        $stmt = $con->prepare("INSERT INTO payments (reference_id, user_id, agent_name, email, amount, trans_date, purpose, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdsss", $reference_id, $agent_id, $agent_name, $email, $amount, $trans_date, $purpose, $status);
        $iquery = $stmt->execute();

        if ($iquery) {
            $_SESSION['reference_id'] = $reference_id;
            $_SESSION['amount'] = $amount;
           
            header("Location: wallet.php");
            exit();
        } else {
            $error = "Processing failed";
            header("Location: wallet.php?error=$error");
            exit();
        }
    }
}

?>