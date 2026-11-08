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
    $whereClauses[] = "(reference_id LIKE '%$search%' OR beneficiary LIKE '%$search%')";
}

if ($date) {
    $whereClauses[] = "real_date='$date'";
}

$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM orders_agent $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch orders data for the current page
$query = "SELECT * FROM orders_agent $whereSql  WHERE (network='AirtelTigo' OR network='Telecel') ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Determine the CSS class for the order status
    $statusClass = '';
    if ($row['order_status'] == "Delivered") {
        $statusClass = 'bg-success text-white';
    } elseif ($row['order_status'] == "Cancelled") {
        $statusClass = 'bg-danger text-white';
    } elseif ($row['order_status'] == "Pending") {
        $statusClass = 'bg-warning text-white';
    } else {
        $statusClass = 'bg-secondary text-white';
    }

    // Add the order data to the orders array
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'order_id' => $row['order_id'],
        'reference_id' => $row['reference_id'],
        'agent_name' => $row['agent_name'],
        'network' => $row['network'],
        'beneficiary' => $row['beneficiary'],
        'gig' => $row['gig'],
        'price' => $row['price'],
        'real_date' => $row['order_date'],
        'order_time' => $row['order_time'],
        'order_status' => "<span style='padding:3px;' class='$statusClass'>{$row['order_status']}</span>", 
        'actions' => '
            <button type="button" class="btn btn-info btn-sm updatebtn" data-id="' . $row['order_id'] . '">
                <i class="fa fa-pencil-square" aria-hidden="true"></i> Update
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
