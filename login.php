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
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Geçerli bir e-posta adresi girin.';
    } else {
        // Kullanıcıyı kontrol et
        $user_sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
        $user_stmt = $pdo->prepare($user_sql);
        $user_stmt->execute([$email]);
        $user = $user_stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Yönlendirme
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error_message = 'E-posta veya şifre hatalı.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - E-Ticaret Sitesi</title>
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
                        <h1>Giriş Yap</h1>
                        <p>Hesabınıza giriş yapın</p>
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
                    
                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="email">E-posta Adresi</label>
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
                            <label for="password">Şifre</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember_me">
                                <span class="checkmark"></span>
                                Beni hatırla
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Şifremi unuttum</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-sign-in-alt"></i>
                            Giriş Yap
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Hesabınız yok mu? <a href="register.php">Kayıt olun</a></p>
                    </div>
                </div>
                
                <div class="auth-info">
                    <h2>Hoş Geldiniz!</h2>
                    <p>E-Ticaret sitemize giriş yaparak:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Güvenli alışveriş yapabilirsiniz</li>
                        <li><i class="fas fa-check"></i> Siparişlerinizi takip edebilirsiniz</li>
                        <li><i class="fas fa-check"></i> İstek listenizi yönetebilirsiniz</li>
                        <li><i class="fas fa-check"></i> Kişisel bilgilerinizi güncelleyebilirsiniz</li>
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