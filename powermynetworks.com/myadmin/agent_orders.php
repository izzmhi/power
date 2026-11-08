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

if(isset($_POST['update_mtn'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $new_status = "Delivered";
    $old_status = "Processing";
    $network = "MTN";
    $update = "update orders_agent set order_status='$new_status' where order_status='$old_status' AND network='$network' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
      $success['produce']="Status changed successfully";
      }
   
    else{
      $errors['produce']="Status update failed";
       }
}

if(isset($_POST['update_at'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $new_status = "Delivered";
    $old_status = "Processing";
    $network = "AT";
    $update = "update orders_agent set order_status='$new_status' where order_status='$old_status' AND network='$network' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
      $success['produce']="Status changed successfully";
      }
   
    else{
      $errors['produce']="Status update failed";
       }
}

if(isset($_POST['update_telecel'])){
    $order_status = "Delivered";
    $network = "TELECEL";
    $old_status = "Processing";
    // Step 1: Fetch orders before updating
    $select = "SELECT * FROM orders_agent WHERE network = ? AND order_status = ?";
    $stmt = mysqli_prepare($con, $select);
    mysqli_stmt_bind_param($stmt, "ss", $network, $old_status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orders = []; // Store order details
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = [
            'recipient_number' => $row['beneficiary'],
            'gig' => $row['gig']
        ];
    }
    // Step 2: Update order status only if orders exist
    if(count($orders) > 0) {
        $update = "UPDATE orders_agent SET order_status = ? WHERE network = ? AND order_status = ?";
        $stmt1 = mysqli_prepare($con, $update);
        mysqli_stmt_bind_param($stmt1, "sss", $order_status, $network, $old_status);
        mysqli_stmt_execute($stmt1);
        if(mysqli_stmt_affected_rows($stmt1) > 0) {
            // Step 3: Prepare bulk SMS data
        } else {
            $errors['produce'] = "No orders were updated.";
        }
    } else {
        $errors['produce'] = "No orders found with status 'Processing'.";
    }
}

if(isset($_POST['delete'])){
  $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
  $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
  $delete = "delete FROM orders_agent where reference_id='$reference_id' AND agent_id='$agent_id'";
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
    $order_status = mysqli_real_escape_string($con, $_POST['order_status']);
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    $update = "update orders_agent set order_status='$order_status' where reference_id='$reference_id'";
    $uquery = mysqli_query($con,$update);
    if($uquery){
      $success['produce']="Order Status Changed Successfully";
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
			<?php $page = 'agent_orders' ?>
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
				
					    			<!--update-->
		<div class="modal fade" id="modalDeleteForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Delete Order</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
					    <div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
								  <input type="text" class="form-control" id="d_reference_id" name="reference_id" readonly required>
								</div>
						    </div>
						</div>
						
						<div class="row px-2">
					        <div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">First name:</label>
									<input type="text" class="form-control" id="d_first" name="first_name" readonly>
								</div>
							</div>
							 <div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Last name:</label>
									<input type="text" class="form-control" id="d_last" name="last_name" readonly>
								</div>
							</div>
					    </div>
						
						<div class="row px-2">
							<div class="col-sm-5 col-12">
								<div class="form-group">
									<label for="contactName">Email:</label>
										<input type="text" class="form-control" id="d_email" name="email" readonly>
								</div>
							</div>
							<div class="col-sm-3 col-12">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
										<input type="text" class="form-control" id="d_telephone" name="telephone" readonly>
								</div>
							</div>
							<div class="col-sm-4 col-12">
								<div class="form-group">
									<label for="contactName">Recipient:</label>
										<input type="text" class="form-control" id="d_beneficiary" name="beneficiary" readonly>
								</div>
							</div>
						</div>
						
						<div class="row px-2">
						<div class="col-sm-4 col-12">
								<div class="form-group">
									<label for="contactName">Network:</label>
									<input type="text" class="form-control" id="d_network" name="network" readonly>
								</div>
							</div>
							<div class="col-sm-4 col-12">
								<div class="form-group">
									<label for="contactName">Gig:</label>
									<input type="tel" class="form-control" id="d_gig" name="gig" readonly>
								</div>
							</div>    
								<div class="col-sm-4 col-12">
								<div class="form-group">
									<label for="contactName">Price:</label>
									<input type="tel" class="form-control" id="d_price" name="price" readonly>
								</div>
							</div> 
						</div>
						
						<hr>
						  <div class="row px-2">
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<button type="submit" name="delete" class="btn btn-success btn-block">Delete</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
		
		<!--update-->
					<!--update-->
			<div class="modal fade" id="modalUpdateForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Order status</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
						<input type="hidden" class="form-control" id="u_reference" name="reference_id" readonly>
						<div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent name:</label>
									<input type="text" class="form-control" id="u_agent_name" readonly>
								</div>
							</div>
					    </div>
						<div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Agent Tel:</label>
									<input type="text" class="form-control" id="u_agent_tel" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Recipient:</label>
									<input type="text" class="form-control" id="u_beneficiary"  readonly>
								</div>
							</div>
					    </div>
						<div class="row px-2">
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Network</label>
	                                  <input type="text" class="form-control" id="u_network" name="network"  readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Order status</label>
									    <select name="order_status" class="form-control" id="u_order_status" required>
											  <option value="">Select Order Status</option>
											  <option value="Pending">Pending</option>
								              <option value="Processing">Processing</option>
									       	  <option value="Delivered">Delivered</option>
											  <option value="Cancelled">Cancelled</option>
										</select>
								</div>
							</div>
						</div>
		
						<hr>
						  <div class="row px-2">
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<button type="submit" name="status" class="btn btn-success btn-block">Update</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>

				<!-- Main container start -->
				<div class="main-container">
				    
			<div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-4">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="print_agent_mtn.php" class="text-success" style="font-size: 1rem; text-align:center;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> MTN</a>
						</div>
					</div>
				</div>
			
				<div class="col-lg-4 col-sm-4 col-3">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="print_agent_at.php" class="text-info" style="font-size: 1rem; text-align:center;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> AT</a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-sm-4 col-5">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="print_agent_telecel.php" class="text-secondary" style="font-size: 1rem; text-align:center;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> TELECEL</a>
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
                                
                                    <?php if(isset($_GET['success'])){
                                      header("Location: agent_orders.php");  
                                    }
                                    ?>


								<?php if(isset($_GET['error'])){
								  ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <div><?php echo $_GET['error']; ?></div>
                                   </div>
                                <?php 
								    
								}
                                ?> 

					<!-- Row start -->
				<div class="row gutters ">
					<h6>Agents Orders</h6>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-12 col-12">
	                <div class="mb-2">
        <input type="text" id="search" class="form-control" placeholder="Search reference id, agent name, beneficiary number">
    </div>
	       </div>
	        </div>
	       
	       <div class="row">
	        <div class="col-lg-3 col-6">
	                <div class="mb-2">
        <select id="agent" class="form-control">
            <option value="">Select Agent</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT agent_name FROM orders_agent ORDER BY agent_name ASC";
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
	        <div class="col-lg-3 col-6">
	                <div class="mb-2">
        <select id="network" class="form-control">
            <option value="">Network</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT network FROM orders_agent ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['network']; ?>"><?php echo $row['network']; ?></option>
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
        <select id="status" class="form-control">
            <option value="">Order Status</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT order_status FROM orders_agent ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['order_status']; ?>"><?php echo $row['order_status']; ?></option>
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

	    	<div class="row gutters mb-2">
                <div class="col-lg-3 col-sm-3 col-6 ml-auto">
					<div class="form-group">
			              <form method="POST" action="" enctype="multipart/form-data">
                            <button type="submit" name="update_telecel" class="btn btn-danger btn-block"><i class="fa fa-check-square" aria-hidden="true"></i> Confirm Telecel Orders</button>
						</form>
					</div>
				</div>
				<div class="col-lg-3 col-sm-3 col-6 ml-auto">
					<div class="form-group">
			              <form method="POST" action="" enctype="multipart/form-data">
                            <button type="submit" name="update_mtn" class="btn btn-warning btn-block"><i class="fa fa-check-square" aria-hidden="true"></i> Confirm MTN Orders</button>
						</form>
					</div>
				</div>
				<div class="col-lg-3 col-sm-3 col-6 ml-auto">
					<div class="form-group">
			              <form method="POST" action="" enctype="multipart/form-data">
                            <button type="submit" name="update_at" class="btn btn-info btn-block"><i class="fa fa-check-square" aria-hidden="true"></i> Confirm AT Orders</button>
						</form>
					</div>
				</div>
				<div class="col-lg-3 col-sm-3 col-6 ml-auto">
					<div class="form-group">
			              <a href="agent_orders.php" class="btn btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</a>
					</div>
				</div>
			</div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Reference</th>
                <th>Agent</th>
                <th>Agent Tel</th>
                <th>Network</th>
                <th>Recipient</th>
                <th>Gig</th>
                <th>Price</th>
                <th>Profit</th>
                <th>Order Date</th>
                <th>Time</th>
                <th>Origin</th>
                <th>Medium</th>
                <th>Status</th>
                <th>Action</th>
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
                url: 'agent_orders_search.php',
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
                                     <td>${order.reference_id}</td>
                                    <td>${order.agent_name}</td>
                                    <td>${order.agent_tel}</td>
                                    <td>${order.network}</td>
                                    <td>${order.beneficiary}</td>
                                    <td>${order.gig}GIG</td>
                                    <td>GHS ${order.price}</td>
                                    <td>GHS ${order.profit}</td>
                                    <td>${order.real_date}</td>
                                    <td>${order.order_time}</td>
                                    <td>${order.origin}</td>
                                    <td>${order.medium}</td>
                                    <td>${order.order_status}</td>
                                    <td>
                                       ${order.actions}
                                    </td>
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
            $('#u_reference').val(data[0]);
            $('#u_agent_name').val(data[1]);
            $('#u_agent_tel').val(data[2]);
            $('#u_network').val(data[3]);
            $('#u_beneficiary').val(data[4]);
            $('#u_gig').val(data[5]);
            $('#u_price').val(data[6]);
            $('#u_order_date').val(data[7]);
            $('#u_order_status').val(data[8]);
            });
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