<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü (basit)
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// İstatistikler
$stats = [];

// Toplam ürün sayısı
$sql = "SELECT COUNT(*) as count FROM products WHERE is_active = 1";
$stmt = $pdo->query($sql);
$stats['products'] = $stmt->fetch()['count'];

// Toplam kullanıcı sayısı
$sql = "SELECT COUNT(*) as count FROM users";
$stmt = $pdo->query($sql);
$stats['users'] = $stmt->fetch()['count'];

// Toplam sipariş sayısı
$sql = "SELECT COUNT(*) as count FROM orders";
$stmt = $pdo->query($sql);
$stats['orders'] = $stmt->fetch()['count'];

// Toplam satış tutarı
$sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$stmt = $pdo->query($sql);
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Son siparişler
$sql = "SELECT o.*, u.first_name, u.last_name FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 5";
$stmt = $pdo->query($sql);
$recent_orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Ticaret</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
        }
        
        .admin-sidebar h2 {
            padding: 0 1rem 2rem;
            border-bottom: 1px solid #34495e;
            margin-bottom: 1rem;
        }
        
        .admin-sidebar ul {
            list-style: none;
        }
        
        .admin-sidebar li {
            margin-bottom: 0.5rem;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 1rem;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: #34495e;
        }
        
        .admin-content {
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #666;
        }
        
        .recent-orders {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-info h4 {
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .order-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending { background: #f39c12; color: white; }
        .status-processing { background: #3498db; color: white; }
        .status-shipped { background: #9b59b6; color: white; }
        .status-delivered { background: #27ae60; color: white; }
        .status-cancelled { background: #e74c3c; color: white; }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Ürünler</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Kategoriler</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Siparişler</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Kullanıcılar</a></li>
                <li><a href="../index.php"><i class="fas fa-home"></i> Siteye Dön</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <h1>Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <h3><?php echo $stats['products']; ?></h3>
                    <p>Toplam Ürün</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Toplam Kullanıcı</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h3><?php echo $stats['orders']; ?></h3>
                    <p>Toplam Sipariş</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-lira-sign"></i>
                    <h3><?php echo formatPrice($stats['revenue']); ?></h3>
                    <p>Toplam Satış</p>
                </div>
            </div>
            
            <div class="recent-orders">
                <h2>Son Siparişler</h2>
                
                <?php if(empty($recent_orders)): ?>
                    <p>Henüz sipariş bulunmuyor.</p>
                <?php else: ?>
                    <?php foreach($recent_orders as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <h4>#<?php echo $order['id']; ?> - <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></h4>
                            <p><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?> - <?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                        <div class="order-status status-<?php echo $order['status']; ?>">
                            <?php 
                            $status_text = [
                                'pending' => 'Beklemede',
                                'processing' => 'İşleniyor',
                                'shipped' => 'Kargoda',
                                'delivered' => 'Teslim Edildi',
                                'cancelled' => 'İptal Edildi'
                            ];
                            echo $status_text[$order['status']] ?? $order['status'];
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>