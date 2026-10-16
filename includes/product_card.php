<?php
// $p must be set before including this file
$_thumb = $p['thumb'] ?? '';
if (!$_thumb) $_thumb = defaultProductImage($p['category_name'] ?? '');
$_hasDiscount = !empty($p['old_price']) && $p['old_price'] > $p['price'];
$_discPct = $_hasDiscount ? round(($p['old_price'] - $p['price']) / $p['old_price'] * 100) : 0;
$_productUrl = url('pages/product.php') . '?slug=' . h($p['slug']);
$_wishlisted  = isWishlisted($p['id']);
?>
<div class="product-card">

  <!-- Image area -->
  <div class="product-img-wrap">
    <!-- Fully clickable image → product detail -->
    <a href="<?= $_productUrl ?>" class="product-img-link" tabindex="-1" aria-label="View <?= h($p['name']) ?>">
      <img src="<?= h($_thumb) ?>" alt="<?= h($p['name']) ?>" class="product-img" loading="lazy">
    </a>

    <!-- Badges (bottom-left) -->
    <?php if (!empty($p['badge'])): ?>
    <span class="product-badge badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span>
    <?php endif; ?>
    <?php if ($_hasDiscount): ?>
    <span class="product-badge badge-discount">-<?= $_discPct ?>%</span>
    <?php endif; ?>

    <!-- Wishlist heart — always visible in top-right corner -->
    <button class="product-wishlist-btn<?= $_wishlisted ? ' wishlisted' : '' ?>"
            data-wishlist="<?= $p['id'] ?>"
            title="<?= $_wishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' ?>"
            aria-label="<?= $_wishlisted ? 'Remove from wishlist' : 'Add to wishlist' ?>">
      <i class="<?= $_wishlisted ? 'fas' : 'far' ?> fa-heart"></i>
    </button>
  </div>

  <!-- Info area -->
  <div class="product-info">
    <div class="product-category"><?= h($p['category_name'] ?? '') ?></div>

    <!-- Product name → product detail -->
    <h3 class="product-name">
      <a href="<?= $_productUrl ?>" class="product-name-link"><?= h($p['name']) ?></a>
    </h3>

    <div class="product-price">
      <span class="price-current"><?= formatPrice($p['price']) ?></span>
      <?php if ($_hasDiscount): ?>
      <span class="price-old"><?= formatPrice($p['old_price']) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Premium Add to Cart button -->
  <button class="product-add-btn" data-add-cart="<?= $p['id'] ?>"
          aria-label="Add <?= h($p['name']) ?> to cart">
    <i class="fas fa-shopping-bag"></i>
    <span>Add to Cart</span>
  </button>
</div>
