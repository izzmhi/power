<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include'../database/dbconfig.php';

if(isset($_GET['delete_id'])){
$delete_id = mysqli_real_escape_string($con, $_GET['delete_id']);
 $delete = "DELETE from special_offers where id= '$delete_id' ";
    $dquery = mysqli_query($con,$delete);
    if($dquery){
       $success = "Package Deleted Successfully";
        header("Location: special.php?success=$success");
    }
    else{
        $error = "Failed to delete a package";
        header("Location: special.php?error=$error");
    }
}


if (isset($_POST['update'])) {
    include '../database/dbconfig.php';
    // Loop through each record
    foreach ($_POST['id'] as $index => $update_id) {
        $package = mysqli_real_escape_string($con, $_POST['package'][$index]);
        $original = mysqli_real_escape_string($con, $_POST['original'][$index]);
        $amount = mysqli_real_escape_string($con, $_POST['amount'][$index]);
        // Update query
        $updatequery = "UPDATE special_offers SET package='$package', original='$original', amount='$amount' WHERE id='$update_id'";
        $uquery = mysqli_query($con, $updatequery);
        // Check if update is successful
        if (!$uquery) {
            $error = "Failed to update package with ID $update_id";
            header("Location: special.php?error=$error");
            exit(); // Exit the loop if there's an error
        }
    }
    // If all updates succeed
    $success = "Packages Updated Successfully";
    header("Location: special.php?success=$success");
}
?>
