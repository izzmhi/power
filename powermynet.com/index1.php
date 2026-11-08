<!DOCTYPE html>
<html lang="en">
  <head>
    	<?php include 'parts/head.php'; ?> 
  </head>
<body>
<section id="header" class="clearfix cd-secondary-nav">
	<?php $page = 'home' ?>
	<?php include 'parts/header.php'; ?> 
</section>
	
<div class="hero-section">
  <div class="hero-bg">
    <img src="img/bg.jpg" alt="WAEC Checker Background">
  </div> 
  <div class="hero-content"> 
    <h2 class="" style="color:black; ">POWERMYNET</h2>
    <h4 class="col">WAEC Results Checker Cards, Shop and Passport and Birth Certificate</h4> 
     <a href="shop" class="col_1" style="font-size:25px; background-colour:black; padding:10px;">Go To Online Shop</a>
  </div>
</div> 

<style>
/* === Hero Section === */
.hero-section {
  position: relative;
  width: 100%;
  height: 250px;
  overflow: hidden;
  border-radius: 6px;
}

/* === Blurred Background Image === */
.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.hero-bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(6px) brightness(70%);
  transform: scale(1.1); /* Slight zoom to hide blur edges */
}

/* === Overlay Text === */
.hero-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: white;
  text-align: center;
}

.hero-content h2 {
  font-size: 50px;
  font-weight: bold;
  letter-spacing: 2px;
  color: #fff;
  margin-bottom: 10px;
}

.hero-content h4 {
  font-size: 25px;
  font-weight: 600;
  color: #f8f8f8;
}
</style>

<br>

<section id="spec">
 <div class="container">
  <div class="row">
   <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h3 class="mgt"><i class="fa fa-play"></i> OUR <span class="col_1">SERVICES</span></h3>
	</div>
   </div>
   <div class="spec_3 clearfix">
    <div class="col-sm-12">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-12 space_left">
	   <div class="spec_3ll clearfix">
		<h4><a href="agent"><span class="col_1">1. DATA BUNDLE SHOP</span></a></h4>
		<h4><a href="http://shop.powermynetworks.com/"><span class="col_1">2. ONLINE SHOP</span></a></h4>
		<h4><a href="checker"><span class="col_1">3. WAEC RESULTS CHECKER</span> </a></h4>
		<h4><a href="#"><span class="col_1" data-toggle="modal" data-target="#passportModal">4. PASSPORT APPLICATION</span> </a></h4>
		<h4><a href="#"><span class="col_1" data-toggle="modal" data-target="#birthModal">5. BIRTH CERTIFICATE APPLICATION</span> </a></h4>
		<h4><a href="business.php"><span class="col_1">6. BUSINESS CERTIFICATE</span> </a></h4>
	   </div>
	  </div>
	 </div>
	</div>
   </div>
  </div>
 </div>
</section>

<!-- Modal Birth -->
<div class="modal fade" id="birthModal" tabindex="-1" role="dialog" aria-labelledby="birthModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-dark fw-bold" id="birthModalLabel" style="color:black;">Birth Certificate Application Requirements</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4">
        <!-- Applicant Details -->
        <h6 class="fw-bold mt-1" style="color:black;">Applicant Details</h6>
        <ul class="ms-3" style="color:black;">
          <li>1. NAME: First name, middle name, last name</li>
          <li>2. DATE OF BIRTH:</li>
          <li>3. FATHER NAME:</li>
          <li>4. FATHER OCCUPATION:</li>
          <li>5. MOTHER NAME:</li>
          <li>6. INFORMANT:</li>
          <li>7. RELIGION:</li>
        </ul>

        <!-- Required Documents -->
        <h6 class="fw-bold mt-3" style="color:black;">Required Documents</h6>
        <ul class=" ms-3 mb-3" style="color:black;">
          <li>1. GHA Card picture of the applicant</li>
          <li>2. GHA Card picture of any of the relatives (Mother or Father)</li>
        </ul>

        <!-- WhatsApp Button -->
        <div class="mt-4">
<a href="https://wa.me/233248348763?text=Hello%2C%20I%20would%20like%20to%20make%20an%20enquiry%20about%20a%20birth%20certificate." 
   class="btn btn-success w-100 py-2 fw-bold" target="_blank">
  <i class="fa fa-whatsapp me-2"></i> WhatsApp Us to Get Started
</a>

        </div>

      </div>
    </div>
  </div>
</div>


<!-- Modal Passport -->
<div class="modal fade" id="passportModal" tabindex="-1" role="dialog" aria-labelledby="passportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-dark fw-bold" id="passportModalLabel" style="color:black;">Passport Application Requirements</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4">
        
        <!-- Applicant Details -->
        <h6 class="fw-bold mt-3" style="color:black;">Applicant Details</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>1. Email Address:</li>
          <li>2. Telephone Number:</li>
          <li>3. House Number (if any):</li>
          <li>4. Street Name:</li>
          <li>5. Postal Address:</li>
          <li>6. Suburb:</li>
          <li>7. City of Residence:</li>
          <li>8. Marital Status:</li>
          <li>9. Profession:</li>
        </ul>

        <!-- Education -->
        <h6 class="fw-bold mt-3" style="color:black;">Education</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>10. Name of Institution:</li>
          <li>11. School Address:</li>
          <li>12. Date Completed:</li>
        </ul>

        <!-- Father Details -->
        <h6 class="fw-bold mt-3" style="color:black;">Father</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>13. Full Name:</li>
          <li>14. Living:</li>
          <li>15. Telephone (if any):</li>
          <li>16. Email (if any):</li>
          <li>17. Home Town:</li>
          <li>18. Residential Address:</li>
        </ul>

        <!-- Mother Details -->
        <h6 class="fw-bold mt-3" style="color:black;">Mother</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>19. Full Name:</li>
          <li>20. Living:</li>
          <li>21. Telephone (if any):</li>
          <li>22. Email (if any):</li>
          <li>23. Home Town:</li>
          <li>24. Residential Address:</li>
        </ul>

        <!-- Grandparent Details -->
        <h6 class="fw-bold mt-3" style="color:black;">Grandparent</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>25. Full Name:</li>
          <li>26. Living:</li>
          <li>27. Telephone (if any):</li>
          <li>28. Email (if any):</li>
          <li>29. Home Town:</li>
          <li>30. Residential Address:</li>
        </ul>

        <!-- Required Documents -->
        <h6 class="fw-bold mt-3" style="color:black;">Required Documents</h6>
        <ul class="list-unstyled ms-3" style="color:black;">
          <li>31. Passport size picture of the applicant</li>
          <li>32. Ghana Card</li>
          <li>33. Birth Certificate</li>
        </ul>

        <!-- WhatsApp Button -->
        <div class="mt-4">
        <a href="https://wa.me/233248348763?text=Hello%2C%20I%20would%20like%20to%20make%20an%20enquiry%20about%20a%20passport%20application." 
           class="btn btn-success w-100 py-2 fw-bold" target="_blank">
          <i class="fa fa-whatsapp me-2"></i> WhatsApp Us to Get Started
        </a>
        </div>
      </div>
    </div>
  </div>
</div>



<section id="spec">
 <div class="container">
  <div class="row">
   <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h3 class="mgt"><i class="fa fa-play"></i> WHAT WE <span class="col_1">OFFER</span></h3>
	</div>
   </div>
   <div class="spec_3 clearfix">
    <div class="col-sm-6">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-6 space_left">
	   <div class="spec_3ll clearfix">
	    <h5 class="mgt">SHOP <span class="col_1"></span></h5>
		<h4><a href="#"><span class="col_1">ELECTRONICS</span> PRODUCTS</a></h4>
		<p class="col_3">Modems, Flash Drives etc.</p>
		<h5><a class="button" href="http://shop.powermynetworks.com/">GO TO SHOP</a></h5>
	   </div>
	  </div>
	  <div class="col-sm-6 space_right">
	   <div class="spec_3lr clearfix">
	     <div class="grid clearfix">
		  <figure class="effect-jazz">
			<a href="#"><img src="img/modem.jpg" height="230" class="iw" alt="img25"></a>
		  </figure>
	  </div>
	   </div>  
	  </div>
	 </div>
	</div>
	
	<div class="col-sm-6">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-6 space_left">
	   <div class="spec_3ll clearfix">
	    <h5 class="mgt">WAEC RESULTS CHECKER</h5>
		<h4><a href="#"><span class="col_1"> BECE </span> WASSCE</a></h4>
		<p class="col_3">Buy your BECE and WASSCE results checkers.</p>
		<h5><a class="button" href="checker">GO TO SITE</a></h5>
	   </div>
	  </div>
	  <div class="col-sm-6 space_right">
	   <div class="spec_3lr clearfix">
	     <div class="grid clearfix">
		  <figure class="effect-jazz">
			<a href="#"><img src="img/waec-logo.jpg" height="230" class="iw" alt="img25"></a>
		  </figure>
	  </div>
	   </div>  
	  </div>
	 </div>
	</div>
	
		<div class="col-sm-6">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-6 space_left">
	   <div class="spec_3ll clearfix">
	    <h5 class="mgt">PASSPORT</h5>
		<p class="col_3">Get your express passport from us</p>
		<h5><a class="button" href="#" data-toggle="modal" data-target="#passportModal">READ MORE</a></h5>
	   </div>
	  </div>
	  <div class="col-sm-6 space_right">
	   <div class="spec_3lr clearfix">
	     <div class="grid clearfix">
		  <figure class="effect-jazz">
			<a href="#"><img src="img/pass.jpg" height="230" class="iw" alt="img25"></a>
		  </figure>
	  </div>
	   </div>  
	  </div>
	 </div>
	</div>
	
	<div class="col-sm-6">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-6 space_left">
	   <div class="spec_3ll clearfix">
	    <h5 class="mgt">BIRTH CERTIFICATE</h5>
		<p class="col_3">Get your express birth certificate from us</p>
		<h5><a class="button" href="#" data-toggle="modal" data-target="#birthModal">READ MORE</a></h5>
	   </div>
	  </div>
	  <div class="col-sm-6 space_right">
	   <div class="spec_3lr clearfix">
	     <div class="grid clearfix">
		  <figure class="effect-jazz">
			<a href="#"><img src="img/birth.jpeg" height="230" class="iw" alt="img25"></a>
		  </figure>
	  </div>
	   </div>  
	  </div>
	 </div>
	</div>
	
	<div class="col-sm-6">
	 <div class="spec_3l clearfix">
	  <div class="col-sm-6 space_left">
	   <div class="spec_3ll clearfix">
	    <h5 class="mgt">BUSINESS CERTIFICATE</h5>
		<h4><a href="#"><span class="col_1"> All Kinds of </span> Business Certificates</a></h4>
		<p class="col_3">Get your express business certificate from us</p>
		<h5><a class="button" href="business.php">READ MORE</a></h5>
	   </div>
	  </div>
	  <div class="col-sm-6 space_right">
	   <div class="spec_3lr clearfix">
	     <div class="grid clearfix">
		  <figure class="effect-jazz">
			<a href="#"><img src="img/business.jpg" height="230" class="iw" alt="img25"></a>
		  </figure>
	  </div>
	   </div>  
	  </div>
	 </div>
	</div>
	
   </div>
  </div>
 </div>
</section>

<section id="popular">
 <div class="container">
  <div class="row">
   <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h3 class="mgt"><i class="fa fa-play"></i> WAEC RESULTS CHECKER  <span class="col_1"></span></h3>
	</div>
   </div>
   <div class="popular_1 clearfix">
       
  <hr>
   <div class="popular_1 clearfix">
       
<!--wassce-->
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="wassceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Payment Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="payForm" class="mt-3 p-4 m-5 rounded" action="checker/pay_wassce.php" enctype="multipart/form-data" style="padding:20px;">
          <!-- Quantity -->
          <div class="row px-2 p-4">
            <div class="col-sm-12 col-12">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Quantity</label>
                <input type="number" id="qty" name="qty" class="form-control w-100"
                       inputmode="numeric" pattern="\d*" min="1" step="1" value="1" placeholder="Qty" required
                       oninput="updateTotal()" 
                       onkeydown="if(event.key === 'e' || event.key === 'E' || event.key === '.' || event.key === ',') event.preventDefault();">
              </div>
            </div>
          </div>
          <!-- Full Name -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Full Name</label>
                <input type="text" id="customer" name="customer" class="form-control" placeholder="Enter your full name" required>
              </div>
            </div>
          </div>
          <!-- Telephone -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Telephone (It will be used to receive SMS for the checker)</label>
                <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Enter your phone number" required>
              </div>
            </div>
          </div>
          <!-- Amount -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Total Amount (GHS)</label>
                <?php
                include 'waec_dbconfig.php';
                $target = "WASSCE";
                $stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
                $stmt->bind_param("s", $target);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $amount = number_format($row['amount'], 2, '.', '');
                ?>
                    <input type="text" id="amount" name="amount" class="form-control" value="<?php echo $amount; ?>" readonly>
                    <input type="hidden" id="unitPrice" value="<?php echo $amount; ?>">
                <?php
                } else {
                    echo '<h5 class="bg-danger text-center" style="color:white; padding:10px; font-weight:bolder; font-size:20px;">OUT OF STOCK</h5>';
                }
                ?>
              </div>
            </div>
            <!-- Submit -->
            <div class="col-12 mt-3">
              <button type="submit" name="pay_wassce" class="btn btn-success w-100 py-2 fw-bold" onclick="payWithPaystack()"> PAY NOW </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#qty").on("input", function() {
    let qty = $("#qty").val();
    $.ajax({
        url: "get_price.php",
        type: "POST",
        data: { qty: qty },
        success: function(response) {
            let data = JSON.parse(response);
            // Update amount with calculated total
            $("#amount").val(data.total);
        }
    });
});
</script>

<!-- JavaScript to calculate total -->
<script>
function updateTotal() {
  const qty = document.getElementById('qty').value;
  const unitPrice = parseFloat(document.getElementById('unitPrice').value);
  const total = (qty && unitPrice) ? (qty * unitPrice).toFixed(2) : unitPrice.toFixed(2);
  document.getElementById('amount').value = total;
}
</script>



<div class="col-sm-6 col-12">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
        <div>
            <h3 class="p-3 mb-4 text-center">
            WASSCE & NOV/DEC<br>
          </h3>
            <br>
          <h5 class="p-3 mb-4 text-center">
            For 2025 WASSCE, 2024 NOV/DEC
          </h5>
        
<?php
include 'waec_dbconfig.php';

$target = "WASSCE";

// Fetch activation status
$stmt = $con->prepare("SELECT wassce FROM activation");
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {

        $status = strtoupper(trim($row['wassce']));

        // If ACTIVATED, check stock
        if ($status === 'ACTIVATED') {

            // Fetch available codes
            $stmt2 = $con->prepare("SELECT status FROM codes WHERE package = ?");
            $stmt2->bind_param("s", $target);
            $stmt2->execute();
            $codeRes = $stmt2->get_result();

            if ($codeRes->num_rows > 0) {
                $out_of_stock = true;

                while ($codeRow = $codeRes->fetch_assoc()) {
                    if (strtoupper(trim($codeRow['status'])) !== 'USED') {
                        $out_of_stock = false;
                        break;
                    }
                }

                if ($out_of_stock) {
                    echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
                } else {
                    echo '<button type="button" class="btn btn-success btn-block w-100" data-toggle="modal" data-target="#wassceModal">Buy Now</button>';
                }

            } else {
                echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
            }

        } elseif ($status === 'DEACTIVATED') {
            echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
        }

    }

} else {
    echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
}

?>


        </div>
      </div>
    </div>
  </div>

<div class="popular_1i2 clearfix mt-3">
    <h3 class="text-center">
        <?php
        include 'waec_dbconfig.php';
        // Target package
        $target = "WASSCE";
        // Fetch amount from prices table
        $stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
        $stmt->bind_param("s", $target);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $amount = number_format($row['amount'], 2); // format to 2 decimal places
            ?>
            <span class="span_3 col_1" style="font-size:28px; font-weight:bold;">
                ₵<?php echo $amount; ?>
            </span>
            <?php
        } else {
            // No rows found — show out of stock
            echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
        }
        ?>
    </h3>
</div>

</div>
<!--wassce ends--> 

<!--BECE-->
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="beceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Payment Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="payForm" class="mt-3 p-4 m-5 rounded" action="checker/pay_bece.php" enctype="multipart/form-data" style="padding:20px;">
          <!-- Quantity -->
          <div class="row px-2 p-4">
            <div class="col-sm-12 col-12">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Quantity</label>
                <input type="number" id="qtyB" name="qty" class="form-control w-100"
                       inputmode="numeric" pattern="\d*" min="1" step="1" value="1" placeholder="Qty" required
                       oninput="updateTotalB()" 
                       onkeydown="if(event.key === 'e' || event.key === 'E' || event.key === '.' || event.key === ',') event.preventDefault();">
              </div>
            </div>
          </div>
          <!-- Full Name -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Full Name</label>
                <input type="text" id="customer" name="customer" class="form-control" placeholder="Enter your full name" required>
              </div>
            </div>
          </div>
          <!-- Telephone -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Telephone (It will be used to receive SMS for the checker)</label>
                <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Enter your phone number" required>
              </div>
            </div>
          </div>
          <!-- Amount -->
          <div class="row px-2 mt-2 p-4">
            <div class="col-12 mb-3">
              <div class="form-group">
                <label class="fw-bold mb-1" style="color:black;">Total Amount (GHS)</label>
                <?php
                include 'waec_dbconfig.php';
                $target = "BECE";
                $stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
                $stmt->bind_param("s", $target);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $amount = number_format($row['amount'], 2, '.', '');
                ?>
                    <input type="text" id="amountB" name="amount" class="form-control" value="<?php echo $amount; ?>" readonly>
                    <input type="hidden" id="unitPriceB" value="<?php echo $amount; ?>">
                <?php
                } else {
                    echo '<h5 class="bg-danger text-center" style="color:white; padding:10px; font-weight:bolder; font-size:20px;">OUT OF STOCK</h5>';
                }
                ?>
              </div>
            </div>
            <!-- Submit -->
            <div class="col-12 mt-3">
              <button type="submit" name="pay_bece" class="btn btn-success w-100 py-2 fw-bold" onclick="payWithPaystack()"> PAY NOW </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript to calculate total -->
<script>
function updateTotalB() {
  const qty = document.getElementById('qtyB').value;
  const unitPrice = parseFloat(document.getElementById('unitPriceB').value);
  const total = (qty && unitPrice) ? (qty * unitPrice).toFixed(2) : unitPrice.toFixed(2);
  document.getElementById('amountB').value = total; 
}
</script>


<div class="col-sm-6 col-12"> 
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
        <div>
            <h3 class="p-3 mb-4 text-center">
            BECE<br>
          </h3>
            <br>
          <h5 class="p-3 mb-4 text-center">
            For 2025 to 1990 BECE
          </h5>
          <br>
<?php
include 'waec_dbconfig.php';

$target = "BECE";

// Fetch activation status
$stmt = $con->prepare("SELECT bece FROM activation");
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {

        $status = strtoupper(trim($row['wassce']));

        // If ACTIVATED, check stock
        if ($status === 'ACTIVATED') {

            // Fetch available codes
            $stmt2 = $con->prepare("SELECT status FROM codes WHERE package = ?");
            $stmt2->bind_param("s", $target);
            $stmt2->execute();
            $codeRes = $stmt2->get_result();

            if ($codeRes->num_rows > 0) {
                $out_of_stock = true;

                while ($codeRow = $codeRes->fetch_assoc()) {
                    if (strtoupper(trim($codeRow['status'])) !== 'USED') {
                        $out_of_stock = false;
                        break;
                    }
                }

                if ($out_of_stock) {
                    echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
                } else {
                    echo '<button type="button" class="btn btn-success btn-block w-100" data-toggle="modal" data-target="#wassceModal">Buy Now</button>';
                }

            } else {
                echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
            }

        } elseif ($status === 'DEACTIVATED') {
            echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
        }

    }

} else {
    echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
}

?>


        </div>
      </div>
    </div>
  </div>

<div class="popular_1i2 clearfix mt-3">
    <h3 class="text-center">
        <?php
        include 'waec_dbconfig.php';
        // Target package
        $target = "BECE";
        // Fetch amount from prices table
        $stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
        $stmt->bind_param("s", $target);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $amount = number_format($row['amount'], 2); // format to 2 decimal places
            ?>
            <span class="span_3 col_1" style="font-size:28px; font-weight:bold;">
                ₵<?php echo $amount; ?>
            </span>
            <?php
        } else {
            // No rows found — show out of stock
            echo '<h5 class="bg-success text-center" style="color:black; padding:10px; font-weight:bolder; font-size:30px;">OUT OF STOCK</h5>';
        }
        ?>
    </h3>
</div>

</div>
<!--BECE ends-->
	

   </div>

   </div>
 
  </div>
 </div>
</section>

	<?php include 'parts/footer.php'; ?> 

</body>
 
</html>
