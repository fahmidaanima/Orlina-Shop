<?php
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Orlina');
$categories = getCategories();
$cartCount = cartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<header class="site-header">
  <div class="container header-inner">
    <!-- Logo -->
    <a href="<?= url('index.php') ?>" class="logo">
      <span class="logo-text">Orlina</span>
      <span class="logo-tagline">Luxury For Her</span>
    </a>

    <!-- Search -->
    <form class="search-form" action="<?= url('pages/search.php') ?>" method="GET">
      <input type="text" name="q" placeholder="Search dresses, sarees, jewelry…" value="<?= h($_GET['q'] ?? '') ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <!-- Actions -->
    <div class="header-actions">
      <a href="<?= url('pages/wishlist.php') ?>" class="icon-btn" title="Wishlist">
        <i class="far fa-heart"></i>
      </a>
      <a href="<?= url('pages/cart.php') ?>" class="icon-btn cart-btn" title="Cart">
        <i class="fas fa-shopping-bag"></i>
        <span class="cart-badge" id="cart-badge"><?= $cartCount > 0 ? $cartCount : '' ?></span>
      </a>
      <?php if (isAdmin()): ?>
      <a href="<?= url('admin/index.php') ?>" class="icon-btn admin-btn" title="Admin">
        <i class="fas fa-cog"></i>
      </a>
      <?php endif; ?>
      <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="main-nav" id="main-nav">
    <div class="container nav-inner">
      <a href="<?= url('index.php') ?>" class="nav-link">Home</a>
      <div class="nav-dropdown">
        <a href="<?= url('pages/shop.php') ?>" class="nav-link">Shop <i class="fas fa-chevron-down"></i></a>
        <div class="dropdown-menu">
          <a href="<?= url('pages/shop.php') ?>">All Products</a>
          <?php foreach ($categories as $cat): ?>
          <a href="<?= url('pages/shop.php') ?>?cat=<?= h($cat['slug']) ?>"><?= h($cat['name']) ?></a>
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
<div class="flash flash-<?= h($_SESSION['flash']['type']) ?>">
  <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= h($_SESSION['flash']['message']) ?>
  <button class="flash-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<main>
