<?php
session_name("AGENT_PORTAL_SESSION");
include '../database/dbconfig.php';
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    // Query the database to get the real package based on the selected amount
    $query = "SELECT mins FROM call_offers WHERE amount = '$amount' AND target = 'Agent'";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        // Return the result as a JSON object
        echo json_encode([
            'success' => true,
            'mins' => $row['mins'], // Return the package name
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>