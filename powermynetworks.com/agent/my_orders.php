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
if(isset($_POST['submit'])){
$reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
$select = "SELECT  * FROM orders_agent WHERE reference_id='$reference_id'";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;
if ($check) {
    while($row=mysqli_fetch_array($sql))
    {
     $network = $row['network'];   
    header("Location: check_orders.php?reference_id=$reference_id&network=$network");
    exit();
    }
    } else {
    header("Location: my_orders.php?success=Reference ID not found");
    exit();
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
			<?php $page = 'my_orders' ?>
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
				<!-- Main container start -->
				<div class="main-container">
				    
				    <!-- Modal -->
<div class="modal fade" id="ModalAgent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Check Order Status</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
        <div class="row gutters">
            <label>Order ID</label>
              <input type="text" class="form-control" placeholder="Order ID" aria-label="Email" aria-describedby="email-addon" name="reference_id">
		</div>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
              <div class="loading-overlay text-center py-3" id="spinner_new" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x" style="color:#990099;"></i>
            <p class="mt-2 text-muted">Please Wait...</p>
          </div>
    <div class="modal-footer">
        <button type="submit" name="submit" class="btn btn-block btn-primary" id="setBtn">Check Now</button>
    </div>
</form>

    </div>
  </div>
</div>

    <script>
        $(document).ready(function() {
    $('#setBtn').on('click', function() {
        // Show the spinner
        $('#spinner_new').show();
    });
    });
    </script>



	<!-- Row start -->
	<div class="row gutters ">
		<div class="col-xl-12 col-sm-12 col-12">
			<div class="card">
				<div class="card-body">
				    <h6>Orders</h6>
				<?php
                    if(isset($_GET['success'])){
                        ?>
                    <div class="card">
                      <div class="card-header bg-success text-white">
                        <?php echo $_GET['success'] ?>
                      </div>
                    </div>
                        <?php
                    }
                        else if(isset($_GET['error'])){
                        ?>
                        <div class="bg-danger text-white mt-2"><?php echo $_GET['error']; ?></div>
                    <?php
                    }
                ?>

							<button class="btn btn-primary" style="font-size: 1rem; text-align:center;" data-toggle="modal" data-target="#ModalAgent" id="">Check Status</button>
	<div class="table-container mt-4">  
		<div class="row mb-1">
            <div class="col-sm-7 col-7">
                <input type="text" id="search" class="form-control" placeholder="Search reference id beneficiary number">
            </div>
            <div class="col-sm-5 col-3">
                <input type="date" id="date" class="form-control">
            </div>
        </div>
        
<div class="table-responsive">
  <table id="basicExample" class="table custom-table table-sm">
    <tbody id="table-body" style="text-align:left">
        
    </tbody>
</table>

    <!-- Pagination -->
    <div class="pagination" id="pagination"></div>
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
			
			<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
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

        function loadTableData(search = '', page = 1, network = '', status = '', date = '') {
            $('.loading-overlay').show();
            $.ajax({
                url: 'orders_search.php',
                method: 'GET',
                data: {
                    search: search,
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
    <td>${order.sn}</td>
    <td style='display:none;'>${order.reference_id}</td>
    <td style='display:none;'>${order.agent_name}</td>
    <td style='display:none;'>${order.agent_tel}</td>
    <td style='display:none;'>${order.network}</td>
    <td style='display:none;'>${order.beneficiary}</td>
    <td style='display:none;'>${order.gig}</td>
    <td style='display:none;'>GHS ${order.price}</td>
    <td style='display:none;'>${order.order_status}</td>
    <td style='display:none;'>${order.real_date}</td>
    <td style='display:none;'>${order.order_time}</td>
    <td style='display:none;'>${order.actions}</td>

    <td>
        ${order.reference_id}<br>
        ${order.beneficiary}<br>
        ${order.network}
    </td>
    <td>
        ${order.gig}GIG<br>
        GHS ${order.price}<br>
        <strong>${order.order_status}</strong>
    </td>
</tr>
<tr>
    <td colspan="4" style="font-style: italic; font-size: 0.9rem; padding-left: 20px; color: gray;">
        ${order.real_date} &nbsp;&nbsp;: ${order.order_time}
    </td>
</tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination, page);
                    } else {
                        $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
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
                    const searchValue = $('#search').val();
                    const networkValue = $('#network').val();
                    const statusValue = $('#status').val();
                    const dateValue = $('#date').val();
                    loadTableData(searchValue, pageNum, networkValue, statusValue, dateValue);
                });
            }
        }

        $('#search').on('input', function() {
            const searchValue = $(this).val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        $('#network, #status, #date').on('change', function() {
            const searchValue = $('#search').val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                const searchValue = $('#search').val();
                const networkValue = $('#network').val();
                const statusValue = $('#status').val();
                const dateValue = $('#date').val();
                loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            const searchValue = $('#search').val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        // Initial table load
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