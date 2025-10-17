<?php
require_once 'config.php';

if (isset($_SESSION['kullanici_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    
    if (!empty($email) && !empty($sifre)) {
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $kullanici = $stmt->fetch();
        
        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['ad'] = $kullanici['ad'];
            $_SESSION['rol'] = $kullanici['rol'];
            
            if ($kullanici['rol'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $hata = 'E-posta veya şifre hatalı!';
        }
    } else {
        $hata = 'Tüm alanları doldurun!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - E-Ticaret</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-sayfa">
        <div class="auth-kutu">
            <h1>🛒 E-Ticaret</h1>
            <h2>Giriş Yap</h2>
            
            <?php if (isset($hata)): ?>
                <div class="mesaj mesaj-hata"><?php echo guvenliCikti($hata); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="sifre" required>
                </div>
                <button type="submit" class="btn btn-primary btn-large">Giriş Yap</button>
            </form>

            <div class="auth-link">
                Henüz hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a>
            </div>
            
            <div class="auth-link">
                <a href="index.php">Ana Sayfaya Dön</a>
            </div>

            <div class="demo-bilgi">
                <h4>Demo Giriş Bilgileri:</h4>
                <p><strong>Admin:</strong> admin@eticaret.com / admin123</p>
            </div>
        </div>
    </div>
</body>
</html>
