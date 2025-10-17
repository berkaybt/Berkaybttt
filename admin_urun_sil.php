<?php
require_once 'config.php';
adminKontrol();

$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($urun_id > 0) {
    $stmt = $db->prepare("DELETE FROM urunler WHERE id = :id");
    if ($stmt->execute(['id' => $urun_id])) {
        $_SESSION['mesaj'] = 'Ürün başarıyla silindi!';
        $_SESSION['mesaj_tip'] = 'basarili';
    } else {
        $_SESSION['mesaj'] = 'Ürün silinirken bir hata oluştu!';
        $_SESSION['mesaj_tip'] = 'hata';
    }
}

header('Location: admin.php');
exit();
?>
