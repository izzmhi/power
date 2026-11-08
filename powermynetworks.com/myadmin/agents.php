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
    $admin_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $admin_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $admin_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $status = "APPROVED";
   
$select = "SELECT * FROM admin WHERE user_id='$admin_id'";
$sql = mysqli_query($con, $select);
if ($sql && mysqli_num_rows($sql) > 0) {
          // Fetch updated user details
    $selects = "SELECT * FROM account_agents WHERE user_id='$user_id'";
    $sqls = mysqli_query($con, $selects);
    if ($sqls && mysqli_num_rows($sqls) > 0) {
        while ($row = mysqli_fetch_array($sqls)) {
            $email = $row['email'];
            $agent_tel = $row['telephone'];
            $update = "update account_agents set status='$status' where user_id= '$user_id' ";
            $uquery = mysqli_query($con,$update);
            if($uquery){
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
                        $mail->isHTML(true);
                        $mail->Subject = "Account Approval";
                        $mail->Body = "
                            <html>
                            <body>
                                <p>Dear Agent,</p>
                                <p>Admin has approved your account.</p>
                                <p>Have a great day!</p>
                                <p><strong>POWERMYNET</strong></p>
                            </body>
                            </html>";
                        $mail->send();
                          $success['produce']="Status changed successfully";
                    } catch (Exception $e) {
                        $errors['email_send'] = "Email sending failed: {$mail->ErrorInfo}";
                    }

                 }
               else{
                 $errors['produce']="Agent Approval failed";
               }
            }
        }
    }
    else{
        $errors['produce']="You are not authorized to do this";
    }    
    }


  if(isset($_POST['delete'])){
  $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
  $agent_email = mysqli_real_escape_string($con, $_POST['agent_email']);
   $admin_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $select = "SELECT * FROM admin WHERE user_id='$admin_id' ";
    $sql = mysqli_query($con, $select);
    if ($sql && mysqli_num_rows($sql) > 0) {
 
  $delete = "delete FROM account_agents where email='$agent_email' ";
  $dquery = mysqli_query($con,$delete);
  if($dquery){
     
    $deletes = "delete FROM agents where email='$agent_email' ";
    $dquerys = mysqli_query($con,$deletes);
    if($dquerys){
            $deletesss = "delete FROM special_offers_orders where agent_email='$agent_email' ";
            $dquerysss = mysqli_query($con,$deletesss);
            if($dquerysss){
               
                $deletessss = "delete FROM orders_agent where agent_email='$agent_email' ";
                $dqueryssss = mysqli_query($con,$deletessss);
                if($dqueryssss){
     
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
                $errors['produce']="Failed to delete from Agents";
            }
        }
        else{
            $errors['produce']="Failed to delete from  agent account";
        }
    }
    else{
        $errors['produce']="You are not authorized to do this";
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

    <!--delete-->
<div class="modal fade" id="modalDeleteForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="addNewContactLabel">Delete Agent Account</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<form class="row" method="POST" action="" enctype="multipart/form-data">
<div class="modal-body">
   <div class="row px-2">
       <div class="col-sm-12 col-12">
                                 <h6>You are about to delete an Agent Account. This will delete all orders and transaction history of Agent.</h6>
   </div>
</div>
   
   <div class="row px-2">
       <div class="col-sm-12 col-12">
<div class="form-group">
 <input type="text" class="form-control" id="d_email" name="agent_email" readonly required>
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
<h6>Agent Approvals</h6>
<div class="col-xl-12 col-sm-12 col-12">
<div class="card">
<div class="card-body">

<div class="table-container">
   
   <div class="row">
       <div class="col-lg-8 col-6">
           <div class="mb-2">
                     <input type="text" id="search" class="form-control" placeholder="Search first name, last name, email, telephone, status">
                </div>
      </div>
      <div class="col-lg-4 col-6">
         <div class="mb-2">
                <select id="register_status" class="form-control">
                <option value="">Select Status</option>
                <?php
                    include '../database/dbconfig.php';
                    $select = "SELECT DISTINCT status FROM account_agents ";
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
                        <option value="">No Agent is Available</option>
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
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Verification</th>
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
            const register_status = $('#register_status').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'agents_search.php',
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
                                    <td>${order.verify}</td>
                                    <td>${order.register_status}</td>
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
<!-- Rating JS -->
<script src="vendor/rating/raty.js"></script>
<script src="vendor/rating/raty-custom.js"></script>
<!-- Main JS -->
<script src="js/main.js"></script>
</body>
</html>