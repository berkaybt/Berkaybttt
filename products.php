<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Kategorileri getir
$categories = getCategories();

// Ürünleri getir
if($search) {
    $products = searchProducts($search);
    $page_title = "Arama: $search";
} elseif($category_id) {
    $products = getProducts($category_id);
    $category = getCategoryById($category_id);
    $page_title = $category ? $category['name'] : 'Ürünler';
} else {
    $products = getProducts();
    $page_title = 'Tüm Ürünler';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - E-Ticaret</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-shopping-bag"></i> E-Ticaret</h1>
                </div>
                
                <nav class="nav">
                    <ul>
                        <li><a href="index.php">Ana Sayfa</a></li>
                        <li><a href="products.php">Ürünler</a></li>
                        <li><a href="about.php">Hakkımızda</a></li>
                        <li><a href="contact.php">İletişim</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="products.php">
                            <input type="text" name="search" placeholder="Ürün ara..." value="<?php echo $search; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="profile.php" class="user-link"><i class="fas fa-user"></i> Profil</a>
                            <a href="logout.php" class="user-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                        <?php else: ?>
                            <a href="login.php" class="user-link"><i class="fas fa-sign-in-alt"></i> Giriş</a>
                            <a href="register.php" class="user-link"><i class="fas fa-user-plus"></i> Kayıt</a>
                        <?php endif; ?>
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="cartCount">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 2rem 0;">
        <div class="products-page">
            <h1><?php echo $page_title; ?></h1>
            
            <!-- Kategori Filtreleri -->
            <div class="category-filters">
                <a href="products.php" class="filter-btn <?php echo !$category_id ? 'active' : ''; ?>">Tümü</a>
                <?php foreach($categories as $category): ?>
                    <a href="products.php?category=<?php echo $category['id']; ?>" 
                       class="filter-btn <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                        <?php echo $category['name']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Ürünler -->
            <div class="products-grid" style="margin-top: 2rem;">
                <?php if(empty($products)): ?>
                    <div class="no-products">
                        <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>Ürün bulunamadı</h3>
                        <p>Aradığınız kriterlere uygun ürün bulunamadı.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Sepete Ekle
                                </button>
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> Görüntüle
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-category"><?php echo $product['category_name']; ?></p>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(4.5)</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>E-Ticaret</h3>
                    <p>Kaliteli ürünler, uygun fiyatlar ve müşteri memnuniyeti odaklı hizmet anlayışımızla yanınızdayız.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Hızlı Linkler</h4>
                    <ul>
                        <li><a href="index.php">Ana Sayfa</a></li>
                        <li><a href="products.php">Ürünler</a></li>
                        <li><a href="about.php">Hakkımızda</a></li>
                        <li><a href="contact.php">İletişim</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Müşteri Hizmetleri</h4>
                    <ul>
                        <li><a href="help.php">Yardım Merkezi</a></li>
                        <li><a href="shipping.php">Kargo Bilgileri</a></li>
                        <li><a href="returns.php">İade ve Değişim</a></li>
                        <li><a href="privacy.php">Gizlilik Politikası</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>İletişim</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> İstanbul, Türkiye</p>
                        <p><i class="fas fa-phone"></i> +90 212 555 0123</p>
                        <p><i class="fas fa-envelope"></i> info@eticaret.com</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 E-Ticaret. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>

<style>
.category-filters {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    color: #333;
    text-decoration: none;
    border-radius: 25px;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.filter-btn:hover,
.filter-btn.active {
    background: #e74c3c;
    color: white;
    border-color: #e74c3c;
}

.no-products {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.product-category {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
</style>