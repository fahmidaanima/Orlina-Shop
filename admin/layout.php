<?php
if (!defined('ADMIN_PAGE')) define('ADMIN_PAGE', 'Dashboard');
require_once __DIR__ . '/../includes/functions.php';

// ── SUPER ADMIN GUARD ──────────────────────────────────────────
// ONLY super_admin role can access any admin panel page.
// Members, admin role users, and guests are all blocked.
$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['role'] !== 'super_admin') {
    // Destroy any stale session and redirect to login
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Access denied. This area is restricted to Super Administrators only.'
    ];
    session_regenerate_id(true);
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= ADMIN_PAGE ?> — Orlina Super Admin</title>
<link rel="stylesheet" href="<?= url('assets/css/google-fonts.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/all.min.css') ?>">
<link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__.'/admin.css') ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper">

  <!-- ── SIDEBAR ──────────────────────────────────────────── -->
  <aside class="admin-sidebar" id="admin-sidebar">

    <div class="admin-brand">
      <div class="admin-brand-icon">✦</div>
      <div>
        <div class="admin-brand-name">Orlina</div>
        <div class="admin-brand-sub">Super Admin</div>
      </div>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-group">
        <div class="admin-nav-label">Overview</div>
        <a href="<?= url('admin/index.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Dashboard'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
          <span>Dashboard</span>
        </a>
      </div>

      <div class="admin-nav-group">
        <div class="admin-nav-label">Store</div>
        <a href="<?= url('admin/products.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Products'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-box-open"></i></span>
          <span>Products</span>
        </a>
        <a href="<?= url('admin/orders.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Orders'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-shopping-bag"></i></span>
          <span>Orders</span>
        </a>
        <a href="<?= url('admin/reviews.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Reviews'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-star"></i></span>
          <span>Reviews</span>
        </a>
      </div>

      <div class="admin-nav-group">
        <div class="admin-nav-label">Content</div>
        <a href="<?= url('admin/blog.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Blog'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
          <span>Blog</span>
        </a>
      </div>

      <div class="admin-nav-group">
        <div class="admin-nav-label">Management</div>
        <a href="<?= url('admin/users.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Users'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-users"></i></span>
          <span>Users</span>
        </a>
        <a href="<?= url('admin/settings.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Settings'?'active':'' ?>">
          <span class="nav-icon"><i class="fas fa-sliders"></i></span>
          <span>Settings</span>
        </a>
      </div>

      <div class="admin-nav-divider"></div>

      <a href="<?= url('index.php') ?>" class="admin-nav-link" target="_blank">
        <span class="nav-icon"><i class="fas fa-arrow-up-right-from-square"></i></span>
        <span>View Store</span>
      </a>
      <a href="<?= url('pages/logout.php') ?>" class="admin-nav-link nav-logout">
        <span class="nav-icon"><i class="fas fa-right-from-bracket"></i></span>
        <span>Logout</span>
      </a>
    </nav>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────── -->
  <div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
      <div class="admin-topbar-left">
        <button id="sidebar-toggle" class="admin-toggle-btn" aria-label="Toggle sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <div class="admin-breadcrumb">
          <span class="breadcrumb-home"><i class="fas fa-house"></i></span>
          <i class="fas fa-chevron-right breadcrumb-sep"></i>
          <span class="breadcrumb-page"><?= h(ADMIN_PAGE) ?></span>
        </div>
      </div>
      <div class="admin-topbar-right">
        <a href="<?= url('index.php') ?>" class="topbar-store-link" target="_blank" title="View Store">
          <i class="fas fa-store"></i>
        </a>
        <div class="admin-user-pill">
          <div class="admin-user-avatar">
            <?= strtoupper(substr($currentUser['first_name'], 0, 1)) ?>
          </div>
          <div class="admin-user-info">
            <div class="admin-user-name"><?= h($currentUser['first_name'].' '.$currentUser['last_name']) ?></div>
            <div class="admin-user-role">Super Admin</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="admin-flash admin-flash-<?= h($_SESSION['flash']['type']) ?>">
      <i class="fas fa-<?= $_SESSION['flash']['type']==='success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
      <?= h($_SESSION['flash']['message']) ?>
      <button onclick="this.parentElement.remove()" class="flash-x"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['flash']); endif; ?>

    <div class="admin-content">
