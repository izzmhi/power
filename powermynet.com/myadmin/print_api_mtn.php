<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php'; // Include PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Xlsx format
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
// Set headers for the spreadsheet (optional)

// Set the API endpoint URL
require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.dataworksgh.com/api/admin_api_print.php');
// Check if the session email is set
if (isset($_SESSION['admin_email'])) {
    $admin_email = $_SESSION['admin_email'];
    // Append the email as a query parameter to the API URL
    $apiUrl = $url . '?admin_email=' . urlencode($admin_email);
    // Initialize cURL session
    $ch = curl_init();
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (if necessary)
    // Execute the cURL request
    $response = curl_exec($ch);
    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Decode the JSON response
        $orderDetails = json_decode($response, true);
        // Check if the response contains order details and the status is 'success'
        if ($orderDetails && $orderDetails['status'] == 'success') {
            $details = $orderDetails['order_details'];
            // Populate the spreadsheet with the API data
            $rowNumber = 2; // Start from the second row (first row is for headers)
            foreach ($details as $detail) {
                $sheet->setCellValue('A' . $rowNumber, $detail['beneficiary']);
                $sheet->setCellValue('B' . $rowNumber, $detail['gig']);
                $rowNumber++;
            }
        } else {
            echo "Failed to retrieve order details from API.";
        }
    }
    // Close the cURL session
    curl_close($ch);
} else {
    // Display error message if the email parameter is not provided in session
    echo "Admin email is required for API call.";
}
// Write the spreadsheet to an .xlsx file and trigger download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="MTN_Api_Orders.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
