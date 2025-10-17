<?php
require_once 'config.php';
adminKontrol();

$siparis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sipariş bilgilerini getir
$stmt = $db->prepare("SELECT * FROM siparisler WHERE id = :id");
$stmt->execute(['id' => $siparis_id]);
$siparis = $stmt->fetch();

if (!$siparis) {
    header('Location: admin.php');
    exit();
}

// Durum güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['durum'])) {
    $yeni_durum = $_POST['durum'];
    $update_stmt = $db->prepare("UPDATE siparisler SET durum = :durum WHERE id = :id");
    if ($update_stmt->execute(['durum' => $yeni_durum, 'id' => $siparis_id])) {
        $_SESSION['mesaj'] = 'Sipariş durumu güncellendi!';
        $_SESSION['mesaj_tip'] = 'basarili';
        header('Location: admin_siparis_detay.php?id=' . $siparis_id);
        exit();
    }
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
    <title>Sipariş Detay - Yönetim Paneli</title>
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

    <main class="container admin-siparis-detay">
        <h1>Sipariş Detayı #<?php echo str_pad($siparis['id'], 6, '0', STR_PAD_LEFT); ?></h1>

        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="mesaj mesaj-<?php echo $_SESSION['mesaj_tip']; ?>">
                <?php 
                echo guvenliCikti($_SESSION['mesaj']); 
                unset($_SESSION['mesaj'], $_SESSION['mesaj_tip']);
                ?>
            </div>
        <?php endif; ?>

        <div class="siparis-bilgi-grid">
            <div class="bilgi-kutu">
                <h3>Sipariş Bilgileri</h3>
                <p><strong>Sipariş No:</strong> #<?php echo str_pad($siparis['id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></p>
                <p><strong>Toplam Tutar:</strong> <?php echo formatFiyat($siparis['toplam_tutar']); ?></p>
                <p><strong>Ödeme Yöntemi:</strong> <?php echo guvenliCikti(ucwords(str_replace('_', ' ', $siparis['odeme_yontemi']))); ?></p>
                <p><strong>Durum:</strong> 
                    <span class="durum-badge durum-<?php echo $siparis['durum']; ?>">
                        <?php echo guvenliCikti(ucwords(str_replace('_', ' ', $siparis['durum']))); ?>
                    </span>
                </p>
            </div>

            <div class="bilgi-kutu">
                <h3>Teslimat Adresi</h3>
                <p><?php echo nl2br(guvenliCikti($siparis['teslimat_adresi'])); ?></p>
            </div>
        </div>

        <div class="durum-guncelle">
            <h3>Sipariş Durumunu Güncelle</h3>
            <form method="POST" class="durum-form">
                <select name="durum" class="form-select">
                    <option value="beklemede" <?php echo $siparis['durum'] === 'beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                    <option value="onaylandi" <?php echo $siparis['durum'] === 'onaylandi' ? 'selected' : ''; ?>>Onaylandı</option>
                    <option value="kargoda" <?php echo $siparis['durum'] === 'kargoda' ? 'selected' : ''; ?>>Kargoda</option>
                    <option value="teslim_edildi" <?php echo $siparis['durum'] === 'teslim_edildi' ? 'selected' : ''; ?>>Teslim Edildi</option>
                    <option value="iptal" <?php echo $siparis['durum'] === 'iptal' ? 'selected' : ''; ?>>İptal</option>
                </select>
                <button type="submit" class="btn btn-primary">Güncelle</button>
            </form>
        </div>

        <h3>Sipariş İçeriği</h3>
        <table class="admin-tablo">
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
                                     class="admin-thumb"
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
                    <td colspan="3"><strong>Toplam:</strong></td>
                    <td><strong><?php echo formatFiyat($siparis['toplam_tutar']); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <a href="admin.php" class="btn btn-secondary">Geri Dön</a>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
