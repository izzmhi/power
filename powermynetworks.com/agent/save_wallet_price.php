<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
// Store price in session
if (isset($_POST['price'])) {
    $_SESSION['price'] = $_POST['price'];
}
// Redirect back to the payment page to proceed with Paystack
header("Location: wallet.php");
exit();
?>