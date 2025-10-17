<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sepet_id = isset($_POST['sepet_id']) ? (int)$_POST['sepet_id'] : 0;
    $miktar = isset($_POST['miktar']) ? (int)$_POST['miktar'] : 1;
    
    if ($sepet_id > 0 && $miktar > 0) {
        // Stok kontrolü
        $check_stmt = $db->prepare("
            SELECT s.id, u.stok 
            FROM sepet s 
            JOIN urunler u ON s.urun_id = u.id 
            WHERE s.id = :id AND s.oturum_id = :oturum_id
        ");
        $check_stmt->execute([
            'id' => $sepet_id,
            'oturum_id' => $_SESSION['sepet_id']
        ]);
        $result = $check_stmt->fetch();
        
        if ($result && $miktar <= $result['stok']) {
            $update_stmt = $db->prepare("UPDATE sepet SET miktar = :miktar WHERE id = :id");
            $update_stmt->execute([
                'miktar' => $miktar,
                'id' => $sepet_id
            ]);
            $_SESSION['mesaj'] = 'Sepet güncellendi!';
            $_SESSION['mesaj_tip'] = 'basarili';
        } else {
            $_SESSION['mesaj'] = 'Stok miktarını aştınız!';
            $_SESSION['mesaj_tip'] = 'hata';
        }
    }
}

header('Location: sepet.php');
exit();
?>
