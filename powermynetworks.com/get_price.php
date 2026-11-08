<?php
include 'waec_dbconfig.php';
$qty = intval($_POST['qty']);
$target = "WASSCE";
$stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
$stmt->bind_param("s", $target);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$unitPrice = floatval($row['amount']);
// Apply discount if qty > 5
$finalUnitPrice = ($qty > 4) ? $unitPrice - 2 : $unitPrice;
// Calculate total
$total = $finalUnitPrice * $qty;
echo json_encode([
    "unitPrice" => $finalUnitPrice,
    "total" => number_format($total, 2, '.', '')
]);
