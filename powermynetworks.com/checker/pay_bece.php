<?php
include 'waec_dbconfig.php';
require_once 'config.php'; // contains PAYSTACK_PUBLIC_KEY
// Base amount you want to receive
$customer  = $_GET['customer'];
$telephone = $_GET['telephone'];
$email     = $telephone . "@gmail.com";
$qty = $_GET['qty'];

// Target package
$target = "BECE";
// Fetch amount from prices table
$stmt = $con->prepare("SELECT amount FROM prices WHERE target = ? LIMIT 1");
$stmt->bind_param("s", $target);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
$row = $res->fetch_assoc();
$amount = $row['amount']; // format to 2 decimal places
}

$base_amount = $amount*$qty; // you want to receive ₵1
// === Calculate Paystack Fee ===
$fee_percent = 0.0195; // 1.95%
$final = $base_amount * $fee_percent;
$final_amount = $base_amount + $final;
// Round to 2 decimals for display
$final_amount = round($final_amount, 2);

?>

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
            <div class="row px-2 mt-2">
               <div class="col-sm-12 col-12"> 
                       <?php
        if(isset($_GET['qty'])){
             ?>
                <div>Card Type  : BECE Checker</div>
                <div>Quantity   : <?php echo $_GET['qty']; ?></div>
                <div>Unit Price : GHS <?php echo $amount ?></div>
             <?php
            }
        ?>
                </div>
            </div>
            </div>
        </div>
      </div>
    </div>
  <div class="popular_1i2 clearfix mt-3">
    <h3>
        <?php
        if(isset($_GET['qty'])){
            $qty = $_GET['qty'];
            $amount = $amount;
            $total = $base_amount;
            ?>
      <span class="span_3 col_1">TOTAL : GHS <?php echo $total; ?></span>
      <?php
            }
        ?>
    </h3>
        <h4>How to Pay using your MoMo number</h4>
        <ul>
            <ol>1. Confirm your details</ol>
            <ol>2. Click on "PAY NOW" button</ol>
            <ol>3. Enter your MoMo number</ol>
            <ol>4. Enter MoMo Pin to authorize payment</ol>
            <ol>5. Wait for some few seconds for the payment process to complete</ol>
            <ol>6. PIN and Serial codes will be sent your number via SMS.</ol>
        </ul>
  </div>
</div>

<div class="col-sm-6 col-12 p-4" style="border: 1px solid #dee2e6;">
  <div class="popular_1i clearfix">
    <div class="popular_1i1 clearfix">
      <div class="grid clearfix">
          <br>
                 <h4 class="card-header text-white fw-bold">Wait for the whole process to complete after payment </h4>
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
      <input type="text" id="amount" class="form-control" value="<?php echo $base_amount; ?>" readonly>
    </div>
<br>
    <!-- Submit -->
    <div class="mb-3 mt-4">
              <script src="https://js.paystack.co/v1/inline.js"></script>
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
	<?php include 'parts/footer.php'; ?> 

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
      window.location.href = "payments_bece.php?reference=" + encodeURIComponent(response.reference);
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
