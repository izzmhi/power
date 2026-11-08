<?php
require_once 'configs/config.php'; // contains PAYSTACK_PUBLIC_KEY
// Base amount you want to receive
$customer  = "Addai";
$telephone = "0554226398";
$email     = $telephone . "@gmail.com";
$qty = "1";
$base_amount = 1; // you want to receive ₵1
// === Calculate Paystack Fee ===
$fee_percent = 0.0195; // 1.95%
$extra_fee   = 0.5;    // flat ₵0.50
$final_amount = ($base_amount + $extra_fee) / (1 - $fee_percent);
// Cap fee at ₵20
if (($final_amount - $base_amount) > 20) {
    $final_amount = $base_amount + 20;
}
// Round to 2 decimals for display
$final_amount = round($final_amount, 2);
// Debug: display computed info (optional)
// echo "Customer: $customer, Phone: $telephone, Email: $email, Total to charge: ₵$final_amount";
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="asset/images/Power.png" />
    <title>Powermynet</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/global.css" rel="stylesheet">
	<link href="css/index.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500&display=swap" rel="stylesheet">
	<script src="js/jquery-2.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
      <script src="https://js.paystack.co/v1/inline.js"></script>
  </head>
<body>
<section id="header" class="clearfix cd-secondary-nav">
	<nav class="navbar nav_t">
		<div class="container">
		    <div class="navbar-header page-scroll">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand col_3" href="index.html"><img src="asset/images/Power.png" style="width:35px; height:35px;"> POWERMYNET</a>
			</div>
			<!-- Brand and toggle get grouped for better mobile display -->
			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			  	 <ul class="nav navbar-nav navbar-right">
				<li><a class="m_tag active_tab" href="index.html">Home</a></li>
				<li><a class="m_tag" href="#">About</a></li>
				<li class="dropdown">
					  <a class="m_tag" href="#" data-toggle="dropdown" role="button" aria-expanded="false">Shop<span class="caret"></span></a>
					  <ul class="dropdown-menu drop_3" role="menu">
					    <li><a href="#">Shop</a></li>
						 <li><a href="#">Shopping Cart</a></li>
						<li><a class="border_none" href="#">Checkout</a></li>
					  </ul>
                   </li>
				<li><a class="m_tag" href="#">Become an Agent</a></li>
				<li><a class="m_tag" href="#">Agent Login</a></li>
				<li><a class="m_tag" href="#aboutModal" data-toggle="modal" data-target="#myModal"><i class="fa fa-search"></i></a></li>
				<div class="modal fade" id="myModal" tabindex="-1" role="dialog"  aria-hidden="true">
            </div>
	            <li class="dropdown">
					<a class="m_tag" href="#" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-shopping-bag"></i></a>
					<ul class="dropdown-menu drop_1" role="menu">
						<li>
						 <div class="drop_1i clearfix">
						  <div class="col-sm-6">
						   <div class="drop_1il clearfix"><h5 class="mgt">2 ITEMS</h5></div>
						  </div>
						  <div class="col-sm-6">
						   <div class="drop_1il text-right clearfix"><h5 class="mgt"><a href="#">VIEW CART</a></h5></div>
						  </div>
						 </div>
						 <div class="drop_1i1 clearfix">
						  <div class="col-sm-6">
						   <div class="drop_1i1l clearfix"><h6 class="mgt bold"><a href="#">Nulla Quis</a> <br> <span class="normal col_2 font_14">1x - $89.00</span> </h6></div>
						  </div>
						  <div class="col-sm-4">
						   <div class="drop_1i1r text-right clearfix"><a href="#"><img src="img/1.jpg" class="iw" height="70" alt="abc"></a></div>
						  </div>
						  <div class="col-sm-2">
						   <div class="drop_1i1r drop_1i1rn text-right clearfix"><a href="#"><span><i class="fa fa-trash"></i></span></a></div>
						  </div>
						 </div>
						 <div class="drop_1i2 clearfix">
						  <div class="col-sm-6">
						   <div class="drop_1il clearfix"><h5 class="mgt">TOTAL</h5></div>
						  </div>
						  <div class="col-sm-6">
						   <div class="drop_1il text-right clearfix"><h5 class="mgt col_1">$138.00</h5></div>
						  </div>
						 </div>
						 <div class="drop_1i3 text-center clearfix">
						  <div class="col-sm-12">
						   <h5><a class="button_1 block" href="#">CHECKOUT</a></h5>
						   <h5><a class="button block" href="#">VIEW CART</a></h5>
						  </div>
						 </div>
						</li>
					  </ul>
					</li>
		  	    </ul>
                 
			</div>
			<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>
	</section>

<section id="spec">
 <div class="container">
  <div class="row">
   <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h3 class="mgt"><i class="fa fa-play"></i> ORDER <span class="col_1">CONFIRMATION</span></h3>
	</div>
   </div>
  </div>
 </div>
</section>

<section id="popular">
 <div class="container">
  <div class="row">
   <div class="popular_1 clearfix">

<div class="col-sm-12 col-12 p-4" style="border: 1px solid #dee2e6;">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
<div class="grid clearfix mb-3 pb-4">
<br>
  <!-- ======= SUCCESS MESSAGE ======= -->
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success shadow-sm p-3 mb-4 mt-2 pt-3 rounded" id="successMessage" style="border-left: 6px solid green; background: #e8f9ee;">
        <br>
      <i class="fa fa-check-circle text-success"></i>
      <strong> Payment Successful!</strong><br>
      <span class="text-dark"><?php echo htmlspecialchars($_GET['success']); ?></span>
    </div>

    <?php if (isset($_GET['reference_id'])): ?>
      <div class="card border-success mb-3 shadow-sm" style="border-radius: 12px;">
        <h4 class="card-header text-white fw-bold">
          <i class="fa fa-credit-card"></i> Transaction Details
        </h4>
        <div class="card-body bg-light">
          <p class="mb-2"><strong>Reference ID:</strong> 
            <span class="text-white"><?php echo htmlspecialchars($_GET['reference_id']); ?></span>
          </p>

          <?php
            include 'database/waec_dbconfig.php';
            $ref = mysqli_real_escape_string($con, $_GET['reference_id']);
            $query = "SELECT serial_code, pin_code FROM payments WHERE reference_id = '$ref'";
            $result = mysqli_query($con, $query);
            if ($result && mysqli_num_rows($result) > 0):
          ?>
            <div class="table-responsive">
              <table class="table table-bordered mt-3">
                <thead class="table-success">
                  <tr>
                    <th>Serial Code</th>
                    <th>PIN Code</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td><code><?php echo htmlspecialchars($row['serial_code']); ?></code></td>
                      <td><code><?php echo htmlspecialchars($row['pin_code']); ?></code></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
            <p class="text-muted small">Keep your Serial and PIN safe — they are needed for checking results.</p>
          <?php else: ?>
            <p class="text-warning">No codes found for this reference.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- ======= ERROR MESSAGE ======= -->
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger shadow-sm p-3 mb-4 rounded" id="errorMessage" style="border-left: 6px solid red; background: #fde8e8;">
      <i class="fa fa-exclamation-circle text-danger"></i>
      <strong> Payment Failed!</strong><br>
      <span class="text-dark"><?php echo htmlspecialchars($_GET['error']); ?></span>
      <?php if (isset($_GET['reference_id'])): ?>
        <div class="mt-2 text-muted small">Reference ID: 
          <strong><?php echo htmlspecialchars($_GET['reference_id']); ?></strong>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

    </div>
  </div>
</div>

	

   </div>
 
  </div>
 </div>
</section>


<section id="footer">
 <div class="container">
  <div class="row">
   <div class="footer_2 clearfix">
    <div class="col-sm-3">
	 <div class="footer_2i clearfix">
	   <h4 class="mgt">ADDRESS OFFICE</h4>
	   <hr class="hr_1 mgl">
	   <p>Accra, Ghana Open: 9:30 AM 8:30PM</p>
	   <p><i class="fa fa-headphones col_1"></i>   Phone : +91 1234 567 890</p>
	   <p><i class="fa fa-envelope col_1"></i>   Email : info@gmail.com</p>
	 </div>
	</div>
   </div>
  </div>
 </div>
</section>

<section id="footer_b">
 <div class="container">
  <div class="row">
   <div class="footer_b1 text-center clearfix">
    <div class="col-sm-12">
	  <p class="mgt col_3">© 2025 POWERMYNET. All Rights Reserved | Design by Tyler</p>
	</div>
   </div>
  </div>
 </div>
</section>

</body>
 
</html>
