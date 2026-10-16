<?php
// Expects $p = product row with thumb, category_name, etc.
$thumb = $p['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=400&h=533&fit=crop';
$rating = getProductRating($p['id']);
$wishlisted = isWishlisted($p['id']);
$discountPct = ($p['old_price'] && $p['old_price'] > $p['price'])
  ? round(($p['old_price'] - $p['price']) / $p['old_price'] * 100)
  : 0;
?>
<div class="product-card">
  <div class="product-img-wrap">
    <a href="<?= url('pages/product.php') ?>?slug=<?= h($p['slug']) ?>">
      <img src="<?= h($thumb) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
    </a>
    <?php if ($p['badge']): ?>
    <span class="product-badge badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span>
    <?php endif; ?>
    <div class="product-actions">
      <button class="action-btn <?= $wishlisted ? 'wishlisted' : '' ?>" data-wishlist="<?= $p['id'] ?>" title="Wishlist">
        <i class="<?= $wishlisted ? 'fas' : 'far' ?> fa-heart"></i>
      </button>
      <a href="<?= url('pages/product.php') ?>?slug=<?= h($p['slug']) ?>" class="action-btn" title="Quick View">
        <i class="fas fa-eye"></i>
      </a>
    </div>
  </div>
  <div class="product-info">
    <div class="product-cat"><?= h($p['category_name'] ?? '') ?></div>
    <div class="product-name">
      <a href="<?= url('pages/product.php') ?>?slug=<?= h($p['slug']) ?>"><?= h($p['name']) ?></a>
    </div>
    <?php if ($rating['count'] > 0): ?>
    <div class="product-stars">
      <?= starRating((float)$rating['avg_rating']) ?>
      <span class="review-count">(<?= $rating['count'] ?>)</span>
    </div>
    <?php endif; ?>
    <div class="product-price">
      <span class="price-current"><?= formatPrice($p['price']) ?></span>
      <?php if ($p['old_price']): ?>
      <span class="price-old"><?= formatPrice($p['old_price']) ?></span>
      <?php if ($discountPct > 0): ?>
      <span class="price-discount">-<?= $discountPct ?>%</span>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
  <button class="product-add-btn" data-add-cart="<?= $p['id'] ?>">
    <i class="fas fa-shopping-bag"></i> Add to Cart
  </button>
</div>
