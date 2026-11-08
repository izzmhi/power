<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include '../database/dbconfig.php';

$network    = "AT";
$pen_status = "Pending";
$del_status = "Delivered";
$pro_status = "Processing";
$can_status = "Cancelled";
$medium     = "EXCEL";

// Fetch orders
$query = "SELECT beneficiary, gig, order_status 
          FROM special_offers_orders 
          WHERE network = ? AND medium = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $network, $medium);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $hasPendingOrders = false;

    // ✅ Create a new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'Beneficiary');
    $sheet->setCellValue('B1', 'Volume');

    $rowIndex = 2;

    while ($row = $result->fetch_assoc()) {
        if (in_array($row['order_status'], [$can_status, $del_status, $pro_status])) {
            continue;
        }

        if ($row['order_status'] === $pen_status) {
            $hasPendingOrders = true;
            $recipient_number = $row['beneficiary'];

            // ✅ Force phone number as text (keep leading zero)
            $sheet->setCellValueExplicit("A{$rowIndex}", $recipient_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("B{$rowIndex}", $row['gig']);

            $rowIndex++;
        }
    }

    if ($hasPendingOrders) {
        // ✅ Update status
        $update = "UPDATE special_offers_orders 
                   SET order_status = ? 
                   WHERE network = ? AND order_status = ? AND medium = ?";
        $updateStmt = $con->prepare($update);
        $updateStmt->bind_param("ssss", $pro_status, $network, $pen_status, $medium);
        $updateStmt->execute();

        // ✅ Download as XLSX
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="AT_BIGTIME_Orders.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } else {
        header("Location: special.php?error=" . urlencode("No Pending Orders Available"));
        exit();
    }
} else {
    header("Location: special.php?error=" . urlencode("No Orders Available"));
    exit();
}
?>
