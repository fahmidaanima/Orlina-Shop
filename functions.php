<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/currency.php';

// ── CSRF ──────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function verify_csrf(): bool {
    return isset($_POST['csrf_token']) && hash_equals(csrf_token(), $_POST['csrf_token']);
}

// ── AUTH ──────────────────────────────────────────────────────
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function isAdmin(): bool    { return !empty($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin','super_admin']); }
function requireLogin(string $redirect = '/pages/login.php'): void {
    if (!isLoggedIn()) { header('Location: ' . BASE_URL . $redirect); exit; }
}
function requireAdmin(): void {
    if (!isAdmin()) { header('Location: ' . BASE_URL . '/index.php'); exit; }
}
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// ── PRODUCTS ──────────────────────────────────────────────────
function getFeaturedProducts(int $limit = 8): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) AS thumb
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.featured = 1 AND p.stock > 0
        ORDER BY p.created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getNewArrivals(int $limit = 6): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) AS thumb
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stock > 0
        ORDER BY p.created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getDealOfTheDay(): ?array {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name AS category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) AS thumb,
               ROUND((p.old_price - p.price) / p.old_price * 100) AS discount_pct
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.old_price IS NOT NULL AND p.old_price > p.price AND p.stock > 0
        ORDER BY discount_pct DESC LIMIT 1
    ");
    return $stmt->fetch() ?: null;
}

function getProductBySlug(string $slug): ?array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function getProductImages(int $productId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getProductReviews(int $productId): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name, u.avatar
        FROM reviews r JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getProductRating(int $productId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS count FROM reviews WHERE product_id = ? AND status='approved'");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

function getCategories(): array {
    global $pdo;
    return $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}

function getSettings(): array {
    global $pdo;
    $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
    return $out;
}

// ── CART ──────────────────────────────────────────────────────
function getCart(): array { return $_SESSION['cart'] ?? []; }

function cartCount(): int {
    return array_sum(getCart());
}

function getCartItems(): array {
    global $pdo;
    $cart = getCart();
    if (!$cart) return [];
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) AS thumb
        FROM products p WHERE p.id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    foreach ($products as &$p) {
        $p['quantity'] = $cart[$p['id']];
        $p['subtotal'] = $p['price'] * $p['quantity'];
    }
    return $products;
}

function cartTotal(): float {
    $total = 0;
    foreach (getCartItems() as $item) $total += $item['subtotal'];
    return $total;
}

// ── WISHLIST ──────────────────────────────────────────────────
function isWishlisted(int $productId): bool {
    if (!isLoggedIn()) return false;
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    return (bool)$stmt->fetch();
}

// ── UTILITY ──────────────────────────────────────────────────
function h(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function generateOrderNumber(): string {
    return 'ORL-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Ymd');
}

function starRating(float $rating): string {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) $html .= '<i class="fas fa-star"></i>';
        elseif ($i - 0.5 <= $rating) $html .= '<i class="fas fa-star-half-alt"></i>';
        else $html .= '<i class="far fa-star"></i>';
    }
    $html .= '</span>';
    return $html;
}

function getUnsplashImage(string $query, int $width = 600, int $height = 800): string {
    // Use Unsplash source for fashion images
    return "https://source.unsplash.com/{$width}x{$height}/?{$query},fashion,women";
}

// ── BASE URL ──────────────────────────────────────────────────
if (!defined('BASE_URL')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $isOrlina = stripos($uri, '/orlina') === 0;
    if ($isOrlina) {
        $parts = explode('/', trim($uri, '/'));
        define('BASE_URL', '/' . ($parts[0] ?? 'orlina'));
    } else {
        define('BASE_URL', '');
    }
}

function url(string $path): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}
