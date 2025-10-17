# E-Ticaret Web Sitesi

Modern ve kullanıcı dostu bir e-ticaret web sitesi. HTML, CSS, PHP ve MySQL kullanılarak geliştirilmiştir.

## 🚀 Özellikler

### Müşteri Özellikleri
- ✅ Responsive tasarım (mobil uyumlu)
- ✅ Ürün katalogu ve arama
- ✅ Ürün detay sayfaları
- ✅ Sepet yönetimi
- ✅ Kullanıcı kayıt/giriş sistemi
- ✅ Güvenli ödeme sistemi
- ✅ Sipariş takibi
- ✅ İstek listesi

### Admin Özellikleri
- ✅ Dashboard ve istatistikler
- ✅ Ürün yönetimi
- ✅ Sipariş yönetimi
- ✅ Kullanıcı yönetimi
- ✅ Kategori yönetimi
- ✅ Raporlar

## 🛠️ Teknolojiler

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Veritabanı:** MySQL 5.7+
- **Stil:** CSS Grid, Flexbox, Responsive Design
- **İkonlar:** Font Awesome
- **Grafikler:** Chart.js

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Web sunucusu (Apache/Nginx)
- PDO PHP uzantısı
- GD PHP uzantısı (resim işleme için)

## 🚀 Kurulum

### 1. Projeyi İndirin
```bash
git clone [repository-url]
cd ecommerce-website
```

### 2. Veritabanını Oluşturun
```sql
-- MySQL'de yeni veritabanı oluşturun
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Veritabanı Şemasını Yükleyin
```bash
# Veritabanı şemasını içe aktarın
mysql -u root -p ecommerce_db < database/schema.sql

# Örnek verileri yükleyin
mysql -u root -p ecommerce_db < database/sample_data.sql
```

### 4. Veritabanı Ayarlarını Yapılandırın
`config/database.php` dosyasını düzenleyin:
```php
$host = 'localhost';
$dbname = 'ecommerce_db';
$username = 'your_username';
$password = 'your_password';
```

### 5. Dosya İzinlerini Ayarlayın
```bash
# Resim klasörü için yazma izni
chmod 755 assets/images/products/
```

### 6. Web Sunucusunu Başlatın
```bash
# PHP built-in server kullanarak
php -S localhost:8000

# Veya Apache/Nginx ile
# Apache: http://localhost/ecommerce-website
# Nginx: http://localhost:80
```

## 📁 Proje Yapısı

```
ecommerce-website/
├── admin/                  # Admin paneli
│   ├── assets/
│   ├── includes/
│   └── *.php
├── assets/                 # Statik dosyalar
│   ├── css/
│   ├── js/
│   └── images/
├── config/                 # Yapılandırma dosyaları
│   └── database.php
├── database/               # Veritabanı dosyaları
│   ├── schema.sql
│   └── sample_data.sql
├── includes/               # Ortak dosyalar
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── ajax/                   # AJAX işlemleri
│   └── *.php
├── index.php              # Ana sayfa
├── products.php           # Ürünler sayfası
├── product.php            # Ürün detay sayfası
├── cart.php               # Sepet sayfası
├── checkout.php           # Ödeme sayfası
├── login.php              # Giriş sayfası
├── register.php           # Kayıt sayfası
└── README.md
```

## 🔧 Yapılandırma

### Site Ayarları
`database/settings` tablosundan site ayarlarını düzenleyebilirsiniz:
- Site adı
- İletişim bilgileri
- Kargo ücreti
- KDV oranı

### E-posta Ayarları
E-posta gönderimi için SMTP ayarlarını yapılandırın (gelecek güncellemede).

## 👥 Varsayılan Hesaplar

### Admin Hesabı
- **E-posta:** admin@eticaret.com
- **Şifre:** 123456

### Test Müşteri Hesapları
- **E-posta:** ahmet@example.com
- **Şifre:** 123456

- **E-posta:** ayse@example.com
- **Şifre:** 123456

## 🎨 Özelleştirme

### Tema Renkleri
Ana renkleri değiştirmek için `assets/css/style.css` dosyasındaki CSS değişkenlerini düzenleyin:
```css
:root {
    --primary-color: #e74c3c;
    --secondary-color: #2c3e50;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
}
```

### Logo ve Favicon
- Logo: `assets/images/logo.png`
- Favicon: `assets/images/favicon.ico`

## 📱 Responsive Tasarım

Site tüm cihazlarda optimize edilmiştir:
- **Desktop:** 1200px+
- **Tablet:** 768px - 1199px
- **Mobile:** 320px - 767px

## 🔒 Güvenlik

- SQL Injection koruması (PDO prepared statements)
- XSS koruması (htmlspecialchars)
- CSRF koruması (gelecek güncellemede)
- Şifre hashleme (password_hash)
- Güvenli oturum yönetimi

## 🚀 Performans

- CSS ve JS minifikasyonu (gelecek güncellemede)
- Resim optimizasyonu
- Veritabanı indeksleme
- Lazy loading (gelecek güncellemede)

## 📊 Özellikler

### Müşteri Paneli
- [x] Ürün görüntüleme ve arama
- [x] Sepet yönetimi
- [x] Sipariş verme
- [x] Hesap yönetimi
- [x] İstek listesi

### Admin Paneli
- [x] Dashboard ve istatistikler
- [x] Ürün yönetimi
- [x] Sipariş yönetimi
- [x] Kullanıcı yönetimi
- [x] Kategori yönetimi

## 🔄 Gelecek Güncellemeler

- [ ] E-posta bildirimleri
- [ ] SMS entegrasyonu
- [ ] Ödeme gateway entegrasyonu
- [ ] Çoklu dil desteği
- [ ] SEO optimizasyonu
- [ ] Cache sistemi
- [ ] API geliştirme

## 🐛 Hata Bildirimi

Hata bulduğunuzda lütfen issue açın veya e-posta gönderin.

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 👨‍💻 Geliştirici

E-Ticaret Web Sitesi - 2024

## 📞 İletişim

- **E-posta:** info@eticaret.com
- **Telefon:** +90 212 555 0123
- **Website:** https://eticaret.com

---

**Not:** Bu proje eğitim amaçlı geliştirilmiştir. Üretim ortamında kullanmadan önce güvenlik testlerini yapın.