<!DOCTYPE html>
<html lang="en">
  <head>
    	<?php include 'parts/head.php'; ?> 
  </head>
<body>
    <link  rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
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
    <h4 class="col big">WAEC RESULTS CHECKER CARDS</h4>
     <!-- WhatsApp Button with Image -->
    <a href="https://wa.me/233248348763?text=Hello%20POWERMYNET!%20I%20want%20to%20buy%20a%20WAEC%20Results%20Checker%20Card."
       target="_blank" class="whatsapp-btn mt-3" style="background-color:#90EE90; border-radius:10px; padding:10px 20px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:10px; font-weight:bold; color:black;">
      <img src="asset/images/WhatsApp.png" alt="Buy on WhatsApp" style="height:40px;">
      <span style="font-size:25px;">Buy on WhatsApp</span>
    </a>
    <br>
        <a href="retrive.php"
       target="_blank" class="btn btn-primary mt-3" style="border-radius:10px; padding:10px 20px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:10px; font-weight:bold; color:white;">
      <span style="font-size:25px;">Retrieve Card</span>
    </a>
    <br>
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
<section id="popular">
 <div class="container">
  <div class="row">
 
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
        <form id="payForm" class="mt-3 p-4 m-5 rounded" action="pay_wassce.php" enctype="multipart/form-data" style="padding:20px;">
          
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
            For 2025 WASSCE, 2024 NOV/DEC to 1992 WASSCE & NOV/DEC
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
        <form id="payForm" class="mt-3 p-4 m-5 rounded" action="pay_bece.php" enctype="multipart/form-data" style="padding:20px;">
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
  <hr>

  <div class="row">
        <div class="spec_1 clearfix">
    <div class="col-sm-12">
	 <h4 class="mgt" style="text-align:center;">PURCHASE WAEC RESULTS CHECKER CARDS <span class="col_1"></span></h4>
	 <h5 class="mgt" style="text-align:center;">Pay with MoMo (MTN,TELECEL,AT and Bank Cards) <span class="col_1"></span></h5>
<div class="d-flex justify-content-center align-items-center flex-wrap gap-4 fw-bold fs-5 text-dark" style="text-align:center;">
  <span style="font-size:16px;"><i class="fa fa-bolt text-success"></i> Instant Delivery</span>
  <span style="font-size:16px;"><i class="fa fa-lock text-primary"></i> Secure Payment</span>
  <span style="font-size:16px;"><i class="fa fa-headphones text-danger"></i> 24/7 Support</span>
</div>

	</div>
	<br>
	<div class="col-sm-12">
	    
	</div>
   </div>
  </div>
  
  
 </div>
</section>
<hr>
<br>
<div style="padding:5px;">
    <h4>How to Buy WAEC Resuts Checker</h4>
        <ul style="font-size:20px;">
            <ol>1. Visit powermynetworks.com</ol>
            <ol>2. Select Results Checker</ol>
            <ol>3. Select the Type of Card (WASSCE or BECE)</ol>
            <ol>4. Enter Quantity and your payment details</ol>
            <ol>5. Confirm and click on Pay Now button</ol>
            <ol>6. Enter your MoMo to pay with Paystack</ol>
            <ol>7. Enter MoMo Pin to authorize payment</ol>
            <ol>8. Wait for some few seconds for the payment process to complete</ol>
            <ol>9. PIN and Serial codes will be sent your number via SMS.</ol>
        </ul>
    <br>
    <h4>How to Check your WAEC results</h4>
    <ul style="font-size:20px;">
            <ol>1. Firstly, log on to the WAEC Portal https://ghana.waecdirect.org/</ol>
            <ol>2. Then fill in the required candidate examination detail as follows: Index Number, Examination type and Examination Year</ol>
            <ol>3. Now enter the Personal Identification Number (PIN) and Serial of the checker card, e.g.  SERIAL: WAT43278578 and PIN: 54768493957.</ol>
            <ol>4. Finally, click on Submit after reviewing your details. Your results will appear for printing</ol>
        </ul>
        
        <h4>How to Retrieve your Resuts Checker after payment or when the Old check is lost</h4>
        <ul style="font-size:20px;">
            <ol>1. Visit powermynetworks.com</ol>
            <ol>2. Select Results Checker</ol>
            <ol>3. Click on Retrive button on the MENU or HOMEPAGE</ol>
            <ol>4. Enter the reference given and phone number you entered at the payments details field</ol>
            <ol>5. PIN and Serial codes will be sent your number via SMS.</ol>
        </ul>
        
        <hr>
        <p style="font-size:15px; text-align:center;">If you face any problem, Call us on 0248348763 or SEND us a WHATSAPP message on +233248348763 for instant assistance</p>
</div>

	<?php include 'parts/footer.php'; ?> 

</body>
</html>