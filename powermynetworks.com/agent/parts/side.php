<!-- Sidebar brand start -->
<div class="sidebar-brand" 
     style="background: linear-gradient(135deg, #001f3f, #00509e); text-align: center;">
    <a href="index.php" class="logo">
        <img src="asset/images/Power.png" style="max-height: 50px;" />
    </a>
    <a href="index.php" class="logo-sm">
        <img src="asset/images/Power.png" style="max-height: 35px;" />
    </a>
        <?php
        include '../database/dbconfig.php';
        $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $select = "SELECT * FROM agents WHERE user_id = '$user_id'";
        $sql = mysqli_query($con, $select);
        if (mysqli_num_rows($sql) > 0) {
            while ($row = mysqli_fetch_array($sql)) {
                ?>
                <div class="text-white">Hi! <?php echo htmlspecialchars($row['first_name']) ?></div>
                <?php
            }
        }
    ?>
</div>

<!-- Sidebar brand end -->

<!-- Sidebar content start -->
<div class="sidebar-content">
    <!-- sidebar menu start -->
    <div class="sidebar-menu">
        <ul>
            <li class="<?php if($page=='home'){echo 'active';} ?>">
                <a href="index.php">
                    <i class="fa fa-home"></i>
                    <span class="menu-text">Home</span>
                </a>			
            </li>
            <li class="<?php if($page=='shop'){echo 'active';} ?>">
                <a href="shop.php">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    <span class="menu-text">My Shop</span>
                </a>			
            </li>
            <li class="<?php if($page=='my_orders'){echo 'active';} ?>">
                <a href="my_orders.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">Orders</span>
                </a>			
            </li>
            <!--
            <li class="<?php if($page=='at_orders'){echo 'active';} ?>">
                <a href="at_orders.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">AT Orders</span>
                </a>			
            </li>
            <li class="<?php if($page=='orders'){echo 'active';} ?>">
                <a href="my_orders.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">Telecel Orders</span>
                </a>			
            </li>
            -->
            <li class="<?php if($page=='mtn_special'){echo 'active';} ?>">
                <a href="special_orders_mtn.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">MTN Big Time Orders</span>
                </a>			
            </li>
            
            <li class="<?php if($page=='special'){echo 'active';} ?>">
                <a href="special_orders.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">AT Big Time Orders</span>
                </a>			
            </li>
            <li class="<?php if($page=='wallet'){echo 'active';} ?>">
                <a href="wallet.php">
                    <i class="fa fa-credit-card"></i>
                    <span class="menu-text">Wallet</span>
                </a>			
            </li>
            <li class="<?php if($page=='afa'){echo 'active';} ?>">
                <a href="afa.php">
                    <i class="fa fa-mobile"></i>
                    <span class="menu-text">MTN AFA</span>
                </a>			
            </li>
            <li class="<?php if($page=='transactions'){echo 'active';} ?>">
                <a href="transactions.php">
                    <i class="fa fa-credit-card"></i>
                    <span class="menu-text">Transactions</span>
                </a>			
            </li>
            <li class="<?php if($page=='settings'){echo 'active';} ?>">
                <a href="settings.php">
                    <i class="fa fa-cog"></i>
                    <span class="menu-text">Settings</span>
                </a>			
            </li>
            <li class="<?php if($page=='logout'){echo 'active';} ?>">
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i>
                    <span class="menu-text">Logout</span>
                </a>							
            </li>
        </ul>
    </div>
    <br>
    <br>
    <br>
    <!-- sidebar menu end -->
</div>
<br>
<!-- Sidebar content end --> 
<!-- CSS -->
<style>
.sidebar-content {
    background: linear-gradient(180deg, #001f3f, #003366);
    height: calc(100vh - 80px); /* Subtract brand section height */
    overflow-y: auto;
}

.sidebar-menu ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.sidebar-menu ul li a {
    display: flex;
    align-items: center;
    color: #ffffff;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 15px;
}

.sidebar-menu ul li a i {
    margin-right: 12px;
    font-size: 18px;
    width: 22px;
    text-align: center;
}

.sidebar-menu ul li a:hover,
.sidebar-menu ul li.active a {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 2px;
    color: #ffd700;
}

</style>
