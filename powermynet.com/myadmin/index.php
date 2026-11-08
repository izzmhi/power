<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}


?>

<!doctype html>
<html lang="en">
	<head>
		<!--Head-->
		<?php include 'parts/head.php'; ?> 
	</head>
	<body>
		<!-- Page wrapper start -->
		<div class="page-wrapper">
			<!-- Sidebar wrapper start -->
			<nav id="sidebar" class="sidebar-wrapper">
			<!-- side-->
			<?php $page = 'home' ?>
			<?php include 'parts/side.php'; ?> 
			</nav>
			<!-- Sidebar wrapper end -->
			<!-- Page content start  -->
			<div class="page-content">
				<!-- Header start -->
				<header class="header">
					<!-- header-->
					<?php include 'parts/header.php'; ?> 
				</header>
				<!-- Header end -->
				<!-- Page header start -->
				<div class="page-header">
						<!-- Nav-->
						<?php include 'parts/nav.php'; ?> 
				</div>
				<!-- Page header end -->
				<!-- Main container start -->
				<div class="main-container">

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card shadow-lg border-left-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-bar-chart" aria-hidden="true"></i> Other Accounts</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <!-- Agents -->
                    <div class="col-md-6 border-end">
                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                          <a href="checker">WAEC Checker</a>
                    </div>

                    <!-- Wallet -->
                    <div class="col-md-4 border-end">
                        <i class="fa fa-shopping-basket" aria-hidden="true"></i>
                        <a href="">Online Shop</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card shadow-lg border-left-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-bar-chart" aria-hidden="true"></i> Account Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <!-- Agents -->
                    <div class="col-md-4 border-end">
                        <i class="fa fa-users fa-2x text-primary mb-2"></i>
                        <h6>Agents</h6>
                        <p>
                            <?php
                            include '../database/dbconfig.php';
                            $select = "SELECT COUNT(*) AS agent_count FROM agents";
                            $sql = mysqli_query($con, $select);
                            $row = mysqli_fetch_assoc($sql);
                            echo $row ? "<strong>{$row['agent_count']}</strong>" : "<span>No Agents</span>";
                            ?>
                        </p>
                    </div>

                    <!-- Wallet -->
                    <div class="col-md-4 border-end">
                        <i class="fa fa-wallet fa-2x text-success mb-2"></i>
                        <h6>Total Wallet</h6>
                        <p>
                            <?php
                            $select = "SELECT SUM(wallet) AS wallet_count FROM agents";
                            $sql = mysqli_query($con, $select);
                            $row = mysqli_fetch_assoc($sql);
                            echo $row && isset($row['wallet_count']) ? "<strong>GHS " . number_format($row['wallet_count'], 2) . "</strong>" : "<span>No Wallet Info</span>";
                            ?>
                        </p>
                    </div>

                    <!-- Revenue -->
                    <div class="col-md-4">
                        <i class="fa fa-money-bill-wave fa-2x text-warning mb-2"></i>
                        <h6>Revenue</h6>
                        <p>
                            <?php
                            $select1 = "SELECT SUM(price) AS wallet_count1 FROM special_offers_orders";
                            $sql1 = mysqli_query($con, $select1);
                            $row1 = mysqli_fetch_assoc($sql1);
                            $wallet_count1 = $row1['wallet_count1'] ?? 0;

                            $select2 = "SELECT SUM(price) AS wallet_count2 FROM orders_agent";
                            $sql2 = mysqli_query($con, $select2);
                            $row2 = mysqli_fetch_assoc($sql2);
                            $wallet_count2 = $row2['wallet_count2'] ?? 0;

                            $total_wallet_count = $wallet_count1 + $wallet_count2;

                            echo $total_wallet_count > 0
                                ? "<strong>GHS " . number_format($total_wallet_count, 2) . "</strong>"
                                : "<span>No Revenue</span>";
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

					
<?php
include '../database/dbconfig.php';
$cards = [
    ['title' => 'Big Time Orders', 'table' => 'special_offers_orders', 'field' => 'order_status', 'icon' => 'fa-star'],
    ['title' => 'Afa', 'table' => 'afa_registration', 'field' => 'register_status', 'icon' => 'fa-id-card'],
];

$pending_data = [];

foreach ($cards as $card) {
    $query = "SELECT COUNT(*) AS pending_count FROM {$card['table']} WHERE {$card['field']} = 'Pending'";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    $pending_count = $row ? (int)$row['pending_count'] : 0;
    $text_class = $pending_count > 0 ? 'text-danger' : 'text-success';

    $pending_data[] = [
        'title' => $card['title'],
        'count' => $pending_count,
        'class' => $text_class,
        'icon' => $card['icon']
    ];
}
?>

<!--
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card shadow-lg border-left-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-bell me-2"></i> Pendings</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <?php foreach ($pending_data as $item): ?>
                        <div class="col-md-4 border-end">
                            <i class="fa <?php echo $item['icon']; ?> fa-2x text-primary mb-2"></i>
                            <h6><?php echo $item['title']; ?></h6>
                            <p class="<?php echo $item['class']; ?>">Pendings: <?php echo $item['count']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
-->

					<!-- Row start -->
				<div class="row gutters ">
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
							     <div class="card-header bg-primary text-white">
                                     <h5 class="mb-0"><i class="fa fa-calculator" aria-hidden="true"></i> Accounts</h5>
                                </div>
								<div class="card-body">
							
	<div class="table-container">

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Date</th>
                <th>MTN</th>
                <th>AT</th>
                <th>TELECEL</th>
                <th>Big Time</th>
                <th>Total Orders</th>
                <th>Total Amount</th>
                <th>Profit</th>
            </tr>
            </thead>
            <tbody id="table-body">
                <!-- Data will be populated here -->
            </tbody>
        </table>
        <div id="pagination-controls" class="mt-2">
            <button class="btn btn-info" id="prev-page">Previous</button>
            <span id="pagination-links"></span>
            <button class="btn btn-info" id="next-page">Next</button>
        </div>
    </div>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>


<!--spinner start-->
    <style>
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0, 0, 0, 0.1);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .loading-text {
            margin-top: 10px;
            font-size: 18px;
            color: #007bff;
        }
    </style>
							
							
						</div>

					</div>
				</div>
			</div>
			

<?php
include '../database/dbconfig.php';
?>
<!-- Row start -->
<div class="row gutters">
    <div class="col-xl-12 col-sm-12 col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-address-book" aria-hidden="true"></i> Top Performing Agents</h5>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="basicExample" class="table custom-table table-sm">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Agent</th>
                                    <th>Email</th>
                                    <th>Total Orders</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody class="display" style="text-align:left">
                              <?php
include '../database/dbconfig.php';

// Query to aggregate and rank agents by total orders and sum of order amounts
$select = "
    SELECT 
        agent_name, 
        agent_email, 
        COUNT(*) AS total_orders,
        SUM(price) AS total_amount
    FROM (
        SELECT agent_name, agent_email, price FROM orders_agent
        UNION ALL
        SELECT agent_name, agent_email, price FROM special_offers_orders
    ) AS all_orders
    GROUP BY 
        agent_name, agent_email
    ORDER BY 
        total_amount DESC
        
        LIMIT 50
";


// Execute the query
$sql = mysqli_query($con, $select);

// Check if the query was successful
if ($sql === false) {
    // Output the error for debugging
    die('Error: ' . mysqli_error($con));
}

$check = mysqli_num_rows($sql) > 0;

// Initialize a row counter
$rowNumber = 1;

if ($check) {
    while ($row = mysqli_fetch_assoc($sql)) {
        ?>
        <tr>
            <th scope="row"><?php echo $rowNumber; ?></th>
            <td style="text-align:left;"><?php echo $row['agent_name']; ?></td>
            <td style="text-align:left;"><?php echo $row['agent_email']; ?></td>
            <td style="text-align:left;"><?php echo $row['total_orders']; ?></td>
            <td style="text-align:left;"><?php echo number_format($row['total_amount'], 2); ?></td>
        </tr>
        <?php
        $rowNumber++;
    }
} else {
    ?>
    <tr>
        <td colspan="6" style="text-align:center; font-weight:bolder">No Orders Available</td>
    </tr>
    <?php
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
include '../database/dbconfig.php';
?>

			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        let currentPage = 1;
        const recordsPerPage = 10;

        function loadTableData() {
            const search = $('#search').val();
            const date = $('#date').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'stats_search.php',
                method: 'GET',
                data: {
                    search: search,
                    date: date,
                    page: currentPage,
                    recordsPerPage: recordsPerPage
                },
                dataType: 'json',
success: function(response) {
    $('#table-body').empty();
    if (response.orders.length > 0) {
        $.each(response.orders, function(index, order) {
            $('#table-body').append(`
                <tr>
                    <th scope="row">${order.sn}</th>
                    <td>${order.order_date}</td>
                    <td>${order.mtn.orders} (${order.mtn.amount})</td>
                    <td>${order.at.orders} (${order.at.amount})</td>
                    <td>${order.telecel.orders} (${order.telecel.amount})</td>
                    <td>${order.big_time.orders} (${order.big_time.amount})</td>
                    <td>${order.total_orders}</td>
                    <td>${order.total_amount}</td>
                    <td>${order.profit}</td>
                </tr>
            `);
        });
        generatePaginationLinks(response.pagination);
    } else {
        $('#table-body').append('<tr><td colspan="7" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
        $('#pagination-links').empty();
    }
},
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#table-body').append('<tr><td colspan="4" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
                },
                complete: function() {
                    $('.loading-overlay').hide();
                }
            });
        }

        function generatePaginationLinks(pagination) {
            $('#pagination-links').empty();
            if (pagination) {
                $('#pagination-links').html(pagination);
                $('.pagination-link').on('click', function(e) {
                    e.preventDefault();
                    const pageNum = $(this).data('page');
                    currentPage = pageNum;
                    loadTableData();
                });
            }
        }

        $('#search, #date').on('input change', function() {
            currentPage = 1;
            loadTableData();
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadTableData();
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadTableData();
        });

        loadTableData(); // Initial load
    });
</script>

		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>