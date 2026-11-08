<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include 'database/dbconfig.php';
$success = array();
$errors = array();
// === LOGGING FUNCTION ===
function log_error($message, $context = [])
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    $file = $logDir . '/upload_codes.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextText = json_encode($context, JSON_UNESCAPED_SLASHES);
    file_put_contents($file, "[$timestamp] $message | $contextText" . PHP_EOL, FILE_APPEND);
}

$success = "";
$errors = [];

if (isset($_POST['upload'])) {
    $category = mysqli_real_escape_string($con, $_POST['category'] ?? '');

    if (empty($category)) {
        $errors[] = "Select a Target";
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        $errors[] = "Please upload a valid CSV file.";
    }

    if (empty($errors)) {
        $fileName = $_FILES['file']['name'];
        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt !== 'csv') {
            $errors[] = "Only CSV files are allowed.";
        } else {
            $handle = fopen($fileTmp, "r");
            if ($handle === false) {
                $errors[] = "Failed to open CSV file.";
            } else {
                $insertCount = 0;
                $duplicateCount = 0;
                $lineNumber = 0;
                $status = "FRESH";

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $lineNumber++;
                    // Skip empty rows or headers
                    if ($lineNumber === 1 && preg_match('/serial/i', $data[0])) continue;
                    if (count($data) < 2) continue;

                    $serial = trim($data[0]);
                    $pin = trim($data[1]);

                    if (empty($serial) || empty($pin)) continue;

                    // Check duplicate
                    $check = $con->prepare("SELECT id FROM codes WHERE serial_code=? AND pin_code=? AND package=? LIMIT 1");
                    $check->bind_param("sss", $serial, $pin, $category);
                    $check->execute();
                    $checkResult = $check->get_result();

                    if ($checkResult->num_rows > 0) {
                        $duplicateCount++;
                        $check->close();
                        continue;
                    }
                    $check->close();
                    $reference_id = 'REF-' . date('YmdHis') . '-' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
                    // Insert
                    $insert = $con->prepare("INSERT INTO codes (reference_id,serial_code, pin_code, package, status) VALUES (?,?, ?, ?, ?)");
                    $insert->bind_param("sssss", $reference_id, $serial, $pin, $category, $status);
                    if ($insert->execute()) {
                        $insertCount++;
                    } else {
                        log_error("Insert failed", ['serial' => $serial, 'pin' => $pin, 'error' => $insert->error]);
                    }
                    $insert->close();
                }

                fclose($handle);

                $success = "Upload complete: $insertCount inserted, $duplicateCount duplicates skipped.";
                header("Location: load.php?success=" . urlencode($success));
                exit();
            }
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $err) {
            log_error("Upload error", ['error' => $err]);
        }
        header("Location: load.php?error=" . urlencode(implode(', ', $errors)));
        exit();
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
			<?php $page = 'prices' ?>
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
<!-- Modal -->
<div class="modal fade" id="ModalAgent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Upload Codes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
         
      <form action="" method="POST" enctype="multipart/form-data" id="paymentForm">
        <div class="modal-body">
          <div class="mb-3">
            <select id="target" name="category" class="form-control" required>
              <option value="">Select a Target</option>
              <option value="BECE">BECE</option>
              <option value="WASSCE">WASSCE</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="file" class="form-label fw-bold">Upload CSV File</label>
            <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
            <small class="text-muted">Only .csv files are allowed.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="upload" class="btn btn-block btn-primary" id="setBtn">Upload</button>
        </div>
      </form>

    </div>
  </div>
</div>




				<!-- Main container start -->
				<div class="main-container">
                   <h6>Cards Management</h6>
                   
                           <div class="row mb-1">
            <div class="col-lg-4 col-4">
                <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ModalAgent">Add card</button>
            </div>
        </div>
					<!-- Row start -->

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
<style>
    .nav-block {
    display: block;
    width: 50%;  /* Each nav will take up half of the row */
    text-align: center;
    box-sizing: border-box; /* Ensures padding is included in width */
}

.nav-tabs {
    display: flex;
    width: 100%;
}

.nav-tabs .nav-item {
    flex-grow: 1; /* Each nav will expand to fill the available space */
}
</style>					

      		<div class="row gutters">
				<div class="col-lg-12 col-sm-12 col-12">
					<div class="card h-100">
						<div class="card-header">
							<div class="card-title"></div>
						</div>
						<div class="card-body">

 <!-- 🔍 Filters -->
<div class="row mb-3">
  <div class="col-md-6 col-12">
    <input type="text" id="searchInput" class="form-control" placeholder="Search by Serial or PIN">
  </div>
  <div class="col-md-3 col-6">
    <select id="statusFilter" class="form-control">
      <option value="">All Status</option>
      <option value="FRESH">FRESH</option>
      <option value="USED">USED</option>
    </select>
  </div>
  <div class="col-md-3 col-6">
    <select id="packageFilter" class="form-control">
      <option value="">All Types</option>
      <option value="WASSCE">WASSCE</option>
      <option value="BECE">BECE</option>
      <option value="NOVDEC">NOVDEC</option>
    </select>
  </div>
</div>

<!-- 🧾 Table -->
<div class="table-responsive">
  <table class="table table-bordered table-striped text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>S/N</th>
        <th>Type</th>
        <th>Serial</th>
        <th>PIN</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="table-body">
      <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
    </tbody>
  </table>
</div>

<!-- 📄 Pagination -->
<div class="d-flex justify-content-between align-items-center mt-3">
  <button id="prevPage" class="btn btn-secondary btn-sm">« Prev</button>
  <span id="pageInfo" class="fw-bold"></span>
  <button id="nextPage" class="btn btn-secondary btn-sm">Next »</button>
</div>

<script>
$(document).ready(function() {
  let currentPage = 1;
  const recordsPerPage = 10;

  function loadCards() {
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    const packageName = $('#packageFilter').val(); // ✅ fixed ID

    $('#table-body').html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');

    $.ajax({
      url: 'fetch_cards.php',
      method: 'GET',
      data: {
        page: currentPage,
        recordsPerPage: recordsPerPage,
        search: search,
        status: status,
        package: packageName // ✅ fixed variable name
      },
      dataType: 'json',
      success: function(response) {
        $('#table-body').empty();

        if (response.cards.length > 0) {
          $.each(response.cards, function(index, card) {
            const statusBadge = card.status === 'USED'
              ? '<span class="badge bg-danger">USED</span>'
              : '<span class="badge bg-success">FRESH</span>';

            $('#table-body').append(`
              <tr>
                <td>${card.sn}</td>
                <td>${card.package}</td>
                <td>${card.serial}</td>
                <td>${card.pin}</td>
                <td>${statusBadge}</td>
              </tr>
            `);
          });
        } else {
          $('#table-body').html('<tr><td colspan="5" class="text-center text-danger fw-bold">No Records Found</td></tr>');
        }

        // Update pagination info
        $('#pageInfo').text('Page ' + response.meta.currentPage + ' of ' + response.meta.totalPages);
        $('#prevPage').prop('disabled', response.meta.currentPage === 1);
        $('#nextPage').prop('disabled', response.meta.currentPage === response.meta.totalPages);
      },
      error: function() {
        $('#table-body').html('<tr><td colspan="5" class="text-center text-danger fw-bold">Error loading data.</td></tr>');
      }
    });
  }

  // 🔁 Trigger events
  $('#searchInput, #statusFilter, #packageFilter').on('input change', function() {
    currentPage = 1;
    loadCards();
  });

  $('#prevPage').on('click', function() {
    if (currentPage > 1) {
      currentPage--;
      loadCards();
    }
  });

  $('#nextPage').on('click', function() {
    currentPage++;
    loadCards();
  });

  // 🚀 Initial load
  loadCards();
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


		

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--spinner start-->


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