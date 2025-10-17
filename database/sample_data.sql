-- Örnek veri ekleme scripti
USE ecommerce_db;

-- Daha fazla kategori ekle
INSERT INTO categories (name, slug, description, parent_id) VALUES
('Telefon & Tablet', 'telefon-tablet', 'Akıllı telefonlar ve tabletler', 1),
('Bilgisayar & Laptop', 'bilgisayar-laptop', 'Masaüstü ve dizüstü bilgisayarlar', 1),
('TV & Ses Sistemleri', 'tv-ses-sistemleri', 'Televizyonlar ve ses sistemleri', 1),
('Kadın Giyim', 'kadin-giyim', 'Kadın giyim ürünleri', 2),
('Erkek Giyim', 'erkek-giyim', 'Erkek giyim ürünleri', 2),
('Çocuk Giyim', 'cocuk-giyim', 'Çocuk giyim ürünleri', 2),
('Mobilya', 'mobilya', 'Ev mobilyaları', 3),
('Dekorasyon', 'dekorasyon', 'Ev dekorasyon ürünleri', 3),
('Fitness', 'fitness', 'Fitness ve spor ekipmanları', 4),
('Outdoor', 'outdoor', 'Açık hava sporları', 4);

-- Daha fazla ürün ekle
INSERT INTO products (name, slug, description, short_description, price, sku, stock_quantity, category_id, image, featured) VALUES
-- Telefon & Tablet
('iPad Pro 12.9"', 'ipad-pro-12-9', 'Apple iPad Pro 12.9" M2 çip, 128GB, Wi-Fi, Liquid Retina XDR ekran.', 'Profesyonel tablet', 32999.99, 'IPP12M2', 20, 6, 'ipad-pro-12-9.jpg', 1),
('Samsung Galaxy Tab S9', 'samsung-galaxy-tab-s9', 'Samsung Galaxy Tab S9 11" 128GB, S Pen dahil, AMOLED ekran.', 'Güçlü Android tablet', 19999.99, 'SGTS9', 15, 6, 'samsung-tab-s9.jpg', 0),

-- Bilgisayar & Laptop
('Dell XPS 13', 'dell-xps-13', 'Dell XPS 13 13.4" i7-1360P, 16GB RAM, 512GB SSD, Windows 11.', 'Premium ultrabook', 25999.99, 'DXPS13', 10, 7, 'dell-xps-13.jpg', 1),
('HP Pavilion 15', 'hp-pavilion-15', 'HP Pavilion 15 15.6" i5-1235U, 8GB RAM, 256GB SSD, Windows 11.', 'Uygun fiyatlı laptop', 15999.99, 'HPP15', 25, 7, 'hp-pavilion-15.jpg', 0),

-- TV & Ses Sistemleri
('Samsung 55" QLED TV', 'samsung-55-qled-tv', 'Samsung 55" QLED 4K Smart TV, HDR10+, Tizen işletim sistemi.', '4K QLED televizyon', 24999.99, 'SQ55', 8, 8, 'samsung-55-qled.jpg', 1),
('Sony WH-1000XM5', 'sony-wh-1000xm5', 'Sony WH-1000XM5 kablosuz gürültü önleyici kulaklık, 30 saat pil.', 'Premium kulaklık', 8999.99, 'SWH1000XM5', 30, 8, 'sony-wh-1000xm5.jpg', 0),

-- Kadın Giyim
('H&M Kadın Elbise', 'hm-kadin-elbise', 'H&M kadın yaz elbisesi, pamuklu, çiçekli desen, günlük kullanım.', 'Şık yaz elbisesi', 299.99, 'HME001', 80, 9, 'hm-elbise.jpg', 0),
('Mango Kadın Ceket', 'mango-kadin-ceket', 'Mango kadın blazer ceket, koyu mavi, iş kıyafeti için ideal.', 'Profesyonel ceket', 599.99, 'MKC001', 45, 9, 'mango-ceket.jpg', 0),

-- Erkek Giyim
('LC Waikiki Erkek Tişört', 'lc-waikiki-erkek-tisort', 'LC Waikiki erkek pamuklu tişört, çeşitli renkler, günlük kullanım.', 'Rahat tişört', 49.99, 'LCWET001', 200, 10, 'lc-tisort.jpg', 0),
('Koton Erkek Pantolon', 'koton-erkek-pantolon', 'Koton erkek chino pantolon, koyu gri, iş ve günlük kullanım.', 'Klasik chino pantolon', 199.99, 'KEP001', 120, 10, 'koton-pantolon.jpg', 0),

-- Mobilya
('IKEA Malm Yatak', 'ikea-malm-yatak', 'IKEA Malm yatak başlığı, beyaz, 160x200 cm, minimal tasarım.', 'Modern yatak başlığı', 1299.99, 'IMY160', 15, 12, 'ikea-malm-yatak.jpg', 0),
('IKEA Billy Kitaplık', 'ikea-billy-kitaplik', 'IKEA Billy kitaplık, beyaz, 80x202 cm, 5 raf, modüler sistem.', 'Pratik kitaplık', 599.99, 'IBK80', 25, 12, 'ikea-billy.jpg', 0),

-- Fitness
('Nike Dumbell Set', 'nike-dumbell-set', 'Nike 2x5kg dambıl seti, kauçuk kaplama, ev sporları için ideal.', 'Ev spor seti', 799.99, 'NDS5KG', 50, 14, 'nike-dumbell.jpg', 0),
('Adidas Yoga Mat', 'adidas-yoga-mat', 'Adidas yoga matı, 6mm kalınlık, kaymaz yüzey, taşıma çantası dahil.', 'Profesyonel yoga matı', 299.99, 'AYM6MM', 100, 14, 'adidas-yoga-mat.jpg', 0),

-- Outdoor
('The North Face Sırt Çantası', 'tnf-sirt-cantasi', 'The North Face Recon sırt çantası, 30L, dayanıklı, şehir ve doğa kullanımı.', 'Dayanıklı sırt çantası', 1299.99, 'TNFR30', 40, 15, 'tnf-sirt-cantasi.jpg', 0),
('Columbia Erkek Mont', 'columbia-erkek-mont', 'Columbia erkek kış montu, su geçirmez, ısı yalıtımlı, outdoor kullanım.', 'Kış montu', 1999.99, 'CEM001', 30, 15, 'columbia-mont.jpg', 0);

-- Örnek kullanıcı ekle (şifre: 123456)
INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, role) VALUES
('Admin', 'User', 'admin@eticaret.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 212 555 0001', 'Admin Adresi', 'İstanbul', '34000', 'admin'),
('Ahmet', 'Yılmaz', 'ahmet@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 212 555 0002', 'Örnek Mah. Test Cad. No:1', 'İstanbul', '34000', 'customer'),
('Ayşe', 'Demir', 'ayse@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 212 555 0003', 'Test Mah. Örnek Cad. No:5', 'Ankara', '06000', 'customer');

-- Örnek sipariş ekle
INSERT INTO orders (user_id, order_number, status, payment_status, payment_method, subtotal, tax_amount, shipping_amount, total_amount, shipping_address, billing_address) VALUES
(2, 'ORD-2024-001', 'delivered', 'paid', 'credit_card', 89999.99, 16199.99, 0.00, 106199.98, 'Örnek Mah. Test Cad. No:1, İstanbul, 34000', 'Örnek Mah. Test Cad. No:1, İstanbul, 34000'),
(3, 'ORD-2024-002', 'processing', 'paid', 'bank_transfer', 1299.99, 233.99, 29.99, 1563.97, 'Test Mah. Örnek Cad. No:5, Ankara, 06000', 'Test Mah. Örnek Cad. No:5, Ankara, 06000');

-- Örnek sipariş detayları
INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES
(1, 1, 1, 89999.99, 89999.99),
(2, 4, 1, 1299.99, 1299.99);

-- Örnek iletişim mesajları
INSERT INTO contact_messages (name, email, subject, message, status) VALUES
('Mehmet Kaya', 'mehmet@example.com', 'Ürün Sorgusu', 'iPhone 15 Pro hakkında bilgi almak istiyorum.', 'new'),
('Fatma Öz', 'fatma@example.com', 'Kargo Takibi', 'Siparişim ne zaman kargoya verilecek?', 'read'),
('Ali Veli', 'ali@example.com', 'İade Talebi', 'Aldığım ürünü iade etmek istiyorum.', 'new');