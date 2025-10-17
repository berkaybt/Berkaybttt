<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

$product = getProduct($product_id);

if (!$product) {
    header('Location: products.php');
    exit();
}

// İlgili ürünler (aynı kategoriden)
$related_products = [];
if ($product['category_id']) {
    $related_products = getProducts(4, $product['category_id']);
    // Mevcut ürünü çıkar
    $related_products = array_filter($related_products, function($p) use ($product_id) {
        return $p['id'] != $product_id;
    });
    $related_products = array_slice($related_products, 0, 3);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - E-Ticaret</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
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

    <div class="container" style="margin-top: 2rem;">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem;">
            <a href="index.php" style="color: #667eea; text-decoration: none;">Ana Sayfa</a>
            <span style="margin: 0 0.5rem; color: #ccc;">/</span>
            <a href="products.php" style="color: #667eea; text-decoration: none;">Ürünler</a>
            <?php if ($product['category_name']): ?>
                <span style="margin: 0 0.5rem; color: #ccc;">/</span>
                <a href="products.php?category=<?php echo $product['category_id']; ?>" style="color: #667eea; text-decoration: none;">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            <?php endif; ?>
            <span style="margin: 0 0.5rem; color: #ccc;">/</span>
            <span style="color: #666;"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- Ürün Detayı -->
        <div class="product-detail">
            <div>
                <img src="<?php echo $product['image'] ?: 'images/no-image.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-detail-image"
                     id="main-image">
                
                <!-- Küçük resimler (gelecekte eklenebilir) -->
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; justify-content: center;">
                    <img src="<?php echo $product['image'] ?: 'images/no-image.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #667eea;"
                         onclick="changeMainImage(this.src)">
                </div>
            </div>
            
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <?php if ($product['category_name']): ?>
                    <div style="margin-bottom: 1rem;">
                        <span style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 20px; color: #667eea; font-size: 0.9rem;">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="product-detail-price"><?php echo formatPrice($product['price']); ?></div>
                
                <div class="product-detail-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">Stok Durumu:</span>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span style="color: #2ed573;">
                                <i class="fas fa-check-circle"></i> Stokta (<?php echo $product['stock_quantity']; ?> adet)
                            </span>
                        <?php else: ?>
                            <span style="color: #ff4757;">
                                <i class="fas fa-times-circle"></i> Stokta Yok
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['featured']): ?>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="background: #ff4757; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                                <i class="fas fa-star"></i> Öne Çıkan Ürün
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (isLoggedIn() && $product['stock_quantity'] > 0): ?>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem;">
                        <div>
                            <label for="quantity" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Miktar:</label>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" 
                                   style="width: 80px; padding: 8px; border: 2px solid #ddd; border-radius: 8px; text-align: center;">
                        </div>
                        
                        <div>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-success" style="padding: 12px 30px; font-size: 1.1rem;">
                                <i class="fas fa-cart-plus"></i> Sepete Ekle
                            </button>
                        </div>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                        <p style="margin-bottom: 1rem;">Ürünü sepete eklemek için giriş yapmalısınız.</p>
                        <a href="login.php" class="btn">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                        <a href="register.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Sosyal Paylaşım -->
                <div style="border-top: 1px solid #eee; padding-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Paylaş:</h4>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" onclick="shareOnFacebook()" style="color: #3b5998; font-size: 1.5rem;">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" onclick="shareOnTwitter()" style="color: #1da1f2; font-size: 1.5rem;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" onclick="shareOnWhatsApp()" style="color: #25d366; font-size: 1.5rem;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- İlgili Ürünler -->
        <?php if (!empty($related_products)): ?>
            <section style="margin-top: 5rem;">
                <h2 class="section-title">İlgili Ürünler</h2>
                <div class="product-grid">
                    <?php foreach ($related_products as $related_product): ?>
                        <div class="product-card fade-in">
                            <?php if ($related_product['featured']): ?>
                                <div class="featured-badge">Öne Çıkan</div>
                            <?php endif; ?>
                            
                            <img src="<?php echo $related_product['image'] ?: 'images/no-image.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related_product['name']); ?>" 
                                 class="product-image">
                            
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($related_product['name']); ?></h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($related_product['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="product-price"><?php echo formatPrice($related_product['price']); ?></div>
                                
                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $related_product['id']; ?>" class="btn">
                                        <i class="fas fa-eye"></i> Detay
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button onclick="addToCart(<?php echo $related_product['id']; ?>)" class="btn btn-success">
                                            <i class="fas fa-cart-plus"></i> Sepete Ekle
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Ana resmi değiştir
        function changeMainImage(src) {
            document.getElementById('main-image').src = src;
        }

        // Sepete ekleme fonksiyonu
        function addToCart(productId) {
            const quantity = document.getElementById('quantity') ? document.getElementById('quantity').value : 1;
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
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

        // Sosyal medya paylaşım fonksiyonları
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo htmlspecialchars($product['name']); ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo htmlspecialchars($product['name']); ?> - ');
            window.open(`https://wa.me/?text=${text}${url}`, '_blank');
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isLoggedIn()): ?>
                updateCartCount();
            <?php endif; ?>
            
            // Fade-in animasyonu
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
        });
    </script>
</body>
</html>