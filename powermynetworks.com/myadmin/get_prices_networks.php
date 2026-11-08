<?php
include '../database/dbconfig.php';

if (isset($_POST['target'])) {
    $target = mysqli_real_escape_string($con, $_POST['target']);
    $sql = mysqli_query($con, "SELECT DISTINCT network FROM prices WHERE target='$target'");
    $options = "<option value=''>Select Network</option>";
    while ($row = mysqli_fetch_array($sql)) {
        $options .= "<option value='{$row['network']}'>{$row['network']}</option>";
    }
    echo $options;
}
?>
