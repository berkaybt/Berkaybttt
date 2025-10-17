<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler - E-Ticaret</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> E-Ticaret
                </a>
                
                <div class="user-menu">
                    <?php if (isLoggedIn()): ?>
                        <span>Hoş geldin, <?php echo $_SESSION['username']; ?></span>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Ürünler</a></li>
                    <li><a href="categories.php" class="active"><i class="fas fa-list"></i> Kategoriler</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> İletişim</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <h1 class="section-title">Kategoriler</h1>
        
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <div class="category-card fade-in">
                    <img src="<?php echo $category['image'] ?: 'images/no-image.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-image">
                    
                    <div class="category-info">
                        <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn">
                            <i class="fas fa-arrow-right"></i> Ürünleri Gör
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Sepet sayısını güncelle
        function updateCartCount() {
            fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.cart-count').textContent = data.count;
            });
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isLoggedIn()): ?>
                updateCartCount();
            <?php endif; ?>
            
            // Fade-in animasyonu
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>

    <style>
        .active {
            background-color: rgba(255,255,255,0.2) !important;
        }
    </style>
</body>
</html>