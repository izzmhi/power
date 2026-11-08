<?php
include '../../database/dbconfig.php';
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    // Query the database to get the real package based on the selected amount
    $query = "SELECT * FROM console_prices WHERE amount = '$amount' ";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        // Return the result as a JSON object
        echo json_encode([
            'success' => true,
            'package_name' => $row['package'], // Return the package name
            'amount' => $row['amount'], // Return the package name
            'real_package' => $row['real_package'], // Return the real package
            'original' => $row['original'] // Return the original package
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>