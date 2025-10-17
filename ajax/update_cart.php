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
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün!']);
    exit();
}

$db = getDB();

if ($action === 'remove') {
    // Üründen sepetten kaldır
    if (removeFromCart($_SESSION['user_id'], $product_id)) {
        echo json_encode(['success' => true, 'message' => 'Ürün sepetten kaldırıldı!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ürün kaldırılırken hata oluştu!']);
    }
} elseif ($quantity > 0) {
    // Miktar güncelle
    $product = getProduct($product_id);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı!']);
        exit();
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Yeterli stok yok!']);
        exit();
    }
    
    $stmt = $db->prepare("UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':product_id', $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sepet güncellendi!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sepet güncellenirken hata oluştu!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz miktar!']);
}
?>