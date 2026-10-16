<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin('/pages/login.php');
define('PAGE_TITLE','My Account');

$user = currentUser();
$tab  = $_GET['tab'] ?? 'dashboard';

// Handle profile update
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['form_action'] ?? '';

    if ($action === 'update_profile') {
        $firstName = sanitize($_POST['first_name']??'');
        $lastName  = sanitize($_POST['last_name']??'');
        $phone     = sanitize($_POST['phone']??'');
        $address   = sanitize($_POST['address']??'');
        $city      = sanitize($_POST['city']??'');
        $pdo->prepare("UPDATE users SET first_name=?,last_name=?,phone=?,address=?,city=? WHERE id=?")->execute([$firstName,$lastName,$phone,$address,$city,$_SESSION['user_id']]);
        $_SESSION['user_name'] = "$firstName $lastName";
        setFlash('success','Profile updated successfully.');
        header('Location: '.url('pages/myaccount.php').'?tab=profile'); exit;
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password']??'';
        $new     = $_POST['new_password']??'';
        $confirm = $_POST['confirm_password']??'';
        if (!password_verify($current, $user['password'])) { setFlash('error','Current password is incorrect.'); }
        elseif (strlen($new) < 6) { setFlash('error','New password must be at least 6 characters.'); }
        elseif ($new !== $confirm) { setFlash('error','Passwords do not match.'); }
        else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,$_SESSION['user_id']]);
            setFlash('success','Password changed successfully.');
        }
        header('Location: '.url('pages/myaccount.php').'?tab=security'); exit;
    }

    if ($action === 'upload_avatar' && !empty($_FILES['avatar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp'])) {
            $dir  = $_SERVER['DOCUMENT_ROOT'].BASE_URL.'/admin/uploads/avatars/';
            if (!is_dir($dir)) mkdir($dir,0755,true);
            $fname = 'avatar_'.$_SESSION['user_id'].'.'.$ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'],$dir.$fname);
            $path = BASE_URL.'/admin/uploads/avatars/'.$fname;
            $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute([$path,$_SESSION['user_id']]);
            setFlash('success','Avatar updated.');
        }
        header('Location: '.url('pages/myaccount.php').'?tab=profile'); exit;
    }
}

// Orders
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$orderStmt->execute([$_SESSION['user_id']]);
$orders = $orderStmt->fetchAll();

// Re-fetch user after possible update
$user = currentUser();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>My Account</h1>
    <nav class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i class="fas fa-chevron-right"></i><span>My Account</span></nav>
  </div>
</div>

<div class="container">
  <div class="account-layout">

    <!-- SIDEBAR -->
    <aside class="account-sidebar">
      <div class="account-user">
        <img src="<?= avatarUrl($user['avatar']??null, $user['first_name'].' '.$user['last_name']) ?>" alt="Avatar" class="account-avatar">
        <strong><?= h($user['first_name'].' '.$user['last_name']) ?></strong>
        <small style="color:var(--text2);"><?= h($user['email']) ?></small>
        <span class="badge-pill badge-purple" style="margin-top:8px;"><?= ucfirst(str_replace('_',' ',$user['role'])) ?></span>
      </div>
      <nav class="account-nav">
        <a href="?tab=dashboard" class="<?= $tab==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="?tab=orders"   class="<?= $tab==='orders'?'active':'' ?>"><i class="fas fa-shopping-bag"></i> My Orders</a>
        <a href="?tab=profile"  class="<?= $tab==='profile'?'active':'' ?>"><i class="fas fa-user"></i> Profile</a>
        <a href="?tab=security" class="<?= $tab==='security'?'active':'' ?>"><i class="fas fa-lock"></i> Security</a>
        <a href="<?= url('pages/wishlist.php') ?>"><i class="far fa-heart"></i> Wishlist</a>
        <a href="<?= url('pages/logout.php') ?>" style="color:#ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </nav>
    </aside>

    <!-- MAIN -->
    <div class="account-content">

      <?php if ($tab === 'dashboard'): ?>
      <!-- Dashboard Tab -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:28px;">
        <div class="stat-card"><div class="stat-icon" style="background:#fdf2f8;color:var(--primary-start);"><i class="fas fa-shopping-bag"></i></div><div class="stat-value"><?= count($orders) ?></div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i></div><div class="stat-value"><?= count(array_filter($orders,fn($o)=>$o['status']==='delivered')) ?></div><div class="stat-label">Delivered</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:#fef9c3;color:#854d0e;"><i class="fas fa-clock"></i></div><div class="stat-value"><?= count(array_filter($orders,fn($o)=>in_array($o['status'],['pending','processing']))) ?></div><div class="stat-label">Active Orders</div></div>
      </div>
      <div class="admin-card">
        <div class="admin-card-header"><h3 class="admin-card-title">Recent Orders</h3><a href="?tab=orders" style="font-size:0.85rem;color:var(--primary-start);">View All</a></div>
        <?php if ($orders): ?>
        <div class="admin-table-wrap">
        <table class="orders-table">
          <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach (array_slice($orders,0,5) as $o):
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id=?"); $cnt->execute([$o['id']]); $itemCount=$cnt->fetchColumn();
          ?>
          <tr>
            <td><strong><?= h($o['order_number']) ?></strong></td>
            <td><?= date('M j, Y',strtotime($o['created_at'])) ?></td>
            <td><?= $itemCount ?> item<?= $itemCount!==1?'s':'' ?></td>
            <td><?= formatPrice($o['total_amount']) ?></td>
            <td><span class="status-badge status-<?= h($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
            <td><a href="<?= url('pages/order_tracking.php') ?>?order=<?= h($o['order_number']) ?>" class="btn btn-sm btn-outline">Track</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php else: ?><div class="empty-state" style="padding:40px;"><i class="fas fa-shopping-bag"></i><h3>No orders yet</h3><p>Start shopping!</p><a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Shop Now</a></div><?php endif; ?>
      </div>

      <?php elseif ($tab === 'orders'): ?>
      <div class="admin-card">
        <div class="admin-card-header"><h3 class="admin-card-title">All Orders</h3></div>
        <?php if ($orders): ?>
        <div class="admin-table-wrap">
        <table class="orders-table">
          <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><strong><?= h($o['order_number']) ?></strong></td>
            <td><?= date('M j, Y',strtotime($o['created_at'])) ?></td>
            <td><?= formatPrice($o['total_amount']) ?></td>
            <td><?= h(ucfirst($o['payment_method']??'COD')) ?></td>
            <td><span class="status-badge status-<?= h($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
            <td><a href="<?= url('pages/order_tracking.php') ?>?order=<?= h($o['order_number']) ?>" class="btn btn-sm btn-outline">Track</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php else: ?><div class="empty-state" style="padding:40px;"><i class="fas fa-shopping-bag"></i><h3>No orders yet</h3><a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Shop Now</a></div><?php endif; ?>
      </div>

      <?php elseif ($tab === 'profile'): ?>
      <div class="admin-card">
        <div class="admin-card-header"><h3 class="admin-card-title">Edit Profile</h3></div>
        <div class="admin-card-body padded">
          <form method="POST" enctype="multipart/form-data" style="margin-bottom:28px;">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="upload_avatar">
            <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">
              <img src="<?= avatarUrl($user['avatar']??null, $user['first_name'].' '.$user['last_name']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-start);" alt="">
              <div>
                <input type="file" name="avatar" accept="image/*" style="margin-bottom:8px;">
                <div style="font-size:0.78rem;color:var(--text2);">JPG, PNG, or WebP. Max 2MB.</div>
              </div>
            </div>
            <button type="submit" class="btn btn-sm btn-outline">Update Avatar</button>
          </form>
          <hr style="border-color:var(--border);margin-bottom:24px;">
          <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="update_profile">
            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?= h($user['first_name']) ?>" required></div>
              <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?= h($user['last_name']) ?>" required></div>
              <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= h($user['phone']??'') ?>"></div>
              <div class="form-group"><label>City</label><input type="text" name="city" value="<?= h($user['city']??'') ?>"></div>
              <div class="form-group full"><label>Address</label><input type="text" name="address" value="<?= h($user['address']??'') ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>

      <?php elseif ($tab === 'security'): ?>
      <div class="admin-card">
        <div class="admin-card-header"><h3 class="admin-card-title">Change Password</h3></div>
        <div class="admin-card-body padded">
          <form method="POST" style="max-width:420px;">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="change_password">
            <div class="form-group" style="margin-bottom:16px;"><label>Current Password</label><input type="password" name="current_password" required></div>
            <div class="form-group" style="margin-bottom:16px;"><label>New Password</label><input type="password" name="new_password" required placeholder="Min. 6 characters"></div>
            <div class="form-group" style="margin-bottom:24px;"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
            <button type="submit" class="btn btn-primary">Update Password</button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
