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
			<?php $page = 'home' ?>
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
            <div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-12">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
						    <h5>MTN Package Prices</h5>
							 <p>All Offers are non-expiry</p>
       <div class="row">
           <div class="col-sm-12">
               <div class="table-responsive">
                   <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">S/N</th>
                            <th></th>
                            <th scope="col">Package</th>
                            <th scope="col">Price</th>
                          </tr>
                        </thead>
                        <tbody>
            <?php
            include '../database/dbconfig.php';
            $network = "MTN";
            $select = "SELECT * FROM prices WHERE network='$network'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            $sn = 1;  // Initialize counter variable

            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
                    <tr>
                        <th scope="row"><?php echo $sn++; ?></th> <!-- Use counter for S/N -->
                        <td><img src="../assets/img/MTN.png" style="width:25px;" class="img-fluid" alt="MTN Logo"></td>
                        <td><?php echo $row['package']; ?> GB</td>
                        <td>GHS<?php echo $row['amount']; ?></td>
                    </tr>
            <?php
                }
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
			
				<div class="col-lg-4 col-sm-4 col-12">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							 <div class="row">
           <div class="col-sm-12">
               <div class="table-responsive">
                    <h5>Telecel Package Prices</h5>
                   <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">S/N</th>
                            <th></th>
                            <th scope="col">Package</th>
                            <th scope="col">Price</th>
                          </tr>
                        </thead>
                        <tbody>
            <?php
            include '../database/dbconfig.php';
            $network = "TELECEL";
            $select = "SELECT * FROM prices WHERE network='$network'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            $sn = 1;  // Initialize counter variable

            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
                    <tr>
                        <th scope="row"><?php echo $sn++; ?></th> <!-- Use counter for S/N -->
                        <td><img src="../assets/img/Telecel.jpg" style="width:25px;" class="img-fluid" alt="Telecel Logo"></td>
                        <td><?php echo $row['package']; ?> GB</td>
                        <td>GHS<?php echo $row['amount']; ?></td>
                    </tr>
            <?php
                }
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
				
					<div class="col-lg-4 col-sm-4 col-12">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							       <div class="row">
           <div class="col-sm-12">
               <div class="table-responsive">
                   <h5>AT Package Prices</h5>
                   <h6>Kindly note, data has 60 days expiry & can be rollover</h6>
                   <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">S/N</th>
                            <th></th>
                            <th scope="col">Package</th>
                            <th scope="col">Price</th>
                          </tr>
                        </thead>
                        <tbody>
            <?php
            include '../database/dbconfig.php';
            $network = "AT";
            $select = "SELECT * FROM prices WHERE network='$network'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            $sn = 1;  // Initialize counter variable

            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
                    <tr>
                        <th scope="row"><?php echo $sn++; ?></th> <!-- Use counter for S/N -->
                        <td><img src="../assets/img/AT.jpg" style="width:25px;" class="img-fluid" alt="AT Logo"></td>
                        <td><?php echo $row['package']; ?> GB</td>
                        <td>GHS<?php echo $row['amount']; ?></td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
                      </table>
                    </div>
     
            <!--          
               <div class="table-responsive">
                   <h5>Non Expiry Offers</h5>
                   <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">S/N</th>
                            <th></th>
                            <th scope="col">Package</th>
                            <th scope="col">Price</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th scope="row">2</th>
                            <td><img src="../media/AT.jpg" class="img-fluid" alt="AT Logo"></td>
                            <td>40 GB</td>
                            <td>GHS110</td>
                          </tr>
                          <tr>
                            <th scope="row">3</th>
                            <td><img src="../media/AT.jpg" class="img-fluid" alt="AT Logo"></td>
                            <td>50 GB</td>
                            <td>GHS130</td>
                          </tr>
                          <tr>
                            <th scope="row">5</th>
                            <td><img src="../media/AT.jpg" class="img-fluid" alt="AT Logo"></td>
                            <td>100 GB</td>
                            <td>GHS230</td>
                          </tr>
                          <tr>
                            <th scope="row">6</th>
                            <td><img src="../media/AT.jpg" class="img-fluid" alt="AT Logo"></td>
                            <td>200 GB</td>
                            <td>GHS450</td>
                          </tr>
                        </tbody>
                      </table>
               </div>
               -->
                                </div>
                            </div>
						</div>
					</div>
				</div>
			</div>


		</div>
			<!-- Page content end -->

		</div>

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