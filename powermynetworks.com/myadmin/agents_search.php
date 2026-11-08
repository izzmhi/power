<?php
session_start();
include '../database/dbconfig.php';
// Initialize variables with default values or sanitized user input
$register_status = isset($_GET['register_status']) ? mysqli_real_escape_string($con, $_GET['register_status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;
// Build the query with optional filters
$whereClauses = [];
if ($search) {
    $whereClauses[] = "(first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR telephone LIKE '%$search%' )";
}
if ($register_status) {
    $whereClauses[] = "status='$register_status'";
}
$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM account_agents $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
// Query to fetch orders data for the current page
$query = "SELECT * FROM account_agents $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    // Add the order data to the orders array
        // Determine the CSS class for the order status
    $statusClass = '';
    if ($row['status'] == "APPROVED") {
        $statusClass = 'bg-success text-white';
    } elseif ($row['status'] == "REJECTED") {
        $statusClass = 'bg-danger text-white';
    } 
    elseif ($row['status'] == "PENDING") {
        $statusClass = 'bg-warning text-white';
    } 
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'user_id' => $row['user_id'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'email' => $row['email'],
        'telephone' => $row['telephone'],
        'verify' => $row['verified'],
        'register_status' => "<span style='padding:3px;' class='$statusClass'>{$row['status']}</span>", 
'actions' => '
<a class="btn btn-success btn-sm" href="verify.php?user_id=' . $row['user_id'] . '">
    <i class="fa fa-pencil-square" aria-hidden="true"></i> Verify
</a>
<button type="button" class="btn btn-info btn-sm approvalbtn" data-id="' . $row['user_id'] . '">
    <i class="fa fa-pencil-square" aria-hidden="true"></i> Approval
</button>
<button type="button" class="btn btn-danger btn-sm deletebtn" data-id="' . $row['user_id'] . '">
    Delete <i class="fa fa-trash" aria-hidden="true"></i>
</button>
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
