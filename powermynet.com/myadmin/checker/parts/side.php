<div class="sidebar-brand">
    <a href="index.php" class="logo">
        <img src="img/Power.png" />
    </a>
    <a href="index.php" class="logo-sm">
        <img src="img/Power.png" />
    </a>
</div>
<!-- Sidebar brand end -->
<!-- Sidebar content start -->
<div class="sidebar-content" style="max-height: 100vh; overflow-y: auto;">
    <!-- sidebar menu start -->
    <div class="sidebar-menu">
        <ul>
            <li class="<?php if($page=='home'){echo 'active';} ?>">
                <a href="index.php">
                    <i class="fa fa-home"></i>
                    <span class="menu-text active">Home</span>
                </a>			
            </li>
            <li class="<?php if($page=='agent_orders'){echo 'active';} ?>">
                <a href="orders.php">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                    <span class="menu-text active">Orders</span>
                </a>			
            </li>
            <li class="<?php if($page=='prices'){echo 'active';} ?>">								
                <a href="load.php">
                    <i class="fa fa-money" aria-hidden="true"></i>
                    <span class="menu-text">Load/Cards</span>
                </a>							
            </li>
            <li class="<?php if($page=='logout'){echo 'active';} ?>">								
                <a href="logout.php">
                    <i class="fa fa-sign-out" aria-hidden="true"></i>
                    <span class="menu-text">Logout</span>
                </a>							
            </li>
        </ul>
    </div>
    <!-- sidebar menu end -->
</div>
<!-- Sidebar content end -->
