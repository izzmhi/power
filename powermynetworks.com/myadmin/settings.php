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

if(isset($_POST['submit'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_names = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_names = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $telephone = mysqli_real_escape_string($con, $_POST['telephone']);

    if (empty($first_names)) {
        $errors['student']="First name is required";
    }
    if (empty($last_names)) {
        $errors['student']="Last name is required";
    }
    if (empty($telephone)) {
        $errors['telephone']="telephone is required";
    }
    
    else{
        $first_name = strtoupper($first_names);
        $last_name = strtoupper($last_names);
        
        $update = "update admin set first_name='$first_name',last_name='$last_name',telephone='$telephone' where user_id= '$user_id' ";
        $uquery = mysqli_query($con,$update);

       if($uquery){
       $success['produce']="Updated is successful";
       }
   
       else{
      $errors['produce']="Update failed";
       }
    }
}


if(isset($_POST['submit_pass'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $old_password = mysqli_real_escape_string($con, $_SESSION['old_password']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_pasword']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

     if (empty($new_password)) {
        $errors_pass['student']="New Password is required";
    }
     else if (empty($old_password)) {
        $errors['level']="Old Password is required";
    }
     else if (empty($confirm_password)) {
        $errors['program']="Confirm new password is required";
    }
     else if (strlen($new_password) < 8) {
        $errors['passwordrepeat']="Password must be at least 8 characters";
    }  
    else if ($new_password!==$confirm_password) {
        $errors['password']="password do not match";
    }
    
    else{
        
  $update = "update admin set password='$confirm_password' where user_id= '$user_id' ";
  $uquery = mysqli_query($con,$update);

    if($uquery){
      $success['produce']="Password is changed successfully";
      }
   
   else{
      $errors['produce']="Password update failed";
       }
      }
}


if (isset($_POST['shop'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM shop_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE shop_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}

if (isset($_POST['orders'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}
  
                 
//Agent mtn

if (isset($_POST['agent_mtn_activation'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM agent_mtn_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE agent_mtn_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}
                    
//Agent at

if (isset($_POST['agent_at_activation'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM agent_at_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE agent_at_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}

//Agent telecel

if (isset($_POST['agent_telecel_activation'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM agent_telecel_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE agent_telecel_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}

//Special offers all

if (isset($_POST['special_all_activation'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM special_all_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE special_all_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
    }
}


//MTN BIG TIME
// Handle MTN BIG TIME toggle
if (isset($_POST['mtnbig_toggle'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    // Get current status
    $check = mysqli_query($con, "SELECT status FROM agent_mtnbig_activation");
    $row = mysqli_fetch_assoc($check);
    $current_status = $row['status'];
    // Toggle logic
    $new_status = ($current_status === "ACTIVATED") ? "DEACTIVATED" : "ACTIVATED";
    // Update status
    $update = "UPDATE agent_mtnbig_activation SET status='$new_status' ";
    $uquery = mysqli_query($con, $update);
    if (!$uquery) {
        $errors['produce'] = "MTN BIG TIME toggle failed";
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
				    
				    <!-- Toggle switch CSS (reuse from your Telecel design) -->
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #28a745; /* green for activated */
}
input:checked + .slider:before {
  transform: translateX(26px);
}
</style>

<div class="row gutters mb-2">
    <!-- Orders Activation -->
    <div class="col-lg-3 col-sm-3 col-6">
        <div class="form-group">
<form method="POST" action="">
  <?php
    include '../database/dbconfig.php';
    $sql = mysqli_query($con, "SELECT * FROM activation LIMIT 1");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $current_status = $row['status'];
  ?>
  
  <p><b>All Orders</b></p>
  <label class="switch">
    <!-- Hidden input ensures value is always sent -->
    <input type="hidden" name="orders" value="DEACTIVATED">
    
    <input type="checkbox" name="orders" value="ACTIVATED" onchange="this.form.submit()" 
      <?php if ($current_status == 'ACTIVATED') echo 'checked'; ?>>
    
    <span class="slider round"></span>
  </label>
  
  <p><?php echo ($current_status == 'ACTIVATED') ? "Orders: ON" : "Orders: OUT OF STOCK"; ?></p>
  
  <?php } ?>
</form>


        </div>
    </div>

    <!-- Shop Activation -->
    <div class="col-lg-3 col-sm-3 col-6">
        <div class="form-group">
<form method="POST" action="">
  <?php
    include '../database/dbconfig.php';
    $sql = mysqli_query($con, "SELECT * FROM shop_activation LIMIT 1");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        $current_status = $row['status'];
  ?>
  
  <p><b>ALL SHOP</b></p>
  <label class="switch">
    <!-- Hidden input ensures value is always sent -->
    <input type="hidden" name="shop" value="DEACTIVATED">
    
    <input type="checkbox" name="shop" value="ACTIVATED" onchange="this.form.submit()" 
      <?php if ($current_status == 'ACTIVATED') echo 'checked'; ?>>
    
    <span class="slider round"></span>
  </label>
  
  <p><?php echo ($current_status == 'ACTIVATED') ? "Shop: ON" : "Shop: OUT OF STOCK"; ?></p>
  
  <?php } ?>
</form>
        </div>
    </div>

</div>

			

<div class="card h-100">
  <div class="card-header">
    <div class="card-title">Locks</div>
  </div>
  <div class="card-body">
    <div class="row">

      <!-- MTN Lock -->
      <div class="col-lg-3 col-sm-4 col-4">
        <h5>MTN Lock</h5>
        <form method="POST" action="">
          <?php
          include '../database/dbconfig.php';
          $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM agent_mtn_activation LIMIT 1"));
          $mtn_status = $row['status'];
          ?>
          <label class="switch">
            <input type="hidden" name="agent_mtn_activation" value="DEACTIVATED">
            <input type="checkbox" name="agent_mtn_activation" value="ACTIVATED" onchange="this.form.submit()" 
              <?php if ($mtn_status == 'ACTIVATED') echo 'checked'; ?>>
            <span class="slider"></span>
          </label>
          <p><?php echo ($mtn_status == 'ACTIVATED') ? "Status: ON" : "Status: OUT OF STOCK"; ?></p>
        </form>
      </div>

      <!-- AT Lock -->
      <div class="col-lg-3 col-sm-4 col-4">
        <h5>AT Lock</h5>
        <form method="POST" action="">
          <?php
          $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM agent_at_activation LIMIT 1"));
          $at_status = $row['status'];
          ?>
          <label class="switch">
            <input type="hidden" name="agent_at_activation" value="DEACTIVATED">
            <input type="checkbox" name="agent_at_activation" value="ACTIVATED" onchange="this.form.submit()" 
              <?php if ($at_status == 'ACTIVATED') echo 'checked'; ?>>
            <span class="slider"></span>
          </label>
          <p><?php echo ($at_status == 'ACTIVATED') ? "Status: ON" : "Status: OUT OF STOCK"; ?></p>
        </form>
      </div>

      <!-- Telecel Lock -->
      <div class="col-lg-3 col-sm-4 col-4">
        <h5>Telecel Lock</h5>
        <form method="POST" action="">
          <?php
          $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM agent_telecel_activation LIMIT 1"));
          $telecel_status = $row['status'];
          ?>
          <label class="switch">
            <input type="hidden" name="agent_telecel_activation" value="DEACTIVATED">
            <input type="checkbox" name="agent_telecel_activation" value="ACTIVATED" onchange="this.form.submit()" 
              <?php if ($telecel_status == 'ACTIVATED') echo 'checked'; ?>>
            <span class="slider"></span>
          </label>
          <p><?php echo ($telecel_status == 'ACTIVATED') ? "Status: ON" : "Status: OUT OF STOCK"; ?></p>
        </form>
      </div>

    </div>
  </div>
</div>
				
                    
					    
<div class="card h-100">
  <div class="card-header">
    <div class="card-title">Other Locks</div>
  </div>
  <div class="card-body">
    <div class="row">

      <!-- AT BIGTIME Lock -->
      <div class="col-lg-3 col-sm-3 col-4">
        <h5>AT BIGTIME Lock</h5>
        <form method="POST" action="">
          <?php
          include '../database/dbconfig.php';
          $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM special_all_activation LIMIT 1"));
          $atbig_status = $row['status'];
          ?>
          <label class="switch">
            <input type="hidden" name="special_all_activation" value="DEACTIVATED">
            <input type="checkbox" name="special_all_activation" value="ACTIVATED" onchange="this.form.submit()" 
              <?php if ($atbig_status == 'ACTIVATED') echo 'checked'; ?>>
            <span class="slider"></span>
          </label>
          <p><?php echo ($atbig_status == 'ACTIVATED') ? "Status: ON" : "Status: OUT OF STOCK"; ?></p>
        </form>
      </div>

      <!-- MTN BIGTIME Lock -->
      <div class="col-lg-3 col-sm-3 col-4">
        <h5>MTN BIGTIME Lock</h5>
        <form method="POST" action="">
          <?php
          $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM agent_mtnbig_activation LIMIT 1"));
          $mtnbig_status = $row['status'];
          ?>
          <label class="switch">
            <input type="hidden" name="mtnbig_toggle" value="DEACTIVATED">
            <input type="checkbox" name="mtnbig_toggle" value="ACTIVATED" onchange="this.form.submit()" 
              <?php if ($mtnbig_status == 'ACTIVATED') echo 'checked'; ?>>
            <span class="slider"></span>
          </label>
          <p><?php echo ($mtnbig_status == 'ACTIVATED') ? "Status: ON" : "Status: OUT OF STOCK"; ?></p>
        </form>
      </div>

    </div>
  </div>
</div>



					<!-- Row start -->
					
		            <div class="row gutters mb-2">

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
                                

				<div class="row gutters">

						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">User Profile</div>
								</div>
								<div class="card-body">
								    
			<?php
            include '../database/dbconfig.php';
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM admin WHERE user_id='$user_id' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">First Name</label>
												<input type="text" class="form-control" id="fullName" name="first_name" value="<?php echo $row['first_name'] ?>" >
											</div>
											<div class="form-group">
												<label for="fullName">Last Name</label>
												<input type="text" class="form-control" id="fullName" name="last_name" value="<?php echo $row['last_name'] ?>" >
											</div>
											<div class="form-group">
												<label for="eMail">Email</label>
												<input type="email" class="form-control" id="eMail" name="email" value="<?php echo $row['email'] ?>" readonly>
											</div>
										</div>
									
										<div class="col-sm-12">
										    <div class="spinner" id="spinner_info" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
											<div class="text-right">
												<button type="submit" name="submit" class="btn btn-success" id="infoBtn">Update Details</button>
											</div>
										</div>
									</div>
                               </form>
                  <?php
                }
              }
            ?> 
									
								</div>
							</div>
						</div>
						
						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Change Password</div>
								</div>
								<div class="card-body">
								    
								<form action="" method="POST" enctype="multipart/form-data">
									<div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">Old Password</label>
												<input type="password" class="form-control" name="old_password" id="fullName" placeholder="Enter the old password">
											</div>
											<div class="form-group">
												<label for="eMail">New Password (It must be 8 characters)</label>
												<input type="password" class="form-control" name="new_password" id="eMail" placeholder="Enter the new password">
											</div>
											<div class="form-group">
												<label for="phone">Confirm Password (It must be 8 characters)</label>
												<input type="password" class="form-control" name="confirm_password" id="phone" placeholder="Confirm new password">
											</div>
										</div>
									
										<div class="col-sm-12">
										    
											<div class="text-right">
											    <div class="spinner" id="spinner_pass" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
												<button type="submit" name="submit_pass" class="btn btn-success" id="passBtn">Change Password</button>
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