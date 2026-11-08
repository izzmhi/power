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

$success = array();
$errors = array();

if (isset($_POST['submit_agent'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $networks = $_POST['network'];
    $packages = $_POST['package']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts

    // Check if any package or amount fields are empty
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }

    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $network = mysqli_real_escape_string($con, $networks[$index]);

            // Check if the record already exists
            $selectquery = "SELECT * FROM prices_agents WHERE agent_id='$agent_id' AND agent_name='$agent_name' AND network='$network' AND package='$package'";
            $query = mysqli_query($con, $selectquery);
            $selectcount = mysqli_num_rows($query);

            if ($selectcount > 0) {
                // Update existing record
                $updatequery = "UPDATE prices_agents SET amount='$amount' WHERE agent_id='$agent_id' AND agent_name='$agent_name' AND network='$network' AND package='$package'";
                $uquery = mysqli_query($con, $updatequery);

                if ($uquery) {
                    $success["update_$index"] = "Package " . ($index + 1) . " is updated successfully";
                } else {
                    $errors["update_$index"] = "Failed to update Package " . ($index + 1);
                }
            } else {
                // Insert new record
                $insertquery = "INSERT INTO prices_agents (agent_id, agent_name, email, network, package, amount) VALUES ('$agent_id','$agent_name','$email','$network', '$package', '$amount')";
                $insert = mysqli_query($con, $insertquery);

                if ($insert) {
                    $success["produce_$index"] = "Package " . ($index + 1) . " prices are set successfully";
                } else {
                    $errors["produce_$index"] = "Failed to set prices for Package " . ($index + 1);
                }
            }
        }
    }
}

if (isset($_POST['update_mtn'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $network = "MTN";
    $packages = $_POST['package']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
    $ids = $_POST['id'];           // Array of IDs

    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }

    // If there are no errors, proceed with the update
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $id = mysqli_real_escape_string($con, $ids[$index]); // Ensure ID is safe to use in query

            // Update query for each row based on the unique id
            $updatequery = "UPDATE prices_agents SET amount='$amount' WHERE agent_id='$agent_id' AND id='$id' AND network='$network'";
            $uquery = mysqli_query($con, $updatequery);

            if ($uquery) {
                 $success["update"] = "Package(s) updated successfully";
            } else {
                $errors["update_$index"] = "Failed to update Package " . ($index + 1);
            }
        }
    }
}

if (isset($_POST['update_telecel'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $network = "TELECEL";
    $packages = $_POST['package']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
    $ids = $_POST['id'];           // Array of IDs

    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }

    // If there are no errors, proceed with the update
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $id = mysqli_real_escape_string($con, $ids[$index]); // Ensure ID is safe to use in query

            // Update query for each row based on the unique id
            $updatequery = "UPDATE prices_agents SET amount='$amount' WHERE agent_id='$agent_id' AND id='$id' AND network='$network'";
            $uquery = mysqli_query($con, $updatequery);

            if ($uquery) {
                $success["update"] = "Package(s) updated successfully";
            } else {
                $errors["update_$index"] = "Failed to update Package " . ($index + 1);
            }
        }
    }
}

if (isset($_POST['update_at'])) {
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $network = "AT";
    $packages = $_POST['package']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
    $ids = $_POST['id'];           // Array of IDs

    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }

    // If there are no errors, proceed with the update
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $id = mysqli_real_escape_string($con, $ids[$index]); // Ensure ID is safe to use in query

            // Update query for each row based on the unique id
            $updatequery = "UPDATE prices_agents SET amount='$amount' WHERE agent_id='$agent_id' AND id='$id' AND network='$network'";
            $uquery = mysqli_query($con, $updatequery);

            if ($uquery) {
                $success["update"] = "Package(s) updated successfully";
            } else {
                $errors["update_$index"] = "Failed to update Package " . ($index + 1);
            }
        }
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
			<?php $page = 'price' ?>
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
<!-- Modal -->
<div class="modal fade" id="ModalAgent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agents Package Pricing</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
            <?php
            include '../database/dbconfig.php';        
            $select = "SELECT * FROM prices ORDER BY network";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                    <div class="row gutters">
                                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-4 col-4">
											<div class="form-group">
												<label for="fullName">Network</label>
												<input type="text" class="form-control" id="fullName" name="network[]" value="<?php echo $row['network'] ?>" readonly required>
											</div>
											</div>
										<div class="col-sm-4 col-4">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-4 col-4">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
									</div>
                            
                  <?php
                }
              }
              else{
                  ?>
                                    <div>No Package is Available</div>
                  <?php
              }
            ?>				

        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="submit_agent" class="btn btn-block btn-primary" id="setBtn">Set Prices</button>
    </div>
</form>

    </div>
  </div>
</div>


				<!-- Main container start -->
				<div class="main-container">
                   <h2>Price Management</h2>
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

            	<div class="row mb-1">
                <div class="col-lg-4 col-4">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAgent">SET New Package</button>
                </div>
            </div>
      
      		<div class="row gutters">

						<div class="col-lg-8 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title"></div>
								</div>
								<div class="card-body">
<ul class="nav nav-tabs" id="aumyTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="aumtn-tab" data-toggle="tab" href="#aumtn" role="tab" aria-controls="aumtn" aria-selected="true">MTN</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="auat-tab" data-toggle="tab" href="#auat" role="tab" aria-controls="auat" aria-selected="false">AT</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="autelecel-tab" data-toggle="tab" href="#autelecel" role="tab" aria-controls="autelecel" aria-selected="false">TELECEL</a>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
    
    <!--Mtn-->
  <div class="tab-pane fade show active" id="aumtn" role="tabpanel" aria-labelledby="aumtn-tab">
      <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $network = "MTN";
            $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                    <div class="row gutters">
                                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
									</div>
                            
                  <?php
                }
              }
              else{
                  ?>
                                    <div>No Package is Available</div>
                  <?php
              }
            ?>								    
								    
								    <div class="col-sm-12">
									    <div class="spinner" id="spinner_updatemtn" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            
                                            </div>
											<div class="text-right">
												<button type="submit" name="update_mtn" class="btn btn-success" id="mtnupdateBtn">Update Prices</button>
											</div>
										</div>
									</form>
    </div>
      
      <!--AT-->
  <div class="tab-pane fade" id="auat" role="tabpanel" aria-labelledby="auat-tab">
    <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $network = "AT";
            $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                    <div class="row gutters">
                                               <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
									</div>
                            
                  <?php
                }
              }
              else{
                  ?>
                                    <div>No Package is Available</div>
                  <?php
              }
            ?>								    
								    
							<div class="col-sm-12">
								    <div class="spinner" id="spinner_updateat" style="display: none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </div>
											<div class="text-right">
												<button type="submit" name="update_at" class="btn btn-success" id="atupdateBtn">Update Prices</button>
											</div>
										</div>
							</form>
      </div>
      
      <!--TELECEL-->
  <div class="tab-pane fade" id="autelecel" role="tabpanel" aria-labelledby="autelecel-tab">
      <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $network = "TELECEL";
            $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                    <div class="row gutters">
                                               <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-6 col-6">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
									</div>
                            
                  <?php
                }
              }
              else{
                  ?>
                                    <div>No Package is Available</div>
                  <?php
              }
            ?>								    
								    
								            <div class="col-sm-12">
									            <div class="spinner" id="spinner_updatetelecel" style="display: none;">
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    </div>
									        		<div class="text-right">
									        			<button type="submit" name="update_telecel" class="btn btn-success" id="telecelupdateBtn">Update Prices</button>
									        		</div>
									        	</div>
									        </form>
                                         </div>
                                     </div>
								</div>
							</div>
						</div>

						<div class="col-lg-4 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Packages</div>
								</div>
								<div class="card-body">
								    
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="amtn-tab" data-toggle="tab" href="#amtn" role="tab" aria-controls="amtn" aria-selected="true">MTN</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="aat-tab" data-toggle="tab" href="#aat" role="tab" aria-controls="aat" aria-selected="false">AT</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="atelecel-tab" data-toggle="tab" href="#atelecel" role="tab" aria-controls="atelecel" aria-selected="false">TELECEL</a>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
    
    <!--MTN-->
  <div class="tab-pane fade show active" id="amtn" role="tabpanel" aria-labelledby="amtn-tab">
    <div class="table-responsive">
    <table id="basicExample" class="table custom-table table-sm">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Bundle</th>
            <th>Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include '../database/dbconfig.php';
        $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $network = "MTN";
        $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id'";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        
        if ($check) {
            $counter = 1; // Initialize row number counter
            while ($row = mysqli_fetch_array($sql)) {
                ?>
                <tr>
                    <td><?php echo $counter; ?></td> <!-- Display row number -->
                    <td><?php echo htmlspecialchars($row['package']); ?></td>
                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                </tr>
                <?php
                $counter++; // Increment row number counter
            }
        } else {
            ?>
            <tr>
                <td colspan="3">No Package is Available</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

    </div>
   
   <!--AT-->   
  <div class="tab-pane fade" id="aat" role="tabpanel" aria-labelledby="aat-tab">
      <div class="table-responsive">
    <table id="basicExample" class="table custom-table table-sm">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Bundle</th>
            <th>Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include '../database/dbconfig.php';
        $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $network = "AT";
        $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id'";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        
        if ($check) {
            $counter = 1; // Initialize row number counter
            while ($row = mysqli_fetch_array($sql)) {
                ?>
                <tr>
                    <td><?php echo $counter; ?></td> <!-- Display row number -->
                    <td><?php echo htmlspecialchars($row['package']); ?></td>
                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                </tr>
                <?php
                $counter++; // Increment row number counter
            }
        } else {
            ?>
            <tr>
                <td colspan="3">No Package is Available</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

      </div>
      
    <!--TELECEL-->
  <div class="tab-pane fade" id="atelecel" role="tabpanel" aria-labelledby="atelecel-tab">
 <div class="table-responsive">
    <table id="basicExample" class="table custom-table table-sm">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Bundle</th>
            <th>Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include '../database/dbconfig.php';
        $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $network = "TELECEL";
        $select = "SELECT * FROM prices_agents WHERE network = '$network' AND agent_id='$agent_id' ";
        $sql = mysqli_query($con, $select);
        $check = mysqli_num_rows($sql) > 0;
        
        if ($check) {
            $counter = 1; // Initialize row number counter
            while ($row = mysqli_fetch_array($sql)) {
                ?>
                <tr>
                    <td><?php echo $counter; ?></td> <!-- Display row number -->
                    <td><?php echo htmlspecialchars($row['package']); ?></td>
                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                </tr>
                <?php
                $counter++; // Increment row number counter
            }
        } else {
            ?>
            <tr>
                <td colspan="3">No Package is Available</td>
            </tr>
            <?php
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
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
    $(document).ready(function() {
    $('#setBtn').on('click', function() {
    // Show the spinner
    $('#spinner_mtn').show();
    });
    });
    </script>
    
     <script>
    $(document).ready(function() {
    $('#mtnupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updatemtn').show();
    });
    });
    </script>
    
    	<script>
    $(document).ready(function() {
    $('#atupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updateat').show();
    });
    });
    </script>
    
     <script>
    $(document).ready(function() {
    $('#telecelupdateBtn').on('click', function() {
    // Show the spinner
    $('#spinner_updatetelecel').show();
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

<style>
    /* Spinner overlay styling */
    .loading-overlay {
        display: none; /* Hide by default */
        position: fixed; /* Cover the entire screen */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
        z-index: 9999; /* Ensure it is above other content */
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