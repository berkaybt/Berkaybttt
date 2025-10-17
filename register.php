<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Validasyon
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Zorunlu alanları doldurun!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin!';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır!';
    } elseif ($password !== $confirm_password) {
        $error = 'Şifreler eşleşmiyor!';
    } else {
        $db = getDB();
        
        // Kullanıcı adı ve e-posta kontrolü
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor!';
        } else {
            // Yeni kullanıcı oluştur
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, address) 
                                 VALUES (:username, :email, :password, :full_name, :phone, :address)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                $success = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
            } else {
                $error = 'Kayıt sırasında bir hata oluştu!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - E-Ticaret</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> E-Ticaret
                </a>
                <div class="user-menu">
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 2rem;">
                <i class="fas fa-user-plus"></i> Kayıt Ol
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Kullanıcı Adı *
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> E-posta *
                    </label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-id-card"></i> Ad Soyad *
                    </label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Telefon
                    </label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Adres
                    </label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Şifre *
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small style="color: #666;">En az 6 karakter olmalıdır</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Şifre Tekrar *
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Kayıt Ol
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p>Zaten hesabınız var mı? <a href="login.php" style="color: #667eea;">Giriş yapın</a></p>
            </div>
        </div>
    </div>

    <footer style="margin-top: 5rem;">
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Şifre eşleşme kontrolü
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.style.borderColor = '#ff4757';
            } else {
                this.style.borderColor = '#2ed573';
            }
        });
    </script>
</body>
</html>