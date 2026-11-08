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
<!-- Modal Big Time -->
<div class="modal fade" id="ModalBig" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Big Time Packages</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <?php
      include '../database/dbconfig.php';
      $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);

      // Fetch all packages — agent-specific if exists, otherwise use defaults
      $query = "
      SELECT 
          s.id AS sid,
          s.network,
          s.package,
          s.original,
          s.amount AS default_amount,
          COALESCE(a.agent_price, s.amount) AS agent_price,
          a.amount AS my_price,
          CASE WHEN a.agent_id IS NOT NULL THEN 1 ELSE 0 END AS is_custom
      FROM special_offers s
      LEFT JOIN agents_prices_special a 
          ON a.network = s.network 
         AND a.package = s.package 
         AND a.agent_id = '$user_id'
      WHERE s.target = 'Agent'
      ORDER BY s.network, CAST(s.package AS UNSIGNED)
      ";

      $sql = mysqli_query($con, $query);
      $packages = [];
      $isUpdating = false;

      if (mysqli_num_rows($sql) > 0) {
          while ($row = mysqli_fetch_array($sql)) {
              $packages[] = $row;
              if ($row['is_custom']) {
                  $isUpdating = true;
              }
          }
      }

      $submitName = $isUpdating ? 'update_special' : 'insert_special';
      $submitIcon = $isUpdating ? 'fa-pencil-square-o' : 'fa-plus-square';
      $submitText = $isUpdating ? 'Update' : 'Add';
      ?>

      <form action="process_price.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <?php if (!empty($packages)) : ?>
            <?php foreach ($packages as $row): ?>
              <div class="row gutters mb-2">
                <input type="hidden" name="id[]" value="<?php echo $row['sid']; ?>">

                <!-- Network -->
                <div class="col-sm-3 col-3">
                  <div class="form-group">
                    <label>Network</label>
                    <input type="text" class="form-control" name="network[]" value="<?php echo $row['network']; ?>" readonly>
                  </div>
                </div>

                <!-- Package -->
                <div class="col-sm-3 col-3">
                  <div class="form-group">
                    <label>Gig</label>
                    <input type="text" class="form-control" name="package[]" value="<?php echo $row['package']; ?>" readonly>
                  </div>
                </div>

                    <input type="hidden" class="form-control" name="original[]" value="<?php echo $row['original']; ?>" readonly> 
                <!-- Agent Price -->
                <div class="col-sm-3 col-3">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="text" class="form-control" name="agent_amount[]" value="<?php echo $row['agent_price']; ?>" readonly>
                  </div>
                </div>

                <!-- My Price -->
                <div class="col-sm-3 col-3">
  <div class="form-group">
    <label>My Price</label>
    <input 
    class="form-control"
      type="text" 
      name="amount[]" 
      onblur="formatToTwoDecimals(this)" 
      value="<?php echo $isUpdating ? number_format($row['my_price'], 2) : ''; ?>"
      required
    >
  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <!-- Action Button -->
            <div class="text-right">
              <button type="submit" name="<?php echo $submitName; ?>" class="btn btn-info" id="<?php echo $submitName; ?>Btn">
                <i class="fa <?php echo $submitIcon; ?>"></i> <?php echo $submitText; ?>
              </button>
            </div>

            <!-- Spinner -->
            <div class="loading-overlay mt-2" id="spinner_<?php echo $submitName; ?>" style="display:none;">
              <div class="spinner"></div>
              <div class="loading-text">Please Wait...</div>
            </div>

          <?php else: ?>
            <div class="alert alert-warning text-center">No Package is Available</div>
          <?php endif; ?>
        </div>
      </form>

    </div>
  </div>
</div>


<!--Modal Afa-->
<div class="modal fade" id="ModalAfa" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">MTN AFA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
 
        <?php
include '../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
// Query to select data from special_offers
$select = "SELECT * FROM agents_prices_afa WHERE agent_id='$user_id'";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;
if ($check) {
    // Initialize the counter variable
    $counter = 1;
    // Loop through the results
    while ($row = mysqli_fetch_array($sql)) {
        ?>
    <form action="process_price.php" method="POST" enctype="multipart/form-data" >
        <div class="modal-body">
          <div class="row gutters">
            <input type="hidden" class="form-control" name="id[]" value="<?php echo $row['id']; ?>" readonly>
                <input type="hidden" class="form-control" name="original[]" value="<?php echo $row['original_price']; ?>" readonly>
            <!-- Offer Column -->
            <div class="col-sm-5 col-5">
                <div class="form-group">
                    <label for="offerName">Agent price</label>
                    <input type="text" class="form-control" name="agent_amount[]" value="<?php echo $row['agent_price']; ?>" readonly>
                </div>
            </div>

            <!-- Price Column -->
            <div class="col-sm-5 col-5">
  <div class="form-group">
    <label>My Price</label>
    <input 
    class="form-control"
      type="text" 
      name="amount[]" 
      onblur="formatToTwoDecimals(this)" 
      value="<?php echo $isUpdating ? number_format($row['amount'], 2) : ''; ?>"
      required
    >
  </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-sm-2 col-2">
                <div class="form-group">
                     <div>Action</div>
                     <button type="submit" name="update_afa" class="btn btn-info" id="updateAfaBtn"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_update_afa" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>

   </form>
        <?php
        // Increment the counter after each iteration
        $counter++;
          }
        } else {
include '../database/dbconfig.php';
// Query to select data from special_offers
$select = "SELECT * FROM afa_prices WHERE target='Agent'";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;
if ($check) {
    // Initialize the counter variable
    $counter = 1;
    // Loop through the results
    while ($row = mysqli_fetch_array($sql)) {
        ?>
    <form action="process_price.php" method="POST" enctype="multipart/form-data" >
        <div class="modal-body">
          <div class="row gutters">
            <input type="hidden" class="form-control" name="id[]" value="<?php echo $row['id']; ?>" required>
                <input type="hidden" class="form-control" name="original[]" value="<?php echo $row['original']; ?>" readonly>
            <!-- Offer Column -->
            <div class="col-sm-4 col-4">
                <div class="form-group">
                    <label for="offerName">Agent price</label>
                    <input type="text" class="form-control" name="agent_amount[]" value="<?php echo $row['amount']; ?>" readonly>
                </div>
            </div>

            <!-- Price Column -->
            <div class="col-sm-4 col-4">
  <div class="form-group">
    <label>My Price</label>
    <input 
    class="form-control"
      type="text" 
      name="amount[]" 
      onblur="formatToTwoDecimals(this)" 
      value="<?php echo $isUpdating ? number_format($row['amount'], 2) : ''; ?>"
      required
    >
  </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-sm-2 col-2">
                <div class="form-group">
                     <div>Action</div>
                     <button type="submit" name="insert_afa" class="btn btn-info" id="insertAfaBtn"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_insert_afa" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
   </form>
        <?php
        // Increment the counter after each iteration
        $counter++;
          }
        } else {
            ?>
            <div>No Package is Available</div>
            <?php
        }
        }
    ?>
    </div>
  </div>
</div>

		<!-- Main container start -->
<div>

<div class="row align-items-center">
  <!-- Dashboard Title -->
<div class="col-5">
  <h4 class="page-title d-flex align-items-center">
    <span class="page-title-icon bg-light border-info text-white me-2">
      <i class="mdi mdi-home"></i>
    </span>
    <div class="dropdown bg-info">
      <a class="nav-link dropdown-toggle text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        Menu
      </a>
      <div class="dropdown-menu bg-info text-white">
        <a class="dropdown-item text-white fs-3" href="shop.php">Home</a>
            <a class="dropdown-item text-white fs-3" href="shop_orders.php">Shop Orders</a>
          <a class="dropdown-item text-white fs-3" href="shop_payouts.php">Payouts History</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-white fs-3" href="shop_settings.php">Settings</a>
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
                                                    ?>
                                                    
                                                <!-- Card Wrapper -->
<div class="card shadow-sm mb-4 border-0 rounded-4">
  <div class"card-body">
      <h6 class="bg-secondary text-white p-2">Note: "My Price (Your price giving to your customers)" must be higher than "Agent Price (Price given to you)" before you can earn profits.</h6>
        <div class="row align-items-center p-2">
            <div class="col-12">
                <button type="button" class="btn btn-primary flex-fill mx-1" data-toggle="modal" data-target="#ModalBig">
                    <i class="fa fa-bolt me-1"></i> Big Time
                </button>
                <button type="button" class="btn btn-warning flex-fill mx-1" data-toggle="modal" data-target="#ModalAfa">
                    <i class="fa fa-bolt me-1"></i> MTN AFA
                </button>
            </div>
        </div>

        <div class="row align-items-center" style="text-align:center;">
            <div class="col-12">
<?php
include '../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
// Check if agent has custom prices
$query = "SELECT * FROM agents_prices_packages WHERE agent_id='$user_id'";
$sql = mysqli_query($con, $query);
$isUpdating = mysqli_num_rows($sql) > 0;
$packages = [];
if ($isUpdating) {
    while ($row = mysqli_fetch_array($sql)) {
        $packages[] = $row;
    }
    $submitName = 'update_package';
    $submitIcon = 'fa-pencil-square-o';
    $submitText = 'Update';
} else {
    // Load default packages
   $query = "SELECT * FROM prices WHERE network IN ('AT', 'MTN', 'TELECEL') ORDER BY network, CAST(package AS UNSIGNED) ASC";
    $sql = mysqli_query($con, $query);
    if (mysqli_num_rows($sql) > 0) {
        while ($row = mysqli_fetch_array($sql)) {
            $packages[] = $row;
        }
    }
    else{
        
    }
    $submitName = 'insert_package';
    $submitIcon = 'fa-plus-square';
    $submitText = 'Add';
}
?>

<form action="process_price.php" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <?php if (!empty($packages)) : ?>
      <?php foreach ($packages as $index => $row): ?>
        <div class="row gutters mb-2">
          <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">

                    <!-- Package -->
          <div class="col-sm-3 col-3">
            <div class="form-group">
              <label>Network</label>
              <input type="text" class="form-control" name="network[]" value="<?php echo $row['network']; ?>" readonly>
            </div>
          </div>
          
          <!-- Package -->
          <div class="col-sm-2 col-3">
            <div class="form-group">
              <label>Gig</label>
              <input type="text" class="form-control" name="package[]" value="<?php echo $row['package']; ?>" readonly>
            </div>
          </div>

          <!-- Original Price (Only visible during insert from special_offers) -->
          <?php if (!$isUpdating): ?>
              <input type="hidden" class="form-control" name="original[]" value="<?php echo $row['original']; ?>" readonly>
          <?php endif; ?>

          <!-- Agent Price -->
          <div class="col-sm-3 col-3">
            <div class="form-group">
              <label>Price</label>
              <input type="text" class="form-control" name="agent_amount[]" value="<?php echo $isUpdating ? $row['agent_price'] : $row['amount']; ?>" readonly>
            </div>
          </div>

          <!-- Selling Price -->
          <div class="col-sm-3 col-3">
  <div class="form-group">
    <label>My Price</label>
    <input 
    class="form-control"
      type="text" 
      name="amount[]" 
      onblur="formatToTwoDecimals(this)" 
      value="<?php echo $isUpdating ? number_format($row['amount'], 2) : ''; ?>"
      required
    >
  </div>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Action Button -->
      <div class="text-right">
        <button type="submit" name="<?php echo $submitName; ?>" class="btn btn-info" id="<?php echo $submitName; ?>Btn">
          <i class="fa <?php echo $submitIcon; ?>"></i> <?php echo $submitText; ?>
        </button>
      </div>

      <!-- Spinner -->
      <div class="loading-overlay mt-2" id="spinner_<?php echo $submitName; ?>" style="display:none;">
        <div class="spinner"></div>
        <div class="loading-text">Please Wait...</div>
      </div>

    <?php else: ?>
      <div class="alert alert-warning text-center">No Package is Available</div>
    <?php endif; ?>
  </div>
</form>
            </div>
         </div>
  </div>
</div> 

<script>
function formatToTwoDecimals(el) {
  if (el.value.trim() !== '') {
    let num = parseFloat(el.value);
    if (!isNaN(num)) {
      el.value = num.toFixed(2); // Forces 2 decimals
    } else {
      el.value = ''; // reset if invalid number
    }
  }
}
</script>
                                                    
                                                    <?php
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