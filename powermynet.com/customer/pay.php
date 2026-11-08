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
		$gig = $_SESSION['gig'];
		$price = $_SESSION['price'];
		$email = $_SESSION['email'];
		$beneficiary = $_SESSION['beneficiary'];
		
		$_SESSION['networks']=$network; 
		$_SESSION['gigs']=$gig;
		$_SESSION['price']=$price;
		$_SESSION['emails']=$email;
		$_SESSION['beneficiarys']=$beneficiary;
		
	if(isset($_POST['test'])){
		$network = $_SESSION['network']; 
		$gig = $_SESSION['gig'];
		$price = $_SESSION['price'];
		$email = $_SESSION['email'];
		$beneficiary = $_SESSION['beneficiary'];
		
		header("Location: mtn_pay.php?paystackpaid=successfullypaid&network=$network&gig=$gig&price=$price&beneficiary=$beneficiary&email=$email");
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
                            <li class="nav-item"> <a class="nav-link click-scroll" href="success.php"> Check MTN Status <i class="fa fa-check-circle" aria-hidden="true"></i></a> </li>
                            <li class="nav-item"> <a class="nav-link click-scroll" href="success1.php"> Check AT/TELECEL Status <i class="fa fa-check-circle" aria-hidden="true"></i></a> </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <header class="site-header " style="height: 50px;">
                <div class="container " style="height: 10px;">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-12">
                            <h4 class="text-white mb-5" >Order Confirmation</h4>
                        </div>
                    </div>
                </div>
            </header>
            
    <section class="section-padding section-bg">
        <div class="container">
                    
            <div  class="row" style="margin-top:-40px;">
             <h6 style="text-align:center;">Please wait for the payment processing to complete before closing it</h6>
               <div class="col-xl-6 col-lg-6 col-md-6 mx-auto">
                <div class="card z-index-0 px-3 py-2">
                <h4 class="px-1 py-1">Payment Info</h4>
               <form action="" method="POST" enctype="multipart/form-data">
                  <div class="mb-3">
                    <input type="text" class="form-control" name="network" value="<?php echo $_SESSION['network'] ?>" readonly>
                  </div>
                  <div class="mb-3">
                        <label>Gig (Data Bundle)</label>
                    <input type="text" class="form-control" name="gig" value="<?php echo $_SESSION['gig'] ?>" readonly>
                  </div>
                   <div class="mb-3">
                         <label>Price (GHS)</label>
                    <input type="text" class="form-control" name="price" value="<?php echo $_SESSION['price'] ?>" readonly>
                     <!--   <small class="form-text text-muted">2% is added as transaction charges</small> -->
                  </div>
                   <!--
                   <div class="mb-3">
                         <label>Price after charges</label>
                    <input type="text" class="form-control" name="price" value="<?php echo $_SESSION['actual_price'] ?>" readonly>
                  </div>
                  -->
                   <div class="mb-3">
                    <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['email'] ?>" readonly>
                  </div>
                  <div class="mb-3" >
                       <label>Data Bundle Recipient number</label>
                    <input type="tel" class="form-control" name="beneficiary" value="<?php echo $_SESSION['beneficiary'] ?>" readonly>
                  </div>
                  <div class="text-center">
                    <script src="https://js.paystack.co/v1/inline.js"></script>
                    <button type="button" name="sub" class="btn btn-block btn-primary" onClick="payPageWithPaystack()" id="add">CONFIRM DETAILS</button> 
                    <!-- <button type="submit" name="test" class="btn btn-block btn-primary" >Test</button> -->
                  </div>
                </form>
            </div>
          </div>                   
  
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
</div> 
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
      window.location.href = `mtn_pay.php?paystackpaid=${encodeURIComponent(referenced)}`;
      },
      onClose: function(){
          alert('window closed');
      }
    });
    handler.openIframe();
  }
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