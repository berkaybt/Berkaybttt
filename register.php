<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if(isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if($_POST) {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validasyon
    if($password !== $confirm_password) {
        $error = 'Şifreler eşleşmiyor!';
    } elseif(strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır!';
    } else {
        $result = registerUser($username, $email, $password, $first_name, $last_name, $phone);
        
        if($result) {
            $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
        } else {
            $error = 'Kayıt sırasında bir hata oluştu!';
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
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="max-width: 500px; margin: 3rem auto; padding: 2rem;">
        <div class="register-form">
            <h2><i class="fas fa-user-plus"></i> Kayıt Ol</h2>
            
            <?php if($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">Ad</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Soyad</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Telefon</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Şifre Tekrar</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Kayıt Ol</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a>
            </p>
        </div>
    </div>
</body>
</html>