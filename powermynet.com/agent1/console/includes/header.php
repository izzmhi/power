  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block"><i class="fa fa-superpowers" aria-hidden="true"></i> CONSOLE</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->
    <div class="search-bar">
    </div><!-- End Search Bar -->
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
          					<li class="dropdown">
							    <a href="../" id="userConsole" class="btn btn-info">
                                  <span class="fw-bolder avatar p-2"> <i class="fa fa-money" aria-hidden="true"></i> WALLET</span>
								</a>
								<a href="#" id="userSettings" class="user-settings" data-toggle="dropdown" aria-haspopup="true">
                                  <span class="fw-bolder avatar p-2">
                                      <img src="../../assets/img/profile-user.png" style="width:25px">
                                      <span class="status busy"></span></span>
								</a>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userSettings">
									<div class="header-profile-actions">
										<div class="header-user-profile">
											<div class="header-user">
											    
												<img src="../../assets/img/profile-user.png" style="width:25px; height:25px;">
											</div>
											   <?php
							    include '../../database/dbconfig.php';
                                  $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                  $select = "select * from agents where user_id = '$user_id' ";
                                  $sql=mysqli_query($con,$select);
                                  $check=mysqli_num_rows($sql)>0;
                                  if($check)
                                  { 
                                      while($row=mysqli_fetch_array($sql))
                                      {
                                          ?>
                                          		<h5><?php echo $row['first_name'] ?></h5>
                                       <?php
                                      }
                                  }
                                
                                  ?> 
											<p>Agent</p>
										</div>
										<a href="settings.php"><i class="icon-user1"></i> Settings</a>
										<a href="help.php"><i class="fa fa-info-circle" aria-hidden="true"></i> Help</a>
										<a href="logout.php"><i class="icon-log-out1"></i> Sign Out</a>
									</div>
								</div>
						</li>
        </ul>
    </nav><!-- End Icons Navigation -->
 </header>