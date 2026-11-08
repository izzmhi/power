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

<!-- Profit Withdrawal Modal paystack-->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content border-0 shadow-lg">
      <!-- Header -->
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="exampleModalLabel">
          <i class="fa fa-money"></i> Profit Withdrawal
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="process-momo.php" method="POST" enctype="multipart/form-data" id="withdrawForm">
        <div class="modal-body">
          <?php
          include '../database/dbconfig.php';
          $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
          $select = "SELECT balance, momo_name, momo_number FROM shop WHERE user_id='$user_id' AND status='APPROVED' AND activeness='ACTIVE'";
          $sql = mysqli_query($con, $select);
          $check = mysqli_num_rows($sql) > 0;
          if ($check) {
              while ($row = mysqli_fetch_array($sql)) {
                  $balance = $row['balance'];
                  $momo_number = $row['momo_number'];
                  $momo_name = $row['momo_name'];
                  if ($balance <= 1) {
                      echo '<div class="alert alert-warning text-center mb-0">Balance is too low. You cannot withdraw.</div>';
                  } 
                  elseif ($row['momo_number']=="" AND $row['momo_name']=="") {
                      echo '<div class="alert alert-warning text-center mb-0">MoMo Account not set.</div>';
                  } 
                  else {
                ?>
            <!-- Display Current Balance -->
            <div class="mb-3">
              <label class="form-label fw-bold">Current Balance</label>
              <input type="text" class="form-control" value="<?php echo number_format($balance, 2); ?>" readonly>
            </div>
            <!-- Withdrawal Amount --> 
            <div class="mb-3">
              <label class="form-label fw-bold"><i class="fa fa-money"></i> Amount to Withdraw</label>
              <input type="number" class="form-control" name="amount" placeholder="Enter amount" min="1" max="<?php echo $balance; ?>" required>
            </div>
          <?php
                  }
              }
          } else {
              echo '<div class="alert alert-danger text-center mb-0">No active account found.</div>';
          }
          ?>
        </div>

        <!-- Footer -->
<!-- Footer -->
<div class="modal-footer">
    <!-- Spinner -->
    <div class="loading-overlay" id="spinner_withdraw" style="display:none;">
        <div class="spinner"></div>
        <div class="loading-text">Please Wait...</div>
    </div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <?php 
    if (!empty($balance)) {  
        // ✅ Show Withdraw button only if new_balance > 1
        if ($balance > 1) {  
            echo '<button type="submit" name="submit_withdraw" class="btn btn-success" id="btnWithdraw">Credit Wallet</button>';
        } 
        // ✅ Optionally show a message if new_balance is too low
        else {
            echo '<div class="alert alert-warning text-center mb-0 w-100">Balance is too low. You cannot withdraw.</div>';
        }
    }
    ?>
</div>
      </form>
    </div>
  </div>
</div>

<!-- JS to prevent entering more than balance -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const amountInput = document.querySelector("input[name='amount']");
    const balance = parseFloat(amountInput?.max || 0);
    if (amountInput) {
        amountInput.addEventListener("input", function () {
            if (parseFloat(this.value) > balance) {
                this.value = balance;
            }
        });
    }
});
</script>


		<!-- Main container start -->
<div class="main-container">
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
        }
        elseif ($row['status'] == "PENDING"){
        }
        elseif ($row['status'] == "APPROVED"){
        ?>
<div class="card bg-success text-white d-flex flex-row justify-content-between align-items-center flex-nowrap rounded-top p-2">
    <!-- LEFT SIDE -->
    <div class="d-flex align-items-center flex-nowrap">
        <i class="fa fa-cart-arrow-down me-2 fs-5"></i> 
        <span class="fw-semibold fs-6">
            <?php
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM shop WHERE user_id='$user_id' AND status ='APPROVED' AND activeness='ACTIVE'";
            $sql = mysqli_query($con, $select);
            if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_array($sql)) {
                    echo " ". htmlspecialchars($row['shop_full']) . " (" . htmlspecialchars($row['shop']) . ")";
                }
            }
            ?>
        </span>
    </div>
    <!-- RIGHT SIDE -->
    <div class="badge bg-white text-success d-flex align-items-center px-3 py-1 rounded-pill shadow-sm flex-nowrap">
        <i class="fa fa-check-circle me-1"></i>
        <span class="fw-semibold">
            <?php
            $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
            $select = "SELECT * FROM shop WHERE user_id='$user_id' AND status ='APPROVED' AND activeness='ACTIVE'";
            $sql = mysqli_query($con, $select);
            if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_array($sql)) {
                    echo htmlspecialchars($row['activeness']);
                }
            }
            ?>
        </span>
    </div>
</div>
<div class="card shadow-sm border-0 p-3 text-center">
    <?php
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $select = "SELECT url FROM shop WHERE user_id='$user_id' AND status ='APPROVED' AND activeness='ACTIVE'";
    $sql = mysqli_query($con, $select);
    if (mysqli_num_rows($sql) > 0) {
        while ($row = mysqli_fetch_array($sql)) {
            $url = htmlspecialchars($row['url']);
            ?>
            <!-- URL Display -->
            <h5 class="text-primary mb-3">
                <a href="<?php echo $url; ?>" target="_blank" class="text-decoration-none">
                    <?php echo $url; ?>
                </a>
            </h5>
            <!-- Action Buttons -->
            <div class="d-flex justify-content-center gap-3">
                <!-- Copy Button -->
                <button class="btn btn-outline-info px-4" onclick="copyToClipboard('<?php echo $url; ?>')">
                    <i class="fa fa-clipboard"></i> Copy
                </button>
                <!-- Visit Button -->
                <a href="<?php echo $url; ?>" target="_blank" class="btn btn-info text-white px-3">
                    <i class="fa fa-link"></i> Visit
                </a>
            </div>
            <?php
        }
    }
    ?>

</div>

<!-- Copy To Clipboard Script -->
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert("Copied: " + text);
    });
}
</script>

<style>
  .custom-card {
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
</style>

    
<!-- No price or low price alert -->
<?php
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
// Fetch all active approved packages for the agent
$query = "SELECT agent_price, amount FROM agents_prices_packages 
          WHERE agent_id='$user_id'";
$result = mysqli_query($con, $query);
// If no price set at all
if (mysqli_num_rows($result) === 0) {
?>
  <div class="alert alert-danger d-flex align-items-center shadow-sm mt-3 p-3 rounded-3" role="alert" style="border-left: 5px solid #dc3545;">
    <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
    <div>
      <strong>Notice:</strong> You have not set your prices.  
      Please go to <a href="shop_pricing.php" class="alert-link text-white text-decoration-underline">Pricing</a> to set your prices.
    </div>
  </div>
<?php
} else {
    $low_price_found = false;
    // Check for low or incorrect prices
    while ($row = mysqli_fetch_assoc($result)) {
        $agent_price = floatval($row['agent_price']);
        $amount = floatval($row['amount']);
        if ($amount <= $agent_price) { // if selling price is not higher
            $low_price_found = true;
            break;
        }
    }
if ($low_price_found) {
?>
  <div class="alert alert-danger d-flex align-items-center shadow-sm mt-3 p-3 rounded-3" role="alert" style="border-left: 5px solid #dc3545;">
    <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
    <div>
      <strong>Warning:</strong> One or more of your package prices are lower than the actual cost price.<br>
      You may not make a profit or could run at a loss.  
      Please go to <a href="shop_pricing.php" class="alert-link text-white text-decoration-underline">Pricing</a> to correct your prices.
    </div>
  </div>
<?php
    }
}
?>

<?php
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
// Fetch all active approved packages for the agent
$query = "SELECT agent_price, amount FROM agents_prices_special 
          WHERE agent_id='$user_id'";
$result = mysqli_query($con, $query);
// If no price set at all
if (mysqli_num_rows($result) === 0) {
?>
  <div class="alert alert-danger d-flex align-items-center shadow-sm mt-3 p-3 rounded-3" role="alert" style="border-left: 5px solid #dc3545;">
    <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
    <div>
      <strong>Notice:</strong> You have not set your BIG TIME prices.  
      Please go to <a href="shop_pricing.php" class="alert-link text-white text-decoration-underline">Pricing</a> to set your prices.
    </div>
  </div>
<?php
} else {
    $low_price_found = false;
    // Check for low or incorrect prices
    while ($row = mysqli_fetch_assoc($result)) {
        $agent_price = floatval($row['agent_price']);
        $amount = floatval($row['amount']);
        if ($amount <= $agent_price) { // if selling price is not higher
            $low_price_found = true;
            break;
        }
    }

if ($low_price_found) {
?>
  <div class="alert alert-danger d-flex align-items-center shadow-sm mt-3 p-3 rounded-3" role="alert" style="border-left: 5px solid #dc3545;">
    <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
    <div>
      <strong>Warning:</strong> One or more of your BIG TIME package prices are lower than the actual cost price.
      You may not make a profit or could run at a loss.  
      Please go to <a href="shop_pricing.php" class="alert-link text-white text-decoration-underline">Pricing</a> to correct your prices.
    </div>
  </div>
<?php
    }
}
?>

    
<?php
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$select = "select momo_name,momo_number from shop where user_id='$user_id' AND status ='APPROVED' AND activeness='ACTIVE' ";
$sql=mysqli_query($con,$select);
$check=mysqli_num_rows($sql)>0;
if($check)
{ 
    while($row=mysqli_fetch_array($sql))
    {
     if($row['momo_number']=="" AND $row['momo_name']==""){
         ?>
<div class="row align-items-center bg-danger text-white p-1">
  <!-- Dashboard Title -->
  <div class="col-7">
    <h6 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
      </span>
      Go to Settings to set up your MoMo Account to receive payments
    </h6>
  </div>
</div>
         <?php
        }
     
    }
}           
?>    

    
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
        <a class="dropdown-item text-white fs-3" href="shop_orders.php">Shop Orders</a>
        <a class="dropdown-item text-white fs-3" href="shop_payouts.php">Payouts History</a>
        <a class="dropdown-item text-white fs-3" href="shop_pricing.php">Pricing</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-white fs-3" href="shop_settings.php">Settings</a>
      </div>
    </div>
  </h4>
</div>

                                                    <?php
                                                    }
                                                ?>
                                                <?php
                                            }
                                                    
                                        }
                                        ?>
    



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

<div class="row">
<div class="col-sm-12 col-lg-6 col-12">
    <div class="card bg-success custom-card">
        <div class="card-header">
            <h5 class="card-title text-white">Balance</h5>
        </div>
        <div class="card-body d-flex justify-content-between align-items-end">
            <!-- LEFT SIDE: ICON + BALANCE -->
            <div class="d-flex align-items-center">
                <i class="fa fa-money text-white" aria-hidden="true" style="font-size: 32px; margin-right: 15px;"></i>
                <div>
                    <?php
                    include '../database/dbconfig.php';
                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                    $select = "SELECT * FROM shop WHERE user_id='$user_id' AND status='APPROVED' AND activeness='ACTIVE'";
                    $sql = mysqli_query($con, $select);
                    if (mysqli_num_rows($sql) > 0) {
                        while ($row = mysqli_fetch_array($sql)) {
                            $balance = $row['balance'];
                        }
                    }
                    ?>
                    <h4 class="card-text text-white mb-0">&#x20B5     <?php echo number_format((float)$balance, 2, '.', ','); ?></h4>
                </div>
            </div>
            <!-- RIGHT SIDE: WITHDRAW BUTTON -->
            <div>
                <?php if ($balance <= 1) { ?>
                    <button type="button" class="btn btn-light border-primary">
                        Withdraw
                    </button>
                <?php } else { ?>
                    <button type="button" class="btn btn-light border-primary" data-toggle="modal" data-target="#exampleModal">
                        Credit Wallet
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-6 col-12">
    <div class="card bg-success custom-card">
        <div class="card-header">
            <h5 class="card-title text-white">Recent Payout</h5>
        </div>

        <div class="card-body d-flex align-items-center justify-content-between">

            <!-- LEFT SIDE ICON -->
            <i class="fa fa-money text-white" aria-hidden="true" 
               style="font-size: 32px; margin-right: 15px;"></i>

            <!-- RIGHT SIDE AMOUNT -->
            <div class="text-end">
                <?php
                    include '../database/dbconfig.php';
                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                    $select = "SELECT * FROM shop WHERE user_id='$user_id' AND status='APPROVED' AND activeness='ACTIVE'";
                    $sql = mysqli_query($con, $select);

                    $payout = 0;
                    if (mysqli_num_rows($sql) > 0) {
                        while ($row = mysqli_fetch_array($sql)) {
                            $payout = $row['payout'];
                        }
                    }
                ?>
                <h4 class="card-text text-white mb-0">
                    &#x20B5 <?php echo $payout; ?>
                </h4>
            </div>

        </div>
    </div>
</div>

<div class="col-sm-6 col-12">
    <div class="card bg-success custom-card">
        <div class="card-header">
            <h5 class="card-title text-white">Total Payouts</h5>
        </div>
        <div class="card-body d-flex align-items-center justify-content-between">
            <!-- LEFT SIDE (ICON) -->
            <i class="fa fa-money text-white" aria-hidden="true" 
               style="font-size: 32px; margin-right: 15px;"></i>
            <!-- RIGHT SIDE (AMOUNT) -->
            <div class="text-right">
                <?php
                    include '../database/dbconfig.php';
                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                    $select = "SELECT * FROM shop WHERE user_id='$user_id' AND status='APPROVED' AND activeness='ACTIVE'";
                    $sql = mysqli_query($con, $select);

                    $total_payout = 0;
                    if (mysqli_num_rows($sql) > 0) {
                        while ($row = mysqli_fetch_array($sql)) {
                            $total_payout = $row['total_payouts'];
                        }
                    }
                ?>
                <h4 class="card-text text-white mb-0">
                    &#x20B5 <?php echo $total_payout; ?>
                </h4>
            </div>
        </div>
    </div>
</div>

 <div class="col-sm-6 col-12">
    <div class="card bg-success custom-card">
        <div class="card-header">
            <h5 class="card-title text-white">Total Profits</h5>
        </div>
        <!-- MAIN FLEX ROW -->
        <div class="card-body d-flex justify-content-between align-items-center">
            <!-- LEFT ICON -->
            <i class="fa fa-money text-white" aria-hidden="true" 
               style="font-size: 32px; margin-right: 15px;"></i>
            <!-- RIGHT AMOUNT -->
            <div class="text-end">
                <?php
                include '../database/dbconfig.php';
                $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                $select = "SELECT profit FROM shop 
                           WHERE user_id='$user_id' 
                           AND status='APPROVED' 
                           AND activeness='ACTIVE'";
                $sql = mysqli_query($con, $select);
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_array($sql)) {
                        $total_profit = $row['profit'];
                    }
                }
                ?>
                <h4 class="card-text text-white mb-0">
                    &#x20B5 <?php echo $total_profit; ?>
                </h4>
            </div>
        </div>
    </div>
</div>

</div>

                                                    
                                                    <?php
                                                    }
                                                ?>
                                                <?php
                                            }
                                                    
                                        }
                                        else{
                                            ?>
                                            <!-- Request a Free SHOP Section -->
<div class="container my-5 p-4 shadow rounded bg-light">
    <h5 class="text-primary fw-bold mb-2">Request a Free SHOP</h5>
    <h6 class="text-muted mb-4">Your Shop will be reviewed and approved within 24hours.</h6>

    <form method="POST" action="process_shop.php" enctype="">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="shop_full" class="form-label">SHOP FULLNAME</label>
                    <input type="text" class="form-control" id="shop_full" name="shop_full" placeholder="Up to 15 letters" 
                    maxlength="15" title="1 to 15 characters" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="shop" class="form-label">SHOP SHORT NAME</label>
                    <input type="text" class="form-control" id="shop" name="shop" 
                       placeholder="Up to 10 letters, no spaces or numbers" pattern="^[A-Za-z]{1,10}$" maxlength="10" 
                       title="1 to 10 letters only, no spaces or numbers" required>
                </div>
            </div>
        </div>

        <!-- Spinner Overlay -->
        <div class="spinner-overlay d-none" id="spinner" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 1050; display: flex; align-items: center; justify-content: center;">
            <div class="spinner text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 fw-bold text-primary">Please Wait...</p>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" name="shop_request" class="btn btn-success" id="spin">Request</button>
        </div>
    </form>
</div>
                                            <?php
                                        }
                                    ?>
    

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
<script>
$(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;

    function loadTableData(search = '', page = 1, date = '') {
        $('.loading-overlay').show();
        $.ajax({
            url: 'shop_payout_search.php',
            method: 'GET',
            data: {
                date: date,
                page: page,
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
                                <td>GHS${order.amount}</td>
                                <td>GHS${order.balance}</td>
                                <td>GHS${order.total_payouts}</td>
                                <td>${order.payout_date}</td>
                            </tr>
                        `);
                    });
                    generatePaginationLinks(response.pagination, page);
                } else {
                    $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">No Payout is Available</td></tr>');
                    $('#pagination-links').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
            },
            complete: function() {
                $('.loading-overlay').hide();
            }
        });
    }

    function generatePaginationLinks(pagination, currentPage) {
        $('#pagination-links').empty();
        if (pagination !== '') {
            $('#pagination-links').html(pagination);
            $('.pagination-link').on('click', function(e) {
                e.preventDefault();
                const pageNum = $(this).data('page');
                const dateValue = $('#date').val();
                currentPage = pageNum;
                loadTableData('', pageNum, dateValue);
            });
        }
    }

    $('#date').on('change', function() {
        currentPage = 1;
        loadTableData('', currentPage, $(this).val());
    });

    // Initial table load
    loadTableData();
});
</script>

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