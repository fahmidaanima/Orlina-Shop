<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Shopping Cart');

$items = getCartItems();
$total = cartTotal();

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Shopping Cart</h1>
    <p><?= cartCount() ?> items in your cart</p>
  </div>
</div>

<div class="container">
  <?php if ($items): ?>
  <div class="cart-layout">

    <!-- Cart Items -->
    <div>
      <table class="cart-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="cart-body">
          <?php foreach ($items as $item): ?>
          <tr id="cart-row-<?= $item['id'] ?>">
            <td>
              <div class="cart-product">
                <img src="<?= h($item['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=120&h=120&fit=crop') ?>" alt="<?= h($item['name']) ?>" class="cart-product-img">
                <div>
                  <div class="cart-product-name"><?= h($item['name']) ?></div>
                  <div class="cart-product-cat">SKU: ORL-<?= str_pad($item['id'],4,'0',STR_PAD_LEFT) ?></div>
                </div>
              </div>
            </td>
            <td><?= formatPrice($item['price']) ?></td>
            <td>
              <div class="quantity-selector" style="transform:scale(0.85);transform-origin:left;">
                <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)"><i class="fas fa-minus"></i></button>
                <input type="number" id="qty-<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" style="width:48px;text-align:center;border:none;font-weight:600;font-size:0.95rem;" data-cart-qty="<?= $item['id'] ?>">
                <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)"><i class="fas fa-plus"></i></button>
              </div>
            </td>
            <td><strong data-subtotal="<?= $item['id'] ?>"><?= formatPrice($item['subtotal']) ?></strong></td>
            <td>
              <form method="POST" action="<?= url('pages/cart_action.php') ?>" data-remove-cart>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <button type="submit" class="remove-btn" title="Remove">
                  <i class="fas fa-times"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="display:flex;gap:12px;margin-top:20px;">
        <a href="<?= url('pages/shop.php') ?>" class="btn btn-outline btn-sm">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="cart-summary">
      <h3 class="summary-title">Order Summary</h3>

      <div class="summary-row">
        <span>Subtotal</span>
        <span id="cart-total"><?= formatPrice($total) ?></span>
      </div>
      <div class="summary-row">
        <span>Shipping</span>
        <span class="summary-shipping"><?= $total >= 2000 ? 'FREE' : formatPrice(80) ?></span>
      </div>
      <?php $shipping = $total >= 2000 ? 0 : 80; ?>
      <div class="summary-row">
        <span class="summary-total">Total</span>
        <span class="summary-total"><?= formatPrice($total + $shipping) ?></span>
      </div>

      <div class="coupon-form">
        <input type="text" placeholder="Coupon code">
        <button class="btn btn-outline btn-sm">Apply</button>
      </div>

      <a href="<?= url('pages/checkout.php') ?>" class="btn btn-primary btn-full">
        <i class="fas fa-lock"></i> Proceed to Checkout
      </a>
      <div style="margin-top:16px;text-align:center;font-size:0.8rem;color:var(--text2);">
        <i class="fas fa-shield-alt"></i> Secure &amp; Encrypted Checkout
      </div>
      <div style="margin-top:16px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;font-size:0.75rem;color:var(--text2);">
        <span>bKash</span>·<span>Nagad</span>·<span>Visa</span>·<span>Mastercard</span>·<span>Stripe</span>
      </div>
    </div>
  </div>

  <?php else: ?>
  <div class="empty-state" style="padding:80px 24px;">
    <i class="fas fa-shopping-bag"></i>
    <h3>Your cart is empty</h3>
    <p>Discover our beautiful collection and add something you love.</p>
    <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">
      <i class="fas fa-shopping-bag"></i> Start Shopping
    </a>
  </div>
  <?php endif; ?>
</div>

<script>
window.__csrf = '<?= csrf_token() ?>';
function updateQty(pid, delta) {
  const input = document.getElementById('qty-' + pid);
  const newVal = Math.max(1, parseInt(input.value) + delta);
  input.value = newVal;
  input.dispatchEvent(new Event('change'));
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
