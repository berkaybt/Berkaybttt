<?php
require_once 'config.php';

// Kategori filtresi
$kategori_filtre = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Ürünleri getir
$sql = "SELECT * FROM urunler WHERE stok > 0";
if ($kategori_filtre) {
    $sql .= " AND kategori = :kategori";
}
$sql .= " ORDER BY olusturma_tarihi DESC";

$stmt = $db->prepare($sql);
if ($kategori_filtre) {
    $stmt->bindParam(':kategori', $kategori_filtre);
}
$stmt->execute();
$urunler = $stmt->fetchAll();

// Kategorileri getir
$kategori_stmt = $db->query("SELECT DISTINCT kategori FROM urunler ORDER BY kategori");
$kategoriler = $kategori_stmt->fetchAll();

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
    <title>E-Ticaret - Ana Sayfa</title>
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
                    <a href="index.php" class="active">Ana Sayfa</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>En İyi Ürünler, En İyi Fiyatlar</h2>
            <p>Teknoloji ürünlerinde güvenilir alışverişin adresi</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container main-content">
        <!-- Kategoriler -->
        <aside class="sidebar">
            <div class="kategori-box">
                <h3>Kategoriler</h3>
                <ul class="kategori-liste">
                    <li><a href="index.php" class="<?php echo !$kategori_filtre ? 'active' : ''; ?>">Tüm Ürünler</a></li>
                    <?php foreach ($kategoriler as $kat): ?>
                        <li>
                            <a href="?kategori=<?php echo urlencode($kat['kategori']); ?>" 
                               class="<?php echo $kategori_filtre === $kat['kategori'] ? 'active' : ''; ?>">
                                <?php echo guvenliCikti($kat['kategori']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Ürün Listesi -->
        <section class="urun-listesi">
            <h2>
                <?php 
                if ($kategori_filtre) {
                    echo guvenliCikti($kategori_filtre) . ' Ürünleri';
                } else {
                    echo 'Tüm Ürünler';
                }
                ?>
            </h2>
            <div class="urun-grid">
                <?php foreach ($urunler as $urun): ?>
                    <div class="urun-kart">
                        <div class="urun-resim">
                            <img src="resimler/<?php echo guvenliCikti($urun['resim']); ?>" 
                                 alt="<?php echo guvenliCikti($urun['ad']); ?>"
                                 onerror="this.src='resimler/placeholder.jpg'">
                        </div>
                        <div class="urun-bilgi">
                            <h3><?php echo guvenliCikti($urun['ad']); ?></h3>
                            <p class="urun-aciklama"><?php echo guvenliCikti(mb_substr($urun['aciklama'], 0, 100)); ?>...</p>
                            <div class="urun-alt">
                                <span class="fiyat"><?php echo formatFiyat($urun['fiyat']); ?></span>
                                <span class="stok">Stok: <?php echo $urun['stok']; ?></span>
                            </div>
                            <div class="urun-butonlar">
                                <a href="urun.php?id=<?php echo $urun['id']; ?>" class="btn btn-primary">Detay</a>
                                <a href="sepet_ekle.php?urun_id=<?php echo $urun['id']; ?>" class="btn btn-success">Sepete Ekle</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
            <p>Güvenli alışverişin adresi</p>
        </div>
    </footer>
</body>
</html>
