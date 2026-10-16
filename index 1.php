<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Dashboard');
requireAdmin();

// Stats
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn();
$totalProducts= $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

$recentOrders = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o LEFT JOIN users u ON o.user_id=u.id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();

$lowStock = $pdo->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 6")->fetchAll();

$topProducts = $pdo->query("
    SELECT p.name, SUM(oi.quantity) AS sold, SUM(oi.quantity*oi.price) AS revenue,
           (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb
    FROM order_items oi JOIN products p ON oi.product_id=p.id
    GROUP BY p.id ORDER BY sold DESC LIMIT 5
")->fetchAll();

include __DIR__ . '/layout.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon stat-icon-pink"><i class="fas fa-taka-sign"></i></div>
    <div><div class="stat-value"><?= formatPrice($totalRevenue) ?></div><div class="stat-label">Total Revenue</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-purple"><i class="fas fa-shopping-bag"></i></div>
    <div><div class="stat-value"><?= number_format($totalOrders) ?></div><div class="stat-label">Total Orders</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-yellow"><i class="fas fa-users"></i></div>
    <div><div class="stat-value"><?= number_format($totalUsers) ?></div><div class="stat-label">Customers</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-green"><i class="fas fa-box"></i></div>
    <div><div class="stat-value"><?= number_format($totalProducts) ?></div><div class="stat-label">Products</div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;">

  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Recent Orders</span>
      <a href="<?= url('admin/orders.php') ?>" class="btn btn-outline btn-sm">View All</a>
    </div>
    <table class="data-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recentOrders as $ord): ?>
        <tr>
          <td><strong><?= h($ord['order_number']) ?></strong></td>
          <td><?= $ord['first_name'] ? h($ord['first_name'].' '.$ord['last_name']) : '<span style="color:var(--text2);">Guest</span>' ?></td>
          <td><?= formatPrice($ord['total_amount']) ?></td>
          <td><span style="text-transform:uppercase;font-size:0.78rem;font-weight:600;"><?= h($ord['payment_method']) ?></span></td>
          <td><span class="status-badge status-<?= h($ord['status']) ?>"><?= h(ucfirst($ord['status'])) ?></span></td>
          <td style="color:var(--text2);font-size:0.85rem;"><?= date('M j', strtotime($ord['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recentOrders): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--text2);padding:32px;">No orders yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Sidebar panels -->
  <div>
    <!-- Low Stock -->
    <div class="admin-card" style="margin-bottom:24px;">
      <div class="admin-card-header">
        <span class="admin-card-title">⚠ Low Stock</span>
        <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm">Manage</a>
      </div>
      <?php if ($lowStock): ?>
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($lowStock as $lp): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.88rem;">
          <span style="font-weight:500;"><?= h(substr($lp['name'],0,28)) ?>…</span>
          <span style="background:#fef2f2;color:#dc2626;padding:2px 10px;border-radius:50px;font-weight:600;font-size:0.75rem;"><?= $lp['stock'] ?> left</span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="color:var(--text2);font-size:0.9rem;text-align:center;padding:16px;">All products are well stocked ✓</p>
      <?php endif; ?>
    </div>

    <!-- Top Products -->
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Top Products</span></div>
      <?php if ($topProducts): ?>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($topProducts as $tp): ?>
        <div style="display:flex;align-items:center;gap:12px;">
          <img src="<?= h($tp['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=60&h=60&fit=crop') ?>" style="width:42px;height:42px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="">
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($tp['name']) ?></div>
            <div style="font-size:0.78rem;color:var(--text2);"><?= $tp['sold'] ?> sold</div>
          </div>
          <div style="font-size:0.85rem;font-weight:600;color:var(--primary-start);"><?= formatPrice($tp['revenue']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="color:var(--text2);font-size:0.9rem;text-align:center;padding:16px;">No sales data yet</p>
      <?php endif; ?>
    </div>
  </div>
</div>

    </div>
  </div>
</div>
</body>
</html>
