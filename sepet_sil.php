<?php
require_once 'config.php';

$sepet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($sepet_id > 0) {
    $stmt = $db->prepare("DELETE FROM sepet WHERE id = :id AND oturum_id = :oturum_id");
    $stmt->execute([
        'id' => $sepet_id,
        'oturum_id' => $_SESSION['sepet_id']
    ]);
    
    $_SESSION['mesaj'] = 'Ürün sepetten çıkarıldı!';
    $_SESSION['mesaj_tip'] = 'basarili';
}

header('Location: sepet.php');
exit();
?>
