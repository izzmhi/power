<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
// Initialize variables
$search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
$date = mysqli_real_escape_string($con, $_GET['date'] ?? '');
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;
// Build the query with optional filters
$whereClauses = ["agent_id='$user_id'"];
if ($search) {
    $whereClauses[] = "(reference_id LIKE '%$search%' OR beneficiary LIKE '%$search%')";
}

if ($date) {
    $whereClauses[] = "real_date='$date'";
}
$whereSql = implode(' AND ', $whereClauses);
// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM orders_agent WHERE $whereSql AND agent_id='$user_id' AND origin='SHOP'";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
// Query to fetch orders data for the current page
$query = "SELECT * FROM orders_agent WHERE $whereSql AND agent_id='$user_id' AND origin='SHOP' ORDER BY id DESC LIMIT $recordsPerPage OFFSET $offset";
$result = mysqli_query($con, $query);
if (!$result) {
    echo json_encode(['orders' => [], 'pagination' => '', 'error' => mysqli_error($con)]);
    exit;
}
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'order_id' => $row['order_id'],
        'reference_id' => $row['reference_id'],
        'email' => $row['email'],
        'beneficiary' => $row['beneficiary'],
        'network' => $row['network'],
        'gig' => $row['gig'],
        'price' => $row['price'],
        'real_date' => $row['order_date'],
        'order_time' => $row['order_time'],
        'order_status' => $row['order_status'],
        'customer_email' => $row['customer_email'],
        'customer_price' => $row['customer_price'],
        'origin' => $row['origin'],
    ];
}
// Generate pagination links/buttons
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
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination
]);
?>