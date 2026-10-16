<?php
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Orlina');
if (!defined('PAGE_DESC'))  define('PAGE_DESC', 'Luxury, redefined for her. Premium women\'s fashion from Orlina.');
$_navCategories = getCategories();
$_cartCount     = cartCount();
// Fetch logged-in user first name for navbar
$_headerUser = isLoggedIn() ? currentUser() : null;
$_userFirstName = $_headerUser ? h($_headerUser['first_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= h(PAGE_DESC) ?>">
<title><?= h(PAGE_TITLE) ?> — Orlina</title>
<link rel="stylesheet" href="<?= asset('css/google-fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('css/all.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script>window._BASE = '<?= BASE_URL ?>';</script>
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
  <div class="container topbar-inner">
    <span><i class="fas fa-shipping-fast"></i> Free shipping on orders over ৳2000</span>
    <span class="topbar-right">
      <?php if (isLoggedIn()): ?>
        <a href="<?= url('pages/myaccount.php') ?>">My Account</a>
        <a href="<?= url('pages/logout.php') ?>">Logout</a>
      <?php else: ?>
        <a href="<?= url('pages/login.php') ?>">Login</a>
        <a href="<?= url('pages/register.php') ?>">Register</a>
      <?php endif; ?>
    </span>
  </div>
</div>

<!-- Main Header -->
<header class="site-header" id="site-header">
  <div class="container header-inner">

    <!-- Logo (left) -->
    <a href="<?= url('index.php') ?>" class="logo">
      <span class="logo-text">Orlina</span>
      <span class="logo-tagline">Luxury For Her</span>
    </a>

    <!-- Search (centre, grows to fill) -->
    <form class="search-form" action="<?= url('pages/search.php') ?>" method="GET" role="search">
      <input type="text" name="q" placeholder="Search dresses, sarees, jewelry…" value="<?= h($_GET['q'] ?? '') ?>" aria-label="Search products">
      <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
    </form>

    <!-- Right-side Actions -->
    <div class="header-actions">

      <!-- Wishlist -->
      <a href="<?= url('pages/wishlist.php') ?>" class="icon-btn" title="Wishlist" aria-label="Wishlist">
        <i class="far fa-heart"></i>
      </a>

      <!-- Cart — with guest auth guard -->
      <?php if (isLoggedIn()): ?>
      <a href="<?= url('pages/cart.php') ?>" class="icon-btn cart-btn" title="Cart" aria-label="Cart">
        <i class="fas fa-shopping-bag"></i>
        <span class="cart-badge" id="cart-badge"><?= $_cartCount > 0 ? $_cartCount : '' ?></span>
      </a>
      <?php else: ?>
      <a href="<?= url('pages/login.php') ?>?redirect=<?= urlencode(url('pages/cart.php')) ?>&msg=cart"
         class="icon-btn cart-btn" title="Cart (login required)" aria-label="Cart">
        <i class="fas fa-shopping-bag"></i>
        <span class="cart-badge" id="cart-badge"></span>
      </a>
      <?php endif; ?>

      <!-- User: first name only (no avatar) when logged in, Login btn for guests -->
      <?php if (isLoggedIn() && $_headerUser): ?>
      <a href="<?= url('pages/myaccount.php') ?>" class="header-user-name-link" title="My Account" aria-label="My Account">
        <?= $_userFirstName ?>
      </a>
      <?php else: ?>
      <a href="<?= url('pages/login.php') ?>" class="btn btn-primary btn-sm header-login-btn" aria-label="Login">
        <i class="fas fa-user"></i> Login
      </a>
      <?php endif; ?>

      <!-- Admin cog -->
      <?php if (isAdmin()): ?>
      <a href="<?= url('admin/index.php') ?>" class="icon-btn admin-btn" title="Admin Panel" aria-label="Admin">
        <i class="fas fa-cog"></i>
      </a>
      <?php endif; ?>

      <!-- Mobile hamburger -->
      <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="main-nav" id="main-nav" role="navigation" aria-label="Main navigation">
    <div class="container nav-inner">
      <a href="<?= url('index.php') ?>" class="nav-link">Home</a>
      <div class="nav-dropdown">
        <a href="<?= url('pages/shop.php') ?>" class="nav-link">Shop <i class="fas fa-chevron-down"></i></a>
        <div class="dropdown-menu" role="menu">
          <a href="<?= url('pages/shop.php') ?>" role="menuitem">All Products</a>
          <?php foreach ($_navCategories as $cat): ?>
          <a href="<?= url('pages/shop.php') ?>?cat=<?= h($cat['slug']) ?>" role="menuitem"><?= h($cat['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= url('pages/blog.php') ?>" class="nav-link">Blog</a>
      <a href="<?= url('pages/order_tracking.php') ?>" class="nav-link">Track Order</a>
    </div>
  </nav>
</header>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash flash-<?= h($_SESSION['flash']['type']) ?>" role="alert">
  <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= h($_SESSION['flash']['message']) ?>
  <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Close"><i class="fas fa-times"></i></button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<main id="main-content">
