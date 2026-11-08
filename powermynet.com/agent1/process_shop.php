<?php
session_name("AGENT_PORTAL_SESSION");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from account_agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
    }
}

if (isset($_POST['shop_request'])) {
    // Sanitize and retrieve session data
    $user_id     = $_SESSION['user_id'] ?? '';
    $first_name  = $_SESSION['first_name'] ?? '';
    $last_name   = $_SESSION['last_name'] ?? '';
    $agent_email = $_SESSION['email'] ?? '';
    $agent_tel   = $_SESSION['telephone'] ?? '';
    $agent_name  = trim($first_name . ' ' . $last_name);

    // Sanitize and validate form inputs
    $shop_full    = strtoupper(trim($_POST['shop_full'] ?? ''));
    $shop         = strtolower(trim($_POST['shop'] ?? ''));
    $request_date = date('Y-m-d');
    $status       = 'PENDING';
    $activeness   = 'INACTIVE';
    $agent_type   = "Agent";
    $profit       = 0.00;
    $balance      = 0.00;
    $payout       = 0.00;
    $total_payouts = 0.00;

    // Validation
    if (empty($shop) || empty($shop_full)) {
        header("Location: shop.php?error=" . urlencode("Shop Name is required"));
        exit();
    }
    
        // ✅ Enforce shop name character limit
    if (strlen($shop_full) > 15) {
        header("Location: shop.php?error=" . urlencode("Shop fullname cannot exceed 15 characters"));
        exit();
    }

    // ✅ Enforce shop name character limit
    if (strlen($shop) > 10) {
        header("Location: shop.php?error=" . urlencode("Shop name cannot exceed 10 characters"));
        exit();
    }

    // Check if agent already has a shop
    $stmt = $con->prepare("SELECT id FROM shop WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: shop.php?error=" . urlencode("You already have a Shop"));
        exit();
    }
    $stmt->close();

    // Check if shop name is already taken
    $stmt = $con->prepare("SELECT shop FROM shop WHERE shop = ?");
    $stmt->bind_param("s", $shop);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: shop.php?error=" . urlencode("This shop name is taken. Please choose another."));
        exit();
    }
    $stmt->close();

    // Insert new shop request
    $insert = $con->prepare("
        INSERT INTO shop 
        (user_id, agent_name, email, telephone, shop, profit, balance, payout, total_payouts, activeness, status, request_date, agent_type, shop_full)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param(
        "sssssddddsssss",
        $user_id,
        $agent_name,
        $agent_email,
        $agent_tel,
        $shop,
        $profit,
        $balance,
        $payout,
        $total_payouts,
        $activeness,
        $status,
        $request_date,
        $agent_type,
        $shop_full
    );

    if ($insert->execute()) {
        header("Location: shop.php?success=" . urlencode("Request made successfully"));
    } else {
        header("Location: shop.php?error=" . urlencode("Request failed. Please try again."));
    }

    $insert->close();
    exit();
}


if (isset($_POST['shop_request'])) {
    // Sanitize and retrieve session data
    $user_id     = $_SESSION['user_id'] ?? '';
    $first_name  = $_SESSION['first_name'] ?? '';
    $last_name   = $_SESSION['last_name'] ?? '';
    $agent_email = $_SESSION['email'] ?? '';
    $agent_tel   = $_SESSION['telephone'] ?? '';
    $agent_name  = trim($first_name . ' ' . $last_name);

    // Sanitize and validate form inputs
    $shop_full    = strtoupper(trim($_POST['shop_full'] ?? ''));
    $shop         = strtolower(trim($_POST['shop'] ?? ''));
    $request_date = date('Y-m-d');
    $status       = 'PENDING';
    $activeness   = 'INACTIVE';
    $agent_type   = "Agent";
    $profit       = 0.00;
    $balance      = 0.00;
    $payout       = 0.00;
    $total_payouts = 0.00;

    // Validation
    if (empty($shop) || empty($shop_full)) {
        header("Location: shop.php?error=" . urlencode("Shop Name is required"));
        exit();
    }

    // ✅ Enforce shop name character limit
    if (strlen($shop) > 10) {
        header("Location: shop.php?error=" . urlencode("Shop name cannot exceed 10 characters"));
        exit();
    }

    // Check if agent already has a shop
    $stmt = $con->prepare("SELECT id FROM shop WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: shop.php?error=" . urlencode("You already have a Shop"));
        exit();
    }
    $stmt->close();

    // Check if shop name is already taken
    $stmt = $con->prepare("SELECT shop FROM shop WHERE shop = ?");
    $stmt->bind_param("s", $shop);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: shop.php?error=" . urlencode("This shop name is taken. Please choose another."));
        exit();
    }
    $stmt->close();

    // Insert new shop request
    $insert = $con->prepare("
        INSERT INTO shop 
        (user_id, agent_name, email, telephone, shop, profit, balance, payout, total_payouts, activeness, status, request_date, agent_type, shop_full)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param(
        "sssssddddsssss",
        $user_id,
        $agent_name,
        $agent_email,
        $agent_tel,
        $shop,
        $profit,
        $balance,
        $payout,
        $total_payouts,
        $activeness,
        $status,
        $request_date,
        $agent_type,
        $shop_full
    );

    if ($insert->execute()) {
        header("Location: shop.php?success=" . urlencode("Request made successfully"));
    } else {
        header("Location: shop.php?error=" . urlencode("Request failed. Please try again."));
    }

    $insert->close();
    exit();
}


if (isset($_POST['sms_pay'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $sender_id = mysqli_real_escape_string($con, $_POST['sender_id']);
    $year = mysqli_real_escape_string($con, $_POST['pay_year']);
    $month = mysqli_real_escape_string($con, $_POST['pay_month']);
    $pay_date = date('Y-m-d');
    $status = 'PAID';
    $amount = 25;
    $purpose = "SMS Payment";

    if (empty($sender_id)) {
        header("Location: settings.php?error=Sender Name is required");
        exit;
    }

    $select = "SELECT wallet, credit FROM agents WHERE user_id='$user_id'";
    $sql = mysqli_query($con, $select);

    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $wallet = $row['wallet'];
        $credit = $row['credit'];

        if ($wallet < $amount) {
            header("Location: settings.php?error=Insufficient wallet balance");
            exit;
        }

        // Generate reference ID
        $reference_id = substr($agent_tel, 0, 5) . date('mdHis') . substr($agent_tel, -2);

        // Step 1: Update Payment Record
        $update = "UPDATE agents_sms_payments 
                   SET status='$status', pay_date='$pay_date', amount='$amount' 
                   WHERE user_id='$user_id' AND year='$year' AND month='$month'";
        if (!mysqli_query($con, $update)) {
            header("Location: settings.php?error=Payment update failed");
            exit;
        }

        // Step 2: Deduct from Wallet and Credit
        $walletf = $wallet - $amount;
        $creditf = $credit - $amount;

        $stmt = $con->prepare("UPDATE agents SET wallet = ?, credit = ? WHERE user_id = ?");
        $stmt->bind_param("dds", $walletf, $creditf, $user_id);
        if (!$stmt->execute()) {
            header("Location: settings.php?error=Failed to update wallet balance");
            exit;
        }

        // Step 3: Record the Transaction
        $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id, trans_id, user_id, agent_name, email, amount, wallet, credit, trans_date, purpose)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdddss", $reference_id, $reference_id, $user_id, $agent_name, $agent_email, $amount, $walletf, $creditf, $pay_date, $purpose);
        if (!$stmt->execute()) {
            header("Location: settings.php?error=Failed to record transaction");
            exit;
        }

        // Step 4: Update Activeness Status
        $active = "ACTIVE";
        $update = "UPDATE agents_sms SET activeness='$active' WHERE user_id='$user_id'";
        if (!mysqli_query($con, $update)) {
            header("Location: settings.php?error=Failed to update SMS activeness");
            exit;
        }

            header("Location: settings.php?success=Payment made successfully");
            exit;

    } else {
        header("Location: settings.php?error=Agent record not found");
        exit;
    }
}


?>