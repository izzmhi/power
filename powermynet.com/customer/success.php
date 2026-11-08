<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['reference'])) {
    header('Location: auth/login');
    exit();
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
                            <h4 class="text-white mb-5" >Order Status</h4>
                        </div>
                    </div>
                </div>
            </header>
            
            
    <section class="section-padding section-bg">
        <div class="container">
                    
            <div  class="row" style="margin-top:-40px;">
                                
        <div class="col-xl-12 col-lg-12 col-md-12 mx-auto">
            <div class="card z-index-0 px-3 py-2">
                        <h3 data-aos="fade-up">ORDER INFO</h3>
                        
                <?php
                    if(isset($_GET['success'])){
                            ?>
                            <div class="bg-success text-white"><?php echo $_GET['success'] ?></div>
                            <?php
                    }
                        else if(isset($_GET['error'])){
                            ?>
                            <div class="bg-danger text-white"><?php echo $_GET['error']; ?></div>
                            <?php
                    }
                ?>
                
                <?php
                if($_GET['network']='MTN'){
                    
                }
                elseif($_GET['network']='TELECEL'){
                    
                }
                elseif($_GET['network']='AFA'){
                    
                }
                elseif($_GET['network']='AT BIG TIME'){
                    
                }
                ?>

   <?php
// Set the API endpoint URL
require_once __DIR__ . '/../configs/pmn_provider.php';
$url = pmn_api_url('https://api.dataworksgh.com/api/response.php');
// Check if the email is set in the request (e.g., from a form submission or query string)
if (isset($_GET['telephone'])) {
    $telephone = $_GET['telephone'];

    // Append the email as a query parameter to the API URL
    $apiUrl = $url . '?telephone=' . urlencode($telephone);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl); // Set the API URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (only if necessary)

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Decode the JSON response
        $orderDetails = json_decode($response, true);

        // Check if the response contains order details and the status is 'success'
        if ($orderDetails && $orderDetails['status'] == 'success') {
            $details = $orderDetails['order_details'];

            // Display the order details
            echo "<div class='mb-3'>{$details['network']} {$details['gig']} GB</div>";
            echo "<div class='mb-3'>GHS{$details['agent_price']}</div>";
            echo "<div class='mb-3'>{$details['beneficiary']}</div>";
            echo "<small class='mb-3'>{$details['order_date']} - {$details['order_time']}</small>";

            // Display order status
            if ($details['order_status'] == "Pending") {
                echo "<h4>Order is Pending.......</h4>";
            } elseif ($details['order_status'] == "Cancelled") {
                echo "<h4>Your Order is cancelled. Admin will refund your money.</h4>";
            } 
              elseif ($details['order_status'] == 'Processing') {
                echo "<h4>Admin is Processing your order. Kindly wait</h4>";
                        }
            else {
                echo "<h4>Order is Delivered.</h4>";
            }
        } else {
            // Display error message if no order details are found or API returned an error
            echo "<div>No Order is made with this NUMBER.</div>";
        }
    }

    // Close the cURL session
    curl_close($ch);
} else {
    // Display error message if the email parameter is not provided
    echo "<div>Telephone is required.</div>";
}
?>



            </div>
          </div>

 <br>
 <hr>
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
          
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <!-- JAVASCRIPT FILES -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/jquery.sticky.js"></script>
        <script src="js/custom.js"></script>

    </body>
</html>