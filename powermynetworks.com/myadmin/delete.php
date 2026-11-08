<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include'../database/dbconfig.php';

if(isset($_GET['pid'])){
$pid = mysqli_real_escape_string($con, $_GET['pid']);
 $delete = "DELETE from prices where id= '$pid' ";
    $dquery = mysqli_query($con,$delete);
    if($dquery){
       $success = "Package Deleted Successfully";
        header("Location: prices.php?success=$success");
    }
    else{
        $error = "Failed to delete a package";
        header("Location: prices.php?error=$error");
    }
}

?>