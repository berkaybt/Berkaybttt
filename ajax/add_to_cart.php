<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün veya miktar']);
    exit;
}

// Ürünün var olduğunu ve stokta olduğunu kontrol et
$product = getProductById($pdo, $product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
    exit;
}

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Yeterli stok yok']);
    exit;
}

// Sepete ekle
addToCart($product_id, $quantity);

// Güncel sepet sayısını al
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

echo json_encode([
    'success' => true,
    'message' => 'Ürün sepete eklendi',
    'cart_count' => $cart_count
]);
?>