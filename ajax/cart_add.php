<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'redirect'=> SITE_URL.'/login.php']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity  = max(1, intval($_POST['quantity'] ?? 1));

if (!$productId) {
    echo json_encode(['success'=>false,'message'=>'Invalid product.']);
    exit;
}

$pdo = getDB();

// Check product exists and has stock
$stmt = $pdo->prepare("SELECT id, stock_quantity FROM products WHERE id=? AND is_active=1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success'=>false,'message'=>'Product not found.']);
    exit;
}
if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success'=>false,'message'=>'Not enough stock available.']);
    exit;
}

$uid = $_SESSION['user_id'];

// Insert or update cart
$check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?");
$check->execute([$uid, $productId]);
$existing = $check->fetch();

if ($existing) {
    $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
    $pdo->prepare("UPDATE cart SET quantity=? WHERE id=?")->execute([$newQty, $existing['id']]);
} else {
    $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)")->execute([$uid, $productId, $quantity]);
}

// Get new cart count
$count = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
$count->execute([$uid]);
$cartCount = (int) $count->fetchColumn();

echo json_encode(['success'=>true,'cart_count'=>$cartCount,'message'=>'Added to cart!']);
