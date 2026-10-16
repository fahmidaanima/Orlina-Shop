<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Orders');
requireAdmin();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf()) {
        $oid    = (int)$_POST['order_id'];
        $status = $_POST['status'];
        $pstatus= $_POST['payment_status'];
        $allowed = ['pending','processing','shipped','delivered','cancelled'];
        if (in_array($status, $allowed)) {
            $pdo->prepare("UPDATE orders SET status=?,payment_status=? WHERE id=?")->execute([$status,$pstatus,$oid]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Order updated.'];
        }
    }
    header('Location: ' . url('admin/orders.php')); exit;
}

$filter = $_GET['status'] ?? '';
$search = $_GET['s'] ?? '';
$where  = [];
$params = [];
if ($filter) { $where[] = 'o.status=?'; $params[] = $filter; }
if ($search) { $where[] = '(o.order_number LIKE ? OR u.email LIKE ? OR u.first_name LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orders = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o LEFT JOIN users u ON o.user_id=u.id
    $whereStr ORDER BY o.created_at DESC LIMIT 60
");
$orders->execute($params);
$orders = $orders->fetchAll();

$counts = [];
foreach (['pending','processing','shipped','delivered','cancelled'] as $s) {
    $counts[$s] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='$s'")->fetchColumn();
}

include __DIR__ . '/layout.php';
?>

<!-- Filter Tabs -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
  <a href="?" class="btn <?= !$filter ? 'btn-primary' : 'btn-outline' ?> btn-sm">All (<?= array_sum($counts) ?>)</a>
  <?php foreach ($counts as $s => $c): ?>
  <a href="?status=<?= $s ?>" class="btn <?= $filter===$s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= ucfirst($s) ?> (<?= $c ?>)</a>
  <?php endforeach; ?>

  <form method="GET" style="margin-left:auto;display:flex;gap:8px;">
    <?php if ($filter): ?><input type="hidden" name="status" value="<?= h($filter) ?>"><?php endif; ?>
    <input type="text" name="s" value="<?= h($search) ?>" placeholder="Search order/email…" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;outline:none;">
    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
  </form>
</div>

<div class="admin-card">
  <table class="data-table">
    <thead>
      <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $ord): ?>
      <tr>
        <td><strong><?= h($ord['order_number']) ?></strong></td>
        <td>
          <?php if ($ord['first_name']): ?>
          <div><?= h($ord['first_name'].' '.$ord['last_name']) ?></div>
          <div style="font-size:0.8rem;color:var(--text2);"><?= h($ord['email']) ?></div>
          <?php else: echo '<span style="color:var(--text2);">Guest</span>'; endif; ?>
        </td>
        <td><strong><?= formatPrice($ord['total_amount']) ?></strong></td>
        <td><span class="status-badge status-<?= h($ord['payment_status']) ?>"><?= h(ucfirst($ord['payment_status'])) ?></span></td>
        <td style="text-transform:uppercase;font-size:0.78rem;font-weight:600;"><?= h($ord['payment_method']) ?></td>
        <td><span class="status-badge status-<?= h($ord['status']) ?>"><?= h(ucfirst($ord['status'])) ?></span></td>
        <td style="font-size:0.85rem;color:var(--text2);"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
        <td>
          <button class="btn-icon btn-icon-edit" onclick="openUpdateModal(<?= json_encode($ord) ?>)" title="Update Status"><i class="fas fa-edit"></i></button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?>
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">No orders found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Update Status Modal -->
<div class="modal-overlay" id="update-modal" style="display:none;">
  <div class="modal-box" style="max-width:420px;">
    <div class="modal-header">
      <h3>Update Order</h3>
      <button class="modal-close" onclick="document.getElementById('update-modal').style.display='none'"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="update_status" value="1">
      <input type="hidden" name="order_id" id="upd-id">
      <div style="background:var(--surface);border-radius:10px;padding:14px;margin-bottom:20px;">
        <strong id="upd-num"></strong><br>
        <span id="upd-total" style="color:var(--primary-start);font-weight:600;"></span>
      </div>
      <div class="form-group">
        <label>Order Status</label>
        <select name="status" id="upd-status" style="width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;">
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="form-group">
        <label>Payment Status</label>
        <select name="payment_status" id="upd-pay" style="width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;">
          <option value="unpaid">Unpaid</option>
          <option value="paid">Paid</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('update-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUpdateModal(o) {
  document.getElementById('upd-id').value     = o.id;
  document.getElementById('upd-num').textContent = o.order_number;
  document.getElementById('upd-total').textContent = 'Total: ' + o.total_amount + ' BDT';
  document.getElementById('upd-status').value  = o.status;
  document.getElementById('upd-pay').value     = o.payment_status;
  document.getElementById('update-modal').style.display = 'flex';
}
</script>

    </div>
  </div>
</div>
</body>
</html>
