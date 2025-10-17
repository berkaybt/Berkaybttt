<?php
require_once 'config.php';
adminKontrol();

$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ürünü getir
$stmt = $db->prepare("SELECT * FROM urunler WHERE id = :id");
$stmt->execute(['id' => $urun_id]);
$urun = $stmt->fetch();

if (!$urun) {
    header('Location: admin.php');
    exit();
}

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
        $update_stmt = $db->prepare("
            UPDATE urunler 
            SET ad = :ad, aciklama = :aciklama, fiyat = :fiyat, stok = :stok, kategori = :kategori, resim = :resim
            WHERE id = :id
        ");
        
        if ($update_stmt->execute([
            'ad' => $ad,
            'aciklama' => $aciklama,
            'fiyat' => $fiyat,
            'stok' => $stok,
            'kategori' => $kategori,
            'resim' => $resim,
            'id' => $urun_id
        ])) {
            $_SESSION['mesaj'] = 'Ürün başarıyla güncellendi!';
            $_SESSION['mesaj_tip'] = 'basarili';
            header('Location: admin.php');
            exit();
        } else {
            $hatalar[] = 'Ürün güncellenirken bir hata oluştu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenle - Yönetim Paneli</title>
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
        <h1>Ürün Düzenle</h1>

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
                <input type="text" name="ad" required value="<?php echo guvenliCikti($urun['ad']); ?>">
            </div>

            <div class="form-group">
                <label>Açıklama</label>
                <textarea name="aciklama" rows="4"><?php echo guvenliCikti($urun['aciklama']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fiyat (₺) *</label>
                    <input type="number" name="fiyat" step="0.01" required value="<?php echo $urun['fiyat']; ?>">
                </div>

                <div class="form-group">
                    <label>Stok *</label>
                    <input type="number" name="stok" required value="<?php echo $urun['stok']; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Kategori *</label>
                <input type="text" name="kategori" required value="<?php echo guvenliCikti($urun['kategori']); ?>">
            </div>

            <div class="form-group">
                <label>Resim Dosya Adı</label>
                <input type="text" name="resim" value="<?php echo guvenliCikti($urun['resim']); ?>">
                <small>Mevcut: <img src="resimler/<?php echo guvenliCikti($urun['resim']); ?>" class="admin-thumb" onerror="this.src='resimler/placeholder.jpg'"></small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Güncelle</button>
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
