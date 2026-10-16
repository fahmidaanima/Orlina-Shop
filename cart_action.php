<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

switch ($action) {
    case 'add':
        if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }
        // Verify product exists
        $stmt = $pdo->prepare("SELECT id, name, stock, price FROM products WHERE id=? AND stock>0");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();
        if (!$prod) { echo json_encode(['success'=>false,'message'=>'Product not available']); exit; }
        $current = $_SESSION['cart'][$productId] ?? 0;
        $newQty  = min($prod['stock'], $current + $quantity);
        $_SESSION['cart'][$productId] = $newQty;
        echo json_encode([
            'success'    => true,
            'message'    => h($prod['name']) . ' added to cart!',
            'cart_count' => cartCount(),
        ]);
        break;

    case 'update':
        if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        $items = getCartItems();
        $itemSubtotal = null;
        foreach ($items as $item) {
            if ($item['id'] == $productId) {
                $itemSubtotal = formatPrice($item['subtotal']);
                break;
            }
        }
        echo json_encode([
            'success'        => true,
            'message'        => 'Cart updated',
            'cart_count'     => cartCount(),
            'cart_total'     => formatPrice(cartTotal()),
            'item_subtotal'  => $itemSubtotal,
        ]);
        break;

    case 'remove':
        unset($_SESSION['cart'][$productId]);
        echo json_encode([
            'success'    => true,
            'message'    => 'Item removed from cart',
            'cart_count' => cartCount(),
            'cart_total' => formatPrice(cartTotal()),
        ]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}
