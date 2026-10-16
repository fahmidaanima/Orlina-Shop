<?php
require_once __DIR__ . '/includes/functions.php';
define('PAGE_TITLE', 'Login');

if (isLoggedIn()) { header('Location: ' . url('pages/myaccount.php')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Security error. Please try again.'; }
    else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['flash'] = ['type'=>'success','message'=>'Welcome back, ' . h($user['first_name']) . '!'];
            $redirect = $_GET['redirect'] ?? ($user['role'] !== 'member' ? url('admin/index.php') : url('pages/myaccount.php'));
            header('Location: ' . $redirect); exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;background:var(--primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Orlina</span>
    </div>
    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-subtitle">Sign in to your account to continue shopping</p>

    <?php if ($error): ?>
    <div class="flash flash-error" style="border-radius:10px;margin-bottom:20px;"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div style="position:relative;">
          <input type="password" name="password" id="pw-input" placeholder="••••••••" required style="padding-right:44px;">
          <button type="button" onclick="togglePw()" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text2);">
            <i class="fas fa-eye" id="pw-icon"></i>
          </button>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;cursor:pointer;">
          <input type="checkbox" name="remember" style="accent-color:var(--primary-start);"> Remember me
        </label>
        <a href="#" style="font-size:0.9rem;color:var(--primary-start);">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-full">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="auth-link">
      Don't have an account? <a href="<?= url('pages/register.php') ?>">Create one free</a>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const inp = document.getElementById('pw-input');
  const ico = document.getElementById('pw-icon');
  if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
  else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
