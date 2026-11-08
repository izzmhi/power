<?php
session_name("AGENT_PORTAL_SESSION");
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
$selects = "select * from agents where user_id='$user_id' ";
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
			<?php $page = 'shop' ?>
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

<div class="row align-items-center">
  <!-- Dashboard Title -->
<div class="col-5">
  <h4 class="page-title d-flex align-items-center">
    <span class="page-title-icon bg-light border-success text-white me-2">
      <i class="mdi mdi-home"></i>
    </span>
    <div class="dropdown bg-success">
      <a class="nav-link dropdown-toggle text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        Menu
      </a>
      <div class="dropdown-menu bg-success text-white">
        <a class="dropdown-item text-white fs-3" href="shop.php">Home</a>
                   <a class="dropdown-item text-white fs-3" href="shop_orders.php">Shop Orders</a>
          <a class="dropdown-item text-white fs-3" href="shop_payouts.php">Payouts History</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-white fs-3" href="shop_pricing.php">Pricing</a>
      </div>
    </div>
  </h4>
</div>    
</div> 

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
					
					                <?php
                                    include '../database/dbconfig.php';
                                    // Sanitize user input
                                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                    // Select query
                                    $select = "SELECT * FROM shop WHERE user_id='$user_id'";
                                    $sql = mysqli_query($con, $select);
                                    // Check if there are any rows
                                    if (mysqli_num_rows($sql) > 0) {
                                        // Fetch each row
                                        while ($row = mysqli_fetch_array($sql)) {
                                            if ($row['status'] == "REJECTED") {
                                                ?>
                                        	<h6>Your SHOP Request for <strong class="text-primary"><?php echo $row['shop']; ?></strong> is <strong class="text-danger">REJECTED</strong></h6>
                                                <?php
                                                }
                                                    elseif ($row['status'] == "PENDING"){
                                                        ?>
                                                        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 shadow-sm rounded" role="alert">
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    Your SHOP request for 
                                                                    <strong class="text-primary"><?php echo $row['shop']; ?></strong> is 
                                                                    <strong class="text-info">PENDING</strong>
                                                                </h6>
                                                            </div>
                                                            <i class="bi bi-hourglass-split text-info fs-4"></i>
                                                        </div>
                                                        <?php
                                                    }
                                                    
                                                    elseif ($row['status'] == "APPROVED"){
$select = "SELECT shop,shop_full,momo_name, momo_number FROM shop  
           WHERE user_id='$user_id' AND status='APPROVED' AND activeness='ACTIVE'";
$sql = mysqli_query($con, $select);

if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_array($sql)) {
        // If MoMo details exist -> show UPDATE form
        if (!empty($row['momo_number']) || !empty($row['momo_name'])) {
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-sm-10">
    <div class="card shadow-lg border-0 rounded-3">
      <div class="card-header bg-primary text-white text-center rounded-top">
        <h5 class="mb-0">Update Your MoMo Details</h5>
      </div>

      <form action="process-momo.php" method="POST" enctype="multipart/form-data">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-bold">MoMo Number</label>
            <input type="tel" class="form-control" name="momo_number"
                   value="<?php echo htmlspecialchars($row['momo_number']); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">MoMo Name</label>
            <input type="text" class="form-control" name="momo_name"
                   value="<?php echo htmlspecialchars($row['momo_name']); ?>" required>
            <small class="text-muted">Ensure this matches your registered MoMo name.</small>
          </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom">
          <!-- Spinner -->
          <div class="loading-overlay" id="spinner_momo" style="display:none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2">Please wait...</span>
          </div>

          <div class="ms-auto">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="submit_momo" class="btn btn-primary" id="btnMomo">Update</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!--
  <div class="col-sm-6 col-12">
          <div class="card shadow-lg border-0 rounded-3">
      <div class="card-header bg-primary text-white text-center rounded-top">
        <h5 class="mb-0">Update Your Shop</h5>
      </div>
           <form action="process-shop.php" method="POST" enctype="multipart/form-data">
        <div class="card-body">
          <div class="mb-3">
                <div class="form-group">
                    <label for="shop_full" class="form-label">SHOP FULLNAME</label>
                    <input type="text" class="form-control" id="shop_full" name="shop_full" value="<?php echo htmlspecialchars($row['shop_full']); ?>" required>
                </div>

          <div class="mb-3">
                           <div class="form-group">
                    <label for="shop" class="form-label">SHOP SHORT NAME</label>
                    <input type="text" class="form-control" id="shop" name="shop" 
                       placeholder="Up to 10 letters, no spaces or numbers" 
                       pattern="^[A-Za-z]{1,10}$" maxlength="10" 
                       title="1 to 10 letters only, no spaces or numbers" value="<?php echo htmlspecialchars($row['shop']); ?>" required>
                </div>
          </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom">
          <!-- Spinner 
          <div class="loading-overlay" id="spinner_momo" style="display:none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2">Please wait...</span>
          </div>

          <div class="ms-auto">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="shop_update" class="btn btn-primary" id="btnMomo">Update</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  -->
  </div>
</div>
<?php
        } else {
            // If MoMo details are empty -> show REGISTER form
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-sm-10">
    <div class="card shadow-lg border-0 rounded-3">
      <div class="card-header bg-primary text-white text-center rounded-top">
        <h5 class="mb-0">Register Your MoMo Details</h5>
      </div>

      <form action="process-momo.php" method="POST" enctype="multipart/form-data">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-bold">MoMo Number</label>
            <input type="tel" class="form-control" name="momo_number" placeholder="Enter MoMo number to receive payments" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">MoMo Name</label>
            <input type="text" class="form-control" name="momo_name" placeholder="Enter exact name on your MoMo account" required>
            <small class="text-muted">Ensure this matches your registered MoMo name.</small>
          </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom">
          <!-- Spinner -->
          <div class="loading-overlay" id="spinner_momo" style="display:none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2">Please wait...</span>
          </div>

          <div class="ms-auto">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="submit_momo" class="btn btn-primary" id="btnMomo">Save</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
        }
    }
}


                                                    }
                                                ?>
                                                <?php
                                            }
                                                    
                                        }
                                        else{
                                            ?>
                                          <h6>Nothing to show here</h6>
                                             <p>Request a shop and get it approved.</p>
                                            <?php
                                        }
                                    ?>

    </div>
</div>

    <script>
        $(document).ready(function() {
    $('#btnMomo').on('click', function() {
        // Show the spinner
        $('#spinner_momo').show();
    });
    });
    </script>

    <script>
        $(document).ready(function() {
    $('#btnWithdraw').on('click', function() {
        // Show the spinner
        $('#spinner_withdraw').show();
    });
    });
    </script>

<br>
<br>
<br>

			<div class="footer">
    	        <?php include 'parts/footer.php'; ?> 
            </div>
		</div>
	</div>
</div>
	
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- Page wrapper end -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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