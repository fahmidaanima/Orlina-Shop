<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Track Order');

$orderNum = trim($_GET['order'] ?? $_POST['order_number'] ?? '');
$order    = null;
$orderItems = [];

if ($orderNum) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNum]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt2 = $pdo->prepare("SELECT oi.*, p.name, p.slug, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        $stmt2->execute([$order['id']]);
        $orderItems = $stmt2->fetchAll();
    }
}

$steps = [
  'pending'    => 0,
  'processing' => 1,
  'shipped'    => 2,
  'delivered'  => 3,
];
$currentStep = $steps[$order['status'] ?? 'pending'] ?? 0;

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Track Your Order</h1>
    <p>Enter your order number to see real-time updates</p>
  </div>
</div>

<div class="container" style="padding:48px 24px;">

  <!-- Search Form -->
  <div style="max-width:560px;margin:0 auto 48px;">
    <form method="POST" style="display:flex;gap:12px;">
      <input type="text" name="order_number" value="<?= h($orderNum) ?>" placeholder="Enter order number (e.g. ORL-ABC123-20260706)" style="flex:1;padding:14px 20px;border:1.5px solid var(--border);border-radius:50px;font-family:'Outfit',sans-serif;font-size:0.95rem;outline:none;">
      <button type="submit" class="btn btn-primary">Track</button>
    </form>
  </div>

  <?php if ($orderNum && !$order): ?>
  <div class="empty-state">
    <i class="fas fa-search"></i>
    <h3>Order not found</h3>
    <p>Please check your order number and try again.</p>
  </div>
  <?php elseif ($order): ?>

  <!-- Order Info -->
  <div style="max-width:800px;margin:0 auto;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px;">
      <div style="background:var(--surface);border-radius:var(--radius);padding:20px;border:1px solid var(--border);">
        <div style="font-size:0.8rem;color:var(--text2);margin-bottom:4px;">Order Number</div>
        <div style="font-weight:700;font-size:1.1rem;"><?= h($order['order_number']) ?></div>
      </div>
      <div style="background:var(--surface);border-radius:var(--radius);padding:20px;border:1px solid var(--border);">
        <div style="font-size:0.8rem;color:var(--text2);margin-bottom:4px;">Order Date</div>
        <div style="font-weight:600;"><?= date('F j, Y', strtotime($order['created_at'])) ?></div>
      </div>
      <div style="background:var(--surface);border-radius:var(--radius);padding:20px;border:1px solid var(--border);">
        <div style="font-size:0.8rem;color:var(--text2);margin-bottom:4px;">Total Amount</div>
        <div style="font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:1.1rem;"><?= formatPrice($order['total_amount']) ?></div>
      </div>
      <div style="background:var(--surface);border-radius:var(--radius);padding:20px;border:1px solid var(--border);">
        <div style="font-size:0.8rem;color:var(--text2);margin-bottom:4px;">Payment</div>
        <div><span class="status-badge status-<?= h($order['payment_status']) ?>"><?= h(ucfirst($order['payment_status'])) ?></span></div>
      </div>
    </div>

    <!-- Tracking Steps -->
    <div style="background:var(--surface);border-radius:var(--radius);padding:32px;border:1px solid var(--border);margin-bottom:32px;">
      <h3 style="margin-bottom:28px;">Delivery Status</h3>
      <div class="tracking-steps">
        <?php
        $trackSteps = [
          ['icon'=>'fa-clipboard-check','label'=>'Order Placed'],
          ['icon'=>'fa-cog','label'=>'Processing'],
          ['icon'=>'fa-shipping-fast','label'=>'Shipped'],
          ['icon'=>'fa-box-open','label'=>'Delivered'],
        ];
        foreach ($trackSteps as $i => $s):
          $isDone   = $i < $currentStep;
          $isActive = $i === $currentStep;
        ?>
        <div class="tracking-step <?= $isDone ? 'done' : ($isActive ? 'active' : '') ?>">
          <div class="step-icon">
            <i class="fas <?= $s['icon'] ?>"></i>
          </div>
          <div class="step-label"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:24px;padding:16px;background:#fff;border-radius:10px;display:flex;align-items:center;gap:12px;">
        <span class="status-badge status-<?= h($order['status']) ?>" style="font-size:0.9rem;padding:8px 16px;"><?= h(ucfirst($order['status'])) ?></span>
        <span style="font-size:0.9rem;color:var(--text2);">
          <?php
          $msgs = ['pending'=>'Your order has been received and is awaiting confirmation.','processing'=>'Your order is being carefully packed by our team.','shipped'=>'Your order is on its way! Expected delivery in 2-3 days.','delivered'=>'Your order has been delivered. Enjoy your purchase!','cancelled'=>'This order has been cancelled.'];
          echo $msgs[$order['status']] ?? 'Order status updated.';
          ?>
        </span>
      </div>
    </div>

    <!-- Order Items -->
    <?php if ($orderItems): ?>
    <div style="background:var(--surface);border-radius:var(--radius);padding:24px;border:1px solid var(--border);">
      <h3 style="margin-bottom:20px;">Order Items</h3>
      <?php foreach ($orderItems as $item): ?>
      <div style="display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid var(--border);">
        <img src="<?= h($item['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=80&h=80&fit=crop') ?>" style="width:64px;height:64px;border-radius:10px;object-fit:cover;" alt="">
        <div style="flex:1;">
          <div style="font-weight:600;"><?= h($item['name']) ?></div>
          <div style="font-size:0.85rem;color:var(--text2);">Qty: <?= $item['quantity'] ?></div>
        </div>
        <div style="font-weight:600;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php elseif (!$orderNum): ?>
  <div style="text-align:center;padding:40px;color:var(--text2);">
    <i class="fas fa-box-open" style="font-size:3rem;margin-bottom:16px;opacity:0.3;"></i>
    <p>Enter your order number above to track your package.</p>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
