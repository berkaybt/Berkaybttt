-- E-Ticaret Veritabanı Yapısı
CREATE DATABASE IF NOT EXISTS eticaret;
USE eticaret;

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kategoriler tablosu
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ürünler tablosu
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Sipariş tablosu
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sipariş detayları tablosu
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sepet tablosu
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Örnek veriler
INSERT INTO categories (name, description, image) VALUES
('Elektronik', 'Telefon, bilgisayar ve elektronik ürünler', 'images/categories/elektronik.jpg'),
('Giyim', 'Erkek, kadın ve çocuk giyim', 'images/categories/giyim.jpg'),
('Ev & Yaşam', 'Ev dekorasyonu ve yaşam ürünleri', 'images/categories/ev-yasam.jpg'),
('Spor', 'Spor giyim ve ekipmanları', 'images/categories/spor.jpg');

INSERT INTO products (name, description, price, stock_quantity, category_id, image, featured) VALUES
('iPhone 15 Pro', 'Apple iPhone 15 Pro 128GB', 45000.00, 10, 1, 'images/products/iphone15.jpg', TRUE),
('Samsung Galaxy S24', 'Samsung Galaxy S24 256GB', 35000.00, 15, 1, 'images/products/samsung-s24.jpg', TRUE),
('MacBook Air M2', 'Apple MacBook Air M2 13 inch', 28000.00, 5, 1, 'images/products/macbook-air.jpg', TRUE),
('Nike Air Max', 'Nike Air Max spor ayakkabı', 2500.00, 20, 4, 'images/products/nike-airmax.jpg', FALSE),
('Levi\'s 501 Jean', 'Klasik Levi\'s 501 kot pantolon', 800.00, 30, 2, 'images/products/levis-jean.jpg', FALSE),
('Kahve Makinesi', 'Otomatik kahve makinesi', 1200.00, 8, 3, 'images/products/kahve-makinesi.jpg', FALSE);

-- Admin kullanıcı oluştur (şifre: admin123)
INSERT INTO users (username, email, password, full_name, is_admin) VALUES
('admin', 'admin@eticaret.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Yöneticisi', TRUE);

-- Test kullanıcısı (şifre: test123)
INSERT INTO users (username, email, password, full_name) VALUES
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Kullanıcısı');