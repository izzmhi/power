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

$success = array();
$errors = array();


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

                   <h6>Registered Admins</h6>
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
					if(isset($_GET['success'])){
					    ?>
					    <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
					    <?php
					}
					else if(isset($_GET['error'])){
					    ?>
					    <div class="alert alert-danger" role="alert" style="border-left:5px solid red"><?php echo $_GET['error']; ?> </div>
					    <?php
					}
					?>
<style>
    .nav-block {
    display: block;
    width: 50%;  /* Each nav will take up half of the row */
    text-align: center;
    box-sizing: border-box; /* Ensures padding is included in width */
}

.nav-tabs {
    display: flex;
    width: 100%;
}

.nav-tabs .nav-item {
    flex-grow: 1; /* Each nav will expand to fill the available space */
}
</style>					

    	<div class="row gutters">
			<div class="col-lg-12 col-sm-12 col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title"></h2>
					</div>
					<div class="card-body">
                        <div class="tab-content" id="myTabContent">
    
  					<!-- Row start -->
				<div class="row">
					<div class="col-xl-12 col-sm-12 col-12">
						<div class="table-responsive">
<table id="basicExample" class="table table-sm">
    <thead>
        <tr>
            <th>S/N</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Telephone</th>
        </tr>
    </thead>
    <tbody>
        <?php
        include '../database/dbconfig.php';
        $select = "SELECT * FROM admin";
        $sql = mysqli_query($con, $select);
        $rowNumber = 1;

        if ($sql && mysqli_num_rows($sql) > 0) {
            while ($row = mysqli_fetch_assoc($sql)) {
                echo "<tr>
                    <th scope='row'>{$rowNumber}</th>
                    <td style='text-align:left;'>{$row['first_name']}</td>
                    <td style='text-align:left;'>{$row['last_name']}</td>
                    <td style='text-align:left;'>{$row['telephone']}</td>
                </tr>";
                $rowNumber++;
            }
        } else {
            echo "<tr><td colspan='4' class='text-center text-danger'>No records found.</td></tr>";
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

        </div>			
					
		</div>
		<!-- Page content end -->
	</div>
		<!-- Page wrapper end -->
		
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


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