<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include'../database/dbconfig.php';


if (isset($_GET['rej'])) {
    $user_id = mysqli_real_escape_string($con, $_GET['rej']);
    $status = "REJECTED";
    // Update query
    $updatequery = "UPDATE agents_sms SET request_status='$status' WHERE user_id='$user_id'";
    $uquery = mysqli_query($con, $updatequery);
    // Check if update is successful
    if ($uquery) {
        // If all updates succeed
        $success = "Status Changed Successfully";
        header("Location: sms.php?success=$success");
        exit;
    }
    else{
         $error = "Failed to update package with ID $update_id";
        header("Location: sms.php?error=$error");
        exit(); // Exit the loop if there's an error
    }
}

if (isset($_GET['del'])) {
    $user_id = $_GET['del'];

    // Validate and sanitize input
    $user_id = mysqli_real_escape_string($con, $user_id);

    // First DELETE from agents_sms
    $delete_sms = "DELETE FROM agents_sms WHERE user_id='$user_id'";
    $result_sms = mysqli_query($con, $delete_sms);

    if ($result_sms) {
        // Then DELETE from agents_sms_payments
        $delete_payments = "DELETE FROM agents_sms_payments WHERE user_id='$user_id'";
        $result_payments = mysqli_query($con, $delete_payments);

        if ($result_payments) {
            $success = "SMS SENDER NAME deleted successfully";
            header("Location: sms.php?success=$success");
            exit();
        } else {
            $error = "Failed to delete from sms payments";
            header("Location: sms.php?error=$error");
            exit();
        }
    } else {
        $error = "Failed to delete SMS SENDER name";
        header("Location: sms.php?error=$error");
        exit();
    }
}



<?php
if (isset($_GET['app'])) {
    require_once 'db_connection.php'; // Optional: include your DB connection

    // Sanitize inputs
    $user_id = mysqli_real_escape_string($con, $_GET['agent_id']);
    $agent_name = mysqli_real_escape_string($con, $_GET['agent_name']);
    $telephone = mysqli_real_escape_string($con, $_GET['telephone']);
    $sender_id = mysqli_real_escape_string($con, $_GET['app']);

    // Constants
    $status = "APPROVED";
    $pay_status = "NOT PAID";
    $amount = 0.00;
    $pay_date = "NONE";
    $start_year = 2025;
    $end_year = 2026;

    // Step 1: Update status in agents_sms
    $updatequery = "UPDATE agents_sms SET request_status='$status' WHERE sender_id='$sender_id'";
    $uquery = mysqli_query($con, $updatequery);

    if ($uquery) {
        // Step 2a: July to December 2025
        for ($month = 7; $month <= 12; $month++) {
            $month_name = date('F', mktime(0, 0, 0, $month, 1, $start_year));
            $month_real = str_pad($month, 2, '0', STR_PAD_LEFT);

            $insert = $con->prepare("INSERT INTO agents_sms_payments (user_id, agent_name, telephone, sender_id, status, month, months, year, pay_date, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($insert) {
                $insert->bind_param("ssssssssss", $user_id, $agent_name, $telephone, $sender_id, $pay_status, $month_name, $month_real, $start_year, $pay_date, $amount);
                if (!$insert->execute()) {
                    $error = "Failed to insert data for $month_name $start_year";
                    header("Location: sms.php?error=$error");
                    exit;
                }
            } else {
                $error = "Failed to prepare insert statement for $month_name $start_year";
                header("Location: sms.php?error=$error");
                exit;
            }
        }

        // Step 2b: January to December 2026
        for ($month = 1; $month <= 12; $month++) {
            $month_name = date('F', mktime(0, 0, 0, $month, 1, $end_year));
            $month_real = str_pad($month, 2, '0', STR_PAD_LEFT);

            $insert = $con->prepare("INSERT INTO agents_sms_payments (user_id, agent_name, telephone, sender_id, status, month, months, year, pay_date, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($insert) {
                $insert->bind_param("ssssssssss", $user_id, $agent_name, $telephone, $sender_id, $pay_status, $month_name, $month_real, $end_year, $pay_date, $amount);
                if (!$insert->execute()) {
                    $error = "Failed to insert data for $month_name $end_year";
                    header("Location: sms.php?error=$error");
                    exit;
                }
            } else {
                $error = "Failed to prepare insert statement for $month_name $end_year";
                header("Location: sms.php?error=$error");
                exit;
            }
        }

        // Success: Redirect with success message
        $success = "Status changed and payments generated successfully.";
        header("Location: sms.php?success=$success");
        exit;
    } else {
        // Update failed
        $error = "Failed to approve SMS sender ID.";
        header("Location: sms.php?error=$error");
        exit;
    }
}
?>




?>
