<?php
define('ADMIN_PAGE','Orders');
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/layout.php';

// Update status
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $oid = (int)$_POST['order_id'];
    if (isset($_POST['status'])) {
        $s = in_array($_POST['status'],['pending','processing','shipped','delivered','cancelled']) ? $_POST['status'] : 'pending';
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$s,$oid]);
        setFlash('success','Order status updated.');
    }
    if (isset($_POST['payment_status'])) {
        $ps = in_array($_POST['payment_status'],['unpaid','paid','refunded']) ? $_POST['payment_status'] : 'unpaid';
        $pdo->prepare("UPDATE orders SET payment_status=? WHERE id=?")->execute([$ps,$oid]);
        setFlash('success','Payment status updated.');
    }
    header('Location: '.url('admin/orders.php').'?'.http_build_query($_GET)); exit;
}

$status  = $_GET['status']??'';
$search  = sanitize($_GET['q']??'');
$page    = max(1,(int)($_GET['page']??1));
$perPage = 15; $offset = ($page-1)*$perPage;

$where = []; $params = [];
if ($status) { $where[] = "o.status=?"; $params[] = $status; }
if ($search) { $where[] = "o.order_number LIKE ?"; $params[] = "%$search%"; }
$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM orders o $whereStr"); $total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = max(1,ceil($totalCount/$perPage));

$orders = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o LEFT JOIN users u ON o.user_id=u.id
    $whereStr ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset
");
$orders->execute($params);
$orders = $orders->fetchAll();

// View single order
$viewOrder = null; $viewItems = [];
if (isset($_GET['view'])) {
    $vs = $pdo->prepare("SELECT o.*, u.first_name, u.last_name, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $vs->execute([(int)$_GET['view']]);
    $viewOrder = $vs->fetch();
    if ($viewOrder) $viewItems = getOrderItems($viewOrder['id']);
}
?>

<?php if ($viewOrder): ?>
<!-- Order Detail View -->
<div style="margin-bottom:16px;"><a href="<?= url('admin/orders.php') ?>" class="btn btn-sm btn-outline"><i class="fas fa-arrow-left"></i> Back to Orders</a></div>
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h3 class="admin-card-title">Order: <?= h($viewOrder['order_number']) ?></h3><span class="status-badge status-<?= h($viewOrder['status']) ?>"><?= ucfirst($viewOrder['status']) ?></span></div>
    <div class="admin-card-body padded">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div><strong>Customer</strong><p><?= h($viewOrder['first_name'].' '.($viewOrder['last_name']??'')) ?><br><?= h($viewOrder['email']??'Guest') ?><br><?= h($viewOrder['phone']??'') ?></p></div>
        <div><strong>Shipping Address</strong><p style="white-space:pre-line;"><?= h($viewOrder['shipping_address']??'—') ?></p></div>
        <div><strong>Payment Method</strong><p><?= h(ucfirst($viewOrder['payment_method']??'COD')) ?></p></div>
        <div><strong>Stripe Session</strong><p style="font-size:0.8rem;"><?= h($viewOrder['stripe_session_id']??'—') ?></p></div>
      </div>
      <table class="admin-table">
        <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($viewItems as $it): ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px;"><img src="<?= h($it['thumb']?:defaultProductImage()) ?>" width="40" height="40" style="border-radius:6px;object-fit:cover;"><span><?= h($it['name']) ?></span></div></td>
          <td><?= $it['quantity'] ?></td>
          <td><?= formatPrice($it['price']) ?></td>
          <td><?= formatPrice($it['price']*$it['quantity']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="3" style="text-align:right;font-weight:700;">Grand Total</td><td><strong><?= formatPrice($viewOrder['total_amount']) ?></strong></td></tr></tfoot>
      </table>
    </div>
  </div>
  <div>
    <div class="admin-card">
      <div class="admin-card-header"><h3 class="admin-card-title">Update Status</h3></div>
      <div class="admin-card-body padded">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
          <div class="admin-form-group" style="margin-bottom:12px;"><label>Order Status</label><select name="status" onchange="this.form.submit()"><?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $viewOrder['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
        </form>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
          <div class="admin-form-group"><label>Payment Status</label><select name="payment_status" onchange="this.form.submit()"><?php foreach (['unpaid','paid','refunded'] as $s): ?><option value="<?= $s ?>" <?= $viewOrder['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- Order List -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <h2 style="font-size:1.3rem;">Orders (<?= $totalCount ?>)</h2>
</div>

<div class="admin-card" style="margin-bottom:20px;"><div class="admin-card-body padded">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
    <div class="admin-form-group"><label>Search Order #</label><input type="text" name="q" value="<?= h($search) ?>" placeholder="ORL-…"></div>
    <div class="admin-form-group"><label>Status</label><select name="status"><option value="">All</option><?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm btn-outline">Clear</a>
  </form>
</div></div>

<div class="admin-card">
  <div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if ($orders): foreach ($orders as $o): ?>
    <tr>
      <td><strong><?= h($o['order_number']) ?></strong></td>
      <td><?= h(($o['first_name']??'Guest').' '.($o['last_name']??'')) ?></td>
      <td><?= formatPrice($o['total_amount']) ?></td>
      <td><span class="badge-pill <?= $o['payment_status']==='paid'?'badge-success':($o['payment_status']==='refunded'?'badge-info':'badge-warning') ?>"><?= ucfirst($o['payment_status']) ?></span></td>
      <td>
        <form method="POST" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <select name="status" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--border);border-radius:8px;font-size:0.82rem;outline:none;">
            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td style="font-size:0.82rem;color:var(--text2);"><?= date('M j, Y',strtotime($o['created_at'])) ?></td>
      <td><a href="?view=<?= $o['id'] ?>" class="btn-icon btn-icon-view"><i class="fas fa-eye"></i></a></td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text2);">No orders found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="pagination" style="padding:16px;"><?php for ($i=1;$i<=$totalPages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
