<?php
include 'database/dbconfig.php';
$select = "SELECT * FROM activation";
$sql = mysqli_query($con, $select);

if ($sql && mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_array($sql);
    if ($row['status'] == "DEACTIVATED") {
        echo '<div class="text-danger">OUT OF STOCK. CHECK AGAIN IN 20 MINS TIME.</div>';
    } else {
        echo '<button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#telecelBuyModal">Buy Now</button>';
    }
} else {
    echo '<div class="text-danger">ERROR: Unable to fetch status.</div>';
}
?>