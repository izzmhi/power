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
$selects = "select * from account_agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
    }
}


$success = array();
$errors = array();

if(isset($_POST['submitss'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $package = mysqli_real_escape_string($con, $_POST['package']);
    $telephone = mysqli_real_escape_string($con, $_POST['telephone']);

       $success['produce']="Data send successful";
       
 
      $errors['produce']="Data sending failed";

    
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
			<?php $page = 'settings' ?>
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
                                if (isset($_GET['success'])) {
                                    echo "<p style='color: green;'>".$_GET['success']."</p>";
                                }
                                
                                if (isset($_GET['error'])) {
                                    echo "<p style='color: red;'>".$_GET['error']."</p>";
                                }
                                ?>
                                

				<div class="row gutters">

						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Send Data</div>
								</div>
								<div class="card-body">
								    
								<form action="send_balance.php" method="POST" enctype="multipart/form-data">
                                    <div class="row gutters">
									
										<div class="col-sm-12">
										    <div class="spinner" id="spinner_info" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
											<div class="text-right">
												<button type="submit" name="submit" class="btn btn-success" id="infoBtn">Check balance</button>
											</div>
										</div>
									</div>
                               </form>
	
                             <form action="send_processing.php" method="POST" enctype="multipart/form-data">
                                    <div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">Number</label>
												<input type="text" class="form-control" name="telephone" >
											</div>
											<div class="form-group">
												<label for="fullName">Mb</label>
												<input type="text" class="form-control" name="package" >
											</div>

										</div>
									
										<div class="col-sm-12">
										    <div class="spinner" id="spinner_info" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
											<div class="text-right">
												<button type="submit" name="submit" class="btn btn-success" id="infoBtn">Send</button>
											</div>
										</div>
									</div>
                               </form>
   
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
    $('#infoBtn').on('click', function() {
    // Show the spinner
    $('#spinner_info').show();
    });
    });
    </script>
    
     <script>
    $(document).ready(function() {
    $('#passBtn').on('click', function() {
    // Show the spinner
    $('#spinner_pass').show();
    });
    });
    </script>
		

<script>
        $(document).ready(function () {
            $('.updatebtn').on('click', function () {
                $('#modalUpdateForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#u_id').val(data[0]);
                $('#u_student').val(data[1]);
                $('#u_level').val(data[2]);
                $('#u_program').val(data[3]);
                $('#u_classes').val(data[4]);
                $('#u_parent_name').val(data[5]);
                $('#u_parent_telephone').val(data[6]);
                $('#u_parent_email').val(data[7]);
                $('#u_town').val(data[8]);
            });
        });
    </script>

  <script>
        $(document).ready(function () {
            $('.deletebtn').on('click', function () {
                $('#modalDeleteForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                  $('#d_id').val(data[0]);
                $('#d_student').val(data[1]);
                $('#d_level').val(data[2]);
                $('#d_program').val(data[3]);
                $('#d_classes').val(data[4]);
                $('#d_parent_name').val(data[5]);
                $('#d_parent_telephone').val(data[6]);
                $('#d_parent_email').val(data[7]);
                $('#d_town').val(data[8]);
            });
        });
    </script>
    
     <script>
        $(document).ready(function () {
            $('.ad').on('click', function () {
                $('#exampleModal').modal('show');
            });
        });
    </script>
    
    <script>
    document.body.style.cursor = 'wait'; // change cursor to wait
window.addEventListener('load', () => {
  document.body.style.cursor = 'default'; // change cursor back to default
});
 </script>
      <script>
        $(document).ready(function () {
            $('.add').on('click', function () {
                $('#exampleModal').modal('show');
       
            });
        });
    </script>

  <script>
 
      $("#ad").on("click", function(){
       document.querySelectorAll('*').forEach(item => {
      item.style.cursor = 'wait';
      })
      
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