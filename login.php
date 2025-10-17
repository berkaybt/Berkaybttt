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

if($_POST) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    $user = loginUser($email, $password);
    
    if($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'E-posta veya şifre hatalı!';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - E-Ticaret</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: 5rem auto; padding: 2rem;">
        <div class="login-form">
            <h2><i class="fas fa-sign-in-alt"></i> Giriş Yap</h2>
            
            <?php if($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Giriş Yap</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                Hesabınız yok mu? <a href="register.php">Kayıt olun</a>
            </p>
        </div>
    </div>
</body>
</html>