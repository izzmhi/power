<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

	<body class="bg-white">


			<div class="page-content">


				<!-- Main container start -->
				<div class="main-container">

					<!-- Row start -->

			
			    <div class="row">
			        <div style="text-align:center; width: 100%;">
                        <h4>SERWAA NYARKO GIRLS' SENIOR HIGH SCHOOL</h4>
                        <img src="img/Sengshs.jpg" alt="SENGSHS" style="width:100px; display: block; margin: 0 auto;"/>
                        <h5><?php echo $_GET['house']; ?></h5>
                    </div>
                    <!-- Centered content end -->
			    <div class="col-lg-12 col-sm-12">
			        
			      <a href="roll_call.php" class="btn btn-info">Back</a>
			        <div class="row bg-white pt-3">
			            
			     
			       <div style="text-align:center; width: 100%;">
                        <h5>Roll Call for <?php echo $_GET['date'] ?> - Morning</h5>
                    </div>
                    <!-- Centered content end -->
			            <div class="col-lg-12 col-sm-12">
			  
			                 <div>
                             <a href="print2.php?house=<?php echo $_SESSION['house']; ?>&date=<?php echo $_SESSION['roll_date']; ?>" class="btn btn-primary">Print</a>


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
					  $house    = mysqli_real_escape_string($con, $_GET['house']);
					  $roll_dates    = mysqli_real_escape_string($con, $_GET['date']);
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
			    
			</div>

	  	 </div>


		<!-- Page wrapper end -->


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