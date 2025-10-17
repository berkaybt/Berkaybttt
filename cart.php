<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$cart_items = getCartItems($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - E-Ticaret</title>
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
                    <span>Hoş geldin, <?php echo $_SESSION['username']; ?></span>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo count($cart_items); ?></span>
                    </a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
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
        <h1 style="margin-bottom: 2rem;">
            <i class="fas fa-shopping-cart"></i> Sepetim
            <?php if (!empty($cart_items)): ?>
                <span style="font-size: 1rem; color: #666;">(<?php echo count($cart_items); ?> ürün)</span>
            <?php endif; ?>
        </h1>

        <?php if (empty($cart_items)): ?>
            <!-- Boş Sepet -->
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <i class="fas fa-shopping-cart" style="font-size: 5rem; color: #ddd; margin-bottom: 2rem;"></i>
                <h2>Sepetiniz Boş</h2>
                <p style="color: #666; margin-bottom: 2rem;">Henüz sepetinize ürün eklemediniz.</p>
                <a href="products.php" class="btn">
                    <i class="fas fa-shopping-bag"></i> Alışverişe Başla
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Sepet Ürünleri -->
                <div>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" id="cart-item-<?php echo $item['product_id']; ?>">
                            <img src="<?php echo $item['image'] ?: 'images/no-image.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="cart-item-image">
                            
                            <div class="cart-item-info">
                                <h3 class="cart-item-title">
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" 
                                       style="text-decoration: none; color: inherit;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h3>
                                <div class="cart-item-price">Birim Fiyat: <?php echo formatPrice($item['price']); ?></div>
                                
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="quantity-input" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)"
                                           id="quantity-<?php echo $item['product_id']; ?>">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div style="font-weight: bold; color: #667eea; font-size: 1.1rem;">
                                    Toplam: <span id="subtotal-<?php echo $item['product_id']; ?>"><?php echo formatPrice($item['subtotal']); ?></span>
                                </div>
                            </div>
                            
                            <button onclick="removeFromCart(<?php echo $item['product_id']; ?>)" 
                                    class="btn btn-danger" 
                                    style="align-self: flex-start;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sepet Özeti -->
                <div>
                    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); position: sticky; top: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; border-bottom: 2px solid #f8f9fa; padding-bottom: 1rem;">
                            <i class="fas fa-receipt"></i> Sipariş Özeti
                        </h3>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Ara Toplam:</span>
                            <span id="cart-subtotal"><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Kargo:</span>
                            <span style="color: #2ed573;">Ücretsiz</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>KDV (%18):</span>
                            <span id="cart-tax"><?php echo formatPrice($cart_total * 0.18); ?></span>
                        </div>
                        
                        <hr style="margin: 1.5rem 0; border: none; border-top: 2px solid #f8f9fa;">
                        
                        <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: #667eea; margin-bottom: 2rem;">
                            <span>Toplam:</span>
                            <span id="cart-total"><?php echo formatPrice($cart_total * 1.18); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn" style="width: 100%; text-align: center; font-size: 1.1rem; padding: 15px;">
                            <i class="fas fa-credit-card"></i> Ödemeye Geç
                        </a>
                        
                        <a href="products.php" class="btn btn-secondary" style="width: 100%; text-align: center; margin-top: 1rem;">
                            <i class="fas fa-arrow-left"></i> Alışverişe Devam
                        </a>
                        
                        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
                            <i class="fas fa-shield-alt" style="color: #2ed573; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <p style="font-size: 0.9rem; color: #666;">Güvenli ödeme garantisi</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Miktar güncelleme
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) {
                if (confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
                    removeFromCart(productId);
                }
                return;
            }
            
            fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Sayfayı yenile
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // Sepetten kaldırma
        function removeFromCart(productId) {
            if (!confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
                return;
            }
            
            fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&action=remove`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`cart-item-${productId}`).remove();
                    location.reload(); // Sayfayı yenile
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            // Miktar input'larına event listener ekle
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('blur', function() {
                    const productId = this.id.split('-')[1];
                    const quantity = parseInt(this.value);
                    if (quantity !== parseInt(this.defaultValue)) {
                        updateQuantity(productId, quantity);
                    }
                });
            });
        });
    </script>

    <style>
        @media (max-width: 768px) {
            .container > div:last-child > div {
                grid-template-columns: 1fr !important;
            }
            
            .cart-item {
                flex-direction: column !important;
                text-align: center !important;
            }
            
            .cart-item-image {
                width: 120px !important;
                height: 120px !important;
                margin: 0 auto 1rem !important;
            }
        }
    </style>
</body>
</html>