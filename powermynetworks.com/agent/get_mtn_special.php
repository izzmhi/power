<?php
session_name("AGENT_PORTAL_SESSION");
include '../database/dbconfig.php';
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $networks = "MTN";
    // Query the database to get the real package based on the selected amount
    $query = "SELECT package FROM special_offers WHERE amount = '$amount' AND target = 'Agent' AND network='$networks' ";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        // Return the result as a JSON object
        echo json_encode([
            'success' => true,
            'package_name' => $row['package'], // Return the package name
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>