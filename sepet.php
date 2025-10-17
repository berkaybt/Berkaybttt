<?php
require_once 'config.php';

// Sepet ürünlerini getir
$stmt = $db->prepare("
    SELECT s.id, s.miktar, u.ad, u.fiyat, u.resim, u.stok, u.id as urun_id,
           (s.miktar * u.fiyat) as ara_toplam
    FROM sepet s
    JOIN urunler u ON s.urun_id = u.id
    WHERE s.oturum_id = :oturum_id
");
$stmt->execute(['oturum_id' => $_SESSION['sepet_id']]);
$sepet_urunler = $stmt->fetchAll();

// Toplam tutar hesapla
$toplam = 0;
foreach ($sepet_urunler as $item) {
    $toplam += $item['ara_toplam'];
}

// Sepetteki ürün sayısı
$sepet_sayisi = array_sum(array_column($sepet_urunler, 'miktar'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - E-Ticaret</title>
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
                    <a href="sepet.php" class="active">🛒 Sepet (<?php echo $sepet_sayisi; ?>)</a>
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

    <!-- Sepet İçeriği -->
    <main class="container sepet-sayfa">
        <h1>Alışveriş Sepetim</h1>

        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="mesaj mesaj-<?php echo $_SESSION['mesaj_tip']; ?>">
                <?php 
                echo guvenliCikti($_SESSION['mesaj']); 
                unset($_SESSION['mesaj'], $_SESSION['mesaj_tip']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($sepet_urunler)): ?>
            <div class="bos-sepet">
                <h2>Sepetiniz boş</h2>
                <p>Alışverişe başlamak için ürünleri inceleyebilirsiniz.</p>
                <a href="index.php" class="btn btn-primary">Alışverişe Başla</a>
            </div>
        <?php else: ?>
            <div class="sepet-icerik">
                <div class="sepet-urunler">
                    <table class="sepet-tablo">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Fiyat</th>
                                <th>Miktar</th>
                                <th>Ara Toplam</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sepet_urunler as $item): ?>
                                <tr>
                                    <td>
                                        <div class="sepet-urun">
                                            <img src="resimler/<?php echo guvenliCikti($item['resim']); ?>" 
                                                 alt="<?php echo guvenliCikti($item['ad']); ?>"
                                                 onerror="this.src='resimler/placeholder.jpg'">
                                            <div>
                                                <a href="urun.php?id=<?php echo $item['urun_id']; ?>">
                                                    <?php echo guvenliCikti($item['ad']); ?>
                                                </a>
                                                <small>Stok: <?php echo $item['stok']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatFiyat($item['fiyat']); ?></td>
                                    <td>
                                        <form action="sepet_guncelle.php" method="POST" class="miktar-form">
                                            <input type="hidden" name="sepet_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" name="miktar" value="<?php echo $item['miktar']; ?>" 
                                                   min="1" max="<?php echo $item['stok']; ?>">
                                            <button type="submit" class="btn btn-sm">Güncelle</button>
                                        </form>
                                    </td>
                                    <td class="fiyat-bold"><?php echo formatFiyat($item['ara_toplam']); ?></td>
                                    <td>
                                        <a href="sepet_sil.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bu ürünü sepetten çıkarmak istediğinize emin misiniz?')">
                                            Sil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="sepet-ozet">
                    <h2>Sipariş Özeti</h2>
                    <div class="ozet-satir">
                        <span>Ara Toplam:</span>
                        <span><?php echo formatFiyat($toplam); ?></span>
                    </div>
                    <div class="ozet-satir">
                        <span>KDV (%20):</span>
                        <span><?php echo formatFiyat($toplam * 0.20); ?></span>
                    </div>
                    <div class="ozet-satir">
                        <span>Kargo:</span>
                        <span><?php echo $toplam > 500 ? 'ÜCRETSİZ' : formatFiyat(50); ?></span>
                    </div>
                    <hr>
                    <div class="ozet-satir toplam">
                        <span>Genel Toplam:</span>
                        <span><?php echo formatFiyat($toplam * 1.20 + ($toplam > 500 ? 0 : 50)); ?></span>
                    </div>
                    <a href="odeme.php" class="btn btn-success btn-large">Ödemeye Geç</a>
                    <a href="index.php" class="btn btn-secondary">Alışverişe Devam Et</a>
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
