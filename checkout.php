<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);

if(empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

if($_POST) {
    $shipping_address = sanitizeInput($_POST['shipping_address']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    
    if(empty($shipping_address)) {
        $error = 'Teslimat adresi gereklidir!';
    } else {
        $shipping_cost = $cart_total >= 500 ? 0 : 25;
        $total_amount = $cart_total + $shipping_cost;
        
        $order_id = createOrder($user_id, $total_amount, $shipping_address, $payment_method);
        
        if($order_id) {
            $success = "Siparişiniz başarıyla oluşturuldu! Sipariş numaranız: #$order_id";
        } else {
            $error = 'Sipariş oluşturulurken bir hata oluştu!';
        }
    }
}

// Kullanıcı bilgilerini getir
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - E-Ticaret</title>
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
                
                <div class="user-actions">
                    <a href="profile.php" class="user-link"><i class="fas fa-user"></i> Profil</a>
                    <a href="logout.php" class="user-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 2rem 0;">
        <h1><i class="fas fa-credit-card"></i> Ödeme</h1>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="message success"><?php echo $success; ?></div>
            <div style="text-align: center; margin: 2rem 0;">
                <a href="orders.php" class="btn btn-primary">Siparişlerimi Görüntüle</a>
                <a href="products.php" class="btn btn-secondary">Alışverişe Devam Et</a>
            </div>
        <?php else: ?>
            <div class="checkout-content">
                <div class="checkout-form">
                    <h2>Sipariş Bilgileri</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="first_name">Ad</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Soyad</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-posta</label>
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Teslimat Adresi *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required 
                                      placeholder="Adres bilgilerinizi giriniz..."><?php echo $user['address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method">Ödeme Yöntemi *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">Seçiniz</option>
                                <option value="credit_card">Kredi Kartı</option>
                                <option value="bank_transfer">Banka Havalesi</option>
                                <option value="cash_on_delivery">Kapıda Ödeme</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-check"></i> Siparişi Tamamla
                        </button>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h3>Sipariş Özeti</h3>
                    
                    <div class="order-items">
                        <?php foreach($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <div class="item-info">
                                <h4><?php echo $item['name']; ?></h4>
                                <p>Adet: <?php echo $item['quantity']; ?></p>
                                <p class="item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-item">
                            <span>Ara Toplam:</span>
                            <span><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="total-item">
                            <span>Kargo:</span>
                            <span><?php echo $cart_total >= 500 ? 'Ücretsiz' : '25.00 ₺'; ?></span>
                        </div>
                        <div class="total-item final">
                            <span>Toplam:</span>
                            <span><?php echo formatPrice($cart_total + ($cart_total >= 500 ? 0 : 25)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
    .checkout-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .checkout-form {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .order-summary {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }
    
    .order-items {
        margin-bottom: 1rem;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .order-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 1rem;
    }
    
    .item-info h4 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .item-info p {
        color: #666;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    
    .item-price {
        color: #e74c3c;
        font-weight: bold;
    }
    
    .order-totals {
        border-top: 2px solid #eee;
        padding-top: 1rem;
    }
    
    .total-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .total-item.final {
        font-weight: bold;
        font-size: 1.2rem;
        color: #e74c3c;
        border-top: 1px solid #eee;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .checkout-content {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>