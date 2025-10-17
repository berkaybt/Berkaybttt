-- E-Ticaret Veritabanı Şeması
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- Kategoriler tablosu
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    parent_id INT DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Ürünler tablosu
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    gallery JSON DEFAULT NULL,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Türkiye',
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Siparişler tablosu
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    billing_address TEXT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sipariş detayları tablosu
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Sepet tablosu (oturum tabanlı sepet için)
CREATE TABLE cart_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sepet öğeleri tablosu
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_session_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_session_id) REFERENCES cart_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- İletişim mesajları tablosu
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site ayarları tablosu
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- İndeksler
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_cart_items_cart ON cart_items(cart_session_id);

-- Örnek veriler ekleme
INSERT INTO categories (name, slug, description) VALUES
('Elektronik', 'elektronik', 'Elektronik ürünler ve aksesuarlar'),
('Giyim', 'giyim', 'Kadın, erkek ve çocuk giyim ürünleri'),
('Ev & Yaşam', 'ev-yasam', 'Ev dekorasyonu ve yaşam ürünleri'),
('Spor & Outdoor', 'spor-outdoor', 'Spor malzemeleri ve outdoor ürünleri'),
('Kitap & Hobi', 'kitap-hobi', 'Kitaplar ve hobi malzemeleri');

INSERT INTO products (name, slug, description, short_description, price, sku, stock_quantity, category_id, image, featured) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'Apple iPhone 15 Pro 128GB, en yeni A17 Pro çip ile güçlendirilmiş, profesyonel fotoğraf ve video çekimi için optimize edilmiş.', 'En yeni iPhone 15 Pro modeli', 89999.99, 'IPH15PRO128', 50, 1, 'iphone15pro.jpg', 1),
('Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Samsung Galaxy S24 Ultra 256GB, S Pen ile birlikte, 200MP kamera ve AI özellikleri.', 'Samsung\'un en güçlü telefonu', 79999.99, 'SGS24U256', 30, 1, 'samsung-s24-ultra.jpg', 1),
('MacBook Air M3', 'macbook-air-m3', 'Apple MacBook Air 13" M3 çip, 8GB RAM, 256GB SSD, 18 saat pil ömrü.', 'Hafif ve güçlü MacBook Air', 45999.99, 'MBA13M3', 25, 1, 'macbook-air-m3.jpg', 1),
('Nike Air Max 270', 'nike-air-max-270', 'Nike Air Max 270 erkek spor ayakkabı, maksimum konfor ve stil.', 'Rahat ve şık spor ayakkabı', 1299.99, 'NAM270', 100, 4, 'nike-air-max-270.jpg', 0),
('Adidas Ultraboost 22', 'adidas-ultraboost-22', 'Adidas Ultraboost 22 koşu ayakkabısı, Boost teknolojisi ile maksimum enerji geri dönüşü.', 'Profesyonel koşu ayakkabısı', 1599.99, 'AUB22', 75, 4, 'adidas-ultraboost-22.jpg', 0),
('Levi\'s 501 Jean', 'levis-501-jean', 'Levi\'s 501 orijinal fit jean pantolon, %100 pamuk, klasik tasarım.', 'Klasik jean pantolon', 599.99, 'L501', 200, 2, 'levis-501.jpg', 0),
('Zara Kadın Bluz', 'zara-kadin-bluz', 'Zara kadın pamuklu bluz, modern kesim, günlük kullanım için ideal.', 'Şık kadın bluzu', 199.99, 'ZKB001', 150, 2, 'zara-bluz.jpg', 0),
('IKEA Kallax Raf', 'ikea-kallax-raf', 'IKEA Kallax 4x4 raf ünitesi, beyaz, modüler depolama çözümü.', 'Pratik depolama rafı', 899.99, 'IKK4X4', 40, 3, 'ikea-kallax.jpg', 0);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'E-Ticaret Sitesi'),
('site_description', 'Kaliteli ürünler, uygun fiyatlar'),
('contact_email', 'info@eticaret.com'),
('contact_phone', '+90 212 555 0123'),
('shipping_cost', '29.99'),
('free_shipping_limit', '500.00'),
('tax_rate', '18.00');