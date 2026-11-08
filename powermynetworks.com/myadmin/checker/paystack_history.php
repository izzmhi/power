<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
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
			<?php $page = 'agent_transactions' ?>
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
				<div class="row gutters ">
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">
								<div class="card-body">
						    		<h6>Transaction History</h6>

<?php
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer REDACTED",
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "cURL Error #: " . htmlspecialchars($err);
    exit();
}

$responseData = json_decode($response, true);

if (!isset($responseData['status']) || !$responseData['status']) {
    echo "Failed to retrieve transactions: " . htmlspecialchars($responseData['message'] ?? 'Unknown error');
    exit();
}

// ✅ Add a search input field
echo "<h5>WASSCE / BECE Transactions</h5>";
echo "<input type='text' id='searchInput' placeholder='Search Transaction ID, Customer, Telephone or Paid At...' 
      onkeyup='filterTable()' 
      style='margin-bottom: 10px; padding: 8px; width: 350px; border: 1px solid #ccc; border-radius: 4px;'>";

// ✅ Start table
echo "<table border='1' cellpadding='10' cellspacing='0' id='transactionTable' 
      style='width:100%; border-collapse: collapse; font-family: Arial, sans-serif;'>
      <thead style='background-color: #f2f2f2;'>
      <tr>
        <th>Transaction ID</th>
        <th>Customer</th>
        <th>Telephone</th>
        <th>Paid At</th>
        <th>Target</th>
      </tr>
      </thead>
      <tbody>";

$found = false;

foreach ($responseData['data'] as $tx) {
    $reference = htmlspecialchars($tx['reference'] ?? 'N/A');
    $paid_at = htmlspecialchars($tx['paid_at'] ?? 'N/A');
    $customer = htmlspecialchars(($tx['customer']['first_name'] ?? '') . ' ' . ($tx['customer']['last_name'] ?? ''));
    $telephone = '';
    $target = '';

    // ✅ Extract from metadata
    if (!empty($tx['metadata']['custom_fields'])) {
        foreach ($tx['metadata']['custom_fields'] as $field) {
            if ($field['variable_name'] === 'telephone') $telephone = htmlspecialchars($field['value']);
            if ($field['variable_name'] === 'target') $target = strtoupper(trim($field['value']));
        }
    }

    // ✅ Only show WASSCE or BECE
    if ($target === 'WASSCE' || $target === 'BECE') {
        $found = true;
        echo "<tr>
                <td>{$reference}</td>
                <td>{$customer}</td>
                <td>{$telephone}</td>
                <td>{$paid_at}</td>
                <td>{$target}</td>
              </tr>";
    }
}

if (!$found) {
    echo "<tr><td colspan='5' style='text-align:center; color:red; font-weight:bold;'>No WASSCE or BECE transactions found.</td></tr>";
}

echo "</tbody></table>";
?>

<!-- 🔎 JavaScript for Search Filtering -->
<script>
function filterTable() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("transactionTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Hide all rows initially
        var td = tr[i].getElementsByTagName("td");
        for (var j = 0; j < td.length; j++) {
            if (td[j]) {
                var txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Show if any match found
                    break;
                }
            }
        }
    }
}
</script>





							
        <div class="table-container">

    <div class="table-responsive">
        
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
   <!--mtn spinner-->
    <script>
        $(document).ready(function() {
    $('#mtnBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_mtn').show();
    });
    });
    </script>
    
       <script>
        $(document).ready(function() {
    $('#updateBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_update').show();
    });
    });
    </script>
    
       <script>
        $(document).ready(function() {
    $('#deleteBtnW').on('click', function() {
        // Show the spinner
        $('#spinner_delete').show();
    });
    });
    </script>
		
		<script>
	$(document).ready(function() {
    // Hide the agent container initially
    $('#agent-container').hide();

    // Listen for changes on the agent_id select element
    $('#agent_id').change(function() {
        var agentId = $(this).val();

        if (agentId) {
            // Show the spinner while fetching data
            $('#spinner_mtn').show();

            // AJAX request to fetch agent details
            $.ajax({
                url: 'agent_transactions_details.php', // The URL to your PHP script that fetches agent details
                type: 'POST',
                data: { user_id: agentId },
                dataType: 'json',
                success: function(response) {
                    // Hide the spinner after data is fetched
                    $('#spinner_mtn').hide();

                    if (response) {
                        // Populate the input fields with the data
                        $('#agent_name').val(response.agent_name);
                        $('#email').val(response.email);
                        $('#telephone').val(response.telephone);
                        $('#wallet').val(response.wallet);

                        // Show the agent container
                        $('#agent-container').show();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle any errors here
                    console.error('An error occurred while fetching agent data:', error);
                    $('#spinner_mtn').hide();
                }
            });
        } else {
            // Hide the agent container if no agent is selected
            $('#agent-container').hide();
        }
    });
});
				</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
 $(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;

    function loadTableData(search= '', agent='', date = '', page = 1) {
        $('.loading-overlay').show();
        $.ajax({
            url: 'agent_transactions_search.php',
            method: 'GET',
            data: {
                search: search,
                agent: agent,
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
                                <td style="display:none">${order.trans_id}</td>
                                <td style="display:none">${order.reference_id}</td>
                                <td style="display:none">${order.user_id}</td>
                                <td>${order.agent_name}</td>
                                <td>${order.email}</td>
                                <td>${order.amount}</td>
                                <td>${order.wallet}</td>
                                <td>${order.purpose}</td>
                                <td>${order.trans_date}</td>
                                 <td>
                                       ${order.actions}
                                    </td>
                            </tr>
                        `);
                    });
                    generatePaginationLinks(response.pagination, page);
                } else {
                    $('#table-body').append('<tr><td colspan="8" style="text-align:center; font-weight:bolder">No Transaction has taken place</td></tr>');
                    $('#pagination-links').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#table-body').append('<tr><td colspan="8" style="text-align:center; font-weight:bolder">Error loading data</td></tr>');
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
                const agentValue = $('#agent').val();
                const dateValue = $('#date').val();
                loadTableData(searchValue, agentValue, dateValue, pageNum);
            });
        }
    }

    $('#search').on('input', function() {
        currentPage = 1;
        loadTableData($(this).val(), $('#agent').val(), $('#date').val(), currentPage);
    });

    $('#agent').on('change', function() {
        currentPage = 1;
        loadTableData($('#search').val(), $(this).val(), $('#date').val(), currentPage);
    });

    $('#date').on('change', function() {
        currentPage = 1;
        loadTableData($('#search').val(), $('#agent').val(), $(this).val(), currentPage);
    });

    $('#prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadTableData($('#search').val(), $('#agent').val(), $('#date').val(), currentPage);
        }
    });

    $('#next-page').on('click', function() {
        currentPage++;
        loadTableData($('#search').val(), $('#agent').val(), $('#date').val(), currentPage);
    });

    // Initial table load
    loadTableData();
});
</script>

   
   <script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.updatebtn', function() {
        $('#updateModal').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
    $('#d_trans_id').val(data[0]);
                $('#u_reference_id').val(data[1]);
                $('#u_user_id').val(data[2]);
                $('#u_agent_name').val(data[3]);
                $('#u_email').val(data[4]);
                $('#u_amount').val(data[5]);
                $('#u_wallet').val(data[6]);
                $('#u_purpose').val(data[7]);
                $('#u_trans_date').val(data[8]);
            });
        });
    </script>
    
       <script>
$(document).ready(function() {
    // Function to handle update button click
    $(document).on('click', '.deletebtn', function() {
        $('#deleteModal').modal('show');
        const $tr = $(this).closest('tr');
        const data = $tr.children('td').map(function() {
            return $(this).text();
        }).get();
        console.log('Update data:', data);
                $('#d_trans_id').val(data[0]);
                $('#d_reference_id').val(data[1]);
                $('#d_user_id').val(data[2]);
                $('#d_agent_name').val(data[3]);
                $('#d_email').val(data[4]);
                $('#d_amount').val(data[5]);
                $('#d_wallet').val(data[6]);
                $('#d_purpose').val(data[7]);
                $('#d_trans_date').val(data[8]);
            });
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