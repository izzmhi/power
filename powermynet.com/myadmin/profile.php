<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include 'database/dbconfig.php';
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


include 'database/dbconfig.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$success = array();
$errors = array();

$success_update = array();
$errors_update = array();

$success_delete = array();
$errors_delete = array();

if (isset($_POST['submit'])) {
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $students = mysqli_real_escape_string($con, $_POST['student']);
    $genders = "FEMALE";
    $houses = mysqli_real_escape_string($con, $_SESSION['house']);
    $level = mysqli_real_escape_string($con, $_POST['level']);
    $programs = mysqli_real_escape_string($con, $_POST['program']);
    $classes = mysqli_real_escape_string($con, $_POST['classes']);
    $parent_names = mysqli_real_escape_string($con, $_POST['parent_name']);
    $parent_telephone = mysqli_real_escape_string($con, $_POST['parent_telephone']);
    $parent_email = mysqli_real_escape_string($con, $_POST['parent_email']);
    $towns = mysqli_real_escape_string($con, $_POST['town']);

    if (empty($students)) {
        $errors['student'] = "Student fullname is required";
    }  else if (empty($level)) {
        $errors['level'] = "Student level is required";
    } else if (empty($programs)) {
        $errors['program'] = "Student program is required";
    } else if (empty($classes)) {
        $errors['classes'] = "Student Class is required";
    } else if (empty($parent_telephone)) {
        $errors['parent_telephone'] = "Parent mobile number is required";
    } else {

        $student = strtoupper($students);
        $gender = strtoupper($genders);
        $house = strtoupper($houses);
        $program = strtoupper($programs);
        $parent_name = strtoupper($parent_names);
        $town = strtoupper($towns);

        // produce id
        $date1 = date('y');
        $date2 = date('m');
        $date3 = date('d');
        $date4 = date('his');

        $query = "SELECT * FROM students WHERE school_id='$school_id' AND student='$student' AND house='$house' AND level='$level' AND program='$program' AND class='$classes'";
        $sqlss = mysqli_query($con, $query);
        if (mysqli_num_rows($sqlss) > 0) {
            $errors['farm'] = "This Student already exists in the house";
        } else {
            $co = bin2hex(random_bytes(2));
            $cod = bin2hex(random_bytes(2));
            $student_id = $date2 . $date3 . $co . $date1 . $cod . $date4;

            $insertquery = "INSERT INTO students(school_id, user_id, student_id, student, gender, house, level, program, class, parent_name, parent_telephone, parent_email, town) VALUES
                                      ('$school_id', '$user_id', '$student_id','$student','$gender','$house','$level','$program','$classes','$parent_name', '$parent_telephone', '$parent_email', '$town')";

            $iquery = mysqli_query($con, $insertquery);
            if ($iquery) {
                $success['program'] = "Student added successfully!";
            } else {
                $errors['program'] = "Failed to add a Student! Error: " . mysqli_error($con);
            }
        }
    }
}


if(isset($_POST['update'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $houses = mysqli_real_escape_string($con, $_SESSION['house']);
      $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
    $students = mysqli_real_escape_string($con, $_POST['student']);
    $genders = "FEMALE";
    $level = mysqli_real_escape_string($con, $_POST['level']);
    $programs = mysqli_real_escape_string($con, $_POST['program']);
    $classes = mysqli_real_escape_string($con, $_POST['classes']);
    $parent_names = mysqli_real_escape_string($con, $_POST['parent_name']);
    $parent_telephone = mysqli_real_escape_string($con, $_POST['parent_telephone']);
    $parent_email = mysqli_real_escape_string($con, $_POST['parent_email']);
    $towns = mysqli_real_escape_string($con, $_POST['town']);

     if (empty($students)) {
        $errors['student']="Student name is required";
    }
     if (empty($level)) {
        $errors['level']="Level is required";
    }
     if (empty($programs)) {
        $errors['program']="Program is required";
    }
     if (empty($classes)) {
        $errors['std_class']="Class is required";
    }

     if (empty($parent_telephone)) {
        $errors['telephone']="Guardian telephone is required";
    }
    
    else{
        
        $student = strtoupper($students);
        $gender = strtoupper($genders);
        $house = strtoupper($houses);
        $program = strtoupper($programs);
        $parent_name = strtoupper($parent_names);
        $town = strtoupper($towns);

  $update = "update students set student='$student',level='$level',program='$program',class='$classes',parent_name='$parent_name',parent_telephone='$parent_telephone',parent_email='$parent_email',town='$town' where school_id = '$school_id' AND student_id= '$student_id' ";
  $uquery = mysqli_query($con,$update);

    if($uquery){
      $success_update['produce']="Student info is updated successfully";
      }
   
   else{
      $errors_update['produce']="Student info update failed";
       }
      }
}

if(isset($_POST['delete'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
    $student_id      = mysqli_real_escape_string($con, $_POST['student_id']);
 
  $delete = "delete FROM students where student_id='$student_id' AND school_id='$school_id' ";
  $dquery = mysqli_query($con,$delete);

    if($dquery){
  
         $delete1 = "delete FROM roll_calls where student_id='$student_id' AND school_id='$school_id' ";
         $dquery1 = mysqli_query($con,$delete1);

           if($dquery1){
           $delete2 = "delete FROM exeats where student_id='$student_id' AND school_id='$school_id' ";
           $dquery2 = mysqli_query($con,$delete2);

           if($dquery2){
           $success_delete['produce']="Student is deleted successfully";
          }
          else{
           $errors_delete['produce']="Failed to delete student from exeats system";
           }
          }
          else{
           $errors_delete['produce']="Failed to delete student from roll_calls system";
           }
   }
   else{
    $errors_delete['produce']="Failed to delete student";
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
			<?php $page = 'profile' ?>
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

				    	<div class="row gutters">
						<div class="col-sm-12">
							<div class="text-right mb-3">
								<!-- Button trigger modal -->
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewContact">New Student</button>
							</div>
						</div>
					</div>

					<!-- Row start -->
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

					<!-- Row start -->
					<div class="row gutters ">
							<h4>Students</h4>
						<div class="col-xl-12 col-sm-12 col-12">
							<div class="card">

										<?php
                      include 'database/dbconfig.php';
					  $school_id    = mysqli_real_escape_string($con, $_SESSION['school_id']);
					  $house    = mysqli_real_escape_string($con, $_SESSION['house']);
                      $select = "select * from students where school_id='$school_id' AND house='$house' ";
                      $sql = mysqli_query($con,$select);
                        $check=mysqli_num_rows($sql)>0;
       
                      while($row=mysqli_fetch_array($sql)){
                      ?>
        
                                            <td style="display:none;"><?php echo $row['student_id'] ?></td>
                                  
                                    <?php
                                 }
                        
                           ?>
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
                $('#u_student').val(data[1]);
                $('#u_level').val(data[2]);
                $('#u_program').val(data[3]);
                $('#u_classes').val(data[4]);
                $('#u_parent_name').val(data[5]);
                $('#u_parent_telephone').val(data[6]);
                $('#u_parent_email').val(data[7]);
                $('#u_town').val(data[8]);
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
                $('#d_student').val(data[1]);
                $('#d_level').val(data[2]);
                $('#d_program').val(data[3]);
                $('#d_classes').val(data[4]);
                $('#d_parent_name').val(data[5]);
                $('#d_parent_telephone').val(data[6]);
                $('#d_parent_email').val(data[7]);
                $('#d_town').val(data[8]);
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