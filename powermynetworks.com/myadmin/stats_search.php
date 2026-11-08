<?php
session_start();
header('Content-Type: application/json');

require_once '../database/dbconfig.php';

// -------- Input --------
$date            = isset($_GET['date']) ? trim($_GET['date']) : '';
$page            = isset($_GET['page']) ? (int)($_GET['page']) : 1;
$recordsPerPage  = isset($_GET['recordsPerPage']) ? (int)($_GET['recordsPerPage']) : 10;

if ($page < 1) $page = 1;
if ($recordsPerPage < 1) $recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// -------- WHERE (safe) --------
// order_date is stored as a formatted string (e.g., '%W, %M %e, %Y')
// Equality filter is fine; we bind it.
$whereSql = '';
$whereParams = [];
$whereTypes = '';

if ($date !== '') {
    $whereSql = 'WHERE order_date = ?';
    $whereParams[] = $date;
    $whereTypes .= 's';
}

// -------- Total count of distinct dates --------
$countSql = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT DISTINCT order_date
        FROM (
            SELECT order_date FROM orders_agent
            UNION ALL
            SELECT order_date FROM special_offers_orders
        ) d
        $whereSql
    ) t
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

// -------- Data query (grouped by order_date) --------
// We compute both total_amount (SUM(price)) and total_profit (SUM(profit)).
// total_orders is COUNT(*) over combined rows to avoid double-counting.
// Per-network stats look across both tables.
// Big Time (special offers) stats use source_table flag.
$dataSql = "
    SELECT 
        order_date,
        COUNT(*) AS total_orders,
        COALESCE(SUM(price), 0)  AS total_amount,
        COALESCE(SUM(profit), 0) AS total_profit,

        COALESCE(SUM(CASE WHEN network = 'MTN'     THEN 1     ELSE 0 END), 0) AS mtn_orders,
        COALESCE(SUM(CASE WHEN network = 'MTN'     THEN price ELSE 0 END), 0) AS mtn_amount,

        COALESCE(SUM(CASE WHEN network = 'AT'      THEN 1     ELSE 0 END), 0) AS at_orders,
        COALESCE(SUM(CASE WHEN network = 'AT'      THEN price ELSE 0 END), 0) AS at_amount,

        COALESCE(SUM(CASE WHEN network = 'Telecel' THEN 1     ELSE 0 END), 0) AS telecel_orders,
        COALESCE(SUM(CASE WHEN network = 'Telecel' THEN price ELSE 0 END), 0) AS telecel_amount,

        COALESCE(SUM(CASE WHEN source_table = 'special_offers_orders' THEN 1     ELSE 0 END), 0) AS big_time_orders,
        COALESCE(SUM(CASE WHEN source_table = 'special_offers_orders' THEN price ELSE 0 END), 0) AS big_time_amount

    FROM (
        SELECT id, order_date, price, profit, network, 'orders_agent' AS source_table
        FROM orders_agent

        UNION ALL

        SELECT id, order_date, price, profit, network, 'special_offers_orders' AS source_table
        FROM special_offers_orders
    ) combined
    $whereSql
    GROUP BY order_date
    ORDER BY STR_TO_DATE(order_date, '%W, %M %e, %Y') DESC
    LIMIT ? OFFSET ?
";

$dataStmt = $con->prepare($dataSql);

// bind WHERE params (if any), then limit/offset (ii)
if ($whereSql !== '') {
    $bindTypes = $whereTypes . 'ii';
    $dataStmt->bind_param($bindTypes, ...array_merge($whereParams, [$recordsPerPage, $offset]));
} else {
    $dataStmt->bind_param('ii', $recordsPerPage, $offset);
}

$dataStmt->execute();
$res = $dataStmt->get_result();

// -------- Build response rows (numeric values; format in UI) --------
$orders = [];
$sn = $offset + 1;

while ($row = $res->fetch_assoc()) {
    $orders[] = [
        'sn'            => $sn++,
        'order_date'    => $row['order_date'],

        'total_orders'  => (int) $row['total_orders'],
        'total_amount'  => (float) number_format((float) $row['total_amount'], 2, '.', ''),
        'profit'  => (float) number_format((float) $row['total_profit'], 2, '.', ''),

        'mtn' => [
            'orders' => (int) $row['mtn_orders'],
            'amount' => (float) number_format((float) $row['mtn_amount'], 2, '.', ''),
        ],
        'at' => [
            'orders' => (int) $row['at_orders'],
            'amount' => (float) number_format((float) $row['at_amount'], 2, '.', ''),
        ],
        'telecel' => [
            'orders' => (int) $row['telecel_orders'],
            'amount' => (float) number_format((float) $row['telecel_amount'], 2, '.', ''),
        ],
        'big_time' => [
            'orders' => (int) $row['big_time_orders'],
            'amount' => (float) number_format((float) $row['big_time_amount'], 2, '.', ''),
        ],
    ];
}


$dataStmt->close();

// -------- Pagination buttons (simple HTML string) --------
$pagination = '';
if ($totalPages > 1) {
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i === $page) ? 'active' : '';
        $pagination .= "<button class='btn btn-secondary pagination-link $activeClass' data-page='{$i}'>{$i}</button>";
    }
}

// -------- Output --------
echo json_encode([
    'orders'     => $orders,
    'pagination' => $pagination,
    'meta'       => [
        'page'            => $page,
        'recordsPerPage'  => $recordsPerPage,
        'totalRecords'    => $totalRecords,
        'totalPages'      => $totalPages,
        'dateFilter'      => $date,
    ],
], JSON_UNESCAPED_UNICODE);

?>
