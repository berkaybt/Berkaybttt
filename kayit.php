<?php
require_once 'config.php';

if (isset($_SESSION['kullanici_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    $sifre_tekrar = isset($_POST['sifre_tekrar']) ? $_POST['sifre_tekrar'] : '';
    
    $hatalar = [];
    
    if (empty($ad)) $hatalar[] = 'Ad alanı zorunludur';
    if (empty($soyad)) $hatalar[] = 'Soyad alanı zorunludur';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $hatalar[] = 'Geçerli bir e-posta giriniz';
    if (empty($sifre) || strlen($sifre) < 6) $hatalar[] = 'Şifre en az 6 karakter olmalıdır';
    if ($sifre !== $sifre_tekrar) $hatalar[] = 'Şifreler eşleşmiyor';
    
    // E-posta kontrolü
    $email_check = $db->prepare("SELECT id FROM kullanicilar WHERE email = :email");
    $email_check->execute(['email' => $email]);
    if ($email_check->fetch()) {
        $hatalar[] = 'Bu e-posta adresi zaten kullanılıyor';
    }
    
    if (empty($hatalar)) {
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO kullanicilar (ad, soyad, email, telefon, sifre, rol)
            VALUES (:ad, :soyad, :email, :telefon, :sifre, 'kullanici')
        ");
        
        if ($stmt->execute([
            'ad' => $ad,
            'soyad' => $soyad,
            'email' => $email,
            'telefon' => $telefon,
            'sifre' => $hashed_sifre
        ])) {
            $_SESSION['mesaj'] = 'Kayıt başarılı! Giriş yapabilirsiniz.';
            $_SESSION['mesaj_tip'] = 'basarili';
            header('Location: giris.php');
            exit();
        } else {
            $hatalar[] = 'Kayıt sırasında bir hata oluştu';
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-sayfa">
        <div class="auth-kutu">
            <h1>🛒 E-Ticaret</h1>
            <h2>Kayıt Ol</h2>
            
            <?php if (!empty($hatalar)): ?>
                <div class="mesaj mesaj-hata">
                    <ul>
                        <?php foreach ($hatalar as $hata): ?>
                            <li><?php echo guvenliCikti($hata); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Ad *</label>
                    <input type="text" name="ad" required value="<?php echo isset($_POST['ad']) ? guvenliCikti($_POST['ad']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Soyad *</label>
                    <input type="text" name="soyad" required value="<?php echo isset($_POST['soyad']) ? guvenliCikti($_POST['soyad']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>E-posta *</label>
                    <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? guvenliCikti($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" name="telefon" value="<?php echo isset($_POST['telefon']) ? guvenliCikti($_POST['telefon']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Şifre * (En az 6 karakter)</label>
                    <input type="password" name="sifre" required>
                </div>
                <div class="form-group">
                    <label>Şifre Tekrar *</label>
                    <input type="password" name="sifre_tekrar" required>
                </div>
                <button type="submit" class="btn btn-primary btn-large">Kayıt Ol</button>
            </form>

            <div class="auth-link">
                Zaten hesabınız var mı? <a href="giris.php">Giriş Yap</a>
            </div>
            
            <div class="auth-link">
                <a href="index.php">Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
