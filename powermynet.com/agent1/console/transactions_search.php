<?php
session_start();
include '../../database/dbconfig.php';

// Initialize variables
$date = mysqli_real_escape_string($con, $_GET['date'] ?? '');
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Build the query with optional filters
$whereClauses = ["agent_id='$user_id'"];

if ($date) {
    $whereClauses[] = "DATE(trans_date) = '$date'";
}
$whereSql = implode(' AND ', $whereClauses);

// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM console_transactions WHERE $whereSql";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch orders data for the current page
$query = "SELECT * FROM console_transactions WHERE $whereSql ORDER BY id DESC LIMIT $recordsPerPage OFFSET $offset";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['orders' => [], 'pagination' => '', 'error' => mysqli_error($con)]);
    exit;
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'trans_id' => $row['trans_id'],
        'console_name' => $row['console_name'],
        'package' => $row['package'],
        'trans_date' => $row['trans_date'],
    ];
}

// Generate pagination links/buttons
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        $pagination .= '<button class="btn btn-link pagination-link ' . $activeClass . '" data-page="' . $i . '">' . $i . '</button>';
    }
}

echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination
]);
?>
