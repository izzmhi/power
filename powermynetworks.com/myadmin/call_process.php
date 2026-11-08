<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include'../database/dbconfig.php';

if(isset($_GET['delete_id'])){
$delete_id = mysqli_real_escape_string($con, $_GET['delete_id']);
 $delete = "DELETE from call_offers where id= '$delete_id' ";
    $dquery = mysqli_query($con,$delete);
    if($dquery){
       $success = "Package Deleted Successfully";
        header("Location: call_offers.php?success=$success");
    }
    else{
        $error = "Failed to delete a package";
        header("Location: call_offers.php?error=$error");
    }
}


if (isset($_POST['update_calls_offers'])) {
    include '../database/dbconfig.php';
    // Loop through each record
    foreach ($_POST['id'] as $index => $update_id) {
        $mins = mysqli_real_escape_string($con, $_POST['mins'][$index]);
        $amount = mysqli_real_escape_string($con, $_POST['amount'][$index]);
        // Update query
        $updatequery = "UPDATE call_offers SET mins='$mins', amount='$amount' WHERE id='$update_id'";
        $uquery = mysqli_query($con, $updatequery);
        // Check if update is successful
        if (!$uquery) {
            $error = "Failed to update package with ID $update_id";
            header("Location: call_offers.php?error=$error");
            exit(); // Exit the loop if there's an error
        }
    }
    // If all updates succeed
    $success = "Packages Updated Successfully";
    header("Location: call_offers.php?success=$success");
}

?>
