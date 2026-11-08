<?php
include '../database/dbconfig.php';

$select = "SELECT * FROM activation";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;

if ($check) {
    while ($row = mysqli_fetch_array($sql)) {
        if ($row['status'] == "DEACTIVATED") {
            echo '<div class="bg-danger text-white p-2">Admin is completing an order. Wait for a few seconds to proceed with payment</div>';
        } else {
            echo '<div class="col-sm-12" id="spinm">
                    <div class="text-right">
                        <button type="submit" name="submit" class="btn btn-success" id="spin">Purchase</button>
                    </div>
                  </div>';
        }
    }
} 
?>
