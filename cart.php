<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Sepet işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_quantity':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity <= 0) {
                removeFromCart($product_id);
            } else {
                updateCartQuantity($product_id, $quantity);
            }
            break;
            
        case 'remove_item':
            $product_id = (int)$_POST['product_id'];
            removeFromCart($product_id);
            break;
            
        case 'clear_cart':
            clearCart();
            break;
    }
    
    header('Location: cart.php');
    exit;
}

$cart_items = getCartItems($pdo);
$cart_total = getCartTotal($pdo);

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
    <title>Sepetim - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="cart-page">
        <div class="container">
            <div class="page-header">
                <h1>Sepetim</h1>
                <p><?php echo count($cart_items); ?> ürün sepetinizde</p>
            </div>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-content">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Sepetiniz Boş</h2>
                        <p>Alışverişe devam etmek için ürünleri inceleyebilirsiniz.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Alışverişe Devam Et
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Sepetteki Ürünler</h2>
                            <form method="POST" class="clear-cart-form" onsubmit="return confirm('Sepeti temizlemek istediğinizden emin misiniz?')">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn btn-outline btn-sm">
                                    <i class="fas fa-trash"></i>
                                    Sepeti Temizle
                                </button>
                            </form>
                        </div>
                        
                        <div class="cart-items-list">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                                <div class="item-image">
                                    <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-sku">SKU: <?php echo $item['sku']; ?></p>
                                    <div class="item-price">
                                        <?php if($item['sale_price']): ?>
                                            <span class="sale-price">₺<?php echo number_format($item['sale_price'], 2); ?></span>
                                            <span class="original-price">₺<?php echo number_format($item['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="price">₺<?php echo number_format($item['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="item-quantity">
                                    <label>Adet:</label>
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?php echo $item['cart_quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock_quantity']; ?>"
                                               onchange="updateQuantity(<?php echo $item['id']; ?>, 0, this.value)">
                                        <button type="button" class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                    <small class="stock-info">
                                        <?php if($item['stock_quantity'] > 0): ?>
                                            Stokta: <?php echo $item['stock_quantity']; ?> adet
                                        <?php else: ?>
                                            <span class="out-of-stock">Stokta yok</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <div class="item-total">
                                    <span class="total-label">Toplam:</span>
                                    <span class="total-price">
                                        ₺<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['cart_quantity'], 2); ?>
                                    </span>
                                </div>
                                
                                <div class="item-actions">
                                    <button type="button" class="btn btn-outline btn-sm remove-item" 
                                            onclick="removeItem(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Sipariş Özeti</h3>
                            
                            <div class="summary-row">
                                <span>Ara Toplam:</span>
                                <span>₺<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Kargo:</span>
                                <span>
                                    <?php if ($shipping == 0): ?>
                                        <span class="free-shipping">Ücretsiz</span>
                                    <?php else: ?>
                                        ₺<?php echo number_format($shipping, 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="summary-row">
                                <span>KDV (%<?php echo $tax_rate; ?>):</span>
                                <span>₺<?php echo number_format($tax, 2); ?></span>
                            </div>
                            
                            <div class="summary-row total-row">
                                <span>Toplam:</span>
                                <span>₺<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <?php if ($shipping > 0): ?>
                            <div class="free-shipping-info">
                                <i class="fas fa-info-circle"></i>
                                <span>₺<?php echo number_format($free_shipping_limit - $subtotal, 2); ?> daha alışveriş yapın, ücretsiz kargo kazanın!</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="checkout-actions">
                                <a href="checkout.php" class="btn btn-primary btn-large">
                                    <i class="fas fa-credit-card"></i>
                                    Ödemeye Geç
                                </a>
                                
                                <a href="products.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i>
                                    Alışverişe Devam Et
                                </a>
                            </div>
                        </div>
                        
                        <div class="promo-code">
                            <h4>İndirim Kodu</h4>
                            <form class="promo-form">
                                <input type="text" placeholder="İndirim kodunuzu girin" name="promo_code">
                                <button type="submit" class="btn btn-outline">Uygula</button>
                            </form>
                        </div>
                        
                        <div class="security-info">
                            <div class="security-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Güvenli Ödeme</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-undo"></i>
                                <span>Kolay İade</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-shipping-fast"></i>
                                <span>Hızlı Kargo</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>