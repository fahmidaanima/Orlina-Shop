<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Search');

$q    = trim($_GET['q'] ?? '');
$products = [];
$total = 0;

if ($q) {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb
        FROM products p LEFT JOIN categories c ON p.category_id=c.id
        WHERE (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?) AND p.stock > 0
        ORDER BY p.featured DESC, p.created_at DESC
        LIMIT 24
    ");
    $stmt->execute([$like, $like, $like]);
    $products = $stmt->fetchAll();
    $total = count($products);
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>Search Results</h1>
    <?php if ($q): ?>
    <p><?= $total ?> result<?= $total !== 1 ? 's' : '' ?> for "<strong><?= h($q) ?></strong>"</p>
    <?php endif; ?>
  </div>
</div>

<div class="container section-sm">
  <div style="max-width:560px;margin:0 auto 40px;">
    <form class="search-form" action="" style="border-radius:var(--radius);padding:4px;">
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search products…" style="padding:14px 20px;" autofocus>
      <button type="submit" style="border-radius:10px;padding:10px 24px;">
        <i class="fas fa-search"></i> Search
      </button>
    </form>
  </div>

  <?php if (!$q): ?>
  <div class="empty-state">
    <i class="fas fa-search"></i>
    <h3>Start searching</h3>
    <p>Type a product name, category, or keyword above.</p>
  </div>
  <?php elseif ($products): ?>
  <div class="products-grid">
    <?php foreach ($products as $p): ?>
    <?php include __DIR__ . '/includes/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="fas fa-search"></i>
    <h3>No results found</h3>
    <p>Try different keywords or browse our shop.</p>
    <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Browse All Products</a>
  </div>
  <?php endif; ?>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
