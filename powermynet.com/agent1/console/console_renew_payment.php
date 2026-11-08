<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../../database/dbconfig.php';
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
       $_SESSION['wallet'] = $rows['wallet'];
    }
}
		$first_name = $_SESSION['first_name'];
		$last_name = $_SESSION['last_name'];
		$email = $_SESSION['email'];
		$telephone = $_SESSION['telephone'];
		
$errors = array();
$success = array();

?>


<!DOCTYPE html>
<html lang="en">
  <?php include 'includes/head.php'; ?> 
<body>
  <!-- ======= Header ======= -->
 <?php include 'includes/header.php'; ?> 
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
   <?php include 'includes/sidebar.php'; ?> 
  <!-- End Sidebar-->
  <main id="main" class="main">
    <section class="section dashboard">
      <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">
            <!-- Recent Sales -->
            <div class="col-12">
  				<div class="row gutters">
					<div class="col-lg-6 col-sm-12 col-12">
						<div class="card h-100">
							<div class="card-header">
								<h4>Payment</h4>
								<div class="card-title">Please wait for the payment processing to complete before closing it</div>
							</div>
								<div class="card-body">
			                        <div class="row">
                                        <div class="col-xl-12 col-md-12 col-12">
                                            <div class="row">
                                                <div>
                                                     <h4><?php echo $_SESSION['network']; ?></h4>
                                                </div>   
                                            </div>
                                            <div class="row">
                                                <div>
                                                    <span><?php echo $_SESSION['console_name']; ?></span>  
                                                </div>   
                                            </div>
                                               <div class="row">
                                                   <div>
                                                       <span class="text-info"><?php echo $_SESSION['real_package']; ?></span></span>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <form action="" method="POST" enctype="multipart/form-data" id="priceForm">
                                           <div class="row gutters">
                                               <div class="col-sm-12 col-12">
                                                   <div class="form-group">
                                                       <label for="fullName">Amount in GHS*</label>
                                                       <input type="text" class="form-control" id="price" name="price" value="<?php echo $_SESSION['purchase_amount'] ?>" readonly>
                                                   </div>
                                               </div>
                                       
                                               <div class="col-sm-12">
                                                   <div class="text-right">
                                                       <script src="https://js.paystack.co/v1/inline.js"></script>
                                                       <button type="button" name="sub" class="btn btn-block btn-primary" onClick="payPageWithPaystack()" id="add">Pay <i class="fa fa-money" aria-hidden="true"></i></button>
                                                   </div>
                                               </div>
                                           </div>
                                       </form>

								</div>
							</div>
						</div>
					</div>
                  
        
                      </div>

                    </div>
                </div><!-- End Recent Sales -->

             </div>
          </div><!-- End Left side columns -->

       </div>
    </section>

  </main><!-- End #main -->
 <?php include 'includes/footer.php'; ?> 
  <!-- ======= Footer ======= -->
 
  <!-- End Footer -->

 <script>
    function payPageWithPaystack() {
        const api = "REDACTED";
        const priceInput = document.getElementById('price').value;
        var handler = PaystackPop.setup({
            key: api,
            email: "<?php echo $email; ?>",
            amount: priceInput * 100,
            currency: "GHS",
            first_name: "<?php echo $first_name; ?>",
            last_name: "<?php echo $last_name; ?>",
            telephone: "<?php echo $telephone; ?>",
            metadata: {
                custom_fields: [
                    {
                        display_name: "First Name:",
                        variable_name: "first_name",
                        value: "<?php echo $first_name; ?>"
                    },
                    {
                        display_name: "Last Name:",
                        variable_name: "last_name",
                        value: "<?php echo $last_name; ?>"
                    },
                    {
                        display_name: "Email:",
                        variable_name: "email",
                        value: "<?php echo $email; ?>"
                    },
                    {
                        display_name: "Telephone:",
                        variable_name: "telephone",
                        value: "<?php echo $telephone; ?>"
                    },
                    {
                        display_name: "Amount:",
                        variable_name: "amount",
                        value: priceInput
                    }
                ]
            },
            callback: function(response){
                const referenced = response.reference;
               // Create a form dynamically and submit it via POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'renew_processing.php';  // Target PHP script

                // Create hidden input for the reference
                const referenceInput = document.createElement('input');
                referenceInput.type = 'hidden';
                referenceInput.name = 'successfullypaid';
                referenceInput.value = referenced;
                form.appendChild(referenceInput);

                // Create hidden input for the price
                const priceInputField = document.createElement('input');
                priceInputField.type = 'hidden';
                priceInputField.name = 'price';
                priceInputField.value = priceInput;  // Price to be sent via POST
                form.appendChild(priceInputField);

                // Append the form to the document and submit it
                document.body.appendChild(form);
                form.submit();  // Submit form
            },
            onClose: function(){
                alert('Window closed without completing the payment.');
            }
        });
        handler.openIframe();
    }
</script>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.min.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
</body>
</html>