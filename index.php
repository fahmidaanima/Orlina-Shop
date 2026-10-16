<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Home');
define('PAGE_DESC', 'Luxury women\'s fashion — dresses, sarees, abayas, jewelry and more. Premium quality at Orlina.');

$featuredProducts = getFeaturedProducts(8);
$newArrivals      = getNewArrivals(6);
$deal             = getDealOfTheDay();
$categories       = getCategories();

$categoryImages = [
  'dresses'  => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=200&h=200&fit=crop',
  'sarees'   => 'https://images.unsplash.com/photo-1583391733956-62e5a2cda5be?w=200&h=200&fit=crop',
  'abayas'   => 'https://images.unsplash.com/photo-1594463750939-ebb28c3f7f75?w=200&h=200&fit=crop',
  'handbags' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=200&h=200&fit=crop',
  'jewelry'  => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=200&h=200&fit=crop',
  'shoes'    => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=200&h=200&fit=crop',
  'skincare' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=200&h=200&fit=crop',
];

$testimonials = [
  ['name'=>'Nusrat Jahan','location'=>'Dhaka','avatar'=>'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&h=100&fit=crop&facepad=2','text'=>'Absolutely love the quality of Orlina\'s dresses. The fabric is premium and the designs are breathtaking. Highly recommend!','rating'=>5],
  ['name'=>'Anika Rahman','location'=>'Chittagong','avatar'=>'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&facepad=2','text'=>'The saree I ordered arrived beautifully packaged. Fast delivery and the quality exceeded my expectations. Will shop again!','rating'=>5],
  ['name'=>'Sadia Islam','location'=>'Sylhet','avatar'=>'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&h=100&fit=crop&facepad=2','text'=>'Orlina has the most elegant collection. I found my perfect abaya here and the customer service was wonderful.','rating'=>4],
];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-text">
        <span class="hero-eyebrow">✦ New Collection 2026</span>
        <h1 class="hero-headline">
          Luxury,<br>Redefined<br>for <em>Her.</em>
        </h1>
        <p class="hero-sub">Premium women's fashion curated from the finest fabrics. Elegant, timeless, and made for the modern woman.</p>
        <div class="hero-ctas">
          <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary">
            <i class="fas fa-shopping-bag"></i> Shop Collection
          </a>
          <a href="<?= url('pages/shop.php') ?>?badge=NEW" class="btn btn-outline">New Arrivals</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-value">12K+</div>
            <div class="hero-stat-label">Happy Customers</div>
          </div>
          <div>
            <div class="hero-stat-value">500+</div>
            <div class="hero-stat-label">Premium Products</div>
          </div>
          <div>
            <div class="hero-stat-value">7-Day</div>
            <div class="hero-stat-label">Easy Returns</div>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <img src="https://images.unsplash.com/photo-1591348278999-ee1d0c06ed7b?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Luxury Fashion" class="hero-img-main">
        <div class="hero-badge-card">
          <div class="hero-badge-icon"><i class="fas fa-star"></i></div>
          <div class="hero-badge-text">
            <strong>4.9 / 5 Rating</strong>
            <span>Based on 3,200+ reviews</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES STRIP -->
<div class="features-strip">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item">
        <div class="feature-icon"><i class="fas fa-plane"></i></div>
        <div><div class="feature-title">Worldwide Shipping</div><div class="feature-desc">Fast delivery everywhere</div></div>
      </div>
      <div class="feature-item">
        <div class="feature-icon"><i class="fas fa-lock"></i></div>
        <div><div class="feature-title">Secure Payment</div><div class="feature-desc">100% safe transactions</div></div>
      </div>
      <div class="feature-item">
        <div class="feature-icon"><i class="fas fa-undo"></i></div>
        <div><div class="feature-title">7-Day Returns</div><div class="feature-desc">Hassle-free returns</div></div>
      </div>
      <div class="feature-item">
        <div class="feature-icon"><i class="fas fa-gem"></i></div>
        <div><div class="feature-title">Premium Quality</div><div class="feature-desc">Finest materials used</div></div>
      </div>
      <div class="feature-item">
        <div class="feature-icon"><i class="fas fa-headset"></i></div>
        <div><div class="feature-title">24/7 Support</div><div class="feature-desc">Always here for you</div></div>
      </div>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✦ Browse by Category</span>
      <h2 class="section-title">Shop Your Style</h2>
      <p class="section-subtitle">From everyday elegance to special occasion glamour</p>
    </div>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= url('pages/shop.php') ?>?cat=<?= h($cat['slug']) ?>" class="category-card">
        <img
          src="<?= $categoryImages[$cat['slug']] ?? 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=200&h=200&fit=crop' ?>"
          alt="<?= h($cat['name']) ?>"
          class="category-img"
          loading="lazy"
        >
        <div class="category-name"><?= h($cat['name']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<?php if ($featuredProducts): ?>
<section class="section" style="background:var(--surface);">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✦ Curated For You</span>
      <h2 class="section-title">Featured Products</h2>
      <p class="section-subtitle">Handpicked pieces our stylists love</p>
    </div>
    <div class="products-grid">
      <?php foreach ($featuredProducts as $p): ?>
      <?php include __DIR__ . '/includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
      <a href="<?= url('pages/shop.php') ?>" class="btn btn-outline">View All Products</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- DEAL OF THE DAY -->
<?php if ($deal): ?>
<section class="deal-section">
  <div class="container">
    <div class="deal-grid">
      <div>
        <?php $dealImg = $deal['thumb'] ?: defaultProductImage($deal['category_name'] ?? ''); ?>
        <img src="<?= h($dealImg) ?>" alt="<?= h($deal['name']) ?>" class="deal-img" loading="lazy">
      </div>
      <div>
        <div class="deal-badge"><i class="fas fa-fire"></i> Deal of the Day</div>
        <h2 class="deal-title"><?= h($deal['name']) ?></h2>
        <p class="deal-desc"><?= h(substr($deal['description'] ?? 'Premium quality product at an unbeatable price.', 0, 200)) ?></p>
        <div class="deal-price product-price">
          <span class="price-current"><?= formatPrice($deal['price']) ?></span>
          <?php if ($deal['old_price']): ?>
          <span class="price-old"><?= formatPrice($deal['old_price']) ?></span>
          <span class="price-discount">-<?= $deal['discount_pct'] ?>% OFF</span>
          <?php endif; ?>
        </div>

        <?php $endTime = new DateTime('+1 day'); ?>
        <span id="countdown-end" data-end="<?= $endTime->format('Y-m-d H:i:s') ?>"></span>
        <div class="countdown">
          <div class="countdown-item"><span class="countdown-value" id="cd-hours">00</span><span class="countdown-label">Hours</span></div>
          <div class="countdown-item"><span class="countdown-value" id="cd-mins">00</span><span class="countdown-label">Mins</span></div>
          <div class="countdown-item"><span class="countdown-value" id="cd-secs">00</span><span class="countdown-label">Secs</span></div>
        </div>

        <div style="display:flex;gap:16px;flex-wrap:wrap;">
          <button class="btn btn-primary" data-add-cart="<?= $deal['id'] ?>">
            <i class="fas fa-shopping-bag"></i> Add to Cart
          </button>
          <a href="<?= url('pages/product.php') ?>?slug=<?= h($deal['slug']) ?>" class="btn btn-outline">View Details</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- NEW ARRIVALS -->
<?php if ($newArrivals): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✦ Just Landed</span>
      <h2 class="section-title">New Arrivals</h2>
      <p class="section-subtitle">Fresh styles added to our collection this week</p>
    </div>
    <div class="products-grid products-grid-3">
      <?php foreach (array_slice($newArrivals, 0, 6) as $p): ?>
      <?php include __DIR__ . '/includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
      <a href="<?= url('pages/shop.php') ?>?sort=newest" class="btn btn-outline">See All New Arrivals</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS -->
<section class="section" style="background:var(--surface);">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✦ What They Say</span>
      <h2 class="section-title">Loved by Thousands</h2>
      <p class="section-subtitle">Real stories from our happy customers across Bangladesh</p>
    </div>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial-card">
        <div class="testimonial-stars">
          <?php for ($i=0;$i<$t['rating'];$i++) echo '<i class="fas fa-star"></i>'; ?>
        </div>
        <p class="testimonial-text">"<?= h($t['text']) ?>"</p>
        <div class="testimonial-author">
          <img src="<?= h($t['avatar']) ?>" alt="<?= h($t['name']) ?>" class="testimonial-avatar" loading="lazy">
          <div>
            <div class="testimonial-name"><?= h($t['name']) ?></div>
            <div class="testimonial-location"><?= h($t['location']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BLOG PREVIEW -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✦ From the Blog</span>
      <h2 class="section-title">Style &amp; Inspiration</h2>
      <p class="section-subtitle">Fashion tips, trends and stories for the modern woman</p>
    </div>
    <div class="blog-grid">
      <a href="<?= url('pages/blog.php') ?>" class="blog-card">
        <div class="blog-img-wrap"><img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&h=340&fit=crop" alt="Blog" class="blog-img" loading="lazy"></div>
        <div class="blog-body">
          <div class="blog-cat">Fashion Tips</div>
          <h3 class="blog-title">10 Ways to Style Your Saree for Every Occasion</h3>
          <div class="blog-meta"><i class="far fa-clock"></i> June 28, 2026 &bull; 5 min read</div>
        </div>
      </a>
      <a href="<?= url('pages/blog.php') ?>" class="blog-card">
        <div class="blog-img-wrap"><img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&h=340&fit=crop" alt="Blog" class="blog-img" loading="lazy"></div>
        <div class="blog-body">
          <div class="blog-cat">Trends 2026</div>
          <h3 class="blog-title">Summer Dress Trends Every Bangladeshi Woman Must Know</h3>
          <div class="blog-meta"><i class="far fa-clock"></i> June 20, 2026 &bull; 4 min read</div>
        </div>
      </a>
      <a href="<?= url('pages/blog.php') ?>" class="blog-card">
        <div class="blog-img-wrap"><img src="https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&h=340&fit=crop" alt="Blog" class="blog-img" loading="lazy"></div>
        <div class="blog-body">
          <div class="blog-cat">Skincare</div>
          <h3 class="blog-title">The Ultimate Skincare Routine for Humid Weather</h3>
          <div class="blog-meta"><i class="far fa-clock"></i> June 15, 2026 &bull; 6 min read</div>
        </div>
      </a>
    </div>
    <div class="text-center mt-8">
      <a href="<?= url('pages/blog.php') ?>" class="btn btn-outline">Visit Our Blog</a>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="newsletter-section">
  <div class="container">
    <h2>Join the Orlina Circle</h2>
    <p>Get exclusive offers, style tips and first access to new collections. No spam, ever.</p>
    <form class="newsletter-form" onsubmit="event.preventDefault();this.querySelector('button').textContent='Subscribed! ✓';">
      <input type="email" placeholder="Your email address" required aria-label="Email address">
      <button type="submit">Subscribe</button>
    </form>
  </div>
</section>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
