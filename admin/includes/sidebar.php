<nav class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-store"></i>
            <span>Admin Panel</span>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item active">
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="orders.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Siparişler</span>
                <span class="badge"><?php echo $stats['total_orders'] ?? 0; ?></span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="products.php">
                <i class="fas fa-box"></i>
                <span>Ürünler</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="categories.php">
                <i class="fas fa-tags"></i>
                <span>Kategoriler</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="users.php">
                <i class="fas fa-users"></i>
                <span>Kullanıcılar</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="reports.php">
                <i class="fas fa-chart-bar"></i>
                <span>Raporlar</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Ayarlar</span>
            </a>
        </li>
        
        <li class="menu-divider"></li>
        
        <li class="menu-item">
            <a href="../index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>Siteyi Görüntüle</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Çıkış Yap</span>
            </a>
        </li>
    </ul>
</nav>