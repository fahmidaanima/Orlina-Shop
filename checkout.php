<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Checkout');

$items = getCartItems();
if (!$items) { header('Location: ' . url('pages/cart.php')); exit; }

$total    = cartTotal();
$shipping = $total >= 2000 ? 0 : 80;
$grandTotal = $total + $shipping;

$settings = getSettings();
$user = currentUser();

// Handle COD / bKash / Nagad order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!verify_csrf()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Security error.']; header('Location: ' . url('pages/checkout.php')); exit; }

    $method = $_POST['payment_method'] ?? 'cod';
    $fname  = trim($_POST['first_name'] ?? '');
    $lname  = trim($_POST['last_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $addr   = trim($_POST['address'] ?? '');
    $city   = trim($_POST['city'] ?? '');

    if (!$fname || !$email || !$phone || !$addr || !$city) {
        $_SESSION['flash'] = ['type'=>'error','message'=>'Please fill in all required fields.'];
        header('Location: ' . url('pages/checkout.php')); exit;
    }

    $shippingAddr = json_encode(compact('fname','lname','email','phone','addr','city'));
    $orderNum     = generateOrderNumber();

    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id,order_number,total_amount,status,payment_method,payment_status,shipping_address)
        VALUES (?,?,?,'pending',?,?,?)
    ");
    $payStatus = ($method === 'cod') ? 'unpaid' : 'unpaid';
    $stmt->execute([
        isLoggedIn() ? $_SESSION['user_id'] : null,
        $orderNum, $grandTotal, $method, $payStatus, $shippingAddr
    ]);
    $orderId = $pdo->lastInsertId();

    foreach ($items as $item) {
        $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)")
            ->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        $pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?")->execute([$item['quantity'], $item['id']]);
    }

    $_SESSION['cart'] = [];
    $_SESSION['last_order'] = $orderNum;

    if ($method === 'stripe') {
        header('Location: ' . url('pages/stripe_checkout.php') . '?order_id=' . $orderId); exit;
    }

    header('Location: ' . url('pages/order_success.php') . '?order=' . urlencode($orderNum)); exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Checkout</h1>
    <nav class="breadcrumb">
      <a href="<?= url('index.php') ?>">Home</a>
      <i class="fas fa-chevron-right"></i>
      <a href="<?= url('pages/cart.php') ?>">Cart</a>
      <i class="fas fa-chevron-right"></i>
      <span>Checkout</span>
    </nav>
  </div>
</div>

<div class="container">
  <form method="POST" action="">
    <?= csrf_field() ?>
    <input type="hidden" name="place_order" value="1">

    <div class="checkout-layout">
      <!-- LEFT: Forms -->
      <div>
        <!-- Shipping Info -->
        <div class="checkout-form-card">
          <h3 class="checkout-form-title"><i class="fas fa-map-marker-alt" style="color:var(--primary-start);margin-right:8px;"></i>Shipping Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" value="<?= h($user['first_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="last_name" value="<?= h($user['last_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email Address *</label>
              <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Phone Number *</label>
              <input type="tel" name="phone" value="<?= h($user['phone'] ?? '') ?>" placeholder="+880 1700-000000" required>
            </div>
            <div class="form-group form-full">
              <label>Street Address *</label>
              <input type="text" name="address" value="<?= h($user['address'] ?? '') ?>" placeholder="House no., Road, Area" required>
            </div>
            <div class="form-group">
              <label>City *</label>
              <input type="text" name="city" value="<?= h($user['city'] ?? '') ?>" placeholder="Dhaka" required>
            </div>
            <div class="form-group">
              <label>Country</label>
              <select name="country">
                <option value="BD" selected>Bangladesh</option>
                <option value="US">United States</option>
                <option value="GB">United Kingdom</option>
                <option value="IN">India</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="checkout-form-card">
          <h3 class="checkout-form-title"><i class="fas fa-credit-card" style="color:var(--primary-start);margin-right:8px;"></i>Payment Method</h3>
          <div class="payment-options">
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_cod" value="cod" checked>
              <label for="pay_cod">
                <i class="fas fa-money-bill-wave" style="color:#22c55e;"></i>
                Cash on Delivery
              </label>
            </div>
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_bkash" value="bkash">
              <label for="pay_bkash">
                <i class="fas fa-mobile-alt" style="color:#e2136e;"></i>
                bKash
              </label>
            </div>
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_nagad" value="nagad">
              <label for="pay_nagad">
                <i class="fas fa-mobile-alt" style="color:#f97316;"></i>
                Nagad
              </label>
            </div>
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_stripe" value="stripe">
              <label for="pay_stripe">
                <i class="fab fa-stripe-s" style="color:#635bff;"></i>
                Stripe (Card)
              </label>
            </div>
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_paypal" value="paypal">
              <label for="pay_paypal">
                <i class="fab fa-paypal" style="color:#003087;"></i>
                PayPal
              </label>
            </div>
            <div class="payment-option">
              <input type="radio" name="payment_method" id="pay_ssl" value="sslcommerz">
              <label for="pay_ssl">
                <i class="fas fa-shield-alt" style="color:#1a56db;"></i>
                SSLCommerz
              </label>
            </div>
          </div>

          <!-- Payment Notes -->
          <div id="pay-note-cod" class="pay-note" style="padding:14px;background:#f0fdf4;border-radius:10px;color:#166534;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> Pay when your order is delivered. No advance payment required.
          </div>
          <div id="pay-note-bkash" class="pay-note" style="display:none;padding:14px;background:#fdf2f8;border-radius:10px;color:#9d174d;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> Send payment to <strong>01700-000000</strong> (Merchant). Use order number as reference.
          </div>
          <div id="pay-note-nagad" class="pay-note" style="display:none;padding:14px;background:#fff7ed;border-radius:10px;color:#9a3412;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> Send payment to <strong>01700-000001</strong> (Merchant). Use order number as reference.
          </div>
          <div id="pay-note-stripe" class="pay-note" style="display:none;padding:14px;background:#f0f4ff;border-radius:10px;color:#1e40af;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> You'll be redirected to a secure Stripe payment page to complete your order.
          </div>
          <div id="pay-note-paypal" class="pay-note" style="display:none;padding:14px;background:#eff6ff;border-radius:10px;color:#1e3a8a;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> You'll be redirected to PayPal to complete payment securely.
          </div>
          <div id="pay-note-sslcommerz" class="pay-note" style="display:none;padding:14px;background:#eff6ff;border-radius:10px;color:#1e3a8a;font-size:0.9rem;">
            <i class="fas fa-info-circle"></i> Pay securely via SSLCommerz — supports all Bangladeshi cards and mobile banking.
          </div>
        </div>

        <div style="padding:16px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);font-size:0.85rem;color:var(--text2);margin-bottom:24px;">
          <i class="fas fa-lock" style="color:var(--primary-start);margin-right:6px;"></i>
          Your personal data will be used to process your order, support your experience, and is protected by our privacy policy.
        </div>
      </div>

      <!-- RIGHT: Summary -->
      <div>
        <div class="cart-summary" style="position:sticky;top:140px;">
          <h3 class="summary-title">Order Summary</h3>
          <?php foreach ($items as $item): ?>
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
            <div style="position:relative;">
              <img src="<?= h($item['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=60&h=60&fit=crop') ?>" style="width:52px;height:52px;border-radius:8px;object-fit:cover;" alt="">
              <span style="position:absolute;top:-6px;right:-6px;background:var(--primary-start);color:#fff;border-radius:50%;width:18px;height:18px;font-size:0.65rem;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= $item['quantity'] ?></span>
            </div>
            <div style="flex:1;font-size:0.88rem;">
              <div style="font-weight:600;"><?= h($item['name']) ?></div>
            </div>
            <div style="font-weight:600;font-size:0.9rem;"><?= formatPrice($item['subtotal']) ?></div>
          </div>
          <?php endforeach; ?>

          <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($total) ?></span></div>
          <div class="summary-row"><span>Shipping</span><span class="summary-shipping"><?= $shipping ? formatPrice($shipping) : 'FREE' ?></span></div>
          <div class="summary-row"><span class="summary-total">Total</span><span class="summary-total"><?= formatPrice($grandTotal) ?></span></div>

          <button type="submit" class="btn btn-primary btn-full" style="margin-top:20px;">
            <i class="fas fa-lock"></i> Place Order
          </button>
          <a href="<?= url('pages/cart.php') ?>" style="display:block;text-align:center;margin-top:12px;font-size:0.85rem;color:var(--text2);">
            <i class="fas fa-arrow-left"></i> Back to Cart
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.pay-note').forEach(n => n.style.display = 'none');
    const note = document.getElementById('pay-note-' + radio.value);
    if (note) note.style.display = 'block';
  });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
