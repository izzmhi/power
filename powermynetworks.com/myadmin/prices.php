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

$success = array();
$errors = array();

if(isset($_POST['submit_agent'])){
    $network = mysqli_real_escape_string($con, $_POST['network']);
    $packages = $_POST['package']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
    $originals = $_POST['original'];
    $target = $_POST['target'];
    if (empty($network)) {
        $errors['network'] = "Select a network";
    }
        if (empty($target)) {
        $errors['target'] = "Select a target";
    }
    // Check if any package or amount fields are empty
    foreach($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original" . ($index + 1) . " is required";
        }
        
    }
    
    $selectquery = "select * from prices where network = '$network' AND package = '$package' AND target = '$target'";
    $query = mysqli_query($con,$selectquery);
    $selectcount = mysqli_num_rows($query);
    if($selectcount>0){
     $errors['email_exist'] = "Packages already exist for this networ and target";
    }
    else{

    if (empty($errors)) {
        foreach($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            $insertquery = "INSERT INTO prices (target,network,package,amount,original) VALUES ('$target','$network','$package','$amount','$original')";
            $insert = mysqli_query($con, $insertquery);
            if (!$insert) {
                $errors["insert_$index"] = "Failed to insert Package '" . $package . "'";
            }
        }

        if (empty($errors)) {
            $success = "All packages added successfully.";
            header("Location: prices.php?success=" . urlencode($success));
            exit();
        } else {
            $errors['general'] = "Some packages could not be added.";
        }
    }
  }
}

if (isset($_POST['update_mtn_agent'])) {
    $network   = "MTN"; 
    $target   = "AGENT"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
           $updatePrices = "UPDATE prices SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target = '$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $updateAgents = "UPDATE agents_prices_packages SET agent_price='$amount', original='$original' WHERE network='$network' AND package='$package'";
                $query2 = mysqli_query($con, $updateAgents);
                if ($query2) {
                    $success['success'] = "Package updated successfully";
                } else {
                    $errors['error'] = "Failed to update agents price list";
                }
            } else {
                $errors['error'] = "Failed to update main price list";
            }
        }
    }
}

if (isset($_POST['update_telecel_agent'])) {
   $network   = "TELECEL"; 
   $target   = "AGENT"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
            $updatePrices = "UPDATE prices SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target = '$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $updateAgents = "UPDATE agents_prices_packages SET agent_price='$amount', original='$original' WHERE network='$network' AND package='$package'";
                $query2 = mysqli_query($con, $updateAgents);
                if ($query2) {
                    $success['success'] = "Package updated successfully";
                } else {
                    $errors['error'] = "Failed to update agents price list";
                }
            } else {
                $errors['error'] = "Failed to update main price list"; 
            }
        }
    }
}

if (isset($_POST['update_at_agent'])) {
   $network   = "AT"; 
   $target   = "AGENT"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
            $updatePrices = "UPDATE prices SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target = '$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $updateAgents = "UPDATE agents_prices_packages SET agent_price='$amount', original='$original' WHERE network='$network' AND package='$package'";
                $query2 = mysqli_query($con, $updateAgents);
                if ($query2) {
                    $success['success'] = "Package updated successfully";
                } else {
                    $errors['error'] = "Failed to update agents price list";
                }
            } else {
                $errors['error'] = "Failed to update main price list";
            }
        }
    }
}


if (isset($_POST['update_mtn_customer'])) {
   $network   = "MTN"; 
   $target   = "CUSTOMER"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
            $updatePrices = "UPDATE prices  SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target='$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $success['success'] = "Package updated successfully";
            } else {
                $errors['error'] = "Failed to update main price list";
            }
        }
    }
}

if (isset($_POST['update_telecel_customer'])) {
   $network   = "TELECEL"; 
   $target   = "CUSTOMER"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
            $updatePrices = "UPDATE prices  SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target='$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $success['success'] = "Package updated successfully";
            } else {
                $errors['error'] = "Failed to update main price list";
            }
        }
    }
}

if (isset($_POST['update_at_customer'])) {
   $network   = "AT"; 
   $target   = "CUSTOMER"; 
    $packages  = $_POST['package']; 
    $amounts   = $_POST['amount'];   
    $ids       = $_POST['id'];            
    $originals = $_POST['original'];
    // Validate input
    foreach ($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original Amount " . ($index + 1) . " is required";
        }
    }
    if (empty($errors)) {
        foreach ($packages as $index => $package) {
            $package  = mysqli_real_escape_string($con, $package);
            $amount   = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            // Update in prices table
            $updatePrices = "UPDATE prices  SET amount='$amount', original='$original'WHERE network='$network' AND package='$package' AND target='$target'";
            $query1 = mysqli_query($con, $updatePrices);
            // Update in agents_prices_packages table
            if ($query1) {
                $success['success'] = "Package updated successfully";
            } else {
                $errors['error'] = "Failed to update main price list";
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
			<?php $page = 'prices' ?>
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
        <h5 class="modal-title" id="exampleModalLabel">Add Package</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
                <div class="mb-3">
     <select id="target" name="target" class="form-control" required>
    <option value="">Select a Target</option>
    <option value="AGENT">AGENT</option>
    <option value="CUSTOMER">CUSTOMER</option>
    </select>
        </div>
        
    <div class="mb-3">
     <select id="nectwork" name="network" class="form-control" required>
    <option value="">Select a Network</option>
    <option value="MTN">MTN</option>
    <option value="AT">AT</option>
    <option value="TELECEL">TELECEL</option>
    </select>
        </div>
     <div class="form-group">
		<label for="fullName">Number of Packages</label>
		<input type="number" class="form-control" id="agent_total" name="total" required>
	</div>
											
    <div class="row gutters" id="agent_count">
<script>
document.getElementById('agent_total').addEventListener('input', function () {
    let total = parseInt(this.value); // Get the number of rows from the input
    let countDiv = document.getElementById('agent_count'); // The container for the rows

    // Clear any existing rows
    countDiv.innerHTML = '';

    if (!isNaN(total) && total > 0) {
        for (let i = 0; i < total; i++) {
            // Create new row
            let row = document.createElement('div');
            row.className = 'row gutters';

            // Create package input (occupying half of the row)
            let packageCol = document.createElement('div');
            packageCol.className = 'col-sm-4 col-3';

            let packageGroup = document.createElement('div');
            packageGroup.className = 'form-group';

            let packageLabel = document.createElement('label');
            packageLabel.innerText = `Package(Gb) ${i + 1}`;

            let packageInput = document.createElement('input');
            packageInput.type = 'text';
            packageInput.className = 'form-control';
            packageInput.name = `package[]`; // Use array notation
            packageInput.required = true;

            packageGroup.appendChild(packageLabel);
            packageGroup.appendChild(packageInput);
            packageCol.appendChild(packageGroup);
            
            // Create price input (occupying half of the row)
            let priceCol = document.createElement('div');
            priceCol.className = 'col-sm-4 col-3';

            let priceGroup = document.createElement('div');
            priceGroup.className = 'form-group';

            let priceLabel = document.createElement('label');
            priceLabel.innerText = `Price(GHS) ${i + 1}`;

            let priceInput = document.createElement('input');
            priceInput.type = 'text';
            priceInput.className = 'form-control';
            priceInput.name = `amount[]`; // Use array notation
            priceInput.required = true;

            priceGroup.appendChild(priceLabel);
            priceGroup.appendChild(priceInput);
            priceCol.appendChild(priceGroup);
            priceCol.appendChild(priceGroup);
            
                        // Create original price input (occupying half of the row)
            let originalCol = document.createElement('div');
            originalCol.className = 'col-sm-4 col-3';

            let originalGroup = document.createElement('div');
            originalGroup.className = 'form-group';

            let originalLabel = document.createElement('label');
            originalLabel.innerText = `Original Price(GHS) ${i + 1}`;

            let originalInput = document.createElement('input');
            originalInput.type = 'text';
            originalInput.className = 'form-control';
            originalInput.name = `original[]`; // Use array notation
            originalInput.required = true;

            originalGroup.appendChild(originalLabel);
            originalGroup.appendChild(originalInput);
            originalCol.appendChild(originalGroup);
            originalCol.appendChild(originalGroup);

             // Append all three columns to the row
            row.appendChild(packageCol);
            row.appendChild(priceCol);
            row.appendChild(originalCol);

            // Append the row to the container
            countDiv.appendChild(row);
        }
    }
});
</script>
	</div>
	
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
                   <h6>Price Management</h6>
                   
                           <div class="row mb-1">
            <div class="col-lg-4 col-4">
                <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAgent">New Package</button>
            </div>
        </div>
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
<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-agent-tab" data-toggle="tab" href="#nav-agent" role="tab" aria-controls="nav-agent" aria-selected="true">Agents</a>
    <a class="nav-item nav-link" id="nav-customers-tab" data-toggle="tab" href="#nav-customers" role="tab" aria-controls="nav-customers" aria-selected="false">Customers</a>
  </div>
</nav>


<div class="tab-content" id="nav-tabContent">
    <!--Agent-->
    <div class="tab-pane fade show active" id="nav-agent" role="tabpanel" aria-labelledby="nav-agent-tab">

      <!-- Bootstrap 4 Tabs Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

      
      	<div class="row gutters">
			<div class="col-lg-12 col-sm-12 col-12">
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
            $network = "MTN";
            $target = "AGENT";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                <div class="row gutters">
                    <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
					<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Package (GIG)</label>
							<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
						</div>
						</div>
						<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Price (GHS)</label>
							<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
						</div>
					</div>
					<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Original Price (GHS)</label>
							<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
						</div>
					</div>
					<div class="col-sm-3 col-4">
						<div class="form-group">
							<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
					<div class="loading-overlay" id="spinner_updatemtn" style="display:none;">
                        <div class="spinner"></div>
                        <div class="loading-text">Please Wait...</div>
                    </div>			    
				  <div class="col-sm-12">
					<div class="text-right">
						<button type="submit" name="update_mtn_agent" class="btn btn-success" id="mtnupdateBtn">Update Prices</button>
					</div>
				</div>
				</form>
    </div>
      
      <!--AT-->
  <div class="tab-pane fade" id="auat" role="tabpanel" aria-labelledby="auat-tab">
    <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $network = "AT";
            $target = "AGENT";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                    <div class="row gutters">
                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
						<div class="col-sm-3 col-3">
							<div class="form-group">
								<label for="fullName">Package (GIG)</label>
								<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
							</div>
							</div>
							<div class="col-sm-3 col-3">
							<div class="form-group">
								<label for="fullName">Price (GHS)</label>
								<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
							</div>
						</div>
						<div class="col-sm-3 col-3">
							<div class="form-group">
								<label for="fullName">Original Price (GHS)</label>
								<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
							</div>
						</div>
						<div class="col-sm-3 col-4">
							<div class="form-group">
								<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
										    		<div class="loading-overlay" id="spinner_updateat" style="display:none;">
                                                        <div class="spinner"></div>
                                                        <div class="loading-text">Please Wait...</div>
                                                    </div>		    
					<div class="col-sm-12">
							<div class="text-right">
							<button type="submit" name="update_at_agent" class="btn btn-success" id="atupdateBtn">Update Prices</button>
						</div>
					</div>
				</form>
      </div>
      
      <!--TELECEL-->
  <div class="tab-pane fade" id="autelecel" role="tabpanel" aria-labelledby="autelecel-tab">
      <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $network = "TELECEL";
            $target = "AGENT";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                <div class="row gutters">
                    <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
					<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Package (GIG)</label>
							<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
						</div>
						</div>
						<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Price (GHS)</label>
							<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
						</div>
					</div>
					<div class="col-sm-3 col-3">
						<div class="form-group">
							<label for="fullName">Original Price (GHS)</label>
							<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
						</div>
					</div>
					<div class="col-sm-3 col-4">
						<div class="form-group">
							<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
								       				 <div class="loading-overlay" id="spinner_updatetelecel" style="display:none;">
                                                        <div class="spinner"></div>
                                                        <div class="loading-text">Please Wait...</div>
                                                    </div>		    
		   <div class="col-sm-12">
			    <div class="text-right">
			     	<button type="submit" name="update_telecel_agent" class="btn btn-success" id="telecelupdateBtn">Update Prices</button>
			    </div>
			</div>
		</form>
    </div>
                            
    <!--MTN SPECIAL-->

      
      <!--AT SPECIAL-->
  
      <!--TELECEL SPECIAL-->

                            
                        </div>
					</div>
				</div>
			</div>
		</div>
   </div>
  
  <!--Customers-->
    <div class="tab-pane fade" id="nav-customers" role="tabpanel" aria-labelledby="nav-customers-tab">

      		<div class="row gutters">
				<div class="col-lg-12 col-sm-12 col-12">
					<div class="card h-100">
						<div class="card-header">
							<div class="card-title"></div>
						</div>
						<div class="card-body">
<ul class="nav nav-tabs" id="cumyTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="cumtn-tab" data-toggle="tab" href="#cumtn" role="tab" aria-controls="cumtn" aria-selected="true">MTN</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="cuat-tab" data-toggle="tab" href="#cuat" role="tab" aria-controls="cuat" aria-selected="false">AT</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="cutelecel-tab" data-toggle="tab" href="#cutelecel" role="tab" aria-controls="cutelecel" aria-selected="false">TELECEL</a>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
    
    <!--Mtn-->
  <div class="tab-pane fade show active" id="cumtn" role="tabpanel" aria-labelledby="cumtn-tab">
      <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $network = "MTN";
            $target = "CUSTOMER";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                   <div class="row gutters">
                                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Original Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-4">
											<div class="form-group">
												<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
						      				      	<div class="loading-overlay" id="spinner_updatemtn" style="display:none;">
                                                        <div class="spinner"></div>
                                                        <div class="loading-text">Please Wait...</div>
                                                    </div>				    
				  <div class="col-sm-12">
							<div class="text-right">
						<button type="submit" name="update_mtn_customer" class="btn btn-info" id="mtnupdateBtn">Update Prices</button>
					</div>
				</div>
			</form>
        </div>
      
      <!--AT-->
  <div class="tab-pane fade" id="cuat" role="tabpanel" aria-labelledby="cuat-tab">
    <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $network = "AT";
            $target = "CUSTOMER";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                      <div class="row gutters">
                                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>

											<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Original Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-4">
											<div class="form-group">
												<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
						      						<div class="loading-overlay" id="spinner_updateat" style="display:none;">
                                                        <div class="spinner"></div>
                                                        <div class="loading-text">Please Wait...</div>
                                                    </div>						    
				  <div class="col-sm-12">
					<div class="text-right">
						<button type="submit" name="update_at_customer" class="btn btn-info" id="atupdateBtn">Update Prices</button>
					</div>
				</div>
			</form>
      </div>
      
      <!--TELECEL-->
  <div class="tab-pane fade" id="cutelecel" role="tabpanel" aria-labelledby="cutelecel-tab">
      <form action="" method="POST" enctype="multipart/form-data">
		<?php
            include '../database/dbconfig.php';
            $network = "TELECEL";
            $target = "CUSTOMER";
            $select = "SELECT * FROM prices WHERE network = '$network' AND target='$target'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                 ?>
                             
                                   <div class="row gutters">
                                        <input type="hidden" class="form-control" id="fullName" name="id[]" value="<?php echo $row['id'] ?>" required>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Package (GIG)</label>
												<input type="text" class="form-control" id="fullName" name="package[]" value="<?php echo $row['package'] ?>" readonly required>
											</div>
											</div>
											<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="amount[]" value="<?php echo $row['amount'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-3">
											<div class="form-group">
												<label for="fullName">Original Price (GHS)</label>
												<input type="text" class="form-control" id="fullName" name="original[]" value="<?php echo $row['original'] ?>" required>
											</div>
										</div>
										<div class="col-sm-3 col-4">
											<div class="form-group">
												<a href="delete.php?pid=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
								    			<div class="loading-overlay" id="spinner_updatetelecel" style="display:none;">
                                                        <div class="spinner"></div>
                                                        <div class="loading-text">Please Wait...</div>
                                                    </div>
								                  <div class="col-sm-12">
								            		<div class="text-right">
									        			<button type="submit" name="update_telecel_customer" class="btn btn-info" id="telecelupdateBtn">Update Prices</button>
									        		</div>
									        	</div>
									        	</form>
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
		


    
     

<!--spinner start-->


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