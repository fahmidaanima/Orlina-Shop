<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Wishlist');

// Handle AJAX toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    header('Content-Type: application/json');
    if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Please login first.']); exit; }
    $pid = (int)($_POST['product_id'] ?? 0);
    if (!$pid) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }
    // Check exists
    $check = $pdo->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
    $check->execute([$_SESSION['user_id'], $pid]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'], $pid]);
        echo json_encode(['success'=>true,'wishlisted'=>false,'message'=>'Removed from wishlist.']);
    } else {
        $pdo->prepare("INSERT INTO wishlists (user_id,product_id) VALUES (?,?) ON DUPLICATE KEY UPDATE id=id")->execute([$_SESSION['user_id'], $pid]);
        echo json_encode(['success'=>true,'wishlisted'=>true,'message'=>'Added to wishlist!']);
    }
    exit;
}

requireLogin();

$wishlistStmt = $pdo->prepare("
    SELECT p.*, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb, c.name AS category_name
    FROM wishlists w JOIN products p ON w.product_id=p.id LEFT JOIN categories c ON p.category_id=c.id
    WHERE w.user_id=? ORDER BY w.created_at DESC
");
$wishlistStmt->execute([$_SESSION['user_id']]);
$items = $wishlistStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>My Wishlist</h1>
    <p><?= count($items) ?> saved items</p>
  </div>
</div>

<div class="container section-sm">
  <?php if ($items): ?>
  <div class="products-grid">
    <?php foreach ($items as $p): ?>
    <?php include __DIR__ . '/includes/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="far fa-heart"></i>
    <h3>Your wishlist is empty</h3>
    <p>Save products you love by clicking the heart icon.</p>
    <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">
      <i class="fas fa-shopping-bag"></i> Start Shopping
    </a>
  </div>
  <?php endif; ?>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
