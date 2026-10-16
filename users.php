<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Users');
requireAdmin();

// Update role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role']) && verify_csrf()) {
    $uid  = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if (in_array($role, ['member','admin','super_admin']) && $uid !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'User role updated.'];
    }
    header('Location: ' . url('admin/users.php')); exit;
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user']) && verify_csrf()) {
    $uid = (int)$_POST['user_id'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'User deleted.'];
    }
    header('Location: ' . url('admin/users.php')); exit;
}

$search = $_GET['s'] ?? '';
$where  = $search ? "WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?" : '';
$params = $search ? ["%$search%","%$search%","%$search%"] : [];
$stmt   = $pdo->prepare("SELECT *, (SELECT COUNT(*) FROM orders WHERE user_id=users.id) AS order_count FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users  = $stmt->fetchAll();

include __DIR__ . '/layout.php';
?>

<div style="display:flex;justify-content:space-between;margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:8px;">
    <input type="text" name="s" value="<?= h($search) ?>" placeholder="Search users…" style="padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;outline:none;">
    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
  </form>
  <div style="font-size:0.9rem;color:var(--text2);"><?= count($users) ?> users found</div>
</div>

<div class="admin-card">
  <table class="data-table">
    <thead>
      <tr><th>User</th><th>Email</th><th>Phone</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td style="display:flex;align-items:center;gap:10px;">
          <img src="<?= $u['avatar'] ? h($u['avatar']) : 'https://ui-avatars.com/api/?name='.urlencode($u['first_name'].' '.$u['last_name']).'&background=ec4899&color=fff&size=80' ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;" alt="">
          <span style="font-weight:600;"><?= h($u['first_name'].' '.$u['last_name']) ?></span>
        </td>
        <td><?= h($u['email']) ?></td>
        <td><?= h($u['phone'] ?? '—') ?></td>
        <td>
          <span class="status-badge <?= $u['role']==='super_admin' ? 'status-paid' : ($u['role']==='admin' ? 'status-processing' : 'status-pending') ?>">
            <?= h(str_replace('_',' ',ucfirst($u['role']))) ?>
          </span>
        </td>
        <td><?= $u['order_count'] ?></td>
        <td style="font-size:0.85rem;color:var(--text2);"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
          <div class="admin-table-actions">
            <form method="POST" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="update_role" value="1">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <select name="role" onchange="this.form.submit()" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:6px;font-family:'Outfit',sans-serif;font-size:0.82rem;outline:none;cursor:pointer;">
                <option value="member"      <?= $u['role']==='member'?'selected':'' ?>>Member</option>
                <option value="admin"       <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                <option value="super_admin" <?= $u['role']==='super_admin'?'selected':'' ?>>Super Admin</option>
              </select>
            </form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user? All their orders will remain.')">
              <?= csrf_field() ?>
              <input type="hidden" name="delete_user" value="1">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button>
            </form>
          </div>
          <?php else: ?>
          <span style="font-size:0.8rem;color:var(--text2);">You</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

    </div>
  </div>
</div>
</body>
</html>
