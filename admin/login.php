<?php
session_start();
require_once '../config/database.php';

$error = '';

// Basit admin girişi (gerçek uygulamada daha güvenli olmalı)
if($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı!';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - E-Ticaret</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-login {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .admin-login h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 2rem;
        }
        
        .admin-login .form-group {
            margin-bottom: 1.5rem;
        }
        
        .admin-login .btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="admin-login">
        <h2><i class="fas fa-shield-alt"></i> Admin Giriş</h2>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem; color: #666;">
            <p>Demo: admin / admin123</p>
            <a href="../index.php" style="color: #e74c3c;">← Siteye Dön</a>
        </div>
    </div>
</body>
</html>