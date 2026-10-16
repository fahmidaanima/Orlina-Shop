<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Order Confirmed');

$orderNum = $_GET['order'] ?? $_SESSION['last_order'] ?? '';
$order = null;
if ($orderNum) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNum]);
    $order = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<div class="success-page">
  <div class="success-icon"><i class="fas fa-check"></i></div>
  <h1 style="font-size:2.5rem;margin-bottom:12px;">Order Confirmed!</h1>
  <p style="color:var(--text2);max-width:480px;margin:0 auto 32px;">
    Thank you for shopping with Orlina. Your order has been received and is being processed with care.
  </p>

  <?php if ($order): ?>
  <div style="background:var(--surface);border-radius:var(--radius);padding:32px;max-width:480px;width:100%;border:1px solid var(--border);text-align:left;margin-bottom:32px;">
    <div style="display:grid;gap:14px;">
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--text2);">Order Number</span><strong><?= h($order['order_number']) ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--text2);">Date</span><span><?= date('M j, Y', strtotime($order['created_at'])) ?></span></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--text2);">Total Amount</span><strong><?= formatPrice($order['total_amount']) ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--text2);">Payment Method</span><span><?= h(strtoupper($order['payment_method'])) ?></span></div>
      <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:var(--text2);">Status</span><span class="status-badge status-pending">Pending</span></div>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;">
    <a href="<?= url('pages/order_tracking.php') ?>?order=<?= urlencode($orderNum) ?>" class="btn btn-primary">
      <i class="fas fa-map-marker-alt"></i> Track Order
    </a>
    <a href="<?= url('index.php') ?>" class="btn btn-outline">
      <i class="fas fa-home"></i> Continue Shopping
    </a>
  </div>

  <div style="margin-top:48px;padding:24px;background:var(--surface);border-radius:var(--radius);max-width:480px;width:100%;text-align:center;">
    <p style="color:var(--text2);font-size:0.9rem;"><i class="fas fa-envelope" style="color:var(--primary-start);margin-right:6px;"></i>A confirmation email has been sent to your email address.</p>
    <p style="color:var(--text2);font-size:0.9rem;margin-top:8px;"><i class="fas fa-headset" style="color:var(--primary-start);margin-right:6px;"></i>Questions? Contact us at <strong>hello@orlina.com</strong></p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
