<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($input['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün']);
    exit;
}

// Kullanıcı giriş yapmış mı kontrol et
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Lütfen giriş yapın']);
    exit;
}

// Ürünün var olduğunu kontrol et
$product_sql = "SELECT id FROM products WHERE id = ? AND status = 'active'";
$product_stmt = $pdo->prepare($product_sql);
$product_stmt->execute([$product_id]);
$product = $product_stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
    exit;
}

// İstek listesi tablosu yoksa oluştur
$create_wishlist_sql = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
)";
$pdo->exec($create_wishlist_sql);

// İstek listesinde var mı kontrol et
$check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([$_SESSION['user_id'], $product_id]);
$exists = $check_stmt->fetch();

if ($exists) {
    // İstek listesinden çıkar
    $remove_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $remove_stmt = $pdo->prepare($remove_sql);
    $remove_stmt->execute([$_SESSION['user_id'], $product_id]);
    
    echo json_encode([
        'success' => true,
        'in_wishlist' => false,
        'message' => 'Ürün istek listesinden çıkarıldı'
    ]);
} else {
    // İstek listesine ekle
    $add_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $add_stmt = $pdo->prepare($add_sql);
    $add_stmt->execute([$_SESSION['user_id'], $product_id]);
    
    echo json_encode([
        'success' => true,
        'in_wishlist' => true,
        'message' => 'Ürün istek listesine eklendi'
    ]);
}
?>