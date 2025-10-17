<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Sipariş bilgilerini getir
$order_sql = "SELECT o.*, u.first_name, u.last_name, u.email FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = ? AND o.user_id = ?";
$order_stmt = $pdo->prepare($order_sql);
$order_stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $order_stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Sipariş detaylarını getir
$order_items_sql = "SELECT oi.*, p.name, p.image, p.sku FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?";
$order_items_stmt = $pdo->prepare($order_items_sql);
$order_items_stmt->execute([$order_id]);
$order_items = $order_items_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Başarılı - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/order-success.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="order-success-page">
        <div class="container">
            <div class="success-container">
                <div class="success-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Siparişiniz Başarıyla Alındı!</h1>
                    <p>Sipariş numaranız: <strong><?php echo $order['order_number']; ?></strong></p>
                    <p>Sipariş detayları e-posta adresinize gönderildi.</p>
                </div>
                
                <div class="order-details">
                    <div class="order-info">
                        <h2>Sipariş Bilgileri</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Sipariş Numarası:</label>
                                <span><?php echo $order['order_number']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Sipariş Tarihi:</label>
                                <span><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Ödeme Yöntemi:</label>
                                <span>
                                    <?php 
                                    $payment_methods = [
                                        'credit_card' => 'Kredi Kartı',
                                        'bank_transfer' => 'Banka Havalesi',
                                        'cash_on_delivery' => 'Kapıda Ödeme'
                                    ];
                                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Sipariş Durumu:</label>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $statuses = [
                                        'pending' => 'Beklemede',
                                        'processing' => 'İşleniyor',
                                        'shipped' => 'Kargoya Verildi',
                                        'delivered' => 'Teslim Edildi',
                                        'cancelled' => 'İptal Edildi'
                                    ];
                                    echo $statuses[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shipping-info">
                        <h2>Teslimat Bilgileri</h2>
                        <div class="address-box">
                            <h3>Teslimat Adresi</h3>
                            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        
                        <div class="address-box">
                            <h3>Fatura Adresi</h3>
                            <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h2>Sipariş Detayları</h2>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-sku">SKU: <?php echo $item['sku']; ?></p>
                                <div class="item-quantity">Adet: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">
                                <span class="unit-price">₺<?php echo number_format($item['price'], 2); ?></span>
                                <span class="total-price">₺<?php echo number_format($item['total'], 2); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Ara Toplam:</span>
                            <span>₺<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Kargo:</span>
                            <span>
                                <?php if ($order['shipping_amount'] == 0): ?>
                                    <span class="free-shipping">Ücretsiz</span>
                                <?php else: ?>
                                    ₺<?php echo number_format($order['shipping_amount'], 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="total-row">
                            <span>KDV:</span>
                            <span>₺<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                        <div class="total-row final-total">
                            <span>Toplam:</span>
                            <span>₺<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h2>Sonraki Adımlar</h2>
                    <div class="steps-grid">
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>E-posta Onayı</h3>
                            <p>Sipariş detayları e-posta adresinize gönderildi.</p>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <h3>Hazırlanıyor</h3>
                            <p>Siparişiniz hazırlanmaya başlandı.</p>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h3>Kargoya Verildi</h3>
                            <p>Kargo takip numarası e-posta ile gönderilecek.</p>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <h3>Teslim Edildi</h3>
                            <p>Siparişiniz adresinize teslim edilecek.</p>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Ana Sayfaya Dön
                    </a>
                    <a href="products.php" class="btn btn-outline">
                        <i class="fas fa-shopping-bag"></i>
                        Alışverişe Devam Et
                    </a>
                    <a href="profile.php" class="btn btn-outline">
                        <i class="fas fa-user"></i>
                        Siparişlerim
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>