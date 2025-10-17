<?php
require_once 'config.php';
adminKontrol();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $aciklama = isset($_POST['aciklama']) ? trim($_POST['aciklama']) : '';
    $fiyat = isset($_POST['fiyat']) ? (float)$_POST['fiyat'] : 0;
    $stok = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;
    $kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
    $resim = isset($_POST['resim']) ? trim($_POST['resim']) : 'placeholder.jpg';
    
    $hatalar = [];
    
    if (empty($ad)) $hatalar[] = 'Ürün adı zorunludur';
    if ($fiyat <= 0) $hatalar[] = 'Geçerli bir fiyat giriniz';
    if (empty($kategori)) $hatalar[] = 'Kategori zorunludur';
    
    if (empty($hatalar)) {
        $stmt = $db->prepare("
            INSERT INTO urunler (ad, aciklama, fiyat, stok, kategori, resim)
            VALUES (:ad, :aciklama, :fiyat, :stok, :kategori, :resim)
        ");
        
        if ($stmt->execute([
            'ad' => $ad,
            'aciklama' => $aciklama,
            'fiyat' => $fiyat,
            'stok' => $stok,
            'kategori' => $kategori,
            'resim' => $resim
        ])) {
            $_SESSION['mesaj'] = 'Ürün başarıyla eklendi!';
            $_SESSION['mesaj_tip'] = 'basarili';
            header('Location: admin.php');
            exit();
        } else {
            $hatalar[] = 'Ürün eklenirken bir hata oluştu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle - Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>🛒 E-Ticaret - Yönetim</h1>
                </div>
                <nav class="nav">
                    <a href="admin.php">Yönetim Paneli</a>
                    <a href="cikis.php">Çıkış Yap</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container admin-form-sayfa">
        <h1>Yeni Ürün Ekle</h1>

        <?php if (!empty($hatalar)): ?>
            <div class="mesaj mesaj-hata">
                <ul>
                    <?php foreach ($hatalar as $hata): ?>
                        <li><?php echo guvenliCikti($hata); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Ürün Adı *</label>
                <input type="text" name="ad" required value="<?php echo isset($_POST['ad']) ? guvenliCikti($_POST['ad']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Açıklama</label>
                <textarea name="aciklama" rows="4"><?php echo isset($_POST['aciklama']) ? guvenliCikti($_POST['aciklama']) : ''; ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fiyat (₺) *</label>
                    <input type="number" name="fiyat" step="0.01" required value="<?php echo isset($_POST['fiyat']) ? $_POST['fiyat'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Stok *</label>
                    <input type="number" name="stok" required value="<?php echo isset($_POST['stok']) ? $_POST['stok'] : '0'; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Kategori *</label>
                <input type="text" name="kategori" required value="<?php echo isset($_POST['kategori']) ? guvenliCikti($_POST['kategori']) : ''; ?>" 
                       placeholder="Örn: Bilgisayar, Telefon, Aksesuar">
            </div>

            <div class="form-group">
                <label>Resim Dosya Adı</label>
                <input type="text" name="resim" value="<?php echo isset($_POST['resim']) ? guvenliCikti($_POST['resim']) : 'placeholder.jpg'; ?>" 
                       placeholder="Örn: urun1.jpg">
                <small>Resmi resimler/ klasörüne yükleyin</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Ürün Ekle</button>
                <a href="admin.php" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
