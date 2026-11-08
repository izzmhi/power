<?php
session_start();
include '../database/dbconfig.php';

// Initialize variables with default values or sanitized user input
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$date = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Build the query with optional filters
$whereClauses = [];

if ($search) {
    $whereClauses[] = "(order_date LIKE '%$search%' OR real_date LIKE '%$search%')";
}

if ($date) {
    $whereClauses[] = "real_date = '$date'";
}

$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Query to fetch total count of distinct order_date records matching the search criteria
$countQuery = "SELECT COUNT(DISTINCT order_date) as total FROM (
                SELECT order_date FROM orders
                UNION ALL
                SELECT order_date FROM orders_agent
               ) AS combined_orders $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch sum of prices and count of orders grouped by order_date for the current page
$query = "SELECT order_date, COUNT(*) AS total_orders, SUM(price) AS total_amount 
          FROM (
              SELECT order_date, price FROM orders
              UNION ALL
              SELECT order_date, price FROM orders_agent
          ) AS combined_orders
          $whereSql
          GROUP BY order_date 
          ORDER BY STR_TO_DATE(order_date, '%W, %M %e, %Y') DESC 
          LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $total_amount = $row['total_amount'] ? number_format($row['total_amount'], 2) : '0.00';
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'order_date' => $row['order_date'],
        'total_orders' => $row['total_orders'],
        'total_amount' => $total_amount
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
