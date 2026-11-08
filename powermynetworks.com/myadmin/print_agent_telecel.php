<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include '../database/dbconfig.php';

$network    = "TELECEL";
$pen_status = "Pending";
$del_status = "Delivered";
$pro_status = "Processing";
$can_status = "Cancelled";
$medium     = "EXCEL";

$query = "SELECT beneficiary, gig, order_status FROM orders_agent WHERE network = ? AND medium = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $network, $medium);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $hasPendingOrders = false;

    // ✅ Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // ✅ Add Headers
    $sheet->setCellValue('A1', 'Beneficiary');
    $sheet->setCellValue('B1', 'Volume');

    $rowIndex = 2;

    while ($row = $result->fetch_assoc()) {
        // Skip unwanted statuses
        if (in_array($row['order_status'], [$can_status, $del_status, $pro_status])) {
            continue;
        }

        if ($row['order_status'] === $pen_status) {
            $hasPendingOrders = true;

            $recipient_number = $row['beneficiary']; // ✅ Keep full number (including zero)

            // ✅ Force as text so Excel keeps leading zero
            $sheet->setCellValueExplicit("A{$rowIndex}", $recipient_number, DataType::TYPE_STRING);
            $sheet->setCellValue("B{$rowIndex}", $row['gig']);

            $rowIndex++;
        }
    }

    if ($hasPendingOrders) {
        // ✅ Update order status
        $update = "UPDATE orders_agent SET order_status = ? WHERE network = ? AND order_status = ? AND medium = ?";
        $updateStmt = $con->prepare($update);
        $updateStmt->bind_param("ssss", $pro_status, $network, $pen_status, $medium);
        $updateStmt->execute();

        // ✅ Send XLSX to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="TELECEL_Orders.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } else {
        header("Location: agent_orders.php?error=" . urlencode("No Pending Orders Available"));
        exit();
    }
} else {
    header("Location: agent_orders.php?error=" . urlencode("No Orders Available"));
    exit();
}
?>
