<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
// Initialize variables
$search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
$network = mysqli_real_escape_string($con, $_GET['network'] ?? '');
$status = mysqli_real_escape_string($con, $_GET['status'] ?? '');
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
if ($network) {
    $whereClauses[] = "network='$network'";
}
if ($status) {
    $whereClauses[] = "order_status='$status'";
}
if ($date) {
    $whereClauses[] = "real_date='$date'";
}
$whereSql = implode(' AND ', $whereClauses);
// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM orders_agent_customers WHERE $whereSql AND agent_id='$user_id' ";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
// Query to fetch orders data for the current page
$query = "SELECT * FROM orders_agent_customers WHERE $whereSql AND agent_id='$user_id' ORDER BY id DESC LIMIT $recordsPerPage OFFSET $offset";
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
        'email' => $row['customer_email'],
        'beneficiary' => $row['beneficiary'],
        'network' => $row['network'],
        'gig' => $row['gig'],
        'price' => $row['price'],
        'real_date' => $row['order_date'],
        'order_time' => $row['order_time'],
        'order_status' => $row['order_status'],
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