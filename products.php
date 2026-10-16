<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Products');
requireAdmin();

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['edit'] ?? 0);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    if (verify_csrf()) {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['product_id']]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Product deleted successfully.'];
    }
    header('Location: ' . url('admin/products.php')); exit;
}

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!verify_csrf()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Security error.']; header('Location: ' . url('admin/products.php')); exit; }

    $pid      = (int)($_POST['product_id'] ?? 0);
    $catId    = (int)$_POST['category_id'];
    $name     = trim($_POST['name']);
    $slug     = slugify($_POST['slug'] ?: $_POST['name']);
    $desc     = trim($_POST['description'] ?? '');
    $price    = (float)$_POST['price'];
    $oldPrice = $_POST['old_price'] ? (float)$_POST['old_price'] : null;
    $badge    = $_POST['badge'] ?: null;
    $stock    = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $imgUrls  = array_filter(array_map('trim', explode("\n", $_POST['image_urls'] ?? '')));

    if ($pid) {
        $pdo->prepare("UPDATE products SET category_id=?,name=?,slug=?,description=?,price=?,old_price=?,badge=?,stock=?,featured=? WHERE id=?")
            ->execute([$catId,$name,$slug,$desc,$price,$oldPrice,$badge,$stock,$featured,$pid]);
        if ($imgUrls) {
            $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$pid]);
            foreach ($imgUrls as $u) $pdo->prepare("INSERT INTO product_images (product_id,image_url) VALUES (?,?)")->execute([$pid,$u]);
        }
        $_SESSION['flash'] = ['type'=>'success','message'=>'Product updated successfully.'];
    } else {
        $pdo->prepare("INSERT INTO products (category_id,name,slug,description,price,old_price,badge,stock,featured) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$catId,$name,$slug,$desc,$price,$oldPrice,$badge,$stock,$featured]);
        $newId = $pdo->lastInsertId();
        foreach ($imgUrls as $u) $pdo->prepare("INSERT INTO product_images (product_id,image_url) VALUES (?,?)")->execute([$newId,$u]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Product created successfully.'];
    }
    header('Location: ' . url('admin/products.php')); exit;
}

$categories = getCategories();
$editProduct = null;
$editImages  = [];
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
    $editImages  = getProductImages($editId);
    $action = 'edit';
}

$search = $_GET['s'] ?? '';
$where  = $search ? "WHERE p.name LIKE ?" : '';
$params = $search ? ["%$search%"] : [];
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name,
           (SELECT image_url FROM product_images WHERE product_id=p.id LIMIT 1) AS thumb
    FROM products p LEFT JOIN categories c ON p.category_id=c.id
    $where ORDER BY p.created_at DESC LIMIT 50
");
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/layout.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <div style="display:flex;gap:12px;">
    <form method="GET" style="display:flex;gap:8px;">
      <input type="text" name="s" value="<?= h($search) ?>" placeholder="Search products…" style="padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;outline:none;">
      <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
    </form>
  </div>
  <button class="btn btn-primary" onclick="openModal('product-modal')">
    <i class="fas fa-plus"></i> Add Product
  </button>
</div>

<!-- Products Table -->
<div class="admin-card">
  <table class="data-table">
    <thead>
      <tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Badge</th><th>Featured</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td><img src="<?= h($p['thumb'] ?? 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=60&h=60&fit=crop') ?>" class="img-preview" alt=""></td>
        <td><strong><?= h($p['name']) ?></strong><br><small style="color:var(--text2);"><?= h($p['slug']) ?></small></td>
        <td><?= h($p['category_name']) ?></td>
        <td>
          <?= formatPrice($p['price']) ?>
          <?php if ($p['old_price']): ?><br><small style="text-decoration:line-through;color:var(--text2);"><?= formatPrice($p['old_price']) ?></small><?php endif; ?>
        </td>
        <td>
          <span style="color:<?= $p['stock'] <= 5 ? '#dc2626' : ($p['stock'] <= 20 ? '#d97706' : '#16a34a') ?>;font-weight:600;">
            <?= $p['stock'] ?>
          </span>
        </td>
        <td>
          <?php if ($p['badge']): ?>
          <span class="product-badge badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span>
          <?php else: echo '<span style="color:var(--text2);">—</span>'; endif; ?>
        </td>
        <td><?= $p['featured'] ? '<i class="fas fa-star" style="color:var(--accent);"></i>' : '<i class="far fa-star" style="color:var(--border);"></i>' ?></td>
        <td>
          <div class="admin-table-actions">
            <button class="btn-icon btn-icon-view" onclick="window.open('<?= url('pages/product.php') ?>?slug=<?= h($p['slug']) ?>','_blank')" title="View"><i class="fas fa-eye"></i></button>
            <button class="btn-icon btn-icon-edit" onclick="editProduct(<?= json_encode($p) ?>)" title="Edit"><i class="fas fa-edit"></i></button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
              <?= csrf_field() ?>
              <input type="hidden" name="delete_product" value="1">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn-icon btn-icon-delete" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$products): ?>
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">No products found. <button onclick="openModal('product-modal')" style="background:none;border:none;color:var(--primary-start);cursor:pointer;font-weight:600;">Add your first product</button></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Product Modal -->
<div class="modal-overlay" id="product-modal" style="display:none;">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-header">
      <h3 id="modal-title">Add Product</h3>
      <button class="modal-close" onclick="closeModal('product-modal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="save_product" value="1">
      <input type="hidden" name="product_id" id="modal-pid" value="0">
      <div class="admin-form-grid">
        <div class="form-group form-full">
          <label>Product Name *</label>
          <input type="text" name="name" id="modal-name" required oninput="autoSlug(this.value)">
        </div>
        <div class="form-group">
          <label>Slug</label>
          <input type="text" name="slug" id="modal-slug">
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category_id" id="modal-cat" required>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Price (BDT) *</label>
          <input type="number" name="price" id="modal-price" step="0.01" min="0" required>
        </div>
        <div class="form-group">
          <label>Old Price (BDT)</label>
          <input type="number" name="old_price" id="modal-old-price" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label>Stock *</label>
          <input type="number" name="stock" id="modal-stock" min="0" required>
        </div>
        <div class="form-group">
          <label>Badge</label>
          <select name="badge" id="modal-badge">
            <option value="">None</option>
            <option value="NEW">NEW</option>
            <option value="SALE">SALE</option>
            <option value="TRENDING">TRENDING</option>
          </select>
        </div>
        <div class="form-group form-full">
          <label>Description</label>
          <textarea name="description" id="modal-desc" rows="3" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;resize:vertical;"></textarea>
        </div>
        <div class="form-group form-full">
          <label>Image URLs (one per line — use Unsplash URLs)</label>
          <textarea name="image_urls" id="modal-imgs" rows="4" placeholder="https://images.unsplash.com/photo-xxx?w=600&h=800&fit=crop" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;resize:vertical;font-size:0.82rem;"></textarea>
          <small style="color:var(--text2);">First URL will be used as main image thumbnail.</small>
        </div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="featured" id="modal-featured">
            <span>Featured Product</span>
          </label>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:20px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product</button>
        <button type="button" class="btn btn-outline" onclick="closeModal('product-modal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display='flex'; }
function closeModal(id) { document.getElementById(id).style.display='none'; }
function autoSlug(v) {
  document.getElementById('modal-slug').value = v.toLowerCase().trim().replace(/[^a-z0-9\s-]/g,'').replace(/[\s-]+/g,'-');
}
function editProduct(p) {
  document.getElementById('modal-title').textContent  = 'Edit Product';
  document.getElementById('modal-pid').value          = p.id;
  document.getElementById('modal-name').value         = p.name;
  document.getElementById('modal-slug').value         = p.slug;
  document.getElementById('modal-cat').value          = p.category_id;
  document.getElementById('modal-price').value        = p.price;
  document.getElementById('modal-old-price').value    = p.old_price || '';
  document.getElementById('modal-stock').value        = p.stock;
  document.getElementById('modal-badge').value        = p.badge || '';
  document.getElementById('modal-desc').value         = p.description || '';
  document.getElementById('modal-featured').checked   = p.featured == 1;
  openModal('product-modal');
}
document.getElementById('product-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('product-modal');
});
</script>

    </div>
  </div>
</div>
</body>
</html>
