<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

$product = getProductById($pdo, $product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// İlgili ürünleri getir
$related_products_sql = "SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' ORDER BY created_at DESC LIMIT 4";
$related_stmt = $pdo->prepare($related_products_sql);
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll();

// Kategori bilgisini getir
$category_sql = "SELECT name FROM categories WHERE id = ?";
$category_stmt = $pdo->prepare($category_sql);
$category_stmt->execute([$product['category_id']]);
$category = $category_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - E-Ticaret Sitesi</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['meta_description'] ?: $product['short_description']); ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="product-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Ana Sayfa</a>
                <i class="fas fa-chevron-right"></i>
                <a href="products.php">Ürünler</a>
                <i class="fas fa-chevron-right"></i>
                <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
            
            <div class="product-detail">
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="assets/images/products/<?php echo $product['image']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             id="main-product-image">
                    </div>
                    
                    <?php if($product['gallery']): ?>
                    <div class="gallery-thumbnails">
                        <?php 
                        $gallery = json_decode($product['gallery'], true);
                        if($gallery && is_array($gallery)):
                            foreach($gallery as $index => $image):
                        ?>
                        <div class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                             data-image="assets/images/products/<?php echo $image; ?>">
                            <img src="assets/images/products/<?php echo $image; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div class="product-header">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <div class="product-meta">
                            <span class="sku">SKU: <?php echo $product['sku']; ?></span>
                            <span class="category">Kategori: <?php echo htmlspecialchars($category['name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <?php if($product['sale_price']): ?>
                            <div class="price-row">
                                <span class="sale-price">₺<?php echo number_format($product['sale_price'], 2); ?></span>
                                <span class="original-price">₺<?php echo number_format($product['price'], 2); ?></span>
                                <span class="discount-percentage">
                                    %<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?> İndirim
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="price-row">
                                <span class="price">₺<?php echo number_format($product['price'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <h3>Açıklama</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <div class="product-options">
                        <div class="quantity-selector">
                            <label for="quantity">Adet:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" onclick="changeQuantity(-1)">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button type="button" class="quantity-btn plus" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <div class="stock-info">
                            <?php if($product['stock_quantity'] > 0): ?>
                                <span class="in-stock">
                                    <i class="fas fa-check-circle"></i>
                                    Stokta (<?php echo $product['stock_quantity']; ?> adet)
                                </span>
                            <?php else: ?>
                                <span class="out-of-stock">
                                    <i class="fas fa-times-circle"></i>
                                    Stokta Yok
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn btn-primary btn-large add-to-cart" 
                                data-id="<?php echo $product['id']; ?>"
                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            <?php echo $product['stock_quantity'] > 0 ? 'Sepete Ekle' : 'Stokta Yok'; ?>
                        </button>
                        
                        <button class="btn btn-outline btn-large wishlist-btn" data-id="<?php echo $product['id']; ?>">
                            <i class="far fa-heart"></i>
                            İstek Listesi
                        </button>
                        
                        <button class="btn btn-outline btn-large share-btn" onclick="shareProduct()">
                            <i class="fas fa-share-alt"></i>
                            Paylaş
                        </button>
                    </div>
                    
                    <div class="product-features">
                        <div class="feature">
                            <i class="fas fa-shipping-fast"></i>
                            <div>
                                <h4>Ücretsiz Kargo</h4>
                                <p>500₺ ve üzeri alışverişlerde</p>
                            </div>
                        </div>
                        
                        <div class="feature">
                            <i class="fas fa-undo"></i>
                            <div>
                                <h4>Kolay İade</h4>
                                <p>14 gün içinde iade garantisi</p>
                            </div>
                        </div>
                        
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Güvenli Alışveriş</h4>
                                <p>SSL sertifikası ile korumalı</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ürün Detayları Tabları -->
            <div class="product-tabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="description">Açıklama</button>
                    <button class="tab-btn" data-tab="specifications">Özellikler</button>
                    <button class="tab-btn" data-tab="reviews">Yorumlar</button>
                    <button class="tab-btn" data-tab="shipping">Kargo</button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="description">
                        <h3>Detaylı Açıklama</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <div class="tab-pane" id="specifications">
                        <h3>Ürün Özellikleri</h3>
                        <table class="specifications-table">
                            <tr>
                                <td>Marka</td>
                                <td>E-Ticaret</td>
                            </tr>
                            <tr>
                                <td>Model</td>
                                <td><?php echo $product['sku']; ?></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                            </tr>
                            <tr>
                                <td>Stok Durumu</td>
                                <td><?php echo $product['stock_quantity'] > 0 ? 'Stokta' : 'Stokta Yok'; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="tab-pane" id="reviews">
                        <h3>Müşteri Yorumları</h3>
                        <div class="reviews-summary">
                            <div class="rating-overview">
                                <div class="average-rating">
                                    <span class="rating-number">4.5</span>
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <span class="rating-count">(24 değerlendirme)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <strong>Ahmet Y.</strong>
                                        <div class="review-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="review-date">2 gün önce</span>
                                </div>
                                <p>Çok kaliteli bir ürün, hızlı kargo. Tavsiye ederim.</p>
                            </div>
                            
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <strong>Ayşe K.</strong>
                                        <div class="review-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="review-date">1 hafta önce</span>
                                </div>
                                <p>Güzel ürün ama kargo biraz geç geldi.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="shipping">
                        <h3>Kargo ve Teslimat</h3>
                        <div class="shipping-info">
                            <div class="shipping-option">
                                <h4>Standart Kargo</h4>
                                <p>2-3 iş günü içinde teslimat</p>
                                <p>Kargo ücreti: 29.99₺</p>
                            </div>
                            
                            <div class="shipping-option">
                                <h4>Hızlı Kargo</h4>
                                <p>1 iş günü içinde teslimat</p>
                                <p>Kargo ücreti: 49.99₺</p>
                            </div>
                            
                            <div class="shipping-option">
                                <h4>Ücretsiz Kargo</h4>
                                <p>500₺ ve üzeri alışverişlerde</p>
                                <p>2-3 iş günü içinde teslimat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- İlgili Ürünler -->
            <?php if(!empty($related_products)): ?>
            <section class="related-products">
                <h2>İlgili Ürünler</h2>
                <div class="products-grid">
                    <?php foreach($related_products as $related_product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/products/<?php echo $related_product['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $related_product['id']; ?>" class="btn btn-outline">
                                    Detayları Gör
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($related_product['name']); ?></h3>
                            <p class="product-price">₺<?php echo number_format($related_product['price'], 2); ?></p>
                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $related_product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Sepete Ekle
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/product.js"></script>
</body>
</html>