<?php
define('ADMIN_PAGE','Products');
require_once __DIR__ . '/../includes/functions.php';

$categories = getCategories();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['action']??'';

    if ($action==='add' || $action==='edit') {
        $name    = sanitize($_POST['name']??'');
        $catId   = (int)$_POST['category_id'];
        $price   = (float)$_POST['price'];
        $oldPrice= $_POST['old_price']!=='' ? (float)$_POST['old_price'] : null;
        $stock   = (int)$_POST['stock'];
        $badge   = in_array($_POST['badge'],['NEW','SALE','TRENDING']) ? $_POST['badge'] : null;
        $featured= isset($_POST['featured']) ? 1 : 0;
        $desc    = sanitize($_POST['description']??'');
        $slug    = slugify($name);
        $imgUrl  = '';

        if (!empty($_FILES['image']['name'])) {
            $ext  = strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp','gif'])) {
                $dir = __DIR__ . '/uploads/';
                if (!is_dir($dir)) mkdir($dir,0755,true);
                $fname = uniqid('prod_').'.'.$ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $dir.$fname);
                $imgUrl = BASE_URL.'/admin/uploads/'.$fname;
            }
        } elseif (!empty($_POST['image_url'])) {
            $imgUrl = sanitize($_POST['image_url']);
        }

        if ($action==='add') {
            // Ensure unique slug
            $base = $slug; $i = 1;
            while ($pdo->prepare("SELECT id FROM products WHERE slug=?")->execute([$slug]) && $pdo->query("SELECT id FROM products WHERE slug='$slug'")->fetch()) {
                $slug = $base.'-'.$i++;
            }
            $stmt = $pdo->prepare("INSERT INTO products (category_id,name,slug,description,price,old_price,stock,badge,featured) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$catId,$name,$slug,$desc,$price,$oldPrice,$stock,$badge,$featured]);
            $pid = $pdo->lastInsertId();
            if ($imgUrl) $pdo->prepare("INSERT INTO product_images (product_id,image_url) VALUES (?,?)")->execute([$pid,$imgUrl]);
            setFlash('success',"Product \"$name\" added.");
        } else {
            $pid = (int)$_POST['product_id'];
            $pdo->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,old_price=?,stock=?,badge=?,featured=? WHERE id=?")->execute([$catId,$name,$desc,$price,$oldPrice,$stock,$badge,$featured,$pid]);
            if ($imgUrl) {
                $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$pid]);
                $pdo->prepare("INSERT INTO product_images (product_id,image_url) VALUES (?,?)")->execute([$pid,$imgUrl]);
            }
            setFlash('success',"Product updated.");
        }
        header('Location: '.url('admin/products.php')); exit;
    }

    if ($action==='delete') {
        $pid = (int)$_POST['product_id'];
        $inOrder = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id=?");
        $inOrder->execute([$pid]);
        if ($inOrder->fetchColumn() > 0) { setFlash('error','Cannot delete: product is in existing orders.'); }
        else {
            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$pid]);
            setFlash('success','Product deleted.');
        }
        header('Location: '.url('admin/products.php')); exit;
    }
}

// ── Include layout AFTER all POST/redirect handling (PRG pattern) ──
include __DIR__ . '/layout.php';

$search = sanitize($_GET['q']??'');
$catFilter = (int)($_GET['cat']??0);
$page = max(1,(int)($_GET['page']??1));
$perPage = 15; $offset = ($page-1)*$perPage;

$where = []; $params = [];
if ($search) { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; }
if ($catFilter) { $where[] = 'p.category_id=?'; $params[] = $catFilter; }
$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM products p $whereStr"); $total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = max(1,ceil($totalCount/$perPage));

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb FROM products p LEFT JOIN categories c ON p.category_id=c.id $whereStr ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Edit product data
$editProduct = null;
if (isset($_GET['edit'])) {
    $editStmt = $pdo->prepare("SELECT * FROM products WHERE id=?"); $editStmt->execute([(int)$_GET['edit']]); $editProduct = $editStmt->fetch();
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <h2 style="font-size:1.3rem;">Products (<?= $totalCount ?>)</h2>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-modal').style.display='flex'">
    <i class="fas fa-plus"></i> Add Product
  </button>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom:24px;">
  <div class="admin-card-body padded">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
      <div class="admin-form-group">
        <label>Search</label>
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Product name…">
      </div>
      <div class="admin-form-group">
        <label>Category</label>
        <select name="cat">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="<?= url('admin/products.php') ?>" class="btn btn-sm btn-outline">Clear</a>
    </form>
  </div>
</div>

<!-- Table -->
<div class="admin-card">
  <div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Badge</th><th>Featured</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if ($products): foreach ($products as $p): ?>
    <tr>
      <td><img src="<?= h($p['thumb'] ?: defaultProductImage($p['cat_name']??'')) ?>" width="52" height="52" alt=""></td>
      <td><strong><?= h($p['name']) ?></strong><br><small style="color:var(--text2);"><?= h($p['slug']) ?></small></td>
      <td><?= h($p['cat_name']??'') ?></td>
      <td><?= formatPrice($p['price']) ?><?php if ($p['old_price']): ?><br><small style="text-decoration:line-through;color:var(--text2);"><?= formatPrice($p['old_price']) ?></small><?php endif; ?></td>
      <td><span class="badge-pill <?= $p['stock']<=0?'badge-danger':($p['stock']<=5?'badge-warning':'badge-success') ?>"><?= $p['stock'] ?></span></td>
      <td><?php if ($p['badge']): ?><span class="badge-pill badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span><?php else: ?>—<?php endif; ?></td>
      <td><?= $p['featured'] ? '<i class="fas fa-star" style="color:var(--accent);"></i>' : '<i class="far fa-star" style="color:var(--border);"></i>' ?></td>
      <td>
        <div class="admin-table-actions">
          <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-icon-edit"><i class="fas fa-edit"></i></a>
          <form method="POST" data-confirm="Delete '<?= h($p['name']) ?>'? This cannot be undone." style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn-icon btn-icon-delete"><i class="fas fa-trash"></i></button>
          </form>
          <a href="<?= url('pages/product.php') ?>?slug=<?= h($p['slug']) ?>" target="_blank" class="btn-icon btn-icon-view"><i class="fas fa-eye"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text2);">No products found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="pagination" style="padding:16px;">
    <?php for ($i=1;$i<=$totalPages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ADD Modal -->
<div class="modal-overlay" id="add-modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Add New Product</h3>
      <button class="modal-close" onclick="document.getElementById('add-modal').style.display='none'">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="admin-form-grid">
        <div class="admin-form-group full"><label>Product Name *</label><input type="text" name="name" required placeholder="Elegant Silk Dress"></div>
        <div class="admin-form-group"><label>Category *</label><select name="category_id" required><option value="">Select…</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Badge</label><select name="badge"><option value="">None</option><option value="NEW">NEW</option><option value="SALE">SALE</option><option value="TRENDING">TRENDING</option></select></div>
        <div class="admin-form-group"><label>Price (BDT) *</label><input type="number" name="price" step="0.01" required placeholder="2500"></div>
        <div class="admin-form-group"><label>Old Price (BDT)</label><input type="number" name="old_price" step="0.01" placeholder="3000"></div>
        <div class="admin-form-group"><label>Stock *</label><input type="number" name="stock" required value="10" min="0"></div>
        <div class="admin-form-group full"><label>Description</label><textarea name="description" rows="3" placeholder="Product description…"></textarea></div>
        <div class="admin-form-group full"><label>Image Upload</label><input type="file" name="image" accept="image/*"></div>
        <div class="admin-form-group full"><label>Or Image URL</label><input type="url" name="image_url" placeholder="https://…"></div>
        <div class="admin-form-group full"><label class="form-check"><input type="checkbox" name="featured" value="1"> Mark as Featured</label></div>
      </div>
      <div style="margin-top:20px;display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Add Product</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('add-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT Modal -->
<?php if ($editProduct): ?>
<div class="modal-overlay" id="edit-modal" style="display:flex;" onclick="if(event.target===this)window.location='<?= url('admin/products.php') ?>'">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Edit Product</h3>
      <a href="<?= url('admin/products.php') ?>" class="modal-close">&times;</a>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="admin-form-grid">
        <div class="admin-form-group full"><label>Product Name *</label><input type="text" name="name" value="<?= h($editProduct['name']) ?>" required></div>
        <div class="admin-form-group"><label>Category *</label><select name="category_id" required><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$editProduct['category_id']?'selected':'' ?>><?= h($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Badge</label><select name="badge"><option value="">None</option><?php foreach (['NEW','SALE','TRENDING'] as $b): ?><option value="<?= $b ?>" <?= $editProduct['badge']===$b?'selected':'' ?>><?= $b ?></option><?php endforeach; ?></select></div>
        <div class="admin-form-group"><label>Price (BDT) *</label><input type="number" name="price" value="<?= $editProduct['price'] ?>" step="0.01" required></div>
        <div class="admin-form-group"><label>Old Price (BDT)</label><input type="number" name="old_price" value="<?= $editProduct['old_price'] ?>" step="0.01"></div>
        <div class="admin-form-group"><label>Stock</label><input type="number" name="stock" value="<?= $editProduct['stock'] ?>" min="0"></div>
        <div class="admin-form-group full"><label>Description</label><textarea name="description" rows="3"><?= h($editProduct['description']??'') ?></textarea></div>
        <div class="admin-form-group full"><label>New Image Upload</label><input type="file" name="image" accept="image/*"></div>
        <div class="admin-form-group full"><label>Or New Image URL</label><input type="url" name="image_url" placeholder="Leave blank to keep existing"></div>
        <div class="admin-form-group full"><label class="form-check"><input type="checkbox" name="featured" value="1" <?= $editProduct['featured']?'checked':'' ?>> Featured</label></div>
      </div>
      <div style="margin-top:20px;display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="<?= url('admin/products.php') ?>" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
