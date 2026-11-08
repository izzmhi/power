<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
header('Content-Type: application/json');
// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['orders' => [], 'pagination' => '', 'error' => 'User not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;
$where = "WHERE user_id = ?";
$params = [$user_id];
$types = "s";
if (!empty($date)) {
    $where .= " AND payout_date = ?";
    $params[] = $date;
    $types .= "s";
}
// Count total records
$countQuery = $con->prepare("SELECT COUNT(*) as total FROM agents_payouts $where");
$countQuery->bind_param($types, ...$params);
$countQuery->execute();
$countResult = $countQuery->get_result();
$totalRecords = $countResult->fetch_assoc()['total'] ?? 0;
$countQuery->close();

$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch paginated data
$query = $con->prepare("SELECT * FROM agents_payouts $where ORDER BY id DESC LIMIT ? OFFSET ?");
$params[] = $recordsPerPage;
$params[] = $offset;
$types .= "ii";
$query->bind_param($types, ...$params);
$query->execute();
$result = $query->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'user_id'=> $row['user_id'],
        'price'  => number_format($row['price'], 2, '.', ''),   // ✅ 2 decimals
        'amount' => number_format($row['amount'], 2, '.', ''),  // ✅ 2 decimals
        'balance'=> number_format($row['balance'], 2, '.', ''), // ✅ 2 decimals
         'total_payouts' => number_format($row['total_payouts'], 2, '.', ''), // ✅ 2 decimals
        'payout_date' => $row['payout_date']
    ];
}
$query->close();

// Generate pagination (smart version)
$pagination = '';
if ($totalPages > 1) {
    if ($page > 1) {
        $pagination .= "<button class='btn btn-link pagination-link' data-page='" . ($page - 1) . "'>&laquo; Prev</button>";
    }

    for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-link pagination-link $activeClass' data-page='$i'>$i</button>";
    }

    if ($page < $totalPages) {
        $pagination .= "<button class='btn btn-link pagination-link' data-page='" . ($page + 1) . "'>Next &raquo;</button>";
    }
}

// Output JSON
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination,
    'error' => ''
]);
