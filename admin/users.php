<?php
define('ADMIN_PAGE','Users');
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/layout.php';

// Handle POST
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['action']??'';

    if ($action==='add') {
        $fn   = sanitize($_POST['first_name']??'');
        $ln   = sanitize($_POST['last_name']??'');
        $email= filter_var(trim($_POST['email']??''),FILTER_SANITIZE_EMAIL);
        $phone= sanitize($_POST['phone']??'');
        $role = in_array($_POST['role'],['member','admin','super_admin']) ? $_POST['role'] : 'member';
        $pass = $_POST['password']??'';
        if (strlen($pass) < 6) { setFlash('error','Password must be 6+ characters.'); }
        else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email=?"); $check->execute([$email]);
            if ($check->fetch()) { setFlash('error','Email already exists.'); }
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (first_name,last_name,email,phone,password,role) VALUES (?,?,?,?,?,?)")->execute([$fn,$ln,$email,$phone,$hash,$role]);
                setFlash('success','User added.');
            }
        }
        header('Location: '.url('admin/users.php')); exit;
    }

    if ($action==='change_role' && isSuperAdmin()) {
        $uid  = (int)$_POST['user_id'];
        $role = in_array($_POST['role'],['member','admin','super_admin']) ? $_POST['role'] : 'member';
        $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);
        setFlash('success','Role updated.');
        header('Location: '.url('admin/users.php')); exit;
    }

    if ($action==='delete' && isSuperAdmin()) {
        $uid = (int)$_POST['user_id'];
        if ($uid === $_SESSION['user_id']) { setFlash('error','Cannot delete your own account.'); }
        else { $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]); setFlash('success','User deleted.'); }
        header('Location: '.url('admin/users.php')); exit;
    }
}

$search = sanitize($_GET['q']??'');
$roleFilter = sanitize($_GET['role']??'');
$page = max(1,(int)($_GET['page']??1));
$perPage = 20; $offset = ($page-1)*$perPage;

$where = []; $params = [];
if ($search) { $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)"; $params = array_merge($params,["%$search%","%$search%","%$search%"]); }
if ($roleFilter) { $where[] = "u.role=?"; $params[] = $roleFilter; }
$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM users u $whereStr"); $total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = max(1,ceil($totalCount/$perPage));

$users = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count FROM users u $whereStr ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
$users->execute($params);
$users = $users->fetchAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <h2 style="font-size:1.3rem;">Users (<?= $totalCount ?>)</h2>
  <?php if (isSuperAdmin()): ?>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-user-modal').style.display='flex'">
    <i class="fas fa-user-plus"></i> Add User
  </button>
  <?php endif; ?>
</div>

<div class="admin-card" style="margin-bottom:20px;"><div class="admin-card-body padded">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
    <div class="admin-form-group"><label>Search</label><input type="text" name="q" value="<?= h($search) ?>" placeholder="Name or email…"></div>
    <div class="admin-form-group"><label>Role</label><select name="role"><option value="">All Roles</option><?php foreach (['member','admin','super_admin'] as $r): ?><option value="<?= $r ?>" <?= $roleFilter===$r?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= url('admin/users.php') ?>" class="btn btn-sm btn-outline">Clear</a>
  </form>
</div></div>

<div class="admin-card">
  <div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Avatar</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if ($users): foreach ($users as $u): ?>
    <tr>
      <td><img src="<?= avatarUrl($u['avatar']??null,$u['first_name'].' '.$u['last_name']) ?>" width="36" height="36" style="border-radius:50%;object-fit:cover;" alt=""></td>
      <td><strong><?= h($u['first_name'].' '.$u['last_name']) ?></strong></td>
      <td><?= h($u['email']) ?></td>
      <td><?= h($u['phone']??'—') ?></td>
      <td>
        <?php if (isSuperAdmin() && $u['id']!=$_SESSION['user_id']): ?>
        <form method="POST" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="change_role">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <select name="role" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--border);border-radius:8px;font-size:0.82rem;outline:none;">
            <?php foreach (['member','admin','super_admin'] as $r): ?>
            <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <?php else: ?>
        <span class="badge-pill <?= $u['role']==='super_admin'?'badge-purple':($u['role']==='admin'?'badge-info':'badge-gray') ?>"><?= ucfirst(str_replace('_',' ',$u['role'])) ?></span>
        <?php endif; ?>
      </td>
      <td><?= $u['order_count'] ?></td>
      <td style="font-size:0.82rem;color:var(--text2);"><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
      <td>
        <?php if (isSuperAdmin() && $u['id']!=$_SESSION['user_id']): ?>
        <form method="POST" data-confirm="Delete user <?= h($u['email']) ?>? This cannot be undone.">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button>
        </form>
        <?php else: ?>—<?php endif; ?>
      </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text2);">No users found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="pagination" style="padding:16px;"><?php for ($i=1;$i<=$totalPages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div>
  <?php endif; ?>
</div>

<!-- Add User Modal -->
<?php if (isSuperAdmin()): ?>
<div class="modal-overlay" id="add-user-modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div class="modal-header"><h3 class="modal-title">Add User</h3><button class="modal-close" onclick="document.getElementById('add-user-modal').style.display='none'">&times;</button></div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="admin-form-grid">
        <div class="admin-form-group"><label>First Name *</label><input type="text" name="first_name" required></div>
        <div class="admin-form-group"><label>Last Name *</label><input type="text" name="last_name" required></div>
        <div class="admin-form-group"><label>Email *</label><input type="email" name="email" required></div>
        <div class="admin-form-group"><label>Phone</label><input type="tel" name="phone"></div>
        <div class="admin-form-group"><label>Role</label><select name="role"><?php foreach (['member','admin','super_admin'] as $r): ?><option value="<?= $r ?>"><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Password *</label><input type="password" name="password" required placeholder="Min. 6 chars"></div>
      </div>
      <div style="margin-top:20px;display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Add User</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('add-user-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
