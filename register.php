<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Zaten giriş yapmışsa yönlendir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $terms = isset($_POST['terms']);
    
    // Validasyon
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Lütfen tüm zorunlu alanları doldurun.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Geçerli bir e-posta adresi girin.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Şifreler eşleşmiyor.';
    } elseif (!$terms) {
        $error_message = 'Kullanım şartlarını kabul etmelisiniz.';
    } else {
        // E-posta kontrolü
        $check_email_sql = "SELECT id FROM users WHERE email = ?";
        $check_email_stmt = $pdo->prepare($check_email_sql);
        $check_email_stmt->execute([$email]);
        
        if ($check_email_stmt->fetch()) {
            $error_message = 'Bu e-posta adresi zaten kullanılıyor.';
        } else {
            // Kullanıcıyı kaydet
            $hashed_password = hashPassword($password);
            
            $insert_sql = "INSERT INTO users (first_name, last_name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $pdo->prepare($insert_sql);
            
            if ($insert_stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone])) {
                $success_message = 'Kayıt başarılı! Giriş yapabilirsiniz.';
                // Formu temizle
                $first_name = $last_name = $email = $phone = '';
            } else {
                $error_message = 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.';
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
    <title>Kayıt Ol - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Kayıt Ol</h1>
                        <p>Yeni hesap oluşturun</p>
                    </div>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form" id="registerForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Ad *</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($first_name ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Soyad *</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($last_name ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-posta Adresi *</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                       placeholder="+90 5XX XXX XX XX">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Şifre *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-help">En az 6 karakter olmalıdır</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Şifre Tekrar *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                <a href="terms.php" target="_blank">Kullanım şartlarını</a> ve <a href="privacy.php" target="_blank">gizlilik politikasını</a> okudum, kabul ediyorum. *
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="newsletter">
                                <span class="checkmark"></span>
                                E-posta ile kampanya ve duyuruları almak istiyorum
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-user-plus"></i>
                            Kayıt Ol
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a></p>
                    </div>
                </div>
                
                <div class="auth-info">
                    <h2>Neden Kayıt Olmalısınız?</h2>
                    <ul>
                        <li><i class="fas fa-check"></i> Hızlı ve güvenli alışveriş</li>
                        <li><i class="fas fa-check"></i> Sipariş geçmişinizi görüntüleyin</li>
                        <li><i class="fas fa-check"></i> İstek listenizi yönetin</li>
                        <li><i class="fas fa-check"></i> Özel indirimlerden yararlanın</li>
                        <li><i class="fas fa-check"></i> Kişisel bilgilerinizi güvenle saklayın</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>