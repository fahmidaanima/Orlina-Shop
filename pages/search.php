<?php
require_once __DIR__ . '/../includes/functions.php';
$q    = sanitize($_GET['q'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$perPage = 12;
$offset  = ($page-1)*$perPage;
$products = [];
$total = 0;

if ($q) {
    define('PAGE_TITLE', 'Search: '.h($q));
    $like = '%'.$q.'%';
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.stock>0 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)");
    $countStmt->execute([$like,$like,$like]);
    $total = $countStmt->fetchColumn();
    $totalPages = max(1,ceil($total/$perPage));

    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb
        FROM products p LEFT JOIN categories c ON p.category_id=c.id
        WHERE p.stock>0 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
        ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([$like,$like,$like]);
    $products = $stmt->fetchAll();
} else {
    define('PAGE_TITLE', 'Search');
    $totalPages = 1;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <h1>Search Results</h1>
    <?php if ($q): ?><p><?= $total ?> results for "<?= h($q) ?>"</p><?php endif; ?>
  </div>
</div>
<div class="container" style="padding:40px 24px;">
  <form action="" method="GET" style="max-width:600px;margin:0 auto 40px;display:flex;gap:12px;">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search products…" style="flex:1;padding:12px 20px;border:1.5px solid var(--border);border-radius:50px;font-family:'Outfit',sans-serif;outline:none;font-size:0.95rem;" required autofocus>
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <?php if ($products): ?>
  <div class="products-grid"><?php foreach ($products as $p): ?><?php include __DIR__ . '/../includes/product_card.php'; ?><?php endforeach; ?></div>
  <?php if ($totalPages > 1): ?>
  <div class="pagination" style="margin-top:40px;">
    <?php for ($i=1;$i<=$totalPages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php elseif ($q): ?>
  <div class="empty-state"><i class="fas fa-search"></i><h3>No products found</h3><p>Try different keywords or browse our shop.</p><a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Browse Shop</a></div>
  <?php endif; ?>
</div>
<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
