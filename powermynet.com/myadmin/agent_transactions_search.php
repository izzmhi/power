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
    $whereClauses[] = "(reference_id LIKE '%$search%' OR agent_name LIKE '%$search%' OR email LIKE '%$search%')";
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
$countQuery = "SELECT COUNT(*) as total FROM agents_transactions $whereSql";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch the transactions data for the current page
$query = "SELECT * FROM agents_transactions $whereSql ORDER BY id DESC LIMIT $recordsPerPage OFFSET $offset";
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
        'reference_id' => $row['reference_id'],
        'user_id' => $row['user_id'],
        'agent_name' => $row['agent_name'],
        'email' => $row['email'],
        'amount' => $row['amount'],
        'wallet' => number_format($row['wallet'], 2),
        'purpose' => $row['purpose'],
        'trans_date' => $row['trans_date'],
        'actions' => '
        <button type="button" class="btn btn-danger btn-sm deletebtn" data-id="' . $row['trans_id'] . '">
            <i class="fa fa-pencil-square" aria-hidden="true"></i> Cancel
        </button>
      ',
    ];
}

// Generating pagination links
$pagination = '';
if ($totalPages > 1) {
    $windowSize = 5;
    $startPage = max(1, $page - floor($windowSize / 2));
    $endPage = min($totalPages, $startPage + $windowSize - 1);

    // Adjust startPage if we're near the end
    if ($endPage - $startPage + 1 < $windowSize) {
        $startPage = max(1, $endPage - $windowSize + 1);
    }

    // Previous button
    if ($startPage > 1) {
        $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='1'>&laquo; First</button>";
        $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='" . ($startPage - 1) . "'>&lsaquo; Prev</button>";
    }

    // Page buttons
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-secondary pagination-link $activeClass' data-page='$i'>$i</button>";
    }

    // Next button
    if ($endPage < $totalPages) {
        $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='" . ($endPage + 1) . "'>Next &rsaquo;</button>";
        $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='$totalPages'>Last &raquo;</button>";
    }
}

// Sending the response back as JSON
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination
]);
?>
