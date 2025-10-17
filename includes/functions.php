<?php
// Kullanıcı işlemleri
function registerUser($username, $email, $password, $first_name, $last_name, $phone = null) {
    global $pdo;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $phone]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($email, $password) {
    global $pdo;
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Kategori işlemleri
function getCategories() {
    global $pdo;
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    global $pdo;
    
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Ürün işlemleri
function getProducts($category_id = null, $limit = null) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1";
    
    $params = [];
    
    if($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function searchProducts($search_term) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)";
    
    $search_param = "%$search_term%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search_param, $search_param]);
    return $stmt->fetchAll();
}

// Sepet işlemleri
function addToCart($user_id, $product_id, $quantity = 1) {
    global $pdo;
    
    // Önce mevcut sepet öğesini kontrol et
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();
    
    if($existing_item) {
        // Mevcut öğenin miktarını artır
        $new_quantity = $existing_item['quantity'] + $quantity;
        $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        // Yeni öğe ekle
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$user_id, $product_id, $quantity]);
    }
}

function getCartItems($user_id) {
    global $pdo;
    
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function updateCartItem($user_id, $product_id, $quantity) {
    global $pdo;
    
    if($quantity <= 0) {
        return removeFromCart($user_id, $product_id);
    }
    
    $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$quantity, $user_id, $product_id]);
}

function removeFromCart($user_id, $product_id) {
    global $pdo;
    
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $product_id]);
}

function clearCart($user_id) {
    global $pdo;
    
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id]);
}

function getCartTotal($user_id) {
    global $pdo;
    
    $sql = "SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCartItemCount($user_id) {
    global $pdo;
    
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Sipariş işlemleri
function createOrder($user_id, $total_amount, $shipping_address, $payment_method) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Sipariş oluştur
        $sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $total_amount, $shipping_address, $payment_method]);
        $order_id = $pdo->lastInsertId();
        
        // Sepet öğelerini sipariş detaylarına ekle
        $cart_items = getCartItems($user_id);
        foreach($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Stok güncelle
            $sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Sepeti temizle
        clearCart($user_id);
        
        $pdo->commit();
        return $order_id;
    } catch(Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function getUserOrders($user_id) {
    global $pdo;
    
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getOrderItems($order_id) {
    global $pdo;
    
    $sql = "SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Yardımcı fonksiyonlar
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if(!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}
?>