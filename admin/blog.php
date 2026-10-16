<?php
define('ADMIN_PAGE','Blog');
require_once __DIR__ . '/../includes/functions.php';

// Handle POST
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['action']??'';

    if ($action==='add_cat') {
        $name = sanitize($_POST['cat_name']??'');
        $slug = slugify($name);
        try { $pdo->prepare("INSERT INTO blog_categories (name,slug) VALUES (?,?)")->execute([$name,$slug]); setFlash('success','Category added.'); } catch(Exception $e) { setFlash('error','Category already exists.'); }
        header('Location: '.url('admin/blog.php')); exit;
    }

    if (in_array($action,['add_post','edit_post'])) {
        $title   = sanitize($_POST['title']??'');
        $catId   = (int)$_POST['category_id'] ?: null;
        $content = $_POST['content']??'';
        $status  = $_POST['status']==='published' ? 'published' : 'draft';
        $slug    = slugify($title);
        $imgUrl  = '';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp'])) {
                $dir = __DIR__.'/uploads/'; if (!is_dir($dir)) mkdir($dir,0755,true);
                $fname = 'blog_'.uniqid().'.'.$ext;
                move_uploaded_file($_FILES['image']['tmp_name'],$dir.$fname);
                $imgUrl = BASE_URL.'/admin/uploads/'.$fname;
            }
        } elseif (!empty($_POST['image_url'])) { $imgUrl = sanitize($_POST['image_url']); }

        if ($action==='add_post') {
            $pdo->prepare("INSERT INTO blog_posts (category_id,title,slug,content,featured_image,status) VALUES (?,?,?,?,?,?)")->execute([$catId,$title,$slug,$content,$imgUrl?:null,$status]);
            setFlash('success','Post published.');
        } else {
            $pid = (int)$_POST['post_id'];
            $pdo->prepare("UPDATE blog_posts SET category_id=?,title=?,content=?,status=?".($imgUrl?",featured_image=?":"")." WHERE id=?")->execute($imgUrl ? [$catId,$title,$content,$status,$imgUrl,$pid] : [$catId,$title,$content,$status,$pid]);
            setFlash('success','Post updated.');
        }
        header('Location: '.url('admin/blog.php')); exit;
    }

    if ($action==='delete_post') {
        $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([(int)$_POST['post_id']]);
        setFlash('success','Post deleted.');
        header('Location: '.url('admin/blog.php')); exit;
    }
}

$blogCategories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();
$posts = $pdo->query("SELECT bp.*, bc.name AS cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id ORDER BY bp.created_at DESC")->fetchAll();
$editPost = null;
if (isset($_GET['edit'])) { $ep = $pdo->prepare("SELECT * FROM blog_posts WHERE id=?"); $ep->execute([(int)$_GET['edit']]); $editPost=$ep->fetch(); }

// ── Include layout AFTER all POST/redirect handling (PRG pattern) ──
include __DIR__ . '/layout.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <h2 style="font-size:1.3rem;">Blog Posts (<?= count($posts) ?>)</h2>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-post-modal').style.display='flex'"><i class="fas fa-plus"></i> New Post</button>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if ($posts): foreach ($posts as $p): ?>
    <tr>
      <td><?php if ($p['featured_image']): ?><img src="<?= h($p['featured_image']) ?>" width="60" height="40" style="object-fit:cover;border-radius:6px;"><?php else: ?>—<?php endif; ?></td>
      <td><strong><?= h($p['title']) ?></strong><br><small style="color:var(--text2);"><?= h($p['slug']) ?></small></td>
      <td><?= h($p['cat_name']??'—') ?></td>
      <td><span class="badge-pill <?= $p['status']==='published'?'badge-success':'badge-gray' ?>"><?= ucfirst($p['status']) ?></span></td>
      <td style="font-size:0.8rem;"><?= date('M j, Y',strtotime($p['created_at'])) ?></td>
      <td>
        <div class="admin-table-actions">
          <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-icon-edit"><i class="fas fa-edit"></i></a>
          <form method="POST" data-confirm="Delete this post?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_post"><input type="hidden" name="post_id" value="<?= $p['id'] ?>"><button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button></form>
        </div>
      </td>
    </tr>
    <?php endforeach; else: ?><tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text2);">No posts yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Add Post Modal -->
<div class="modal-overlay" id="add-post-modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box" style="max-width:680px;">
    <div class="modal-header"><h3 class="modal-title">New Blog Post</h3><button class="modal-close" onclick="document.getElementById('add-post-modal').style.display='none'">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_post">
      <div class="admin-form-group" style="margin-bottom:14px;"><label>Title *</label><input type="text" name="title" required></div>
      <div class="admin-form-grid" style="margin-bottom:14px;">
        <div class="admin-form-group"><label>Category</label><select name="category_id"><option value="">None</option><?php foreach ($blogCategories as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div>
        <div class="admin-form-group"><label>Featured Image Upload</label><input type="file" name="image" accept="image/*"></div>
        <div class="admin-form-group"><label>Or Image URL</label><input type="url" name="image_url"></div>
      </div>
      <div class="admin-form-group" style="margin-bottom:16px;"><label>Content *</label><textarea name="content" rows="8" required style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:10px;font-family:'Outfit',sans-serif;resize:vertical;outline:none;"></textarea></div>
      <div style="display:flex;gap:12px;"><button type="submit" class="btn btn-primary">Publish Post</button><button type="button" class="btn btn-outline" onclick="document.getElementById('add-post-modal').style.display='none'">Cancel</button></div>
    </form>
  </div>
</div>

<!-- Edit Post Modal -->
<?php if ($editPost): ?>
<div class="modal-overlay" id="edit-post-modal" style="display:flex;" onclick="if(event.target===this)window.location='<?= url('admin/blog.php') ?>'">
  <div class="modal-box" style="max-width:680px;">
    <div class="modal-header"><h3 class="modal-title">Edit Post</h3><a href="<?= url('admin/blog.php') ?>" class="modal-close">&times;</a></div>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit_post">
      <input type="hidden" name="post_id" value="<?= $editPost['id'] ?>">
      <div class="admin-form-group" style="margin-bottom:14px;"><label>Title *</label><input type="text" name="title" value="<?= h($editPost['title']) ?>" required></div>
      <div class="admin-form-grid" style="margin-bottom:14px;">
        <div class="admin-form-group"><label>Category</label><select name="category_id"><option value="">None</option><?php foreach ($blogCategories as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$editPost['category_id']?'selected':'' ?>><?= h($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Status</label><select name="status"><option value="draft" <?= $editPost['status']==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= $editPost['status']==='published'?'selected':'' ?>>Published</option></select></div>
        <div class="admin-form-group"><label>New Image</label><input type="file" name="image" accept="image/*"></div>
        <div class="admin-form-group"><label>Or Image URL</label><input type="url" name="image_url" placeholder="Leave blank to keep existing"></div>
      </div>
      <div class="admin-form-group" style="margin-bottom:16px;"><label>Content</label><textarea name="content" rows="8" style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:10px;font-family:'Outfit',sans-serif;resize:vertical;outline:none;"><?= h($editPost['content']) ?></textarea></div>
      <div style="display:flex;gap:12px;"><button type="submit" class="btn btn-primary">Save</button><a href="<?= url('admin/blog.php') ?>" class="btn btn-outline">Cancel</a></div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
