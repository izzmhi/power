<style>
/* Default styles for all screens */
.sticky-footer {
  display: none; /* Hidden by default for non-mobile screens */
}

/* Styles for mobile screens */
@media (max-width: 768px) {
  .sticky-footer {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: linear-gradient(180deg, #001f3f, #003366); /* Gradient background */
    border-top: 1px solid white;
    text-align: center;
    z-index: 1000;
  }

  .sticky-footer .col-3 {
    flex: 1;
    padding: 10px 0;
    font-size: 16px;
    font-weight: bold;
  }

  .footer-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white; /* Better contrast */
    text-decoration: none;
    font-size: 14px;
  }

  .footer-link i {
    font-size: 18px;
    margin-bottom: 2px;
  }

  .menu-text {
    font-size: 12px;
  }
}
</style>

<!-- Sticky Footer -->
<div class="sticky-footer d-md-none">
  <div class="container-fluid">
    <div class="row text-center p-1">
      <div class="col-3">
        <a href="index.php" class="footer-link">
          <i class="fa fa-home"></i>
          <span class="menu-text">Home</span>
        </a>
      </div>
      <div class="col-3">
        <a href="wallet.php" class="footer-link">
          <i class="fa fa-money"></i>
          <span class="menu-text">Wallet</span>
        </a>
      </div>
      
      <div class="col-3">
        <a href="shop.php" class="footer-link">
          <i class="fa fa-shopping-bag"></i>
          <span class="menu-text">Shop</span>
        </a>
      </div>
      
      <div class="col-3">
        <a id="toggle-sidebar" href="#" class="footer-link">
          <i class="fa fa-bars"></i>
          <span class="menu-text text-white">Menu</span>
        </a>
      </div>
    </div>
  </div>
</div>
