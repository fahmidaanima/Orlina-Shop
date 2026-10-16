<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Reviews');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $rid    = (int)$_POST['review_id'];
    $action = $_POST['review_action'] ?? '';
    if ($action === 'approve') $pdo->prepare("UPDATE reviews SET status='approved' WHERE id=?")->execute([$rid]);
    elseif ($action === 'reject') $pdo->prepare("UPDATE reviews SET status='rejected' WHERE id=?")->execute([$rid]);
    elseif ($action === 'delete') $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Review updated.'];
    header('Location: ' . url('admin/reviews.php')); exit;
}

$filter = $_GET['status'] ?? 'pending';
$stmt   = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name, p.name AS product_name, p.slug AS product_slug
    FROM reviews r
    JOIN users u ON r.user_id=u.id
    JOIN products p ON r.product_id=p.id
    WHERE r.status=?
    ORDER BY r.created_at DESC
");
$stmt->execute([$filter]);
$reviews = $stmt->fetchAll();

$counts = [];
foreach (['pending','approved','rejected'] as $s) {
    $counts[$s] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status='$s'")->fetchColumn();
}

include __DIR__ . '/layout.php';
?>

<div style="display:flex;gap:8px;margin-bottom:20px;">
  <?php foreach ($counts as $s => $c): ?>
  <a href="?status=<?= $s ?>" class="btn <?= $filter===$s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= ucfirst($s) ?> (<?= $c ?>)</a>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <?php if ($reviews): ?>
  <div style="display:grid;gap:16px;">
    <?php foreach ($reviews as $rev): ?>
    <div style="padding:20px;border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <strong><?= h($rev['first_name'].' '.$rev['last_name']) ?></strong>
            <span style="color:var(--accent);"><?= str_repeat('★',$rev['rating']).(str_repeat('☆',5-$rev['rating'])) ?></span>
            <span class="status-badge status-<?= $rev['status']==='approved'?'paid':($rev['status']==='pending'?'pending':'cancelled') ?>"><?= h(ucfirst($rev['status'])) ?></span>
          </div>
          <div style="font-size:0.85rem;color:var(--text2);margin-bottom:8px;">
            On: <a href="<?= url('pages/product.php') ?>?slug=<?= h($rev['product_slug']) ?>" target="_blank" style="color:var(--primary-start);"><?= h($rev['product_name']) ?></a>
            &bull; <?= date('M j, Y', strtotime($rev['created_at'])) ?>
          </div>
          <?php if ($rev['review_text']): ?>
          <p style="color:var(--text);font-size:0.92rem;line-height:1.6;">"<?= h($rev['review_text']) ?>"</p>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
          <?php if ($rev['status'] !== 'approved'): ?>
          <form method="POST" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
            <input type="hidden" name="review_action" value="approve">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Approve</button>
          </form>
          <?php endif; ?>
          <?php if ($rev['status'] !== 'rejected'): ?>
          <form method="POST" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
            <input type="hidden" name="review_action" value="reject">
            <button type="submit" class="btn btn-outline btn-sm" style="border-color:#ef4444;color:#ef4444;"><i class="fas fa-times"></i> Reject</button>
          </form>
          <?php endif; ?>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this review?')">
            <?= csrf_field() ?>
            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
            <input type="hidden" name="review_action" value="delete">
            <button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state"><i class="fas fa-star"></i><h3>No <?= $filter ?> reviews</h3></div>
  <?php endif; ?>
</div>

    </div>
  </div>
</div>
</body>
</html>
