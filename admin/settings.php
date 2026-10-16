<?php
define('ADMIN_PAGE','Settings');
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $keys = ['active_currency','stripe_publishable_key','stripe_secret_key','paypal_client_id','sslcommerz_store_id','sslcommerz_store_password'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $val = sanitize($_POST[$key]);
            $check = $pdo->prepare("SELECT id FROM settings WHERE setting_key=?"); $check->execute([$key]);
            if ($check->fetch()) { $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$val,$key]); }
            else { $pdo->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (?,?)")->execute([$key,$val]); }
        }
    }
    setFlash('success','Settings saved.');
    header('Location: '.url('admin/settings.php')); exit;
}

$settings = getSettings();
?>

<h2 style="font-size:1.3rem;margin-bottom:24px;">Site Settings</h2>

<form method="POST">
  <?= csrf_field() ?>

  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h3 class="admin-card-title"><i class="fas fa-globe" style="color:var(--primary-start);margin-right:8px;"></i>Currency Settings</h3></div>
    <div class="admin-card-body padded">
      <div class="admin-form-group" style="max-width:300px;">
        <label>Active Currency</label>
        <select name="active_currency">
          <?php foreach (['BDT'=>'BDT — Bangladeshi Taka','USD'=>'USD — US Dollar','EUR'=>'EUR — Euro','GBP'=>'GBP — British Pound'] as $code => $label): ?>
          <option value="<?= $code ?>" <?= ($settings['active_currency']??'BDT')===$code?'selected':'' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <p style="margin-top:8px;font-size:0.82rem;color:var(--text2);">All prices are stored in BDT and converted dynamically for display.</p>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h3 class="admin-card-title"><i class="fas fa-credit-card" style="color:var(--primary-start);margin-right:8px;"></i>Stripe Settings</h3></div>
    <div class="admin-card-body padded">
      <div class="admin-form-grid">
        <div class="admin-form-group">
          <label>Publishable Key</label>
          <input type="text" name="stripe_publishable_key" value="<?= h($settings['stripe_publishable_key']??'') ?>" placeholder="pk_live_…">
        </div>
        <div class="admin-form-group">
          <label>Secret Key</label>
          <input type="password" name="stripe_secret_key" value="<?= h($settings['stripe_secret_key']??'') ?>" placeholder="sk_live_…">
        </div>
      </div>
      <p style="margin-top:8px;font-size:0.82rem;color:var(--text2);">Get your keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" style="color:var(--primary-start);">Stripe Dashboard</a>.</p>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h3 class="admin-card-title"><i class="fab fa-paypal" style="color:#003087;margin-right:8px;"></i>PayPal Settings</h3></div>
    <div class="admin-card-body padded">
      <div class="admin-form-group" style="max-width:500px;">
        <label>PayPal Client ID</label>
        <input type="text" name="paypal_client_id" value="<?= h($settings['paypal_client_id']??'') ?>" placeholder="AW…">
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h3 class="admin-card-title">SSLCommerz Settings</h3></div>
    <div class="admin-card-body padded">
      <div class="admin-form-grid">
        <div class="admin-form-group"><label>Store ID</label><input type="text" name="sslcommerz_store_id" value="<?= h($settings['sslcommerz_store_id']??'') ?>"></div>
        <div class="admin-form-group"><label>Store Password</label><input type="password" name="sslcommerz_store_password" value="<?= h($settings['sslcommerz_store_password']??'') ?>"></div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
