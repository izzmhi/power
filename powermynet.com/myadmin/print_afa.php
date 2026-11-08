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
$del_status = "Registered";
$pro_status = "Processing";
$can_status = "Cancelled";

$current_time = date('h_i_s_A'); // Sanitized time for filename
$real_date = date('d_m_Y');

// Fetch all records
$select = "SELECT * FROM afa_registration";
$result = mysqli_query($con, $select);

if ($result && mysqli_num_rows($result) > 0) {
    // Create spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $sheet->setCellValue('A1', 'FULLNAME');
    $sheet->setCellValue('B1', 'NUMBER');
    $sheet->setCellValue('C1', 'ID TYPE');
    $sheet->setCellValue('D1', 'ID NUMBER');
    $sheet->setCellValue('E1', 'REGION');
    $sheet->setCellValue('F1', 'LOCATION');
    $sheet->setCellValue('G1', 'DATE OF BIRTH');

    $rowNumber = 2;
    $hasPendingOrders = false;

    // Rewind result and loop through
    mysqli_data_seek($result, 0);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['register_status'] !== $pen_status) continue;

        $hasPendingOrders = true;

        $sheet->setCellValue('A' . $rowNumber, $row['fullname']);
        $sheet->setCellValue('B' . $rowNumber, $row['telephone']);
        $sheet->setCellValue('C' . $rowNumber, $row['id_type']);
        $sheet->setCellValue('D' . $rowNumber, $row['id_number']);
        $sheet->setCellValue('E' . $rowNumber, $row['region']);
        $sheet->setCellValue('F' . $rowNumber, $row['location']);
        $sheet->setCellValue('G' . $rowNumber, $row['dob']);
        $rowNumber++;
    }

    if ($hasPendingOrders) {
        // Update status
        $update = "UPDATE afa_registration SET register_status='$pro_status' WHERE register_status='$pen_status'";
        if (mysqli_query($con, $update)) {
            // Set headers and download
            $filename = "AFA_REGISTRATIONS_{$real_date}_{$current_time}.xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit();
        } else {
            header('Location: afa.php?reset');
            exit();
        }
    } else {
        echo "No pending orders to export.";
        exit();
    }
} else {
    echo "No orders available.";
    exit();
}
?>
