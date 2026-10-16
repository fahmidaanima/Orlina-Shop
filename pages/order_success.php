<?php
require_once __DIR__ . '/../includes/functions.php';
define('PAGE_TITLE', 'Order Confirmed');

$orderNumber = $_GET['order'] ?? ($_SESSION['last_order_number'] ?? '');
$order = $orderNumber ? getOrderByNumber($orderNumber) : null;

include __DIR__ . '/../includes/header.php';
?>

<div style="padding:60px 24px;">
  <div class="success-card">
    <div class="success-icon"><i class="fas fa-check"></i></div>
    <h1 style="font-size:2rem;margin-bottom:12px;">Order Confirmed!</h1>
    <p style="color:var(--text2);margin-bottom:8px;">Thank you for your purchase. We've received your order.</p>

    <?php if ($order): ?>
    <div style="background:var(--surface);border-radius:12px;padding:20px;margin:20px 0;text-align:left;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:0.88rem;">
        <div><span style="color:var(--text2);">Order Number</span><br><strong><?= h($order['order_number']) ?></strong></div>
        <div><span style="color:var(--text2);">Date</span><br><strong><?= date('M j, Y', strtotime($order['created_at'])) ?></strong></div>
        <div><span style="color:var(--text2);">Total</span><br><strong><?= formatPrice($order['total_amount']) ?></strong></div>
        <div><span style="color:var(--text2);">Payment</span><br><strong><?= h(ucfirst($order['payment_method'] ?? 'COD')) ?></strong></div>
      </div>
    </div>
    <?php endif; ?>

    <p style="font-size:0.88rem;color:var(--text2);margin-bottom:24px;">A confirmation email will be sent to you shortly. You can track your order below.</p>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="<?= url('pages/order_tracking.php') ?>?order=<?= h($orderNumber) ?>" class="btn btn-primary">
        <i class="fas fa-map-marker-alt"></i> Track Order
      </a>
      <a href="<?= url('pages/shop.php') ?>" class="btn btn-outline">Continue Shopping</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
