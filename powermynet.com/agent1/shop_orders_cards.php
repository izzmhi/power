<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
// =======================
// ERROR LOGGING FUNCTION
// =======================
function log_error($msg) {
    $file = __DIR__ . "/orders_debug.log";
    file_put_contents($file, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

// =======================
// SESSION CHECK
// =======================
if (!isset($_SESSION['user_id'])) {
    log_error("ERROR: Session 'user_id' NOT SET!");
    exit("Not logged in.");
}

$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
log_error("SESSION USER_ID: $user_id");

// =======================
// INPUTS
// =======================
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';
$date   = isset($_POST['date']) ? mysqli_real_escape_string($con, $_POST['date']) : '';
$page   = isset($_POST['page']) ? (int)$_POST['page'] : 1;

log_error("INPUT RECEIVED -> search='$search', date='$date', page=$page");

// =======================
// PAGINATION CONFIG
// =======================
$limit = 10;
$offset = ($page - 1) * $limit;
log_error("Pagination -> limit=$limit, offset=$offset");

// =======================
// BASE QUERY
// =======================
$query = "SELECT * FROM orders_agent WHERE agent_id='$user_id' AND origin='SHOP'";
log_error("BASE QUERY INITIALIZED: $query");

// =======================
// SEARCH FILTER
// =======================
if ($search !== '') {
    $query .= " AND (reference_id LIKE '%$search%' OR beneficiary LIKE '%$search%')";
    log_error("SEARCH FILTER APPLIED: $search");
}

// =======================
// DATE FILTER
// =======================
if ($date !== '') {
    $query .= " AND order_date='$date'";
    log_error("DATE FILTER APPLIED: $date");
}

log_error("QUERY BEFORE COUNT: $query");

// =======================
// COUNT QUERY
// =======================
$countQuery = str_replace("SELECT *", "SELECT COUNT(*) AS total", $query);
log_error("COUNT QUERY: $countQuery");

$countResult = mysqli_query($con, $countQuery);

if (!$countResult) {
    log_error("COUNT QUERY ERROR: " . mysqli_error($con));
}

$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

log_error("COUNT RESULT -> totalRows=$totalRows, totalPages=$totalPages");

// =======================
// FINAL PAGINATION QUERY
// =======================
$query .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
log_error("FINAL QUERY: $query");

// =======================
// EXECUTE MAIN QUERY
// =======================
$result = mysqli_query($con, $query);

if (!$result) {
    log_error("MAIN QUERY ERROR: " . mysqli_error($con));
    exit("Error loading data.");
}

$rowCount = mysqli_num_rows($result);
log_error("MAIN QUERY RESULT -> rows returned: $rowCount");

// =======================
// DISPLAY RESULTS
// =======================
if ($rowCount > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        log_error("ROW FETCHED: reference=" . $row['reference_id'] . 
                  ", network=" . $row['network'] . 
                  ", beneficiary=" . $row['beneficiary'] .
                  ", price=" . $row['price']);

?>
        <div class="row p-2">
            <div class="col-12">
                <div class="card shadow-lg rounded-lg border-0">
                    <div class="card-body">

                        <div class="row py-1">
                            <div class="col-7">
                                <span class="text-primary font-weight-bold"><?= $row['reference_id'] ?></span>
                            </div>
                            <div class="col-5 text-right">
                                <span class="badge badge-info p-2"><?= $row['gig'] ?>GB - <?= $row['network'] ?></span>
                            </div>
                        </div>

                        <div class="row py-1">
                            <div class="col-6">
                                <span class="text-secondary font-weight-bold"><?= $row['beneficiary'] ?></span>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-primary p-2">GHS<?= $row['price'] ?></span>
                            </div>
                        </div>

                        <div class="row py-1">
                            <div class="col-4"></div>
                            <div class="col-8 text-right">
                                <span class="badge badge-success p-2"><?= $row['order_status'] ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-right">
                                <span class="text-muted" style="font-size: 12px;">
                                    <?= $row['order_time'] ?>, <?= $row['order_date'] ?>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
<?php
    }

} else {

    log_error("NO RESULTS FOUND FOR THIS QUERY.");
    echo "<div class='text-center text-muted p-3'>No results found.</div>";
}

// =======================
// PAGINATION LOG
// =======================
log_error("GENERATING PAGINATION BUTTONS");

// =======================
// PAGINATION BUTTONS
// =======================
echo "<div class='pagination-wrapper'>";

$range = 2;

// First page
if ($page > ($range + 1)) {
    echo "<button onclick='loadData(1)' class='btn btn-primary btn-sm'>1</button>";
    echo "<span class='mx-1'>...</span>";
}

// Middle pages
for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++) {
    $active = ($i == $page) ? "btn-dark" : "btn-primary";
    echo "<button onclick='loadData($i)' class='btn $active btn-sm'>$i</button>";
}

// Last page
if ($page < ($totalPages - $range)) {
    echo "<span class='mx-1'>...</span>";
    echo "<button onclick='loadData($totalPages)' class='btn btn-primary btn-sm'>$totalPages</button>";
}

echo "</div>";

log_error("===== REQUEST COMPLETED SUCCESSFULLY =====");

?>
