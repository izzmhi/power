      
                  <div class="toggle-btns">
						<a id="toggle-sidebar" href="#"><i class="icon-list"></i></a>
						<a id="pin-sidebar" href="#"><i class="icon-list"></i></a>
					</div>
					<div>
			
					</div>
					<div class="">
						<!-- Custom search start -->
						<div class="custom-search">
							
						</div>
						<!-- Custom search end -->

						<!-- Header actions start -->
						<ul class="">
							
							<li class="dropdown">
							    <a href="paystack_history.php" class="btn btn-info" >Paystack</a>
								<a href="#" id="userSettings" class="user-settings" data-toggle="dropdown" aria-haspopup="true">
		
                                  <span class="fw-bolder avatar p-2">
                                      <img src="../img/profile-user.png" style="width:25px">
                                      <span class="status busy"></span></span>
							
								</a>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userSettings">
									<div class="header-profile-actions">
										<div class="header-user-profile">
											<div class="header-user">
											    
												<img src="../img/profile-user.png" style="width:25px; height:25px;">
											</div>
											   <?php
							    include '../database/dbconfig.php';
                                  $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
                                 $select = "select * from admin where user_id='$user_id'";
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
											<p>Administrator</p>
										</div>
										<a href="settings.php"><i class="icon-user1"></i> Settings</a>
										<a href="logout.php"><i class="icon-log-out1"></i> Sign Out</a>
									</div>
								</div>
							</li>
						</ul>
						<!-- Header actions end -->
					</div>