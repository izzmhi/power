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

$success = array();
$errors = array();

if(isset($_POST['approve'])){
    $user_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $status = "APPROVED";

                // Fetch updated user details
                $selects = "SELECT * FROM account_agents WHERE user_id='$user_id'";
                $sqls = mysqli_query($con, $selects);
                if ($sqls && mysqli_num_rows($sqls) > 0) {
                    while ($row = mysqli_fetch_array($sqls)) {
                        $email = $row['email'];
                        $update = "update account_agents set status='$status' where user_id= '$user_id' ";
                        $uquery = mysqli_query($con,$update);
                        if($uquery){

            $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CODECRAFTNETWORK');
                    $mail->addAddress($email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "ACCOUNT | Approval";
                    $mail->Body = "
                    <html>
                    <body>
                    <div class='container-xxl bg-white p-0'>
                     <div class='container-xxl position-relative p-0'>
                       <br>
                   <p>Dear Agent,</p>
                   <p> Thanks For creating your account.</p>
                   <p> Your account is approved. Clink on the link below to login.</p>
                   <p> https://codecrafnetwork.com/agent/auth/login</p>
                     <p>Have a great day!</p>
                           <!-- Footer Start -->
                     <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                         <div class='container'>
                             <div class='copyright'>
                                 <div class='row'>
                                     <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                         &copy; <a class='border-bottom'>CODECRAFTNETWORK </a>, All Right Reserved. 
                                     </div>
                                     <div class='col-md-6 text-center text-md-end'>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <!-- Footer End -->
                    </body>
                    </html>";

                    $mail->send();
                    $success['produce']="Status changed successfully";
                } catch (Exception $e) {
                    $errors['mail_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                 }
               else{
                 $errors['produce']="Agent Approval failed";
               }
                    }
                }
            }

  if(isset($_POST['delete'])){
  $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
  $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
  
  $delete = "delete FROM account_agents where email='$agent_email' ";
  $dquery = mysqli_query($con,$delete);
  if($dquery){
      
    $deletes = "delete FROM agents where email='$agent_email' ";
    $dquerys = mysqli_query($con,$deletes);
    if($dquerys){
        
      $deletess = "delete FROM agents_transactions where email='$agent_email' ";
      $dqueryss = mysqli_query($con,$deletess);
      if($dqueryss){
          
            $deletesss = "delete FROM special_offers_orders where agent_email='$agent_email' ";
            $dquerysss = mysqli_query($con,$deletesss);
            if($dquerysss){
                
                $deletessss = "delete FROM orders_agent where agent_email='$agent_email' ";
                $dqueryssss = mysqli_query($con,$deletessss);
                if($dqueryssss){
     
                    $mail = new PHPMailer(true);
                try {
                    // Server settings
                     $mail->isSMTP();
                    $mail->Host = 'mail.codecraftnetwork.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'admin@codecraftnetwork.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    // Recipients
                    $mail->setFrom('admin@codecraftnetwork.com', 'CODECRAFTNETWORK');
                    $mail->addAddress($agent_email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "ACCOUNTS | DELETION";
                    $mail->Body = "
                    <html>
                        <body>
                            <div class='container-xxl bg-white p-0'>
                            <div class='container-xxl position-relative p-0'>
                              <br>
                          <p>Dear Agent,</p>
                          <p> Thanks For creating your account.</p>
                          <p> Your account is disapproved. The Administrator has cancelled your registration.</p>
                          
                            <p>Have a great day!</p>
                                  <!-- Footer Start -->
                            <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                            <div class='container'>
                                <div class='copyright'>
                                    <div class='row'>
                                        <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                            &copy; <a class='border-bottom'>CODECRAFTNETWORK </a>, All Right Reserved. 
                                        </div>
                                        <div class='col-md-6 text-center text-md-end'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer End -->
                       </body>
                    </html>";

                    $mail->send();
                    $success['produce']="Status changed successfully";
                } catch (Exception $e) {
                    $errors['mail_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                        }
                        else{
                            $errors['produce']="Failed to delete from  Orders Agent";
                        }
                    }
                    else{
                        $errors['produce']="Failed to delete from  special Orders";
                    }
                }
                else{
                $errors['produce']="Failed to delete from  Agent Transactions";
                }
            }
            else{
                $errors['produce']="Failed to delete from Agents";
            }
        }
        else{
            $errors['produce']="Failed to delete from  agent account";
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
			<?php $page = 'ussd' ?>
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
				
				
					    			<!--delete-->
			<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">USSD Requests</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">
					    

	 <div class="table-responsive">
    <table id="Example" class="table custom-table table-sm">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Agent</th>
            <th>Telephone</th>
            <th>Sender Name</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Activeness</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
            <!-- Data will be populated here -->
            <?php
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            // Select query
            $select = "SELECT * FROM agents_sms";
            $sql = mysqli_query($con, $select);
            $sn = 1; // Initialize counter

            // Check if there are any rows
            if (mysqli_num_rows($sql) > 0) {
                // Fetch each row
                while ($row = mysqli_fetch_array($sql)) {
            ?>
                <tr>
                    <td><?php echo $sn; ?></td> <!-- Display counter -->
                    <td><?php echo $row['agent_name']; ?></td>
                    <td><?php echo $row['telephone']; ?></td>
                    <td><?php echo $row['sender_id']; ?></td>
                    <td><?php echo $row['request_date']; ?></td>
                    <td><?php echo $row['request_status']; ?></td>
                    <td><?php echo $row['activeness']; ?></td>
                    <td>
                        <a href="ussd_codes.php?app=<?php echo $row['sender_id']; ?>&agent_id=<?php echo $row['user_id']; ?>&agent_name=<?php echo $row['agent_name']; ?>&telephone=<?php echo $row['telephone']; ?>" class="btn btn-success">APPROVE</a>
                        <a href="ussd_codes.php?rej=<?php echo $row['sender_id']; ?>" class="btn btn-danger">REJECT</a>
                    </td>
                </tr>
            <?php
                    $sn++; // Increment counter
                }
            } else {
                echo "<tr><td colspan='8'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

					</div>
					</div>
	             </form>
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
					<h5>USSD</h5>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
								    
								    <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">  View USSD Requests </button>
	<br>						
	<div class="table-container mt-2">
	   <h5>USSD Payments</h5>
	    <div class="row">
	        <div class="col-lg-12 col-12">
	            <div class="mb-2">
                     <input type="text" id="search" class="form-control" placeholder="Search Agent, telephone, email, sender name, month, year">
                </div>
	       </div>
	    </div>
	    
	    <div class="row">
	       <div class="col-lg-6 col-6">
	          <div class="mb-2">
                <select id="agent" class="form-control">
                <option value="">Select Agent</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT agent_name FROM agents_sms_payments ";
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
                        <option value="">No Agent is Available</option>
                        <?php
                    }
                    ?>
                </select>
              </div>
	        </div>
	        <div class="col-lg-6 col-6">
	          <div class="mb-2">
                <select id="sender_id" class="form-control">
                <option value="">Select SENDER NAME</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT sender_id FROM agents_sms_payments ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                        while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['sender_id']; ?>"><?php echo $row['sender_id']; ?></option>
                        <?php
                    }
                    } else {
                        ?>
                        <option value="">No Sender Name is Available</option>
                        <?php
                    }
                    ?>
                </select>
              </div>
	        </div>
	    </div>
	    
	    <div class="row">
	       <div class="col-lg-4 col-4">
	          <div class="mb-2">
                <select id="year" class="form-control">
                <option value="">Select Year</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT year FROM agents_sms_payments ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                        while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['year']; ?>"><?php echo $row['year']; ?></option>
                        <?php
                    }
                    } else {
                        ?>
                        <option value="">No Year is Available</option>
                        <?php
                    }
                    ?>
                </select>
              </div>
	        </div>
	        <div class="col-lg-4 col-4">
	          <div class="mb-2">
                <select id="month" class="form-control">
                <option value="">Select Month</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT month FROM agents_sms_payments ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                        while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['month']; ?>"><?php echo $row['month']; ?></option>
                        <?php
                    }
                    } else {
                        ?>
                        <option value="">No Month is Available</option>
                        <?php
                    }
                    ?>
                </select>
              </div>
	        </div>
	        <div class="col-lg-4 col-4">
	          <div class="mb-2">
                <select id="status" class="form-control">
                <option value="">Select Status</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT status FROM agents_sms_payments ";
                    $sql = mysqli_query($con, $select);
                    $check = mysqli_num_rows($sql) > 0;
                    if ($check) {
                        while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['status']; ?>"><?php echo $row['status']; ?></option>
                        <?php
                    }
                    } else {
                        ?>
                        <option value="">No USSD is Available</option>
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
                <th>Year</th>
                <th>Month</th>
                <th>Agent</th>
                <th>Telephone</th>
                <th>Sender Name</th>
                <th>Amount</th>
                <th>Pay Date</th>
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
            const search   = $('#search').val();
            const agent    = $('#agent').val();
            const sender_id= $('#sender_id').val();
            const year     = $('#year').val();
            const month    = $('#month').val();
            const status   = $('#status').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'ussd_search.php',
                method: 'GET',
                data: {
                    search: search,
                    agent: agent,
                    sender_id: sender_id,
                    year: year,
                    month: month,
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
                                    <td>${order.year}</td>
                                    <td>${order.month}</td>
                                    <td>${order.agent_name}</td>
                                    <td>${order.telephone}</td>
                                    <td>${order.sender_id}</td>
                                    <td>${order.amount}</td>
                                    <td>${order.pay_date}</td>
                                    <td>${order.pay_status}</td>
                                    <td> ${order.actions}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">No USSD is Available</td></tr>');
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

        $('#search, #agent, #sender_id, #year, #month, #status').on('input change', function() {
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