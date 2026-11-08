<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

// Check if an AJAX request is made
if (isset($_GET['action']) && $_GET['action'] == 'getRemainingData') {
    // Example remaining GB value (you could replace this with real data from a database or session)
    $remainingGB = 51.00; // Replace with dynamic calculation if needed
    // Return JSON response
    echo json_encode(['remainingGB' => $remainingGB]);
    exit; // Stop further script execution for AJAX request
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
    }  
}

$errors = array();
$success = array();
if (isset($_POST['submit'])) {
    $reference_id = mysqli_real_escape_string($con, $_POST['reference_id']);
    // Check for empty telephone
    if (empty($reference_id)) {
        $errors['telephone'] = "Order ID is required";
    } else {
        $stmt = $con->prepare("SELECT * FROM console_orders WHERE order_id=?");
        $stmt->bind_param("s", $reference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header("Location: orders.php?reference_id=$reference_id");
            exit(); 
        } else {
            $errors['reference_id'] = "Account not found!";
        }
        $stmt->close();
    }
}
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
  <main id="main" class="main">
    <section class="section dashboard">
       <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-12">
            <div class="row">
            <!-- Recent Sales -->
            <div class="col-12">
                <div class="card recent-sales overflow-auto">
                   <div class="card-body">
                      <h5 class="card-title">Orders</h5>
                      
                    <?php
                    if(isset($_GET['new_success'])){
                        ?>
                        <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['new_success']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['new_console_error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red"> <?php echo $_GET['new_console_error']; ?> </div>
                        <?php
                    }
                    else if(isset($_GET['new_console_transaction_error'])){
                        ?>
                        <div class="alert alert-danger" role="alert" style="border-left:5px solid red"> <?php echo $_GET['new_console_transaction_error']; ?> </div>
                        <?php
                    }
                    ?>
                  
                  	<?php if(count($errors)>0): ?>
                       <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                           <?php foreach($errors as $error): ?>
                               <div><?php echo $error; ?></div>
                           <?php endforeach; ?>
                       </div>
                    <?php endif; ?> 

            <div class="row">
                <div class="col-xl-12 col-md-12 col-12">
   
         <!--     <div class="row gutters mb-2">
				<div class="col-lg-4 col-sm-4 col-12">
					<div class="card border-left-primary shadow ">
						<div class="card-body">
						     <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAgent">Check Status</button>
						</div>
					</div>
				</div>
			</div>
		-->	
								<!-- Row start -->
				<div class="row gutters ">
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							<h6>ORDER INFO</h6>
					<?php
                    if(isset($_GET['success'])){
                        ?>
                    <div class="card">
                      <div class="card-header bg-success text-white">
                        <?php echo $_GET['success'] ?>
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

	<div class="table-container mt-4">
	    <div class="row">
	        <div class="col-sm-8 col-6">
	                    <div class="mb-2">
            <input type="text" id="search" class="form-control" placeholder="Search reference id recipient number">
        </div>
	        </div>
	        <div class="col-sm-4 col-6">
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
                    <th>Reference ID</th>
                    <th>Console</th>
                    <th>Recipient</th>
                    <th>Package</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody id="table-body" style="text-align:left">
                <!-- Dynamic content will be loaded here -->
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
function updateBatteryIcon() {
    let dataUsageText = document.getElementById('data-usage').textContent;
    let [remainingGBText, totalGBText] = dataUsageText.split(" / ");
    let remainingGB = parseFloat(remainingGBText.replace("GB", "").trim());
    let totalGB = parseFloat(totalGBText.replace("GB", "").trim());
    let percentage = (remainingGB / totalGB) * 100;

    let iconHTML;
    if (percentage >= 99) {
        iconHTML = '<i class="fa fa-battery-full" aria-hidden="true"></i>';
    } else if (percentage >= 75) {
        iconHTML = '<i class="fa fa-battery-three-quarters" aria-hidden="true"></i>';
    } else if (percentage >= 50) {
        iconHTML = '<i class="fa fa-battery-half" aria-hidden="true"></i>';
    } else if (percentage >= 25) {
        iconHTML = '<i class="fa fa-battery-quarter" aria-hidden="true"></i>';
    } else {
        iconHTML = '<i class="fa fa-battery-empty" aria-hidden="true"></i>';
    }
    document.getElementById('battery-icon').innerHTML = iconHTML;
}

function checkRecipientNumbers() {
    const recipientNumber = document.getElementById('atrecipient').value;
    const repeatNumber = document.getElementById('atrepeat').value;
    const atMessage = document.getElementById('atmessage');
    const atMessageGreen = document.getElementById('atmessagegreen');
    const atIncorrect = document.getElementById('atincorrect');
    const footerAtSend = document.getElementById('footer_at_send');

    const pattern = /^(027|057|026|056)\d{7}$/;
    if (!pattern.test(recipientNumber)) {
        atIncorrect.style.display = 'block';
        atMessage.style.display = 'none';
        atMessageGreen.style.display = 'none';
        footerAtSend.style.display = 'none';
    } else if (recipientNumber !== repeatNumber) {
        atIncorrect.style.display = 'none';
        atMessage.style.display = 'block';
        atMessageGreen.style.display = 'none';
        footerAtSend.style.display = 'none';
    } else {
        atIncorrect.style.display = 'none';
        atMessage.style.display = 'none';
        atMessageGreen.style.display = 'block';
        footerAtSend.style.display = 'block';
    }
}

// Call updateBatteryIcon to initialize and set it to update at intervals
updateBatteryIcon();
setInterval(updateBatteryIcon, 5000);

// Add event listeners to check recipient numbers on input
document.getElementById('atrecipient').addEventListener('input', checkRecipientNumbers);
document.getElementById('atrepeat').addEventListener('input', checkRecipientNumbers);
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        const recordsPerPage = 10;

        function loadTableData(search = '', page = 1, network = '', status = '', date = '') {
            $('.loading-overlay').show();
            $.ajax({
                url: 'orders_search.php',
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
                                    <td>${order.order_id}</td>
                                    <td style="display:none;">${order.console_id}</td>
                                    <td>${order.console_name}</td>
                                    <td style="display:none;">${order.agent_id}</td>
                                    <td style="display:none;">${order.agent_name}</td>
                                    <td style="display:none;">${order.agent_email}</td>
                                    <td>${order.recipient}</td>
                                    <td>${order.package}GIG</td>
                                    <td>${order.real_date}</td>
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