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
			<?php $page = 'shop' ?>
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

<!-- Modal Big Time Orders -->
<div class="modal fade" id="ModalBigOrders" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="exampleModalLabel">Big Time ORDERS</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" method="POST" enctype="multipart/form-data">
      <!-- Search Box -->
      <div class="p-3">
        <input type="search" id="search_special" class="form-control" placeholder="Search orders...">
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
          <thead class="thead-dark">
            <tr>
              <th>S/N</th>
              <th>Reference</th>
              <th>Customer</th>
              <th>Recipient</th>
              <th>Gig</th>
              <th>Network</th>
              <th>Price</th>
              <th>Order Date</th>
              <th>Order Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="ordersTable">
            <?php
            include '../database/dbconfig.php';
            $agent_id = $_SESSION['user_id'];
            $origin = "SHOP";
            $stmt = $con->prepare("SELECT * FROM special_offers_orders WHERE agent_id = ? AND origin = ? ORDER BY order_date, order_time");
            $stmt->bind_param("ss", $agent_id,$origin);
            $stmt->execute();
            $result = $stmt->get_result();
            $sn = 1;
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr>
                  <td>{$sn}</td>
                  <td>".htmlspecialchars($row['reference_id'])."</td>
                  <td>".htmlspecialchars($row['customer_email'])."</td>
                  <td>".htmlspecialchars($row['beneficiary'])."</td>
                  <td>".htmlspecialchars($row['gig'])." GB</td>
                  <td>".htmlspecialchars($row['network'])."</td>
                  <td>GHS ".htmlspecialchars($row['customer_price'])."</td>
                  <td>".htmlspecialchars($row['order_date'])."</td>
                  <td>".htmlspecialchars($row['order_time'])."</td>
                  <td>".htmlspecialchars($row['order_status'])."</td>
                </tr>";
                $sn++;
              }
            } else {
              echo "<tr><td colspan='11'>No Orders found.</td></tr>";
            }
            $stmt->close();
            ?>
          </tbody>
        </table>
      </div>
   </form>
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center p-3">
        <button id="prevBtn" class="btn btn-secondary btn-sm">Previous</button>
        <span id="pageInfo" class="fw-bold"></span>
        <button id="nextBtn" class="btn btn-secondary btn-sm">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- Pagination + Search Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("ordersTable");
    const rows = table.getElementsByTagName("tr");
    const searchInput = document.getElementById("search_special");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const pageInfo = document.getElementById("pageInfo");

    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = [...rows];

    function displayTable(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        Array.from(filteredRows).forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? "" : "none";
        });

        pageInfo.textContent = `Page ${currentPage} of ${Math.ceil(filteredRows.length / rowsPerPage)}`;
        prevBtn.disabled = (currentPage === 1);
        nextBtn.disabled = (currentPage === Math.ceil(filteredRows.length / rowsPerPage));
    }

    searchInput.addEventListener("keyup", function() {
        const query = this.value.toLowerCase();
        filteredRows = [...rows].filter(row => row.innerText.toLowerCase().includes(query));
        currentPage = 1;
        displayTable(currentPage);
    });

    prevBtn.addEventListener("click", function() {
        if (currentPage > 1) {
            currentPage--;
            displayTable(currentPage);
        }
    });

    nextBtn.addEventListener("click", function() {
        if (currentPage < Math.ceil(filteredRows.length / rowsPerPage)) {
            currentPage++;
            displayTable(currentPage);
        }
    });

    displayTable(currentPage);
});
</script>





		<!-- Main container start -->
<div class="main-container">
<div class="row align-items-center">
  <!-- Dashboard Title -->
<div class="col-5">
  <h4 class="page-title d-flex align-items-center">
    <span class="page-title-icon bg-light border-info text-white me-2">
      <i class="mdi mdi-home"></i>
    </span>
    <div class="dropdown bg-info">
      <a class="nav-link dropdown-toggle text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        Menu
      </a>
      <div class="dropdown-menu bg-info text-white">
        <a class="dropdown-item text-white fs-3" href="shop.php">Home</a>
        <a class="dropdown-item text-white fs-3" href="shop_payouts.php">Shop Payouts</a>
        <a class="dropdown-item text-white fs-3" href="shop_pricing.php">Pricing</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-white fs-3" href="shop_settings.php">Settings</a>
      </div>
    </div>
  </h4>
</div>    
</div> 


					<?php
					if(isset($_GET['success'])){
					    ?>
					    <div class="alert alert-success" role="alert" style="border-left:5px solid green"> <?php echo $_GET['success']; ?> </div>
					    <?php
					}
					else if(isset($_GET['error'])){
					    ?>
					    <div class="alert alert-danger" role="alert" style="border-left:5px solid red"><?php echo $_GET['error']; ?> </div>
					    <?php
					}
					?>
					
					                <?php
                                    include '../database/dbconfig.php';
                                    // Sanitize user input
                                    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                    // Select query
                                    $select = "SELECT * FROM shop WHERE user_id='$user_id'";
                                    $sql = mysqli_query($con, $select);
                                    // Check if there are any rows
                                    if (mysqli_num_rows($sql) > 0) {
                                        // Fetch each row
                                        while ($row = mysqli_fetch_array($sql)) {
                                            if ($row['status'] == "REJECTED") {
                                                ?>
                                        	<h6>Your SHOP Request for <strong class="text-primary"><?php echo $row['shop']; ?></strong> is <strong class="text-danger">REJECTED</strong></h6>
                                                <?php
                                                }
                                                    elseif ($row['status'] == "PENDING"){
                                                        ?>
                                                        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 shadow-sm rounded" role="alert">
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    Your SHOP request for 
                                                                    <strong class="text-primary"><?php echo $row['shop']; ?></strong> is 
                                                                    <strong class="text-info">PENDING</strong>
                                                                </h6>
                                                            </div>
                                                            <i class="bi bi-hourglass-split text-info fs-4"></i>
                                                        </div>
                                                        <?php
                                                    }
                                                    
                                                    elseif ($row['status'] == "APPROVED"){
                                                    ?>
                                                    
<div class="card shadow-sm mb-3">
<div class="card-body">
  <div class="d-flex justify-content-between text-center">
    <button type="button" class="btn btn-primary flex-fill mx-1" data-toggle="modal" data-target="#ModalBigOrders">
      Big Time Orders
    </button>
    <button type="button" class="btn btn-primary flex-fill mx-1" data-toggle="modal" data-target="#AfaOrders">
      AFA Orders
    </button>
  </div>
</div>

<div class="table-container mt-1">

    <div class="row p-1">
        <div class="col-sm-8 col-7 px-2">
            <input type="text" id="search" class="form-control" placeholder="Search reference id beneficiary number">
        </div>
        <div class="col-sm-4 col-3">
            <input type="date" id="date" class="form-control">
        </div>
    </div>

    <!-- Results will load here -->
    <div id="results"></div>

    <!-- Pagination buttons -->
    <div class="mt-2 p-5" id="pagination"></div>
</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadData(page = 1) {
    const search = document.getElementById("search").value;
    const date = document.getElementById("date").value;

    const formData = new FormData();
    formData.append("search", search);
    formData.append("date", date);
    formData.append("page", page);

    fetch("shop_orders_cards.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("results").innerHTML = data;
    });
}

// Live Search
document.getElementById("search").addEventListener("keyup", () => loadData());

// Date Filter
document.getElementById("date").addEventListener("change", () => loadData());

// Load first page initially
loadData();
</script>

<style>
    .pagination-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin-top: 10px;
}

.pagination-wrapper button {
    min-width: 35px;
    padding: 5px 8px;
    font-size: 13px;
    border-radius: 6px;
}

</style>
   
    </div>

                                                    
                                                    <?php
                                                    }
                                                ?>
                                                <?php
                                            }
                                                    
                                        }
                                        else{
                                            ?>
                                            <h6>Nothing to show here</h6>
                                             <p>Request a shop and get it approved.</p>
                                            <?php
                                        }
                                    ?>

<br>
<br>
<br>

			<div class="footer">
    	        <?php include 'parts/footer.php'; ?> 
            </div>
		</div>
	</div>
</div>
	

	<!-- Page wrapper end -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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