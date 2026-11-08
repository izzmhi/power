<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from account_agents where user_id='$user_id' ";
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
    }
}


$success = array();
$errors = array();

if (isset($_POST['submit'])) {

    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $telephone = mysqli_real_escape_string($con, $_POST['telephone']);

    // === Step 1: Get Access Token ===
    $consumer_key = "5lgfXXXXXXXXXXXXXXXXXXXXXXXXslTu";
    $consumer_secret = "REDACTED";
    $token_url = "https://mtn-preprod-preprod.apigee.net/v1/oauth/access_token";
    $primarykey="400cba8326fc4352a3f8ef710938c35b";
    $secondarykey="3bb54ee7fee6456180a2497a9e06a5ff";

    $auth = base64_encode("$consumer_key:$consumer_secret");

    $token_curl = curl_init();
    curl_setopt_array($token_curl, [
        CURLOPT_URL => $token_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic $auth",
            "Content-Type: application/x-www-form-urlencoded"
        ],
        CURLOPT_POSTFIELDS => "grant_type=client_credentials"
    ]);

    $token_response = curl_exec($token_curl);
    $token_error = curl_error($token_curl);
    curl_close($token_curl);

    if ($token_error) {
        echo "Token Error: $token_error";
        exit;
    }

    $token_data = json_decode($token_response, true);
    $access_token = $token_data['access_token'];

    // === Step 2: Make Withdrawal Request ===
    $withdraw_url = "https://preprod.mtn.com/v1/withdraw";

    // Prepare unique IDs
    $correlatorId = uniqid();
    $transactionId = uniqid();

    $post_data = [
        "correlatorId" => $correlatorId,
        "customerId" => $telephone,
        "resource" => "wallet",
        "transactionRequestDate" => gmdate("Y-m-d\TH:i:s.000\Z"),
        "callingSystem" => "YourSystem",
        "targetSystem" => "MTN",
        "description" => "Withdrawal Request by $telephone",
        "authorizationCode" => "xxxxxxxxxxxxxxxxx", // Replace with valid code
        "amount" => [
            "amount" => (float)$amount,
            "units" => "GHS" // Or "XOF" or applicable currency
        ],
        "status" => "Pending",
        "externalReference" => "EXT-" . $transactionId,
        "additionalInformation" => [
            [
                "name" => "Purpose",
                "description" => "Withdrawal"
            ]
        ]
    ];

    $withdraw_curl = curl_init();
    curl_setopt_array($withdraw_curl, [
        CURLOPT_URL => $withdraw_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $access_token",
            "Content-Type: application/json",
            "X-API-Key: your_api_key_here", // Replace with actual API key
            "transactionId: Tylerioaj34593865"
        ],
        CURLOPT_POSTFIELDS => json_encode($post_data)
    ]);

    $withdraw_response = curl_exec($withdraw_curl);
    $withdraw_error = curl_error($withdraw_curl);
    curl_close($withdraw_curl);

    if ($withdraw_error) {
        echo "Withdraw Error: $withdraw_error";
    } else {
        echo $withdraw_response;
    }
}



?>

<!doctype html>

<html lang="en">
	<head>
		<!--Head-->
		<?php include 'parts/head.php'; ?> 
	</head>
	<body>
		<!-- Page wrapper start -->
		<div class="page-wrapper">
			<!-- Sidebar wrapper start -->
			<nav id="sidebar" class="sidebar-wrapper">
			<!-- side-->
			<?php $page = 'settings' ?>
			<?php include 'parts/side.php'; ?> 
			</nav>
			<!-- Sidebar wrapper end -->
			<!-- Page content start  -->
			<div class="page-content">
				<!-- Header start -->
				<header class="header">
					<!-- header-->
					<?php include 'parts/header.php'; ?> 
				</header>
				<!-- Header end -->
				<!-- Page header start -->
				<div class="page-header">
						<!-- Nav-->
						<?php include 'parts/nav.php'; ?> 
				</div>
				<!-- Page header end -->
				<!-- Main container start -->
				<div class="main-container">

                                <?php if(count($success)>0): ?>
                                   <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                   <?php foreach($success as $succes): ?>
                                   <div><?php echo $succes; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 

								<?php if(count($errors)>0): ?>
                                   <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                   <?php foreach($errors as $error): ?>
                                   <div><?php echo $error; ?></div>
                                   <?php endforeach; ?>
                                   </div>
                                <?php endif; ?> 
                                
                                <?php
                                if (isset($_GET['success'])) {
                                    echo "<p style='color: green;'>".$_GET['success']."</p>";
                                }
                                
                                if (isset($_GET['error'])) {
                                    echo "<p style='color: red;'>".$_GET['error']."</p>";
                                }
                                ?>
                                

				<div class="row gutters">

						<div class="col-lg-6 col-sm-12 col-12">
							<div class="card h-100">
								<div class="card-header">
									<div class="card-title">Send Data</div>
								</div>
								<div class="card-body">
								    
								<form action="send_balance.php" method="POST" enctype="multipart/form-data">
                                    <div class="row gutters">
									
										<div class="col-sm-12">
										    <div class="spinner" id="spinner_info" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
											<div class="text-right">
												<button type="submit" name="submit" class="btn btn-success" id="infoBtn">Check balance</button>
											</div>
										</div>
									</div>
                               </form>
	
                             <form action="send_processing.php" method="POST" enctype="multipart/form-data">
                                    <div class="row gutters">
										<div class="col-sm-12 col-12">
											<div class="form-group">
												<label for="fullName">Number</label>
												<input type="text" class="form-control" name="telephone" >
											</div>
											<div class="form-group">
												<label for="fullName">Amount</label>
												<input type="text" class="form-control" name="amount" >
											</div>

										</div>
									
										<div class="col-sm-12">
										    <div class="spinner" id="spinner_info" style="display: none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Please Wait...
                                            </div>
											<div class="text-right">
												<button type="submit" name="submit" class="btn btn-success" id="infoBtn">Withdraw</button>
											</div>
										</div>
									</div>
                               </form>
   
								</div>
							</div>
						</div>
						
					</div>

			</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		
		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
	</body>
</html>