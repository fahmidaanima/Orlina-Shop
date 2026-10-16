<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Blog');
requireAdmin();

// Delete post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post']) && verify_csrf()) {
    $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([(int)$_POST['post_id']]);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Post deleted.'];
    header('Location: ' . url('admin/blog.php')); exit;
}

// Save post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post']) && verify_csrf()) {
    $pid     = (int)($_POST['post_id'] ?? 0);
    $catId   = $_POST['category_id'] ?: null;
    $title   = trim($_POST['title']);
    $slug    = slugify($_POST['slug'] ?: $title);
    $content = $_POST['content'] ?? '';
    $img     = trim($_POST['featured_image'] ?? '');
    $status  = $_POST['status'];

    if ($pid) {
        $pdo->prepare("UPDATE blog_posts SET category_id=?,title=?,slug=?,content=?,featured_image=?,status=? WHERE id=?")
            ->execute([$catId,$title,$slug,$content,$img,$status,$pid]);
    } else {
        $pdo->prepare("INSERT INTO blog_posts (category_id,title,slug,content,featured_image,status) VALUES (?,?,?,?,?,?)")
            ->execute([$catId,$title,$slug,$content,$img,$status]);
    }
    $_SESSION['flash'] = ['type'=>'success','message'=>'Post saved.'];
    header('Location: ' . url('admin/blog.php')); exit;
}

// Save category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cat']) && verify_csrf()) {
    $cname = trim($_POST['cat_name']);
    $cslug = slugify($cname);
    $pdo->prepare("INSERT IGNORE INTO blog_categories (name,slug) VALUES (?,?)")->execute([$cname,$cslug]);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Category added.'];
    header('Location: ' . url('admin/blog.php')); exit;
}

$posts    = $pdo->query("SELECT bp.*, bc.name AS cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id ORDER BY bp.created_at DESC")->fetchAll();
$blogCats = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();

include __DIR__ . '/layout.php';
?>

<div style="display:flex;justify-content:space-between;margin-bottom:24px;">
  <h2 style="font-size:1.1rem;font-weight:600;">Blog Posts (<?= count($posts) ?>)</h2>
  <button class="btn btn-primary" onclick="openModal('blog-modal')"><i class="fas fa-plus"></i> New Post</button>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:24px;">
  <div class="admin-card">
    <table class="data-table">
      <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
          <td>
            <strong><?= h($post['title']) ?></strong><br>
            <small style="color:var(--text2);">/<?= h($post['slug']) ?></small>
          </td>
          <td><?= h($post['cat_name'] ?? '—') ?></td>
          <td><span class="status-badge <?= $post['status']==='published'?'status-delivered':'status-pending' ?>"><?= h(ucfirst($post['status'])) ?></span></td>
          <td style="font-size:0.85rem;color:var(--text2);"><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
          <td>
            <div class="admin-table-actions">
              <a href="<?= url('pages/blog_single.php') ?>?slug=<?= h($post['slug']) ?>" target="_blank" class="btn-icon btn-icon-view"><i class="fas fa-eye"></i></a>
              <button class="btn-icon btn-icon-edit" onclick="editPost(<?= json_encode($post) ?>)"><i class="fas fa-edit"></i></button>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this post?')">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_post" value="1">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text2);">No posts yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div>
    <div class="admin-card">
      <h4 style="margin-bottom:16px;">Blog Categories</h4>
      <ul style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
        <?php foreach ($blogCats as $bc): ?>
        <li style="display:flex;justify-content:space-between;font-size:0.9rem;padding:6px 0;border-bottom:1px solid var(--border);">
          <span><?= h($bc['name']) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="save_cat" value="1">
        <div style="display:flex;gap:8px;">
          <input type="text" name="cat_name" placeholder="New category" required style="flex:1;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:0.88rem;outline:none;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Blog Post Modal -->
<div class="modal-overlay" id="blog-modal" style="display:none;">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-header">
      <h3 id="blog-modal-title">New Post</h3>
      <button class="modal-close" onclick="closeModal('blog-modal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="save_post" value="1">
      <input type="hidden" name="post_id" id="blog-pid" value="0">
      <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" id="blog-title" required oninput="document.getElementById('blog-slug').value=this.value.toLowerCase().replace(/[^a-z0-9\s]/g,'').replace(/\s+/g,'-')">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label>Slug</label>
          <input type="text" name="slug" id="blog-slug">
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category_id" id="blog-cat" style="width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;">
            <option value="">None</option>
            <?php foreach ($blogCats as $bc): ?>
            <option value="<?= $bc['id'] ?>"><?= h($bc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Featured Image URL</label>
        <input type="url" name="featured_image" id="blog-img" placeholder="https://images.unsplash.com/…">
      </div>
      <div class="form-group">
        <label>Content *</label>
        <textarea name="content" id="blog-content" rows="8" required style="width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;resize:vertical;"></textarea>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="blog-status" style="width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;">
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Post</button>
        <button type="button" class="btn btn-outline" onclick="closeModal('blog-modal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display='flex'; }
function closeModal(id) { document.getElementById(id).style.display='none'; }
function editPost(p) {
  document.getElementById('blog-modal-title').textContent = 'Edit Post';
  document.getElementById('blog-pid').value     = p.id;
  document.getElementById('blog-title').value   = p.title;
  document.getElementById('blog-slug').value    = p.slug;
  document.getElementById('blog-cat').value     = p.category_id || '';
  document.getElementById('blog-img').value     = p.featured_image || '';
  document.getElementById('blog-content').value = p.content;
  document.getElementById('blog-status').value  = p.status;
  openModal('blog-modal');
}
document.getElementById('blog-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('blog-modal');
});
</script>

    </div>
  </div>
</div>
</body>
</html>
