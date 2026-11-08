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

$_SESSION['admin_email']="azumahnbalino1@gmail.com";

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
			<?php $page = 'apimtn' ?>
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

					<!-- Row start -->
				<div class="row gutters ">
					<h6>PAGES API MTN Orders</h6>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
							
<style>
/* Simple styling for pagination */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 15px;
}
.pagination button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 6px 12px;
    margin: 2px;
    border-radius: 4px;
    cursor: pointer;
}
.pagination button.active {
    background-color: #0056b3;
}
#searchInput {
    margin-bottom: 10px;
    width: 100%;
    max-width: 300px;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>

<div class="table-container">
    <div class="table-responsive">
        <!-- Search Box -->
        <input type="text" id="searchInput" placeholder="Search orders...">

        <table id="basicExample" class="table custom-table table-sm">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Recipient</th>
                    <th>Network</th>
                    <th>Gig</th>
                    <th>Price</th>
                    <th>Order Date</th>
                    <th>Order Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="table-bodyss" style="text-align:left">
                <?php
                require_once __DIR__ . '/../configs/pmn_provider.php';
                $url = pmn_api_url('https://api.dataworksgh.com/api/admin_response.php');
                
                if (isset($_SESSION['admin_email'])) {
                    $admin_email = $_SESSION['admin_email'];
                    $apiUrl = $url . '?admin_email=' . urlencode($admin_email);
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    
                    $response = curl_exec($ch);
                    
                    if (curl_errno($ch)) {
                        echo 'Error: ' . curl_error($ch);
                    } else {
                        $orderDetails = json_decode($response, true);
                        
                        if ($orderDetails && $orderDetails['status'] == 'success') {
                            $details = $orderDetails['order_details'];
                            
                            $sn = 1;
                            foreach ($details as $detail) {
                                echo "<tr>";
                                echo "<td>{$sn}</td>";
                                echo "<td>{$detail['beneficiary']}</td>";
                                echo "<td>{$detail['network']}</td>";
                                echo "<td>{$detail['gig']} GB</td>";
                                echo "<td>GHS{$detail['price']}</td>";
                                echo "<td>{$detail['order_date']}</td>";
                                echo "<td>{$detail['order_time']}</td>";
                                
                                if ($detail['order_status'] == 'Pending') {
                                    echo "<td style='background-color:orange; color:white'>Order is Pending....</td>";
                                } elseif ($detail['order_status'] == 'Processing') {
                                    echo "<td style='background-color:blue; color:white'>Processing..</td>";
                                } elseif ($detail['order_status'] == 'Cancelled') {
                                    echo "<td style='background-color:red; color:white'>Cancelled</td>";
                                } else {
                                    echo "<td style='background-color:green; color:white'>Delivered</td>";
                                }
                                
                                echo "</tr>";
                                $sn++;
                            }
                        } else {
                            echo "<tr><td colspan='8'>No Order is made.</td></tr>";
                        }
                    }
                    
                    curl_close($ch);
                } else {
                    echo "<tr><td colspan='8'>Email is required.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<script>
// SEARCH FUNCTION
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#basicExample tbody tr");
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// PAGINATION
const rowsPerPage = 10;
const table = document.getElementById('basicExample');
const rows = table.querySelectorAll('tbody tr');
const pagination = document.getElementById('pagination');
let currentPage = 1;

function displayRows() {
    let start = (currentPage - 1) * rowsPerPage;
    let end = start + rowsPerPage;
    rows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });
}

function setupPagination() {
    pagination.innerHTML = '';
    let pageCount = Math.ceil(rows.length / rowsPerPage);
    for (let i = 1; i <= pageCount; i++) {
        let btn = document.createElement('button');
        btn.textContent = i;
        if (i === currentPage) btn.classList.add('active');
        btn.addEventListener('click', () => {
            currentPage = i;
            displayRows();
            setupPagination();
        });
        pagination.appendChild(btn);
    }
}

displayRows();
setupPagination();
</script>


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

        function loadTableData(search = '', page = 1, network = '', status = '', date = '') {
            $('.loading-overlay').show();
            $.ajax({
                url: 'orders_search.php',
                method: 'GET',
                data: {
                    search: search,
                    network: network,
                    status: status,
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
                                    <td style="display:none;">${order.reference_id}</td>
                                    <td>${order.beneficiary}</td>
                                    <td>${order.network}</td>
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
		<!-- Rating JS -->
		<script src="vendor/rating/raty.js"></script>
		<script src="vendor/rating/raty-custom.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>

	</body>

</html>