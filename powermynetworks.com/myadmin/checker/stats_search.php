<?php
session_start();
header('Content-Type: application/json');
require_once 'database/dbconfig.php';

// ====== Input ======
$date           = isset($_GET['date']) ? trim($_GET['date']) : '';
$page           = isset($_GET['page']) ? (int)($_GET['page']) : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)($_GET['recordsPerPage']) : 10;

if ($page < 1) $page = 1;
if ($recordsPerPage < 1) $recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// ====== WHERE (safe) ======
$whereSql = '';
$whereParams = [];
$whereTypes = '';

if ($date !== '') {
    $whereSql = 'WHERE trans_date = ?';
    $whereParams[] = $date;
    $whereTypes .= 's';
}

// ====== Total count ======
$countSql = "
    SELECT COUNT(DISTINCT trans_date) AS total
    FROM payments
    $whereSql
";

$countStmt = $con->prepare($countSql);
if ($whereSql !== '') {
    $countStmt->bind_param($whereTypes, ...$whereParams);
}
$countStmt->execute();
$countRes = $countStmt->get_result();
$totalRecords = (int)($countRes->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = ($totalRecords > 0) ? (int)ceil($totalRecords / $recordsPerPage) : 1;

// ====== Fetch paginated data ======
$dataSql = "
    SELECT 
        trans_date,
        COUNT(*) AS total_orders,
        COALESCE(SUM(amount), 0) AS total_amount,
        COALESCE(SUM(profit), 0) AS total_profit,

        COALESCE(SUM(CASE WHEN purpose = 'BECE' THEN 1 ELSE 0 END), 0) AS bece_orders,
        COALESCE(SUM(CASE WHEN purpose = 'BECE' THEN amount ELSE 0 END), 0) AS bece_amount,

        COALESCE(SUM(CASE WHEN purpose = 'WASSCE' THEN 1 ELSE 0 END), 0) AS wassce_orders,
        COALESCE(SUM(CASE WHEN purpose = 'WASSCE' THEN amount ELSE 0 END), 0) AS wassce_amount

    FROM payments
    $whereSql
    GROUP BY trans_date
    ORDER BY STR_TO_DATE(trans_date, '%Y-%m-%d') DESC
    LIMIT ? OFFSET ?
";

$dataStmt = $con->prepare($dataSql);

// Bind WHERE + pagination params
if ($whereSql !== '') {
    $bindTypes = $whereTypes . 'ii';
    $dataStmt->bind_param($bindTypes, ...array_merge($whereParams, [$recordsPerPage, $offset]));
} else {
    $dataStmt->bind_param('ii', $recordsPerPage, $offset);
}

$dataStmt->execute();
$res = $dataStmt->get_result();

// ====== Build data ======
$orders = [];
$sn = $offset + 1;

while ($row = $res->fetch_assoc()) {
    $orders[] = [
        'sn'           => $sn++,
        'order_date'   => $row['trans_date'],
        'total_orders' => (int)$row['total_orders'],
        'total_amount' => number_format((float)$row['total_amount'], 2, '.', ''),
        'profit'       => number_format((float)$row['total_profit'], 2, '.', ''),
        'wassce' => [
            'orders' => (int)$row['wassce_orders'],
            'amount' => number_format((float)$row['wassce_amount'], 2, '.', ''),
        ],
        'bece' => [
            'orders' => (int)$row['bece_orders'],
            'amount' => number_format((float)$row['bece_amount'], 2, '.', ''),
        ],
    ];
}
$dataStmt->close();

// ====== Pagination ======
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-secondary pagination-link $active' data-page='{$i}'>{$i}</button>";
    }
}

// ====== Output ======
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination,
    'meta' => [
        'page' => $page,
        'recordsPerPage' => $recordsPerPage,
        'totalRecords' => $totalRecords,
        'totalPages' => $totalPages,
        'dateFilter' => $date,
    ],
], JSON_UNESCAPED_UNICODE);
?>
