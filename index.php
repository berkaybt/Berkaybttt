<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kategorileri getir
$categories = getCategories();
$products = getProducts();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticaret Sitesi</title>
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
                        <input type="text" placeholder="Ürün ara..." id="searchInput">
                        <button type="button" id="searchBtn"><i class="fas fa-search"></i></button>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>En İyi Ürünleri Keşfedin</h2>
                <p>Kaliteli ürünler, uygun fiyatlar ve hızlı teslimat</p>
                <a href="products.php" class="btn btn-primary">Alışverişe Başla</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>Kategoriler</h2>
            <div class="categories-grid">
                <?php foreach($categories as $category): ?>
                <div class="category-card">
                    <div class="category-image">
                        <img src="assets/images/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
                    </div>
                    <h3><?php echo $category['name']; ?></h3>
                    <p><?php echo $category['description']; ?></p>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">Görüntüle</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Öne Çıkan Ürünler</h2>
            <div class="products-grid">
                <?php foreach(array_slice($products, 0, 8) as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="product-overlay">
                            <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Sepete Ekle
                            </button>
                            <button class="btn btn-secondary" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                <i class="fas fa-eye"></i> Görüntüle
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price"><?php echo number_format($product['price'], 2); ?> ₺</p>
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
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Ücretsiz Kargo</h3>
                    <p>500₺ ve üzeri alışverişlerde ücretsiz kargo</p>
                </div>
                <div class="feature">
                    <i class="fas fa-undo"></i>
                    <h3>Kolay İade</h3>
                    <p>30 gün içinde kolay iade imkanı</p>
                </div>
                <div class="feature">
                    <i class="fas fa-headset"></i>
                    <h3>7/24 Destek</h3>
                    <p>Müşteri hizmetlerimiz her zaman yanınızda</p>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Güvenli Ödeme</h3>
                    <p>SSL sertifikası ile güvenli ödeme</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>E-Ticaret</h3>
                    <p>Kaliteli ürünler, uygun fiyatlar ve müşteri memnuniyeti odaklı hizmet anlayışımızla yanınızdayız.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
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