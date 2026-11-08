<?php
session_name("AGENT_PORTAL_SESSION");
include '../database/dbconfig.php';
if (!isset($_POST['qty']) || $_POST['qty'] == "") {
    echo json_encode(["status" => "error", "message" => "Invalid qty"]);
    exit;
}
$qty = intval($_POST['qty']);
$select = "SELECT amount FROM checker_price WHERE product='WASSCE' LIMIT 1";
$sql = mysqli_query($con, $select);

if (mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);
    $price = floatval($row['amount']);
    // Apply discount rule
    if ($qty >= 5) {
        $price_after_discount = $price - 2;
        if ($price_after_discount < 0) $price_after_discount = 0; // safety
        $total = $qty * $price_after_discount;
        $final_price = $price_after_discount;
    } else {
        $total = $qty * $price;
        $final_price = $price;
    }
    echo json_encode([
        "status" => "success",
        "total" => $total,
        "price" => $final_price,
        "original_price" => $price,
        "discount_applied" => $qty >= 5
    ]);
    exit;
}
echo json_encode(["status" => "error", "message" => "Price not found"]);
?>
