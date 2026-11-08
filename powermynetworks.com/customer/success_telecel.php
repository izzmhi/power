<!DOCTYPE html>

<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if reference_id is available in the URL
if (isset($_GET['reference_id'])) {
    $reference_id = filter_var($_GET['reference_id'], FILTER_SANITIZE_STRING);
    // Set the cookie to expire after 24 hours (86400 seconds = 24 hours)
    setcookie("reference_id", $reference_id, time() + 86400, "/");
    // Optionally display a success message or redirect the user
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
            <h1 class="h1">Success Page</h1>
        </div>
    </div>
    <!-- Start Section -->
    <section class="container py-2">
        <div class="row text-center pt-2 pb-1">
            <div class="col-lg-6 m-auto">
                <h3>ORDER INFO</h3>
               <?php
                    if(isset($_GET['success'])){
                        ?>
                    <div class="card">
                      <div class="card-header bg-success text-white">
                        <?php echo $_GET['success'] ?>
                      </div>
                      <div class="card-body">
                        <p class="card-text">Your ORDER ID is: <?php echo $_GET['reference_id']; ?></p>
                        <small>Keep your Order ID. You will need it to check order status</small>

                      </div>
                    </div>
    
                        <?php
                    }
                        else if(isset($_GET['error'])){
                        ?>
                        <div class="bg-danger text-white"><?php echo $_GET['error']; ?></div>
                        <?php
                    }
                ?>
            </div>
        </div>
        
        <div class="row text-center pt-2 pb-1">
            <div class="col-lg-6 m-auto">
                   <div class="card">
                       <div class="card-body">
          <?php

if(isset($_GET['reference_id'])){
$errors = [];
$success = [];
include '../database/dbconfig.php';
    $reference_id = $_GET['reference_id'];
            include '../database/dbconfig.php';
            $ref_id = $reference_id;
            $select = "SELECT * FROM orders_agent WHERE reference_id='$reference_id'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
            <div>Number: <?php echo $row['beneficiary']; ?></div>
            <div>Package: <?php echo $row['gig']; ?>Gig</div>
            <div>Price: GHS<?php echo $row['price']; ?></div>
            <div>Order Date: <?php echo $row['order_date']; ?> - <div>Time: <?php echo $row['order_time']; ?></div></div>
            <div class='mb-3'>Order status is <strong><?php echo $row['order_status']; ?></strong> </div>
            <?php
                }
            }
}
else{
        header("Location: auth/login4");
}
?>

                   </div>
                </div>
            </div>

        </div>
    </section>
    <!-- End Section -->
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