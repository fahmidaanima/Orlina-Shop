<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin('/pages/login.php');

$orderId = (int)($_GET['order'] ?? 0);
if (!$orderId) { header('Location: ' . url('pages/cart.php')); exit; }

$settings = getSettings();
$stripeKey = $settings['stripe_secret_key'] ?? '';
if (!$stripeKey || $stripeKey === 'sk_test_REPLACE') {
    setFlash('error', 'Stripe is not configured. Please pay on delivery.');
    header('Location: ' . url('index.php')); exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: ' . url('pages/cart.php')); exit; }

// Redirect to Stripe (requires stripe-php SDK)
// Placeholder: redirect to order success
setFlash('info', 'Stripe payment integration requires the Stripe PHP SDK. Order recorded as pending.');
header('Location: ' . url('pages/order_success.php') . '?order=' . urlencode($order['order_number'])); exit;
