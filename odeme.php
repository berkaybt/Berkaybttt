<?php
require_once 'config.php';

// Sepet kontrolü
$stmt = $db->prepare("
    SELECT s.id, s.miktar, u.ad, u.fiyat, u.stok, u.id as urun_id,
           (s.miktar * u.fiyat) as ara_toplam
    FROM sepet s
    JOIN urunler u ON s.urun_id = u.id
    WHERE s.oturum_id = :oturum_id
");
$stmt->execute(['oturum_id' => $_SESSION['sepet_id']]);
$sepet_urunler = $stmt->fetchAll();

if (empty($sepet_urunler)) {
    header('Location: sepet.php');
    exit();
}

// Toplam hesapla
$ara_toplam = 0;
foreach ($sepet_urunler as $item) {
    $ara_toplam += $item['ara_toplam'];
}
$kdv = $ara_toplam * 0.20;
$kargo = $ara_toplam > 500 ? 0 : 50;
$toplam = $ara_toplam + $kdv + $kargo;

// Form gönderimi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
    $adres = isset($_POST['adres']) ? trim($_POST['adres']) : '';
    $odeme_yontemi = isset($_POST['odeme_yontemi']) ? $_POST['odeme_yontemi'] : '';
    
    $hatalar = [];
    
    if (empty($ad)) $hatalar[] = 'Ad alanı zorunludur';
    if (empty($soyad)) $hatalar[] = 'Soyad alanı zorunludur';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $hatalar[] = 'Geçerli bir e-posta adresi giriniz';
    if (empty($telefon)) $hatalar[] = 'Telefon alanı zorunludur';
    if (empty($adres)) $hatalar[] = 'Adres alanı zorunludur';
    if (empty($odeme_yontemi)) $hatalar[] = 'Ödeme yöntemi seçiniz';
    
    if (empty($hatalar)) {
        try {
            $db->beginTransaction();
            
            // Kullanıcı ID'si (misafir için NULL)
            $kullanici_id = isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : null;
            
            // Sipariş oluştur
            $siparis_stmt = $db->prepare("
                INSERT INTO siparisler (kullanici_id, toplam_tutar, durum, teslimat_adresi, odeme_yontemi)
                VALUES (:kullanici_id, :toplam_tutar, 'beklemede', :teslimat_adresi, :odeme_yontemi)
            ");
            $teslimat_adresi = "$ad $soyad\n$adres\nTel: $telefon\nE-posta: $email";
            $siparis_stmt->execute([
                'kullanici_id' => $kullanici_id,
                'toplam_tutar' => $toplam,
                'teslimat_adresi' => $teslimat_adresi,
                'odeme_yontemi' => $odeme_yontemi
            ]);
            
            $siparis_id = $db->lastInsertId();
            
            // Sipariş detayları ve stok güncelleme
            foreach ($sepet_urunler as $item) {
                // Sipariş detayı ekle
                $detay_stmt = $db->prepare("
                    INSERT INTO siparis_detaylari (siparis_id, urun_id, miktar, birim_fiyat, ara_toplam)
                    VALUES (:siparis_id, :urun_id, :miktar, :birim_fiyat, :ara_toplam)
                ");
                $detay_stmt->execute([
                    'siparis_id' => $siparis_id,
                    'urun_id' => $item['urun_id'],
                    'miktar' => $item['miktar'],
                    'birim_fiyat' => $item['fiyat'],
                    'ara_toplam' => $item['ara_toplam']
                ]);
                
                // Stok güncelle
                $stok_stmt = $db->prepare("UPDATE urunler SET stok = stok - :miktar WHERE id = :id");
                $stok_stmt->execute([
                    'miktar' => $item['miktar'],
                    'id' => $item['urun_id']
                ]);
            }
            
            // Sepeti temizle
            $temizle_stmt = $db->prepare("DELETE FROM sepet WHERE oturum_id = :oturum_id");
            $temizle_stmt->execute(['oturum_id' => $_SESSION['sepet_id']]);
            
            $db->commit();
            
            $_SESSION['mesaj'] = 'Siparişiniz başarıyla oluşturuldu! Sipariş No: ' . $siparis_id;
            $_SESSION['mesaj_tip'] = 'basarili';
            header('Location: siparis_onay.php?id=' . $siparis_id);
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $hatalar[] = 'Sipariş oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

// Sepetteki ürün sayısı
$sepet_sayisi = array_sum(array_column($sepet_urunler, 'miktar'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - E-Ticaret</title>
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
                </nav>
            </div>
        </div>
    </header>

    <!-- Ödeme Formu -->
    <main class="container odeme-sayfa">
        <h1>Ödeme Bilgileri</h1>

        <?php if (!empty($hatalar)): ?>
            <div class="mesaj mesaj-hata">
                <ul>
                    <?php foreach ($hatalar as $hata): ?>
                        <li><?php echo guvenliCikti($hata); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="odeme-icerik">
            <div class="odeme-form">
                <form method="POST" action="">
                    <h2>Teslimat Bilgileri</h2>
                    
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
                        <label>Telefon *</label>
                        <input type="tel" name="telefon" required value="<?php echo isset($_POST['telefon']) ? guvenliCikti($_POST['telefon']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Teslimat Adresi *</label>
                        <textarea name="adres" rows="4" required><?php echo isset($_POST['adres']) ? guvenliCikti($_POST['adres']) : ''; ?></textarea>
                    </div>

                    <h2>Ödeme Yöntemi</h2>
                    
                    <div class="odeme-yontemleri">
                        <label class="odeme-secenek">
                            <input type="radio" name="odeme_yontemi" value="kredi_karti" required>
                            <span>💳 Kredi Kartı</span>
                        </label>
                        <label class="odeme-secenek">
                            <input type="radio" name="odeme_yontemi" value="kapida_odeme" required>
                            <span>🚚 Kapıda Ödeme</span>
                        </label>
                        <label class="odeme-secenek">
                            <input type="radio" name="odeme_yontemi" value="havale" required>
                            <span>🏦 Havale / EFT</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success btn-large">Siparişi Tamamla</button>
                </form>
            </div>

            <div class="odeme-ozet">
                <h2>Sipariş Özeti</h2>
                
                <div class="ozet-urunler">
                    <?php foreach ($sepet_urunler as $item): ?>
                        <div class="ozet-urun">
                            <span><?php echo guvenliCikti($item['ad']); ?> x <?php echo $item['miktar']; ?></span>
                            <span><?php echo formatFiyat($item['ara_toplam']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr>

                <div class="ozet-satir">
                    <span>Ara Toplam:</span>
                    <span><?php echo formatFiyat($ara_toplam); ?></span>
                </div>
                <div class="ozet-satir">
                    <span>KDV (%20):</span>
                    <span><?php echo formatFiyat($kdv); ?></span>
                </div>
                <div class="ozet-satir">
                    <span>Kargo:</span>
                    <span><?php echo $kargo == 0 ? 'ÜCRETSİZ' : formatFiyat($kargo); ?></span>
                </div>
                <hr>
                <div class="ozet-satir toplam">
                    <span>Genel Toplam:</span>
                    <span><?php echo formatFiyat($toplam); ?></span>
                </div>

                <?php if ($ara_toplam > 500): ?>
                    <div class="ucretsiz-kargo-mesaj">
                        ✓ Ücretsiz kargo kazandınız!
                    </div>
                <?php endif; ?>
            </div>
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
