<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include'../database/dbconfig.php';


if (isset($_GET['rej'])) {
    $sender_id = mysqli_real_escape_string($con, $_GET['rej']);
    $status = "REJECTED";
    // Update query
    $updatequery = "UPDATE agents_sms SET request_status='$status' WHERE sender_id='$sender_id'";
    $uquery = mysqli_query($con, $updatequery);
    // Check if update is successful
    if ($uquery) {
        // If all updates succeed
        $success = "Status Changed Successfully";
        header("Location: ussd.php?success=$success");
        exit;
    }
    else{
         $error = "Failed to update package with ID $update_id";
        header("Location: ussd.php?error=$error");
        exit(); // Exit the loop if there's an error
    }
}
   


if (isset($_GET['app'])) {
    $user_id = mysqli_real_escape_string($con, $_GET['agent_id']);
    $agent_name = mysqli_real_escape_string($con, $_GET['agent_name']);
    $telephone = mysqli_real_escape_string($con, $_GET['telephone']);
    $sender_id = mysqli_real_escape_string($con, $_GET['app']);
    $status = "APPROVED";
    $pay_status = "NOT PAID";
    $amount = 0.00;
    $pay_date = "NONE";

    // Update query
    $updatequery = "UPDATE agents_sms SET request_status='$status' WHERE sender_id='$sender_id'";
    $uquery = mysqli_query($con, $updatequery);

    // Check if the update is successful
    if ($uquery) {
        $start_year = 2025;
        $end_year = 2026;

        // Loop through years and months
        for ($year = $start_year; $year <= $end_year; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $month_name = date('F', mktime(0, 0, 0, $month, 1, $year)); // Convert month number to month name
                $month_real = str_pad($month, 2, '0', STR_PAD_LEFT); // Ensure month is two digits (e.g., 01, 02)

                if ($insertordersItems = $con->prepare("INSERT INTO agents_sms_payments (user_id, agent_name, telephone, sender_id, status, month, months, year, pay_date, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insertordersItems->bind_param("ssssssssss", $user_id, $agent_name, $telephone, $sender_id, $pay_status, $month_name, $month_real, $year, $pay_date, $amount);

                    if (!$insertordersItems->execute()) {
                        // If there's an error, redirect with an error message
                        $error = "Failed to insert data for $month_name, $year";
                        header("Location: ussd.php?error=$error");
                        exit;
                    }
                } else {
                    // If preparation of the query fails
                    $error = "Failed to prepare statement for $month_name, $year";
                    header("Location: ussd.php?error=$error");
                    exit;
                }
            }
        }

        // If all inserts succeed
        $success = "Status Changed and Payments Data Inserted Successfully";
        header("Location: ussd.php?success=$success");
        exit;
    } else {
        // If update fails
        $error = "Failed to update ussd";
        header("Location: ussd.php?error=$error");
        exit;
    }
}



?>
