<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-store"></i>
                    <span>E-Ticaret</span>
                </a>
            </div>
            
            <nav class="nav">
                <ul class="nav-menu">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="products.php">Ürünler</a></li>
                    <li><a href="categories.php">Kategoriler</a></li>
                    <li><a href="about.php">Hakkımızda</a></li>
                    <li><a href="contact.php">İletişim</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="search-box">
                    <form action="search.php" method="GET">
                        <input type="text" name="q" placeholder="Ürün ara..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php" class="btn btn-outline">
                            <i class="fas fa-user"></i>
                            Profil
                        </a>
                        <a href="logout.php" class="btn btn-outline">
                            <i class="fas fa-sign-out-alt"></i>
                            Çıkış
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i>
                            Giriş
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="cart">
                    <a href="cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>