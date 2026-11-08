<?php
session_start();
include 'database/dbconfig.php';

// === GLOBAL ERROR LOGGER ===
function log_error($message, $context = [])
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    $file = $logDir . '/orders.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    file_put_contents($file, "[$timestamp] $message | $contextJson" . PHP_EOL, FILE_APPEND);
}

try {
    // === Input Sanitization ===
    $purpose = isset($_GET['purpose']) ? trim($_GET['purpose']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $recordsPerPage = isset($_GET['recordsPerPage']) ? max(1, (int)$_GET['recordsPerPage']) : 10;
    $offset = ($page - 1) * $recordsPerPage;

    $where = [];
    $params = [];
    $types = '';

    // === Build WHERE Clauses ===
    if (!empty($search)) {
        $where[] = "(reference_id LIKE ? OR customer LIKE ? OR telephone LIKE ?)";
        $searchLike = "%$search%";
        $params = array_merge($params, [$searchLike, $searchLike, $searchLike]);
        $types .= 'sss';
    }

    if (!empty($purpose)) {
        $where[] = "purpose = ?";
        $params[] = $purpose;
        $types .= 's';
    }

    if (!empty($date)) {
        $where[] = "trans_date = ?";
        $params[] = $date;
        $types .= 's';
    }

    $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

    // === Count Total Records ===
    $countQuery = "SELECT COUNT(*) AS total FROM payments $whereSql";
    $countStmt = $con->prepare($countQuery);
    if ($countStmt === false) {
        throw new Exception("Prepare failed for count query: " . $con->error);
    }

    if ($types) {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $totalRecords = (int)($countRes->fetch_assoc()['total'] ?? 0);
    $countStmt->close();

    $totalPages = $totalRecords > 0 ? ceil($totalRecords / $recordsPerPage) : 1;

    // === Fetch Paginated Data ===
    $dataQuery = "SELECT * FROM payments $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
    $dataStmt = $con->prepare($dataQuery);
    if ($dataStmt === false) {
        throw new Exception("Prepare failed for data query: " . $con->error);
    }

    $bindTypes = $types . 'ii';
    $bindValues = array_merge($params, [$recordsPerPage, $offset]);

    $dataStmt->bind_param($bindTypes, ...$bindValues);
    $dataStmt->execute();
    $res = $dataStmt->get_result();

    $orders = [];
    $sn = $offset + 1;

    while ($row = $res->fetch_assoc()) {
        $orders[] = [
            'sn' => $sn++,
            'reference_id' => $row['reference_id'],
            'customer' => $row['customer'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'amount' => number_format((float)$row['amount'], 2, '.', ''),
            'profit' => number_format((float)$row['profit'], 2, '.', ''),
            'order_date' => $row['trans_date'],
            'order_time' => $row['trans_time'],
            'purpose' => $row['purpose'],
            'serial' => $row['serial_code'],
            'pin' => $row['pin_code'],
        ];
    }
    $dataStmt->close();

    // === Build Pagination Buttons ===
    $pagination = '';
    if ($totalPages > 1) {
        $window = 5;
        $startPage = max(1, $page - floor($window / 2));
        $endPage = min($totalPages, $startPage + $window - 1);

        if ($startPage > 1) {
            $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='1'>&laquo; First</button>";
            $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='" . ($page - 1) . "'>&lsaquo; Prev</button>";
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = ($i == $page) ? 'active' : '';
            $pagination .= "<button class='btn btn-secondary pagination-link $active' data-page='$i'>$i</button>";
        }

        if ($endPage < $totalPages) {
            $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='" . ($page + 1) . "'>Next &rsaquo;</button>";
            $pagination .= "<button class='btn btn-outline-secondary pagination-link' data-page='$totalPages'>Last &raquo;</button>";
        }
    }

} catch (Exception $e) {
    log_error("Exception occurred", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Internal server error. Check logs.']);
    exit();
}

// === If No Exception, Return Data ===
echo json_encode([
    'orders' => $orders,
    'pagination' => $pagination,
    'meta' => [
        'page' => $page,
        'recordsPerPage' => $recordsPerPage,
        'totalRecords' => $totalRecords,
        'totalPages' => $totalPages,
    ]
]);
?>
