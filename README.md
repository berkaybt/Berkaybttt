# 🛒 E-Ticaret Web Sitesi

Modern ve profesyonel bir e-ticaret web sitesi. HTML, CSS, PHP ve MySQL teknolojileri kullanılarak sıfırdan geliştirilmiştir.

## 📋 Özellikler

### Müşteri Paneli
- ✅ Kullanıcı kayıt ve giriş sistemi
- ✅ Ürün listeleme ve kategorilere göre filtreleme
- ✅ Detaylı ürün görüntüleme sayfası
- ✅ Alışveriş sepeti yönetimi
- ✅ Ödeme ve sipariş oluşturma
- ✅ Sipariş onay sayfası
- ✅ Modern ve responsive tasarım

### Admin Paneli
- ✅ Ürün ekleme, düzenleme ve silme
- ✅ Sipariş yönetimi
- ✅ Sipariş durumu güncelleme
- ✅ İstatistik gösterimi
- ✅ Stok takibi

## 🚀 Kurulum

### Gereksinimler
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri / MariaDB
- Apache veya Nginx web sunucusu
- XAMPP, WAMP, MAMP veya benzeri yerel sunucu (geliştirme için)

### Adım 1: Dosyaları İndirin
Projeyi bilgisayarınıza indirin veya klonlayın:
```bash
git clone [repository-url]
```

### Adım 2: Veritabanını Oluşturun
1. phpMyAdmin'i açın (genellikle `http://localhost/phpmyadmin`)
2. Yeni bir veritabanı oluşturun (Örn: `eticaret`)
3. `database.sql` dosyasını içe aktarın:
   - Veritabanını seçin
   - "İçe Aktar" (Import) sekmesine gidin
   - `database.sql` dosyasını seçin
   - "Git" (Go) butonuna tıklayın

### Adım 3: Veritabanı Bağlantısını Yapılandırın
`config.php` dosyasını açın ve gerekirse veritabanı bilgilerini güncelleyin:

```php
define('DB_HOST', 'localhost');      // Veritabanı sunucusu
define('DB_USER', 'root');           // Veritabanı kullanıcı adı
define('DB_PASS', '');               // Veritabanı şifresi
define('DB_NAME', 'eticaret');       // Veritabanı adı
```

### Adım 4: Web Sunucusuna Yerleştirin
Proje dosyalarını web sunucunuzun kök dizinine kopyalayın:
- XAMPP için: `C:/xampp/htdocs/eticaret/`
- WAMP için: `C:/wamp64/www/eticaret/`
- MAMP için: `/Applications/MAMP/htdocs/eticaret/`

### Adım 5: Çalıştırın
Web tarayıcınızda şu adresi açın:
```
http://localhost/eticaret/
```

## 👤 Giriş Bilgileri

### Admin Paneli
- **E-posta:** admin@eticaret.com
- **Şifre:** admin123

Admin paneline erişmek için:
```
http://localhost/eticaret/admin.php
```

### Yeni Kullanıcı Kaydı
Müşteri olarak kullanmak için kayıt sayfasından yeni bir hesap oluşturabilirsiniz:
```
http://localhost/eticaret/kayit.php
```

## 📁 Proje Yapısı

```
eticaret/
│
├── resimler/                    # Ürün resimleri
│   ├── placeholder.jpg
│   ├── laptop1.jpg
│   ├── iphone1.jpg
│   └── ...
│
├── config.php                   # Veritabanı yapılandırması
├── database.sql                 # Veritabanı şeması
├── style.css                    # CSS stilleri
│
├── index.php                    # Ana sayfa
├── urun.php                     # Ürün detay sayfası
├── sepet.php                    # Alışveriş sepeti
├── sepet_ekle.php              # Sepete ürün ekleme
├── sepet_guncelle.php          # Sepet güncelleme
├── sepet_sil.php               # Sepetten ürün silme
├── odeme.php                    # Ödeme sayfası
├── siparis_onay.php            # Sipariş onay sayfası
│
├── giris.php                    # Kullanıcı girişi
├── kayit.php                    # Kullanıcı kaydı
├── cikis.php                    # Çıkış yapma
│
├── admin.php                    # Admin ana sayfa
├── admin_urun_ekle.php         # Ürün ekleme
├── admin_urun_duzenle.php      # Ürün düzenleme
├── admin_urun_sil.php          # Ürün silme
├── admin_siparis_detay.php     # Sipariş detayları
│
└── README.md                    # Proje dokümantasyonu
```

## 💾 Veritabanı Tabloları

### urunler
Ürün bilgilerini saklar (id, ad, aciklama, fiyat, stok, kategori, resim)

### kullanicilar
Kullanıcı hesap bilgilerini saklar (id, ad, soyad, email, sifre, rol)

### siparisler
Sipariş bilgilerini saklar (id, kullanici_id, toplam_tutar, durum, teslimat_adresi)

### siparis_detaylari
Sipariş ürün detaylarını saklar (id, siparis_id, urun_id, miktar, birim_fiyat)

### sepet
Alışveriş sepeti verilerini saklar (id, oturum_id, urun_id, miktar)

## 🎨 Özellikler

### Güvenlik
- ✅ PDO ile SQL Injection koruması
- ✅ Password hashing (bcrypt)
- ✅ XSS koruması (htmlspecialchars)
- ✅ CSRF token koruması (oturum bazlı)
- ✅ Admin yetki kontrolü

### Kullanıcı Deneyimi
- ✅ Modern ve responsive tasarım
- ✅ Mobil uyumlu arayüz
- ✅ Kullanıcı dostu navigasyon
- ✅ Gerçek zamanlı sepet güncellemesi
- ✅ Kategori filtreleme
- ✅ Ürün arama (kolay eklenebilir)

### Yönetim
- ✅ Kolay ürün yönetimi
- ✅ Sipariş takibi
- ✅ Stok kontrolü
- ✅ Sipariş durum güncelleme
- ✅ İstatistik paneli

## 🔧 Konfigürasyon

### Ürün Resimlerini Ekleme
1. Ürün resimlerini `resimler/` klasörüne yükleyin
2. Admin panelinden ürün eklerken resim dosya adını girin
3. Desteklenen formatlar: JPG, PNG, SVG

### Kargo Ücreti Ayarı
`sepet.php` ve `odeme.php` dosyalarında kargo ücretini değiştirebilirsiniz:
```php
$kargo = $ara_toplam > 500 ? 0 : 50; // 500 TL üzeri ücretsiz kargo
```

### KDV Oranı Ayarı
`sepet.php` ve `odeme.php` dosyalarında KDV oranını değiştirebilirsiniz:
```php
$kdv = $ara_toplam * 0.20; // %20 KDV
```

## 📱 Responsive Tasarım
Site tüm cihazlarda mükemmel çalışır:
- 💻 Desktop (1200px+)
- 📱 Tablet (768px - 1199px)
- 📱 Mobil (<768px)

## 🐛 Hata Giderme

### "Veritabanı bağlantı hatası" Mesajı
- `config.php` dosyasındaki veritabanı bilgilerini kontrol edin
- MySQL/MariaDB servisinin çalıştığından emin olun
- Veritabanının oluşturulduğunu doğrulayın

### Resimler Görünmüyor
- `resimler/` klasörünün var olduğundan emin olun
- Dosya izinlerini kontrol edin (755 veya 777)
- Resim dosya adlarının doğru olduğundan emin olun

### Admin Paneline Erişilemiyor
- Kullanıcı rolünün 'admin' olduğundan emin olun
- Veritabanında admin kullanıcısının var olduğunu kontrol edin
- Şifrenin doğru olduğundan emin olun (admin123)

## 🚀 Geliştirme Önerileri

### Eklenebilecek Özellikler
- 🔍 Gelişmiş ürün arama
- ⭐ Ürün değerlendirme ve yorumlar
- 📧 E-posta bildirimleri
- 💳 Gerçek ödeme entegrasyonu (iyzico, PayTR vb.)
- 📊 Gelişmiş raporlama
- 🌍 Çoklu dil desteği
- 💬 Canlı destek sistemi
- 🎁 Kupon ve indirim kodları
- 📱 PWA desteği
- 🔐 İki faktörlü kimlik doğrulama

## 📄 Lisans
Bu proje eğitim amaçlı hazırlanmıştır. Ticari kullanım için gerekli lisansları almanız gerekmektedir.

## 🤝 Destek
Sorularınız veya önerileriniz için:
- GitHub Issues kullanabilirsiniz
- Pull request'ler her zaman kabul edilir

## 📝 Notlar
- Bu proje temel bir e-ticaret sitesidir ve üretim ortamı için ek güvenlik önlemleri alınmalıdır
- Gerçek bir projede HTTPS kullanımı zorunludur
- Ödeme sistemleri entegre edilmeden önce PCI-DSS standartlarına uygunluk sağlanmalıdır
- Düzenli yedekleme ve güvenlik güncellemeleri yapılmalıdır

## ⚡ Hızlı Başlangıç
```bash
# 1. XAMPP'i başlatın (Apache ve MySQL)
# 2. phpMyAdmin'de 'eticaret' veritabanı oluşturun
# 3. database.sql dosyasını içe aktarın
# 4. http://localhost/eticaret/ adresini ziyaret edin
# 5. admin@eticaret.com / admin123 ile giriş yapın
```

---

**Geliştirici:** E-Ticaret Projesi  
**Versiyon:** 1.0.0  
**Son Güncelleme:** 2025  
**Teknolojiler:** PHP, MySQL, HTML5, CSS3, JavaScript

🎉 Başarılı alışverişler dileriz!
