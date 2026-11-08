<?php
require_once 'config.php'; // contains PAYSTACK_PUBLIC_KEY

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
            include 'waec_dbconfig.php';
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

	<?php include 'parts/footer.php'; ?> 

</body>
 
</html>
