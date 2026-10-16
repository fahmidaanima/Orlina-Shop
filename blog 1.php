<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Blog');

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset  = ($page - 1) * $perPage;
$catSlug = $_GET['cat'] ?? '';

$where  = ["bp.status='published'"];
$params = [];
if ($catSlug) { $where[] = "bc.slug=?"; $params[] = $catSlug; }
$whereStr = 'WHERE ' . implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id $whereStr");
$total->execute($params);
$totalPosts = $total->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $perPage));

$stmt = $pdo->prepare("
    SELECT bp.*, bc.name AS cat_name, bc.slug AS cat_slug
    FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id
    $whereStr ORDER BY bp.created_at DESC LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$blogCats = $pdo->query("SELECT bc.*, COUNT(bp.id) AS post_count FROM blog_categories bc LEFT JOIN blog_posts bp ON bc.id=bp.category_id AND bp.status='published' GROUP BY bc.id ORDER BY bc.name")->fetchAll();

// Placeholder blog posts for demo (when DB is empty)
$demoPosts = [
  ['id'=>1,'title'=>'10 Ways to Style Your Saree for Every Occasion','slug'=>'style-saree-occasions','featured_image'=>'https://images.unsplash.com/photo-1583391733956-62e5a2cda5be?w=700&h=400&fit=crop','cat_name'=>'Fashion Tips','cat_slug'=>'fashion','created_at'=>'2026-06-28 10:00:00','content'=>'Discover elegant saree styling tips for every occasion from weddings to casual outings.'],
  ['id'=>2,'title'=>'Summer Dress Trends Every Woman Must Know in 2026','slug'=>'summer-dress-trends-2026','featured_image'=>'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=700&h=400&fit=crop','cat_name'=>'Trends','cat_slug'=>'trends','created_at'=>'2026-06-20 10:00:00','content'=>'Stay ahead of summer fashion with these stunning dress trends for 2026.'],
  ['id'=>3,'title'=>'The Ultimate Skincare Routine for Humid Weather','slug'=>'skincare-humid-weather','featured_image'=>'https://images.unsplash.com/photo-1552664730-d307ca884978?w=700&h=400&fit=crop','cat_name'=>'Skincare','cat_slug'=>'skincare','created_at'=>'2026-06-15 10:00:00','content'=>'Beat the heat with this comprehensive skincare routine tailored for tropical climates.'],
  ['id'=>4,'title'=>'How to Choose the Perfect Abaya for Any Event','slug'=>'choose-perfect-abaya','featured_image'=>'https://images.unsplash.com/photo-1594463750939-ebb28c3f7f75?w=700&h=400&fit=crop','cat_name'=>'Fashion Tips','cat_slug'=>'fashion','created_at'=>'2026-06-10 10:00:00','content'=>'A comprehensive guide to selecting the right abaya for every occasion.'],
  ['id'=>5,'title'=>'Top 5 Handbags Every Woman Should Own This Season','slug'=>'top-handbags-season','featured_image'=>'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700&h=400&fit=crop','cat_name'=>'Accessories','cat_slug'=>'accessories','created_at'=>'2026-06-05 10:00:00','content'=>'Invest in these timeless handbag styles that will elevate every outfit.'],
  ['id'=>6,'title'=>'Jewelry Trends: What\'s Hot for Women in 2026','slug'=>'jewelry-trends-2026','featured_image'=>'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=700&h=400&fit=crop','cat_name'=>'Jewelry','cat_slug'=>'jewelry','created_at'=>'2026-05-30 10:00:00','content'=>'From statement pieces to minimalist designs, explore the jewelry trends defining 2026.'],
];
if (!$posts) $posts = $demoPosts;

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Style & Inspiration</h1>
    <p>Fashion tips, trends and lifestyle stories for the modern Bangladeshi woman</p>
  </div>
</div>

<div class="container">
  <div class="blog-layout">

    <!-- Posts -->
    <div>
      <?php if ($posts): ?>
      <div style="display:grid;gap:32px;">
        <?php foreach ($posts as $post):
          $img = $post['featured_image'] ?: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=700&h=400&fit=crop';
        ?>
        <div class="blog-full-card">
          <div class="blog-img-wrap" style="overflow:hidden;">
            <img src="<?= h($img) ?>" alt="<?= h($post['title']) ?>" class="blog-img" style="height:100%;min-height:240px;" loading="lazy">
          </div>
          <div class="blog-body" style="padding:28px;display:flex;flex-direction:column;justify-content:center;">
            <?php if (!empty($post['cat_name'])): ?>
            <div class="blog-cat"><?= h($post['cat_name']) ?></div>
            <?php endif; ?>
            <h2 class="blog-title" style="font-size:1.4rem;-webkit-line-clamp:2;">
              <a href="<?= url('pages/blog_single.php') ?>?slug=<?= h($post['slug']) ?>"><?= h($post['title']) ?></a>
            </h2>
            <p style="color:var(--text2);font-size:0.9rem;line-height:1.7;margin:12px 0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
              <?= h(strip_tags($post['content'] ?? '')) ?>
            </p>
            <div class="blog-meta" style="margin-top:auto;">
              <i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?>
              &nbsp;&bull;&nbsp;
              <i class="far fa-clock"></i> 5 min read
            </div>
            <div style="margin-top:16px;">
              <a href="<?= url('pages/blog_single.php') ?>?slug=<?= h($post['slug']) ?>" class="btn btn-outline btn-sm">
                Read More <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination" style="margin-top:40px;">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <a href="?page=<?= $i ?><?= $catSlug ? '&cat='.h($catSlug) : '' ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <div class="empty-state"><i class="fas fa-newspaper"></i><h3>No posts yet</h3><p>Check back soon for style inspiration and fashion tips.</p></div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <aside>
      <div class="blog-sidebar-card">
        <h4 style="margin-bottom:16px;">Search</h4>
        <form action="<?= url('pages/search.php') ?>" style="display:flex;gap:8px;">
          <input type="text" name="q" placeholder="Search…" style="flex:1;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;outline:none;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
      </div>

      <?php if ($blogCats): ?>
      <div class="blog-sidebar-card">
        <h4 style="margin-bottom:16px;">Categories</h4>
        <ul style="display:flex;flex-direction:column;gap:8px;">
          <li><a href="<?= url('pages/blog.php') ?>" style="display:flex;justify-content:space-between;color:var(--text);font-size:0.9rem;padding:4px 0;"><span>All Posts</span><span style="color:var(--text2);"><?= $totalPosts ?></span></a></li>
          <?php foreach ($blogCats as $bc): ?>
          <li><a href="?cat=<?= h($bc['slug']) ?>" style="display:flex;justify-content:space-between;color:var(--text);font-size:0.9rem;padding:4px 0;"><span><?= h($bc['name']) ?></span><span style="color:var(--text2);"><?= $bc['post_count'] ?></span></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="blog-sidebar-card" style="background:var(--primary);color:#fff;">
        <h4 style="margin-bottom:12px;color:#fff;">Newsletter</h4>
        <p style="font-size:0.85rem;opacity:0.9;margin-bottom:16px;">Get exclusive fashion tips delivered to your inbox.</p>
        <input type="email" placeholder="Your email" style="width:100%;padding:10px 14px;border-radius:8px;border:none;font-family:'Outfit',sans-serif;margin-bottom:8px;">
        <button class="btn btn-white btn-sm btn-full">Subscribe</button>
      </div>
    </aside>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
