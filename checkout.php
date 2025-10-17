<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$cart_items = getCartItems($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$error = '';
$success = '';

// Kullanıcı bilgilerini getir
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_POST) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($full_name) || empty($phone) || empty($address) || empty($payment_method)) {
        $error = 'Tüm alanları doldurun!';
    } else {
        // Sipariş oluştur
        $total_amount = $cart_total * 1.18; // KDV dahil
        
        try {
            $db->beginTransaction();
            
            // Sipariş ekle
            $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) 
                                 VALUES (:user_id, :total_amount, :shipping_address, 'pending')");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':total_amount', $total_amount);
            $shipping_info = json_encode([
                'full_name' => $full_name,
                'phone' => $phone,
                'address' => $address,
                'payment_method' => $payment_method
            ]);
            $stmt->bindParam(':shipping_address', $shipping_info);
            $stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Sipariş detaylarını ekle
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                     VALUES (:order_id, :product_id, :quantity, :price)");
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
                
                // Stok güncelle
                $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - :quantity 
                                     WHERE id = :product_id");
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->execute();
            }
            
            // Sepeti temizle
            clearCart($_SESSION['user_id']);
            
            $db->commit();
            
            // Başarılı sipariş sayfasına yönlendir
            header("Location: order-success.php?order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Sipariş oluşturulurken hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - E-Ticaret</title>
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
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <!-- Checkout Adımları -->
        <div style="display: flex; justify-content: center; margin-bottom: 3rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #2ed573;">
                    <div style="width: 30px; height: 30px; background: #2ed573; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">1</div>
                    <span>Sepet</span>
                </div>
                <div style="width: 50px; height: 2px; background: #2ed573;"></div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #667eea;">
                    <div style="width: 30px; height: 30px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                    <span>Ödeme</span>
                </div>
                <div style="width: 50px; height: 2px; background: #ddd;"></div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #ddd;">
                    <div style="width: 30px; height: 30px; background: #ddd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                    <span>Tamamlandı</span>
                </div>
            </div>
        </div>

        <h1 style="margin-bottom: 2rem; text-align: center;">
            <i class="fas fa-credit-card"></i> Ödeme Bilgileri
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Ödeme Formu -->
            <div>
                <form method="POST">
                    <!-- Teslimat Bilgileri -->
                    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; color: #333;">
                            <i class="fas fa-truck"></i> Teslimat Bilgileri
                        </h3>
                        
                        <div class="form-group">
                            <label for="full_name">
                                <i class="fas fa-user"></i> Ad Soyad *
                            </label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Telefon *
                            </label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">
                                <i class="fas fa-map-marker-alt"></i> Teslimat Adresi *
                            </label>
                            <textarea id="address" name="address" class="form-control" rows="4" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>

                    <!-- Ödeme Yöntemi -->
                    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; color: #333;">
                            <i class="fas fa-credit-card"></i> Ödeme Yöntemi
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e9ecef; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="credit_card" required style="margin-right: 1rem;">
                                <div>
                                    <i class="fas fa-credit-card" style="font-size: 1.5rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                                    <div style="font-weight: bold;">Kredi Kartı</div>
                                    <div style="font-size: 0.9rem; color: #666;">Visa, MasterCard</div>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e9ecef; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="bank_transfer" required style="margin-right: 1rem;">
                                <div>
                                    <i class="fas fa-university" style="font-size: 1.5rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                                    <div style="font-weight: bold;">Havale/EFT</div>
                                    <div style="font-size: 0.9rem; color: #666;">Banka Havalesi</div>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e9ecef; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="cash_on_delivery" required style="margin-right: 1rem;">
                                <div>
                                    <i class="fas fa-money-bill-wave" style="font-size: 1.5rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                                    <div style="font-weight: bold;">Kapıda Ödeme</div>
                                    <div style="font-size: 0.9rem; color: #666;">Nakit/Kart</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Kredi Kartı Bilgileri (JavaScript ile gösterilecek) -->
                    <div id="credit-card-form" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem; display: none;">
                        <h3 style="margin-bottom: 1.5rem; color: #333;">
                            <i class="fas fa-credit-card"></i> Kart Bilgileri
                        </h3>
                        
                        <div class="form-group">
                            <label for="card_number">
                                <i class="fas fa-credit-card"></i> Kart Numarası
                            </label>
                            <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="expiry_date">
                                    <i class="fas fa-calendar"></i> Son Kullanma
                                </label>
                                <input type="text" id="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5">
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">
                                    <i class="fas fa-lock"></i> CVV
                                </label>
                                <input type="text" id="cvv" class="form-control" placeholder="123" maxlength="3">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_holder">
                                <i class="fas fa-user"></i> Kart Sahibi
                            </label>
                            <input type="text" id="card_holder" class="form-control" placeholder="Ad Soyad">
                        </div>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; font-size: 1.2rem; padding: 15px;">
                        <i class="fas fa-lock"></i> Siparişi Tamamla
                    </button>
                </form>
            </div>

            <!-- Sipariş Özeti -->
            <div>
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); position: sticky; top: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 2px solid #f8f9fa; padding-bottom: 1rem;">
                        <i class="fas fa-receipt"></i> Sipariş Özeti
                    </h3>
                    
                    <!-- Ürünler -->
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1.5rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f8f9fa;">
                                <img src="<?php echo $item['image'] ?: 'images/no-image.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: bold; font-size: 0.9rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="color: #666; font-size: 0.8rem;"><?php echo $item['quantity']; ?> x <?php echo formatPrice($item['price']); ?></div>
                                </div>
                                <div style="font-weight: bold; color: #667eea;">
                                    <?php echo formatPrice($item['subtotal']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span>Ara Toplam:</span>
                        <span><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span>Kargo:</span>
                        <span style="color: #2ed573;">Ücretsiz</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span>KDV (%18):</span>
                        <span><?php echo formatPrice($cart_total * 0.18); ?></span>
                    </div>
                    
                    <hr style="margin: 1.5rem 0; border: none; border-top: 2px solid #f8f9fa;">
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: #667eea;">
                        <span>Toplam:</span>
                        <span><?php echo formatPrice($cart_total * 1.18); ?></span>
                    </div>
                    
                    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
                        <i class="fas fa-shield-alt" style="color: #2ed573; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <p style="font-size: 0.9rem; color: #666;">256-bit SSL ile güvenli ödeme</p>
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
        // Ödeme yöntemi seçimi
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const creditCardForm = document.getElementById('credit-card-form');
                if (this.value === 'credit_card') {
                    creditCardForm.style.display = 'block';
                } else {
                    creditCardForm.style.display = 'none';
                }
                
                // Seçili ödeme yöntemini vurgula
                document.querySelectorAll('input[name="payment_method"]').forEach(r => {
                    r.closest('label').style.borderColor = '#e9ecef';
                    r.closest('label').style.backgroundColor = 'white';
                });
                this.closest('label').style.borderColor = '#667eea';
                this.closest('label').style.backgroundColor = '#f8f9fa';
            });
        });

        // Kart numarası formatı
        document.getElementById('card_number').addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formattedValue;
        });

        // Son kullanma tarihi formatı
        document.getElementById('expiry_date').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });

        // CVV sadece rakam
        document.getElementById('cvv').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Form validasyonu
        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                e.preventDefault();
                alert('Lütfen bir ödeme yöntemi seçin!');
                return;
            }
            
            if (paymentMethod.value === 'credit_card') {
                const cardNumber = document.getElementById('card_number').value;
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                const cardHolder = document.getElementById('card_holder').value;
                
                if (!cardNumber || !expiryDate || !cvv || !cardHolder) {
                    e.preventDefault();
                    alert('Lütfen tüm kart bilgilerini doldurun!');
                    return;
                }
            }
            
            // Onay mesajı
            if (!confirm('Siparişinizi onaylıyor musunuz?')) {
                e.preventDefault();
            }
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