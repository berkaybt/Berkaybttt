<?php
require_once 'config.php';
adminKontrol();

// Ürünleri getir
$urunler_stmt = $db->query("SELECT * FROM urunler ORDER BY id DESC");
$urunler = $urunler_stmt->fetchAll();

// Siparişleri getir
$siparisler_stmt = $db->query("SELECT * FROM siparisler ORDER BY siparis_tarihi DESC LIMIT 10");
$siparisler = $siparisler_stmt->fetchAll();

// İstatistikler
$toplam_urun = $db->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
$toplam_siparis = $db->query("SELECT COUNT(*) FROM siparisler")->fetchColumn();
$bekleyen_siparis = $db->query("SELECT COUNT(*) FROM siparisler WHERE durum = 'beklemede'")->fetchColumn();
$toplam_gelir = $db->query("SELECT SUM(toplam_tutar) FROM siparisler WHERE durum != 'iptal'")->fetchColumn();

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
    <title>Yönetim Paneli - E-Ticaret</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>🛒 E-Ticaret - Yönetim</h1>
                </div>
                <nav class="nav">
                    <a href="index.php">Ana Sayfa</a>
                    <a href="sepet.php">🛒 Sepet (<?php echo $sepet_sayisi; ?>)</a>
                    <a href="admin.php" class="active">Yönetim</a>
                    <a href="cikis.php">Çıkış Yap</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Admin Panel -->
    <main class="container admin-sayfa">
        <h1>Yönetim Paneli</h1>
        <p>Hoş geldiniz, <?php echo guvenliCikti($_SESSION['ad']); ?>!</p>

        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="mesaj mesaj-<?php echo $_SESSION['mesaj_tip']; ?>">
                <?php 
                echo guvenliCikti($_SESSION['mesaj']); 
                unset($_SESSION['mesaj'], $_SESSION['mesaj_tip']);
                ?>
            </div>
        <?php endif; ?>

        <!-- İstatistikler -->
        <div class="istatistik-grid">
            <div class="istatistik-kart">
                <h3>Toplam Ürün</h3>
                <p class="istatistik-sayi"><?php echo $toplam_urun; ?></p>
            </div>
            <div class="istatistik-kart">
                <h3>Toplam Sipariş</h3>
                <p class="istatistik-sayi"><?php echo $toplam_siparis; ?></p>
            </div>
            <div class="istatistik-kart">
                <h3>Bekleyen Sipariş</h3>
                <p class="istatistik-sayi"><?php echo $bekleyen_siparis; ?></p>
            </div>
            <div class="istatistik-kart">
                <h3>Toplam Gelir</h3>
                <p class="istatistik-sayi"><?php echo formatFiyat($toplam_gelir ?? 0); ?></p>
            </div>
        </div>

        <!-- Ürün Yönetimi -->
        <section class="admin-section">
            <div class="section-header">
                <h2>Ürün Yönetimi</h2>
                <a href="admin_urun_ekle.php" class="btn btn-success">+ Yeni Ürün Ekle</a>
            </div>
            
            <table class="admin-tablo">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($urunler as $urun): ?>
                        <tr>
                            <td><?php echo $urun['id']; ?></td>
                            <td>
                                <img src="resimler/<?php echo guvenliCikti($urun['resim']); ?>" 
                                     alt="<?php echo guvenliCikti($urun['ad']); ?>"
                                     class="admin-thumb"
                                     onerror="this.src='resimler/placeholder.jpg'">
                            </td>
                            <td><?php echo guvenliCikti($urun['ad']); ?></td>
                            <td><?php echo guvenliCikti($urun['kategori']); ?></td>
                            <td><?php echo formatFiyat($urun['fiyat']); ?></td>
                            <td><?php echo $urun['stok']; ?></td>
                            <td>
                                <a href="admin_urun_duzenle.php?id=<?php echo $urun['id']; ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                <a href="admin_urun_sil.php?id=<?php echo $urun['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Sipariş Yönetimi -->
        <section class="admin-section">
            <h2>Son Siparişler</h2>
            <table class="admin-tablo">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siparisler as $siparis): ?>
                        <tr>
                            <td>#<?php echo str_pad($siparis['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></td>
                            <td><?php echo formatFiyat($siparis['toplam_tutar']); ?></td>
                            <td>
                                <span class="durum-badge durum-<?php echo $siparis['durum']; ?>">
                                    <?php echo guvenliCikti(ucwords(str_replace('_', ' ', $siparis['durum']))); ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_siparis_detay.php?id=<?php echo $siparis['id']; ?>" class="btn btn-sm btn-primary">Detay</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Ticaret. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
