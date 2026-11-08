<?php
include '../database/dbconfig.php';

$limit = 10; // rows per page
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$start = ($page - 1) * $limit;

// Search filter
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

$where = '';
if (!empty($search)) {
    $where = "WHERE agent_name LIKE '%$search%' OR amount LIKE '%$search%' OR balance LIKE '%$search%' OR total_payouts LIKE '%$search%' OR payout_date LIKE '%$search%'";
}

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM agents_payouts $where";
$count_result = mysqli_query($con, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Fetch records
$query = "SELECT * FROM agents_payouts $where ORDER BY payout_date DESC LIMIT $start, $limit";
$result = mysqli_query($con, $query);

$output = '';
$sn = $start + 1;

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= "<tr class='align-middle text-center'>
            <td>{$sn}</td>
            <td>".htmlspecialchars($row['agent_name'])."</td>
            <td>".htmlspecialchars($row['amount'])."</td>
            <td>".htmlspecialchars($row['balance'])."</td>
            <td>".htmlspecialchars($row['total_payouts'])."</td>
            <td>".htmlspecialchars($row['payout_date'])."</td>
        </tr>";
        $sn++;
    }
} else {
    $output = "<tr><td colspan='7' class='text-center text-muted'>No records found</td></tr>";
}

// Pagination
$pagination = '';
if ($total_pages > 1) {
    $pagination .= "<nav><ul class='pagination justify-content-center'>";
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $active'><a href='#' class='page-link' data-page='$i'>$i</a></li>";
    }
    $pagination .= "</ul></nav>";
}

echo json_encode(['table' => $output, 'pagination' => $pagination]);
