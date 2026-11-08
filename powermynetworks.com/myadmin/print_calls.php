<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php'; // Include PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
include '../database/dbconfig.php';
$pen_status = "Pending";
$del_status = "Delivered";
$pro_status = "Processing";
$can_status = "Cancelled";
// Query to select only pending orders
$select = "SELECT beneficiary, mins, order_status FROM call_orders";
$result = mysqli_query($con, $select);
if ($result && mysqli_num_rows($result) > 0) {
    // Create a new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set headers (optional)
    $sheet->setCellValue('A1', 'NUMBERS');
    $sheet->setCellValue('B1', 'MINUTES');
    $rowNumber = 2;
    $hasPendingOrders = false;
    // Loop through each row in the result set
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if order status is one of the statuses we don't want to update or print
        if ($row['order_status'] == $can_status || $row['order_status'] == $del_status || $row['order_status'] == $pro_status) {
            continue; // Skip to the next row
        }
        // If order is pending, print and update it
        if ($row['order_status'] == $pen_status) {
            $hasPendingOrders = true;
            // Print pending order details in the spreadsheet
            $sheet->setCellValue('A' . $rowNumber, $row['beneficiary']);
            $sheet->setCellValue('B' . $rowNumber, $row['mins']);
            $rowNumber++;
        }
    }
    // Update all pending orders to "Processing" if there were any pending orders
    if ($hasPendingOrders) {
        $update = "UPDATE call_orders SET order_status='$pro_status' WHERE order_status='$pen_status'";
        $uquery = mysqli_query($con, $update);
        if ($uquery) {
            // Send the spreadsheet file to the browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Call_Orders.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit();
        } else {
            // Redirect in case of update failure
            header('Location: call_offers.php');
            exit();
        }
    } else {
        echo "<option value=''>No Pending Orders Available</option>";
    }
} else {
    echo "<option value=''>No Orders Available</option>";
}
?>
