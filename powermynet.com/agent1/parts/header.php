<div class="header-actions" style="display: flex; align-items: center; width: 100%;">
    <!-- LEFT SIDE: Profile or Logo (optional) -->
    <div class="header-left">
        <!-- You can add a logo or text here -->
    </div>

    <!-- RIGHT SIDE: Icons and User Dropdown -->
    <div style="display: flex; align-items: center; gap: 15px; margin-left: auto;">
        <i class="fa fa-commenting fa-2x text-info" data-toggle="modal" data-target="#noticeModal"></i>
        
        <div class="dropdown">
            <a href="#" id="userSettings" class="user-settings" data-toggle="dropdown" aria-haspopup="true">
                <span class="fw-bolder avatar p-2">
                    <img src="../asset/images/profile-user.png" style="width:25px">
                    <span class="status busy"></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userSettings">
                <div class="header-profile-actions">
                    <div class="header-user-profile">
                        <div class="header-user">
                            <img src="../asset/images/profile-user.png" style="width:25px; height:25px;">
                        </div>
                        <?php
                        $sql = mysqli_query($con, $select);
                        if (mysqli_num_rows($sql) > 0) {
                            while ($row = mysqli_fetch_array($sql)) {
                                echo "<h5>" . htmlspecialchars($row['first_name']) . "</h5>";
                            }
                        }
                        ?>
                        <p>Agent</p>
                    </div>
                    <a href="settings.php"><i class="icon-user1"></i> Settings</a>
                    <a href="help.php"><i class="fa fa-info-circle"></i> Help</a>
                    <a href="logout.php"><i class="icon-log-out1"></i> Sign Out</a>
                </div>
            </div>
        </div>

        <a href="logout.php"><i class="fa fa-sign-out fa-2x text-info"></i></a>
    </div>
</div>
