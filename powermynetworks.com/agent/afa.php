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
			<?php $page = 'afa' ?>
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
				    
				    <div class="modal fade" id="ModalAgent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Check Afa Status</h5>
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
    <script>
        $(document).ready(function() {
    $('#setBtn').on('click', function() {
        // Show the spinner
        $('#spinner_new').show();
    });
    });
    </script>
    
    <!--spinner start-->
     <style>
    .modal-body {
    position: relative; /* Allows absolute positioning of spinner within this container */
}

/* Spinner overlay styling */
.loading-overlay {
    display: none; /* Hide by default */
    position: absolute; /* Position it absolutely within the modal body */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay to see the spinner */
    z-index: 999; /* Ensure it is above other content */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner styling */
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

/* Loading text styling */
.loading-text {
    margin-top: 10px;
    font-size: 18px;
    color: #007bff;
}
    </style>
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
				<div class="row gutters mt-2">
					<div class="col-xl-12 col-sm-12 col-12">
						<div class="card">
							<div class="card-body">
						    <h6>Afa Registrations</h6>
	<div class="table-container mt-3">

<style>
/* Search box */
#searchInput {
    margin-bottom: 10px;
    width: 100%;
    max-width: 300px;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Pagination styling */
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
</style>

<div class="table-responsive">
    <!-- Search Input -->
    <input type="text" id="searchInput" placeholder="Search orders...">

    <table id="basicExample" class="table custom-table table-sm">
        <thead>

        </thead>
        <tbody id="table-body" style="text-align:left">

        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination" id="pagination"></div>
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

// PAGINATION FUNCTION
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
			<div class="footer">
    	<?php include 'parts/footer.php'; ?> 
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

        function loadTableData(search = '', page = 1, status = '', date = '') {
            $('.loading-overlay').show();
            $.ajax({
                url: 'afa_search.php',
                method: 'GET',
                data: {
                    search: search,
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
    <td>${order.sn}</td>
    <td style='display:none;'>${order.reference_id}</td>
    <td style='display:none;'>${order.fullname}</td>
    <td style='display:none;'>${order.telephone}</td>
    <td style='display:none;'>${order.register_date}</td>
    <td style='display:none;'>${order.register_status}</td>

    <td>
        ${order.reference_id}<br>
        ${order.fullname}<br>
    </td>
    <td>
        ${order.telephone}<br>
        <strong>${order.register_status}</strong>
    </td>
</tr>
<tr>
    <td colspan="4" style="font-style: italic; font-size: 0.9rem; padding-left: 20px; color: gray;">
        ${order.register_date} &nbsp;&nbsp;: ${order.register_time}
    </td>
</tr>
                            `);
                        });
                        generatePaginationLinks(response.pagination, page);
                    } else {
                        $('#table-body').append('<tr><td colspan="12" style="text-align:center; font-weight:bolder">No one is registered</td></tr>');
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
                    const statusValue = $('#status').val();
                    const dateValue = $('#date').val();
                    loadTableData(searchValue, pageNum, statusValue, dateValue);
                });
            }
        }

        $('#search').on('input', function() {
            const searchValue = $(this).val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, statusValue, dateValue);
        });

        $('#network, #status, #date').on('change', function() {
            const searchValue = $('#search').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            currentPage = 1;
            loadTableData(searchValue, currentPage, statusValue, dateValue);
        });

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                const searchValue = $('#search').val();
                const statusValue = $('#status').val();
                const dateValue = $('#date').val();
                loadTableData(searchValue, currentPage, statusValue, dateValue);
            }
        });

        $('#next-page').on('click', function() {
            currentPage++;
            const searchValue = $('#search').val();
            const statusValue = $('#status').val();
            const dateValue = $('#date').val();
            loadTableData(searchValue, currentPage, statusValue, dateValue);
        });

        // Initial table load
        loadTableData();
    });
</script>
        
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