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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

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
       $_SESSION['wallet'] = $rows['wallet'];
    }
}
$errors = array();
$success = array();




require '../vendor/autoload.php'; // Include PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
if (isset($_POST['submit_file']) && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    if (!empty($file)) {
        // Load the Excel file
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(); // Convert sheet data to array
        $_SESSION['excel_data'] = $data; // Store in session to display in form
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
			<?php $page = 'wallet' ?>
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
<div class="modal fade" id="sample" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <p>NB: all numbers must be 10 characters long</p>
          <p>NB: We need only the numbers and the gigs. Nothing else including headers or titles</p>
          <p>NB: The sample below is for MTN. Change numbers for AT or TELECEL.</p>
          <p>NB: One file must contain numbers of the same network. Don't mix MTN with AT numbers</p>
         <img src="img/sample.png" style="width:350px; height:200px">
      </div>
    </div>
  </div>
</div>
				    
<div class="modal fade" id="upload" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <form class="mt-2" action="" method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="row gutters">
            <div class="col-sm-12 col-12">
                <div class="form-group">
                    <label for="fullName">Upload excel file here*</label>
                    <input type="file" class="form-control" id="file" name="file" accept=".xls,.xlsx" required>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
          		<div class="loading-overlay" id="spinner_upload" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" name="submit_file" class="btn btn-primary" id="supload">Upload</button>
      </div>
      </form>
    </div>
  </div>
</div>
				    

					<!-- Row start -->

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
                    <div class="card">
                      <div class="card-header bg-success text-white">
                        <?php echo $_GET['success'] ?>
                      </div>
                    </div>
                        <?php
                    }
                        else if(isset($_GET['error'])){
                        ?>
                        <div class="bg-danger text-white"><?php echo $_GET['error']; ?></div>
                        <?php
                    }
                ?>

				<div class="row gutters">
						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
					<?php
                    if(isset($_GET['network'])){
                     if($_GET['network']=="MTN"){
                         ?>
                         <div class="text-warning p-2">MTN</div>
                         <?php
                     }
                     elseif($_GET['network']=="MTNBIG"){
                        ?>
                         <div class="text-warning p-2">MTN BIG TIME</div>
                         <?php
                     }
                    elseif($_GET['network']=="AT"){
                        ?>
                         <div class="text-primary p-2">AT</div>
                         <?php
                     }
                    elseif($_GET['network']=="ATBIG"){
                        ?>
                         <div class="text-primary p-2">AT BIG TIME</div>
                         <?php
                     }
                     elseif($_GET['network']=="TELECEL"){
                        ?>
                         <div class="text-danger p-2">TELECEL</div>
                         <?php
                     }
                    }
                    else{
                        
                    }
                    ?>
								<div class="card-header">
								    <div class="row d-flex justify-content-between align-items-center">
								    <h5 class="card-title">Bulk Purchase</h5>
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#upload">Upload</button>
								    </div>
								    <a href="#" style="text-decoration:underline" data-toggle="modal" data-target="#sample">See Sample</a>
								</div>
								<hr>
								<div class="card-body">
<?php 
if (isset($_SESSION['excel_data'])): 
    include '../database/dbconfig.php'; // Include database connection
    $total_price = 0; // Initialize total price
    $network = mysqli_real_escape_string($con, $_GET['network']);
    $missing_packages = []; // Track missing gig packages
?>

    <form action="bulk_processing.php" method="POST" enctype="multipart/form-data">
        <table class="table" border="1">
            <thead class="thead-dark">
                <tr>
                    <th>Recipient</th>
                    <th>Gig</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>

            <?php
                if (!empty($network)) {
                    // Choose table dynamically
                  if($_GET['network']=="ATBIG"){
                     $target = "Agent";
                     $main_network = "AT";
                    foreach ($_SESSION['excel_data'] as $index => $row): 
                        $amount = 0; // Initialize amount
                        $gig_value = mysqli_real_escape_string($con, trim($row[1]));

                        // Fetch the price based on the Gig value
                        $query = "SELECT amount, original FROM special_offers WHERE package = ? AND network = ? AND target = ? ";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "sss", $gig_value, $main_network,$target);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            $amount = $data['amount'];
                            $original = $data['original'];
                            $total_price += $amount;
                        } else {
                            $missing_packages[] = $gig_value; // Store missing package
                            $original = "N/A"; // Default value
                        }
            ?>
                <tr>
                    <td><input type="text" class="form-control" name="beneficiary[]" value="<?= htmlspecialchars($row[0]); ?>" required></td>
                    <td><input type="text" class="form-control" name="gig[]" value="<?= htmlspecialchars($gig_value); ?>" readonly required></td>
                    <td><input type="text" class="form-control" name="price[]" value="<?= htmlspecialchars($amount); ?>" readonly required></td>
                    <td><input type="hidden" class="form-control" name="original[]" value="<?= htmlspecialchars($original); ?>" readonly required></td>
                </tr>
            <?php 
                    mysqli_stmt_close($stmt);
                    endforeach;   
                    }
                    
                    elseif($_GET['network']=="MTN"){
                     $main_network = "MTN";
                    foreach ($_SESSION['excel_data'] as $index => $row): 
                        $amount = 0; // Initialize amount
                        $gig_value = mysqli_real_escape_string($con, trim($row[1]));

                        // Fetch the price based on the Gig value
                        $query = "SELECT amount, original FROM prices WHERE package = ? AND network = ? ";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "ss", $gig_value, $main_network);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            $amount = $data['amount'];
                            $original = $data['original'];
                            $total_price += $amount;
                        } else {
                            $missing_packages[] = $gig_value; // Store missing package
                            $original = "N/A"; // Default value
                        }
            ?>
                <tr>
                    <td><input type="text" class="form-control" name="beneficiary[]" value="<?= htmlspecialchars($row[0]); ?>" required></td>
                    <td><input type="text" class="form-control" name="gig[]" value="<?= htmlspecialchars($gig_value); ?>" readonly required></td>
                    <td><input type="text" class="form-control" name="price[]" value="<?= htmlspecialchars($amount); ?>" readonly required></td>
                    <td><input type="hidden" class="form-control" name="original[]" value="<?= htmlspecialchars($original); ?>" readonly required></td>
                </tr>
            <?php 
                    mysqli_stmt_close($stmt);
                    endforeach;   
                    }
                    
                    elseif($_GET['network']=="AT"){
                     $main_network = "AT";
                    foreach ($_SESSION['excel_data'] as $index => $row): 
                        $amount = 0; // Initialize amount
                        $gig_value = mysqli_real_escape_string($con, trim($row[1]));

                        // Fetch the price based on the Gig value
                        $query = "SELECT amount, original FROM prices WHERE package = ? AND network = ?  ";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "ss", $gig_value, $main_network);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            $amount = $data['amount'];
                            $original = $data['original'];
                            $total_price += $amount;
                        } else {
                            $missing_packages[] = $gig_value; // Store missing package
                            $original = "N/A"; // Default value
                        }
            ?>
                <tr>
                    <td><input type="text" class="form-control" name="beneficiary[]" value="<?= htmlspecialchars($row[0]); ?>" required></td>
                    <td><input type="text" class="form-control" name="gig[]" value="<?= htmlspecialchars($gig_value); ?>" readonly required></td>
                    <td><input type="text" class="form-control" name="price[]" value="<?= htmlspecialchars($amount); ?>" readonly required></td>
                    <td><input type="hidden" class="form-control" name="original[]" value="<?= htmlspecialchars($original); ?>" readonly required></td>
                </tr>
            <?php 
                    mysqli_stmt_close($stmt);
                    endforeach;   
                    }
                    
                    elseif($_GET['network']=="TELECEL"){
                     $main_network = "Telecel";
                    foreach ($_SESSION['excel_data'] as $index => $row): 
                        $amount = 0; // Initialize amount
                        $gig_value = mysqli_real_escape_string($con, trim($row[1]));

                        // Fetch the price based on the Gig value
                        $query = "SELECT amount, original FROM prices WHERE package = ? AND network = ? ";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "ss", $gig_value, $main_network);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            $amount = $data['amount'];
                            $original = $data['original'];
                            $total_price += $amount;
                        } else {
                            $missing_packages[] = $gig_value; // Store missing package
                            $original = "N/A"; // Default value
                        }
            ?>
                <tr>
                    <td><input type="text" class="form-control" name="beneficiary[]" value="<?= htmlspecialchars($row[0]); ?>" required></td>
                    <td><input type="text" class="form-control" name="gig[]" value="<?= htmlspecialchars($gig_value); ?>" readonly required></td>
                    <td><input type="text" class="form-control" name="price[]" value="<?= htmlspecialchars($amount); ?>" readonly required></td>
                    <td><input type="hidden" class="form-control" name="original[]" value="<?= htmlspecialchars($original); ?>" readonly required></td>
                </tr>
            <?php 
                    mysqli_stmt_close($stmt);
                    endforeach;   
                    }
                    
                    
                } 
            ?>

            </tbody>
        </table>

        <!-- Display missing packages error if any -->
        <?php if (!empty($missing_packages)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> Package(s) was/were not found in the system. Please upload new file.
            </div>
        <?php endif; ?>

        <!-- Total Amount Display -->
        <label for="total">Total Amount:</label>
        <input type="text" name="total" value="<?= number_format($total_price, 2); ?>" class="form-control" readonly>
        
        <label for="total">Selection:</label>
        <input type="text" name="selection" value="<?php echo $_GET['network']; ?>" class="form-control" readonly>
        
        <br><br>

        <?php
        // Get user balance from agents table
        $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $select = "SELECT wallet FROM agents WHERE user_id='$user_id'";
        $sql = mysqli_query($con, $select);

        if ($sql && mysqli_num_rows($sql) > 0) {
            $bal_row = mysqli_fetch_assoc($sql);

            if ($bal_row['wallet'] > $total_price && empty($missing_packages)) { // Prevent order if errors exist
        ?>
                <div class="loading-overlay" id="spinner_submit" style="display:none;">
                    <div class="spinner"></div>
                    <div class="loading-text">Loading...</div>
                </div>
                <button type="submit" class="btn btn-primary" name="submit" id="ssubmit">Confirm and Order</button>
        <?php
            } elseif (!empty($missing_packages)) {
                echo "<div class='alert alert-warning'>Order submission is disabled due to missing packages.</div>";
            } else {
        ?>
                <div>You don't have enough Wallet Balance</div>
        <?php
            }
        }
        ?>

    </form>

<?php endif; ?>



									</div>
								</div>
							</div>
						</div>
	
					</div>
					
					    <script>
        $(document).ready(function() {
    $('#ssubmit').on('click', function() {
        // Show the spinner
        $('#spinner_submit').show();
    });
    });
    </script>
     <script>
        $(document).ready(function() {
    $('#supload').on('click', function() {
        // Show the spinner
        $('#spinner_upload').show();
    });
    });
    </script>
    
    			
<!--spinner start-->
     <style>
    .modal-body {
    position: relative; /* Allows absolute positioning of spinner within this container */
}

/* Spinner overlay styling */
.loading-overlay {
    display: none; /* Hide by default */
    position: absolute; /* Position it absolutely within the modal body */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
    z-index: 999; /* Ensure it is above other content */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner styling */
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

/* Loading text styling */
.loading-text {
    margin-top: 10px;
    font-size: 18px;
    color: #007bff;
}
    </style>

		</div>
			<!-- Page content end -->
		</div>
		<!-- Page wrapper end -->
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