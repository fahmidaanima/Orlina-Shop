<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'My Account');
requireLogin();

$user = currentUser();
$tab  = $_GET['tab'] ?? 'dashboard';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verify_csrf()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Security error.']; }
    else {
        $fname   = trim($_POST['first_name'] ?? '');
        $lname   = trim($_POST['last_name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $stmt = $pdo->prepare("UPDATE users SET first_name=?,last_name=?,phone=?,address=?,city=? WHERE id=?");
        $stmt->execute([$fname,$lname,$phone,$address,$city,$user['id']]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Profile updated successfully.'];
        header('Location: ' . url('pages/myaccount.php') . '?tab=profile'); exit;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Security error.']; }
    else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Current password is incorrect.'];
        } elseif (strlen($new) < 8) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'New password must be at least 8 characters.'];
        } elseif ($new !== $confirm) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Passwords do not match.'];
        } else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Password changed successfully.'];
        }
        header('Location: ' . url('pages/myaccount.php') . '?tab=password'); exit;
    }
}

// Orders
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$orders->execute([$user['id']]);
$myOrders = $orders->fetchAll();

// Wishlist
$wishlistStmt = $pdo->prepare("
    SELECT p.*, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb, c.name AS category_name
    FROM wishlists w JOIN products p ON w.product_id=p.id LEFT JOIN categories c ON p.category_id=c.id
    WHERE w.user_id=? ORDER BY w.created_at DESC
");
$wishlistStmt->execute([$user['id']]);
$wishlistItems = $wishlistStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>My Account</h1>
    <p>Manage your profile, orders and wishlist</p>
  </div>
</div>

<div class="container">
  <div class="account-layout">

    <!-- Sidebar -->
    <aside class="account-sidebar">
      <div class="account-avatar">
        <img src="<?= $user['avatar'] ? h($user['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'].' '.$user['last_name']) . '&background=ec4899&color=fff&size=160' ?>" class="avatar-img" alt="">
        <div class="account-name"><?= h($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="account-email"><?= h($user['email']) ?></div>
      </div>
      <nav class="account-nav">
        <a href="?tab=dashboard" class="account-nav-link <?= $tab==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="?tab=orders" class="account-nav-link <?= $tab==='orders'?'active':'' ?>"><i class="fas fa-box"></i> My Orders</a>
        <a href="?tab=wishlist" class="account-nav-link <?= $tab==='wishlist'?'active':'' ?>"><i class="fas fa-heart"></i> Wishlist</a>
        <a href="?tab=profile" class="account-nav-link <?= $tab==='profile'?'active':'' ?>"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="?tab=password" class="account-nav-link <?= $tab==='password'?'active':'' ?>"><i class="fas fa-lock"></i> Change Password</a>
        <a href="<?= url('pages/logout.php') ?>" class="account-nav-link" style="color:#ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </nav>
    </aside>

    <!-- Content -->
    <div class="account-content">

      <?php if ($tab === 'dashboard'): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">Welcome back, <?= h($user['first_name']) ?>! 👋</h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
          <div style="background:var(--primary);border-radius:var(--radius);padding:20px;color:#fff;text-align:center;">
            <div style="font-size:2rem;font-weight:700;"><?= count($myOrders) ?></div>
            <div style="font-size:0.85rem;opacity:0.9;">Total Orders</div>
          </div>
          <div style="background:var(--surface);border-radius:var(--radius);padding:20px;text-align:center;border:1px solid var(--border);">
            <div style="font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= count($wishlistItems) ?></div>
            <div style="font-size:0.85rem;color:var(--text2);">Wishlist Items</div>
          </div>
          <div style="background:var(--surface);border-radius:var(--radius);padding:20px;text-align:center;border:1px solid var(--border);">
            <div style="font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= count(array_filter($myOrders, fn($o) => $o['status']==='delivered')) ?></div>
            <div style="font-size:0.85rem;color:var(--text2);">Completed</div>
          </div>
        </div>
      </div>

      <?php if ($myOrders): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">Recent Orders</h3>
        <table class="data-table">
          <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach (array_slice($myOrders,0,5) as $ord): ?>
            <tr>
              <td><strong><?= h($ord['order_number']) ?></strong></td>
              <td><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
              <td><?= formatPrice($ord['total_amount']) ?></td>
              <td><span class="status-badge status-<?= h($ord['status']) ?>"><?= h(ucfirst($ord['status'])) ?></span></td>
              <td><a href="<?= url('pages/order_tracking.php') ?>?order=<?= urlencode($ord['order_number']) ?>" style="color:var(--primary-start);font-size:0.85rem;">Track</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <?php elseif ($tab === 'orders'): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">My Orders (<?= count($myOrders) ?>)</h3>
        <?php if ($myOrders): ?>
        <table class="data-table">
          <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($myOrders as $ord): ?>
            <tr>
              <td><strong><?= h($ord['order_number']) ?></strong></td>
              <td><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
              <td><?= formatPrice($ord['total_amount']) ?></td>
              <td><span class="status-badge status-<?= h($ord['payment_status']) ?>"><?= h(ucfirst($ord['payment_status'])) ?></span></td>
              <td><span class="status-badge status-<?= h($ord['status']) ?>"><?= h(ucfirst($ord['status'])) ?></span></td>
              <td><a href="<?= url('pages/order_tracking.php') ?>?order=<?= urlencode($ord['order_number']) ?>" class="btn btn-outline btn-sm">Track</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-box"></i><h3>No orders yet</h3><p>Start shopping and your orders will appear here.</p><a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Shop Now</a></div>
        <?php endif; ?>
      </div>

      <?php elseif ($tab === 'wishlist'): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">My Wishlist (<?= count($wishlistItems) ?>)</h3>
        <?php if ($wishlistItems): ?>
        <div class="products-grid products-grid-3">
          <?php foreach ($wishlistItems as $p): ?>
          <?php include __DIR__ . '/includes/product_card.php'; ?>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="far fa-heart"></i><h3>No saved items</h3><p>Browse our collection and save your favorites.</p><a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Explore Products</a></div>
        <?php endif; ?>
      </div>

      <?php elseif ($tab === 'profile'): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">Edit Profile</h3>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="update_profile" value="1">
          <div class="form-grid">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="first_name" value="<?= h($user['first_name']) ?>" required>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="last_name" value="<?= h($user['last_name']) ?>">
            </div>
            <div class="form-group">
              <label>Email (read-only)</label>
              <input type="email" value="<?= h($user['email']) ?>" disabled style="background:#f3f4f6;color:var(--text2);">
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="tel" name="phone" value="<?= h($user['phone'] ?? '') ?>">
            </div>
            <div class="form-group form-full">
              <label>Address</label>
              <input type="text" name="address" value="<?= h($user['address'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>City</label>
              <input type="text" name="city" value="<?= h($user['city'] ?? '') ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </form>
      </div>

      <?php elseif ($tab === 'password'): ?>
      <div class="account-card">
        <h3 style="margin-bottom:20px;">Change Password</h3>
        <form method="POST" style="max-width:400px;">
          <?= csrf_field() ?>
          <input type="hidden" name="change_password" value="1">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="8">
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Update Password</button>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
