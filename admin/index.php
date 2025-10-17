<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// İstatistikler
$db = getDB();

// Toplam ürün sayısı
$stmt = $db->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Toplam kullanıcı sayısı
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Toplam sipariş sayısı
$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Toplam satış tutarı
$stmt = $db->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stmt->execute();
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Son siparişler
$stmt = $db->prepare("SELECT o.*, u.full_name FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Düşük stoklu ürünler
$stmt = $db->prepare("SELECT * FROM products WHERE stock_quantity < 5 ORDER BY stock_quantity ASC LIMIT 5");
$stmt->execute();
$low_stock_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Ticaret</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-cog"></i> Admin Panel
            </h2>
            <p style="color: #ccc; font-size: 0.9rem;">Hoş geldin, <?php echo $_SESSION['username']; ?></p>
        </div>
        
        <ul>
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Ürünler</a></li>
            <li><a href="categories.php"><i class="fas fa-list"></i> Kategoriler</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Siparişler</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Kullanıcılar</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
            <li style="margin-top: 2rem; border-top: 1px solid #555; padding-top: 1rem;">
                <a href="../index.php"><i class="fas fa-home"></i> Siteye Dön</a>
            </li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <h1 style="margin-bottom: 2rem;">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>

        <!-- İstatistik Kartları -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Toplam Ürün</div>
                <i class="fas fa-box" style="position: absolute; top: 1rem; right: 1rem; font-size: 2rem; color: #e9ecef;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Kayıtlı Kullanıcı</div>
                <i class="fas fa-users" style="position: absolute; top: 1rem; right: 1rem; font-size: 2rem; color: #e9ecef;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Toplam Sipariş</div>
                <i class="fas fa-shopping-cart" style="position: absolute; top: 1rem; right: 1rem; font-size: 2rem; color: #e9ecef;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo formatPrice($total_sales); ?></div>
                <div class="stat-label">Toplam Satış</div>
                <i class="fas fa-lira-sign" style="position: absolute; top: 1rem; right: 1rem; font-size: 2rem; color: #e9ecef;"></i>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 3rem;">
            <!-- Son Siparişler -->
            <div>
                <h3 style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-shopping-cart"></i> Son Siparişler</span>
                    <a href="orders.php" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Tümünü Gör</a>
                </h3>
                
                <div class="table">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Müşteri</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #666;">Henüz sipariş yok</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'pending' => '#ffeaa7',
                                                'processing' => '#74b9ff',
                                                'shipped' => '#00b894',
                                                'delivered' => '#00b894',
                                                'cancelled' => '#ff7675'
                                            ];
                                            $status_names = [
                                                'pending' => 'Beklemede',
                                                'processing' => 'İşleniyor',
                                                'shipped' => 'Kargoda',
                                                'delivered' => 'Teslim Edildi',
                                                'cancelled' => 'İptal'
                                            ];
                                            ?>
                                            <span style="background: <?php echo $status_colors[$order['status']]; ?>; color: #2d3436; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem;">
                                                <?php echo $status_names[$order['status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Düşük Stoklu Ürünler -->
            <div>
                <h3 style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-exclamation-triangle" style="color: #ff7675;"></i> Düşük Stok</span>
                    <a href="products.php" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Tümünü Gör</a>
                </h3>
                
                <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <?php if (empty($low_stock_products)): ?>
                        <div style="padding: 2rem; text-align: center; color: #666;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: #00b894; margin-bottom: 1rem;"></i>
                            <p>Tüm ürünler yeterli stokta!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($low_stock_products as $product): ?>
                            <div style="padding: 1rem; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: bold; margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </div>
                                    <div style="color: #666; font-size: 0.9rem;">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: #ff7675; font-weight: bold;">
                                        <?php echo $product['stock_quantity']; ?> adet
                                    </div>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>" style="color: #667eea; font-size: 0.8rem; text-decoration: none;">
                                        Düzenle
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Hızlı Eylemler -->
        <div style="margin-top: 3rem;">
            <h3 style="margin-bottom: 1.5rem;">
                <i class="fas fa-bolt"></i> Hızlı Eylemler
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="products.php?action=add" class="btn" style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-plus" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><br>
                    Yeni Ürün Ekle
                </a>
                
                <a href="categories.php?action=add" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-list" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><br>
                    Yeni Kategori Ekle
                </a>
                
                <a href="orders.php?status=pending" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-clock" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><br>
                    Bekleyen Siparişler
                </a>
                
                <a href="users.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><br>
                    Kullanıcı Yönetimi
                </a>
            </div>
        </div>
    </div>

    <script>
        // Sayfa yüklendiğinde aktif menüyü vurgula
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuLinks = document.querySelectorAll('.admin-sidebar a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        // Otomatik yenileme (5 dakikada bir)
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>

    <style>
        .stat-card {
            position: relative;
        }
        
        .active {
            background-color: #555 !important;
        }
        
        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0 !important;
            }
            
            .admin-content > div:nth-child(3) {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>