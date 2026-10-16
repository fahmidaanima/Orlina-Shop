<?php
require_once __DIR__ . '/includes/functions.php';
define('ADMIN_PAGE', 'Settings');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $keys = ['active_currency','stripe_publishable_key','stripe_secret_key','paypal_client_id','sslcommerz_store_id','sslcommerz_store_password'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $pdo->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key,$_POST[$key],$_POST[$key]]);
        }
    }
    $_SESSION['flash'] = ['type'=>'success','message'=>'Settings saved successfully.'];
    header('Location: ' . url('admin/settings.php')); exit;
}

$settings = getSettings();
include __DIR__ . '/layout.php';
?>

<form method="POST" style="max-width:720px;">
  <?= csrf_field() ?>

  <!-- Currency -->
  <div class="admin-card" style="margin-bottom:24px;">
    <h3 style="margin-bottom:20px;">🌍 Currency Settings</h3>
    <div class="form-group">
      <label>Active Currency</label>
      <select name="active_currency" style="width:100%;max-width:280px;padding:11px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;">
        <?php foreach (['BDT'=>'BDT — Bangladeshi Taka (৳)','USD'=>'USD — US Dollar ($)','EUR'=>'EUR — Euro (€)','GBP'=>'GBP — British Pound (£)'] as $code => $label): ?>
        <option value="<?= $code ?>" <?= ($settings['active_currency']??'BDT')===$code?'selected':'' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="padding:14px;background:#fef9ee;border-radius:10px;font-size:0.88rem;color:#92400e;">
      <i class="fas fa-info-circle"></i> All prices are stored in BDT and converted dynamically using fixed exchange rates.
    </div>
  </div>

  <!-- Stripe -->
  <div class="admin-card" style="margin-bottom:24px;">
    <h3 style="margin-bottom:20px;"><i class="fab fa-stripe-s" style="color:#635bff;"></i> Stripe Settings</h3>
    <div class="admin-form-grid">
      <div class="form-group">
        <label>Publishable Key</label>
        <input type="text" name="stripe_publishable_key" value="<?= h($settings['stripe_publishable_key'] ?? '') ?>" placeholder="pk_test_…">
      </div>
      <div class="form-group">
        <label>Secret Key</label>
        <input type="text" name="stripe_secret_key" value="<?= h($settings['stripe_secret_key'] ?? '') ?>" placeholder="sk_test_…">
      </div>
    </div>
    <div style="padding:14px;background:#f0f4ff;border-radius:10px;font-size:0.88rem;color:#1e40af;">
      <i class="fas fa-shield-alt"></i> Get your Stripe API keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" style="color:#1e40af;">dashboard.stripe.com/apikeys</a>
    </div>
  </div>

  <!-- PayPal -->
  <div class="admin-card" style="margin-bottom:24px;">
    <h3 style="margin-bottom:20px;"><i class="fab fa-paypal" style="color:#003087;"></i> PayPal Settings</h3>
    <div class="form-group">
      <label>PayPal Client ID</label>
      <input type="text" name="paypal_client_id" value="<?= h($settings['paypal_client_id'] ?? '') ?>" placeholder="Your PayPal Client ID">
    </div>
  </div>

  <!-- SSLCommerz -->
  <div class="admin-card" style="margin-bottom:24px;">
    <h3 style="margin-bottom:20px;"><i class="fas fa-shield-alt" style="color:#1a56db;"></i> SSLCommerz Settings</h3>
    <div class="admin-form-grid">
      <div class="form-group">
        <label>Store ID</label>
        <input type="text" name="sslcommerz_store_id" value="<?= h($settings['sslcommerz_store_id'] ?? '') ?>" placeholder="Your Store ID">
      </div>
      <div class="form-group">
        <label>Store Password</label>
        <input type="text" name="sslcommerz_store_password" value="<?= h($settings['sslcommerz_store_password'] ?? '') ?>" placeholder="Your Store Password">
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
</form>

    </div>
  </div>
</div>
</body>
</html>
