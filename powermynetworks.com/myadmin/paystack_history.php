<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

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
       $_SESSION['email'] = $rows['email'];

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
/**	
// Set the reference ID for the transaction you want to retrieve
$reference_id = "T700242076228990";  // Replace this with the actual reference you want to search
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference_id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer REDACTED", // Ensure "Bearer" is included before the secret key
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // Decode JSON response
    $responseData = json_decode($response, true);
    
    if ($responseData['status']) {
        echo "<h5>Transaction Details for Reference ID: " . htmlspecialchars($reference_id) . "</h5>";
        $transaction = $responseData['data'];
        
        // Display transaction details in a readable format
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Details</th></tr>";
        echo "<tr><td>Transaction ID</td><td>" . htmlspecialchars($transaction['id']) . "</td></tr>";
        echo "<tr><td>Status</td><td>" . htmlspecialchars($transaction['status']) . "</td></tr>";
        echo "<tr><td>Amount (GHS)</td><td>" . number_format($transaction['amount'] / 100, 2) . "</td></tr>";
        echo "<tr><td>Paid At</td><td>" . htmlspecialchars($transaction['paid_at']) . "</td></tr>";
        echo "<tr><td>Customer Email</td><td>" . htmlspecialchars($transaction['customer']['email']) . "</td></tr>";
        echo "<tr><td>Bank</td><td>" . htmlspecialchars($transaction['authorization']['bank']) . "</td></tr>";
        echo "</table>";
    } else {
        echo "Failed to retrieve transaction: " . htmlspecialchars($responseData['message']);
    }
}
   -->
   **/
?>


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
        "Authorization: Bearer REDACTED", // Correct format for authorization
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // Decode JSON response
    $responseData = json_decode($response, true);
    
    if ($responseData['status']) {
        echo "<h6>Transactions Retrieved Successfully:</h6>";
        
        // Add a search input
        echo "<input type='text' id='searchInput' placeholder='Search for Transaction ID, Reference ID, or Email' onkeyup='filterTable()' style='margin-bottom: 10px; padding: 8px; width: 350px; border: 1px solid #ccc; border-radius: 4px;'>";

        // Start HTML table
        echo "<table border='1' cellpadding='10' cellspacing='0' id='transactionTable' style='width:100%; border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'>
                <th>Transaction ID</th>
                <th>Reference ID</th>
                <th>Status</th>
                <th>Amount (GHS)</th>
                <th>Paid At</th>
                <th>Customer Email</th>
              </tr>";
        
        // Loop through each transaction and print the details in table rows
        foreach ($responseData['data'] as $transaction) {
            // Extract transaction_id from metadata if available
            $metadata = $transaction['metadata'] ?? [];
            $transactionID = $metadata['custom_fields'][5]['value'] ?? 'N/A'; // Assuming the first field is transaction_id

            echo "<tr>";
            echo "<td>" . htmlspecialchars($transactionID) . "</td>"; // Transaction ID
            echo "<td>" . htmlspecialchars($transaction['reference'] ?? 'N/A') . "</td>"; // Paystack Reference ID
            echo "<td>" . htmlspecialchars($transaction['status'] ?? 'N/A') . "</td>"; // Status
            echo "<td>" . number_format(($transaction['amount'] ?? 0) / 100, 2) . "</td>"; // Amount
            echo "<td>" . htmlspecialchars($transaction['paid_at'] ?? 'N/A') . "</td>"; // Paid At
            echo "<td>" . htmlspecialchars($transaction['customer']['email'] ?? 'N/A') . "</td>"; // Customer Email
            echo "</tr>";
        }
        
        // Close HTML table
        echo "</table>";
    } else {
        echo "Failed to retrieve transactions: " . htmlspecialchars($responseData['message']);
    }
}
?>

<!-- JavaScript for Search Filtering -->
<script>
function filterTable() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("transactionTable");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Hide all rows initially

        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Show row if match is found
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