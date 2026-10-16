<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT bp.*, bc.name AS cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id WHERE bp.slug=? AND bp.status='published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

// Static fallback
if (!$post) {
    $staticPosts = [
        'style-saree' => ['title'=>'10 Ways to Style Your Saree for Every Occasion','cat_name'=>'Fashion Tips','featured_image'=>'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&h=600&fit=crop','created_at'=>'2026-06-28 10:00:00','content'=>'<p>Sarees are the quintessential garment of elegance and grace. They have been a symbol of femininity and tradition for centuries, yet they continue to evolve with modern fashion trends.</p><h2>1. The Classic Drape</h2><p>The traditional Bengali drape is timeless. Perfect for weddings, pujas, and formal events, this style showcases the full beauty of the saree\'s fabric and border.</p><h2>2. The Butterfly Style</h2><p>Popular in South India, this style creates a gorgeous butterfly-like effect at the back pallu. It\'s dramatic and perfect for special occasions.</p><h2>3. The Modern Pant Style</h2><p>Pair your saree with fitted pants instead of a traditional petticoat for a contemporary, fusion look that\'s gaining popularity among young women.</p><p>Whether you\'re dressing for a casual day out or an extravagant wedding, there\'s always a perfect way to drape your saree. Experiment with accessories, blouses, and draping styles to find what works best for you.</p>'],
        'summer-trends' => ['title'=>'Summer Dress Trends Every Bangladeshi Woman Must Know','cat_name'=>'Trends 2026','featured_image'=>'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&h=600&fit=crop','created_at'=>'2026-06-20 10:00:00','content'=>'<p>Bangladesh\'s hot and humid summers call for breathable fabrics and light colors. Here are the top trends dominating women\'s fashion this season.</p><h2>Flowy Maxi Dresses</h2><p>Nothing beats a flowy maxi dress in summer. Look for cotton or linen blends that allow air circulation while keeping you looking elegant.</p><h2>Pastel Color Palette</h2><p>Soft pinks, lavenders, mint greens, and peach tones are dominating the 2026 summer palette. These colors are not just cool to look at — they also reflect heat better than darker shades.</p>'],
    ];
    if (isset($staticPosts[$slug])) {
        $post = array_merge($staticPosts[$slug], ['slug'=>$slug]);
    } else {
        header('Location: '.url('pages/blog.php')); exit;
    }
}

define('PAGE_TITLE', $post['title']);

$recent = $pdo->query("SELECT * FROM blog_posts WHERE status='published' ORDER BY created_at DESC LIMIT 4")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div style="padding-top:20px;">
    <nav class="breadcrumb">
      <a href="<?= url('index.php') ?>">Home</a>
      <i class="fas fa-chevron-right"></i>
      <a href="<?= url('pages/blog.php') ?>">Blog</a>
      <i class="fas fa-chevron-right"></i>
      <span><?= h(substr($post['title'],0,40)) ?>…</span>
    </nav>
  </div>
</div>

<div class="container">
  <div class="blog-single-layout">
    <article class="blog-content">
      <?php if (!empty($post['cat_name'])): ?>
      <div class="blog-cat" style="font-size:0.85rem;margin-bottom:12px;"><?= h($post['cat_name']) ?></div>
      <?php endif; ?>
      <h1><?= h($post['title']) ?></h1>
      <div style="display:flex;gap:16px;align-items:center;margin:12px 0 24px;font-size:0.85rem;color:var(--text2);">
        <span><i class="far fa-clock"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
        <span><i class="far fa-user"></i> Orlina Editorial</span>
      </div>
      <?php if (!empty($post['featured_image'])): ?>
      <img src="<?= h($post['featured_image']) ?>" alt="<?= h($post['title']) ?>" class="blog-content-img">
      <?php endif; ?>
      <div class="blog-content-body">
        <?= $post['content'] ?>
      </div>
      <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <span style="font-weight:600;font-size:0.88rem;">Share:</span>
        <a href="#" class="btn btn-sm btn-outline"><i class="fab fa-facebook-f"></i> Facebook</a>
        <a href="#" class="btn btn-sm btn-outline"><i class="fab fa-whatsapp"></i> WhatsApp</a>
      </div>
    </article>

    <aside class="blog-sidebar">
      <div class="sidebar-card">
        <h3>Recent Posts</h3>
        <?php
        $recentImages = ['https://images.unsplash.com/photo-1483985988355-763728e1935b?w=80&h=80&fit=crop','https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=80&h=80&fit=crop','https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=80&h=80&fit=crop','https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=80&h=80&fit=crop'];
        if ($recent): foreach ($recent as $i=>$r): ?>
        <a href="<?= url('pages/blog_single.php') ?>?slug=<?= h($r['slug']) ?>" style="display:flex;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);text-decoration:none;">
          <img src="<?= h($r['featured_image'] ?: $recentImages[$i%4]) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0;" alt="">
          <div>
            <div style="font-size:0.85rem;font-weight:600;color:var(--text);"><?= h($post['title']) ?></div>
            <div style="font-size:0.75rem;color:var(--text2);"><?= date('M j',strtotime($r['created_at'])) ?></div>
          </div>
        </a>
        <?php endforeach; else: ?>
        <p style="font-size:0.85rem;color:var(--text2);">No recent posts yet.</p>
        <?php endif; ?>
      </div>

      <div class="sidebar-card">
        <h3>Shop the Look</h3>
        <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary btn-full btn-sm">Browse Collection</a>
      </div>
    </aside>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
