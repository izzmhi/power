<div class="container">
    <a class="navbar-brand" href="<?php echo $_SESSION['url'] ?>"><?php echo $_SESSION['shop_name']; ?></a>
    <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        Menu<i class="fas fa-bars"></i> </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="<?php echo $_SESSION['url'] ?>">Home</a></li>
            <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="/my/<?php echo $_SESSION['shop']; ?>/check.php">Status</a></li>
            <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="/my/<?php echo $_SESSION['shop']; ?>/reorder.php">Re-order</a></li>
        </ul>
    </div>
</div>