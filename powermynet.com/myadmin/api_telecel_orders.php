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
$selects = "select * from admin where user_id='$user_id'";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];

    }
}

$_SESSION['admin_email']="owusuebenezer377@gmail.com";


if (isset($_POST['submit'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    // Check for empty telephone
    if (empty($reference_id)) {
        $errors['telephone'] = "Order ID is required";
    } else {
        $stmt = $con->prepare("SELECT * FROM orders WHERE reference_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header("Location: api_telecel_orders.php?reference_id=$reference_id");
            exit(); 
        } else {
            $errors['reference_id'] = "Account not found!";
        }
        $stmt->close();
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
			<?php $page = 'apitelecel' ?>
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
				    
				      <!-- Modal -->
<div class="modal fade" id="ModalAgent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Check Order Status</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
     <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
    <div class="modal-body">
                <div class="row gutters">
                    
                    <label>Order ID</label>
                      <input type="text" class="form-control" placeholder="Order ID" aria-label="Email" aria-describedby="email-addon" name="reference_id">
                
				</div>

        <!-- Spinner -->
        <div class="loading-overlay" id="spinner_mtn" style="display:none;">
            <div class="spinner"></div>
            <div class="loading-text">Please Wait...</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="submit" class="btn btn-block btn-primary" id="setBtn">Check Now</button>
    </div>
</form>

    </div>
  </div>
</div>

 <div class="row gutters mb-2">
					<div class="col-lg-4 col-sm-4 col-6">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
						     <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAgent">Check Status</button>
						</div>
					</div>
				</div>

			</div>

					<!-- Row start -->

					<!-- Row start -->
				<div class="row gutters ">
					<h4>Orders</h4>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
<?php
if (isset($_GET['reference_id'])) {

$errors = [];
$success = [];
$reference_id = $_GET['reference_id'];
include '../database/dbconfig.php';

// Set the API endpoint URL for fetching transaction status
$url = 'https://value4moni.com/api/v1/transactionstatus';

// Data to send to the API (in this case API_Key and Reference)
$data = array(
    "API_Key" => "REDACTED",
    "Reference" => $reference_id
);

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the content type to JSON
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send the data as JSON

// Execute the cURL request
$response = curl_exec($ch);

// Check if any error occurred
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Decode the API response
    $responseData = json_decode($response, true);

    // Log the raw API response for debugging (optional)
    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";

    // Check if the response contains the status and other transaction details
    if (isset($responseData['status'])) {
        // Display the status and other transaction details in a <div>
        
            include '../database/dbconfig.php';
            $ref_id = $reference_id;
            $select = "SELECT * FROM orders WHERE reference_id='$ref_id'";
            $sql = mysqli_query($con, $select);
            $check = mysqli_num_rows($sql) > 0;
            if ($check) {
                while ($row = mysqli_fetch_array($sql)) {
            ?>
            <div>Number: <?php echo $row['beneficiary']; ?></div>
            <div>Package: <?php echo $row['gig']; ?>Gig</div>
            <div>Price: GHS<?php echo $row['price']; ?></div>
            <div>Order Date: <?php echo $row['order_date']; ?> - <div>Time: <?php echo $row['order_time']; ?></div></div>
            <?php
                }
            }
        echo "<div class='mb-3'><strong>Order ID ".$reference_id." is </strong> " . $responseData['status'] . "</div>";
    } else {
        // Handle case where the API doesn't return the expected data
        echo "<div>Transaction status not found for the reference ID.</div>";
    }
}
// Close the cURL session
curl_close($ch);
}
else{
   ?>
     <div>Click to Check Status ID.</div>
   <?php
}


?>

<br>
<br>

	<div class="table-container">
	    
	<div class="row">
	    <div class="col-lg-12 col-12">
	        <div class="mb-2">
               <input type="text" id="search" class="form-control" placeholder="Search reference id, agent name, beneficiary number">
           </div>
	    </div>
	</div>
	 
	<div class="row">
	   <div class="col-lg-4 col-6">
            <div class="mb-2">
                <input type="date" id="date" class="form-control">
            </div>
	   </div>
	</div>

    <div class="table-responsive">
        <table id="basicExample" class="table custom-table table-sm">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Reference</th>
                <th>Network</th>
                <th>Recipient</th>
                <th>Gig</th>
                <th>Price</th>
                <th>Order Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody id="table-body">
                <!-- Data will be populated here -->
            </tbody>
        </table>
        <div id="pagination-controls" class="mt-2">
            <button class="btn btn-info" id="prev-page">Previous</button>
            <span id="pagination-links"></span>
            <button class="btn btn-info" id="next-page">Next</button>
        </div>
    </div>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>




<!--spinner start-->
    <style>
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0, 0, 0, 0.1);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .loading-text {
            margin-top: 10px;
            font-size: 18px;
            color: #007bff;
        }
    </style>
							
							
						</div>

					</div>
				</div>
			</div>
			
		</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        const recordsPerPage = 10;

        function loadTableData(search = '', page = 1, date = '') {
            $('.loading-overlay').show();
            $.ajax({
                url: 'api_orders_search.php',
                method: 'GET',
                data: {
                    search: search,
                    date: date,
                    page: page,
                    recordsPerPage: recordsPerPage
                },
                dataType: 'json',
                success: function(response) {
                    $('#table-body').empty();
                    if (response.orders.length > 0) {
                        $.each(response.orders, function(index, order) {
                            $('#table-body').append(`
                                <tr>
                                    <th scope="row">${order.sn}</th>
                                    <td style="display:none;">${order.order_id}</td>
                                    <td>${order.reference_id}</td>
                                    <td>${order.network}</td>
                                    <td>${order.beneficiary}</td>
                                    <td>${order.gig}GIG</td>
                                    <td>GHS${order.price}</td>
                                    <td>${order.real_date}</td>
                                    <td>${order.order_time}</td>
                                    <td>${order.order_status}</td>
                                </tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination, page);
                    } else {
                        $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">No Order is Available</td></tr>');
                        $('#pagination-links').empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
                },
                complete: function() {
                    $('.loading-overlay').hide();
                }
            });
        }

        function generatePaginationLinks(pagination, currentPage) {
            $('#pagination-links').empty();
            if (pagination !== '') {
                $('#pagination-links').html(pagination);
                $('.pagination-link').on('click', function(e) {
                    e.preventDefault();
                    const pageNum = $(this).data('page');
                    const searchValue = $('#search').val();
                    const networkValue = $('#network').val();
                    const statusValue = $('#status').val();
                    const dateValue = $('#date').val();
                    loadTableData(searchValue, pageNum, networkValue, statusValue, dateValue);
                });
            }
        }

        $('#search').on('input', function() {
            const searchValue = $(this).val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        $('#network, #status, #date').on('change', function() {
            const searchValue = $('#search').val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                const searchValue = $('#search').val();
                const networkValue = $('#network').val();
                const statusValue = $('#status').val();
                const dateValue = $('#date').val();
                loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            const searchValue = $('#search').val();
            const networkValue = $('#network').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            loadTableData(searchValue, currentPage, networkValue, statusValue, dateValue);
        });

        // Initial table load
        loadTableData();
    });
</script>
        
    
		
    <script>
        $(document).ready(function() {
            $(".std_program").change(function() {
                var selectedProgram = $(this).val();

                // Send an AJAX request to fetch cities based on the selected region
                $.ajax({
                    type: "POST",
                    url: "fetch_class.php",
                    data: {program: selectedProgram},
                    success: function(response) {
                         console.log(response);
                        $(".classes").html(response);
                    }
                });
            });
            
                $("#std_house").change(function() {
                var selectedHouse = $(this).val();

                // Send an AJAX request to fetch cities based on the selected region
                $.ajax({
                    type: "POST",
                    url: "fetch_rooms.php",
                    data: {house: selectedHouse},
                    success: function(response) {
                         console.log(response);
                        $("#drooms").html(response);
                    }
                });
            });
        });
    </script>

<script>
        $(document).ready(function () {
            $('.updatebtn').on('click', function () {
                $('#modalUpdateForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#u_id').val(data[0]);
                 $('#u_rid').val(data[1]);
                $('#u_first').val(data[2]);
                $('#u_last').val(data[3]);
                $('#u_email').val(data[4]);
                $('#u_telephone').val(data[5]);
                $('#u_beneficiary').val(data[6]);
                $('#u_network').val(data[7]);
                $('#u_gig').val(data[8]);
                $('#u_price').val(data[9]);
                $('#u_status').val(data[11]);
            });
        });
    </script>

  <script>
        $(document).ready(function () {
            $('.deletebtn').on('click', function () {
                $('#modalDeleteForm').modal('show');
                $tr = $(this).closest('tr');
                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();
                console.log(data);
                $('#d_id').val(data[0]);
                 $('#d_rid').val(data[1]);
                $('#d_first').val(data[2]);
                $('#d_last').val(data[3]);
                $('#d_email').val(data[4]);
                $('#d_telephone').val(data[5]);
                $('#d_beneficiary').val(data[6]);
                $('#d_network').val(data[7]);
                $('#d_gig').val(data[8]);
                $('#d_price').val(data[9]);
                $('#d_status').val(data[11]);
            });
        });
    </script>
    
     <script>
        $(document).ready(function () {
            $('.ad').on('click', function () {
                $('#exampleModal').modal('show');
            });
        });
    </script>
    
    <script>
    document.body.style.cursor = 'wait'; // change cursor to wait
window.addEventListener('load', () => {
  document.body.style.cursor = 'default'; // change cursor back to default
});
 </script>
      <script>
        $(document).ready(function () {
            $('.add').on('click', function () {
                $('#exampleModal').modal('show');
       
            });
        });
    </script>

  <script>
 
      $("#ad").on("click", function(){
       document.querySelectorAll('*').forEach(item => {
      item.style.cursor = 'wait';
      })
      
        });
</script>

		<!-- Required jQuery first, then Bootstrap Bundle JS -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/moment.js"></script>

		<!-- Slimscroll JS -->
		<script src="vendor/slimscroll/slimscroll.min.js"></script>
		<script src="vendor/slimscroll/custom-scrollbar.js"></script>

		<!-- Daterange -->
		<script src="vendor/daterange/daterange.js"></script>
		<script src="vendor/daterange/custom-daterange.js"></script>

		<!-- Chartist JS -->
		<script src="vendor/chartist/js/chartist.min.js"></script>
		<script src="vendor/chartist/js/chartist-tooltip.js"></script>
		<script src="vendor/chartist/js/custom/threshold/threshold.js"></script>
		<script src="vendor/chartist/js/custom/bar/bar-chart-orders.js"></script>

		<!-- jVector Maps -->
		<script src="vendor/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
		<script src="vendor/jvectormap/world-mill-en.js"></script>
		<script src="vendor/jvectormap/gdp-data.js"></script>
		<script src="vendor/jvectormap/custom/world-map-markers2.js"></script>

		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>

		<!-- Main JS -->
		<script src="js/main.js"></script>

	</body>

</html>