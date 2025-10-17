<?php
// Veritabanı Konfigürasyonu
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eticaret');

// Oturum Başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı Bağlantısı
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yardımcı Fonksiyonlar
function guvenliCikti($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function formatFiyat($fiyat) {
    return number_format($fiyat, 2, ',', '.') . ' ₺';
}

function oturumKontrol() {
    if (!isset($_SESSION['kullanici_id'])) {
        header('Location: giris.php');
        exit();
    }
}

function adminKontrol() {
    if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

// Sepet ID'si (oturum bazlı)
if (!isset($_SESSION['sepet_id'])) {
    $_SESSION['sepet_id'] = session_id();
}
?>
