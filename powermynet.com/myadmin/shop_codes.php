<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include'../database/dbconfig.php';
if (isset($_GET['rej'])) {
    $user_id = mysqli_real_escape_string($con, $_GET['rej']);
    $status = "REJECTED";
    // Update query
    $updatequery = "UPDATE shop SET status='$status' WHERE user_id='$user_id'";
    $uquery = mysqli_query($con, $updatequery);
    // Check if update is successful
    if ($uquery) {
        // If all updates succeed
        $success = "Status Changed Successfully";
        header("Location: shop.php?success=$success");
        exit;
    }
    else{
         $error = "Failed to update package with ID $update_id";
        header("Location: shop.php?error=$error");
        exit(); // Exit the loop if there's an error
    }
}

if (isset($_GET['del'])) {
    $user_id = mysqli_real_escape_string($con, $_GET['del']);
    $delete_sms = "DELETE FROM shop WHERE user_id='$user_id'";
    $result_sms = mysqli_query($con, $delete_sms);
    if ($result_sms) {
            $success = "Shop deleted successfully";
            header("Location: shop.php?success=$success");
            exit();
    } else {
        $error = "Failed to delete Shop";
        header("Location: shop.php?error=$error");
        exit();
    }
}

if (isset($_GET['approve'])) {
    // === Configuration ===
    $status = "APPROVED";
    $activeness = "ACTIVE";
    $cpanel_domain = 'pmnet.shop';
    $logFile = __DIR__ . '/deployment_log.log';
    // === Logging Function ===
    function log_message($message) {
        global $logFile;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
    log_message("=== Starting Dynamic Shop Approval ===");
    // === Fetch All Shops ===
    $query = "SELECT id, shop FROM shop";
    $result = $con->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $shop_id = $row['id'];
            $shop = trim($row['shop']);
            if (empty($shop)) {
                log_message("Skipped: Empty shop name for ID $shop_id");
                continue;
            }
            // Create a dynamic URL (no folder)
            $shop_url = "https://$cpanel_domain/" . urlencode($shop);
            // Update database
            $update = $con->prepare("UPDATE shop SET status=?, activeness=?, url=? WHERE id=?");
            $update->bind_param("sssi", $status, $activeness, $shop_url, $shop_id);
            if ($update->execute()) {
                log_message("✅ Shop '$shop' (ID $shop_id) approved. URL: $shop_url");
            } else {
                log_message("❌ Database update failed for shop '$shop' (ID $shop_id)");
            }
            $update->close();
        }
        $success = "All shops approved dynamically with no folder creation.";
        log_message($success);
        header("Location: shop.php?success=" . urlencode($success));
        exit;
    } else {
        $error = "No shops found to approve.";
        log_message($error);
        header("Location: shop.php?error=" . urlencode($error));
        exit;
    }
}


if (isset($_GET['app'])) {
    $user_id   = mysqli_real_escape_string($con, $_GET['agent_id']);
    $sender_id = mysqli_real_escape_string($con, $_GET['app']);
    $status     = "APPROVED";
    $activeness = "ACTIVE";
    $cpanel_domain = 'pmnet.shop';
    // Step 1: Get shop name
    $query = "SELECT shop FROM shop WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($shop);
    $stmt->fetch();
    $stmt->close();
    if (!$shop) {
        header("Location: shop.php?error=Shop name not found.");
        exit;
    }
    // New dynamic shop URL
    $shop_url = "https://$cpanel_domain/" . urlencode($shop);
    // Step 2: Update status + URL
    $updatequery = "UPDATE shop SET status = ?, activeness = ?, url = ? WHERE user_id = ?";
    $stmt = $con->prepare($updatequery);
    $stmt->bind_param("ssss", $status, $activeness, $shop_url, $user_id);
    $stmt->execute();
    $stmt->close();
    // Step 3: Redirect with success
    $success = "Shop Approved Successfully: $shop_url";
    header("Location: shop.php?success=" . urlencode($success));
    exit;
}
?>