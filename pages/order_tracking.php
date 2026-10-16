<?php
require_once __DIR__ . '/../includes/functions.php';
define('PAGE_TITLE', 'Track Order');

$orderNumber = sanitize($_GET['order'] ?? ($_POST['order_number'] ?? ''));
$order = null;
$orderItems = [];
$error = '';

if ($orderNumber) {
    $order = getOrderByNumber($orderNumber);
    if ($order) {
        $orderItems = getOrderItems($order['id']);
    } else {
        $error = 'No order found with that order number. Please check and try again.';
    }
}

$steps = [
    'pending'    => ['icon'=>'fa-clock','label'=>'Order Placed','desc'=>'Your order has been received'],
    'processing' => ['icon'=>'fa-cog','label'=>'Processing','desc'=>'We\'re preparing your order'],
    'shipped'    => ['icon'=>'fa-truck','label'=>'Shipped','desc'=>'Your order is on its way'],
    'delivered'  => ['icon'=>'fa-check-circle','label'=>'Delivered','desc'=>'Order delivered successfully'],
];
$statusOrder = ['pending','processing','shipped','delivered'];
$currentStep = array_search($order['status'] ?? 'pending', $statusOrder);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Track Your Order</h1>
    <p>Enter your order number to see the status</p>
  </div>
</div>

<div class="container" style="padding:48px 24px;">
  <form method="GET" style="max-width:500px;margin:0 auto 40px;display:flex;gap:12px;">
    <input type="text" name="order" value="<?= h($orderNumber) ?>" placeholder="Enter order number (ORL-XXXXXXXX-XXXXXXXX)" class="form-group" style="flex:1;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-family:'Outfit',sans-serif;outline:none;" required>
    <button type="submit" class="btn btn-primary">Track</button>
  </form>

  <?php if ($error): ?>
  <div class="flash flash-error" style="max-width:700px;margin:0 auto 24px;border-radius:12px;"><?= h($error) ?></div>
  <?php endif; ?>

  <?php if ($order): ?>
  <div class="tracking-card" style="max-width:800px;margin:0 auto;">
    <!-- Order Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:32px;padding-bottom:24px;border-bottom:1px solid var(--border);">
      <div>
        <div style="font-size:0.8rem;color:var(--text2);font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Order Number</div>
        <div style="font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;"><?= h($order['order_number']) ?></div>
        <div style="font-size:0.82rem;color:var(--text2);margin-top:4px;"><?= date('M j, Y — g:i A', strtotime($order['created_at'])) ?></div>
      </div>
      <div>
        <span class="status-badge status-<?= h($order['status']) ?>"><?= h(ucfirst($order['status'])) ?></span>
      </div>
    </div>

    <!-- Timeline -->
    <div class="tracking-timeline" style="margin-bottom:32px;">
      <?php foreach ($statusOrder as $i => $s):
        $isDone = $currentStep !== false && $i <= $currentStep;
        $info   = $steps[$s];
      ?>
      <div class="tracking-step <?= $isDone ? 'done' : '' ?>">
        <div class="tracking-dot">
          <i class="fas <?= $info['icon'] ?>"></i>
        </div>
        <div class="tracking-info">
          <h4><?= $info['label'] ?></h4>
          <p><?= $info['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Items -->
    <?php if ($orderItems): ?>
    <div style="border-top:1px solid var(--border);padding-top:24px;">
      <h3 style="font-size:1rem;margin-bottom:16px;">Order Items</h3>
      <div style="display:grid;gap:12px;">
        <?php foreach ($orderItems as $item): ?>
        <div style="display:flex;gap:12px;align-items:center;padding:12px;background:var(--surface);border-radius:10px;">
          <img src="<?= h($item['thumb'] ?: defaultProductImage()) ?>" style="width:52px;height:52px;object-fit:cover;border-radius:8px;" alt="">
          <div style="flex:1;">
            <div style="font-weight:600;font-size:0.9rem;"><?= h($item['name']) ?></div>
            <div style="font-size:0.78rem;color:var(--text2);">Qty: <?= $item['quantity'] ?></div>
          </div>
          <div style="font-weight:700;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
        <strong style="font-size:1.1rem;">Total: <?= formatPrice($order['total_amount']) ?></strong>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
