<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

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
			<?php $page = 'sms' ?>
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
<!-- Payouts-->
<div class="modal fade" id="payoutModal" tabindex="-1" role="dialog" aria-labelledby="addNewContactLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> <!-- Changed from modal-xl to modal-lg for smaller modal -->
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addNewContactLabel">Payouts History</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="">
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12 col-12">
                    <input type="search" name="search" class="form-control">
                </div>
            </div>
<div class="table-responsive">
    <table class="table table-sm table-hover table-bordered text-sm">
        <thead class="thead-light">
            <tr class="text-center">
                <th>S/N</th>
                <th>Agent</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Total Payouts</th>
                <th>Payout Date</th>
            </tr>
        </thead>
        <tbody id="payoutTable">
            <!-- AJAX Data Here -->
        </tbody>
    </table>
</div>
<div id="paginationLinks"></div>

        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadPayouts(page = 1, search = '') {
    $.post('fetch_payouts.php', { page: page, search: search }, function(response) {
        let data = JSON.parse(response);
        $('#payoutTable').html(data.table);
        $('#paginationLinks').html(data.pagination);
    });
}

$(document).ready(function() {
    loadPayouts();

    // Live search
    $('input[name="search"]').on('keyup', function() {
        let search = $(this).val();
        loadPayouts(1, search);
    });

    // Pagination click
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        let search = $('input[name="search"]').val();
        loadPayouts(page, search);
    });
});
</script>

					    		
<div class="modal fade" id="smsexampleModal" tabindex="-1" role="dialog" aria-labelledby="addNewContactLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> <!-- Changed from modal-xl to modal-lg for smaller modal -->
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addNewContactLabel">Shop Requests</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="">
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered text-sm">
              <thead class="thead-light">
                <tr class="text-center">
                  <th>S/N</th>
                  <th>Agent</th>
                  <th>Shop</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Activeness</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                include'../database/dbconfig.php';
                $select = "SELECT * FROM shop";
                $sql = mysqli_query($con, $select);
                $sn = 1;
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        echo '<tr class="align-middle text-center">';
                        echo '<td>' . $sn . '</td>';
                        echo '<td>' . htmlspecialchars($row['agent_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['shop_full']) . ' - ' . htmlspecialchars($row['shop']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['request_date']) . '</td>';
                        echo '<td><span class="badge badge-' . 
                             ($row['status'] == 'PENDING' ? 'warning' : 'success') . '">' . 
                             htmlspecialchars($row['status']) . '</span></td>';
                        echo '<td><span class="badge badge-' . 
                             ($row['activeness'] == 'ACTIVE' ? 'success' : 'secondary') . '">' . 
                             htmlspecialchars($row['activeness']) . '</span></td>';
                        echo '<td>
                                <div class="btn-group" role="group">
                                  <a href="shop_codes.php?app=' . urlencode($row['shop']) . '&agent_id=' . urlencode($row['user_id']) . '&agent_name=' . urlencode($row['agent_name']) . '&telephone=' . urlencode($row['telephone']) . '" class="btn btn-sm btn-success" title="Approve"><i class="fa fa-check"></i></a>
                                  <a href="shop_codes.php?rej=' . urlencode($row['user_id']) . '" class="btn btn-sm btn-warning" title="Reject"><i class="fa fa-times"></i></a>
                                  <a href="shop_codes.php?del=' . urlencode($row['user_id']) . '" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm(\'Are you sure you want to delete this request?\');"><i class="fa fa-trash"></i></a>
                                </div>
                              </td>';
                        echo '</tr>';
                        $sn++;
                    }
                } else {
                    echo '<tr><td colspan="7" class="text-center text-muted">No records found</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

		
		<!--update-->
			<div class="modal fade" id="modalUpdateForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Approval status</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

								  <input type="hidden" class="form-control" id="u_id" name="agent_id" readonly required>
					
						<div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">First name:</label>
									<input type="text" class="form-control" id="u_first_name" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Last name:</label>
									<input type="text" class="form-control" id="u_last_name" readonly>
								</div>
							</div>
					    </div>
					    
					    <div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Agent Email:</label>
									<input type="text" class="form-control" id="u_email" name="agent_email" readonly>
								</div>
							</div>
							   <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
									<input type="text" class="form-control" id="u_telephone" readonly>
								</div>
							</div>
					    </div>
	
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Approval status</label>
									    <select name="approval_status" class="form-control" id="u_status" required>
											  <option value="">Select Approval Status</option>
									       	  <option value="APPROVED">APPROVED</option>
											  <option value="REJECTED">REJECT</option>
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
									<button type="submit" name="approve" class="btn btn-success btn-block">Approve</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>
			
			<!--Payment-->
			<div class="modal fade" id="modalPayForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Payments</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="shop_payments.php" enctype="multipart/form-data">
					<div class="modal-body">

					<input type="hidden" class="form-control" id="p_id" name="agent_id" readonly required>
					
						<div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Agent:</label>
									<input type="text" class="form-control" id="p_agent" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Telephone:</label>
									<input type="text" class="form-control" id="p_agent_tel" readonly>
								</div>
							</div>
					    </div>
					    
					    <div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Shop name:</label>
									<input type="text" class="form-control" id="p_shop" readonly>
								</div>
							</div>
							   <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Balance:</label>
									<input type="text" class="form-control" id="p_balance" readonly>
								</div>
							</div>
					    </div>
					    
					    <div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Amount</label>
									<input type="text" class="form-control" name="amount" required>
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
									<button type="submit" name="pay" class="btn btn-success btn-block">Pay</button>
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

    <?php
    if(isset($_GET['success']))
    {
      ?>
      <div class="alert alert-success" role="alert" style="border-left:5px solid green">
         <div><?php echo $_GET['success']; ?></div>
       </div>
       <?php
    }
    else if(isset($_GET['error'])){
        ?>
       <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
       <div><?php echo $_GET['error']; ?></div>
       </div>
       <?php
    }
    ?>

					<!-- Row start -->
	<div class="row gutters ">
		<h6>Shop</h6>
			<div class="col-xl-12 col-sm-12 col-12">
				<div class="card">
					<div class="card-body">
								    
								    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#smsexampleModal"> View Shop Requests </button>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payoutModal"> Payouts History </button>
        <a href="shop_codes.php?approve=approve&user_id=<?php echo $user_id; ?>" class="btn btn-primary"> Approve all shops (<span>wait for success response when you click on Approve all shops)</span> </a>
	<br>
	<br>
	<div class="table-container mt-2">
	   <h5>Shop Payments</h5>
	    <div class="row">
	        <div class="col-lg-12 col-12">
	            <div class="mb-2">
                     <input type="text" id="search" class="form-control" placeholder="Search Agent, telephone, email, sender name, month, year">
                </div>
	       </div>
	    </div>
	    
    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Agent</th>
                <th>Agent Tel</th>
                <th>Shop</th>
                <th>Balance</th>
                <th>Profit</th>
                <th>Payouts</th>
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
            const search     = $('#search').val();
            const agent      = $('#agent').val();
            const shop       = $('#shop').val();
            const activeness = $('#activeness').val();
            const status     = $('#status').val();
            $('.loading-overlay').show();
            $.ajax({
                url: 'shops_search.php',
                method: 'GET',
                data: {
                    search: search,
                    agent: agent,
                    shop: shop,
                    activeness: activeness,
                    status: status,
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
                                    <td style="display:none;">${order.user_id}</td>
                                    <td>${order.agent}</td>
                                    <td>${order.agent_tel}</td>
                                    <td>${order.shop}</td>
                                    <td>${order.balance}</td>
                                    <td>${order.profit}</td>
                                    <td>${order.total_payouts}</td>
                                    <td> ${order.actions}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">No Shop is Available</td></tr>');
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

        $('#search, #agent, #shop, #year, #activeness, #status').on('input change', function() {
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
    $(document).on('click', '.paybtn', function() {
        $('#modalPayForm').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
                $('#p_id').val(data[0]);
                $('#p_agent').val(data[1]);
                $('#p_agent_tel').val(data[2]);
                $('#p_shop').val(data[3]);
                $('#p_balance').val(data[4]);
            });
        });
</script>

<script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.approvalbtn', function() {
        $('#modalUpdateForm').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
                $('#u_id').val(data[0]);
                $('#u_first_name').val(data[1]);
                $('#u_last_name').val(data[2]);
                $('#u_email').val(data[3]);
                $('#u_telephone').val(data[4]);
                $('#u_verify').val(data[5]);
                $('#u_status').val(data[6]);
            });
        });
    </script>
    
    <script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.deletebtn', function() {
        $('#modalDeleteForm').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
             }).get();
            console.log('Delete data:', data);
        
                $('#d_id').val(data[0]);
                $('#d_first').val(data[1]);
                $('#d_last').val(data[2]);
                $('#d_email').val(data[3]);
                $('#d_telephone').val(data[4]);
                $('#d_regdate').val(data[5]);
                $('#d_status').val(data[6]);
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
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>