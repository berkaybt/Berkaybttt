<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek!']);
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$product_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün veya miktar!']);
    exit();
}

// Ürün kontrolü
$product = getProduct($product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı!']);
    exit();
}

// Stok kontrolü
if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Yeterli stok yok!']);
    exit();
}

// Sepete ekle
if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
    echo json_encode(['success' => true, 'message' => 'Ürün sepete eklendi!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Sepete eklenirken hata oluştu!']);
}
?>