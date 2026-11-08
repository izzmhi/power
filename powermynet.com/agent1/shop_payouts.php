<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
    }
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
			<?php $page = 'shop' ?>
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
<div class="row align-items-center">
  <!-- Dashboard Title -->
<div class="col-5">
  <h4 class="page-title d-flex align-items-center">
    <span class="page-title-icon bg-light border-info text-white me-2">
      <i class="mdi mdi-home"></i>
    </span>
    <div class="dropdown bg-info">
      <a class="nav-link dropdown-toggle text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        Menu
      </a>
      <div class="dropdown-menu bg-info text-white">
        <a class="dropdown-item text-white fs-3" href="shop.php">Home</a>
        <a class="dropdown-item text-white fs-3" href="shop_orders.php">Shop Orders</a>
        <a class="dropdown-item text-white fs-3" href="shop_pricing.php">Pricing</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-white fs-3" href="shop_settings.php">Settings</a>
      </div>
    </div>
  </h4>
</div>    
</div>  

					<?php
					if(isset($_GET['success'])){
					    ?>
					    <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
					    <?php
					}
					else if(isset($_GET['error'])){
					    ?>
					    <div class="alert alert-danger" role="alert" style="border-left:5px solid red"><?php echo $_GET['error']; ?> </div>
					    <?php
					}
					?>
					
					                <?php
                                    include '../database/dbconfig.php';
                                    // Sanitize user input
                                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                    // Select query
                                    $select = "SELECT * FROM shop WHERE user_id='$user_id'";
                                    $sql = mysqli_query($con, $select);
                                    // Check if there are any rows
                                    if (mysqli_num_rows($sql) > 0) {
                                        // Fetch each row
                                        while ($row = mysqli_fetch_array($sql)) {
                                            if ($row['status'] == "REJECTED") {
                                                ?>
                                        	<h6>Your SHOP Request for <strong class="text-primary"><?php echo $row['shop']; ?></strong> is <strong class="text-danger">REJECTED</strong></h6>
                                                <?php
                                                }
                                                    elseif ($row['status'] == "PENDING"){
                                                        ?>
                                                        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 shadow-sm rounded" role="alert">
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    Your SHOP request for 
                                                                    <strong class="text-primary"><?php echo $row['shop']; ?></strong> is 
                                                                    <strong class="text-info">PENDING</strong>
                                                                </h6>
                                                            </div>
                                                            <i class="bi bi-hourglass-split text-info fs-4"></i>
                                                        </div>
                                                        <?php
                                                    }
                                                    
                                                    elseif ($row['status'] == "APPROVED"){
                                                    ?>

	<div class="table-container mt-4">
        <div class="row p-1"> 
            <div class="col-sm-12 col-11">
                <input type="date" id="date" class="form-control" placeholder="Date">
            </div>
        </div> 
        <div class="table-responsive">
            <table id="basicExample" class="table custom-table table-sm">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Total Payouts</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody id="table-body" style="text-align:left">
                <!-- Dynamic content will be loaded here -->
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

                                                    
                                                    <?php
                                                    }
                                                ?>
                                                <?php
                                            }
                                                    
                                        }
                                        else{
                                            ?>
                                            <!-- Request a Free SHOP Section -->
<div class="container my-5 p-4 shadow rounded bg-light">
    <h5 class="text-primary fw-bold mb-2">Request a Free SHOP</h5>
    <h6 class="text-muted mb-4">Your Shop will be reviewed and approved within 24hours.</h6>

    <form method="POST" action="process_shop.php" enctype="">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="shop_full" class="form-label">SHOP FULLNAME</label>
                    <input type="text" class="form-control" id="shop_full" name="shop_full" placeholder="Enter full SHOP name here" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="shop" class="form-label">SHOP SHORT NAME</label>
                    <input type="text" class="form-control" id="shop" name="shop" 
                       placeholder="Up to 10 letters, no spaces or numbers" 
                       pattern="^[A-Za-z]{1,10}$" maxlength="10" 
                       title="1 to 10 letters only, no spaces or numbers" required>
                </div>
            </div>
        </div>

        <!-- Spinner Overlay -->
        <div class="spinner-overlay d-none" id="spinner" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 1050; display: flex; align-items: center; justify-content: center;">
            <div class="spinner text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 fw-bold text-primary">Please Wait...</p>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" name="shop_request" class="btn btn-success" id="spin">Request</button>
        </div>
    </form>
</div>
                                            <?php
                                        }
                                    ?>
    

    <script>
        $(document).ready(function() {
    $('#btnMomo').on('click', function() {
        // Show the spinner
        $('#spinner_momo').show();
    });
    });
    </script>

    <script>
        $(document).ready(function() {
    $('#btnWithdraw').on('click', function() {
        // Show the spinner
        $('#spinner_withdraw').show();
    });
    });
    </script>

<br>
<br>
<br>

			<div class="footer">
    	        <?php include 'parts/footer.php'; ?> 
            </div>
		</div>
	</div>
</div>
	
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;

    function loadTableData(search = '', page = 1, date = '') {
        $('.loading-overlay').show();
        $.ajax({
            url: 'shop_payout_search.php',
            method: 'GET',
            data: {
                date: date,
                page: page,
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
                                <td>GHS${order.amount}</td>
                                <td>GHS${order.balance}</td>
                                <td>GHS${order.total_payouts}</td>
                                <td>${order.payout_date}</td>
                            </tr>
                        `);
                    });
                    generatePaginationLinks(response.pagination, page);
                } else {
                    $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">No Payout is Available</td></tr>');
                    $('#pagination-links').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
            },
            complete: function() {
                $('.loading-overlay').hide();
            }
        });
    }

    function generatePaginationLinks(pagination, currentPage) {
        $('#pagination-links').empty();
        if (pagination !== '') {
            $('#pagination-links').html(pagination);
            $('.pagination-link').on('click', function(e) {
                e.preventDefault();
                const pageNum = $(this).data('page');
                const dateValue = $('#date').val();
                currentPage = pageNum;
                loadTableData('', pageNum, dateValue);
            });
        }
    }

    $('#date').on('change', function() {
        currentPage = 1;
        loadTableData('', currentPage, $(this).val());
    });

    // Initial table load
    loadTableData();
});
</script>

	<!-- Page wrapper end -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<!-- Required jQuery first, then Bootstrap Bundle JS -->
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/moment.js"></script>
	<!-- Rating JS -->
	<script src="vendor/rating/raty.js"></script>
	<script src="vendor/rating/raty-custom.js"></script>
	<!-- Main JS -->
	<script src="js/main.js"></script>
	</body>
</html>