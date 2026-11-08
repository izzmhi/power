<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include'database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$select = "select * from accounts where user_id = '$user_id' ";
$sql=mysqli_query($con,$select);
$check=mysqli_num_rows($sql)>0;
if($check)
{ 
    while($row=mysqli_fetch_array($sql))
    {
       $_SESSION['email'] = $row['email'];
       $_SESSION['fullname']= $row['fullname'];
       $_SESSION['user_type'] = $row['user_type'];
       $_SESSION['telephone'] = $row['telephone'];
    }
}

include'database/dbconfig.php';
$school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
$fullname = mysqli_real_escape_string($con, $_SESSION['fullname']);
$selects = "select * from houses where school_id='$school_id' AND mistress = '$fullname' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['house'] = $rows['house'];
       $_SESSION['mistress']= $rows['mistress'];
       $_SESSION['assistant'] = $rows['assistant'];
    }
}

    date_default_timezone_set('GMT');
    $roll_times = date('H:i:s A');
    $real_dates = date('l, F d, Y');
    $roll_dates = date('Y-m-d');
    
     $_SESSION['real_date'] = $real_dates;
    $_SESSION['roll_date'] = $roll_dates;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$success = array();
$errors = array();

include 'database/dbconfig.php';

if (isset($_POST['submit'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $house = mysqli_real_escape_string($con, $_SESSION['house']);
    $year = mysqli_real_escape_string($con, $_SESSION['year']);
    $semester = mysqli_real_escape_string($con, $_SESSION['semester']);
    $session_name = mysqli_real_escape_string($con, $_SESSION['session_name']);
    $moreven = mysqli_real_escape_string($con, $_POST['moreven']);

 date_default_timezone_set('GMT');
    $roll_time = date('H:i:s A');
    $real_date = date('l, F d, Y');
    $roll_date = date('Y-m-d');
    
    // Fetch all students in the house
    $select_all_students = $con->prepare("SELECT * FROM current_students WHERE school_id = ? AND house = ?");
    $select_all_students->bind_param("ss", $school_id, $house);
    $select_all_students->execute();
    $result_all_students = $select_all_students->get_result();

    // Iterate over each student to record their roll call status
    while ($row = $result_all_students->fetch_assoc()) {
        $student_id = $row['student_id'];
        $student = $row['student'];
        $level = $row['level'];
        $status = isset($_POST['statuses'][$student_id]) ? 'PRESENT' : 'ABSENT';

        // Generate roll_call_id
        $date1 = date('y');
        $date2 = date('m');
        $date3 = date('d');
        $date4 = date('his');
        $co = bin2hex(random_bytes(2));
        $cod = bin2hex(random_bytes(2));
        $roll_call_id = $date2 . $date3 . $co . $date1 . $cod . $date4;

               // Insert roll call record
        $insertquery = $con->prepare("INSERT INTO roll_calls (school_id,user_id,roll_call_id,student_id,student,house,level,roll_status,real_date,roll_date,roll_time,moreven,year,semester,session_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertquery->bind_param("sssssssssssssss", $school_id, $user_id, $roll_call_id, $student_id, $student, $house, $level, $status, $real_date, $roll_date, $roll_time,$moreven,$year,$semester,$session_name);

        if ($insertquery->execute()) {
            $success['program'] = "Roll Call taken successfully!";
        } else {
            $errors['program'] = "Failed to take Roll Call! Error: " . $con->error;
        }
    } 
    
    // Close statements and connection
    $select_all_students->close();
    $insertquery->close();
    $con->close();
}



if (isset($_POST['update'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $house = mysqli_real_escape_string($con, $_SESSION['house']);

    date_default_timezone_set('GMT');
    $roll_time = date('H:i:s A');
    $real_date = date('l, F d, Y');
    $roll_date = date('Y-m-d');

    // Fetch all students in the house
    $select_all_students = $con->prepare("SELECT * FROM students WHERE school_id = ? AND house = ?");
    $select_all_students->bind_param("ss", $school_id, $house);
    $select_all_students->execute();
    $result_all_students = $select_all_students->get_result();

    // Iterate over each student to record their roll call status
    while ($row = $result_all_students->fetch_assoc()) {
        $student_id = $row['student_id'];
        $student = $row['student'];
        $level = $row['level'];
        $status = isset($_POST['statuses'][$student_id]) ? 'PRESENT' : 'ABSENT';

        // Directly update the roll call record
        $update = $con->prepare("UPDATE roll_calls SET roll_status = ? WHERE student_id = ? AND roll_date = ?");
        $update->bind_param("sss", $status, $student_id, $roll_date);
        $query_success = $update->execute();
        $update->close();

        // Check for success or failure
        if ($query_success && $con->affected_rows > 0) {
            $success = true;
        } else {
            $error = true;
        }
    }

    // Close statements and connection
    $select_all_students->close();
    $con->close();
}


if (isset($_POST['print'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $house = mysqli_real_escape_string($con, $_SESSION['house']);
    $date = mysqli_real_escape_string($con, $_POST['date']);

    $dateObject = new DateTime($date);
    $rolls_date = $dateObject->format('Y-m-d');

    // Fetch all students in the house
    $select_all_students = $con->prepare("SELECT * FROM roll_calls WHERE school_id = ? AND house = ? AND roll_date=?");
    $select_all_students->bind_param("sss", $school_id, $house, $rolls_date);
    $select_all_students->execute();
    $result_all_students = $select_all_students->get_result();

    // Iterate over each student to record their roll call status
    while ($row = $result_all_students->fetch_assoc()) {
     $_SESSION['rolls_date'] = $rolls_date;
     header("Location: print.php?date=$rolls_date&house=$house");

    } 
    
    // Close statements and connection
    $select_all_students->close();
    $con->close();
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
				<?php $page = 'management' ?>
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

			<!--Print-->
	<div class="modal fade" id="printContact" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
			    <div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Print Roll Call Data</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row gutters" method="POST" action="" enctype="multipart/form-data">
				<div class="modal-body">

	<div class="row">
	    <div class="col-lg-12">
	        <label class="form-control">Select Date to print</label>
	    </div>
	</div>
	<div class="row">
	    <div class="col-lg-12">
	        <input type="date" name="date" class="form-control" required>
	    </div>
	</div>

				    <hr>
				    <div class="row">
				        <div class="col-lg-6">
				            <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
				        </div>
				        <div class="col-lg-6">
				            <button type="submit" name="print" class="btn btn-success btn-block">Select</button>
				        </div>
				    </div>
				</div>
			 </form>
			</div>
		</div>
	</div>
		
		<!--update-->
		<div class="modal fade" id="updateNewContact" tabindex="-1" role="dialog"aria-labelledby="updateNewContactLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			    <div class="modal-header">
						<h5 class="modal-title" id="updateNewContactLabel">Roll Call Update</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row gutters" method="POST" action="" enctype="multipart/form-data">
				<div class="modal-body">
				    
				    <div class="row">
				        <div class="col-sm-12 col-12">
				            <div class="table-responsive">
            <table id="basicExample" class="table custom-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="display" style="text-align:left">
                    <?php
                    include 'database/dbconfig.php';
                    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
                    $house = mysqli_real_escape_string($con, $_SESSION['house']);
                    $roll_date = $_SESSION['roll_date'];
                    $select = "SELECT * FROM roll_calls WHERE school_id='$school_id' AND house='$house' AND roll_date='$roll_date' ORDER BY level DESC";
                    $sql = mysqli_query($con, $select);
                    while ($row = mysqli_fetch_array($sql)) {
                    ?>
                        <tr>
                            <td style="display:none;"><input type="text" name="roll_call_id" value="<?php echo $row['roll_call_id'] ?>" readonly></td>
                            <td style="display:none;"><?php echo $row['roll_call_id'] ?></td>
                            <td style="display:none;"><?php echo $row['student_id'] ?></td>
                            <td style="text-align:left;"><?php echo $row['student'] ?></td>
                            <td style="text-align:left;"><?php echo $row['level'] ?></td>
                            <td style="text-align:left;">
                         <input type="checkbox" name="statuses[<?php echo $row['student_id'] ?>]" <?php if ($row['roll_status'] == 'PRESENT') echo 'checked'; ?> >
                           </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
				    </div>
				    </div>
				    <hr>
				    <div class="row">
				        <div class="col-lg-6">
				            <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
				        </div>
				        <div class="col-lg-6">
				            <button type="submit" name="update" class="btn btn-success btn-block">Update</button>
				        </div>
				    </div>
				</div>
			 </form>
			</div>
		</div>
	</div>
		
	<!--insert-->
	<div class="modal fade" id="addNewContact" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			    <div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Roll Call Entry</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form class="row gutters" method="POST" action="" enctype="multipart/form-data">
				<div class="modal-body">
				    <div class="row">
				        <div class="col-lg-12">
				         <?php
                           // Enable error reporting
                           ini_set('display_errors', 1);
                           ini_set('display_startup_errors', 1);
                           error_reporting(E_ALL);

                           try {
                               // Get the current date and time
                               $date = new DateTime();
                           
                               // Get the hour component of the date
                               $hour = (int) $date->format('H');
                           
                               // Determine if it's morning or evening
                               if ($hour < 12) {
                                   ?>
                                  <input type="text" name="moreven" value="<?php echo "Morning"; ?>" readonly>
                                   <?php
                                   
                               } else {
                                   ?>
                                     <input type="text" name="moreven" value="<?php echo "Evening"; ?>" readonly>
                                   <?php
                               }
                           } catch (Exception $e) {
                               // Handle any errors
                               echo "Error: " . $e->getMessage();
                           }
                           ?>
				        </div>
				        
				    </div>
				    <hr>

				    <div class="row">
				        <div class="col-sm-12 col-12">
				            <div class="table-responsive">
            <table id="basicExample" class="table custom-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="display" style="text-align:left">
                    <?php
                    include 'database/dbconfig.php';
                    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
                    $house = mysqli_real_escape_string($con, $_SESSION['house']);
                    $select = "SELECT * FROM current_students WHERE school_id='$school_id' AND house='$house' ORDER BY level DESC, student";
                    $sql = mysqli_query($con, $select);
                    while ($row = mysqli_fetch_array($sql)) {
                    ?>
                        <tr>
                            <td style="display:none;"><?php echo $row['student_id'] ?></td>
                            <td style="text-align:left;"><?php echo $row['student'] ?></td>
                            <td style="text-align:left;"><?php echo $row['level'] ?></td>
                            <td style="text-align:left;">
                                <input type="checkbox" name="statuses[<?php echo $row['student_id'] ?>]" >
                            </td>
                           
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
				    </div>
				    </div>
				    <hr>
				    <div class="row">
				        <div class="col-lg-6">
				            <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Cancel</button>
				        </div>
				        <div class="col-lg-6">
				            <button type="submit" name="submit" class="btn btn-success btn-block">Done</button>
				        </div>
				    </div>
				</div>
			 </form>
			</div>
		</div>
	</div>

                 <!-- modal ends-->       

				<!-- Main container start -->
				<div class="main-container">
               
					<!-- Row start -->
					<div class="row gutters">
						<div class="col-sm-12">
							<div class="text-right mb-3">
								<!-- Button trigger modal -->
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewContact">New Roll Call</button>
								<button type="button" class="btn btn-success" data-toggle="modal" data-target="#printContact">Print Roll Call Data</button>
							</div>
						</div>
					</div>
									
					<div class="row gutters">
						<div class="col-xl-12 col-sm-12 col-12">
						    <div class="row px-5 my-2">
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
  
                            </div>
						</div>
					</div>
				
      <h4>Select Date to Search</h4>			
<form class="row gutters" method="POST" id="searchForm" enctype="multipart/form-data">
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-8">
                <input type="date" name="search_date" class="form-control" id="search_date" required>
            </div>
            <div class="col-lg-2">
                <button type="submit" name="search" class="btn btn-success btn-block">Search</button>
            </div>
            <div class="col-lg-2">
                <a href="roll_call.php" class="btn btn-info btn-block">Reset</a>
            </div>
        </div>
    </div>
</form>
				
				
				<div class="row" id="previous">
			    
			    </div>
				
				
			<div class="row" id="current">
			    
			    <div class="col-lg-6 col-sm-12">
			        <div class="row bg-white pt-3">
			            <div class="col-lg-12 col-sm-12">
			                <h5>Roll Call for <?php echo $_SESSION['real_date'] ?> - Morning</h5>
			                 <div>
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateNewContact">Update Today's Roll Call</button>
                            </div>
                            <br>
			                  <div class="card">

								<div class="card-body">
								<div class="table-container ">
						
								<div class="table-responsive">
									<table id="basicExample" class="table custom-table table-sm">
										<thead>
											<tr>
											    <th>S/N</th>
												<th>Student</th>
												<th>Level</th>
												<th>Status</th>
											</tr>
										</thead>
										<?php
                      include 'database/dbconfig.php';
					  $school_id    = mysqli_real_escape_string($con, $_SESSION['school_id']);
					  $house    = mysqli_real_escape_string($con, $_SESSION['house']);
					  $roll_dates    = mysqli_real_escape_string($con, $_SESSION['roll_date']);
                      $select = "select * from roll_calls where school_id='$school_id' AND house='$house' AND roll_date='$roll_dates' AND moreven='Morning' ORDER BY level DESC,student";
                      $sql = mysqli_query($con,$select);
                      $check=mysqli_num_rows($sql)>0;
                                // Initialize a row counter
                                           $rowNumber = 1;
                                           if($check)
                                           { 
                      while($row=mysqli_fetch_array($sql)){
                      ?>
                                     <tbody class="display" style="text-align:left">
                                        <tr>
                                             <th scope="row"><?php echo $rowNumber; ?></th>
                                            <td style="display:none;"><?php echo $row['roll_call_id'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['student'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['level'] ?></td>
											<td style="text-align:left;"><?php echo $row['roll_status'] ?></td>

                                        </tr>  
                                    </tbody>
                                     <?php
                                     $rowNumber++;
                                    }
                                 }
                                 else{
                                     ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; font-weight:bolder">No Roll Call Today</td>
                                    </tr>
                                    <?php
                                 }
                        
                           ?>
									</table>
								</div>
							</div>
						</div>

								</div>
			            </div>
			        </div>
			       
			    </div>
			    
			      <div class="col-lg-6 col-sm-12">
			        <div class="row bg-white pt-3">
			            <div class="col-lg-12 col-sm-12">
			                <h5>Roll Call for <?php echo $_SESSION['real_date'] ?> - Evening</h5>
                            <br>
                            <br>
                            <br>
			                  <div class="card">

								<div class="card-body">
								<div class="table-container ">
						
								<div class="table-responsive">
									<table id="basicExample" class="table custom-table table-sm">
									<table id="basicExample" class="table custom-table">
										<thead>
											<tr>
											    <th>S/N</th>
												<th>Student</th>
												<th>Level</th>
												<th>Status</th>
											</tr>
										</thead>
										<?php
                      include 'database/dbconfig.php';
					  $school_id    = mysqli_real_escape_string($con, $_SESSION['school_id']);
					  $house    = mysqli_real_escape_string($con, $_SESSION['house']);
					  $roll_dates    = mysqli_real_escape_string($con, $_SESSION['roll_date']);
                      $select = "select * from roll_calls where school_id='$school_id' AND house='$house' AND roll_date='$roll_dates' AND moreven='Evening' ORDER BY level DESC,student";
                      $sql = mysqli_query($con,$select);
                      $check=mysqli_num_rows($sql)>0;
                                // Initialize a row counter
                                           $rowNumber = 1;
                                           if($check)
                                           { 
                      while($row=mysqli_fetch_array($sql)){
                      ?>
                                     <tbody class="display" style="text-align:left">
                                        <tr>
                                             <th scope="row"><?php echo $rowNumber; ?></th>
                                            <td style="display:none;"><?php echo $row['roll_call_id'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['student'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['level'] ?></td>
											<td style="text-align:left;"><?php echo $row['roll_status'] ?></td>

                                        </tr>  
                                    </tbody>
                                     <?php
                                     $rowNumber++;
                                    }
                                 }
                                 else{
                                     ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; font-weight:bolder">No Roll Call Today</td>
                                    </tr>
                                    <?php
                                 }
                        
                           ?>
									</table>
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
		
		
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#searchForm").on("submit", function(e) {
        e.preventDefault(); // Prevent the default form submission

        var selectedDate = $("#search_date").val();
        // Send an AJAX request to fetch previous roll calls
        $.ajax({
            type: "POST",
            url: "fetch_previous_roll_call.php",
            data: { sel_date: selectedDate },
            success: function(response) {
                console.log(response);
                $("#previous").html(response);
                 // Hide the #current div
                $("#current").hide();
            }
        });
    });
});
</script>
		
		
		
		
		<!-- Page wrapper end -->
		
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
                $('#u_student_id').val(data[1]);
                $('#u_student').val(data[2]);
                $('#u_room').val(data[3]);
                $('#u_destination').val(data[4]);
                $('#u_exeat_date').val(data[5]);
                $('#u_ex_date').val(data[6]);
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
                $('#d_student_id').val(data[1]);
                $('#d_student').val(data[2]);
                $('#d_room').val(data[3]);
                $('#d_destination').val(data[4]);
                $('#d_exeat_date').val(data[5]);
                $('#d_ex_date').val(data[6]);
                $('#d_arrival_date').val(data[7]);
                $('#d_arrival_time').val(data[8]);
            });
        });
    </script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
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

		<!-- Datepickers -->
		<script src="vendor/datepicker/js/picker.js"></script>
		<script src="vendor/datepicker/js/picker.date.js"></script>
		<script src="vendor/datepicker/js/custom-picker.js"></script>

	    <!-- Data Tables -->
	    <script src="vendor/datatables/dataTables.min.js"></script>
		<script src="vendor/datatables/dataTables.bootstrap.min.js"></script>

		<!-- Custom Data tables -->
		<script src="vendor/datatables/custom/custom-datatables.js"></script>
		<script src="vendor/datatables/custom/fixedHeader.js"></script>

		<!-- Download / CSV / Copy / Print -->
		<script src="vendor/datatables/buttons.min.js"></script>
		<script src="vendor/datatables/jszip.min.js"></script>
		<script src="vendor/datatables/pdfmake.min.js"></script>
		<script src="vendor/datatables/vfs_fonts.js"></script>
		<script src="vendor/datatables/html5.min.js"></script>
		<script src="vendor/datatables/buttons.print.min.js"></script>

		<!-- Main JS -->
		<script src="js/main.js"></script>

	</body>

</html>