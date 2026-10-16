<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$product = getProductBySlug($slug);
if (!$product) { header('Location: ' . url('pages/shop.php')); exit; }

$images  = getProductImages($product['id']);
if (!$images) $images = ['https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&h=800&fit=crop'];
$mainImg = $images[0];

$reviews = getProductReviews($product['id']);
$rating  = getProductRating($product['id']);
$related = $pdo->prepare("
    SELECT p.*, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb,
           c.name AS category_name
    FROM products p LEFT JOIN categories c ON p.category_id=c.id
    WHERE p.category_id=? AND p.id!=? AND p.stock>0 LIMIT 4
");
$related->execute([$product['category_id'], $product['id']]);
$relatedProducts = $related->fetchAll();

define('PAGE_TITLE', h($product['name']));

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!isLoggedIn()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Please login to submit a review.']; }
    elseif (!verify_csrf()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Security error.']; }
    else {
        $ratingVal  = max(1, min(5, (int)$_POST['rating']));
        $reviewText = trim($_POST['review_text'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id,user_id,rating,review_text,status) VALUES (?,?,?,?,'pending')");
        $stmt->execute([$product['id'], $_SESSION['user_id'], $ratingVal, $reviewText]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Review submitted! It will appear after moderation.'];
        header('Location: ' . url('pages/product.php') . '?slug=' . urlencode($slug)); exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <nav class="breadcrumb">
    <a href="<?= url('index.php') ?>">Home</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= url('pages/shop.php') ?>">Shop</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= url('pages/shop.php') ?>?cat=<?= h($product['slug'] ?? '') ?>"><?= h($product['category_name'] ?? '') ?></a>
    <i class="fas fa-chevron-right"></i>
    <span><?= h($product['name']) ?></span>
  </nav>
</div>

<div class="container">
  <div class="product-detail-grid">

    <!-- GALLERY ─────────────────────────── -->
    <div class="product-gallery">
      <div class="gallery-main">
        <img id="gallery-main-img" src="<?= h($mainImg) ?>" alt="<?= h($product['name']) ?>">
      </div>
      <?php if (count($images) > 1): ?>
      <div class="gallery-thumbs">
        <?php foreach ($images as $i => $img): ?>
        <div class="gallery-thumb <?= $i===0?'active':'' ?>" data-src="<?= h($img) ?>">
          <img src="<?= h($img) ?>" alt="Product image">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- INFO ──────────────────────────── -->
    <div class="product-detail-info">
      <div class="product-detail-brand"><?= h($product['category_name'] ?? 'Orlina') ?></div>
      <h1 class="product-detail-name"><?= h($product['name']) ?></h1>

      <div class="product-detail-rating">
        <?php if ($rating['count'] > 0): ?>
        <?= starRating((float)$rating['avg_rating']) ?>
        <span style="font-weight:600;"><?= number_format($rating['avg_rating'],1) ?></span>
        <span class="text-muted">(<?= $rating['count'] ?> reviews)</span>
        <?php else: ?>
        <span class="text-muted">No reviews yet</span>
        <?php endif; ?>
      </div>

      <div class="product-detail-price product-price">
        <span class="price-current"><?= formatPrice($product['price']) ?></span>
        <?php if ($product['old_price']): ?>
        <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
        <?php $disc = round(($product['old_price']-$product['price'])/$product['old_price']*100); ?>
        <span class="price-discount">-<?= $disc ?>% OFF</span>
        <?php endif; ?>
      </div>

      <?php if ($product['description']): ?>
      <p style="color:var(--text2);margin-bottom:24px;line-height:1.8;"><?= h($product['description']) ?></p>
      <?php endif; ?>

      <!-- Quantity -->
      <div class="quantity-selector">
        <button class="qty-btn" id="qty-minus"><i class="fas fa-minus"></i></button>
        <input type="number" id="qty-input" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
        <button class="qty-btn" id="qty-plus"><i class="fas fa-plus"></i></button>
      </div>

      <div class="product-detail-actions">
        <button class="btn btn-primary" data-add-cart="<?= $product['id'] ?>">
          <i class="fas fa-shopping-bag"></i> Add to Cart
        </button>
        <button class="btn btn-outline <?= isWishlisted($product['id']) ? 'wishlisted' : '' ?>" data-wishlist="<?= $product['id'] ?>">
          <i class="<?= isWishlisted($product['id']) ? 'fas' : 'far' ?> fa-heart"></i>
          Wishlist
        </button>
      </div>

      <div class="product-detail-meta">
        <div class="meta-row">
          <span class="meta-label">SKU:</span>
          <span class="meta-value">ORL-<?= str_pad($product['id'],4,'0',STR_PAD_LEFT) ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Category:</span>
          <span class="meta-value"><?= h($product['category_name'] ?? '') ?></span>
        </div>
        <?php if ($product['badge']): ?>
        <div class="meta-row">
          <span class="meta-label">Tag:</span>
          <span class="meta-value"><span class="product-badge badge-<?= h($product['badge']) ?>"><?= h($product['badge']) ?></span></span>
        </div>
        <?php endif; ?>
        <div class="meta-row">
          <span class="meta-label">Stock:</span>
          <span class="meta-value" style="color:<?= $product['stock'] > 0 ? '#22c55e' : '#ef4444' ?>">
            <?= $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' left)' : 'Out of Stock' ?>
          </span>
        </div>
        <div class="meta-row" style="margin-top:16px;gap:20px;">
          <a href="#" style="color:var(--text2);font-size:0.9rem;"><i class="fab fa-facebook-f"></i> Share</a>
          <a href="#" style="color:var(--text2);font-size:0.9rem;"><i class="fab fa-instagram"></i> Share</a>
          <a href="#" style="color:var(--text2);font-size:0.9rem;"><i class="fab fa-whatsapp"></i> Share</a>
        </div>
      </div>
    </div>
  </div>

  <!-- TABS ──────────────────────────────── -->
  <div class="product-tabs">
    <div class="tab-nav">
      <button class="tab-btn active" data-tab="description">Description</button>
      <button class="tab-btn" data-tab="reviews">Reviews (<?= $rating['count'] ?>)</button>
      <button class="tab-btn" data-tab="shipping">Shipping & Returns</button>
    </div>

    <div id="tab-description" class="tab-panel active">
      <p style="line-height:1.9;color:var(--text2);"><?= nl2br(h($product['description'] ?? 'No description available.')) ?></p>
    </div>

    <div id="tab-reviews" class="tab-panel">
      <?php if ($reviews): ?>
      <div style="display:grid;gap:20px;margin-bottom:40px;">
        <?php foreach ($reviews as $rev): ?>
        <div style="padding:24px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
            <img src="<?= $rev['avatar'] ? h($rev['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($rev['first_name'].' '.$rev['last_name']) ?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;" alt="">
            <div>
              <div style="font-weight:600;"><?= h($rev['first_name'].' '.$rev['last_name']) ?></div>
              <div style="font-size:0.8rem;color:var(--text2);"><?= date('M j, Y', strtotime($rev['created_at'])) ?></div>
            </div>
            <div style="margin-left:auto;"><?= starRating((float)$rev['rating']) ?></div>
          </div>
          <?php if ($rev['review_text']): ?>
          <p style="color:var(--text2);"><?= h($rev['review_text']) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Review Form -->
      <?php if (isLoggedIn()): ?>
      <div style="background:var(--surface);padding:32px;border-radius:var(--radius);border:1px solid var(--border);">
        <h3 style="margin-bottom:20px;">Write a Review</h3>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="review_submit" value="1">
          <div class="form-group">
            <label>Rating</label>
            <div class="star-picker">
              <?php for ($i=5;$i>=1;$i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i===5?'checked':'' ?>>
              <label for="star<?= $i ?>">★</label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="form-group">
            <label>Your Review</label>
            <textarea name="review_text" rows="4" placeholder="Share your experience with this product…" style="width:100%;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-family:'Outfit',sans-serif;resize:vertical;"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
      </div>
      <?php else: ?>
      <p class="text-muted"><a href="<?= url('pages/login.php') ?>" style="color:var(--primary-start);">Login</a> to write a review.</p>
      <?php endif; ?>
    </div>

    <div id="tab-shipping" class="tab-panel">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
        <div style="padding:24px;background:var(--surface);border-radius:var(--radius);">
          <h4><i class="fas fa-shipping-fast" style="color:var(--primary-start);margin-right:8px;"></i>Shipping</h4>
          <ul style="margin-top:12px;color:var(--text2);line-height:2;">
            <li>Dhaka: 1–2 business days</li>
            <li>Outside Dhaka: 3–5 business days</li>
            <li>International: 7–14 business days</li>
            <li>Free shipping on orders over ৳2000</li>
          </ul>
        </div>
        <div style="padding:24px;background:var(--surface);border-radius:var(--radius);">
          <h4><i class="fas fa-undo" style="color:var(--primary-start);margin-right:8px;"></i>Returns</h4>
          <ul style="margin-top:12px;color:var(--text2);line-height:2;">
            <li>7-day return policy</li>
            <li>Item must be unused and in original packaging</li>
            <li>Contact support to initiate a return</li>
            <li>Refund processed within 5 business days</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- RELATED ─────────────────────────────── -->
  <?php if ($relatedProducts): ?>
  <div class="section-sm">
    <div class="section-header">
      <span class="section-label">✦ You May Also Like</span>
      <h2 class="section-title">Related Products</h2>
    </div>
    <div class="products-grid">
      <?php foreach ($relatedProducts as $p): ?>
      <?php include __DIR__ . '/includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
