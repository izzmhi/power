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

if (isset($_POST['submit'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    if (empty($message)) {
        $errors['message'] = "Message is required";
    }

    if (empty($errors)) {
        date_default_timezone_set('GMT');
        $message_time = date('H:i:s A');
        $message_date = date('d-m-Y');

        // Produce unique message ID
        $date1 = date('y');
        $date2 = date('m');
        $date3 = date('d');
        $date4 = date('his');
        $co = bin2hex(random_bytes(2));
        $cod = bin2hex(random_bytes(2));
        $message_id = $date2.$date3.$co.$date1.$cod.$date4;
        
          $insertquery = "INSERT INTO messages(message_id, message, message_time, message_date) VALUES ('$message_id', '$message', '$message_time', '$message_date')";
            $iquery = mysqli_query($con, $insertquery);

            if ($iquery) { 

                $success['message'] = "Message sent successfully!";
            } else {
                $errors['message'] = "Failed to insert message into the database!";
            }
    }
}

if (isset($_POST['delete'])) {
    // SQL query to delete all messages from the table
    $deletequery = "DELETE FROM messages";
    $dquery = mysqli_query($con, $deletequery);
    if ($dquery) {
        $success['message'] = "Message deleted successfully!";
    } else {
        $errors['message'] = "Failed to delete message!";
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
			<?php $page = 'messaging' ?>
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
					<!-- Row start -->
		            <div class="main-container">
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
					<h4>Orders</h4>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
        <form method="POST" action="" enctype="multipart/form-data">
			<button class="btn btn-danger" type="submit" name="delete">DELETE MESSAGES </button>
        <!--
        <div id="studentDetails" class="">
         Student details will be dynamically inserted here 
         </div>
         <br>
         -->
         		<div class="mt-2">

			    
				<div class="form-group">
					<textarea class="form-control" name="message" placeholder="Type your message here..."></textarea>
				</div>
				<div class="form-group" style="text-align:right">
					<button class="btn btn-primary" type="submit" name="submit">SEND <i class="icon-send"></i></button>
				</div>
            </div>
            <hr>
            <h5 class="bg-muted text-info pt-2" style="text-align:right;">Messages</h5>
				<div class="" >
						<ul class="border border-info p-2" >
						<?php
                        include '../database/dbconfig.php';

                        $select = "select * from messages ORDER BY id DESC ";
                        $sql = mysqli_query($con,$select);
                        while($row=mysqli_fetch_array($sql))
					          {
                                ?>
								<li class=''>
									<div class=''>
						
										<p><?php echo $row['message'] ?></p>
										<div style="font-size:10px"><?php echo $row['message_date'] ?> </div>
									</div>
								</li>
								<hr>
							 <?php
                            }
                        ?>						
						</ul>
					</div>
    
    </form>

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