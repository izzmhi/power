<?php
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
$selects = "select * from admin where user_id='$user_id'";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];

    }
}

$success = array();
$errors = array();

if(isset($_POST['update'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
    $order_status = mysqli_real_escape_string($con, $_POST['order_status']);

    $update = "update orders_agent set order_status='$order_status' where order_id= '$order_id' ";
    $uquery = mysqli_query($con,$update);

    if($uquery){
      $success['produce']="Status changed successfully";
      }
   
    else{
      $errors['produce']="Status update failed";
       }
      
}

if(isset($_POST['delete'])){
  $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
  $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
 
  $delete = "delete FROM orders_agent where order_id='$order_id' AND agent_id='$agent_id'";
  $dquery = mysqli_query($con,$delete);

    if($dquery){
    $success['produce']="Order Deleted Successfully";
   }
   else{
    $errors['produce']="Failed to delete student";
    }
     
    }
    

if(isset($_POST['status'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $status = "Delivered";
    $update = "update orders_agent set order_status='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
      $success['produce']="All others have been confirmed and Delivered";
      }
    else{
      $errors['produce']="Status update failed";
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
			<?php $page = 'console' ?>
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
					<h6>Orders from Console</h6>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-12 col-12">
	                <div class="mb-2">
        <input type="text" id="search" class="form-control" placeholder="Search order id, agent name, beneficiary number">
    </div>
	       </div>
	        </div>
	       
	       <div class="row">
	        <div class="col-lg-8 col-6">
	                <div class="mb-2">
        <select id="agent" class="form-control">
            <option value="">Select Agent</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT agent_name FROM console_orders ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['agent_name']; ?>"><?php echo $row['agent_name']; ?></option>
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
	        	        <div class="col-lg-4 col-6">
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
                <th>Agent</th>
                <th>Recipient</th>
                <th>Gig</th>
                <th>Order Date</th>
                <th>Time</th>
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

        function loadTableData() {
            const search = $('#search').val();
            const agent = $('#agent').val();
            const network = $('#network').val();
            const status = $('#status').val();
            const mode = $('#mode').val();
            const date = $('#date').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'console_orders_search.php',
                method: 'GET',
                data: {
                    search: search,
                    agent: agent,
                    network: network,
                    status: status,
                    mode: mode,
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
                                    <td style="display:none;">${order.order_id}</td>
                                    <td style="display:none;">${order.console_id}</td>
                                    <td>${order.agent_name}</td>
                                    <td>${order.beneficiary}</td>
                                    <td>${order.package}GIG</td>
                                    <td>${order.real_date}</td>
                                    <td>${order.order_time}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
                        $('#pagination-links').empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
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

        $('#search, #agent, #network, #status, #mode, #date').on('input change', function() {
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


<script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.updatebtn', function() {
        $('#modalUpdateForm').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
        
                $('#u_id').val(data[0]);
                $('#u_agent_name').val(data[1]);
                $('#u_beneficiary').val(data[2]);
                $('#u_network').val(data[3]);
                $('#u_gig').val(data[4]);
                $('#u_price').val(data[5]);
                $('#u_mode').val(data[6]);
                $('#u_order_date').val(data[7]);
                $('#u_order_status').val(data[8]);
            });
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