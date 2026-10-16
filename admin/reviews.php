<?php
define('ADMIN_PAGE','Reviews');
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $rid = (int)$_POST['review_id'];
    $action = $_POST['action']??'';
    if ($action==='approve') { $pdo->prepare("UPDATE reviews SET status='approved' WHERE id=?")->execute([$rid]); setFlash('success','Review approved.'); }
    elseif ($action==='reject') { $pdo->prepare("UPDATE reviews SET status='rejected' WHERE id=?")->execute([$rid]); setFlash('success','Review rejected.'); }
    elseif ($action==='delete') { $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]); setFlash('success','Review deleted.'); }
    header('Location: '.url('admin/reviews.php')); exit;
}

$filter = $_GET['status']??'pending';
$where  = $filter ? "WHERE r.status='$filter'" : '';

$reviews = $pdo->query("
    SELECT r.*, u.first_name, u.last_name, p.name AS product_name
    FROM reviews r
    JOIN users u ON r.user_id=u.id
    JOIN products p ON r.product_id=p.id
    $where
    ORDER BY r.created_at DESC
")->fetchAll();

$counts = [];
foreach (['pending','approved','rejected'] as $s) {
    $counts[$s] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status='$s'")->fetchColumn();
}
?>

<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
  <?php foreach (['pending'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger',''=>'badge-gray'] as $s => $cls): ?>
  <a href="?status=<?= $s ?>" class="btn btn-sm <?= $filter===$s?'btn-primary':'btn-outline' ?>">
    <?= $s ? ucfirst($s) : 'All' ?> <?= isset($counts[$s]) ? "({$counts[$s]})" : '' ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if ($reviews): foreach ($reviews as $r): ?>
    <tr>
      <td style="max-width:160px;"><strong><?= h($r['product_name']) ?></strong></td>
      <td><?= h($r['first_name'].' '.$r['last_name']) ?></td>
      <td><?= starRating((float)$r['rating']) ?></td>
      <td style="max-width:240px;font-size:0.85rem;color:var(--text2);"><?= h(substr($r['review_text']??'(No text)',0,120)) ?></td>
      <td><span class="badge-pill <?= $r['status']==='approved'?'badge-success':($r['status']==='rejected'?'badge-danger':'badge-warning') ?>"><?= ucfirst($r['status']) ?></span></td>
      <td style="font-size:0.8rem;color:var(--text2);"><?= date('M j, Y',strtotime($r['created_at'])) ?></td>
      <td>
        <div class="admin-table-actions">
          <?php if ($r['status']!=='approved'): ?>
          <form method="POST"><<?= csrf_field() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="approve"><button type="submit" class="btn-icon btn-icon-view" title="Approve"><i class="fas fa-check"></i></button></form>
          <?php endif; ?>
          <?php if ($r['status']!=='rejected'): ?>
          <form method="POST"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reject"><button type="submit" class="btn-icon btn-icon-edit" title="Reject" style="background:#fef9c3;color:#92400e;"><i class="fas fa-ban"></i></button></form>
          <?php endif; ?>
          <form method="POST" data-confirm="Delete this review?"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete"><button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button></form>
        </div>
      </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text2);">No reviews found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
