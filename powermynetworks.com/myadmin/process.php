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
    foreach ($_POST['id'] as $index => $update_id) {

        $package   = mysqli_real_escape_string($con, $_POST['package'][$index]);
        $original  = mysqli_real_escape_string($con, $_POST['original'][$index]);
        $amount    = mysqli_real_escape_string($con, $_POST['amount'][$index]);
        $network   = mysqli_real_escape_string($con, $_POST['network'][$index]);

        // Update main package table
        $updatequery = "
            UPDATE special_offers 
            SET original='$original', amount='$amount' 
            WHERE id='$update_id'
        ";

        if (mysqli_query($con, $updatequery)) {

            // Update agents price table (FIXED)
            $updateAgents = "
                UPDATE agents_prices_special 
                SET agent_price='$amount', original='$original' 
                WHERE network='$network' 
                AND package='$package'
            ";

            if (mysqli_query($con, $updateAgents)) {
                // Continue loop
            } else {
                $error = "Failed to update agents price for Package: $package";
                header("Location: special.php?error=" . urlencode($error));
                exit();
            }

        } else {
            $error = "Failed to update package with ID $update_id";
            header("Location: special.php?error=" . urlencode($error));
            exit();
        }
    }

    // If loop finishes successfully
    $success = "Packages Updated Successfully";
    header("Location: special.php?success=" . urlencode($success));
    exit();
}

?>
