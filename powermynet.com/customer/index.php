<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['mtn'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = filter_input(INPUT_POST, 'real_package', FILTER_SANITIZE_SPECIAL_CHARS);

   if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    else if (empty($gig)){
        $errors['gig'] = "Gig is not selected";
    }
     else if (empty($price)){
        $errors['price'] = "Price is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }

    else{
    
    date_default_timezone_set('GMT');
    $order_times = date('H: i: s: A');
    $order_dates = date('l, F d, Y');
    $real_dates = date('Y-m-d');

    $date4 = date('ymd');
    $time  = date('his');
    $to = bin2hex(random_bytes(3));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to.$date4.$tok.$time;
        
		$_SESSION['package'] = $package;
		$_SESSION['gig'] = $gig;
		$_SESSION['real_package'] = $real_package;
		$_SESSION['price'] = $price;
		$_SESSION['network'] = $network;
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
		$_SESSION['actual_price'] = $actual_price;
			  
        header("location:pay.php");
    
    }
    
	}
	
if(isset($_POST['at'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = filter_input(INPUT_POST, 'real_package', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    else if (empty($gig)){
        $errors['gig'] = "Gig is not selected";
    }
     else if (empty($price)){
        $errors['price'] = "Price is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }

    else{
    
    date_default_timezone_set('GMT');
    $order_times = date('H: i: s: A');
    $order_dates = date('l, F d, Y');
    $real_dates = date('Y-m-d');

    $date4 = date('ymd');
    $time  = date('his');
    $to = bin2hex(random_bytes(3));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to.$date4.$tok.$time;
        
		$_SESSION['package'] = $package;
		$_SESSION['gig'] = $gig;
		$_SESSION['real_package'] = $real_package;
		$_SESSION['price'] = $price;
		$_SESSION['network'] = "AT";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
			  
        header("location:pay2.php");
	}
	}
	
if(isset($_POST['telecel'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = filter_input(INPUT_POST, 'real_package', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    else if (empty($gig)){
        $errors['gig'] = "Gig is not selected";
    }
     else if (empty($price)){
        $errors['price'] = "Price is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }
    else{
    date_default_timezone_set('GMT');
    $order_times = date('H: i: s: A');
    $order_dates = date('l, F d, Y');
    $real_dates = date('Y-m-d');
    $date4 = date('ymd');
    $time  = date('his');
    $to = bin2hex(random_bytes(3));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to.$date4.$tok.$time;
		$_SESSION['package'] = $package;
		$_SESSION['gig'] = $gig;
		$_SESSION['real_package'] = $real_package;
		$_SESSION['price'] = $price;
		$_SESSION['network'] = "Telecel";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
        header("location:pay3.php");
	}
	}
	
//--Special offers
	
	if(isset($_POST['special'])){
$package = filter_input(INPUT_POST, 'package', FILTER_SANITIZE_SPECIAL_CHARS);
$gig = filter_input(INPUT_POST, 'gig', FILTER_SANITIZE_SPECIAL_CHARS);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$network = filter_input(INPUT_POST, 'network', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$beneficiary = filter_input(INPUT_POST, 'beneficiary', FILTER_SANITIZE_SPECIAL_CHARS);
$real_package = filter_input(INPUT_POST, 'real_package', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($package)) {
        $errors['package'] = "Package is not selected";
    }
    else if (empty($gig)){
        $errors['gig'] = "Gig is not selected";
    }
     else if (empty($price)){
        $errors['price'] = "Price is not selected";
    }
     else if (empty($email)){
        $errors['email'] = "Email is required";
    }
     else if (empty($beneficiary)){
        $errors['beneficiary'] = "Recipient number is required";
    }

    else{
    
    date_default_timezone_set('GMT');
    $order_times = date('H: i: s: A');
    $order_dates = date('l, F d, Y');
    $real_dates = date('Y-m-d');

    $date4 = date('ymd');
    $time  = date('his');
    $to = bin2hex(random_bytes(3));
    $tok = bin2hex(random_bytes(2));
    $order_id = $to.$date4.$tok.$time;
        
		$_SESSION['package'] = $package;
		$_SESSION['gig'] = $gig;
		$_SESSION['real_package'] = $real_package;
		$_SESSION['price'] = $price;
		$_SESSION['network'] = "AT";
		$_SESSION['email'] = $email;
		$_SESSION['beneficiary'] = $beneficiary;
			  
        header("location:pay_special.php");
	}
	}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>HOME | CodeCraftNetwork</title>
        <link href="../assets/img/code.png" rel="icon">
        <link href="../assets/img/code.png" rel="apple-touch-icon">
        <!-- CSS FILES -->        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/bootstrap-icons.css" rel="stylesheet">
        <link href="css/templatemo-topic-listing.css" rel="stylesheet">
        	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>   
    </head>
    
    <body class="topics-listing-page" id="top">
    <main>

     <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="../index.php" class="logo d-flex align-items-center"> <img src="../assets/img/code.png" style="width:60px;" alt=""> <span>CodeCraftNetwork</span> </a>
 
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-lg-5 me-lg-auto">
                            <li class="nav-item"> <a class="nav-link click-scroll" href="index.php">Home</a> </li>
                            <li class="nav-item"> <a class="nav-link click-scroll" href="auth/login"> Check Status <i class="fa fa-check-circle" aria-hidden="true"></i></a> </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <header class="site-header " style="height: 50px;">
                <div class="container " style="height: 10px;">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-12">
                            <h4 class="text-white mb-5" >Select Network</h4>
                        </div>
                    </div>
                </div>
            </header>
            
    <div class="row gutters p-2 mb-3">
        <div class="col-sm-12 col-12">
            <div class="form-group">
                <div>1. Complaints made after end of the day will not be accepted. All complaint should be made on 0208707376 (WhatsApp only)</div>
                <div>2. You should report when order is delivered but not received after 2 hours. </div>
                <div>3. If order is pending no need to complain. </div>
            </div>
        </div>
    </div>
            
            
 <!--modals-->
  <!--mtn offers-->
  <!-- Modal -->
<div class="modal fade" id="mtnPriceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">MTN Packages and Prices</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
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
            $select = "SELECT package,amount FROM prices_customers WHERE network='$network'";
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
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!--Mtn Buy-->
<!-- Modal -->
<div class="modal fade" id="mtnBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">MTN Package Purchase</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
       <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div id="price">
                    <input type="hidden" class="form-control" name="network" value="MTN" readonly>
                  </div>
                  <div>
                    <select required name="package" class="form-control" id="listmtn">
                        <option value="">Select a package (GIG)</option>
                 <?php
            include '../database/dbconfig.php';
            $network="MTN";
            $select = "SELECT amount,package FROM prices_customers WHERE network='$network' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                <option value="<?php echo $row['amount']; ?>"><?php echo $row['package']; ?></option>
                <?php
                }
              }
            ?> 
                    </select>
                  </div>
                  <div class="row" id="mtn-price-container" style="display: none;">
                      <div class="mb-3" id="price">
                         <input type="hidden" class="form-control" name="gig" id="gigmtn" readonly>
                       </div>
                        <div class="mb-3" id="price">
                            <label>Price (GHS)</label>
                         <input type="text" class="form-control" name="price" id="pricemtn" readonly>
                       </div>
                          <div class="mb-3">
                         <input type="hidden" class="form-control" name="real_package" id="rpackagemtn" readonly>
                       </div>
                   <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" required>
                  </div>
                    <div class="mb-3" >
                             <label>Data Bundle Recipient number</label>
                    <input type="tel" class="form-control" id="mtn_recipient" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit number" placeholder="Recipient number" name="beneficiary" oninput="mtnvalidatePhoneNumber(this)" required>
                    <span>Bundle made to wrongfully given number is non-refundable</span>
                  </div>
                    <div class="mb-3" >
                             <label>Confirm Recipient number</label>
                    <input type="tel" class="form-control" id="mtn_repeat" placeholder="Confirm Recipient number" name="repeat" required>
                    <span id="mtn_matchMessage"></span>
                  </div>
                  </div>
            </div>

            <div class="modal-footer" id="mtn_footer">
                 <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
             <button type="submit" name="mtn" class="btn btn-block btn-primary" id="mtnBtn">PROCEED</button> 
            </div>

      </form>
    </div>
  </div>
</div>

 <!--Telecel offers-->
  <!-- Modal -->
<div class="modal fade" id="telecelPriceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Telecel Packages and Prices</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
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
            $network = "TELECEL";
            $select = "SELECT amount,package FROM prices_customers WHERE network='$network'";
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
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!--Telecel Buy-->
<!-- Modal -->
<div class="modal fade" id="telecelBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Telecel Package Purchase</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
       <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div id="price">
                    <input type="hidden" class="form-control" name="network" value="TELECEL" readonly>
                  </div>
                  <div>
                    <select required name="package" class="form-control" id="listtelecel">
                        <option value="">Select a package (GIG)</option>
                     <?php
            include '../database/dbconfig.php';
            $network="TELECEL";
            $select = "SELECT amount,package FROM prices_customers WHERE network='$network' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                <option value="<?php echo $row['amount']; ?>"><?php echo $row['package']; ?></option>
                <?php
                }
              }
            ?> 
                    </select>
                  </div>
                  <div class="row" id="telecel-price-container" style="display: none;">
                      <div class="mb-3" id="price">
                         <input type="hidden" class="form-control" name="gig" id="gigtelecel" readonly>
                       </div>
                        <div class="mb-3" id="price">
                            <label>Price (GHS)</label>
                         <input type="text" class="form-control" name="price" id="pricetelecel" readonly>
                       </div>
                          <div class="mb-3" >
                         <input type="hidden" class="form-control" name="real_package" id="rpackagetelecel" readonly>
                       </div>
                   <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" required>
                  </div>
                       <div class="mb-3" >
                             <label>Data Bundle Recipient number</label>
                    <input type="tel" class="form-control" id="telecel_recipient" maxlength="10" placeholder="Recipient number" name="beneficiary" required>
                    <span>Bundle made to wrongfully given number is non-refundable</span>
                  </div>
                    <div class="mb-3" >
                             <label>Confirm Recipient number</label>
                    <input type="tel" class="form-control" id="telecel_repeat" placeholder="Confirm Recipient number" name="repeat" required>
                    <span id="telecel_matchMessage"></span>
                  </div>
                  </div>
            </div>
            <div class="modal-footer" id="telecel_footer">
                <div class="loading-overlay" id="spinner_at" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
             <button type="submit" name="telecel" class="btn btn-block btn-primary" id="telecelBtn">PROCEED</button> 
            </div>
      </form>
    </div>
  </div>
</div>

<!--AirtelTigo offers-->
  <!-- Modal -->
<div class="modal fade" id="atPriceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">AT Packages and Prices</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <div class="row">
           <div class="col-sm-12">
  <div class="table-responsive">
    <h5>Kindly note, data has 60 days expiry & can be rollover</h5>
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
            $select = "SELECT amount,package FROM prices_customers WHERE network='$network'";
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

           </div>
       </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!--AirtelTigo Buy-->
<!-- Modal -->
<div class="modal fade" id="atBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">AT Package Purchase</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
       <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div id="price">
                    <input type="hidden" class="form-control" name="network" value="AT" readonly>
                  </div>
                  <div class="">
                    <select required name="package" class="form-control" id="listat">
                        <option value="">Select a package (GIG)</option>
            <?php
            include '../database/dbconfig.php';
            $network="AT";
            $select = "SELECT amount,package FROM prices_customers WHERE network='$network' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                <option value="<?php echo $row['amount']; ?>"><?php echo $row['package']; ?></option>
                <?php
                }
              }
            ?> 
                    </select>
                  </div>
                  <div class="row" id="at-price-container" style="display: none;">
                      <div class="mb-3" id="price">
                         <input type="hidden" class="form-control" name="gig" id="gigat" readonly>
                       </div>
                        <div class="mb-3" id="price">
                            <label>Price (GHS)</label>
                         <input type="text" class="form-control" name="price" id="priceat" readonly>
                       </div>
                         <div class="mb-3">
                         <input type="hidden" class="form-control" name="real_package" id="rpackageat" readonly>
                       </div>
                   <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" required>
                  </div>
                 <div class="mb-3" >
                             <label>Data Bundle Recipient number</label>
                    <input type="tel" class="form-control" id="at_recipient" maxlength="10" placeholder="Recipient number" name="beneficiary" required>
                    <span>Bundle made to wrongfully given number is non-refundable</span>
                  </div>
                    <div class="mb-3" >
                             <label>Confirm Recipient number</label>
                    <input type="tel" class="form-control" id="at_repeat" placeholder="Confirm Recipient number" name="repeat" required>
                    <span id="at_matchMessage"></span>
                  </div>
                  </div>
            </div>
            <div class="modal-footer" id="at_footer">
                <div class="loading-overlay" id="spinner_at" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
             <button type="submit" name="at" class="btn btn-block btn-primary" id="atBtn">PROCEED</button> 
            </div>
      </form>
    </div>
  </div>
</div>

<!--SpecialBuy-->
  <!-- Modal -->
<div class="modal fade" id="specialPriceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
          
            <?php
            include '../database/dbconfig.php';
            $package = "AT BIG TIME";
            $target = "Customer";
            $select = "SELECT DISTINCT offer_name FROM special_offers WHERE offer_name='$package' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                        <h5 class="modal-title" id="exampleModalLabel">AT Non-Expiry Prices</h5>
                <?php
                }
              }
            ?>
          

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <div class="row">
           <div class="col-sm-12">
  <div class="table-responsive">
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">S/N</th>
                <th scope="col"></th>
                <th scope="col">Network</th>
                <th scope="col">Package</th>
                <th scope="col">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../database/dbconfig.php';
            $package = "AT BIG TIME";
            $target = "Customer";
            $select = "SELECT DISTINCT * FROM special_offers WHERE offer_name='$package' AND target='$target' ORDER BY package DESC ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            $sn = 1;  // Initialize counter variable

            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
                    <tr>
                        <th scope="row"><?php echo $sn++; ?></th> <!-- Use counter for S/N -->
                        <td><img src="../assets/img/Offer.jpg" style="width:25px;" class="img-fluid" ></td>
                        <td><?php echo $row['network']; ?></td>
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
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="specialBuyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
          
                      <?php
            include '../database/dbconfig.php';
            $package = "AT BIG TIME";
            $target = "Customer";
            $select = "SELECT DISTINCT offer_name FROM special_offers WHERE offer_name='$package' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                        <h5 class="modal-title" id="exampleModalLabel">AT Non-Expiry Offer</h5>
                <?php
                }
              }
            ?>
          
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
       <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div id="price">
                  </div>
                  <div class="">
                    <select required name="package" class="form-control" id="listspecial">
                        <option value="">Select a package (GIG)</option>
            <?php
            include '../database/dbconfig.php';
            $package = "AT BIG TIME";
            $target = "Customer";
            $select = "SELECT DISTINCT * FROM special_offers WHERE offer_name='$package' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
                ?>
                <option value="<?php echo $row['amount']; ?>"><?php echo $row['package']; ?></option>
                <?php
                }
              }
            ?> 
                    </select>
                  </div>
                  <div class="row" id="special-price-container" style="display: none;">
                      <div class="mb-3" id="price">
                         <input type="hidden" class="form-control" name="gig" id="gigspecial" readonly>
                       </div>
                        <div class="mb-3" id="price">
                            <label>Price (GHS)</label>
                         <input type="text" class="form-control" name="price" id="pricespecial" readonly>
                       </div>
                         <div class="mb-3">
                         <input type="hidden" class="form-control" name="real_package" id="rpackagespecial" readonly>
                       </div>
                   <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" required>
                  </div>
                 <div class="mb-3" >
                             <label>Data Bundle Recipient number</label>
                    <input type="tel" class="form-control" id="special_recipient" maxlength="10" placeholder="Recipient number" name="beneficiary" required>
                    <span>Bundle made to wrongfully given number is non-refundable</span>
                  </div>
                    <div class="mb-3" >
                             <label>Confirm Recipient number</label>
                    <input type="tel" class="form-control" id="special_repeat" placeholder="Confirm Recipient number" name="repeat" required>
                    <span id="special_matchMessage"></span>
                  </div>
                  </div>
            </div>
            <div class="modal-footer" id="special_footer">
                <div class="loading-overlay" id="spinner_special" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
             <button type="submit" name="special" class="btn btn-block btn-primary" id="specialBtn">PROCEED</button> 
            </div>
      </form>
    </div>
  </div>
</div>

<script>
    $(document).ready(function() {
        $('#listspecial').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_special.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON respons
                        if (data.success) {
                             $('#networkspecial').val(data.network); // Set the package name
                            $('#gigspecial').val(data.package_name); // Set the package name
                            $('#pricespecial').val(selectedValue); // Set the selected price (amount)
                            $('#packagespecial').val(data.real_package); // Set the real package from the database
                            $('#special-price-container').show();
                        } else {
                            $('#special-price-container').hide();
                        }
                    }
                });
            } else {
                $('#special-price-container').hide();
            }
        });
    });
</script>

  
  <!--modals end-->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <!--mtn spinner-->
    <script>
        $(document).ready(function() {
    $('#mtnBtn').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
         <!--telecel spinner-->
    <script>
        $(document).ready(function() {
    $('#telecelBtn').on('click', function() {
        // Show the spinner
        $('#spinner_telecel').show();
    });
    });
    </script>
         <!--at spinner-->
    <script>
        $(document).ready(function() {
    $('#atBtn').on('click', function() {
        // Show the spinner
        $('#spinner_at').show();
    });
    });
    </script>
    
        <script>
        $(document).ready(function() {
    $('#specialBtn').on('click', function() {
        // Show the spinner
        $('#spinner_special').show();
    });
    });
    </script>
    
    
    <script>
      function mtnvalidatePhoneNumber(input) {
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Check if the input is less than 10 digits
    if (input.value.length < 10) {
        input.setCustomValidity("Please enter a valid 10-digit number.");
    } else {
        input.setCustomValidity(""); // Reset the error message
    }
    }
    </script>    
    
<script>
    document.getElementById('mtn_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('mtn_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('mtn_matchMessage');
        const proceedButton = document.getElementById('mtn_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
            proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('at_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('at_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('at_matchMessage');
        const proceedButton = document.getElementById('at_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('telecel_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('telecel_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('telecel_matchMessage');
        const proceedButton = document.getElementById('telecel_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>

<script>
    document.getElementById('special_repeat').addEventListener('input', function() {
        const recipient = document.getElementById('special_recipient').value;
        const repeat = this.value;
        const messageSpan = document.getElementById('special_matchMessage');
        const proceedButton = document.getElementById('special_footer');

        if (repeat === recipient) {
            messageSpan.textContent = "Numbers match.";
            messageSpan.style.color = "green";
            proceedButton.style.display = "block";
        } else {
            messageSpan.textContent = "Numbers do not match.";
            messageSpan.style.color = "red";
              proceedButton.style.display = "none";
        }
    });
</script>
  
            
            <style>
                .sit-header {
  background-image: linear-gradient(15deg, #13547a 0%, #80d0c7 100%);
  padding-top: 150px;
  padding-bottom: 80px;
}

.site-header .container {
  height: 60px;
}

.breadcrumb-item+.breadcrumb-item::before,
.breadcrumb-item a:hover,
.breadcrumb-item.active {
  color: var(--white-color);
}

.site-header .custom-icon {
  color: var(--white-color);
  font-size: var(--h4-font-size);
}

.site-header .custom-icon:hover {
  color: var(--secondary-color);
}
            </style>

    <section class="section-padding section-bg">
        <div class="container">
            <div  class="row" style="margin-top:-70px;">
                <div class="col-lg-3 col-md-6">
            
<div class="card text-center mb-3">
  <div class="card-header text-white bg-warning">MTN</div>
  <div class="card-body">
   <img src="../assets/img/MTN.png" style="width:400px; height:250px;" class="img-fluid" alt="MTN Logo">
   <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#mtnPriceModal">Price List</button>
  </div>
  <div class="card-footer text-muted">
    <?php
    include '../database/dbconfig.php';
    
    // Check overall activation status
    $activationStatus = mysqli_query($con, "SELECT status FROM activation");
    if (mysqli_num_rows($activationStatus) > 0) {
        $row = mysqli_fetch_array($activationStatus);
        
        // If main status is deactivated, show "Out of Stock"
        if ($row['status'] == "DEACTIVATED") {
            echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
        } else {
            
                // Check customer_mtn_activation status
                $mtnActivationStatus = mysqli_query($con, "SELECT status FROM customer_mtn_activation");
                $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
                
                if ($mtnActivationRow['status'] == "DEACTIVATED") {
                    echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
                } else {
                    echo '<div class="col-lg-12 col-12" id="activation">';
                    echo '<button type="button" class="btn btn-warning btn-block w-100" data-toggle="modal" data-target="#mtnBuyModal">Buy Now</button>';
                    echo '</div>';
                }
            
        }
    } else {
        echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
    }
    ?>
  </div>
</div>

            
        </div>
        
          <div class="col-lg-3 col-md-6">
            
<div class="card text-center mb-3">
  <div class="card-header text-white bg-danger">Telecel</div>
  <div class="card-body">
    <img src="../assets/img/Telecel.jpg" style="width:400px; height:250px;" class="img-fluid" alt="MTN Logo">
    <br>
  <button type="button" class="btn btn-danger btn-block mt-1" data-toggle="modal" data-target="#telecelPriceModal">Price List</button>
  </div>
  <div class="card-footer text-muted">
    <?php
    include '../database/dbconfig.php';
    
    // Check overall activation status
    $activationStatus = mysqli_query($con, "SELECT status FROM activation");
    if (mysqli_num_rows($activationStatus) > 0) {
        $row = mysqli_fetch_array($activationStatus);
        
        // If main status is deactivated, show "Out of Stock"
        if ($row['status'] == "DEACTIVATED") {
            echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
        } else {
            
                // Check customer_mtn_activation status
                $mtnActivationStatus = mysqli_query($con, "SELECT status FROM customer_telecel_activation");
                $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
                
                if ($mtnActivationRow['status'] == "DEACTIVATED") {
                    echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
                } else {
                    echo '<div class="col-lg-12 col-12" id="activation">';
                    echo '<button type="button" class="btn btn-danger btn-block w-100" data-toggle="modal" data-target="#telecelBuyModal">Buy Now</button>';
                    echo '</div>';
                }
            
        }
    } else {
        echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
    }
    ?>
  </div>
</div>
            
        </div>
        
          <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="700">
            
<div class="card text-center">
  <div class="card-header text-white bg-primary">AT</div>
  <div class="card-body">
   <img src="../assets/img/AT.jpg" style="width:400px; height:250px;" class="img-fluid" alt="MTN Logo">
   <br>
   <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#atPriceModal">Price List</button>
  </div>
  <div class="card-footer text-muted">
    <?php
    include '../database/dbconfig.php';
    
    // Check overall activation status
    $activationStatus = mysqli_query($con, "SELECT status FROM activation");
    if (mysqli_num_rows($activationStatus) > 0) {
        $row = mysqli_fetch_array($activationStatus);
        
        // If main status is deactivated, show "Out of Stock"
        if ($row['status'] == "DEACTIVATED") {
            echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
        } else {

                // Check customer_mtn_activation status
                $mtnActivationStatus = mysqli_query($con, "SELECT status FROM customer_at_activation");
                $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
                
                if ($mtnActivationRow['status'] == "DEACTIVATED") {
                    echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
                } else {
                    echo '<div class="col-lg-12 col-12" id="activation">';
                    echo '<button type="button" class="btn btn-primary btn-block w-100" data-toggle="modal" data-target="#atBuyModal">Buy Now</button>';
                    echo '</div>';
                }
            
        }
    } else {
        echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
    }
    ?>
  </div>
</div>
            
        </div>
        
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="700">
            
            
              <?php
            include '../database/dbconfig.php';
            $package = "AT BIG TIME";
            $target = "Customer";
            $select = "SELECT DISTINCT offer_name FROM special_offers WHERE offer_name='$package' AND target='$target' ";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
  <div class="card text-center">
  <div class="card-header text-white bg-info">AT Non-Expiry Offers</div>
  <div class="card-body">
   <img src="../assets/img/AT.jpg" style="width:400px; height:250px;" class="img-fluid" >
   <br>
   <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#specialPriceModal">Price List</button>
  </div>
  <div class="card-footer text-muted">
    <?php
    include '../database/dbconfig.php';
    
    // Check overall activation status
    $activationStatus = mysqli_query($con, "SELECT status FROM activation");
    if (mysqli_num_rows($activationStatus) > 0) {
        $row = mysqli_fetch_array($activationStatus);
        
        // If main status is deactivated, show "Out of Stock"
        if ($row['status'] == "DEACTIVATED") {
            echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
        } else {
  
                // Check customer_mtn_activation status
                $mtnActivationStatus = mysqli_query($con, "SELECT status FROM special_all_activation");
                $mtnActivationRow = mysqli_fetch_array($mtnActivationStatus);
                
                if ($mtnActivationRow['status'] == "DEACTIVATED") {
                    echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
                } else {
                    echo '<div class="col-lg-12 col-12" id="activation">';
                    echo '<button type="button" class="btn btn-info btn-block w-100" data-toggle="modal" data-target="#specialBuyModal">Buy Now</button>';
                    echo '</div>';
                }

        }
    } else {
        echo '<div class="col-lg-12 col-12">OUT OF STOCK</div>';
    }
    ?>
  </div>
</div>
            <?php
                }
            }
            else{

            }
            ?>
            
            

            
        </div>
 <br>
 <hr>
 <br>
 
   <style>
    .whatsapp-icon {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        cursor: pointer;
    }
    </style>

        <a href="https://wa.me/233208707376" target="_blank" class="whatsapp-icon">
         <img src="../assets/img/WhatsApp.png" alt="WhatsApp" style="width: 50px; height: 50px;">
          </a>

                    <div class="row mt-4">
                        <div class="col-lg-12 col-12">
                            
                            <div id="accordion">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          HOW IT WORKS
        </button>
      </h5>
    </div>
    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
          <p>1. Browse Packages: Visit our website and explore the available data packages from MTN, Telecel, and AirtelTigo Ghana.</p>
          <p>2. Select Your Plan: Choose the data package that best suits your needs and budget.</p>
          <p>3. Make a Payment: Securely pay using your preferred payment method.</p>
   
</div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          IMPORTANT NOTICE
        </button>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
      <div class="card-body">
                 <p>   Our MTN Data Package Does not support the following</p>
          <p>1. Other Network.</p>
          <p>2. Merchant SIM.</p>
          <p>3. EVD SIM.</p>
          <p>4. Turbonet SIMS.</p>
    <p>4. Broadband SIMS.</p>
<p>5. Blacklisted SIM.</p>
<p>* Sometimes a customer will not receive the SMS alert but the data is sent.</p>
<p>* Always check total data balance.</p>

      </div>
    </div>
  </div>
</div>

     <div class="row mt-5">
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/MTN.png" class="img-fluid" alt="MTN Logo">
            </div>
          </div>
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/Telecel.jpg" class="img-fluid" alt="TELECEL Logo">
            </div>
          </div>
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/AT.jpg" class="img-fluid" alt="MTN Logo">
            </div>
          </div>
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/mastercard.jpg" class="img-fluid" alt="MTN Logo">
            </div>
          </div>
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/Visa.jpg" class="img-fluid" alt="MTN Logo">
            </div>
          </div>
          <div class="col-lg-2 col-sm-4 col-3">
               <div class="card z-index-0 px-3 py-2">
               <img src="../assets/img/paystack.png" class="img-fluid" alt="MTN Logo">
            </div>
          </div>
      </div>
                            
</div>

                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer section-padding">
            <div class="container">
                <div class="row">

                    <div class="col-lg-3 col-md-4 col-6 mb-4 mb-lg-0">
                        <h6 class="site-footer-title mb-3">Information</h6>
                        <p class="text-white d-flex mb-1">
                            <a href="tel: +233208707376" class="site-footer-link">
                                +233208707376
                            </a>
                        </p>
                        <p class="text-white d-flex">
                            <a href="mailto:codecraftnetwork1@gmail.com" class="site-footer-link">
                                codecraftnetwork1@gmail.com
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </footer>
        
          <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
          
          <script>
    $(document).ready(function() {
        $('#listmtn').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_mtn.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON response
                        
                        if (data.success) {
                            $('#gigmtn').val(data.package_name); // Set the package name
                            $('#pricemtn').val(selectedValue); // Set the selected price (amount)
                            $('#rpackagemtn').val(data.real_package); // Set the real package from the database
                            $('#mtn-price-container').show();
                        } else {
                            $('#mtn-price-container').hide();
                        }
                    }
                });
            } else {
                $('#mtn-price-container').hide();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#listat').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_at.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON response
                        
                        if (data.success) {
                            $('#gigat').val(data.package_name); // Set the package name
                            $('#priceat').val(selectedValue); // Set the selected price (amount)
                            $('#rpackageat').val(data.real_package); // Set the real package from the database
                            $('#at-price-container').show();
                        } else {
                            $('#at-price-container').hide();
                        }
                    }
                });
            } else {
                $('#at-price-container').hide();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#listtelecel').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_telecel.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON respons
                        if (data.success) {
                            $('#gigtelecel').val(data.package_name); // Set the package name
                            $('#pricetelecel').val(selectedValue); // Set the selected price (amount)
                            $('#rpackagetelecel').val(data.real_package); // Set the real package from the database
                            $('#telecel-price-container').show();
                        } else {
                            $('#telecel-price-container').hide();
                        }
                    }
                });
            } else {
                $('#telecel-price-container').hide();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#listspecial').change(function() {
            var selectedValue = $(this).val(); // Get the selected value (amount)
            
            if (selectedValue) {
                // AJAX request to fetch the real package from the database
                $.ajax({
                    url: 'get_special.php', // PHP script to handle the request
                    type: 'POST',
                    data: { amount: selectedValue },
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the JSON respons
                        if (data.success) {
                            $('#gigspecial').val(data.package_name); // Set the package name
                            $('#pricespecial').val(selectedValue); // Set the selected price (amount)
                            $('#rpackagespecial').val(data.real_package); // Set the real package from the database
                            $('#special-price-container').show();
                        } else {
                            $('#special-price-container').hide();
                        }
                    }
                });
            } else {
                $('#special-price-container').hide();
            }
        });
    });
</script>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <!-- JAVASCRIPT FILES -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/jquery.sticky.js"></script>
        <script src="js/custom.js"></script>

    </body>
</html>