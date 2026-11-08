<?php
session_name("AGENT_PORTAL_SESSION"); 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
include'../database/dbconfig.php';
function log_debug(string $message): void {
    error_log('[DEBUG] ' . $message);
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
			<?php $page = 'apiorders' ?>
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

					<!-- Row start -->
		  
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

					<!-- Row start -->
				<div class="row gutters ">
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
								    <h6>MTN Orders</h6>
							
    <style>
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.85);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0,0,0,.1);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>


<div id="loader" class="loading-overlay">
    <div class="spinner"></div>
    <div class="loading-text mt-2">Loading orders...</div>
</div>

<div class="table-container">
    <div class="table-responsive">
        <table class="table custom-table table-sm">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>ID</th>
                    <th>Recipient</th>
                    <th>Network</th>
                    <th>Gig</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <!-- AJAX CONTENT -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    fetch("fetch_mtn.php")
        .then(res => res.text())
        .then(html => {
            document.getElementById("table-body").innerHTML = html;
        })
        .catch(() => {
            document.getElementById("table-body").innerHTML =
                "<tr><td colspan='9'>Failed to load orders</td></tr>";
        })
        .finally(() => {
            document.getElementById("loader").style.display = "none";
        });
});
</script>
							
							
						</div>

					</div>
				</div>
			</div>
			
		</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
</div>

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