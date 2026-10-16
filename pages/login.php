<?php
require_once __DIR__ . '/../includes/functions.php';

// Already logged in — send to the right place
if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: ' . url('admin/index.php')); exit;
    }
    header('Location: ' . url('pages/myaccount.php')); exit;
}

// Capture redirect destination and message from query string
$redirectTo = '';
if (!empty($_GET['redirect'])) {
    $redirectTo = filter_var(trim($_GET['redirect']), FILTER_SANITIZE_URL);
    // Only allow relative or same-host URLs for safety
    if (strpos($redirectTo, 'http') === 0) {
        $host = parse_url($redirectTo, PHP_URL_HOST);
        if ($host !== $_SERVER['HTTP_HOST']) $redirectTo = '';
    }
}

// Show contextual message if redirected from cart/wishlist
$flashMsg = '';
if (!empty($_GET['msg'])) {
    $flashMsg = 'Please login to continue.';
}

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $email    = filter_var(trim($_POST['email']??''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $postRedirect = sanitize($_POST['redirect_to'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'].' '.$user['last_name'];
        setFlash('success', 'Welcome back, '.$user['first_name'].'!');

        // Role-based redirect: only super_admin goes to admin panel
        if ($user['role'] === 'super_admin') {
            header('Location: ' . url('admin/index.php')); exit;
        }
        // Redirect back to previous page if provided
        if ($postRedirect) {
            header('Location: ' . $postRedirect); exit;
        }
        header('Location: ' . url('pages/myaccount.php')); exit;
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}

define('PAGE_TITLE','Login');
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Orlina</span>
    </div>
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-subtitle">Sign in to your account to continue</p>

    <?php if ($flashMsg): ?>
    <div class="flash flash-info" style="border-radius:10px;margin-bottom:20px;">
      <i class="fas fa-info-circle"></i> <?= h($flashMsg) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="flash flash-error" style="border-radius:10px;margin-bottom:20px;"><?= h($error) ?></div>
    <?php endif ?>

    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="redirect_to" value="<?= h($redirectTo) ?>">
      <div class="form-group" style="margin-bottom:16px;">
        <label>Email Address</label>
        <input type="email" name="email" value="<?= h($_POST['email']??'') ?>" required placeholder="you@example.com" autofocus>
      </div>
      <div class="form-group" style="margin-bottom:24px;">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Your password">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Sign In</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.88rem;color:var(--text2);">
      Don't have an account? <a href="<?= url('pages/register.php') ?>" style="color:var(--primary-start);font-weight:600;">Register</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
