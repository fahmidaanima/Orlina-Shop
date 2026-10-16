<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Register');

if (isLoggedIn()) { header('Location: ' . url('pages/myaccount.php')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Security error.'; }
    else {
        $fname    = trim($_POST['first_name'] ?? '');
        $lname    = trim($_POST['last_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$fname || !$email || !$password) { $error = 'Please fill in all required fields.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Please enter a valid email address.'; }
        elseif (strlen($password) < 8) { $error = 'Password must be at least 8 characters.'; }
        elseif ($password !== $confirm) { $error = 'Passwords do not match.'; }
        else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $check->execute([$email]);
            if ($check->fetch()) { $error = 'An account with this email already exists.'; }
            else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name,last_name,email,phone,password,role) VALUES (?,?,?,?,?,'member')");
                $stmt->execute([$fname, $lname, $email, $phone, $hash]);
                $uid = $pdo->lastInsertId();
                $_SESSION['user_id']   = $uid;
                $_SESSION['user_role'] = 'member';
                $_SESSION['user_name'] = $fname;
                $_SESSION['flash'] = ['type'=>'success','message'=>'Welcome to Orlina, ' . h($fname) . '! Your account is ready.'];
                header('Location: ' . url('pages/myaccount.php')); exit;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card" style="max-width:540px;">
    <div class="auth-logo">
      <span style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Orlina</span>
    </div>
    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Join thousands of women who love Orlina</p>

    <?php if ($error): ?>
    <div class="flash flash-error" style="border-radius:10px;margin-bottom:20px;"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?= h($_POST['first_name'] ?? '') ?>" placeholder="Nusrat" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?= h($_POST['last_name'] ?? '') ?>" placeholder="Jahan">
        </div>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" placeholder="+880 1700-000000">
      </div>
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" placeholder="Minimum 8 characters" required minlength="8">
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" placeholder="Repeat your password" required>
      </div>
      <div style="margin-bottom:20px;font-size:0.85rem;color:var(--text2);">
        By creating an account you agree to our <a href="#" style="color:var(--primary-start);">Terms of Service</a> and <a href="#" style="color:var(--primary-start);">Privacy Policy</a>.
      </div>
      <button type="submit" class="btn btn-primary btn-full">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-link">
      Already have an account? <a href="<?= url('pages/login.php') ?>">Sign in</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
