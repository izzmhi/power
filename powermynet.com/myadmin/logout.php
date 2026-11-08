<?php
session_start();
// Clear all session data
session_destroy();
// Clear the 'user_id' cookie by setting its expiration time in the past
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, "/"); // Expire the cookie
}
// Redirect to the login page
header("Location:auth/login");
exit;
?>