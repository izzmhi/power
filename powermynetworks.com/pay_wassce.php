<?php
require_once 'configs/config.php'; // contains PAYSTACK_PUBLIC_KEY
// Base amount you want to receive
$customer  = $_GET['customer'];
$telephone = $_GET['telephone'];
$email     = $telephone . "@gmail.com";
$qty = $_GET['qty'];
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
<div class="col-sm-6 col-12">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
        <div>
            <!-- Input: fixed width and numeric-only safeguards -->
            <div class="row px-2 mt-4">
               <div class="col-sm-12 col-12"> 
                <P>Card Type  : WASSCE / Nov/Dec Checker</P>
                <P>Quantity   : 1</P>
                <P>Unit Price : GHS 30.00</P>
                </div>
            </div>
            </div>
        </div>
      </div>
    </div>
  <div class="popular_1i2 clearfix mt-3">
    <h3>
        <?php
        if(isset($_POST['buy_wassce'])){
            $qty = $_POST['qty'];
            $amount = "2";
            $total = $qty*$amount;
            ?>
      <span class="span_3 col_1">TOTAL : GHS <?php echo $total; ?></span>
      <?php
            }
        ?>
    </h3>
  </div>
</div>

<div class="col-sm-6 col-12 p-4" style="border: 1px solid #dee2e6;">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
          <br>
                 <h4 class="card-header text-white fw-bold">Wait for the whole process to complete after payment        </h4>
<!-- ======= PAYMENT FORM ======= -->
  <form id="payForm" onsubmit="return false;">
      <br>
    <!-- Full Name -->
    <div class="mb-3">
      <label class="fw-bold">Full Name</label>
      <input type="text" id="customer" class="form-control" value="<?php echo htmlspecialchars($customer); ?>" readonly>
    </div>

    <!-- Telephone -->
    <div class="mb-3">
      <label class="fw-bold">Telephone</label>
      <input type="text" id="telephone" class="form-control" value="<?php echo htmlspecialchars($telephone); ?>" readonly>
    </div>

    <!-- Amount -->
    <div class="mb-3">
      <label class="fw-bold">Amount (GHS)</label>
      <input type="text" id="amount" class="form-control" value="<?php echo $final_amount; ?>" readonly>
    </div>
<br>
    <!-- Submit -->
    <div class="mb-3 mt-4">
         <button type="button" class="btn btn-success btn-block w-100 fw-bold py-2" onclick="payWithPaystack()"> PAY NOW</button>
    </div>
  </form>
  <br>

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

 <script>
function payWithPaystack() {
  var handler = PaystackPop.setup({
    key: "<?php echo PAYSTACK_PUBLIC_KEY; ?>",
    email: "<?php echo $email; ?>",
    amount: <?php echo $final_amount; ?> * 100, // Convert GHS to pesewas
    currency: "GHS",
    metadata: {
      custom_fields: [
        { display_name: "Customer Name", variable_name: "customer", value: "<?php echo $customer; ?>" },
        { display_name: "Telephone", variable_name: "telephone", value: "<?php echo $telephone; ?>" },
        { display_name: "Quantities", variable_name: "quantities", value: "<?php echo $qty; ?>" }
      ]
    },
    callback: function(response) {
      // ✅ Redirect immediately — no alert
      window.location.href = "payments_wassce.php?reference=" + encodeURIComponent(response.reference);
    },
    onClose: function() {
      // You can log this or do nothing
      console.log("Payment window closed");
    }
  });

  handler.openIframe();
}
</script>

</body>
 
</html>
