<?php
include '../database/dbconfig.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $query = "SELECT first_name, last_name, email, telephone, wallet FROM agents WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $email, $telephone, $wallet);
    $stmt->fetch();
    $stmt->close();

    $agent_name = $first_name . " " . $last_name;

    // Return the data as a JSON response
    echo json_encode([
        'agent_name' => $agent_name,
        'email' => $email,
        'telephone' => $telephone,
        'wallet' => $wallet
    ]);
}
?>

