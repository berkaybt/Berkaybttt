<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir!';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username OR email = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Geçersiz kullanıcı adı veya şifre!';
            }
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - E-Ticaret</title>
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
                    <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 2rem;">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
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
                        <i class="fas fa-user"></i> Kullanıcı Adı veya E-posta
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Şifre
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p>Hesabınız yok mu? <a href="register.php" style="color: #667eea;">Kayıt olun</a></p>
                <p><a href="forgot-password.php" style="color: #667eea;">Şifremi unuttum</a></p>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h4>Demo Hesaplar:</h4>
                <p><strong>Admin:</strong> admin / admin123</p>
                <p><strong>Kullanıcı:</strong> testuser / test123</p>
            </div>
        </div>
    </div>

    <footer style="margin-top: 5rem;">
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>