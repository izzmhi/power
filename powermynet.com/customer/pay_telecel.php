<!DOCTYPE html>

<?php
session_start();
if (!isset($_SESSION['package'])) {
    header('Location: index.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

        $network = $_SESSION['network']; 
        $code_network = $_SESSION['code_network']; 
		$gig = $_SESSION['gig'];
		$real_package = $_SESSION['real_package'];
		$actual_price = $_SESSION['actual_price'];
		$price = $_SESSION['price'];
		$email = $_SESSION['email'];
		$beneficiary = $_SESSION['beneficiary'];
		
		$_SESSION['networks']=$network; 
		$_SESSION['code_networks']=$code_network; 
		$_SESSION['gigs']=$gig;
		$_SESSION['prices']=$price;
		$_SESSION['actual_prices']=$actual_price;
		$_SESSION['emails']=$email;
		$_SESSION['beneficiarys']=$beneficiary;
		$_SESSION['real_packages'] = $real_package;
		
	if(isset($_POST['test'])){
		$network = $_SESSION['network']; 
		$code_network = $_SESSION['code_network']; 
		$gig = $_SESSION['gig'];
		$real_package = $_SESSION['real_package'];
		$price = $_SESSION['price'];
		$email = $_SESSION['email'];
		$beneficiary = $_SESSION['beneficiary'];
		$actual_price = $_SESSION['actual_price'];
		header("location:telecel_pay.php?paystackpaid=successfullypaid&network=$network&gig=$gig&price=$price&email=$email&beneficiary=$beneficiary&real_package=$real_package&actual_price=$actual_price");
	}
?>

<html lang="en">
<head>
    <?php include 'parts/head.php'; ?> 
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <?php include 'parts/header.php'; ?> 
    </nav>
    <!-- Close Header -->
    <div class="container-fluid bg-light py-2">
        <div class="col-md-6 m-auto text-center">
            <h1 class="h1">Payment Page</h1>
            <p> Please wait for the payment processing to complete before closing it </p>
        </div>
    </div>
<!-- Start Contact -->
                <div class="container py-2">
                    <div class="row py-2">

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="network" value="<?php echo $_SESSION['network'] ?>" readonly>
                                        <label for="name">Network</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="gig" value="<?php echo $_SESSION['gig'] ?>" readonly>
                                        <label for="email">Gig (Data Bundle)</label>
                                    </div>
                                </div>
                                                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="gig" value="<?php echo $_SESSION['real_package'] ?>" readonly>
                                        <label for="email">Real Package</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="phone" class="form-control" id="phone" name="price" value="<?php echo $_SESSION['actual_price'] ?>" readonly>
                                        <label for="phone">Price (GHS)</label>
                                        <small class="form-text text-muted">2% is added as transaction charges</small>
                                    </div>
                                </div>

                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="project" name="price" value="<?php echo $_SESSION['price'] ?>" readonly>
                                        <label for="project">Price after charges</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="project" name="email" value="<?php echo $_SESSION['email'] ?>" readonly>
                                        <label for="project">Email</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="subject" name="beneficiary" value="<?php echo $_SESSION['beneficiary'] ?>" readonly>
                                        <label for="subject">Data Bundle Recipient number</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                        <script src="https://js.paystack.co/v1/inline.js"></script>
                                    <button class="btn btn-primary w-100 py-3" type="button" name="sub" onClick="payPageWithPaystack()" id="add">CONFIRM DETAILS</button>
                                    <button type="submit" name="test" class="btn btn-block btn-primary" >Test</button> -->
                                </div>
                            </div>
                        </form>

                   </div>
                </div>

<script>
  function payPageWithPaystack(){
  const api = "REDACTED";
    var handler = PaystackPop.setup({
      key: api,
      email: "<?php echo $email; ?>",
      amount: <?php echo $price*100; ?>,
      currency: "GHS",
      beneficiary: "<?php echo $beneficiary; ?>",
      network: "<?php echo $network; ?>",
      bundle: "<?php echo $gig; ?>",
      // label: "Optional string that replaces customer email"
      metadata: {
         custom_fields: [
            {
                display_name: "Email:",
                variable_name: "email",
                value: "<?php echo $email; ?>"
            },
            {
                display_name: "Beneficiary:",
                variable_name: "beneficiary",
                value: "<?php echo $beneficiary; ?>"
            },
            {
                display_name: "Network:",
                variable_name: "network",
                value: "<?php echo $network; ?>"
            },
            {
                display_name: "Bundle:",
                variable_name: "bundle",
                value: "<?php echo $gig; ?>"
            },
            {
                display_name: "Amount:",
                variable_name: "amount",
                value: "<?php echo $price; ?>"
            }
            
         ]
      },
      callback: function(response){
           const referenced = response.reference;
       window.location.href = `telecel_pay.php?paystackpaid=${encodeURIComponent(referenced)}`;
      },
      onClose: function(){
          alert('window closed');
      }
    });
    handler.openIframe();
  }
</script>

    <!-- Start Footer -->
    <footer class="bg-dark" id="tempaltemo_footer">
        <?php include 'parts/footer.php'; ?> 
    </footer>
    <!-- End Footer -->

    <!-- Start Script -->
    <script src="../assets/js/jquery-1.11.0.min.js"></script>
    <script src="../assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/templatemo.js"></script>
    <script src="../assets/js/custom.js"></script>
    <!-- End Script -->

    <!-- Start Slider Script -->
    <script src="../assets/js/slick.min.js"></script>

</body>

</html>