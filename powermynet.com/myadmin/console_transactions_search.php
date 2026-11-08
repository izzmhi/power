<?php
session_start();
include '../database/dbconfig.php';

// Capture and sanitize the input parameters
$date = mysqli_real_escape_string($con, $_GET['date'] ?? '');
$agent = mysqli_real_escape_string($con, $_GET['agent'] ?? '');
$search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Build the WHERE clause based on filters
$whereClauses = [];
if ($search) {
    $whereClauses[] = "(trans_id LIKE '%$search%' OR console_id LIKE '%$search%' OR agent_name LIKE '%$search%' OR agent_email LIKE '%$search%')";
}
if ($agent) {
    $whereClauses[] = "agent_name='$agent'";
}
if ($date) {
    $whereClauses[] = "trans_date = '$date'";
}
$whereSql = implode(' AND ', $whereClauses);
$whereSql = $whereSql ? 'WHERE ' . $whereSql : '';

// Query to get the total count of records matching the criteria
$countQuery = "SELECT COUNT(*) as total FROM console_transactions $whereSql";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch the transactions data for the current page
$query = "SELECT * FROM console_transactions $whereSql ORDER BY id DESC LIMIT $recordsPerPage OFFSET $offset";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['orders' => [], 'pagination' => '', 'error' => mysqli_error($con)]);
    exit;
}

// Fetching the results and preparing the response
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'trans_id' => $row['trans_id'],
        'console_id' => $row['console_id'],
        'network' => $row['network'],
        'agent_name' => $row['agent_name'],
        'email' => $row['agent_email'],
        'tel' => $row['agent_tel'],
        'package' => $row['package'],
        'purchase_amount' => number_format($row['purchase_amount'], 2),
        'trans_date' => $row['trans_date'],
        'trans_desc' => $row['trans_desc'],
        
    ];
}

// Generating pagination links
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        $pagination .= '<button class="btn btn-link pagination-link ' . $activeClass . '" data-page="' . $i . '">' . $i . '</button>';
    }
}

// Sending the response back as JSON
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination
]);
?>
