<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Öne çıkan ürünleri getir
$featured_products = getProducts(6, null, 1);
// Kategorileri getir
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticaret Sitesi - Ana Sayfa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> E-Ticaret
                </a>
                
                <div class="user-menu">
                    <?php if (isLoggedIn()): ?>
                        <span>Hoş geldin, <?php echo $_SESSION['username']; ?></span>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Ürünler</a></li>
                    <li><a href="categories.php"><i class="fas fa-list"></i> Kategoriler</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> İletişim</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>En İyi Ürünler, En Uygun Fiyatlar</h1>
            <p>Kaliteli ürünleri keşfedin ve güvenli alışveriş yapın</p>
            <a href="products.php" class="btn">Ürünleri İncele</a>
            <a href="categories.php" class="btn btn-secondary">Kategoriler</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="container">
        <h2 class="section-title">Öne Çıkan Ürünler</h2>
        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card fade-in">
                    <?php if ($product['featured']): ?>
                        <div class="featured-badge">Öne Çıkan</div>
                    <?php endif; ?>
                    
                    <img src="<?php echo $product['image'] ?: 'images/no-image.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                    
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">Detay</a>
                            <?php if (isLoggedIn()): ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-success">
                                    <i class="fas fa-cart-plus"></i> Sepete Ekle
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="products.php" class="btn">Tüm Ürünleri Gör</a>
        </div>
    </section>

    <!-- Categories -->
    <section class="container">
        <h2 class="section-title">Kategoriler</h2>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <div class="category-card fade-in">
                    <img src="<?php echo $category['image'] ?: 'images/no-image.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-image">
                    
                    <div class="category-info">
                        <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn">Ürünleri Gör</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Features -->
    <section class="container" style="margin: 5rem 0;">
        <div class="product-grid">
            <div class="product-card" style="text-align: center;">
                <div style="padding: 2rem;">
                    <i class="fas fa-shipping-fast" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h3>Hızlı Kargo</h3>
                    <p>Siparişleriniz 24 saat içinde kargoya verilir</p>
                </div>
            </div>
            
            <div class="product-card" style="text-align: center;">
                <div style="padding: 2rem;">
                    <i class="fas fa-shield-alt" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h3>Güvenli Ödeme</h3>
                    <p>256-bit SSL sertifikası ile güvenli ödeme</p>
                </div>
            </div>
            
            <div class="product-card" style="text-align: center;">
                <div style="padding: 2rem;">
                    <i class="fas fa-undo" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h3>Kolay İade</h3>
                    <p>14 gün içinde koşulsuz iade garantisi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Hakkımızda</h3>
                    <ul>
                        <li><a href="#">Şirket Bilgileri</a></li>
                        <li><a href="#">Kariyer</a></li>
                        <li><a href="#">Basın</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Müşteri Hizmetleri</h3>
                    <ul>
                        <li><a href="#">İletişim</a></li>
                        <li><a href="#">SSS</a></li>
                        <li><a href="#">Kargo Takip</a></li>
                        <li><a href="#">İade & Değişim</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Kategoriler</h3>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                            <li><a href="products.php?category=<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Sosyal Medya</h3>
                    <ul>
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-youtube"></i> YouTube</a></li>
                    </ul>
                </div>
            </div>
            
            <div style="border-top: 1px solid #555; padding-top: 2rem; margin-top: 2rem;">
                <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script>
        // Sepete ekleme fonksiyonu
        function addToCart(productId) {
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün sepete eklendi!');
                    updateCartCount();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // Sepet sayısını güncelle
        function updateCartCount() {
            fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.cart-count').textContent = data.count;
            });
        }

        // Sayfa yüklendiğinde sepet sayısını güncelle
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isLoggedIn()): ?>
                updateCartCount();
            <?php endif; ?>
        });

        // Scroll animasyonları
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>