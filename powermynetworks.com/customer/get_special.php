<?php
include '../database/dbconfig.php';
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    // Query the database to get the real package based on the selected amount
    $query = "SELECT network, package, real_package FROM special_offers WHERE amount = '$amount' AND target = 'Customer'";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        // Return the result as a JSON object
        echo json_encode([
            'success' => true,
            'network' => $row['network'], // Return the package name
            'package_name' => $row['package'], // Return the package name
            'real_package' => $row['real_package'] // Return the real package
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>