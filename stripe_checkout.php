<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Stripe Payment');

$orderId = (int)($_GET['order_id'] ?? 0);
$order = null;
if ($orderId) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
}

$settings = getSettings();
$stripeKey = $settings['stripe_publishable_key'] ?? 'pk_test_REPLACE';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:80px 24px;text-align:center;">
  <div style="max-width:480px;margin:0 auto;background:var(--surface);border-radius:24px;padding:48px;border:1px solid var(--border);">
    <div style="width:72px;height:72px;background:#f5f3ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:1.8rem;color:#635bff;">
      <i class="fab fa-stripe-s"></i>
    </div>
    <h2 style="margin-bottom:8px;">Stripe Payment</h2>
    <?php if ($order): ?>
    <p style="color:var(--text2);margin-bottom:24px;">Order: <strong><?= h($order['order_number']) ?></strong><br>Amount: <strong><?= formatPrice($order['total_amount']) ?></strong></p>
    <div style="background:#eff6ff;border-radius:10px;padding:16px;margin-bottom:24px;font-size:0.9rem;color:#1e40af;">
      <i class="fas fa-info-circle"></i> To activate Stripe payments, add your API keys in the Admin Panel → Settings.
    </div>
    <?php endif; ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <a href="<?= url('pages/order_success.php') ?>?order=<?= urlencode($order['order_number'] ?? '') ?>" class="btn btn-primary btn-full">
        <i class="fas fa-check"></i> Simulate Payment (Dev Mode)
      </a>
      <a href="<?= url('pages/cart.php') ?>" class="btn btn-outline btn-full">Back to Cart</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
