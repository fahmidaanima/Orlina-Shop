<?php
require_once __DIR__ . '/../includes/functions.php';

// Handle toggle via AJAX — must come BEFORE requireLogin so guests get JSON (not a redirect page)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if (!verify_csrf()) { echo json_encode(['success'=>false,'message'=>'Security error']); exit; }
    $pid = (int)($_POST['product_id']??0);
    if (!isLoggedIn()) {
        $currentUrl = $_SERVER['HTTP_REFERER'] ?? '';
        $loginUrl   = BASE_URL . '/pages/login.php?redirect=' . urlencode($currentUrl) . '&msg=wishlist';
        echo json_encode(['success'=>false,'redirect'=>$loginUrl,'message'=>'Please login to continue.']); exit;
    }
    $exists = isWishlisted($pid);
    if ($exists) {
        $pdo->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'],$pid]);
        echo json_encode(['success'=>true,'wishlisted'=>false,'message'=>'Removed from wishlist.']);
    } else {
        try {
            $pdo->prepare("INSERT INTO wishlists (user_id,product_id) VALUES (?,?)")->execute([$_SESSION['user_id'],$pid]);
            echo json_encode(['success'=>true,'wishlisted'=>true,'message'=>'Added to wishlist!']);
        } catch(Exception $e) {
            echo json_encode(['success'=>false,'message'=>'Could not add']);
        }
    }
    exit;
}

// Page view — requires login
requireLogin('/pages/login.php');
define('PAGE_TITLE', 'My Wishlist');

$stmt = $pdo->prepare("
    SELECT p.*, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb,
           c.name AS category_name
    FROM wishlists w JOIN products p ON w.product_id=p.id LEFT JOIN categories c ON p.category_id=c.id
    WHERE w.user_id=? ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$wishlist = $stmt->fetchAll();

// Handle remove
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['remove_wishlist']) && verify_csrf()) {
    $pid = (int)$_POST['product_id'];
    $pdo->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'],$pid]);
    setFlash('success','Item removed from wishlist.');
    header('Location: '.url('pages/wishlist.php')); exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>My Wishlist</h1>
    <p><?= count($wishlist) ?> items saved</p>
  </div>
</div>

<div class="container">
  <?php if ($wishlist): ?>
  <div class="wishlist-grid" style="padding:40px 0;">
    <?php foreach ($wishlist as $p): ?>
    <div style="position:relative;">
      <?php include __DIR__ . '/../includes/product_card.php'; ?>
      <form method="POST" style="position:absolute;top:8px;left:8px;">
        <?= csrf_field() ?>
        <input type="hidden" name="remove_wishlist" value="1">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <button type="submit" title="Remove from wishlist" style="width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,0.9);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#dc2626;">
          <i class="fas fa-times" style="font-size:0.75rem;"></i>
        </button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state" style="padding:80px 24px;">
    <i class="far fa-heart"></i>
    <h3>Your wishlist is empty</h3>
    <p>Save items you love by clicking the heart icon on any product.</p>
    <a href="<?= url('pages/shop.php') ?>" class="btn btn-primary mt-4">Start Shopping</a>
  </div>
  <?php endif; ?>
</div>

<script>window.__csrf = '<?= csrf_token() ?>';</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
