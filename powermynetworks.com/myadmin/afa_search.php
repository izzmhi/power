<?php
session_start();
include '../database/dbconfig.php';

// Initialize variables with default values or sanitized user input
$mode = isset($_GET['mode']) ? mysqli_real_escape_string($con, $_GET['mode']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$agent = isset($_GET['agent']) ? mysqli_real_escape_string($con, $_GET['agent']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';
$date = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Build the query with optional filters
$whereClauses = [];

if ($search) {
    $whereClauses[] = "(reference_id LIKE '%$search%' OR fullname LIKE '%$search%' OR email LIKE '%$search%' OR telephone LIKE '%$search%')";
}
if ($agent) {
    $whereClauses[] = "fullname='$agent'";
}

if ($status) {
    $whereClauses[] = "register_status='$status'";
}

if ($date) {
    $whereClauses[] = "real_date='$date'";
}

$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Query to fetch total count of records matching the search criteria
$countQuery = "SELECT COUNT(*) as total FROM afa_registration $whereSql";
$stmt = $con->prepare($countQuery);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Query to fetch orders data for the current page
$query = "SELECT * FROM afa_registration $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ii', $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Determine the CSS class for the order status
    $statusClass = '';
    if ($row['register_status'] == "Registered") {
        $statusClass = 'bg-success text-white';
    } elseif ($row['register_status'] == "Cancelled") {
        $statusClass = 'bg-danger text-white';
    } elseif ($row['register_status'] == "PENDING") {
        $statusClass = 'bg-warning text-white';
    } else {
        $statusClass = 'bg-secondary text-white';
    }

    // Add the order data to the orders array
    $orders[] = [
        'sn' => $offset + count($orders) + 1,
        'reference_id' => $row['reference_id'],
        'fullname' => $row['fullname'],
        'email' => $row['email'],
        'telephone' => $row['telephone'],
        'occupation' => $row['occupation'],
        'id_number' => $row['id_number'],
        'region' => $row['region'],
        'locations' => $row['location'],
        'dob' => $row['dob'],
        'register_date' => $row['register_date'],
        'img_front' => $row['img_front'],
        'img_back' => $row['img_back'],
        'register_status' => "<span style='padding:3px;' class='$statusClass'>{$row['register_status']}</span>", // Apply the CSS class here
        'actions' => '
            <button type="button" class="btn btn-info btn-sm updatebtn" data-id="' . $row['reference_id'] . '">
                <i class="fa fa-pencil-square" aria-hidden="true"></i> Update
            </button>
            <button type="button" class="btn btn-primary btn-sm copybtn" 
            data-fullname="' . $row['fullname'] . '" 
            data-telephone="' . $row['telephone'] . '" 
            data-idnumber="' . $row['id_number'] . '"
            data-location="' . $row['location'] . '"
            data-occupation="' . $row['occupation'] . '"
            data-dob="' . $row['dob'] . '"
            data-region="' . $row['region'] . '">
        <i class="fa fa-solid fa-copy"></i> Copy
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
