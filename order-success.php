<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Sipariş bilgilerini getir
$db = getDB();
$stmt = $db->prepare("SELECT o.*, u.full_name, u.email FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     WHERE o.id = :order_id AND o.user_id = :user_id");
$stmt->bindParam(':order_id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Sipariş detaylarını getir
$stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = :order_id");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$shipping_info = json_decode($order['shipping_address'], true);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Başarılı - E-Ticaret</title>
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
                        <span class="cart-count">0</span>
                    </a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <!-- Checkout Adımları -->
        <div style="display: flex; justify-content: center; margin-bottom: 3rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #2ed573;">
                    <div style="width: 30px; height: 30px; background: #2ed573; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                    <span>Sepet</span>
                </div>
                <div style="width: 50px; height: 2px; background: #2ed573;"></div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #2ed573;">
                    <div style="width: 30px; height: 30px; background: #2ed573; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                    <span>Ödeme</span>
                </div>
                <div style="width: 50px; height: 2px; background: #2ed573;"></div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #2ed573;">
                    <div style="width: 30px; height: 30px; background: #2ed573; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                    <span>Tamamlandı</span>
                </div>
            </div>
        </div>

        <!-- Başarı Mesajı -->
        <div style="text-align: center; background: white; padding: 3rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 3rem;">
            <i class="fas fa-check-circle" style="font-size: 5rem; color: #2ed573; margin-bottom: 2rem;"></i>
            <h1 style="color: #2ed573; margin-bottom: 1rem;">Siparişiniz Başarıyla Alındı!</h1>
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">
                Sipariş numaranız: <strong style="color: #667eea;">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong>
            </p>
            <p style="color: #666;">
                Siparişiniz en kısa sürede hazırlanacak ve size bilgi verilecektir.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Sipariş Detayları -->
            <div>
                <!-- Sipariş Bilgileri -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-info-circle"></i> Sipariş Bilgileri
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <strong>Sipariş No:</strong><br>
                            #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
                        </div>
                        <div>
                            <strong>Sipariş Tarihi:</strong><br>
                            <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                        </div>
                        <div>
                            <strong>Durum:</strong><br>
                            <span style="background: #ffeaa7; color: #2d3436; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.9rem;">
                                Beklemede
                            </span>
                        </div>
                        <div>
                            <strong>Toplam Tutar:</strong><br>
                            <span style="color: #667eea; font-weight: bold; font-size: 1.2rem;">
                                <?php echo formatPrice($order['total_amount']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Teslimat Bilgileri -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-truck"></i> Teslimat Bilgileri
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                        <div>
                            <strong>Alıcı:</strong><br>
                            <?php echo htmlspecialchars($shipping_info['full_name']); ?>
                        </div>
                        <div>
                            <strong>Telefon:</strong><br>
                            <?php echo htmlspecialchars($shipping_info['phone']); ?>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <strong>Adres:</strong><br>
                            <?php echo nl2br(htmlspecialchars($shipping_info['address'])); ?>
                        </div>
                        <div>
                            <strong>Ödeme Yöntemi:</strong><br>
                            <?php 
                            $payment_methods = [
                                'credit_card' => 'Kredi Kartı',
                                'bank_transfer' => 'Havale/EFT',
                                'cash_on_delivery' => 'Kapıda Ödeme'
                            ];
                            echo $payment_methods[$shipping_info['payment_method']] ?? $shipping_info['payment_method'];
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Sipariş Ürünleri -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-box"></i> Sipariş Edilen Ürünler
                    </h3>
                    
                    <?php foreach ($order_items as $item): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f8f9fa;">
                            <img src="<?php echo $item['image'] ?: 'images/no-image.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <div style="flex: 1;">
                                <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div style="color: #666;">
                                    Miktar: <?php echo $item['quantity']; ?> x <?php echo formatPrice($item['price']); ?>
                                </div>
                            </div>
                            <div style="font-weight: bold; color: #667eea; font-size: 1.1rem;">
                                <?php echo formatPrice($item['quantity'] * $item['price']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Yan Panel -->
            <div>
                <!-- Sonraki Adımlar -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-clock"></i> Sonraki Adımlar
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <div style="width: 30px; height: 30px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">1</div>
                            <div>
                                <strong>Sipariş Onayı</strong><br>
                                <small style="color: #666;">E-posta ile bilgilendirme</small>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <div style="width: 30px; height: 30px; background: #ddd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">2</div>
                            <div>
                                <strong>Hazırlık</strong><br>
                                <small style="color: #666;">1-2 iş günü</small>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <div style="width: 30px; height: 30px; background: #ddd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">3</div>
                            <div>
                                <strong>Kargo</strong><br>
                                <small style="color: #666;">2-3 iş günü</small>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <div style="width: 30px; height: 30px; background: #ddd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">4</div>
                            <div>
                                <strong>Teslimat</strong><br>
                                <small style="color: #666;">Kapınızda</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hızlı Linkler -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-link"></i> Hızlı Linkler
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="profile.php" class="btn" style="text-align: center;">
                            <i class="fas fa-user"></i> Siparişlerimi Görüntüle
                        </a>
                        
                        <a href="products.php" class="btn btn-secondary" style="text-align: center;">
                            <i class="fas fa-shopping-bag"></i> Alışverişe Devam Et
                        </a>
                        
                        <a href="contact.php" class="btn btn-secondary" style="text-align: center;">
                            <i class="fas fa-envelope"></i> İletişime Geç
                        </a>
                    </div>
                    
                    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
                        <i class="fas fa-headset" style="color: #667eea; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <p style="font-size: 0.9rem; color: #666;">
                            Sorularınız için<br>
                            <strong>0850 123 45 67</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Sayfa yüklendiğinde sepet sayısını güncelle
        document.addEventListener('DOMContentLoaded', function() {
            // Sepet boş olduğu için 0 göster
            document.querySelector('.cart-count').textContent = '0';
        });
    </script>

    <style>
        @media (max-width: 768px) {
            .container > div:last-child > div {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>