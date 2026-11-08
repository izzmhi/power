<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

$success = array();
$errors = array();

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
			<?php $page = 'orders' ?>
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
				    
			<div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-4">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="print.php" class="text-success" style="font-size: 1rem; text-align:center;"><i class="fa fa-file-excel-o" aria-hidden="true"></i>Print</a>
						</div>
					</div>
				</div>
		
			</div>

					<!-- Row start -->
		  
                                <?php if(count($success)>0): ?>
                                   <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                   <?php foreach($success as $succes): ?>
                                   <div><?php echo $succes; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 


					<!-- Row start -->
				<div class="row gutters ">
					<h6>Orders</h6>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-12 col-12">
	                <div class="mb-2">
        <input type="text" id="search" class="form-control" placeholder="Search reference id, customer, telephone">
    </div>
	       </div>
	        </div>
	       
	       <div class="row">

	        <div class="col-lg-3 col-6">
	                <div class="mb-2">
        <select id="purpose" class="form-control">
            <option value="">Purpose</option>
            <?php
                include 'database/dbconfig.php';
                $select = "SELECT DISTINCT purpose FROM payments";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['purpose']; ?>"><?php echo $row['purpose']; ?></option>
                        <?php
                    }
                } else {
                    ?>
                    <option value="">No Order is Available</option>
                    <?php
                }
                ?>
        </select>
    </div>
	        </div>

	   <div class="col-lg-3 col-6">
            <div class="mb-2">
                <input type="date" id="date" class="form-control">
            </div>
	   </div>
	    </div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Telephone</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Profit</th>
                <th>Date</th>
                <th>Time</th>
                <th>Serial</th>
                <th>Pins</th>
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
			
		</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;

    // === Load Table Data ===
    function loadTableData() {
        const search = $('#search').val();
        const purpose = $('#purpose').val();
        const date = $('#date').val();

        $('.loading-overlay').show();

        $.ajax({
            url: 'orders_search.php',
            method: 'GET',
            data: {
                search: search,
                purpose: purpose,
                date: date,
                page: currentPage,
                recordsPerPage: recordsPerPage
            },
            dataType: 'json',
            success: function(response) {
                $('#table-body').empty();

                if (response.orders && response.orders.length > 0) {
                    $.each(response.orders, function(index, order) {
                        $('#table-body').append(`
                            <tr>
                                <th scope="row">${order.sn}</th>
                                <td>${order.reference_id}</td>
                                <td>${order.customer}</td>
                                <td>${order.telephone}</td>
                                <td>${order.purpose}</td>
                                <td>GHS ${order.amount}</td>
                                <td>GHS ${order.profit}</td>
                                <td>${order.order_date}</td>
                                <td>${order.order_time}</td>
                                <td>${order.serial}</td>
                                <td>${order.pin}</td>
                            </tr>
                        `);
                    });

                    generatePaginationLinks(response.pagination);
                } else {
                    $('#table-body').append(`
                        <tr>
                            <td colspan="11" style="text-align:center; font-weight:bolder">
                                No Orders Found
                            </td>
                        </tr>
                    `);
                    $('#pagination-links').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#table-body').html(`
                    <tr>
                        <td colspan="11" style="text-align:center; font-weight:bolder; color:red;">
                            Error loading data
                        </td>
                    </tr>
                `);
            },
            complete: function() {
                $('.loading-overlay').hide();
            }
        });
    }

    // === Pagination Handler ===
    function generatePaginationLinks(pagination) {
        $('#pagination-links').html(pagination);
        $('.pagination-link').on('click', function(e) {
            e.preventDefault();
            const pageNum = $(this).data('page');
            currentPage = pageNum;
            loadTableData();
        });
    }

    // === Filter Listeners ===
    $('#search, #purpose, #date').on('input change', function() {
        currentPage = 1;
        loadTableData();
    });

    // === Manual Page Buttons (Optional) ===
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

    // === Initial Load ===
    loadTableData();
});
</script>


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