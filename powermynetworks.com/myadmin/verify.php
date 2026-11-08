<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

include'../database/dbconfig.php';
if(isset($_GET['user_id'])){
    $user_id = mysqli_real_escape_string($con, $_GET['user_id']);
    $verified = "True";
        // Update the account as verified
        $updatequery = "UPDATE account_agents SET verified='$verified' WHERE user_id='$user_id' LIMIT 1";
        $uquery = mysqli_query($con, $updatequery);
        if ($uquery) {
             $success = "Account is Verified";
              header("Location: agents.php?success=$success");
              exit(); // It's good practice to call exit() after header redirection
        } else {
            $error = "Account Verification failed";
            header("Location: agents.php?error=$error");
              exit(); // It's good practice to call exit() after header redirection
        }
}
?>