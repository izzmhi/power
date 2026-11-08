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

if(isset($_POST['new_offer'])){
  $package = mysqli_real_escape_string($con, $_POST['package']);
    if (empty($package)) {
        $errors['package'] = "Enter a package name";
    }
    $selectquery = "select * from special_packages where package = '$package' ";
    $query = mysqli_query($con,$selectquery);
    $selectcount = mysqli_num_rows($query);
    if($selectcount>0){
     $errors['email_exist'] = "Package already exist";
    }
    else{
            $insertquery = "INSERT INTO special_packages (package) VALUES ('$package')";
            $insert = mysqli_query($con, $insertquery);
            if ($insert) {
                $success["sucess"] = "Offer is added successfully";
            } else {
                $errors["error"] = "Failed to add an offer ";
            }
   }
}


if(isset($_POST['submit_package'])){
    $target = mysqli_real_escape_string($con, $_POST['target']);
    $offer = "IDD Calls";
    $minss = $_POST['mins']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
     if (empty($target)) {
        $errors['target'] = "Select a target";
    }
    // Check if any package or amount fields are empty
    foreach($minss as $index => $mins) {
        if (empty($mins)) {
            $errors["mins_$index"] = "Minutes " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }
    
    $selectquery = "select * from call_offers where target = '$target' AND offer_name = '$offer' AND mins = '$mins' ";
    $query = mysqli_query($con,$selectquery);
    $selectcount = mysqli_num_rows($query);
    if($selectcount>0){
     $errors['email_exist'] = "This offer exist.";
    }
    else{

    // If there are no errors, proceed with the insertion
    if (empty($errors)) {
        foreach($minss as $index => $mins) {
            $mins = mysqli_real_escape_string($con, $mins);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $insertquery = "INSERT INTO call_offers (target,offer_name,mins,amount) VALUES ('$target','$offer','$mins','$amount')";
            $insert = mysqli_query($con, $insertquery);
            if ($insert) {
                $success["sucess"] = "Offer package is added successfully";
            } else {
                $errors["error"] = "Failed to add offer Package ";
            }
        }
    }
  }
}



if (isset($_POST['update_calls_offers'])) {
    $minss = $_POST['mins']; // Array of packages
    $amounts = $_POST['amount'];   // Array of amounts
    $ids = $_POST['id'];           // Array of IDs
    // Validate input
    foreach ($minss as $index => $mins) {
        if (empty($mins)) {
            $errors["mins_$index"] = "Credit " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
    }
    // If there are no errors, proceed with the update
    if (empty($errors)) {
        foreach ($minss as $index => $mins) {
            $mins = mysqli_real_escape_string($con, $mins);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $id = mysqli_real_escape_string($con, $ids[$index]); // Ensure ID is safe to use in query
            // Update query for each row based on the unique id
            $updatequery = "UPDATE call_offers SET mins='$mins', amount='$amount' WHERE id='$id' ";
            $uquery = mysqli_query($con, $updatequery);
            if ($uquery) {
                $success["sucess"] = "Package is updated successfully";
            } else {
                $errors["error"] = "Failed to update Package ";
            }
        }
    }
}



if(isset($_POST['delete'])){
  $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
  $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
 
  $delete = "delete FROM call_orders where order_id='$order_id' AND agent_id='$agent_id'";
  $dquery = mysqli_query($con,$delete);

    if($dquery){
    $success['produce']="Order Deleted Successfully";
   }
   else{
    $errors['produce']="Failed to delete student";
    }
     
    }
   
   
 if(isset($_POST['update'])){
    $agent_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    $order_status = mysqli_real_escape_string($con, $_POST['order_status']);
    $telephone = mysqli_real_escape_string($con, $_POST['beneficiary']);
    $mins = mysqli_real_escape_string($con, $_POST['mins']);
    $update = "update call_orders set order_status='$order_status' where reference_id= '$reference_id' ";
    $uquery = mysqli_query($con,$update);
    if($uquery){
    if($order_status=="Delivered"){

          // Send SMS
    $smsData = [
        "type" => 1,
        "senderid" => "Hubcel",
        "messages" => [
            [
                "recipient" => $telephone,
                "message" => " Your account has been credited with $mins Mins  with no expiry, Dail *124# to check your balance."
            ]
        ]
    ];
    $ch = curl_init('https://api.moolre.com/open/sms/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-VASKEY: REDACTED'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        // Log or process successful response
        $success['program'] = "SMS sent successfully!";
    } else {
        $errors['msg'] = "Failed to send SMS! Response: $response";
    }

        
    }   
    else{
        
    }
      }
   
    else{
      $errors['produce']="Status update failed";
       }
      
} 

if(isset($_POST['status'])){
    $status = "Delivered";
    $old_status = "Processing";
$selects = "select * from call_orders where order_status='$old_status' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $telephone = $rows['beneficiary'];
       $mins = $rows['mins'];
    }
}
    
    $update = "update call_orders set order_status='$status' where order_status='$old_status'";
    $uquery = mysqli_query($con,$update);
    if($uquery){
        
    // Send SMS
    $smsData = [
        "type" => 1,
        "senderid" => "Hubcel",
        "messages" => [
            [
                "recipient" => $telephone,
                "message" => " Your account has been credited with $mins Mins  with no expiry, Dail *124# to check your balance."
            ]
        ]
    ];
    $ch = curl_init('https://api.moolre.com/open/sms/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-VASKEY: REDACTED'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        // Log or process successful response
        $success['program'] = "SMS sent successfully!";
    } else {
        $errors['msg'] = "Failed to send SMS! Response: $response.  All others have been confirmed and Delivered";
    }
        
  
      }
    else{
      $errors['produce']="Status update failed";
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
			<?php $page = 'calls' ?>
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

<!--Modal Package-->
<div class="modal fade" id="ModalAPackage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Call Mins Offers</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" >
    <div class="modal-body">
       <div class="mb-3">
          <select id="target" name="target" class="form-control" required>
              <option value="">Select a Target</option>
              <option value="Agent">Agent</option>
              <option value="Customer">Customer</option>
           </select>
       </div>

     <div class="form-group">
		<label for="fullName">Number of Packages</label>
		<input type="number" class="form-control" id="agent_total" name="total" required>
	</div>
											
    <div class="mb-3" id="agent_count">
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
            packageCol.className = 'col-sm-6 col-6';

            let packageGroup = document.createElement('div');
            packageGroup.className = 'form-group';

            let packageLabel = document.createElement('label');
            packageLabel.innerText = `Mins ${i + 1}`;

            let packageInput = document.createElement('input');
            packageInput.type = 'text';
            packageInput.className = 'form-control';
            packageInput.name = `mins[]`; // Use array notation
            packageInput.required = true;

            packageGroup.appendChild(packageLabel);
            packageGroup.appendChild(packageInput);
            packageCol.appendChild(packageGroup);

            // Create price input (occupying half of the row)
            let priceCol = document.createElement('div');
            priceCol.className = 'col-sm-6 col-6';

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

             // Append all three columns to the row
            row.appendChild(packageCol);
            row.appendChild(priceCol);

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
        <button type="submit" name="submit_package" class="btn btn-block btn-primary" id="setBtn">Submit Package</button>
    </div>
</form>

    </div>
  </div>
</div>

<!--Modal all-->
<div class="modal fade" id="ModalPackages" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Packages</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="call_process.php" method="POST" enctype="multipart/form-data" >
        <div class="modal-body">
        
        <?php
include '../database/dbconfig.php';
// Query to select data from special_offers
$select = "SELECT * FROM call_offers ORDER BY target, mins DESC";
$sql = mysqli_query($con, $select);
$check = mysqli_num_rows($sql) > 0;

if ($check) {
    // Initialize the counter variable
    $counter = 1;

    // Loop through the results
    while ($row = mysqli_fetch_array($sql)) {
        ?>
        <div class="row gutters">
            <input type="hidden" class="form-control" name="id[]" value="<?php echo $row['id']; ?>" required>
            <!-- S/N Column -->
            <div class="col-sm-1 col-4">
                <div class="form-group">
                    <label for="fullName">S/N</label>
                    <input type="text" class="form-control" value="<?php echo $counter; ?>" readonly>
                </div>
            </div>

            <!-- Target Column -->
            <div class="col-sm-2 col-4">
                <div class="form-group">
                    <label for="target">Target</label>
                    <input type="text" class="form-control" name="target[]" value="<?php echo $row['target']; ?>" readonly>
                </div>
            </div>

            <!-- Package Column -->
            <div class="col-sm-3 col-4">
                <div class="form-group">
                    <label for="package">Mins</label>
                    <input type="text" class="form-control" name="mins[]" value="<?php echo $row['mins']; ?>" required>
                </div>
            </div>

            <!-- Price Column -->
            <div class="col-sm-3 col-4">
                <div class="form-group">
                    <label for="amount">Price (GHS)</label>
                    <input type="text" class="form-control" name="amount[]" value="<?php echo $row['amount']; ?>" required>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-sm-3 col-4">
                <div class="form-group">
                     <div>Action</div>
                     <button type="submit" name="update_calls_offers" class="btn btn-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Update</button>
                    <a href="call_process.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <?php
        // Increment the counter after each iteration
        $counter++;
    }
} else {
    ?>
    <div>No Package is Available</div>
    <?php
}
?>
        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn_customer" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>

</form>

    </div>
  </div>
</div>

		<!--update-->
		<div class="modal fade" id="modalUpdateForm" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Order status</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row" method="POST" action="" enctype="multipart/form-data">
					<div class="modal-body">

								  <input type="text" class="form-control" id="u_reference_id" name="reference_id" readonly required>
					
						<div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Agent name:</label>
									<input type="text" class="form-control" id="u_agent_name" name="agent_name" readonly>
								</div>
							</div>
					    </div>
					    
					    <div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Beneficiary:</label>
									<input type="text" class="form-control" id="u_beneficiary" name="beneficiary" readonly>
								</div>
							</div>
					    </div>
					    
					    						<div class="row px-2">
					        <div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Mins:</label>
									<input type="text" class="form-control" id="u_mins" name="mins" readonly>
								</div>
							</div>
					    </div>
	
						<div class="row px-2">
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Order status</label>
									    <select name="order_status" class="form-control" id="u_order_status" required>
											  <option value="">Select Order Status</option>
											  <option value="Pending">Pending</option>
											  <option value="Processing">Processing</option>
									       	  <option value="Delivered">Delivered</option>
											  <option value="Cancelled">Cancelled</option>
										</select>
								</div>
							</div>
						</div>
		
						<hr>
						  <div class="row px-2">
						      <div class="col-sm-6 col-12">
								<div class="form-group">
						             <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<button type="submit" name="update" class="btn btn-success btn-block">Update</button>
								</div>
							</div>
					    </div>
					</div>
					</div>
	             </form>
				</div>
			</div>


				<!-- Main container start -->
		<div class="main-container">
            <h6>Call Mins</h6>
                   
            <div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-4">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
							<a href="print_calls.php" class="text-success" style="font-size: 1rem; text-align:center;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download</a>
						</div>
					</div>
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
			
			<div class="row mb-1">
                <div class="col-lg-3 col-6">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAPackage">New Package</button>
                </div>
                <div class="col-lg-3 col-6">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalPackages">Packages</button>
                </div>
                <div class="col-lg-3 col-6">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <button type="submit" id="order_status" name="status" class="btn btn-primary btn-block">Confirm Orders</button>
					</form>
                </div>
                <div class="col-lg-3 col-6">
                    <a href="call_offers.php" class="btn btn-success">Refresh</a>
                </div>
            </div>
					
		
		<!-- Row start -->
<div class="row gutters mt-3">
    <div class="col-xl-12 col-sm-12 col-12">
        <div class="card">
            <div class="card-body">
                
                
               	<div class="table-container">
	    
	    <div class="row">
	        <div class="col-lg-12 col-12">
	                <div class="mb-2">
        <input type="text" id="search" class="form-control" placeholder="Search reference id, agent name, beneficiary number">
    </div>
	       </div>
	        </div>
	       
	       <div class="row">
	        <div class="col-lg-4 col-6">
	                <div class="mb-2">
        <select id="agent" class="form-control">
            <option value="">Select Agent</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT agent_name FROM call_orders ORDER BY agent_name";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['agent_name']; ?>"><?php echo $row['agent_name']; ?></option>
                        <?php
                    }
                } else {
                    ?>
                    <option value="">No Order is Available</option>
                    <?php
                }
                ?>
        </select>
    </div>
	        </div>
	        	        <div class="col-lg-4 col-6">
	              <div class="mb-2">
        <select id="status" class="form-control">
            <option value="">Order Status</option>
            <?php
                include '../database/dbconfig.php';
                $select = "SELECT DISTINCT order_status FROM call_orders ";
                $sql = mysqli_query($con, $select);
                $check = mysqli_num_rows($sql) > 0;
                if ($check) {
                    while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <option value="<?php echo $row['order_status']; ?>"><?php echo $row['order_status']; ?></option>
                        <?php
                    }
                } else {
                    ?>
                    <option value="">No Order is Available</option>
                    <?php
                }
                ?>
        </select>
    </div>
	        </div>
	        
	   <div class="col-lg-4 col-6">
            <div class="mb-2">
                <input type="date" id="date" class="form-control">
            </div>
	   </div>

	    </div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Reference</th>
                <th>Agent</th>
                <th>Recipient</th>
                <th>Mins</th>
                <th>Price</th>
                <th>Order Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="table-body">
                <!-- Data will be populated here -->
            </tbody>
        </table>
        <div id="pagination-controls" class="mt-2">
            <button class="btn btn-info" id="prev-page">Previous</button>
            <span id="pagination-links"></span>
            <button class="btn btn-info" id="next-page">Next</button>
        </div>
    </div>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>


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
		

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        const recordsPerPage = 10;

        function loadTableData() {
            const search = $('#search').val();
            const agent = $('#agent').val();
            const status = $('#status').val();
            const date = $('#date').val();

            $('.loading-overlay').show();

            $.ajax({
                url: 'call_offers_search.php',
                method: 'GET',
                data: {
                    search: search,
                    agent: agent,
                    status: status,
                    date: date,
                    page: currentPage,
                    recordsPerPage: recordsPerPage
                },
                dataType: 'json',
                success: function(response) {
                    $('#table-body').empty();
                    if (response.orders.length > 0) {
                        $.each(response.orders, function(index, order) {
                            $('#table-body').append(`
                                <tr>
                                    <th scope="row">${order.sn}</th>
                                     <td>${order.reference_id}</td>
                                    <td>${order.agent_name}</td>
                                    <td>${order.beneficiary}</td>
                                    <td>${order.mins}</td>
                                    <td>GHS ${order.price}</td>
                                    <td>${order.real_date}</td>
                                    <td>${order.order_time}</td>
                                    <td>${order.order_status}</td>
                                    <td>
                                       ${order.actions}
                                    </td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination);
                    } else {
                        $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
                        $('#pagination-links').empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#table-body').append('<tr><td colspan="11" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
                },
                complete: function() {
                    $('.loading-overlay').hide();
                }
            });
        }

        function generatePaginationLinks(pagination) {
            $('#pagination-links').empty();
            if (pagination) {
                $('#pagination-links').html(pagination);
                $('.pagination-link').on('click', function(e) {
                    e.preventDefault();
                    const pageNum = $(this).data('page');
                    currentPage = pageNum;
                    loadTableData();
                });
            }
        }

        $('#search, #agent, #status, #date').on('input change', function() {
            currentPage = 1;
            loadTableData();
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadTableData();
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            loadTableData();
        });

        loadTableData(); // Initial load
    });
</script>


<script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.updatebtn', function() {
        $('#modalUpdateForm').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
                $('#u_reference_id').val(data[0]);
                $('#u_agent_name').val(data[1]);
                $('#u_beneficiary').val(data[2]);
                $('#u_mins').val(data[3]);
                $('#u_price').val(data[4]);
                $('#u_order_date').val(data[5]);
                $('#u_order_status').val(data[6]);
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