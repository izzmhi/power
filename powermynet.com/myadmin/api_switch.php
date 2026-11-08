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
require_once __DIR__ . '/../configs/pmn_provider.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pmn_provider_product'])) {
    pmn_set_provider_source((string) $_POST['pmn_provider_product'], (string) ($_POST['pmn_provider_source'] ?? 'current'));
    header('Location: api_switch.php');
    exit();
}

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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $columns = ['mtn', 'at', 'telecel', 'at_big_time', 'mtn_big_time', 'afa'];

    foreach ($columns as $col) {
        if (isset($_POST[$col])) {
            $status = $_POST[$col]; // will be "API" or "EXCEL"
            $update = "UPDATE api_switch SET $col='$status'";
            $uquery = mysqli_query($con, $update);

            if ($uquery) {
                $success[$col] = ucfirst(str_replace('_',' ',$col)) . " switched to $status successfully";
            } else {
                $errors[$col] = "Switch for " . ucfirst(str_replace('_',' ',$col)) . " failed";
            }
        }
    }
}



if(isset($_POST['mtnbig_api'])){
    $status = "API";
    $update = "update api_switch set mtn_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN Big Time is Switched to API Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }

if(isset($_POST['mtnbig_excel'])){
    $status = "EXCEL";
    $update = "update api_switch set mtn_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN Big Time is Switched to Excel Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }

if(isset($_POST['mtn_api'])){
    $status = "API";
    $update = "update api_switch set mtn='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN is Switched to API Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }

if(isset($_POST['mtn_excel'])){
    $status = "EXCEL";
    $update = "update api_switch set mtn='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN is Switched to Excel Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }
    
if(isset($_POST['mtn_big_time_api'])){
    $status = "API";
    $update = "update api_switch set mtn_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN Big Time is Switched to API Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }

if(isset($_POST['mtn_big_time_excel'])){
    $status = "EXCEL";
    $update = "update api_switch set mtn_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="MTN Big Time is Switched to Excel Successfully";
      }
    else{
      $errors['produce']="Switch failed";
       }
    }
    
if(isset($_POST['at_api'])){
    $status = "API";
    $update = "update api_switch set at='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="AT is Switched to API Successfully";
      }
    else{
      $errors['produce']="Switch failed failed";
       }
    }

if(isset($_POST['at_excel'])){
    $status = "EXCEL";
    $update = "update api_switch set at='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="AT is Switched to Excel Successfully";
      }
    else{
      $errors['produce']="Switch failed failed";
       }
    }
    
if(isset($_POST['at_big_time_api'])){
    $status = "API";
    $update = "update api_switch set at_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="AT Big Time is Switched to API Successfully";
      }
    else{
      $errors['produce']="Switch failed failed";
       }
    }

if(isset($_POST['at_big_time_excel'])){
    $status = "EXCEL";
    $update = "update api_switch set at_big_time='$status' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        $success['produce']="AT Big Time is Switched to Excel Successfully";
      }
    else{
      $errors['produce']="Switch failed failed";
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
			<?php $page = 'api_switch' ?>
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
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(26px);
}
</style>

<div class="card shadow p-3">
  <div class="card-header bg-primary">
    <h5 class="card-title text-white">API / Excel Switches</h5>
  </div>
  <div class="card-body">
    <div class="row text-center">

      <!-- Telecel -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            include '../database/dbconfig.php';
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT telecel FROM api_switch LIMIT 1"));
            $current_status = $row['telecel'];
          ?>
          <p><b>Telecel</b></p>
          <label class="switch">
            <input type="hidden" name="telecel" value="EXCEL">
            <input type="checkbox" name="telecel" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>

      <!-- AT -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT at FROM api_switch LIMIT 1"));
            $current_status = $row['at'];
          ?>
          <p><b>AT</b></p>
          <label class="switch">
            <input type="hidden" name="at" value="EXCEL">
            <input type="checkbox" name="at" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>

      <!-- AT Big Time -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT at_big_time FROM api_switch LIMIT 1"));
            $current_status = $row['at_big_time'];
          ?>
          <p><b>AT Big Time</b></p>
          <label class="switch">
            <input type="hidden" name="at_big_time" value="EXCEL">
            <input type="checkbox" name="at_big_time" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>

      <!-- MTN -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT mtn FROM api_switch LIMIT 1"));
            $current_status = $row['mtn'];
          ?>
          <p><b>MTN</b></p>
          <label class="switch">
            <input type="hidden" name="mtn" value="EXCEL">
            <input type="checkbox" name="mtn" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>

      <!-- MTN Big Time -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT mtn_big_time FROM api_switch LIMIT 1"));
            $current_status = $row['mtn_big_time'];
          ?>
          <p><b>MTN Big Time</b></p>
          <label class="switch">
            <input type="hidden" name="mtn_big_time" value="EXCEL">
            <input type="checkbox" name="mtn_big_time" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>
      
            <!-- AFA -->
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <?php
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT afa FROM api_switch LIMIT 1"));
            $current_status = $row['afa'];
          ?>
          <p><b>MTN AFA</b></p>
          <label class="switch">
            <input type="hidden" name="afa" value="EXCEL">
            <input type="checkbox" name="afa" value="API" onchange="this.form.submit()" 
              <?php if ($current_status == 'API') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo ($current_status == 'API') ? "API: ON" : "Excel: ON"; ?></p>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- API Provider: Current API (CodeCraft / DataWorks) or Realestplug -->
<div class="card shadow p-3 mt-4">
  <div class="card-header bg-primary">
    <h5 class="card-title text-white">API Provider</h5>
  </div>
  <div class="card-body">
    <p class="text-muted mb-3">Choose who delivers each product when its switch above is on API. Off = Current API (CodeCraft / DataWorks). On = Realestplug.</p>
    <div class="row text-center">
      <?php foreach (pmn_provider_products() as $pkey => $plabel): $psrc = pmn_provider_source($pkey); ?>
      <div class="col-md-2 col-6">
        <form method="POST" action="">
          <p><b><?php echo htmlspecialchars($plabel); ?></b></p>
          <input type="hidden" name="pmn_provider_product" value="<?php echo $pkey; ?>">
          <label class="switch">
            <input type="hidden" name="pmn_provider_source" value="current">
            <input type="checkbox" name="pmn_provider_source" value="realest" onchange="this.form.submit()"
              <?php if ($psrc === 'realest') echo 'checked'; ?>>
            <span class="slider round"></span>
          </label>
          <p><?php echo $psrc === 'realest' ? 'Realestplug' : 'Current API'; ?></p>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

			
			
			
			
		</div>
		
					<!-- Row start -->



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