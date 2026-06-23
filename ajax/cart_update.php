<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false]); exit; }

$cartId  = intval($_POST['cart_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));
$uid = $_SESSION['user_id'];

$pdo  = getDB();
$stmt = $pdo->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
$stmt->execute([$quantity, $cartId, $uid]);

echo json_encode(['success'=>true]);
