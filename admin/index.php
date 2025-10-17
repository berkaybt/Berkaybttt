<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// İstatistikleri getir
$stats = [];

// Toplam sipariş sayısı
$order_count_sql = "SELECT COUNT(*) as count FROM orders";
$order_count_stmt = $pdo->prepare($order_count_sql);
$order_count_stmt->execute();
$stats['total_orders'] = $order_count_stmt->fetch()['count'];

// Toplam gelir
$revenue_sql = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
$revenue_stmt = $pdo->prepare($revenue_sql);
$revenue_stmt->execute();
$stats['total_revenue'] = $revenue_stmt->fetch()['total'] ?: 0;

// Toplam ürün sayısı
$product_count_sql = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
$product_count_stmt = $pdo->prepare($product_count_sql);
$product_count_stmt->execute();
$stats['total_products'] = $product_count_stmt->fetch()['count'];

// Toplam kullanıcı sayısı
$user_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$user_count_stmt = $pdo->prepare($user_count_sql);
$user_count_stmt->execute();
$stats['total_users'] = $user_count_stmt->fetch()['count'];

// Son siparişler
$recent_orders_sql = "SELECT o.*, u.first_name, u.last_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_stmt = $pdo->prepare($recent_orders_sql);
$recent_orders_stmt->execute();
$recent_orders = $recent_orders_stmt->fetchAll();

// Stokta azalan ürünler
$low_stock_sql = "SELECT * FROM products WHERE stock_quantity <= 10 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 5";
$low_stock_stmt = $pdo->prepare($low_stock_sql);
$low_stock_stmt->execute();
$low_stock_products = $low_stock_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-content">
                <div class="page-header">
                    <h1>Dashboard</h1>
                    <p>E-Ticaret yönetim paneline hoş geldiniz</p>
                </div>
                
                <!-- İstatistik Kartları -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['total_orders']); ?></h3>
                            <p>Toplam Sipariş</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-lira-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>₺<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p>Toplam Gelir</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['total_products']); ?></h3>
                            <p>Toplam Ürün</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['total_users']); ?></h3>
                            <p>Toplam Kullanıcı</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <!-- Son Siparişler -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Son Siparişler</h2>
                            <a href="orders.php" class="btn btn-outline btn-sm">Tümünü Gör</a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recent_orders)): ?>
                                <p class="no-data">Henüz sipariş bulunmuyor</p>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($recent_orders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <h4><?php echo $order['order_number']; ?></h4>
                                            <p><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                                            <small><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></small>
                                        </div>
                                        <div class="order-status">
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
                                            <span class="order-total">₺<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Stokta Azalan Ürünler -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Stokta Azalan Ürünler</h2>
                            <a href="products.php" class="btn btn-outline btn-sm">Tümünü Gör</a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($low_stock_products)): ?>
                                <p class="no-data">Tüm ürünler stokta yeterli</p>
                            <?php else: ?>
                                <div class="products-list">
                                    <?php foreach ($low_stock_products as $product): ?>
                                    <div class="product-item">
                                        <div class="product-image">
                                            <img src="../../assets/images/products/<?php echo $product['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="stock-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Stok: <?php echo $product['stock_quantity']; ?> adet
                                            </p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Hızlı Erişim -->
                <div class="quick-actions">
                    <h2>Hızlı Erişim</h2>
                    <div class="actions-grid">
                        <a href="products.php?action=add" class="action-card">
                            <i class="fas fa-plus"></i>
                            <span>Yeni Ürün Ekle</span>
                        </a>
                        <a href="orders.php" class="action-card">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Siparişleri Yönet</span>
                        </a>
                        <a href="categories.php" class="action-card">
                            <i class="fas fa-tags"></i>
                            <span>Kategorileri Yönet</span>
                        </a>
                        <a href="users.php" class="action-card">
                            <i class="fas fa-users"></i>
                            <span>Kullanıcıları Yönet</span>
                        </a>
                        <a href="settings.php" class="action-card">
                            <i class="fas fa-cog"></i>
                            <span>Site Ayarları</span>
                        </a>
                        <a href="../index.php" class="action-card">
                            <i class="fas fa-external-link-alt"></i>
                            <span>Siteyi Görüntüle</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>