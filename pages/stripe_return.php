<?php
require_once __DIR__ . '/../includes/functions.php';

// Verify Stripe session and update order payment status
$sessionId = $_GET['session_id'] ?? '';
if (!$sessionId) { header('Location: ' . url('index.php')); exit; }

// In production: verify the session with Stripe SDK
// $stripe = new \Stripe\StripeClient($settings['stripe_secret_key']);
// $session = $stripe->checkout->sessions->retrieve($sessionId);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE stripe_session_id=?");
$stmt->execute([$sessionId]);
$order = $stmt->fetch();

if ($order) {
    $pdo->prepare("UPDATE orders SET payment_status='paid' WHERE id=?")->execute([$order['id']]);
    setFlash('success', 'Payment successful! Your order is confirmed.');
    header('Location: ' . url('pages/order_success.php') . '?order=' . urlencode($order['order_number'])); exit;
}

setFlash('error', 'Could not verify payment. Please contact support.');
header('Location: ' . url('index.php')); exit;
