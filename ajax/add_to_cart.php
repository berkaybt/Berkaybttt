<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız']);
    exit;
}

if($_POST) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz ürün veya miktar']);
        exit;
    }
    
    // Ürünün var olup olmadığını kontrol et
    $product = getProductById($product_id);
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
        exit;
    }
    
    // Stok kontrolü
    if($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Yeterli stok yok']);
        exit;
    }
    
    $result = addToCart($_SESSION['user_id'], $product_id, $quantity);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Ürün sepete eklendi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sepete ekleme başarısız']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
}
?>