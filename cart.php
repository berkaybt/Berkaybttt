<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);

// AJAX istekleri için
if(isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch($_POST['action']) {
        case 'update_quantity':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if(updateCartItem($user_id, $product_id, $quantity)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Güncelleme başarısız']);
            }
            exit;
            
        case 'remove_item':
            $product_id = (int)$_POST['product_id'];
            
            if(removeFromCart($user_id, $product_id)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Silme başarısız']);
            }
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - E-Ticaret</title>
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
                        <a href="profile.php" class="user-link"><i class="fas fa-user"></i> Profil</a>
                        <a href="logout.php" class="user-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo array_sum(array_column($cart_items, 'quantity')); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 2rem 0;">
        <h1><i class="fas fa-shopping-cart"></i> Sepetim</h1>
        
        <?php if(empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>Sepetiniz boş</h3>
                <p>Alışverişe başlamak için ürünleri inceleyin.</p>
                <a href="products.php" class="btn btn-primary">Alışverişe Başla</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        <div class="cart-item-info">
                            <h4><?php echo $item['name']; ?></h4>
                            <p class="cart-item-price"><?php echo formatPrice($item['price']); ?></p>
                            <p>Stok: <?php echo $item['stock_quantity']; ?> adet</p>
                        </div>
                        <div class="cart-item-controls">
                            <div class="quantity-controls">
                                <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" 
                                       onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)"
                                       min="1" max="<?php echo $item['stock_quantity']; ?>">
                                <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                            </div>
                            <button class="remove-item" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Sipariş Özeti</h3>
                    <div class="summary-item">
                        <span>Ara Toplam:</span>
                        <span id="subtotal"><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Kargo:</span>
                        <span><?php echo $cart_total >= 500 ? 'Ücretsiz' : '25.00 ₺'; ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Toplam:</span>
                        <span id="total"><?php echo formatPrice($cart_total + ($cart_total >= 500 ? 0 : 25)); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-credit-card"></i> Ödemeye Geç
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function updateQuantity(productId, quantity) {
        if(quantity < 1) return;
        
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update_quantity&product_id=' + productId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Güncelleme başarısız: ' + data.message);
            }
        });
    }
    
    function removeItem(productId) {
        if(confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=remove_item&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Silme başarısız: ' + data.message);
                }
            });
        }
    }
    </script>

    <style>
    .empty-cart {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .cart-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .cart-items {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .cart-summary {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .summary-item.total {
        font-weight: bold;
        font-size: 1.2rem;
        color: #e74c3c;
        border-bottom: none;
    }
    
    @media (max-width: 768px) {
        .cart-content {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>