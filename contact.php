<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$error = '';

if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message_text = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $error = 'Lütfen tüm alanları doldurun!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin!';
    } else {
        // Burada normalde e-posta gönderme işlemi yapılır
        // Şimdilik sadece başarı mesajı gösterelim
        $message = 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim - E-Ticaret</title>
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
                    <li><a href="categories.php"><i class="fas fa-list"></i> Kategoriler</a></li>
                    <li><a href="contact.php" class="active"><i class="fas fa-envelope"></i> İletişim</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <h1 class="section-title">İletişim</h1>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; margin-bottom: 3rem;">
            <!-- İletişim Formu -->
            <div>
                <div class="form-container">
                    <h3 style="margin-bottom: 1.5rem;">
                        <i class="fas fa-envelope"></i> Bize Mesaj Gönderin
                    </h3>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Ad Soyad *
                            </label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> E-posta *
                            </label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">
                                <i class="fas fa-tag"></i> Konu *
                            </label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Konu Seçin</option>
                                <option value="Genel Bilgi" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Genel Bilgi') ? 'selected' : ''; ?>>Genel Bilgi</option>
                                <option value="Sipariş Durumu" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Sipariş Durumu') ? 'selected' : ''; ?>>Sipariş Durumu</option>
                                <option value="İade/Değişim" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'İade/Değişim') ? 'selected' : ''; ?>>İade/Değişim</option>
                                <option value="Teknik Destek" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Teknik Destek') ? 'selected' : ''; ?>>Teknik Destek</option>
                                <option value="Öneri/Şikayet" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Öneri/Şikayet') ? 'selected' : ''; ?>>Öneri/Şikayet</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i> Mesajınız *
                            </label>
                            <textarea id="message" name="message" class="form-control" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Mesajı Gönder
                        </button>
                    </form>
                </div>
            </div>

            <!-- İletişim Bilgileri -->
            <div>
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-info-circle"></i> İletişim Bilgileri
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong>Adres</strong><br>
                                <span style="color: #666;">Atatürk Cad. No:123<br>Çankaya/Ankara</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <strong>Telefon</strong><br>
                                <span style="color: #666;">0850 123 45 67</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong>E-posta</strong><br>
                                <span style="color: #666;">info@eticaret.com</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <strong>Çalışma Saatleri</strong><br>
                                <span style="color: #666;">Pazartesi - Cuma: 09:00 - 18:00<br>Cumartesi: 09:00 - 16:00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sosyal Medya -->
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">
                        <i class="fas fa-share-alt"></i> Sosyal Medya
                    </h3>
                    
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="#" style="width: 50px; height: 50px; background: #3b5998; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.3s;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        
                        <a href="#" style="width: 50px; height: 50px; background: #1da1f2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.3s;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        
                        <a href="#" style="width: 50px; height: 50px; background: #e4405f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.3s;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        
                        <a href="#" style="width: 50px; height: 50px; background: #ff0000; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.3s;">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SSS -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 2rem; text-align: center;">
                <i class="fas fa-question-circle"></i> Sık Sorulan Sorular
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <h4 style="color: #667eea; margin-bottom: 1rem;">Kargo ne kadar sürer?</h4>
                    <p style="color: #666;">Siparişleriniz 1-2 iş günü içinde kargoya verilir ve 2-3 iş günü içinde adresinize teslim edilir.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 1rem;">İade nasıl yapılır?</h4>
                    <p style="color: #666;">Ürünlerinizi 14 gün içinde koşulsuz iade edebilirsiniz. İade işlemi için bizimle iletişime geçin.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 1rem;">Hangi ödeme yöntemleri kabul edilir?</h4>
                    <p style="color: #666;">Kredi kartı, havale/EFT ve kapıda ödeme seçenekleri mevcuttur.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 1rem;">Kargo ücreti var mı?</h4>
                    <p style="color: #666;">Tüm siparişlerde kargo ücretsizdir.</p>
                </div>
            </div>
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
            
            // Sosyal medya hover efekti
            document.querySelectorAll('a[style*="border-radius: 50%"]').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>

    <style>
        .active {
            background-color: rgba(255,255,255,0.2) !important;
        }
        
        @media (max-width: 768px) {
            .container > div:nth-child(2) > div {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>