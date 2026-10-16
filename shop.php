<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Shop');

// ── Build Filters ─────────────────────────────
$catSlug  = $_GET['cat']    ?? '';
$badge    = $_GET['badge']  ?? '';
$sort     = $_GET['sort']   ?? 'newest';
$minPrice = (int)($_GET['min'] ?? 0);
$maxPrice = (int)($_GET['max'] ?? 50000);
$rating   = (int)($_GET['rating'] ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

$where  = ['p.stock > 0'];
$params = [];

if ($catSlug) {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}
if ($badge && in_array($badge, ['NEW','SALE','TRENDING'])) {
    $where[] = 'p.badge = ?';
    $params[] = $badge;
}
$where[] = 'p.price >= ?';
$params[] = $minPrice;
$where[] = 'p.price <= ?';
$params[] = $maxPrice;

if ($rating > 0) {
    $where[] = '(SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id=p.id AND r.status="approved") >= ?';
    $params[] = $rating;
}

$orderMap = [
  'newest'    => 'p.created_at DESC',
  'oldest'    => 'p.created_at ASC',
  'price_asc' => 'p.price ASC',
  'price_desc'=> 'p.price DESC',
  'name_asc'  => 'p.name ASC',
  'rating'    => 'avg_r DESC',
];
$orderBy = $orderMap[$sort] ?? 'p.created_at DESC';

$whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$countSql = "
  SELECT COUNT(*) FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  $whereStr
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));

// Products
$sql = "
  SELECT p.*, c.name AS category_name,
         (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) AS thumb,
         COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id=p.id AND r.status='approved'),0) AS avg_r
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  $whereStr
  ORDER BY $orderBy
  LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$allCategories = getCategories();

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Shop <?= $catSlug ? h(ucfirst($catSlug)) : 'All' ?></h1>
    <p><?= $totalProducts ?> products found</p>
    <nav class="breadcrumb">
      <a href="<?= url('index.php') ?>">Home</a>
      <i class="fas fa-chevron-right"></i>
      <a href="<?= url('pages/shop.php') ?>">Shop</a>
      <?php if ($catSlug): ?>
      <i class="fas fa-chevron-right"></i>
      <span><?= h(ucfirst($catSlug)) ?></span>
      <?php endif; ?>
    </nav>
  </div>
</div>

<div class="container">
  <div class="shop-layout">

    <!-- SIDEBAR ─────────────────────────── -->
    <aside class="shop-sidebar">
      <form method="GET" action="" id="filter-form">
        <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>

        <!-- Categories -->
        <div class="filter-card">
          <div class="filter-title">Categories</div>
          <div class="filter-list">
            <label class="filter-item">
              <input type="radio" name="cat" value="" <?= !$catSlug ? 'checked' : '' ?> onchange="document.getElementById('filter-form').submit()">
              All Categories
            </label>
            <?php foreach ($allCategories as $cat): ?>
            <label class="filter-item">
              <input type="radio" name="cat" value="<?= h($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'checked' : '' ?> onchange="document.getElementById('filter-form').submit()">
              <?= h($cat['name']) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Price Range -->
        <div class="filter-card">
          <div class="filter-title">Price Range</div>
          <input type="range" id="price-range" name="max" min="0" max="50000" value="<?= $maxPrice ?>" step="500">
          <div class="price-labels">
            <span>৳0</span>
            <span id="price-max-label">৳<?= number_format($maxPrice) ?></span>
          </div>
          <input type="hidden" name="min" value="0">
          <button type="submit" class="btn btn-primary btn-sm btn-full mt-4">Apply</button>
        </div>

        <!-- Badge Filter -->
        <div class="filter-card">
          <div class="filter-title">Product Type</div>
          <div class="filter-list">
            <?php foreach ([''=>'All Products','NEW'=>'New Arrivals','SALE'=>'On Sale','TRENDING'=>'Trending'] as $b => $label): ?>
            <label class="filter-item">
              <input type="radio" name="badge" value="<?= h($b) ?>" <?= $badge === $b ? 'checked' : '' ?> onchange="document.getElementById('filter-form').submit()">
              <?= h($label) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Rating -->
        <div class="filter-card">
          <div class="filter-title">Minimum Rating</div>
          <div class="filter-list">
            <?php for ($r=5;$r>=1;$r--): ?>
            <label class="filter-item">
              <input type="radio" name="rating" value="<?= $r ?>" <?= $rating==$r ? 'checked' : '' ?> onchange="document.getElementById('filter-form').submit()">
              <?= str_repeat('★',$r) ?><?= str_repeat('☆',5-$r) ?> & up
            </label>
            <?php endfor; ?>
            <label class="filter-item">
              <input type="radio" name="rating" value="0" <?= $rating==0 ? 'checked' : '' ?> onchange="document.getElementById('filter-form').submit()">
              All Ratings
            </label>
          </div>
        </div>
      </form>
    </aside>

    <!-- MAIN ────────────────────────────── -->
    <div class="shop-main">
      <div class="shop-toolbar">
        <span class="shop-count">
          Showing <?= min($offset+1, $totalProducts) ?>–<?= min($offset+$perPage, $totalProducts) ?> of <?= $totalProducts ?> products
        </span>
        <div class="shop-sort">
          <select onchange="window.location.href=this.value">
            <?php
            $sortBase = '?' . http_build_query(array_merge($_GET, ['sort'=>'', 'page'=>1]));
            $sorts = ['newest'=>'Newest First','oldest'=>'Oldest First','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low','name_asc'=>'Name A–Z','rating'=>'Top Rated'];
            foreach ($sorts as $k => $label): ?>
            <option value="<?= h('?' . http_build_query(array_merge($_GET, ['sort'=>$k,'page'=>1]))) ?>" <?= $sort===$k ? 'selected' : '' ?>>
              <?= h($label) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php if ($products): ?>
      <div class="products-grid">
        <?php foreach ($products as $p): ?>
        <?php include __DIR__ . '/includes/product_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>" class="page-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No products found</h3>
        <p>Try adjusting your filters or browse all products.</p>
        <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Clear Filters</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
