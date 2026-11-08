<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from accounts where user_id='$user_id' AND user_type = 'Administrator' ";
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
    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
    $order_status = mysqli_real_escape_string($con, $_POST['status']);

    $update = "update orders set order_status='$order_status' where order_id= '$order_id' ";
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
 
  $delete = "delete FROM orders where order_id='$order_id' ";
  $dquery = mysqli_query($con,$delete);

    if($dquery){
    $success['produce']="Order Deleted Successfully";
   }
   else{
    $errors['produce']="Failed to delete student";
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
				    <h3>Agents Account Summary</h3>
				    		<div class="row gutters mb-2">
				    					    

	<style>
	    .card {
    border: 1px solid rgba(0, 0, 0, 0.125); /* Example border color */
    border-radius: 0.35rem; /* Example border radius */
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.border-left-primary {
    border-left: 5px solid #4e73df; /* Example primary color */
    border-radius: 0.35rem 0 0 0.35rem; /* Example round left border */
}

.shadow {
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1); /* Example shadow */
}

.py-2 {
    padding-top: 0.5rem; /* Example vertical padding */
    padding-bottom: 0.1rem; /* Example vertical padding */
}

.py-2,
.px-2 {
    padding-top: 0.5rem;
	</style>
					    
						<div class="col-lg-4 col-sm-3 col-4">
							<div class="card border-left-primary shadow ">
							    <div class="card-header">
									<div class="card-title" style="">Total Agents</div>
								</div>
								<div class="card-body">
<?php
include '../database/dbconfig.php';
$select = "SELECT COUNT(*) AS agent_count FROM agents ";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);
if ($row) {
    $agent_count = $row['agent_count'];
    ?>
    <h2><?php echo $agent_count; ?></h2>
    <?php
} else {
    echo '<div>No Agent is registered</div>';
}
?>
								</div>
							</div>
						</div>
						
						<div class="col-lg-4 col-sm-3 col-4">
							<div class="card border-left-primary shadow ">
							    <div class="card-header">
									<div class="card-title">Wallet Balance</div>
								</div>
								<div class="card-body">
<?php
include '../database/dbconfig.php';

$select = "SELECT SUM(wallet) AS wallet_count FROM agents";
$sql = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($sql);

if ($row && isset($row['wallet_count'])) {
    ?>
    <h2><?php echo $row['wallet_count']; ?></h2>
    <?php
} else {
    echo '<div>No Agent is registered or wallet total is zero</div>';
}
?>
								</div>
							</div>
						</div>
						
							<div class="col-lg-4 col-sm-3 col-4">
							<div class="card border-left-primary shadow ">
							    <div class="card-header">
									<div class="card-title">Amounts Received</div>
								</div>
								<div class="card-body">
<?php
include '../database/dbconfig.php';

// Query to get the sum of prices from 'orders' table
$select1 = "SELECT SUM(price) AS wallet_count1 FROM orders";
$sql1 = mysqli_query($con, $select1);
$row1 = mysqli_fetch_assoc($sql1);
$wallet_count1 = $row1 && isset($row1['wallet_count1']) ? $row1['wallet_count1'] : 0;

// Query to get the sum of prices from 'orders_agent' table
$select2 = "SELECT SUM(price) AS wallet_count2 FROM orders_agent";
$sql2 = mysqli_query($con, $select2);
$row2 = mysqli_fetch_assoc($sql2);
$wallet_count2 = $row2 && isset($row2['wallet_count2']) ? $row2['wallet_count2'] : 0;

// Calculate the total sum of both tables
$total_wallet_count = $wallet_count1 + $wallet_count2;

// Display the total
if ($total_wallet_count > 0) {
    ?>
    <h2><?php echo $total_wallet_count; ?></h2>
    <?php
} else {
    echo '<div>No Agent is registered or wallet total is zero</div>';
}
?>
								</div>
							</div>
						</div>
	
		
					</div>
				    

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
					<h4>Daily Orders and Amount</h4>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-8 col-8">
	                <div class="mb-2">
        <input type="text" id="search" class="form-control" placeholder="Search reference id, agent name, beneficiary number">
    </div>
	       </div>
	       	   <div class="col-lg-4 col-4">
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
                <th>Date</th>
                <th>Total Orders</th>
                <th>Total Amount</th>
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
    <h4>Top Performing Agents</h4>
    <div class="col-xl-12 col-sm-12 col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="basicExample" class="table custom-table table-sm">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Agent</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
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
        agent_tel, 
        COUNT(*) AS total_orders,
        SUM(price) AS total_amount
    FROM 
        orders_agent 
    GROUP BY 
        agent_name, agent_email, agent_tel 
    ORDER BY 
        total_orders DESC
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
            <td style="text-align:left;"><?php echo $row['agent_tel']; ?></td>
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
<!-- Row start -->
<div class="row gutters">
    <h4>Top Performing Customers</h4>
    <div class="col-xl-12 col-sm-12 col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="basicExample" class="table custom-table table-sm">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
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
        email, 
        beneficiary, 
        COUNT(*) AS total_orders,
        SUM(price) AS total_amount
    FROM 
        orders
    GROUP BY 
        email, beneficiary 
    ORDER BY 
        total_orders DESC
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
            <td style="text-align:left;"><?php echo $row['email']; ?></td>
            <td style="text-align:left;"><?php echo $row['beneficiary']; ?></td>
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
            const date = $('#date').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'daily_search.php',
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
                                    <td>${order.total_orders}</td>
                                    <td>${order.total_amount}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="4" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
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