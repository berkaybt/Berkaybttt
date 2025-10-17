<?php
require_once 'config.php';

$siparis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($siparis_id <= 0) {
    header('Location: index.php');
    exit();
}

// Sipariş bilgilerini getir
$stmt = $db->prepare("SELECT * FROM siparisler WHERE id = :id");
$stmt->execute(['id' => $siparis_id]);
$siparis = $stmt->fetch();

if (!$siparis) {
    header('Location: index.php');
    exit();
}

// Sipariş detaylarını getir
$detay_stmt = $db->prepare("
    SELECT sd.*, u.ad, u.resim
    FROM siparis_detaylari sd
    JOIN urunler u ON sd.urun_id = u.id
    WHERE sd.siparis_id = :siparis_id
");
$detay_stmt->execute(['siparis_id' => $siparis_id]);
$detaylar = $detay_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Onayı - E-Ticaret</title>
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
                </nav>
            </div>
        </div>
    </header>

    <!-- Sipariş Onay -->
    <main class="container siparis-onay-sayfa">
        <div class="onay-kutu">
            <div class="onay-ikon">✓</div>
            <h1>Siparişiniz Alındı!</h1>
            <p class="tesekkur">Alışverişiniz için teşekkür ederiz.</p>
            
            <div class="siparis-bilgi">
                <h2>Sipariş Numarası: #<?php echo str_pad($siparis['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                <p>Sipariş Tarihi: <?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></p>
                <p>Ödeme Yöntemi: <?php echo guvenliCikti(ucwords(str_replace('_', ' ', $siparis['odeme_yontemi']))); ?></p>
                <p>Durum: <span class="durum-badge durum-<?php echo $siparis['durum']; ?>">
                    <?php echo guvenliCikti(ucwords(str_replace('_', ' ', $siparis['durum']))); ?>
                </span></p>
            </div>

            <div class="siparis-detay">
                <h3>Sipariş Detayları</h3>
                <table class="detay-tablo">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Miktar</th>
                            <th>Birim Fiyat</th>
                            <th>Ara Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detaylar as $detay): ?>
                            <tr>
                                <td>
                                    <div class="detay-urun">
                                        <img src="resimler/<?php echo guvenliCikti($detay['resim']); ?>" 
                                             alt="<?php echo guvenliCikti($detay['ad']); ?>"
                                             onerror="this.src='resimler/placeholder.jpg'">
                                        <?php echo guvenliCikti($detay['ad']); ?>
                                    </div>
                                </td>
                                <td><?php echo $detay['miktar']; ?></td>
                                <td><?php echo formatFiyat($detay['birim_fiyat']); ?></td>
                                <td><?php echo formatFiyat($detay['ara_toplam']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Toplam Tutar:</strong></td>
                            <td><strong><?php echo formatFiyat($siparis['toplam_tutar']); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="teslimat-bilgi">
                <h3>Teslimat Adresi</h3>
                <p><?php echo nl2br(guvenliCikti($siparis['teslimat_adresi'])); ?></p>
            </div>

            <div class="sonraki-adimlar">
                <h3>Sonraki Adımlar</h3>
                <ul>
                    <li>Siparişiniz hazırlanıyor</li>
                    <li>Kargoya verildiğinde e-posta ile bilgilendirileceksiniz</li>
                    <li>Kargo takip numaranız e-posta adresinize gönderilecek</li>
                </ul>
            </div>

            <a href="index.php" class="btn btn-primary btn-large">Alışverişe Devam Et</a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
