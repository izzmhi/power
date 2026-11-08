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
            <li class="<?php if($page=='schecker'){echo 'active';} ?>">
                <a href="checker">
                    <i class="fa fa-check-square-o" aria-hidden="true"></i>
                    <span class="menu-text active">Checker</span>
                </a>			
            </li>
            <li class="<?php if($page=='api_switch'){echo 'active';} ?>">
                <a href="api_switch.php">
                    <i class="fa fa-home"></i>
                    <span class="menu-text active">API / Excel Switch</span>
                </a>			
            </li>
            <!--
<li class="nav-item dropdown <?php if($page == 'console') { echo 'active'; } ?>">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-superpowers" aria-hidden="true"></i>
        <span class="menu-text">Console</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right bg-info">
        <li class="dropdown-item">
            <a href="console.php">
                <span class="menu-text">Console</span>
            </a>         
        </li>
        <li class="dropdown-item">
            <a href="console_orders.php">
                <span class="menu-text">Console Orders</span>
            </a>         
        </li>
        <li class="dropdown-item">
            <a href="console_transactions.php">
                <span class="menu-text">Console Transactions</span>
            </a>         
        </li>
        <li class="dropdown-item">
            <a href="console_pricing.php">
                <span class="menu-text">Console Pricing</span>
            </a>         
        </li>
    </ul>      
</li>  -->
            <li class="<?php if($page=='shop'){echo 'active';} ?>">
                <a href="shop.php">
                    <i class="icon-message-circle"></i>
                    <span class="menu-text active">Agents Shops</span>
                </a>			
            </li>
            <li class="<?php if($page=='agents'){echo 'active';} ?>">
                <a href="agents.php">
                    <i class="icon-message-circle"></i>
                    <span class="menu-text active">Agents and Approvals</span>
                </a>			
            </li>
            <li class="<?php if($page=='list'){echo 'active';} ?>">
                <a href="list.php">
                    <i class="icon-message-circle"></i>
                    <span class="menu-text active">My Agents</span>
                </a>			
            </li>
            
            <li class="<?php if($page=='agent_transactions'){echo 'active';} ?>">
                <a href="agent_transactions.php">
                    <i class="fa fa-credit-card-alt" aria-hidden="true"></i>
                    <span class="menu-text active">Agent Transactions</span>
                </a>			
            </li>


            <li class="<?php if($page=='agent_orders'){echo 'active';} ?>">
                <a href="agent_orders.php">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                    <span class="menu-text active">Orders</span>
                </a>			
            </li>
            <li class="<?php if($page=='special'){echo 'active';} ?>">
                <a href="special.php">
                    <i class="icon-message-circle"></i>
                    <span class="menu-text active">Big Time</span>
                </a>			
            </li>
            <li class="<?php if($page=='afa'){echo 'active';} ?>">
                <a href="afa.php">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                    <span class="menu-text active">MTN AFA</span>
                </a>			
            </li>  
            <li class="<?php if($page=='afa'){echo 'active';} ?>">
                <a href="api_mtn_orders.php">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                    <span class="menu-text active">Pages Orders</span>
                </a>			
            </li>  
            <!--
            <li class="<?php if($page=='calls'){echo 'active';} ?>"> -->
            <!--    <a href="call_offers.php"> -->
            <!--        <i class="fa fa-shopping-bag" aria-hidden="true"></i>
             <!--       <span class="menu-text active">AFA Call Mins</span>
             <!--   </a>			
            </li>
            -->

            <li class="<?php if($page=='messaging'){echo 'active';} ?>">								
                <a href="messaging.php">
                    <i class="fa fa-commenting" aria-hidden="true"></i>
                    <span class="menu-text">Messaging</span>
                </a>							
            </li>
            <li class="<?php if($page=='prices'){echo 'active';} ?>">								
                <a href="prices.php">
                    <i class="fa fa-money" aria-hidden="true"></i>
                    <span class="menu-text">Prices</span>
                </a>							
            </li>
            <li class="<?php if($page=='settings'){echo 'active';} ?>">								
                <a href="settings.php">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                    <span class="menu-text">Settings</span>
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
