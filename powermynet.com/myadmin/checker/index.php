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
				    
				    <div class="row mb-3">
				        <div class="col-sm-4 col-6">
				            <a href="../" class="btn btn-info btn-block">Back</a>
				            <a href="buy.php" class="btn btn-info btn-block">Buy</a>
				        </div>
				    </div>
<div class="row">
  <div class="col-lg-12 col-md-12">
    <div class="card shadow-lg border-left-primary">
      <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0">
          <i class="fa fa-bar-chart" aria-hidden="true"></i> CARDS Summary
        </h5>
      </div>

      <div class="card-body">
        <div class="row text-center justify-content-center">
          <!-- Number of Purchases -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>Cards</h6>
              <p class="mb-0 fw-bold">
                <?php
                include 'database/dbconfig.php';
                $select = "SELECT COUNT(*) AS agent_count FROM codes";
                $sql = mysqli_query($con, $select);
                $row = mysqli_fetch_assoc($sql);
                echo $row ? "<strong>{$row['agent_count']}</strong>" : "<span>No Cards Available</span>";
                ?>
              </p>
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>WASSCE</h6>
              <p class="mb-0 fw-bold">
<?php
$select = "SELECT COUNT(reference_id) AS wallet_count FROM codes WHERE package='WASSCE'";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);

if ($row && isset($row['wallet_count'])) {
    echo "<strong>{$row['wallet_count']}</strong>";
} else {
    echo "<span>No WASSCE Cards</span>";
}
?>

              </p>
            </div>
          </div>

          <!-- Total Profit -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>BECE</h6>
              <p class="mb-0 fw-bold">
<?php
$select = "SELECT COUNT(reference_id) AS wallet_count FROM codes WHERE package='BECE'";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);

if ($row && isset($row['wallet_count'])) {
    echo "<strong>{$row['wallet_count']}</strong>";
} else {
    echo "<span>No BECE Cards</span>";
}
?>

              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12 col-md-12">
    <div class="card shadow-lg border-left-primary">
      <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0">
          <i class="fa fa-bar-chart" aria-hidden="true"></i> Remaining Cards
        </h5>
      </div>

      <div class="card-body">
        <div class="row text-center justify-content-center">
          <!-- Number of Purchases -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>Cards</h6>
              <p class="mb-0 fw-bold">
                <?php
                include 'database/dbconfig.php';
                $select = "SELECT COUNT(*) AS agent_count FROM codes WHERE status='FRESH'";
                $sql = mysqli_query($con, $select);
                $row = mysqli_fetch_assoc($sql);
                echo $row ? "<strong>{$row['agent_count']}</strong>" : "<span>No Cards Available</span>";
                ?>
              </p>
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>WASSCE</h6>
              <p class="mb-0 fw-bold">
<?php
$select = "SELECT COUNT(reference_id) AS wallet_count FROM codes WHERE package='WASSCE' AND status='FRESH'";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);

if ($row && isset($row['wallet_count'])) {
    echo "<strong>{$row['wallet_count']}</strong>";
} else {
    echo "<span>No WASSCE Cards</span>";
}
?>

              </p>
            </div>
          </div>

          <!-- Total Profit -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>BECE</h6>
              <p class="mb-0 fw-bold">
<?php
$select = "SELECT COUNT(reference_id) AS wallet_count FROM codes WHERE package='BECE' AND status='FRESH'";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);

if ($row && isset($row['wallet_count'])) {
    echo "<strong>{$row['wallet_count']}</strong>";
} else {
    echo "<span>No BECE Cards</span>";
}
?>

              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12 col-md-12">
    <div class="card shadow-lg border-left-primary">
      <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0">
          <i class="fa fa-bar-chart" aria-hidden="true"></i> Account Summary
        </h5>
      </div>

      <div class="card-body">
        <div class="row text-center justify-content-center">
          <!-- Number of Purchases -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>Orders</h6>
              <p class="mb-0 fw-bold">
                <?php
                include 'database/dbconfig.php';
                $select = "SELECT COUNT(*) AS agent_count FROM payments";
                $sql = mysqli_query($con, $select);
                $row = mysqli_fetch_assoc($sql);
                echo $row ? "<strong>{$row['agent_count']}</strong>" : "<span>No Payment</span>";
                ?>
              </p>
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>Sales</h6>
              <p class="mb-0 fw-bold">
                <?php
                $select = "SELECT SUM(amount) AS wallet_count FROM payments";
                $sql = mysqli_query($con, $select);
                $row = mysqli_fetch_assoc($sql);
                echo $row && isset($row['wallet_count'])
                  ? "<strong>GHS " . number_format($row['wallet_count'], 2) . "</strong>"
                  : "<span>No Revenue</span>";
                ?>
              </p>
            </div>
          </div>

          <!-- Total Profit -->
          <div class="col-4 col-sm-3 col-md-4 mb-3">
            <div class="p-3 border rounded shadow-sm bg-white">
              <h6>Profit</h6>
              <p class="mb-0 fw-bold">
                <?php
                $select1 = "SELECT SUM(profit) AS wallet_count1 FROM payments";
                $sql1 = mysqli_query($con, $select1);
                $row1 = mysqli_fetch_assoc($sql1);
                $wallet_count1 = $row1['wallet_count1'] ?? 0;
                echo $wallet_count1 > 0
                  ? "<strong>GHS " . number_format($wallet_count1, 2) . "</strong>"
                  : "<span>No Profit</span>";
                ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


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
                <th>WASSCE</th>
                <th>BECE</th>
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
                    <td>${order.wassce.orders} (${order.wassce.amount})</td>
                    <td>${order.bece.orders} (${order.bece.amount})</td>
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