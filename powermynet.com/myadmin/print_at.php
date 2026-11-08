<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Include PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Changed to Xlsx

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include '../database/dbconfig.php';

$network = "AirtelTigo";
$status = "Pending";
$select = "SELECT beneficiary, gig FROM orders WHERE network='$network' AND order_status='$status'";
$result = mysqli_query($con, $select);

if (!$result) {
    die('Error: ' . mysqli_error($con));
}

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers (optional)
//$sheet->setCellValue('A1', 'Beneficiary');
//$sheet->setCellValue('B1', 'Gig');

$rowNumber = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNumber, $row['beneficiary']);
    $sheet->setCellValue('B' . $rowNumber, $row['gig']);
    $rowNumber++;
}

// Write the spreadsheet to an .xlsx file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="AT_Orders.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
