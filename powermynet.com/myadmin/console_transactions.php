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

if (isset($_POST['submit'])) {
    // Sanitize and validate input data
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $agent_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $purpose = "Top-up";

    // Ensure amount is a valid integer
    if (!filter_var($amount, FILTER_VALIDATE_INT) || $amount <= 0) {
        $errors['amount'] = "Invalid amount. Please enter a valid number greater than 0.";
    } else {
        $trans_date = date('Y-m-d');
        
                     // Fetch updated user details
                $selects = "SELECT * FROM consoles WHERE user_id='$agent_id'";
                $sqls = mysqli_query($con, $selects);

                if ($sqls && mysqli_num_rows($sqls) > 0) {
                    while ($row = mysqli_fetch_array($sqls)) {
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $telephone = $row['telephone'];
                        $email = $row['email'];
                        $wallet = $row['wallet'];
                        $credit = $row['credit'];
                        $agent_name = $first_name . " " . $last_name;

        // Update wallet and credit balance
        $walletf = $wallet + $amount;
        $creditf = $credit + $amount;

        $stmt = $con->prepare("UPDATE consoles SET amount=?, wallet=?, credit=? WHERE user_id=?");
        $stmt->bind_param("iiis", $amount, $walletf, $creditf, $agent_id);
        $uquery = $stmt->execute();

        if ($uquery) {
            // Generate unique transaction IDs
            $date1 = date('y');
            $date2 = date('m');
            $date3 = date('d');
            $date4 = date('his');
            $co = bin2hex(random_bytes(2));
            $cod = bin2hex(random_bytes(2));
            $trans_id = $date2 . $date3 . $co . $date1 . $cod . $date4;
            $reference_id = $date2 . $date3 . $cod . $date1 . $co . $date4;
            
                                    // Insert transaction details
            $stmt = $con->prepare("INSERT INTO console_transactions (reference_id,trans_id,user_id,agent_name,email,amount,wallet,credit,trans_date,purpose) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssdddss", $reference_id, $trans_id, $agent_id, $agent_name, $email, $amount, $walletf, $creditf, $trans_date,$purpose);
            $iquery = $stmt->execute();
            if ($iquery) {
                // Send confirmation email
                $to = $email;
                $subject = "Top-up | Wallet";
                $headers = "From: CODECRAFTNETWORK <codecraftnetwork1@gmail.com>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=ISO-8859-1\r\n";
                $message = "<html>
               <body>
                   <div class='container-xxl bg-white p-0'>
                       <div class='container-xxl position-relative p-0'>
                           <br>
                           <p>Dear Agent,</p>
                           <p>You have top-up your wallet with GHS$amount</p>
                           <p>Your Wallet Balance is GHS$walletf</p>
                           <p>Have a great day!</p>
                           <!-- Footer Start -->
                           <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                               <div class='container'>
                                   <div class='copyright'>
                                       <div class='row'>
                                           <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                               &copy; <a class='border-bottom'>CODECRAFTNETWORK</a>, All Right Reserved.
                                           </div>
                                           <div class='col-md-6 text-center text-md-end'></div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <!-- Footer End -->
                       </div>
                   </div>
               </body>
               </html>";
                mail($to, $subject, $message, $headers);
                $success = "Wallet top-up successful";
                header("Location: agent_transactions.php?success=$success");
                exit();
            } else {
                $errors['insert'] = "Failed to add to transaction history: " . $stmt->error;
            }

            
        } else {
            $errors['produce'] = "Wallet top-up update failed: " . $stmt->error;
        }
        }
        }
    }
}

if(isset($_POST['update'])){
  $agent_id = mysqli_real_escape_string($con, $_POST['user_id']);
  $trans_id = mysqli_real_escape_string($con, $_POST['trans_id']);
  $amount = mysqli_real_escape_string($con, $_POST['amount']);
  $wallet = mysqli_real_escape_string($con, $_POST['wallet']);

    $walletf = $wallet-$amount;
    $update = "update agents_transactions set amount='$amount',  wallet='$walletf', credit='$walletf' where user_id='$agent_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
     
    $updates = "update agents set amount='$amount',  wallet='$walletf', credit='$walletf' where user_id='$agent_id' ";
    $uquerys = mysqli_query($con,$updates);
    if($uquerys){
      $success['produce']="Transaction updated successfully";
      }
   
    else{
      $errors['produce']="Agent update failed";
       }
     
      }
   
    else{
      $errors['produce']="Transaction update failed";
       }

      
}

if(isset($_POST['delete'])){
  $agent_id = mysqli_real_escape_string($con, $_POST['user_id']);
  $trans_id = mysqli_real_escape_string($con, $_POST['trans_id']);
  $amount = mysqli_real_escape_string($con, $_POST['amount']);
  $wallet = mysqli_real_escape_string($con, $_POST['wallet']);
  $email = mysqli_real_escape_string($con, $_POST['email']);
  
  $amounts = floatval($amount);
  $wallets = floatval($wallet);

  $delete = "delete FROM agents_transactions where trans_id='$trans_id' AND user_id='$agent_id' ";
  $dquery = mysqli_query($con,$delete);
  if($dquery){
    
    $walletf = $wallets-$amounts;
    $update = "update agents set amount='$amount',  wallet='$walletf', credit='$walletf' where user_id='$agent_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        
                  // Send confirmation email
                $to = $email;
                $subject = "Transaction Cancellation | Wallet";
                $headers = "From: CODECRAFTNETWORK <codecraftnetwork1@gmail.com>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=ISO-8859-1\r\n";
                $message = "<html>
               <body>
                   <div class='container-xxl bg-white p-0'>
                       <div class='container-xxl position-relative p-0'>
                           <br>
                           <p>Dear Agent,</p>
                           <p>Admin has cancelled the GHS$amount.00 top-up of your wallet</p>
                           <p>Your Wallet Balance is GHS$walletf.00.</p>
                           <p>Have a great day!</p>
                           <!-- Footer Start -->
                           <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                               <div class='container'>
                                   <div class='copyright'>
                                       <div class='row'>
                                           <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                               &copy; <a class='border-bottom'>CODECRAFTNETWORK</a>, All Right Reserved.
                                           </div>
                                           <div class='col-md-6 text-center text-md-end'></div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <!-- Footer End -->
                       </div>
                   </div>
               </body>
               </html>";
                mail($to, $subject, $message, $headers);
        
      $success['produce']="Transaction Cancelled successfully";
      }
   
    else{
      $errors['produce']="Agent update failed";
       }
    }
    else{
    $errors['produce']="Transaction failed to cancel";
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
				
				<!-- Modal -->
<div class="modal fade" id="mtnBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Manual Wallet Top-up</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
        <div class="mb-3">
               <select id="agent_id" name="agent_id" class="form-control">
                    <option value="">Select Agent</option>
                    <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT * FROM agents ORDER BY first_name";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['user_id']; ?>"><?php echo $row['first_name'] . "  " . $row['last_name']; ?></option>
                        <?php
                    }
                    } else {
                    ?>
                    <option value="">No Agent is Available</option>
                    <?php
                    }
                    ?>
                    </select>
        </div>
        <div id="agent-container" style="display: none;">

            <div class="mb-3" id="amount_con">
                <label>Amount</label>
                <input type="number" class="form-control" id="amount" placeholder="Amount to top-up" name="amount" required>
            </div>

        </div>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="submit" class="btn btn-block btn-primary" id="mtnBtnW">Submit</button>
    </div>
</form>

    </div>
  </div>
</div>

	<!-- Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Update Wallet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
        <input type="text" class="form-control" name="trans_id" id="u_trans_id" readonly>
       <input type="text" class="form-control" name="reference_id" id="u_reference_id" readonly>
       <input type="text" class="form-control" name="user_id" id="u_user_id" readonly>

              <div class="mb-3">
                <input type="text" class="form-control" name="agent_name" id="u_agent_name" readonly>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" name="email" id="u_email" readonly>
            </div>

               <div class="mb-3">
                <div class="row">
                    <div class="col-lg-6 col-6">
                      <label>Amount</label>
                     <input type="text" class="form-control" id="u_amount" name="amount" required>
                    </div>
                      <div class="col-lg-6 col-6">
                       <label>Wallet Balance:</label>
                        <input type="text" class="form-control" name="wallet" id="u_wallet" readonly> 
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="row">
                    <div class="col-lg-6 col-6">
                      <label>Purpose</label>
                     <input type="text" class="form-control" name="purpose" id="u_purpose" readonly>
                    </div>
                      <div class="col-lg-6 col-6">
                        <label>Transaction Date</label>
                        <input type="text" class="form-control" id="u_trans_date" name="trans_date" readonly required>
                    </div>
                </div>
            </div>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_update" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="update" class="btn btn-block btn-primary" id="updateBtnW">Update</button>
    </div>
</form>

    </div>
  </div>
</div>

				<!-- Main container start -->
				<div class="main-container">
    
			
			<?php
			if(isset($_GET['success'])){
			?>
			  <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                <div><?php echo $_GET['success']; ?></div>
                </div>
			<?php
			}
			?>

					<!-- Row start -->
				<div class="row gutters ">
					<h6>Console Transactions</h6>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
        <div class="table-container">
       	    <div class="row">
	           <div class="col-lg-6 col-6">
	                <div class="mb-2">
                       <input type="text" id="search" class="form-control" placeholder="">
                    </div>
	             </div>
	            <div class="col-lg-6 col-6">
	           <div class="mb-2">
                 <select id="agent" class="form-control">
                    <option value="">Select Agent</option>
                    <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT agent_name FROM console_transactions ";
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
                    <option value="">No Transaction is Available</option>
                    <?php
                    }
                    ?>
                    </select>
                </div>
	        </div>
	        </div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Agent</th>
                    <th>Email</th>
                    <th>Gig</th>
                    <th>Date</th>
                    <th>Desc</th>
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
							
							
						</div>

					</div>
				</div>
			</div>
			
		</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		
			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <!--mtn spinner-->

    
       <script>
        $(document).ready(function() {
    $('#deleteBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_delete').show();
    });
    });
    </script>
		
		<script>
	$(document).ready(function() {
    // Hide the agent container initially
    $('#agent-container').hide();

    // Listen for changes on the agent_id select element
    $('#agent_id').change(function() {
        var agentId = $(this).val();

        if (agentId) {
            // Show the spinner while fetching data
            $('#spinner_mtn').show();

            // AJAX request to fetch agent details
            $.ajax({
                url: 'agent_transactions_details.php', // The URL to your PHP script that fetches agent details
                type: 'POST',
                data: { user_id: agentId },
                dataType: 'json',
                success: function(response) {
                    // Hide the spinner after data is fetched
                    $('#spinner_mtn').hide();

                    if (response) {
                        // Populate the input fields with the data
                        $('#agent_name').val(response.agent_name);
                        $('#email').val(response.email);
                        $('#telephone').val(response.telephone);
                        $('#wallet').val(response.wallet);

                        // Show the agent container
                        $('#agent-container').show();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle any errors here
                    console.error('An error occurred while fetching agent data:', error);
                    $('#spinner_mtn').hide();
                }
            });
        } else {
            // Hide the agent container if no agent is selected
            $('#agent-container').hide();
        }
    });
});
				</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
 $(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;

    function loadTableData(search= '', agent='', date = '', page = 1) {
        $('.loading-overlay').show();
        $.ajax({
            url: 'console_transactions_search.php',
            method: 'GET',
            data: {
                search: search,
                agent: agent,
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
                                <td style="display:none">${order.trans_id}</td>
                                <td style="display:none">${order.console_id}</td>
                                <td>${order.agent_name}</td>
                                <td>${order.email}</td>
                                <td style="display:none">${order.tel}</td>
                                <td>${order.package}GIG</td>
                                <td>${order.trans_date}</td>
                                <td>${order.trans_desc}</td>
                                
                            </tr>
                        `);
                    });
                    generatePaginationLinks(response.pagination, page);
                } else {
                    $('#table-body').append('<tr><td colspan="8" style="text-align:center; font-weight:bolder">No Transaction has taken place</td></tr>');
                    $('#pagination-links').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#table-body').append('<tr><td colspan="8" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
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
                const agentValue = $('#agent').val();
                const dateValue = $('#date').val();
                loadTableData(searchValue, agentValue, dateValue, pageNum);
            });
        }
    }

    $('#search').on('input', function() {
        currentPage = 1;
        loadTableData($(this).val(), $('#agent').val(), $('#date').val(), currentPage);
    });

    $('#agent').on('change', function() {
        currentPage = 1;
        loadTableData($('#search').val(), $(this).val(), $('#date').val(), currentPage);
    });

    $('#date').on('change', function() {
        currentPage = 1;
        loadTableData($('#search').val(), $('#agent').val(), $(this).val(), currentPage);
    });

    $('#prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadTableData($('#search').val(), $('#agent').val(), $('#date').val(), currentPage);
        }
    });

    $('#next-page').on('click', function() {
        currentPage++;
        loadTableData($('#search').val(), $('#agent').val(), $('#date').val(), currentPage);
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