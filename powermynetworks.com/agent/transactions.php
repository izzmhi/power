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
$selects = "select * from account_agents where user_id='$user_id' ";
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
			<?php $page = 'transactions' ?>
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
				    
				             <?php
                        if(isset($_GET['success'])){
                            ?>
                            <div class="bg-success text-white"><?php echo $_GET['success'] ?></div>
                            <?php
                        }
                        else if(isset($_GET['error'])){
                            ?>
                            <div class="bg-danger text-white"><?php echo $_GET['error']; ?></div>
                            <?php
                        }
                        ?>

					<!-- Row start -->
				<div class="row gutters ">

						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
									<h6>Transactions</h6>
<div class="table-container">
    <div class="row">
	   <div class="col-lg-12 col-12">
	       <div class="mb-2">
                <input type="text" id="search" class="form-control" placeholder="Search reference id, agent name, beneficiary number">
            </div>
	   </div>
	</div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
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

        function loadTableData(date = '', page = 1, search = 1, agent = 1) {
            $('.loading-overlay').show();
            $.ajax({
                url: 'transactions_search.php',
                method: 'GET',
                data: {
                    date: date,
                    search: search,
                    agent: agent,
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
                                    <td style="display:none">${order.reference_id}</td>
                                    <td style="display:none">${order.amount}</td>
                                    <td style="display:none">${order.wallet}</td>
                                    <td style="display:none">${order.purpose}</td>
                                    <td style="display:none">${order.trans_date}</td>
                               
                                    <td>
                                        ${order.reference_id}<br>
                                       GHS ${order.amount}<br>
                                    </td>
                                    <td>
                                       GHS ${order.wallet}<br>
                                        <strong>${order.purpose}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-style: italic; font-size: 0.9rem; padding-left: 20px; color: gray;">
                                        ${order.trans_date}
                                    </td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination, page);
                    } else {
                        $('#table-body').append('<tr><td colspan="6" style="text-align:center; font-weight:bolder">No Transaction has taken place</td></tr>');
                        $('#pagination-links').empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#table-body').append('<tr><td colspan="6" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
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
                    loadTableData(dateValue, pageNum);
                });
            }
        }

        $('#date').on('change', function() {
            const dateValue = $(this).val();
            currentPage = 1;
            loadTableData(dateValue, currentPage);
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                const dateValue = $('#date').val();
                loadTableData(dateValue, currentPage);
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            const dateValue = $('#date').val();
            loadTableData(dateValue, currentPage);
        });

        // Initial table load
        loadTableData();
    });
</script>

        

		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>

		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>

		<!-- Daterange -->
		<script src="vendor/daterange/daterange.js"></script>
		<script src="vendor/daterange/custom-daterange.js"></script>

		<!-- Chartist JS -->
		<script src="vendor/chartist/js/chartist.min.js"></script>
		<script src="vendor/chartist/js/chartist-tooltip.js"></script>
		<script src="vendor/chartist/js/custom/threshold/threshold.js"></script>
		<script src="vendor/chartist/js/custom/bar/bar-chart-orders.js"></script>

		<!-- jVector Maps -->
		<script src="vendor/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
		<script src="vendor/jvectormap/world-mill-en.js"></script>
		<script src="vendor/jvectormap/gdp-data.js"></script>
		<script src="vendor/jvectormap/custom/world-map-markers2.js"></script>

		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>

		<!-- Main JS -->
		<script src="js/main.js"></script>

	</body>

</html>