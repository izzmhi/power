<?php
session_name("AGENT_PORTAL_SESSION");
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

    date_default_timezone_set('GMT');
    $roll_times = date('H:i:s A');
    $real_dates = date('l, F d, Y');
    $roll_dates = date('Y-m-d');
    
     $exetime = date('H: i: s: A');
     $real_date = date('l, F d, Y');
     $exeat_dates = date('Y-m-d');
 
    $_SESSION['exeat_dates'] = $exeat_dates;
    $_SESSION['real_date'] = $real_dates;
    $_SESSION['roll_date'] = $roll_dates;

	
        include 'database/dbconfig.php';
        $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
        $selects = "select * from streams where school_id='$school_id'";
        $sqls = mysqli_query($con,$selects);
        $checks=mysqli_num_rows($sqls)>0;
        if($checks)
            { 
            while($rows=mysqli_fetch_array($sqls))
           {
            $_SESSION['year'] = $rows['year'];
            $_SESSION['semester'] = $rows['semester'];
            $_SESSION['session_name'] = $rows['session_name'];
            $_SESSION['stream'] = $rows['stream'];
                }
            }

include'database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selectss = "select * from accounts where user_id = '$user_id' ";
$sqlss=mysqli_query($con,$selectss);
$checkss=mysqli_num_rows($sqlss)>0;
if($checkss)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['email'] = $rows['email'];
       $_SESSION['fullname']= $rows['fullname'];
       $_SESSION['user_type'] = $rows['user_type'];
       $_SESSION['telephone'] = $rows['telephone'];
    }
}

include'database/dbconfig.php';
$school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
$fullname = mysqli_real_escape_string($con, $_SESSION['fullname']);
$selectss = "select * from houses where school_id='$school_id' AND mistress = '$fullname' ";
$sqlss=mysqli_query($con,$selectss);
$checkss=mysqli_num_rows($sqlss)>0;
if($checkss)
{ 
    while($rowss=mysqli_fetch_array($sqlss))
    {
       $_SESSION['house'] = $rowss['house'];
       $_SESSION['mistress']= $rowss['mistress'];
       $_SESSION['assistant'] = $rowss['assistant'];

    }
}


 date_default_timezone_set('GMT');
 $exetime = date('H: i: s: A');
 $real_date = date('l, F d, Y');
 $exeat_dates = date('Y-m-d');
 $_SESSION['exeat_dates'] = $exeat_dates;
 

include 'database/dbconfig.php';
$success  = array();
$errors  = array();
if(isset($_POST['submit'])){
    $user_id     =  mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id   =  mysqli_real_escape_string($con, $_SESSION['school_id']);
	$house       =  mysqli_real_escape_string($con, $_SESSION['house']);
	$assignee      =  mysqli_real_escape_string($con, $_SESSION['fullname']);
	$student_id     =  mysqli_real_escape_string($con, $_POST['student_id']);
	$exeat_date    =  mysqli_real_escape_string($con, $_POST['exeat_date']);
	$exeat_time    =  mysqli_real_escape_string($con, $_POST['exeat_time']);
	$reasons       =  mysqli_real_escape_string($con, $_POST['reasons']);
	$expected_date =  mysqli_real_escape_string($con, $_POST['expected_date']);
	$destination   =  mysqli_real_escape_string($con, $_POST['destination']);
	$year = mysqli_real_escape_string($con, $_SESSION['year']);
	$semester = mysqli_real_escape_string($con, $_SESSION['semester']);
    $session_name = mysqli_real_escape_string($con, $_SESSION['session_name']);
    $moreven = mysqli_real_escape_string($con, $_POST['moreven']);
    
    if (empty($student_id)) {
        $errors['student_id']="Student is required";
    }
	else if (empty($destination)) {
        $errors['destination']="Destination is required";
    }

	else{
		$exeat_date = date('Y-m-d');
		//produce id
	   $date1                     = date('y');
	   $date2                     = date('m');
	   $date3                     = date('d');
	   $date4                     = date('his');
	   $co = bin2hex(random_bytes(2));
	   $cod = bin2hex(random_bytes(2));
	   $exeat_id = $date2.$date3.$co.$date1.$cod.$date4; 
	   
	     // Get parent
        $stmt = $con->prepare("SELECT * FROM students WHERE school_id = ? AND student_id = ?");
        $stmt->bind_param("ss", $school_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
 
                $parent_name = $row['parent_name'];
                $parent_telephone = $row['parent_telephone'];
                $parent_email = $row['parent_email'];
                $student = $row['student'];
  
                $insertquery = "insert into exeats(school_id,user_id,exeat_id,student_id,student,house,destination,real_date,exeat_date,exeat_time,reasons,expected_date,arrival_date,time_in,assignee,year,semester,session_name)values
	            ('$school_id','$user_id','$exeat_id','$student_id','$student','$house','$destination','$real_date','$exeat_date','$exeat_time','$reasons','$expected_date','NOT IN','NOT IN','$assignee','$year','$semester','$session_name')";
	           $iquery = mysqli_query($con,$insertquery);
	            if($iquery){
	      
	      //Message
	 
        $headers = [
        'Host: api.smsonlinegh.com',
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: key 251ab76f086f4c921a5642ac8e7d7f37f72cfe3ccc64588536ada96b9f48c69d'
    ];
	
    // set up the message data
    $messageData = [
        'text'=>"Dear guardian, Your ward, {$student} has been granted an exeat to {$destination} on {$exeat_date}, {$exeat_time}. Thank you.",
        'type'=> 0,	// GSM default
        'sender'=> 'SENGSHS',
        'destinations'=> ["{$parent_telephone}"]
    ];
	
    // initialise cURL

    $ch = curl_init('https://api.smsonlinegh.com/v5/sms/send');
    
    // set cURL optionS
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute for response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close curl
    curl_close($ch);

      $success['program']="Exeat assigned successfully!";
		}
    	else  {  
	   //return false;  
	   $errors['program']="Failed to assign exeat!";
	  } 
              
        } else {
            $errors['passwordrepeat'] = "Student not found!";
        }
        $stmt->close();
    }
  }


include 'database/dbconfig.php';
$success_update  = array();
$errors_update  = array();

if(isset($_POST['update'])){
    $user_id    = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id  = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $exeat_id   = mysqli_real_escape_string($con, $_POST['exeat_id']);
    $student    = mysqli_real_escape_string($con, $_POST['student']);
    $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
    $student    = mysqli_real_escape_string($con, $_POST['student']);
    $inputDate  = mysqli_real_escape_string($con, $_POST['arrival_date']);
    $inputTime  = mysqli_real_escape_string($con, $_POST['arrival_time']);

    if (empty($inputDate)) {
        $errors_update['inputDate']="Arrival Date is required";
    }
     if (empty($inputTime)) {
        $errors_update['inputTime']="Arrival Time is required";
    }

    else{
        
         $select="select * from students where school_id='$school_id' AND student_id='$student_id' ";
        $sql=mysqli_query($con,$select);
        $check = mysqli_num_rows($sql);
        if($check>0){
         while($row=mysqli_fetch_array($sql))
         {
            $parent_name      = $row['parent_name'];
            $parent_telephone = $row['parent_telephone'];
            $parent_email     = $row['parent_email'];
        
        // Create a DateTime object from the input date
   $dateObject = new DateTime($inputDate);
  $arrivalDate = $dateObject->format('l, F j, Y');
  
   // Create a DateTime object from the input time
   $timeObject = new DateTime($inputTime);
   $arrivalTime = $timeObject->format('g:i a');
        
  $update = "update exeats set arrival_date='$arrivalDate', time_in='$arrivalTime' where exeat_id= '$exeat_id' ";
  $uquery = mysqli_query($con,$update);

    if($uquery){

      $headers = [
        'Host: api.smsonlinegh.com',
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: key 251ab76f086f4c921a5642ac8e7d7f37f72cfe3ccc64588536ada96b9f48c69d'
    ];
	
    // set up the message data
    $messageData = [
        'text'=>"Dear guardian, Your ward, {$student} arrived on campus on {$arrivalDate} {$arrivalTime}. Thank you.",
        'type'=> 0,	// GSM default
        'sender'=> 'SENGSHS',
        'destinations'=> ["{$parent_telephone}"]
    ];
	
    // initialise cURL

    $ch = curl_init('https://api.smsonlinegh.com/v5/sms/send');
    
    // set cURL optionS
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute for response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close curl
    curl_close($ch);

      $success_update['produce']="Exeat is updated successfully";
      }
   
   else{
      $errors_update['produce']="Exeat update failed";
       }
      }
        }
        else{
            
        }
     }
  }


include 'database/dbconfig.php';
$success_delete  = array();
$errors_delete  = array();
if(isset($_POST['delete'])){
    $admin_id = mysqli_real_escape_string($con, $_SESSION['admin_id']);
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $exeat_id      = mysqli_real_escape_string($con, $_POST['exeat_id']);
    $student_id      = mysqli_real_escape_string($con, $_POST['student_id']);
    $student      = mysqli_real_escape_string($con, $_POST['student']);
     
        $select="select * from students where school_id='$school_id' AND student_id='$student_id' ";
        $sql=mysqli_query($con,$select);
        $check = mysqli_num_rows($sql);
        if($check>0){
        while($row=mysqli_fetch_array($sql))
         {
            $parent_name = $row['parent_name'];
            $parent_telephone = $row['parent_telephone'];
            $parent_email = $row['parent_email'];
        

        $delete = "delete FROM exeats where student_id = '$student_id' AND exeat_id='$exeat_id'";
        $dquery = mysqli_query($con,$delete);
        if($dquery){
           $success_delete['produce']="Exeat is deleted successfully";
        
              $headers = [
        'Host: api.smsonlinegh.com',
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: key 251ab76f086f4c921a5642ac8e7d7f37f72cfe3ccc64588536ada96b9f48c69d'
    ];
	
    // set up the message data
    $messageData = [
        'text'=>"Dear guardian, Your ward, {$student} exeat has been cancelled. Thank you.",
        'type'=> 0,	// GSM default
        'sender'=> 'SENGSHS',
        'destinations'=> ["{$parent_telephone}"]
    ];
	
    // initialise cURL

    $ch = curl_init('https://api.smsonlinegh.com/v5/sms/send');
    
    // set cURL optionS
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute for response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close curl
    curl_close($ch);

   }
   else{
    $errors_delete['produce']="Exeat deletion failed";
          }
        }
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
		 <div class="modal fade" id="modalUpdateForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">Update an Exeat</h5>
                          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">&times;</span></button>
                        </div>
                            <form action="" method="post" enctype="multipart/form-data">
                                  <div class="modal-body">
                       <div class="row">
                        <div class="col-lg-12">
                      
                            <input type="hidden" class="form-control" name="exeat_id" id="u_id" readonly>
                            <input type="hidden" class="form-control" name="student_id" id="u_student_id" readonly>
                                    <div class="row">
                                         <div class="col-md-12 mb-2">
                                        <label>Student</label>
                                            <div class="form-floating">
                                                <input type="text" class="form-control" name="student" id="u_student" readonly>
                                            </div>
                                       </div>
                                    </div>
                                    
                                     <div class="row">
                                         <div class="col-md-12 mb-2">
                                           <label>Destination</label>
                                             <div class="form-floating">
                                                  <input type="text" class="form-control" name="destination" id="u_destination" readonly>
                                              </div>
                                       </div>
                                    </div>
                                         <div class="row">
                                              <div class="col-md-6 mb-2">
                                           <label>Exeat Date</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="exeat_date" id="u_exeat_date" readonly>
                                           </div>
                                       </div>
                                       <div class="col-md-6 mb-2">
                                           <label>Expected Date</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="expected_date" id="u_ex_date" readonly>
                                           </div>
                                       </div>
                                       </div>
                                       
                                        <div class="row">
                                        <div class="col-md-6 mb-2">
                                           <label>Arrival Date</label>
                                           <div class="form-floating">
                                               <input type="date" class="form-control" name="arrival_date">
                                           </div>
                                       </div>
                                       <div class="col-md-6 mb-2">
                                           <label>Arrival Time</label>
                                           <div class="form-floating">
                                               <input type="time" class="form-control" name="arrival_time" >
                                           </div>
                                       </div>
                                       </div>
                                  </div>
                               </div>
                        </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary" name="update" >Update</button>
                            </div>
                            </form>
                      </div>
                    </div>
                </div>               
            
      <!--Modal-->

      <!--modal Farm-->
              <!--Delete-->  
           <div class="modal fade" id="modalDeleteForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">Delete Exeat</h5>
                          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">&times;</span></button>
                        </div>
                            <form action="" method="post" enctype="multipart/form-data">
                                  <div class="modal-body">
                           
                                    <div class="row">
                        <div class="col-lg-12">
                      
                            <input type="hidden" class="form-control" name="exeat_id" id="d_id">
                            <input type="hidden" class="form-control" name="student_id" id="d_student_id" readonly>
                                       
                                    <div class="row">
                                         <div class="col-md-12 mb-2">
                                           <label>Student</label>
                                             <div class="form-floating">
                                                  <input type="text" class="form-control" name="student" id="d_student" readonly>
                                              </div>
                                       </div>
                                    </div>
                                   
                                    <div class="row">
                                       <div class="col-md-12 mb-2">
                                           <label>Destination</label>
                                           <div class="form-floating">
                                               <textarea class="form-control" name="destination" id="d_destination" readonly></textarea>
                                           </div>
                                       </div>
                                       </div>
                                         <div class="row">
                                    <div class="col-md-6 mb-2">
                                           <label>Exeat</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="exeat_date" id="d_exeat_date" readonly>
                                           </div>
                                       </div>
                                       <div class="col-md-6 mb-2">
                                           <label>Expected Date</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="expected_date" id="d_ex_date" readonly>
                                           </div>
                                       </div>
                                     </div>
                                       
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                           <label>Arrival Date</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="arrival_date" id="d_arrival_date" readonly>
                                           </div>
                                       </div>
                                       <div class="col-md-6 mb-2">
                                           <label>Arrival Time</label>
                                           <div class="form-floating">
                                               <input type="text" class="form-control" name="arrival_time" id="d_arrival_time" readonly>
                                           </div>
                                       </div>
                                       </div>
                                  </div>
                               </div>
                                  </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">No, Close<!/button>
                              <button type="submit" class="btn btn-danger" name="delete" >Yes, Delete!</button>
                            </div>
                            </form>
                      </div>
                    </div>
                </div>  
		
		<!-- new exeat-->
		<div class="modal fade" id="addNewContact" tabindex="-1" role="dialog"aria-labelledby="addNewContactLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addNewContactLabel">Exeat Entry</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<form class="row gutters" method="POST" action="" enctype="multipart/form-data">

							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Student:</label>
									<select name="student_id" class="form-control">
											<option value="">Select Student</option>
											<?php
                                        include 'database/dbconfig.php';
					                    $school_id    = mysqli_real_escape_string($con, $_SESSION['school_id']);
										$house    = mysqli_real_escape_string($con, $_SESSION['house']);
                                        $select = "select * from current_students where school_id='$school_id' AND house='$house' ORDER BY level DESC,student";
                                        $sql = mysqli_query($con,$select);
                                        while($row=mysqli_fetch_array($sql))
					                       {
                                                ?>
									      		<option value="<?php echo $row['student_id'] ?>"><?php echo $row['student']. " - ".$row['level']." ".$row['program']." ".$row['class'] ?></option>
                                          <?php
                                            }
                                        ?>
										</select>
								</div>
							</div>

							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Destination:</label>
									<input type="text" class="form-control" id="contactName" name="destination" required>
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Expected Date:</label>
									<input type="date" class="form-control" id="contactName" name="expected_date" required>
								</div>
							</div>
							<div class="col-sm-12 col-12">
								<div class="form-group">
									<label for="contactName">Reason:</label>
                                    <textarea class="form-control" id="contactName" name="reasons" ></textarea>	
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Exeat Date:</label>
									<input type="text" class="form-control" id="contactName" name="exeat_date" value="<?php echo $real_date ?>" readonly>
								</div>
							</div>
							<div class="col-sm-6 col-12">
								<div class="form-group">
									<label for="contactName">Exeat Time:</label>
									<input type="text" class="form-control" id="contactName" name="exeat_time" value="<?php echo $exetime ?>" readonly>
								</div>
							</div>
					</div>
					<div class="modal-footer custom">
							<div class="left-side">
								<button type="button" class="btn btn-link danger btn-block"	data-dismiss="modal">Cancel</button>
							</div>
								<div class="divider"></div>
							<div class="right-side">
								<button type="submit" name="submit" class="btn btn-link success btn-block">Done</button>
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
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewContact">New Exeat</button>
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
                                    
                                     <?php if(count($success_update)>0): ?>
                                    <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                    <?php foreach($success_update as $succes_u): ?>
                                    <div><?php echo $succes_u; ?></div>
                                    <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?> 

									<?php if(count($errors_update)>0): ?>
                                    <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                    <?php foreach($errors_update as $error_u): ?>
                                    <div><?php echo $error_u; ?></div>
                                    <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?> 
                                    
                                     <?php if(count($success_delete)>0): ?>
                                    <div class="alert alert-success" role="alert" style="border-left:5px solid green">
                                    <?php foreach($success_delete as $succes_d): ?>
                                    <div><?php echo $succes_d; ?></div>
                                    <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?> 

									<?php if(count($errors_delete)>0): ?>
                                    <div class="alert alert-danger" role="alert" style="border-left:5px solid red">
                                    <?php foreach($errors_delete as $error_d): ?>
                                    <div><?php echo $error_d; ?></div>
                                    <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?> 
                            </div>
						</div>
					</div>
					
						<!-- Row start -->
					<div class="row gutters ">
							 <h3>Exeats as at <?php echo $_SESSION['real_date'] ?></h3>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">

								<div class="card-body">
								<div class="table-container ">
						
								<div class="table-responsive">
									<table id="basicExample" class="table custom-table table-sm">
										<thead>
											<tr>
										    <th>S/N</th>
                                            <th>Student</th>
                                            <th>Destination</th>
                                            <th>Exeat Date</th>
                                            <th>Expected Date</th>
                                            <th>Arrival Date</th>
                                            <th>Arrival Time</th>
                                            <th>Action</th>
											</tr>
										</thead>
										<?php
                      include 'database/dbconfig.php';
					  $school_id    = mysqli_real_escape_string($con, $_SESSION['school_id']);
					  $house    = mysqli_real_escape_string($con, $_SESSION['house']);
					  $exeat_dates    = mysqli_real_escape_string($con, $_SESSION['exeat_dates']);
					  
                      $select = "select * from exeats where school_id='$school_id' AND house='$house' ";
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
                                            <td style="display:none;"><?php echo $row['exeat_id'] ?></td>
                                            <td style="display:none;"><?php echo $row['student_id'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['student'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['destination'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['exeat_date'] ?></td>
                                            <td style="text-align:left;"><?php echo $row['expected_date'] ?></td>
                                            <?php
                                            if($row['arrival_date']=="NOT IN"){
                                                ?>
                                                    <td class="bg-danger text-white" style="text-align:left;"><?php echo $row['arrival_date'] ?></td>
                                                <?php
                                            }
                                            else{
                                                ?>
                                                    <td class="bg-success text-white" style="text-align:left;"><?php echo $row['arrival_date'] ?></td>
                                                <?php
                                            }
                                            ?>
											<td style="text-align:left;"><?php echo $row['time_in'] ?></td>
                                            <td style="text-align:left;"> <button type="button" style="font-size:11px;" class="btn btn-info btn-sm updatebtn"><i class="fa fa-pencil-square" aria-hidden="true"></i> Update</button> 
                                            <button type="button" style="font-size:11px;" class="btn btn-danger btn-sm deletebtn"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button></td>                                        </tr>  
                                    </tbody>
                                        <?php
                                     $rowNumber++;
                                    }
                                 }
                                 else{
                                     ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; font-weight:bolder">No exeat Today</td>
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
				
				<!-- Main container end -->

			</div>
			<!-- Page content end -->

		</div>
		<!-- Page wrapper end -->
		
		
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#dateSearch").change(function() {
            var dateSearch = $(this).val();
            // Send an AJAX request to fetch exeat_details based on the selected date
            $.ajax({
                type: "POST",
                url: "exeat_search.php",
                data: { dateSearch: dateSearch },
                success: function(response) {
                    $("#exeat").html(response);
                }
            });
        });
    });
</script>

 <script>
        // Function to update the date and time every second
        function updateLiveDateTime() {
            var liveDateTimeElement = document.getElementById('liveDateTime');
            var currentTime = new Date();
            var hours = currentTime.getHours();
            var minutes = currentTime.getMinutes();
            var seconds = currentTime.getSeconds();
            var ampm = hours >= 12 ? 'pm' : 'am';

            // Convert hours to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12;

            // Add leading zero to minutes and seconds if needed
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            var dateString = currentTime.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            
            // Update the content of the liveDateTimeElement
            liveDateTimeElement.innerHTML = dateString + ', ' + hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        }

        // Update the date and time initially
        updateLiveDateTime();

        // Set interval to update the date and time every second
        setInterval(updateLiveDateTime, 1000);
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
                $('#u_student_id').val(data[1]);
                $('#u_student').val(data[2]);
                $('#u_destination').val(data[3]);
                $('#u_exeat_date').val(data[4]);
                $('#u_ex_date').val(data[5]);
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
                $('#d_destination').val(data[3]);
                $('#d_exeat_date').val(data[4]);
                $('#d_ex_date').val(data[5]);
                $('#d_arrival_date').val(data[6]);
                $('#d_arrival_time').val(data[7]);
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