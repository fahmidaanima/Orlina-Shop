<?php
define('ADMIN_PAGE','Dashboard');
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/layout.php';

// Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status!='cancelled'")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newUsersMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$lowStock      = $pdo->query("SELECT * FROM products WHERE stock > 0 AND stock <= 5 ORDER BY stock ASC LIMIT 5")->fetchAll();

// Recent orders
$recentOrders = $pdo->query("
    SELECT o.*, u.first_name, u.last_name
    FROM orders o LEFT JOIN users u ON o.user_id=u.id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();

// Top selling
$topProducts = $pdo->query("
    SELECT p.name, p.price, COUNT(oi.id) AS sold
    FROM order_items oi JOIN products p ON p.id=oi.product_id
    GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5
")->fetchAll();
?>

<!-- Stat Cards -->
<div class="stat-cards">
  <div class="stat-card">
    <div class="stat-icon" style="background:#fdf2f8;color:var(--primary-start);"><i class="fas fa-box"></i></div>
    <div class="stat-value"><?= number_format($totalProducts) ?></div>
    <div class="stat-label">Total Products</div>
    <div class="stat-change stat-up"><i class="fas fa-arrow-up"></i> Active catalog</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-shopping-bag"></i></div>
    <div class="stat-value"><?= number_format($totalOrders) ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-change stat-up"><i class="fas fa-clock"></i> <?= $pendingOrders ?> pending</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-taka-sign"></i></div>
    <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-change stat-up"><i class="fas fa-arrow-up"></i> Excl. cancelled</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fas fa-users"></i></div>
    <div class="stat-value"><?= number_format($totalUsers) ?></div>
    <div class="stat-label">Total Users</div>
    <div class="stat-change stat-up"><i class="fas fa-user-plus"></i> +<?= $newUsersMonth ?> this month</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

  <!-- Recent Orders Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">Recent Orders</h3>
      <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
      <?php if ($recentOrders): foreach ($recentOrders as $o): ?>
      <tr>
        <td><strong style="font-size:0.82rem;"><?= h($o['order_number']) ?></strong></td>
        <td><?= h($o['first_name'].' '.($o['last_name']??'Guest')) ?></td>
        <td><?= formatPrice($o['total_amount']) ?></td>
        <td><span class="badge-pill <?= $o['payment_status']==='paid'?'badge-success':($o['payment_status']==='refunded'?'badge-info':'badge-warning') ?>"><?= ucfirst($o['payment_status']) ?></span></td>
        <td><span class="status-badge status-<?= h($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
        <td style="font-size:0.8rem;color:var(--text2);"><?= date('M j',strtotime($o['created_at'])) ?></td>
        <td><a href="<?= url('admin/orders.php') ?>?view=<?= $o['id'] ?>" class="btn-icon btn-icon-view"><i class="fas fa-eye"></i></a></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="7" style="text-align:center;color:var(--text2);padding:32px;">No orders yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- Sidebar: Top Products + Low Stock -->
  <div>
    <div class="admin-card" style="margin-bottom:24px;">
      <div class="admin-card-header"><h3 class="admin-card-title">Top Selling</h3></div>
      <div style="padding:0 16px;">
        <?php if ($topProducts): foreach ($topProducts as $i => $p): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
          <div style="width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0;"><?= $i+1 ?></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:0.88rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($p['name']) ?></div>
            <div style="font-size:0.75rem;color:var(--text2);"><?= formatPrice($p['price']) ?></div>
          </div>
          <div style="font-size:0.82rem;font-weight:700;color:var(--primary-start);"><?= $p['sold'] ?> sold</div>
        </div>
        <?php endforeach; else: ?>
        <p style="padding:16px 0;color:var(--text2);font-size:0.88rem;">No sales data yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($lowStock): ?>
    <div class="admin-card">
      <div class="admin-card-header"><h3 class="admin-card-title" style="color:#dc2626;">⚠ Low Stock Alert</h3></div>
      <div style="padding:0 16px;">
        <?php foreach ($lowStock as $p): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
          <span style="font-size:0.88rem;"><?= h($p['name']) ?></span>
          <span class="badge-pill badge-danger"><?= $p['stock'] ?> left</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
