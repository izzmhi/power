<?php
session_start();
include '../database/dbconfig.php';
// Initialize variables with default values or sanitized user input
$search    = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$agent     = isset($_GET['agent']) ? mysqli_real_escape_string($con, $_GET['agent']) : '';
$sender_id = isset($_GET['sender_id']) ? mysqli_real_escape_string($con, $_GET['sender_id']) : '';
$year      = isset($_GET['year']) ? mysqli_real_escape_string($con, $_GET['year']) : '';
$month     = isset($_GET['month']) ? mysqli_real_escape_string($con, $_GET['month']) : '';
$status    = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;
// Build the query with optional filters
$whereClauses = [];
if ($search) {
    $whereClauses[] = "(agent_name LIKE '%$search%' OR sender_id LIKE '%$search%' OR year LIKE '%$search%' OR month LIKE '%$search%' OR status LIKE '%$search%' OR email LIKE '%$search%' )";
}
if ($agent) {
    $whereClauses[] = "agent_name='$agent'";
}
if ($sender_id) {
    $whereClauses[] = "sender_id='$sender_id'";
}
if ($year) {
    $whereClauses[] = "year='$year'";
}
if ($month) {
    $whereClauses[] = "month='$month'";
}
if ($status) {
    $whereClauses[] = "status='$status'";
}
$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM agents_sms_payments $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
// Query to fetch orders data for the current page
$query = "SELECT * FROM agents_sms_payments $whereSql ORDER BY year,months LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    // Add the order data to the orders array
        // Determine the CSS class for the order status
    $statusClass = '';
    if ($row['status'] == "PAID") {
        $statusClass = 'bg-success text-white';
    } elseif ($row['status'] == "UNPAID") {
        $statusClass = 'bg-danger text-white';
    } 
    else {
        $statusClass = 'bg-info text-white';
    } 
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'user_id' => $row['user_id'],
        'year' => $row['year'],
        'month' => $row['month'],
        'agent_name' => $row['agent_name'],
        'telephone' => $row['telephone'],
        'sender_id' => $row['sender_id'],
        'amount' => $row['amount'],
        'pay_date' => $row['pay_date'],
        'pay_status' => "<span style='padding:3px;' class='$statusClass'>{$row['status']}</span>", 
'actions' => '
<a class="btn btn-success btn-sm" href="verify.php?user_id=' . $row['user_id'] . '"> <i class="fa fa-pencil-square" aria-hidden="true"></i> action</a>
<button type="button" class="btn btn-info btn-sm approvalbtn" data-id="' . $row['user_id'] . '"> Active </button>
<button type="button" class="btn btn-danger btn-sm deletebtn" data-id="' . $row['user_id'] . '"> Inactive </button>
',

    ];
}

// Generate pagination buttons
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-secondary pagination-link $activeClass' data-page='$i'>$i</button>";
    }
}

// Prepare the response array
$response = [
    'orders' => $orders,
    'pagination' => $pagination
];

// Output the response as JSON
echo json_encode($response);
?>
