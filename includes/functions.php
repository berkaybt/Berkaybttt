<?php
// Genel fonksiyonlar
session_start();

// Güvenlik fonksiyonları
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Ürün fonksiyonları
function getProducts($limit = null, $category_id = null, $featured = null) {
    $db = getDB();
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    
    if ($category_id) {
        $sql .= " AND p.category_id = :category_id";
    }
    
    if ($featured !== null) {
        $sql .= " AND p.featured = :featured";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $db->prepare($sql);
    
    if ($category_id) {
        $stmt->bindParam(':category_id', $category_id);
    }
    
    if ($featured !== null) {
        $stmt->bindParam(':featured', $featured);
    }
    
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProduct($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories() {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sepet fonksiyonları
function addToCart($user_id, $product_id, $quantity = 1) {
    $db = getDB();
    
    // Önce sepette var mı kontrol et
    $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Varsa miktarı artır
        $stmt = $db->prepare("UPDATE cart SET quantity = quantity + :quantity 
                             WHERE user_id = :user_id AND product_id = :product_id");
    } else {
        // Yoksa yeni ekle
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                             VALUES (:user_id, :product_id, :quantity)");
    }
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':quantity', $quantity);
    
    return $stmt->execute();
}

function getCartItems($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT c.*, p.name, p.price, p.image, (c.quantity * p.price) as subtotal
                         FROM cart c 
                         JOIN products p ON c.product_id = p.id 
                         WHERE c.user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCartTotal($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(c.quantity * p.price) as total
                         FROM cart c 
                         JOIN products p ON c.product_id = p.id 
                         WHERE c.user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

function removeFromCart($user_id, $product_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    return $stmt->execute();
}

function clearCart($user_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    return $stmt->execute();
}

// Para formatı
function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

// Resim yükleme
function uploadImage($file, $directory = 'images/products/') {
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $filepath = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}
?>