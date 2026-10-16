<?php
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) { header('Location: '.url('pages/myaccount.php')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $firstName = sanitize($_POST['first_name']??'');
    $lastName  = sanitize($_POST['last_name']??'');
    $email     = filter_var(trim($_POST['email']??''), FILTER_SANITIZE_EMAIL);
    $phone     = sanitize($_POST['phone']??'');
    $password  = $_POST['password']??'';
    $confirm   = $_POST['confirm_password']??'';

    if (strlen($password) < 6)      $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm)  $error = 'Passwords do not match.';
    else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name,last_name,email,phone,password,role) VALUES (?,?,?,?,?,'member')");
            $stmt->execute([$firstName,$lastName,$email,$phone,$hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_role'] = 'member';
            $_SESSION['user_name'] = "$firstName $lastName";
            setFlash('success', "Welcome to Orlina, $firstName!");
            header('Location: '.url('pages/myaccount.php')); exit;
        }
    }
}

define('PAGE_TITLE','Register');
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card" style="max-width:500px;">
    <div class="auth-logo">
      <span style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Orlina</span>
    </div>
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Join Orlina and shop the finest women's fashion</p>

    <?php if ($error): ?>
    <div class="flash flash-error" style="border-radius:10px;margin-bottom:20px;"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-grid" style="margin-bottom:16px;">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?= h($_POST['first_name']??'') ?>" required placeholder="Fahmida">
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" value="<?= h($_POST['last_name']??'') ?>" required placeholder="Islam">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label>Email Address *</label>
        <input type="email" name="email" value="<?= h($_POST['email']??'') ?>" required placeholder="you@example.com">
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?= h($_POST['phone']??'') ?>" placeholder="+880 1700-000000">
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label>Password *</label>
        <input type="password" name="password" required placeholder="Min. 6 characters">
      </div>
      <div class="form-group" style="margin-bottom:24px;">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" required placeholder="Repeat password">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:0.88rem;color:var(--text2);">
      Already have an account? <a href="<?= url('pages/login.php') ?>" style="color:var(--primary-start);font-weight:600;">Sign In</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
