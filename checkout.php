<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Giriş kontrolü
requireLogin();

$cart_items = getCartItems($pdo);
$cart_total = getCartTotal($pdo);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Kullanıcı bilgilerini getir
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

$error_message = '';
$success_message = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    $billing_address = sanitizeInput($_POST['billing_address'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if (empty($shipping_address) || empty($billing_address) || empty($payment_method)) {
        $error_message = 'Lütfen tüm zorunlu alanları doldurun.';
    } else {
        // Sipariş oluştur
        $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Site ayarları
        $settings_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('shipping_cost', 'free_shipping_limit', 'tax_rate')";
        $settings_stmt = $pdo->prepare($settings_sql);
        $settings_stmt->execute();
        $settings = [];
        while ($row = $settings_stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $shipping_cost = (float)$settings['shipping_cost'];
        $free_shipping_limit = (float)$settings['free_shipping_limit'];
        $tax_rate = (float)$settings['tax_rate'];
        
        $subtotal = $cart_total;
        $shipping = $subtotal >= $free_shipping_limit ? 0 : $shipping_cost;
        $tax = $subtotal * ($tax_rate / 100);
        $total = $subtotal + $shipping + $tax;
        
        try {
            $pdo->beginTransaction();
            
            // Sipariş oluştur
            $order_sql = "INSERT INTO orders (user_id, order_number, status, payment_status, payment_method, subtotal, tax_amount, shipping_amount, total_amount, shipping_address, billing_address, notes) VALUES (?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, ?)";
            $order_stmt = $pdo->prepare($order_sql);
            $order_stmt->execute([
                $_SESSION['user_id'],
                $order_number,
                $payment_method,
                $subtotal,
                $tax,
                $shipping,
                $total,
                $shipping_address,
                $billing_address,
                $notes
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Sipariş detaylarını ekle
            $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
            $order_item_stmt = $pdo->prepare($order_item_sql);
            
            foreach ($cart_items as $item) {
                $item_price = $item['sale_price'] ?: $item['price'];
                $item_total = $item_price * $item['cart_quantity'];
                
                $order_item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['cart_quantity'],
                    $item_price,
                    $item_total
                ]);
                
                // Stok güncelle
                $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $update_stock_stmt = $pdo->prepare($update_stock_sql);
                $update_stock_stmt->execute([$item['cart_quantity'], $item['id']]);
            }
            
            $pdo->commit();
            
            // Sepeti temizle
            clearCart();
            
            // Başarı sayfasına yönlendir
            header('Location: order-success.php?order_id=' . $order_id);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Sipariş oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

// Site ayarları
$settings_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('shipping_cost', 'free_shipping_limit', 'tax_rate')";
$settings_stmt = $pdo->prepare($settings_sql);
$settings_stmt->execute();
$settings = [];
while ($row = $settings_stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$shipping_cost = (float)$settings['shipping_cost'];
$free_shipping_limit = (float)$settings['free_shipping_limit'];
$tax_rate = (float)$settings['tax_rate'];

$subtotal = $cart_total;
$shipping = $subtotal >= $free_shipping_limit ? 0 : $shipping_cost;
$tax = $subtotal * ($tax_rate / 100);
$total = $subtotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="checkout-page">
        <div class="container">
            <div class="page-header">
                <h1>Ödeme</h1>
                <p>Siparişinizi tamamlayın</p>
            </div>
            
            <div class="checkout-layout">
                <div class="checkout-form">
                    <form method="POST" id="checkoutForm">
                        <?php if ($error_message): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Teslimat Adresi -->
                        <div class="form-section">
                            <h2><i class="fas fa-shipping-fast"></i> Teslimat Adresi</h2>
                            <div class="form-group">
                                <label for="shipping_address">Adres *</label>
                                <textarea id="shipping_address" 
                                          name="shipping_address" 
                                          rows="4" 
                                          required
                                          placeholder="Tam adresinizi girin"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Fatura Adresi -->
                        <div class="form-section">
                            <h2><i class="fas fa-file-invoice"></i> Fatura Adresi</h2>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="same_address" onchange="toggleBillingAddress()">
                                    <span class="checkmark"></span>
                                    Teslimat adresi ile aynı
                                </label>
                            </div>
                            <div class="form-group" id="billing_address_group">
                                <label for="billing_address">Fatura Adresi *</label>
                                <textarea id="billing_address" 
                                          name="billing_address" 
                                          rows="4" 
                                          required
                                          placeholder="Fatura adresinizi girin"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Ödeme Yöntemi -->
                        <div class="form-section">
                            <h2><i class="fas fa-credit-card"></i> Ödeme Yöntemi</h2>
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="credit_card" required>
                                    <div class="payment-card">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Kredi Kartı</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="bank_transfer" required>
                                    <div class="payment-card">
                                        <i class="fas fa-university"></i>
                                        <span>Banka Havalesi</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cash_on_delivery" required>
                                    <div class="payment-card">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Kapıda Ödeme</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Kredi Kartı Bilgileri -->
                        <div class="form-section" id="credit_card_details" style="display: none;">
                            <h3>Kredi Kartı Bilgileri</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_number">Kart Numarası</label>
                                    <input type="text" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="form-group">
                                    <label for="card_cvv">CVV</label>
                                    <input type="text" id="card_cvv" placeholder="123" maxlength="4">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_month">Ay</label>
                                    <select id="card_month">
                                        <option value="">Ay</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="card_year">Yıl</label>
                                    <select id="card_year">
                                        <option value="">Yıl</option>
                                        <?php for($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sipariş Notları -->
                        <div class="form-section">
                            <h2><i class="fas fa-sticky-note"></i> Sipariş Notları</h2>
                            <div class="form-group">
                                <label for="notes">Notlar (İsteğe bağlı)</label>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Siparişiniz hakkında özel notlarınız..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i>
                                Sepete Dön
                            </a>
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-check"></i>
                                Siparişi Tamamla
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="order-summary">
                    <div class="summary-card">
                        <h2>Sipariş Özeti</h2>
                        
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Adet: <?php echo $item['cart_quantity']; ?></p>
                                    <span class="item-price">
                                        ₺<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['cart_quantity'], 2); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="total-row">
                                <span>Ara Toplam:</span>
                                <span>₺<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <div class="total-row">
                                <span>Kargo:</span>
                                <span>
                                    <?php if ($shipping == 0): ?>
                                        <span class="free-shipping">Ücretsiz</span>
                                    <?php else: ?>
                                        ₺<?php echo number_format($shipping, 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="total-row">
                                <span>KDV (%<?php echo $tax_rate; ?>):</span>
                                <span>₺<?php echo number_format($tax, 2); ?></span>
                            </div>
                            
                            <div class="total-row final-total">
                                <span>Toplam:</span>
                                <span>₺<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($shipping > 0): ?>
                        <div class="free-shipping-info">
                            <i class="fas fa-info-circle"></i>
                            <span>₺<?php echo number_format($free_shipping_limit - $subtotal, 2); ?> daha alışveriş yapın, ücretsiz kargo kazanın!</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="security-badges">
                            <div class="security-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Güvenli Ödeme</span>
                            </div>
                            <div class="security-badge">
                                <i class="fas fa-lock"></i>
                                <span>SSL Şifreleme</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/checkout.js"></script>
</body>
</html>