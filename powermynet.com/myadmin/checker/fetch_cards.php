<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include 'database/dbconfig.php';

/* ======================================================
   ERROR LOGGER FUNCTION
   ====================================================== */
function log_error($message, $context = [])
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $file = $logDir . '/fetch_cards.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextText = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $entry = "[$timestamp] $message " . ($contextText ? "| Context: $contextText" : "") . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND);
}

/* ======================================================
   INPUTS
   ====================================================== */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$package = isset($_GET['package']) ? trim($_GET['package']) : ''; // ✅ correct key
$offset = ($page - 1) * $recordsPerPage;

// Ensure safe defaults
if ($page < 1) $page = 1;
if ($recordsPerPage < 1) $recordsPerPage = 10;

/* ======================================================
   BUILD FILTERS
   ====================================================== */
$where = [];
$params = [];
$types = '';

try {
    // Filter by package (optional)
    if (!empty($package)) {
        $where[] = "package = ?";
        $params[] = $package;
        $types .= 's';
    }

    // Search filter (serial or pin)
    if (!empty($search)) {
        $where[] = "(serial_code LIKE ? OR pin_code LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    // Filter by status
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

    /* ======================================================
       COUNT TOTAL RECORDS
       ====================================================== */
    $countQuery = "SELECT COUNT(*) AS total FROM codes $whereSQL";
    $countStmt = $con->prepare($countQuery);
    if (!$countStmt) {
        throw new Exception("Count Query Prepare Failed: " . $con->error);
    }

    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = (int)($countResult->fetch_assoc()['total'] ?? 0);
    $countStmt->close();

    $totalPages = ($totalRecords > 0) ? ceil($totalRecords / $recordsPerPage) : 1;

    /* ======================================================
       FETCH DATA
       ====================================================== */
    $query = "SELECT * FROM codes $whereSQL ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception("Main Query Prepare Failed: " . $con->error);
    }

    $bindTypes = $types . "ii";
    $bindValues = array_merge($params, [$recordsPerPage, $offset]);

    if (!empty($params)) {
        $stmt->bind_param($bindTypes, ...$bindValues);
    } else {
        $stmt->bind_param("ii", $recordsPerPage, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $cards = [];
    $sn = $offset + 1;

    while ($row = $result->fetch_assoc()) {
        $cards[] = [
            'sn'      => $sn++,
            'package' => htmlspecialchars($row['package']),
            'serial'  => htmlspecialchars($row['serial_code']),
            'pin'     => htmlspecialchars($row['pin_code']),
            'status'  => htmlspecialchars($row['status'])
        ];
    }
    $stmt->close();

    /* ======================================================
       SUCCESS RESPONSE
       ====================================================== */
    echo json_encode([
        'cards' => $cards,
        'meta'  => [
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalRecords' => $totalRecords
        ]
    ]);
} catch (Exception $e) {
    /* ======================================================
       ERROR HANDLING
       ====================================================== */
    log_error("Database Error", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    echo json_encode([
        'error' => true,
        'message' => 'An internal error occurred. Please check logs.',
        'meta' => [
            'currentPage' => $page,
            'totalPages' => 1,
            'totalRecords' => 0
        ]
    ]);
}
?>
