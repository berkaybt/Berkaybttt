-- E-Ticaret Veritabanı Şeması
-- Bu dosyayı MySQL veya MariaDB'de çalıştırın

CREATE DATABASE IF NOT EXISTS eticaret CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eticaret;

-- Ürünler Tablosu
CREATE TABLE IF NOT EXISTS urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(200) NOT NULL,
    aciklama TEXT,
    fiyat DECIMAL(10, 2) NOT NULL,
    stok INT DEFAULT 0,
    kategori VARCHAR(100),
    resim VARCHAR(255),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kullanıcılar Tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    soyad VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    adres TEXT,
    rol ENUM('kullanici', 'admin') DEFAULT 'kullanici',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Siparişler Tablosu
CREATE TABLE IF NOT EXISTS siparisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT,
    toplam_tutar DECIMAL(10, 2) NOT NULL,
    durum ENUM('beklemede', 'onaylandi', 'kargoda', 'teslim_edildi', 'iptal') DEFAULT 'beklemede',
    teslimat_adresi TEXT NOT NULL,
    odeme_yontemi VARCHAR(50),
    siparis_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sipariş Detayları Tablosu
CREATE TABLE IF NOT EXISTS siparis_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id INT NOT NULL,
    urun_id INT,
    miktar INT NOT NULL,
    birim_fiyat DECIMAL(10, 2) NOT NULL,
    ara_toplam DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sepet Tablosu (Oturum bazlı)
CREATE TABLE IF NOT EXISTS sepet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    oturum_id VARCHAR(255) NOT NULL,
    urun_id INT NOT NULL,
    miktar INT DEFAULT 1,
    ekleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek Admin Kullanıcısı (Şifre: admin123)
INSERT INTO kullanicilar (ad, soyad, email, sifre, rol) VALUES 
('Admin', 'Kullanıcı', 'admin@eticaret.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Örnek Ürünler
INSERT INTO urunler (ad, aciklama, fiyat, stok, kategori, resim) VALUES
('Laptop Dell XPS 13', 'Intel Core i7, 16GB RAM, 512GB SSD, 13.3" FHD Display', 25999.99, 15, 'Bilgisayar', 'laptop1.jpg'),
('iPhone 15 Pro', '256GB, Titan Blue, A17 Pro Chip, ProMotion Display', 45999.99, 10, 'Telefon', 'iphone1.jpg'),
('Samsung Galaxy S24', '256GB, Phantom Black, Snapdragon 8 Gen 3', 35999.99, 20, 'Telefon', 'samsung1.jpg'),
('Sony WH-1000XM5 Kulaklık', 'Kablosuz, Noise Cancelling, 30 Saat Batarya', 8999.99, 30, 'Aksesuar', 'kulaklik1.jpg'),
('Apple Watch Series 9', 'GPS, 45mm, Midnight Aluminum Case', 12999.99, 25, 'Aksesuar', 'watch1.jpg'),
('iPad Air M2', '11", 256GB, Space Gray, Apple M2 Chip', 21999.99, 18, 'Tablet', 'ipad1.jpg'),
('MacBook Pro 14"', 'M3 Pro Chip, 18GB RAM, 512GB SSD', 67999.99, 8, 'Bilgisayar', 'macbook1.jpg'),
('Samsung 55" QLED TV', '4K, Smart TV, Quantum HDR', 28999.99, 12, 'Elektronik', 'tv1.jpg'),
('Logitech MX Master 3', 'Kablosuz Mouse, Ergonomik Tasarım', 2499.99, 50, 'Aksesuar', 'mouse1.jpg'),
('Mekanik Klavye RGB', 'Cherry MX Blue, Türkçe Q Klavye, RGB Aydınlatma', 3999.99, 35, 'Aksesuar', 'keyboard1.jpg');
