<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$post = null;
if ($slug) {
    $stmt = $pdo->prepare("SELECT bp.*, bc.name AS cat_name, bc.slug AS cat_slug FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id WHERE bp.slug=? AND bp.status='published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
}

// Demo post fallback
if (!$post) {
    $demoPosts = [
        'style-saree-occasions' => ['title'=>'10 Ways to Style Your Saree for Every Occasion','cat_name'=>'Fashion Tips','created_at'=>'2026-06-28 10:00:00','featured_image'=>'https://images.unsplash.com/photo-1583391733956-62e5a2cda5be?w=1200&h=600&fit=crop','content'=>"<p>The saree is one of the most versatile and timeless garments in South Asian fashion. Whether you're attending a wedding, office function, or casual outing, there's always a perfect way to drape and style a saree.</p><h3>1. Classic Nivi Drape</h3><p>The Nivi drape from Andhra Pradesh is the most popular style. It's elegant, simple, and works for almost any occasion. Perfect for office wear with a crisp cotton saree.</p><h3>2. Bengali Style</h3><p>The Bengali drape is characterized by the pleats being tucked at the back. This style looks spectacular at weddings and cultural events.</p><h3>3. Gujarati Style</h3><p>In the Gujarati style, the pallu is draped in front over the right shoulder. This creates a stunning, festive look perfect for celebrations.</p><p>Whatever your occasion, Orlina's curated saree collection has the perfect piece for you. Explore our handpicked selection of silk, cotton, and designer sarees crafted for the modern woman.</p>"],
    ];
    if (isset($demoPosts[$slug])) {
        $post = array_merge(['id'=>0,'slug'=>$slug,'cat_slug'=>'fashion'], $demoPosts[$slug]);
    } else {
        header('Location: ' . url('pages/blog.php')); exit;
    }
}

define('PAGE_TITLE', h($post['title']));

$related = $pdo->prepare("SELECT bp.*, bc.name AS cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id WHERE bp.status='published' AND bp.id!=? ORDER BY RAND() LIMIT 3");
$related->execute([$post['id']]);
$relatedPosts = $related->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:32px;">
  <nav class="breadcrumb">
    <a href="<?= url('index.php') ?>">Home</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= url('pages/blog.php') ?>">Blog</a>
    <i class="fas fa-chevron-right"></i>
    <span><?= h($post['title']) ?></span>
  </nav>
</div>

<article>
  <!-- Hero Image -->
  <?php if (!empty($post['featured_image'])): ?>
  <div style="width:100%;height:460px;overflow:hidden;margin-bottom:0;">
    <img src="<?= h($post['featured_image']) ?>" alt="<?= h($post['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
  </div>
  <?php endif; ?>

  <div class="container" style="padding-top:48px;padding-bottom:48px;">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:48px;align-items:flex-start;">
      <div>
        <?php if (!empty($post['cat_name'])): ?>
        <div class="blog-cat" style="margin-bottom:12px;"><?= h($post['cat_name']) ?></div>
        <?php endif; ?>
        <h1 style="font-size:clamp(1.8rem,3.5vw,2.8rem);margin-bottom:16px;"><?= h($post['title']) ?></h1>
        <div class="blog-meta" style="margin-bottom:32px;padding-bottom:24px;border-bottom:1px solid var(--border);">
          <i class="far fa-calendar"></i> <?= date('F j, Y', strtotime($post['created_at'])) ?>
          &nbsp;&bull;&nbsp;
          <i class="far fa-clock"></i> 5 min read
          &nbsp;&bull;&nbsp;
          <i class="fas fa-tag"></i> <?= h($post['cat_name'] ?? 'Fashion') ?>
        </div>

        <div style="line-height:1.9;color:var(--text);font-size:1.05rem;">
          <?= $post['content'] ?>
        </div>

        <!-- Share -->
        <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <span style="font-weight:600;color:var(--text2);">Share:</span>
          <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-facebook-f"></i> Facebook</a>
          <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-instagram"></i> Instagram</a>
          <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        </div>
      </div>

      <!-- Sidebar -->
      <aside style="position:sticky;top:140px;">
        <div class="blog-sidebar-card">
          <h4 style="margin-bottom:16px;">Shop This Look</h4>
          <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary btn-full"><i class="fas fa-shopping-bag"></i> Browse Collection</a>
        </div>
        <?php if ($relatedPosts): ?>
        <div class="blog-sidebar-card">
          <h4 style="margin-bottom:16px;">Related Posts</h4>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($relatedPosts as $rp): ?>
            <a href="?slug=<?= h($rp['slug']) ?>" style="display:flex;gap:12px;align-items:center;color:var(--text);">
              <?php if ($rp['featured_image']): ?>
              <img src="<?= h($rp['featured_image']) ?>" style="width:56px;height:56px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="">
              <?php endif; ?>
              <span style="font-size:0.85rem;font-weight:500;line-height:1.4;"><?= h($rp['title']) ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="blog-sidebar-card" style="background:var(--primary);color:#fff;">
          <h4 style="color:#fff;margin-bottom:8px;">✦ Newsletter</h4>
          <p style="font-size:0.85rem;opacity:0.9;margin-bottom:12px;">Get weekly style tips in your inbox.</p>
          <input type="email" placeholder="your@email.com" style="width:100%;padding:10px;border-radius:8px;border:none;margin-bottom:8px;font-family:'Outfit',sans-serif;">
          <button class="btn btn-white btn-sm btn-full">Subscribe</button>
        </div>
      </aside>
    </div>
  </div>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>
