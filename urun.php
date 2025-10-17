<?php
require_once 'config.php';

$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ürünü getir
$stmt = $db->prepare("SELECT * FROM urunler WHERE id = :id");
$stmt->execute(['id' => $urun_id]);
$urun = $stmt->fetch();

if (!$urun) {
    header('Location: index.php');
    exit();
}

// Sepetteki ürün sayısı
$sepet_stmt = $db->prepare("SELECT SUM(miktar) as toplam FROM sepet WHERE oturum_id = :oturum_id");
$sepet_stmt->execute(['oturum_id' => $_SESSION['sepet_id']]);
$sepet_sayisi = $sepet_stmt->fetch()['toplam'] ?? 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo guvenliCikti($urun['ad']); ?> - E-Ticaret</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>🛒 E-Ticaret</h1>
                </div>
                <nav class="nav">
                    <a href="index.php">Ana Sayfa</a>
                    <a href="sepet.php">🛒 Sepet (<?php echo $sepet_sayisi; ?>)</a>
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="admin.php">Yönetim</a>
                        <?php endif; ?>
                        <a href="cikis.php">Çıkış Yap</a>
                    <?php else: ?>
                        <a href="giris.php">Giriş Yap</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Ürün Detay -->
    <main class="container urun-detay-sayfa">
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / 
            <a href="index.php?kategori=<?php echo urlencode($urun['kategori']); ?>"><?php echo guvenliCikti($urun['kategori']); ?></a> / 
            <span><?php echo guvenliCikti($urun['ad']); ?></span>
        </div>

        <div class="urun-detay">
            <div class="urun-detay-resim">
                <img src="resimler/<?php echo guvenliCikti($urun['resim']); ?>" 
                     alt="<?php echo guvenliCikti($urun['ad']); ?>"
                     onerror="this.src='resimler/placeholder.jpg'">
            </div>
            
            <div class="urun-detay-bilgi">
                <h1><?php echo guvenliCikti($urun['ad']); ?></h1>
                
                <div class="kategori-badge">
                    <span><?php echo guvenliCikti($urun['kategori']); ?></span>
                </div>

                <div class="fiyat-box">
                    <span class="fiyat-buyuk"><?php echo formatFiyat($urun['fiyat']); ?></span>
                </div>

                <div class="stok-bilgi">
                    <?php if ($urun['stok'] > 0): ?>
                        <span class="stok-var">✓ Stokta var (<?php echo $urun['stok']; ?> adet)</span>
                    <?php else: ?>
                        <span class="stok-yok">✗ Stokta yok</span>
                    <?php endif; ?>
                </div>

                <div class="urun-aciklama-detay">
                    <h3>Ürün Açıklaması</h3>
                    <p><?php echo nl2br(guvenliCikti($urun['aciklama'])); ?></p>
                </div>

                <?php if ($urun['stok'] > 0): ?>
                    <form action="sepet_ekle.php" method="POST" class="sepet-form">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <div class="miktar-secim">
                            <label>Miktar:</label>
                            <input type="number" name="miktar" value="1" min="1" max="<?php echo $urun['stok']; ?>">
                        </div>
                        <button type="submit" class="btn btn-success btn-large">Sepete Ekle</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-disabled btn-large" disabled>Stokta Yok</button>
                <?php endif; ?>

                <div class="urun-ozellikler">
                    <h3>Ürün Özellikleri</h3>
                    <ul>
                        <li><strong>Kategori:</strong> <?php echo guvenliCikti($urun['kategori']); ?></li>
                        <li><strong>Stok Durumu:</strong> <?php echo $urun['stok'] > 0 ? 'Stokta var' : 'Stokta yok'; ?></li>
                        <li><strong>Ürün Kodu:</strong> #<?php echo str_pad($urun['id'], 6, '0', STR_PAD_LEFT); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Benzer Ürünler -->
        <?php
        $benzer_stmt = $db->prepare("SELECT * FROM urunler WHERE kategori = :kategori AND id != :id AND stok > 0 LIMIT 4");
        $benzer_stmt->execute(['kategori' => $urun['kategori'], 'id' => $urun['id']]);
        $benzer_urunler = $benzer_stmt->fetchAll();
        
        if (count($benzer_urunler) > 0):
        ?>
        <div class="benzer-urunler">
            <h2>Benzer Ürünler</h2>
            <div class="urun-grid">
                <?php foreach ($benzer_urunler as $benzer): ?>
                    <div class="urun-kart">
                        <div class="urun-resim">
                            <img src="resimler/<?php echo guvenliCikti($benzer['resim']); ?>" 
                                 alt="<?php echo guvenliCikti($benzer['ad']); ?>"
                                 onerror="this.src='resimler/placeholder.jpg'">
                        </div>
                        <div class="urun-bilgi">
                            <h3><?php echo guvenliCikti($benzer['ad']); ?></h3>
                            <div class="urun-alt">
                                <span class="fiyat"><?php echo formatFiyat($benzer['fiyat']); ?></span>
                            </div>
                            <div class="urun-butonlar">
                                <a href="urun.php?id=<?php echo $benzer['id']; ?>" class="btn btn-primary">Detay</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
