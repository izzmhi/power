<?php
session_start();
include '../database/dbconfig.php';

// Initialize variables with default values or sanitized user input
$search       = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$agent        = isset($_GET['agent']) ? mysqli_real_escape_string($con, $_GET['agent']) : '';
$shop         = isset($_GET['shop']) ? mysqli_real_escape_string($con, $_GET['shop']) : '';
$status       = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';
$activeness   = isset($_GET['activeness']) ? mysqli_real_escape_string($con, $_GET['activeness']) : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Build WHERE clause conditions
$whereClauses = [];

// Mandatory filters
$whereClauses[] = "activeness = 'ACTIVE'";
$whereClauses[] = "status = 'APPROVED'";

// Optional filters
if ($search) {
    $whereClauses[] = "(agent_name LIKE '%$search%' OR email LIKE '%$search%' OR shop_full LIKE '%$search%' OR status LIKE '%$search%')";
}
if ($agent) {
    $whereClauses[] = "agent_name = '$agent'";
}
if ($shop) {
    $whereClauses[] = "shop = '$shop'";
}
if ($activeness) {
    $whereClauses[] = "activeness = '$activeness'";
}
if ($status) {
    $whereClauses[] = "status = '$status'";
}

// Combine into WHERE SQL
$whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

// --- Count total records
$countQuery = "SELECT COUNT(*) as total FROM shop $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// --- Fetch current page records
$query = "SELECT * FROM shop $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($row = $result->fetch_assoc()) {
    // Format status label
    $statusClass = '';
    if ($row['status'] == "PAID") {
        $statusClass = 'bg-success text-white';
    } elseif ($row['status'] == "UNPAID") {
        $statusClass = 'bg-danger text-white';
    } else {
        $statusClass = 'bg-info text-white';
    }

    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'user_id' => $row['user_id'],
        'agent' => $row['agent_name'],
        'agent_tel' => $row['telephone'],
        'shop' => $row['shop'],
        'balance' => $row['balance'],
        'profit' => number_format((float)$row['profit'], 2, '.', ''),
        'payout' => $row['payout'],
        'total_payouts' => $row['total_payouts'],
        'actions' => '
            <button type="button" class="btn btn-success btn-sm paybtn" data-id="' . htmlspecialchars($row['user_id']) . '">Pay</button>
            <button type="button" class="btn btn-info btn-sm approvalbtn" data-id="' . htmlspecialchars($row['user_id']) . '">Status</button>
            <button type="button" class="btn btn-primary btn-sm deletebtn" data-id="' . htmlspecialchars($row['user_id']) . '">Activeness</button>
        '
    ];
}

// --- Generate pagination buttons
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-secondary pagination-link $activeClass' data-page='$i'>$i</button>";
    }
}

// --- Prepare the response
$response = [
    'orders' => $orders,
    'pagination' => $pagination
];

// Output JSON
echo json_encode($response);
?>
