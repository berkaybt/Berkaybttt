# E-Ticaret Web Sitesi

Modern, responsive ve kullanıcı dostu bir e-ticaret web sitesi. HTML, CSS, PHP ve MySQL kullanılarak geliştirilmiştir.

## Özellikler

### Müşteri Özellikleri
- ✅ Responsive tasarım (mobil uyumlu)
- ✅ Ürün kataloğu ve arama
- ✅ Kategori filtreleme
- ✅ Sepet sistemi
- ✅ Kullanıcı kayıt/giriş sistemi
- ✅ Sipariş takibi
- ✅ Güvenli ödeme sistemi

### Admin Özellikleri
- ✅ Dashboard ve istatistikler
- ✅ Ürün yönetimi (ekleme, düzenleme, silme)
- ✅ Kategori yönetimi
- ✅ Sipariş yönetimi
- ✅ Kullanıcı yönetimi

## Kurulum

### Gereksinimler
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Web sunucusu (Apache/Nginx)

### Adımlar

1. **Projeyi indirin**
   ```bash
   git clone [repository-url]
   cd ecommerce-website
   ```

2. **Veritabanını oluşturun**
   - MySQL'de yeni bir veritabanı oluşturun
   - `database.sql` dosyasını çalıştırarak tabloları oluşturun

3. **Veritabanı ayarlarını yapın**
   - `config/database.php` dosyasını düzenleyin
   - Veritabanı bilgilerinizi girin

4. **Dosya izinlerini ayarlayın**
   ```bash
   chmod 755 assets/images/products/
   chmod 755 assets/images/categories/
   ```

5. **Web sunucusunu başlatın**
   - Apache/Nginx ile projeyi çalıştırın
   - Veya PHP built-in server ile:
   ```bash
   php -S localhost:8000
   ```

## Kullanım

### Müşteri Paneli
- Ana sayfa: `index.php`
- Ürünler: `products.php`
- Sepet: `cart.php`
- Kayıt: `register.php`
- Giriş: `login.php`

### Admin Paneli
- Admin giriş: `admin/login.php`
- Kullanıcı adı: `admin`
- Şifre: `admin123`

## Dosya Yapısı

```
ecommerce-website/
├── admin/                  # Admin paneli
│   ├── index.php
│   ├── login.php
│   ├── products.php
│   └── ...
├── assets/                 # Statik dosyalar
│   ├── css/
│   ├── js/
│   └── images/
├── config/                 # Yapılandırma
│   └── database.php
├── includes/               # Ortak dosyalar
│   └── functions.php
├── ajax/                   # AJAX istekleri
├── index.php              # Ana sayfa
├── products.php           # Ürünler sayfası
├── cart.php               # Sepet sayfası
├── checkout.php           # Ödeme sayfası
├── login.php              # Giriş sayfası
├── register.php           # Kayıt sayfası
└── database.sql           # Veritabanı yapısı
```

## Veritabanı Tabloları

- `users` - Kullanıcı bilgileri
- `categories` - Ürün kategorileri
- `products` - Ürün bilgileri
- `cart` - Sepet öğeleri
- `orders` - Siparişler
- `order_items` - Sipariş detayları

## Özelleştirme

### Tema Değişiklikleri
- `assets/css/style.css` dosyasını düzenleyin
- Renkler, fontlar ve düzen ayarlarını değiştirin

### Yeni Özellikler
- `includes/functions.php` dosyasına yeni fonksiyonlar ekleyin
- Veritabanı yapısını genişletin

## Güvenlik

- SQL injection koruması (PDO prepared statements)
- XSS koruması (htmlspecialchars)
- Şifre hashleme (password_hash)
- Session yönetimi

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Destek

Herhangi bir sorun yaşarsanız, lütfen issue oluşturun veya iletişime geçin.

## Güncellemeler

### v1.0.0
- İlk sürüm
- Temel e-ticaret özellikleri
- Admin paneli
- Responsive tasarım