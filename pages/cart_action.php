<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']); exit;
}

if (!verify_csrf()) {
    echo json_encode(['success'=>false,'message'=>'Security error']); exit;
}

$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1,(int)($_POST['quantity'] ?? 1));

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

switch ($action) {
    case 'add':
        if ($productId <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }
        // Authentication guard — guests must log in before adding to cart
        if (!isLoggedIn()) {
            $currentUrl = $_SERVER['HTTP_REFERER'] ?? '';
            $loginUrl   = BASE_URL . '/pages/login.php?redirect=' . urlencode($currentUrl) . '&msg=cart';
            echo json_encode(['success'=>false,'redirect'=>$loginUrl,'message'=>'Please login to continue.']); exit;
        }
        $product = getProductById($productId);
        if (!$product || $product['stock'] < 1) { echo json_encode(['success'=>false,'message'=>'Product unavailable']); exit; }
        $current = $_SESSION['cart'][$productId] ?? 0;
        $newQty  = min($current + $quantity, $product['stock']);
        $_SESSION['cart'][$productId] = $newQty;
        echo json_encode(['success'=>true,'cart_count'=>cartCount(),'message'=>'Added to cart']);
        break;


    case 'update':
        if (isset($_SESSION['cart'][$productId])) {
            if ($quantity <= 0) { unset($_SESSION['cart'][$productId]); }
            else {
                $product = getProductById($productId);
                $maxQty  = $product ? $product['stock'] : 99;
                $_SESSION['cart'][$productId] = min($quantity, $maxQty);
            }
        }
        $total = cartTotal();
        $itemSubtotal = 0;
        if (isset($_SESSION['cart'][$productId])) {
            $prod = getProductById($productId);
            if ($prod) $itemSubtotal = $prod['price'] * $_SESSION['cart'][$productId];
        }
        echo json_encode(['success'=>true,'cart_count'=>cartCount(),'cart_total'=>$total,'item_subtotal'=>$itemSubtotal]);
        break;

    case 'remove':
        unset($_SESSION['cart'][$productId]);
        echo json_encode(['success'=>true,'cart_count'=>cartCount(),'cart_total'=>cartTotal()]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}
