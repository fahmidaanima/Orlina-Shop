<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin('/pages/login.php');
define('PAGE_TITLE', 'Checkout');

$items   = getCartItems();
if (!$items) { header('Location: ' . url('pages/cart.php')); exit; }

$total   = cartTotal();
$shipping = $total >= 2000 ? 0 : 80;
$grand   = $total + $shipping;
$user    = currentUser();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $firstName  = sanitize($_POST['first_name'] ?? '');
    $lastName   = sanitize($_POST['last_name']  ?? '');
    $email      = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone      = sanitize($_POST['phone'] ?? '');
    $address    = sanitize($_POST['address'] ?? '');
    $city       = sanitize($_POST['city'] ?? '');
    $payMethod  = sanitize($_POST['payment_method'] ?? 'cod');

    $shippingAddr = "$firstName $lastName\n$address, $city\nPhone: $phone\nEmail: $email";

    try {
        $pdo->beginTransaction();

        $orderNum = generateOrderNumber();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id,order_number,total_amount,status,payment_method,payment_status,shipping_address) VALUES (?,?,?,'pending',?,?,'unpaid')");
        $stmt->execute([$_SESSION['user_id'], $orderNum, $grand, $payMethod, 'unpaid']);
        $orderId = $pdo->lastInsertId();

        foreach ($items as $item) {
            $stmt2 = $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)");
            $stmt2->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            $pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?")->execute([$item['quantity'],$item['id']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];

        if ($payMethod === 'stripe') {
            header('Location: ' . url('pages/stripe_checkout.php') . '?order=' . $orderId); exit;
        }

        $_SESSION['last_order_number'] = $orderNum;
        setFlash('success', 'Order placed successfully!');
        header('Location: ' . url('pages/order_success.php') . '?order=' . urlencode($orderNum)); exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Something went wrong. Please try again.');
    }
}

include __DIR__ . '/../includes/header.php';
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
  <form method="POST" class="checkout-layout">
    <?= csrf_field() ?>

    <div>
      <!-- Shipping Info -->
      <div class="checkout-section">
        <h3><i class="fas fa-map-marker-alt" style="color:var(--primary-start);margin-right:8px;"></i>Shipping Information</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" value="<?= h($user['first_name'] ?? '') ?>" required placeholder="Fahmida">
          </div>
          <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" value="<?= h($user['last_name'] ?? '') ?>" required placeholder="Islam">
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Phone *</label>
            <input type="tel" name="phone" value="<?= h($user['phone'] ?? '') ?>" required placeholder="+880 1700-000000">
          </div>
          <div class="form-group full">
            <label>Address *</label>
            <input type="text" name="address" value="<?= h($user['address'] ?? '') ?>" required placeholder="House/Road/Area">
          </div>
          <div class="form-group">
            <label>City *</label>
            <input type="text" name="city" value="<?= h($user['city'] ?? '') ?>" required placeholder="Dhaka">
          </div>
          <div class="form-group">
            <label>Country</label>
            <select name="country">
              <option value="BD" selected>Bangladesh</option>
              <option value="US">United States</option>
              <option value="GB">United Kingdom</option>
              <option value="CA">Canada</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div class="checkout-section">
        <h3><i class="fas fa-credit-card" style="color:var(--primary-start);margin-right:8px;"></i>Payment Method</h3>
        <div class="payment-methods">
          <label class="payment-method selected">
            <input type="radio" name="payment_method" value="cod" checked>
            <span class="payment-icon">💵</span>
            <div><div class="payment-label">Cash on Delivery</div><div class="payment-desc">Pay when your order arrives</div></div>
          </label>
          <label class="payment-method">
            <input type="radio" name="payment_method" value="bkash">
            <span class="payment-icon" style="font-size:1rem;font-weight:700;color:#e2136e;">bKash</span>
            <div><div class="payment-label">bKash</div><div class="payment-desc">Pay via bKash mobile banking</div></div>
          </label>
          <label class="payment-method">
            <input type="radio" name="payment_method" value="nagad">
            <span class="payment-icon" style="font-size:1rem;font-weight:700;color:#f6891f;">Nagad</span>
            <div><div class="payment-label">Nagad</div><div class="payment-desc">Pay via Nagad mobile banking</div></div>
          </label>
          <label class="payment-method">
            <input type="radio" name="payment_method" value="stripe">
            <span class="payment-icon">💳</span>
            <div><div class="payment-label">Credit / Debit Card</div><div class="payment-desc">Visa, Mastercard via Stripe</div></div>
          </label>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="order-summary-card">
        <h3 style="margin-bottom:16px;">Your Order</h3>
        <div class="order-items-list">
          <?php foreach ($items as $item): ?>
          <div class="order-item">
            <img src="<?= h($item['thumb'] ?: defaultProductImage()) ?>" alt="<?= h($item['name']) ?>">
            <div class="order-item-info">
              <div class="order-item-name"><?= h($item['name']) ?></div>
              <div class="order-item-qty">× <?= $item['quantity'] ?></div>
            </div>
            <div class="order-item-price"><?= formatPrice($item['subtotal']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($total) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span class="summary-shipping"><?= $shipping === 0 ? 'FREE' : formatPrice($shipping) ?></span></div>
        <div class="summary-row"><span class="summary-total">Total</span><span class="summary-total"><?= formatPrice($grand) ?></span></div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:16px;">
          <i class="fas fa-lock"></i> Place Order
        </button>
        <div style="margin-top:12px;text-align:center;font-size:0.78rem;color:var(--text2);">
          <i class="fas fa-shield-alt"></i> Your information is secure and encrypted
        </div>
      </div>
    </div>
  </form>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
