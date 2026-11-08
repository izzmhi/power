<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

include'../database/dbconfig.php';
require 'config.php';
if (isset($_POST['pay'])) {
    // Sanitize and validate input
    $agent_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $purpose = "Settlement";

    if (!filter_var($amount, FILTER_VALIDATE_INT) || $amount <= 0) {
        $error = "Invalid amount. Please enter a valid number greater than 0.";
        header("Location: shop.php?error=" . urlencode($error));
        exit();
    }

    $payout_date = date('Y-m-d');

    // Fetch agent/shop details
    $shopQuery = $con->prepare("SELECT * FROM shop WHERE user_id = ?");
    $shopQuery->bind_param("s", $agent_id);
    $shopQuery->execute();
    $shopResult = $shopQuery->get_result();

    if ($shopResult && $shopResult->num_rows > 0) {
        $shop = $shopResult->fetch_assoc();
        $agent_name = $shop['agent_name'];
        $telephone = $shop['telephone'];
        $email = $shop['email'];
        $old_balance = $shop['balance'];
        $old_profit = $shop['profit'];
        $old_payout = $shop['payout'];
        $momo_number       = $shop['momo_number'];
        $momo_name         = $shop['momo_name'];
        $old_total_payout  = $shop['total_payouts'];
        $process       = $old_balance-1;
        $new_balance = $old_balance - $amount;

        if ($amount > $process) {
            $error = "You are paying $amount more than the balance $process. 1GH will be deducted";
            header("Location: shop.php?error=" . urlencode($error));
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
    $amount_pesewas = intval($amount * 100);

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
                 "Authorization: Bearer " . PAYSTACK_SECRET_KEY,  // Use the stored key
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
   $processing_fee = 1; // or bonus
   $new_amount = $amount + $processing_fee;
   $new_balance      = $old_balance - $new_amount;
   $new_total_payout = $old_total_payout + $new_amount;

    $updateShop = $con->prepare("UPDATE shop SET payout = ?, balance = ?, total_payouts = ? WHERE user_id = ?");
    $updateShop->bind_param("ddds", $new_amount, $new_balance, $new_total_payout, $agent_id);
    $updateShop->execute();

    if ($updateShop->affected_rows > 0) {
        // Insert payout record
        $payout_id = date('md') . bin2hex(random_bytes(2)) . date('y') . bin2hex(random_bytes(2)) . date('His');
        $insertPayout = $con->prepare("INSERT INTO agents_payouts (user_id, agent_name, email, telephone, amount, balance, total_payouts, payout_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertPayout->bind_param("ssssddds", $agent_id, $agent_name, $email, $telephone, $new_amount, $new_balance, $new_total_payout, $payout_date);
        $insertPayout->execute();
        // Send email
        if ($insertPayout->affected_rows > 0) {
            sendSettlementEmail($email, $new_amount, $new_balance);
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
function sendSettlementEmail($email, $new_amount, $balance) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'codecraftnetwork.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@codecraftnetwork.com';
        $mail->Password = 'REDACTED';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('noreply@codecraftnetwork.com', 'CodeCraftNetwork');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Agent Settlement";
        $mail->Body = "
            <p>Dear Agent,</p>
            <p>You have withdrawn GHS {$new_amount}.</p>
            <p>Your new shop account balance is GHS {$balance}.</p>
            <p>Thank you for using CodeCraftNetwork.</p>
            <footer style='margin-top:20px; font-size:small; color:gray;'>&copy; CodeCraftNetwork</footer>
        ";
        $mail->send();
    } catch (Exception $e) {
        // Log error but don't block payout
    }
}

?>