<?php
// Veritabanı fonksiyonları

function getFeaturedProducts($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT 4");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLatestProducts($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductsByCategory($pdo, $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll();
}

function getAllCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function searchProducts($pdo, $search_term) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND status = 'active' ORDER BY created_at DESC");
    $search_term = "%$search_term%";
    $stmt->execute([$search_term, $search_term]);
    return $stmt->fetchAll();
}

function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function getCartItems($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cart_items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($pdo, $product_id);
        if ($product) {
            $product['cart_quantity'] = $quantity;
            $cart_items[] = $product;
        }
    }
    
    return $cart_items;
}

function getCartTotal($pdo) {
    $cart_items = getCartItems($pdo);
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['cart_quantity'];
    }
    
    return $total;
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function updateCartQuantity($product_id, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($product_id);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>