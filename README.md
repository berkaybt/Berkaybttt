# E-Ticaret Web Sitesi

Modern ve responsive tasarıma sahip, PHP ve MySQL ile geliştirilmiş tam özellikli e-ticaret web sitesi.

## 🚀 Özellikler

### Kullanıcı Özellikleri
- ✅ Kullanıcı kayıt ve giriş sistemi
- ✅ Ürün listeleme ve detay sayfaları
- ✅ Kategori bazlı ürün filtreleme
- ✅ Arama ve sıralama özellikleri
- ✅ Sepet yönetimi
- ✅ Güvenli ödeme sistemi
- ✅ Sipariş takibi
- ✅ Responsive tasarım

### Admin Özellikleri
- ✅ Admin dashboard
- ✅ Ürün yönetimi (CRUD)
- ✅ Kategori yönetimi
- ✅ Sipariş yönetimi
- ✅ Kullanıcı yönetimi
- ✅ Stok takibi
- ✅ Satış istatistikleri

### Teknik Özellikler
- ✅ Modern CSS3 ve JavaScript
- ✅ Responsive tasarım
- ✅ AJAX ile dinamik içerik
- ✅ Güvenli PHP kodlama
- ✅ PDO ile veritabanı işlemleri
- ✅ Session yönetimi
- ✅ Resim yükleme sistemi

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- GD extension (resim işlemleri için)

## 🛠️ Kurulum

### 1. Dosyaları İndirin
```bash
git clone [repository-url]
cd eticaret-sitesi
```

### 2. Veritabanını Oluşturun
```sql
-- MySQL'de yeni veritabanı oluşturun
CREATE DATABASE eticaret;

-- database.sql dosyasını import edin
mysql -u root -p eticaret < database.sql
```

### 3. Veritabanı Ayarlarını Yapın
`config/database.php` dosyasını düzenleyin:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eticaret');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 4. Klasör İzinlerini Ayarlayın
```bash
chmod 755 images/
chmod 755 images/products/
chmod 755 images/categories/
```

### 5. Web Sunucusunu Başlatın
Apache veya Nginx üzerinden projeyi çalıştırın.

## 👥 Varsayılan Hesaplar

### Admin Hesabı
- **Kullanıcı Adı:** admin
- **Şifre:** admin123
- **E-posta:** admin@eticaret.com

### Test Kullanıcısı
- **Kullanıcı Adı:** testuser
- **Şifre:** test123
- **E-posta:** test@example.com

## 📁 Dosya Yapısı

```
eticaret-sitesi/
├── admin/                  # Admin paneli
│   ├── index.php          # Admin dashboard
│   └── products.php       # Ürün yönetimi
├── ajax/                  # AJAX işlemleri
│   ├── add_to_cart.php    # Sepete ekleme
│   ├── get_cart_count.php # Sepet sayısı
│   └── update_cart.php    # Sepet güncelleme
├── config/                # Yapılandırma dosyaları
│   └── database.php       # Veritabanı bağlantısı
├── css/                   # Stil dosyaları
│   └── style.css          # Ana CSS dosyası
├── images/                # Resim dosyaları
│   ├── products/          # Ürün resimleri
│   ├── categories/        # Kategori resimleri
│   └── no-image.jpg       # Varsayılan resim
├── includes/              # Yardımcı dosyalar
│   └── functions.php      # Genel fonksiyonlar
├── index.php              # Ana sayfa
├── products.php           # Ürün listesi
├── product.php            # Ürün detayı
├── categories.php         # Kategoriler
├── cart.php               # Sepet
├── checkout.php           # Ödeme
├── order-success.php      # Sipariş başarılı
├── login.php              # Giriş
├── register.php           # Kayıt
├── logout.php             # Çıkış
├── contact.php            # İletişim
├── database.sql           # Veritabanı yapısı
└── README.md              # Bu dosya
```

## 🎨 Tasarım Özellikleri

- **Modern Gradient Tasarım:** Mor-mavi gradient renk paleti
- **Responsive Layout:** Tüm cihazlarda uyumlu
- **Smooth Animasyonlar:** CSS3 geçişleri ve animasyonlar
- **Font Awesome İkonlar:** Profesyonel ikon seti
- **Card-based Layout:** Modern kart tasarımı

## 🔧 Özelleştirme

### Renk Temasını Değiştirme
`css/style.css` dosyasında ana renkleri değiştirebilirsiniz:
```css
/* Ana renk paleti */
--primary-color: #667eea;
--secondary-color: #764ba2;
--success-color: #2ed573;
--danger-color: #ff4757;
```

### Logo Değiştirme
Header bölümündeki logo metnini değiştirebilir veya resim ekleyebilirsiniz.

### Ödeme Sistemi Entegrasyonu
`checkout.php` dosyasında gerçek ödeme gateway'i entegre edebilirsiniz.

## 📧 İletişim

Sorularınız için:
- **E-posta:** info@eticaret.com
- **Telefon:** 0850 123 45 67

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'i push edin (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

## 📝 Güncelleme Notları

### v1.0.0
- İlk sürüm yayınlandı
- Temel e-ticaret özellikleri eklendi
- Admin paneli tamamlandı
- Responsive tasarım uygulandı

---

**Not:** Bu proje eğitim amaçlı geliştirilmiştir. Canlı ortamda kullanmadan önce güvenlik testlerini yapmanız önerilir.