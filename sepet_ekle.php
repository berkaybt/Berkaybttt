<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_id = isset($_POST['urun_id']) ? (int)$_POST['urun_id'] : 0;
    $miktar = isset($_POST['miktar']) ? (int)$_POST['miktar'] : 1;
} else {
    $urun_id = isset($_GET['urun_id']) ? (int)$_GET['urun_id'] : 0;
    $miktar = 1;
}

if ($urun_id <= 0 || $miktar <= 0) {
    header('Location: index.php');
    exit();
}

// Ürün stok kontrolü
$stmt = $db->prepare("SELECT stok FROM urunler WHERE id = :id");
$stmt->execute(['id' => $urun_id]);
$urun = $stmt->fetch();

if (!$urun || $urun['stok'] < $miktar) {
    $_SESSION['mesaj'] = 'Üzgünüz, yeterli stok yok!';
    $_SESSION['mesaj_tip'] = 'hata';
    header('Location: urun.php?id=' . $urun_id);
    exit();
}

// Sepette bu ürün var mı kontrol et
$sepet_check = $db->prepare("SELECT id, miktar FROM sepet WHERE oturum_id = :oturum_id AND urun_id = :urun_id");
$sepet_check->execute([
    'oturum_id' => $_SESSION['sepet_id'],
    'urun_id' => $urun_id
]);
$mevcut = $sepet_check->fetch();

if ($mevcut) {
    // Miktarı güncelle
    $yeni_miktar = $mevcut['miktar'] + $miktar;
    if ($yeni_miktar > $urun['stok']) {
        $_SESSION['mesaj'] = 'Stok miktarını aştınız!';
        $_SESSION['mesaj_tip'] = 'hata';
        header('Location: sepet.php');
        exit();
    }
    
    $update_stmt = $db->prepare("UPDATE sepet SET miktar = :miktar WHERE id = :id");
    $update_stmt->execute([
        'miktar' => $yeni_miktar,
        'id' => $mevcut['id']
    ]);
} else {
    // Yeni ürün ekle
    $insert_stmt = $db->prepare("INSERT INTO sepet (oturum_id, urun_id, miktar) VALUES (:oturum_id, :urun_id, :miktar)");
    $insert_stmt->execute([
        'oturum_id' => $_SESSION['sepet_id'],
        'urun_id' => $urun_id,
        'miktar' => $miktar
    ]);
}

$_SESSION['mesaj'] = 'Ürün sepete eklendi!';
$_SESSION['mesaj_tip'] = 'basarili';

header('Location: sepet.php');
exit();
?>
