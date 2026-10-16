<?php
if (!defined('ADMIN_PAGE')) define('ADMIN_PAGE', 'Dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= ADMIN_PAGE ?> — Orlina Admin</title>
<link rel="stylesheet" href="<?= url('assets/css/google-fonts.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/all.min.css') ?>">
<link rel="stylesheet" href="<?= url('../assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('admin/admin.css') ?>">
</head>
<body>
<div class="admin-wrapper">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">✦ Orlina</div>
    <nav class="admin-nav">
      <div class="admin-nav-section">Main</div>
      <a href="<?= url('admin/index.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Dashboard'?'active':'' ?>">
        <i class="fas fa-th-large"></i> Dashboard
      </a>

      <div class="admin-nav-section">Store</div>
      <a href="<?= url('admin/products.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Products'?'active':'' ?>">
        <i class="fas fa-box"></i> Products
      </a>
      <a href="<?= url('admin/orders.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Orders'?'active':'' ?>">
        <i class="fas fa-shopping-bag"></i> Orders
      </a>
      <a href="<?= url('admin/reviews.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Reviews'?'active':'' ?>">
        <i class="fas fa-star"></i> Reviews
      </a>

      <div class="admin-nav-section">Content</div>
      <a href="<?= url('admin/blog.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Blog'?'active':'' ?>">
        <i class="fas fa-newspaper"></i> Blog
      </a>

      <div class="admin-nav-section">Users</div>
      <a href="<?= url('admin/users.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Users'?'active':'' ?>">
        <i class="fas fa-users"></i> Users
      </a>

      <div class="admin-nav-section">Config</div>
      <a href="<?= url('admin/settings.php') ?>" class="admin-nav-link <?= ADMIN_PAGE==='Settings'?'active':'' ?>">
        <i class="fas fa-cog"></i> Settings
      </a>

      <div class="admin-nav-section" style="margin-top:32px;"></div>
      <a href="<?= url('index.php') ?>" class="admin-nav-link" target="_blank">
        <i class="fas fa-external-link-alt"></i> View Store
      </a>
      <a href="<?= url('pages/logout.php') ?>" class="admin-nav-link" style="color:#f87171;">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div class="admin-topbar-title"><?= ADMIN_PAGE ?></div>
      <div style="display:flex;align-items:center;gap:16px;">
        <span style="font-size:0.85rem;color:var(--text2);">
          <i class="fas fa-user-shield" style="color:var(--primary-start);"></i>
          <?= h($_SESSION['user_name'] ?? 'Admin') ?>
        </span>
      </div>
    </div>

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>" style="border-radius:0;">
      <?= h($_SESSION['flash']['message']) ?>
      <button class="flash-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['flash']); endif; ?>

    <div class="admin-content">
