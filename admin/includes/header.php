<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb">
            <span>Admin Panel</span>
            <i class="fas fa-chevron-right"></i>
            <span>Dashboard</span>
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-actions">
            <div class="notifications">
                <button class="notification-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Bildirimler</h3>
                        <button class="mark-all-read">Tümünü Okundu İşaretle</button>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="notification-content">
                                <h4>Yeni Sipariş</h4>
                                <p>ORD-2024-001 numaralı sipariş alındı</p>
                                <small>2 dakika önce</small>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="notification-content">
                                <h4>Stok Uyarısı</h4>
                                <p>iPhone 15 Pro stokta azalıyor</p>
                                <small>1 saat önce</small>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="notification-content">
                                <h4>Yeni Kullanıcı</h4>
                                <p>Yeni kullanıcı kaydı yapıldı</p>
                                <small>3 saat önce</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="user-menu">
                <button class="user-btn" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span><?php echo $_SESSION['user_name']; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        Profil
                    </a>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        Ayarlar
                    </a>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Çıkış Yap
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>