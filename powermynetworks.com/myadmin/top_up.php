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

if (isset($_POST['submit'])) {
    // Sanitize and validate input data
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $agent_id = mysqli_real_escape_string($con, $_POST['agent_id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    $admin_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $admin_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $purpose = "Admin Top-up";
    // Ensure amount is a valid integer
    if (!filter_var($amount, FILTER_VALIDATE_INT) || $amount <= 0) {
        $errors['amount'] = "Invalid amount. Please enter a valid number greater than 0.";
    } else {
        $trans_date = date('Y-m-d');
        
        $select = "SELECT * FROM admin WHERE user_id='$user_id' ";
        $sql = mysqli_query($con, $select);
        if ($sql && mysqli_num_rows($sql) > 0) {
                         // Fetch updated user details
                $selects = "SELECT * FROM agents WHERE user_id='$agent_id'";
                $sqls = mysqli_query($con, $selects);
                if ($sqls && mysqli_num_rows($sqls) > 0) {
                    while ($row = mysqli_fetch_array($sqls)) {
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $telephone = $row['telephone'];
                        $email = $row['email'];
                        $wallet = $row['wallet'];
                        $agent_name = $first_name . " " . $last_name;
        // Update wallet and credit balance
        $walletf = $wallet + $amount;
        $stmt = $con->prepare("UPDATE agents SET amount=?, wallet=? WHERE user_id=?");
        $stmt->bind_param("dds", $amount, $walletf,$agent_id);
        $uquery = $stmt->execute();
        if ($uquery) {

            // Insert transaction details
            $stmt = $con->prepare("INSERT INTO agents_transactions (reference_id,user_id,agent_name,email,amount,wallet,trans_date,purpose,admin_name,admin_email) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssddssss", $reference_id, $agent_id, $agent_name, $email, $amount, $walletf, $trans_date,$purpose,$admin_name,$admin_email);
            $iquery = $stmt->execute();
            if ($iquery) {

                $mail = new PHPMailer(true);
                try {
                        $mail->isSMTP();
                    $mail->Host = 'mail.powermynetworks.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@powermynetworks.com';
                    $mail->Password = 'REDACTED';
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;
                    $mail->setFrom('noreply@powermynetworks.com', 'Powermynet');
                    $mail->addAddress($email);
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "ACCOUNT TOP-UP";
                    $mail->Body = "
                    <html>
                        <body>
                            <div class='container-xxl bg-white p-0'>
                            <div class='container-xxl position-relative p-0'>
                              <br>
                          <p>Dear Agent,</p>
                          <p>Your Wallet is loaded with GHS$amount.</p>
                          <p>Your current Wallet balance is GHS$walletf</p>
                            <p>Have a great day!</p>
                                  <!-- Footer Start -->
                            <div class='container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn'>
                            <div class='container'>
                                <div class='copyright'>
                                    <div class='row'>
                                        <div class='col-md-6 text-center text-md-start mb-3 mb-md-0'>
                                            &copy; <a class='border-bottom'>POWERMYNET </a>, All Right Reserved.
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
                $success = "Wallet top-up successful";
                header("Location: agent_transactions.php?success=$success");
                exit();
                } catch (Exception $e) {
                    $errors['mail_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                
            } else {
                $errors['insert'] = "Failed to add to transaction history: " . $stmt->error;
            }
        } else {
            $errors['produce'] = "Wallet top-up update failed: " . $stmt->error;
        }
        }
        }
    }
    else{
        $errors['produce']="You are not authorized to do this";
    }  

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
			<?php $page = 'agents' ?>
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
			
		<!--update-->
			<div class="modal fade" id="modalUpdateForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Wallet Top Up</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

								  <input type="hidden" class="form-control" id="u_id" name="agent_id" readonly required>
					
						<div class="row px-2">
					        <div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">First name:</label>
									<input type="text" class="form-control" id="u_first_name" name="first_name" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-6">
								<div class="form-group">
									<label for="contactName">Last name:</label>
									<input type="text" class="form-control" id="u_last_name" name="last_name" readonly>
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
									<input type="text" class="form-control" id="u_telephone" name="agent_telephone" readonly>
								</div>
							</div>
					    </div>
							<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Enter Paystack Reference</label>
									<input type="text" class="form-control" name="reference_id" required>
								</div>
							</div>
						</div>
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Enter Amount</label>
									<input type="text" class="form-control" name="amount" required>
								</div>
							</div>
						</div>
	
						<hr>
						  <div class="row px-2">
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success btn-block">Top up</button>
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

					<!-- Row start -->
<div class="row gutters ">
	<h6>Wallet Top_up</h6>
		<div class="col-xl-12 col-sm-12 col-12">
			<div class="card">
				<div class="card-body">
							
	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-12 col-12">
	            <div class="mb-2">
                     <input type="text" id="search" class="form-control" placeholder="Search first name, last name, email, telephone">
                </div>
	       </div>

	    </div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Amount</th>
                <th>Wallet</th>
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
            const register_status = $('#register_status').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'top_up_search.php',
                method: 'GET',
                data: {
                    search: search,
                    register_status: register_status,
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
                                     <td>${order.first_name}</td>
                                    <td>${order.last_name}</td>
                                    <td>${order.email}</td>
                                    <td>${order.telephone}</td>
                                    <td>${order.amount}</td>
                                    <td>${order.wallet}</td>
                                    <td> ${order.actions}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">No Agent is Available</td></tr>');
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

        $('#search, #register_status').on('input change', function() {
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