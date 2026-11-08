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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require 'config.php';
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
       $_SESSION['last_name']  = $rows['last_name'];
       $_SESSION['email']      = $rows['email'];
       $_SESSION['telephone']  = $rows['telephone'];
    }
}

if (isset($_POST['submit_momo'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $momo_number = mysqli_real_escape_string($con, $_POST['momo_number']);
    $momo_name = mysqli_real_escape_string($con, $_POST['momo_name']);
    // Validate input
    if (empty($momo_number)) {
        header("Location: shop.php?error=MoMo number is required");
        exit();
    }
    if (empty($momo_name)) {
        header("Location: shop.php?error=MoMo name is required");
        exit();
    }
    // Check if agent already has a shop
    $stmt = $con->prepare("SELECT status FROM shop WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check approval
        if ($row['status'] !== "APPROVED") {
            header("Location: shop.php?error=Your shop is not approved");
            exit();
        }
        // Update MoMo details
        $stmt = $con->prepare("UPDATE shop SET momo_name = ?, momo_number = ? WHERE user_id = ?");
        $stmt->bind_param("sss", $momo_name, $momo_number, $user_id);
        if (!$stmt->execute()) {
            header("Location: shop.php?error=Failed to update MoMo account details");
            exit();
        }
        header("Location: shop.php?success=MoMo account updated successfully");
        exit();
    } else {
        header("Location: shop.php?error=No shop found for this user");
        exit();
    }
}


if (isset($_POST['submit_withdraw'])) {
    // Sanitize input
    $user_id     = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $amount      = floatval($_POST['amount']);
    $first_name  = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name   = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $email       = mysqli_real_escape_string($con, $_SESSION['email']);
    $telephone   = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $agent_name  = $first_name . " " . $last_name;
    $payout_date = date('Y-m-d');
    // Get shop info
    $stmt = $con->prepare("SELECT * FROM shop WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $shopResult = $stmt->get_result();
    if ($shopResult->num_rows === 0) {
        header("Location: shop.php?error=" . urlencode("Shop not found."));
        exit();
    }
    $shop = $shopResult->fetch_assoc();
    if ($shop['status'] !== "APPROVED") {
        header("Location: shop.php?error=" . urlencode("Your shop is not approved"));
        exit();
    }
    if ($shop['activeness'] !== "ACTIVE") {
        header("Location: shop.php?error=" . urlencode("Your shop is not active"));
        exit();
    }
    // Ensure shop has enough balance
    if ($shop['balance'] < $amount) {
        header("Location: shop.php?error=" . urlencode("Insufficient shop balance."));
        exit();
    }
    // Old values
    $old_balance      = floatval($shop['balance']);
    $old_total_payout = floatval($shop['total_payouts']);
    // Get agent wallet
    $stmt = $con->prepare("SELECT wallet FROM agents WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $agentResult = $stmt->get_result();
    if ($agentResult->num_rows === 0) {
        header("Location: shop.php?error=" . urlencode("Agent not found."));
        exit();
    }
    $agent = $agentResult->fetch_assoc();
    $wallet = floatval($agent['wallet']);
    // Update wallet
    $new_wallet = $wallet + $amount;
    // Update shop balances
    $new_balance      = $old_balance - $amount;
    $new_total_payout = $old_total_payout + $amount;
    $updateShop = $con->prepare("UPDATE shop SET payout=?, balance=?, total_payouts=? WHERE user_id=?");
    $updateShop->bind_param("ddds", $amount, $new_balance, $new_total_payout, $user_id);
    $updateShop->execute();
    if ($updateShop->affected_rows > 0) {
        // Insert payout record
        $insertPayout = $con->prepare("INSERT INTO agents_payouts (user_id, agent_name, email, telephone, amount, balance, total_payouts, payout_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertPayout->bind_param("sssdddds",$user_id, $agent_name, $email, $telephone, $amount, $new_balance, $new_total_payout, $payout_date);
        $insertPayout->execute();
        if ($insertPayout->affected_rows > 0) {
            // Update agent wallet
            $updateAgent = $con->prepare("UPDATE agents SET wallet=? WHERE user_id=?");
            $updateAgent->bind_param("ds", $new_wallet, $user_id);
            $updateAgent->execute();
            if ($updateAgent->affected_rows > 0) {
                sendSettlementEmail($email, $amount, $new_balance);
                header("Location: shop.php?success=" . urlencode("Payment successful."));
                exit();
            } else {
                header("Location: shop.php?error=" . urlencode("Failed to update agent wallet."));
                exit();
            }
        } else {
            header("Location: shop.php?error=" . urlencode("Failed to insert payout record."));
            exit();
        }
    } else {
        header("Location: shop.php?error=" . urlencode("Failed to update shop balance."));
        exit();
    }
}



if (isset($_POST['submit_withdraw_paystack'])) {
    // Sanitize input
    $user_id     = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $amount      = floatval($_POST['amount']); // safer numeric handling
    $first_name  = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name   = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $email       = mysqli_real_escape_string($con, $_SESSION['email']);
    $telephone   = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $agent_name  = $first_name . " " . $last_name;
    // Validation
    if (empty($amount) || $amount <= 1) {
        header("Location: shop.php?error=" . urlencode("Amount is required and must be greater than zero"));
        exit();
    }
    $payout_date = date('Y-m-d');
    // Get shop info
    $stmt = $con->prepare("SELECT * FROM shop WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: shop.php?error=" . urlencode("Agent not found."));
        exit();
    }

    $row = $result->fetch_assoc();

    if ($row['status'] !== "APPROVED") {
        header("Location: shop.php?error=" . urlencode("Your shop is not approved"));
        exit();
    }
    if ($row['activeness'] !== "ACTIVE") {
        header("Location: shop.php?error=" . urlencode("Your shop is not active"));
        exit();
    }

    $momo_number       = $row['momo_number'];
    $momo_name         = $row['momo_name'];
    $old_balance       = $row['balance'];
    $old_total_payout  = $row['total_payouts'];
    $process       = $old_balance-1;
    if ($amount > $process) {
        header("Location: shop.php?error=" . urlencode("You cannot withdraw more than your balance"));
        exit();
    }

    // Auto-detect network
    $network = detectNetwork($momo_number);
    if ($network === "Unknown") {
        header("Location: shop.php?error=" . urlencode("Unable to detect network for number: $momo_number"));
        exit();
    }

    // Create Paystack Transfer Recipient
    $recipient_url = "https://api.paystack.co/transferrecipient";
    $recipient_fields = [
        "type"           => "mobile_money",
        "name"           => $momo_name,
        "account_number" => $momo_number,
        "bank_code"      => $network, 
        "currency"       => "GHS"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $recipient_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($recipient_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $recipient_response = curl_exec($ch);
    if ($recipient_response === false) {
        die("Curl error: " . curl_error($ch));
    }
    curl_close($ch);

    $recipient_data = json_decode($recipient_response, true);
    if (!$recipient_data['status']) {
        header("Location: shop.php?error=" . urlencode("Failed to create recipient: " . $recipient_data['message']));
        exit();
    }
    $recipient_code = $recipient_data['data']['recipient_code'];

    //  Initiate Paystack Transfer
    $transfer_url = "https://api.paystack.co/transfer";
    $paystack_amount = $amount -1;
    $amount_pesewas = intval($paystack_amount * 100);

    $transfer_fields = [
        "source"    => "balance",
        "reason"    => "Agent Settlement",
        "amount"    => $amount_pesewas,
        "recipient" => $recipient_code
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $transfer_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transfer_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $transfer_response = curl_exec($ch);
    if ($transfer_response === false) {
        die("Curl error: " . curl_error($ch));
    }
    curl_close($ch);
    $transfer_data = json_decode($transfer_response, true);
 // roceed if either queued or success
if (
    !$transfer_data['status'] || 
    (
        strtolower($transfer_data['data']['status']) !== 'success' && 
        strtolower($transfer_data['message']) !== 'transfer has been queued'
    )
) {
    header("Location: shop.php?error=" . urlencode("Transfer failed: " . $transfer_data['message']));
    exit();
}
    // 4️ Update balances

   $new_balance      = $old_balance - $amount;
   $new_total_payout = $old_total_payout + $amount;

    $updateShop = $con->prepare("UPDATE shop SET payout = ?, balance = ?, total_payouts = ? WHERE user_id = ?");
    $updateShop->bind_param("ddds", $amount, $new_balance, $new_total_payout, $user_id);
    $updateShop->execute();

    if ($updateShop->affected_rows > 0) {
        // Insert payout record
        $payout_id = date('md') . bin2hex(random_bytes(2)) . date('y') . bin2hex(random_bytes(2)) . date('His');
        $insertPayout = $con->prepare("INSERT INTO agents_payouts (user_id, agent_name, email, telephone, amount, balance, total_payouts, payout_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertPayout->bind_param("ssssddds", $user_id, $agent_name, $email, $telephone, $amount, $new_balance, $new_total_payout, $payout_date);
        $insertPayout->execute();
        // Send email
        if ($insertPayout->affected_rows > 0) {
            sendSettlementEmail($email, $amount, $new_balance);
            header("Location: shop.php?success=" . urlencode("Payment successful."));
            exit();
        } else {
            header("Location: shop.php?error=" . urlencode("Failed to insert payout record."));
            exit();
        }
    } else {
        header("Location: shop.php?error=" . urlencode("Failed to update shop balance."));
        exit();
    }
}

// Helper: Detect network
function detectNetwork($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 3) === "233") {
        $phone = "0" . substr($phone, 3);
    }

    $mtnPrefixes       = ['024', '054', '055', '059', '025'];
    $vodafonePrefixes  = ['020', '050'];
    $airteltigoPrefixes = ['026', '056', '027', '057'];

    $prefix = substr($phone, 0, 3);

    if (in_array($prefix, $mtnPrefixes)) {
        return "MTN";
    } elseif (in_array($prefix, $vodafonePrefixes)) {
        return "VOD";
    } elseif (in_array($prefix, $airteltigoPrefixes)) {
        return "ATL";
    } else {
        return "Unknown";
    }
}

// Helper: Send settlement email
function sendSettlementEmail($email, $amount, $balance) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'de102.aveshost.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@powermynetworks.com';
        $mail->Password = 'REDACTED';
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Agent Settlement";
        $mail->Body = "
            <p>Dear Agent,</p>
            <p>You have withdrawn GHS {$amount}.</p>
            <p>Your new shop account balance is GHS {$balance}.</p>
            <p>Thank you for using Powermynet.</p>
            <footer style='margin-top:20px; font-size:small; color:gray;'>&copy; Powermynet</footer>
        ";
        $mail->send();
    } catch (Exception $e) {
        // Log error but don't block payout
    }
}




?>