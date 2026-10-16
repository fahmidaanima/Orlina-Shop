<?php
require_once __DIR__ . '/../includes/functions.php';
define('PAGE_TITLE', 'Blog');

$catId    = (int)($_GET['cat'] ?? 0);
$page     = max(1,(int)($_GET['page'] ?? 1));
$perPage  = 9;
$offset   = ($page-1)*$perPage;

$blogCategories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();

$where  = ["bp.status='published'"];
$params = [];
if ($catId) { $where[] = 'bp.category_id=?'; $params[] = $catId; }
$whereStr = 'WHERE '.implode(' AND ',$where);

$total = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp $whereStr");
$total->execute($params);
$totalPosts = $total->fetchColumn();
$totalPages = max(1,ceil($totalPosts/$perPage));

$stmt = $pdo->prepare("
    SELECT bp.*, bc.name AS cat_name
    FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id
    $whereStr ORDER BY bp.created_at DESC LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$blogImages = [
    'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&h=340&fit=crop',
    'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&h=340&fit=crop',
    'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&h=340&fit=crop',
    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&h=340&fit=crop',
    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&h=340&fit=crop',
];

// Static blog posts when DB is empty
if (!$posts) {
    $posts = [
        ['id'=>1,'title'=>'10 Ways to Style Your Saree for Every Occasion','slug'=>'style-saree','cat_name'=>'Fashion Tips','featured_image'=>$blogImages[0],'created_at'=>'2026-06-28 10:00:00','content'=>'Sarees are the quintessential garment of elegance...'],
        ['id'=>2,'title'=>'Summer Dress Trends Every Bangladeshi Woman Must Know','slug'=>'summer-trends','cat_name'=>'Trends 2026','featured_image'=>$blogImages[1],'created_at'=>'2026-06-20 10:00:00','content'=>'This summer, embrace bold patterns and breathable fabrics...'],
        ['id'=>3,'title'=>'The Ultimate Skincare Routine for Humid Weather','slug'=>'skincare-humid','cat_name'=>'Skincare','featured_image'=>$blogImages[2],'created_at'=>'2026-06-15 10:00:00','content'=>'Humid weather demands a different skincare approach...'],
        ['id'=>4,'title'=>'Abaya Styling Guide: Modern & Modest','slug'=>'abaya-guide','cat_name'=>'Style Guide','featured_image'=>$blogImages[3],'created_at'=>'2026-06-10 10:00:00','content'=>'Abayas have evolved into stunning fashion statements...'],
        ['id'=>5,'title'=>'Jewelry Trends: What\'s Hot in 2026','slug'=>'jewelry-2026','cat_name'=>'Trends 2026','featured_image'=>$blogImages[4],'created_at'=>'2026-06-05 10:00:00','content'=>'This year\'s jewelry trends are bold and beautiful...'],
        ['id'=>6,'title'=>'How to Choose the Perfect Handbag','slug'=>'handbag-guide','cat_name'=>'Shopping Guide','featured_image'=>$blogImages[0],'created_at'=>'2026-06-01 10:00:00','content'=>'A great handbag can elevate any outfit...'],
    ];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Orlina Blog</h1>
    <p>Style tips, trends, and stories for the modern woman</p>
    <nav class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i class="fas fa-chevron-right"></i><span>Blog</span></nav>
  </div>
</div>

<div class="container">
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin:32px 0 8px;justify-content:center;">
    <a href="<?= url('pages/blog.php') ?>" class="btn btn-sm <?= !$catId?'btn-primary':'btn-outline' ?>">All Posts</a>
    <?php foreach ($blogCategories as $bc): ?>
    <a href="?cat=<?= $bc['id'] ?>" class="btn btn-sm <?= $catId==$bc['id']?'btn-primary':'btn-outline' ?>"><?= h($bc['name']) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="blog-grid" style="padding:24px 0 48px;">
    <?php foreach ($posts as $i => $post): ?>
    <?php $img = $post['featured_image'] ?: $blogImages[$i % count($blogImages)]; ?>
    <a href="<?= url('pages/blog_single.php') ?>?slug=<?= h($post['slug']) ?>" class="blog-card">
      <div class="blog-img-wrap"><img src="<?= h($img) ?>" alt="<?= h($post['title']) ?>" class="blog-img" loading="lazy"></div>
      <div class="blog-body">
        <?php if (!empty($post['cat_name'])): ?>
        <div class="blog-cat"><?= h($post['cat_name']) ?></div>
        <?php endif; ?>
        <h3 class="blog-title"><?= h($post['title']) ?></h3>
        <p class="blog-excerpt"><?= h(substr(strip_tags($post['content'] ?? ''), 0, 120)) ?>…</p>
        <div class="blog-meta"><i class="far fa-clock"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination" style="margin-bottom:48px;">
    <?php for ($i=1;$i<=$totalPages;$i++): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
