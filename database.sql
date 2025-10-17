-- E-Ticaret Veritabanı Yapısı
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    category_id INT,
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Sepet tablosu
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Siparişler tablosu
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sipariş detayları tablosu
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Örnek veriler ekle
INSERT INTO categories (name, description, image) VALUES
('Elektronik', 'Elektronik ürünler ve aksesuarlar', 'electronics.jpg'),
('Giyim', 'Kadın, erkek ve çocuk giyim', 'clothing.jpg'),
('Ev & Yaşam', 'Ev dekorasyonu ve yaşam ürünleri', 'home.jpg'),
('Spor', 'Spor malzemeleri ve fitness', 'sports.jpg');

INSERT INTO products (name, description, price, category_id, image, stock_quantity) VALUES
('iPhone 15 Pro', 'Apple iPhone 15 Pro 128GB', 45999.99, 1, 'iphone15.jpg', 10),
('Samsung Galaxy S24', 'Samsung Galaxy S24 256GB', 32999.99, 1, 'samsung-s24.jpg', 15),
('MacBook Air M2', 'Apple MacBook Air 13" M2', 25999.99, 1, 'macbook-air.jpg', 8),
('Nike Air Max', 'Nike Air Max 270 Erkek Ayakkabı', 1299.99, 2, 'nike-airmax.jpg', 25),
('Adidas T-Shirt', 'Adidas Erkek T-Shirt', 199.99, 2, 'adidas-tshirt.jpg', 50),
('Zara Elbise', 'Zara Kadın Yaz Elbisesi', 399.99, 2, 'zara-dress.jpg', 20),
('IKEA Masa', 'IKEA Beyaz Çalışma Masası', 899.99, 3, 'ikea-desk.jpg', 12),
('Philips Kahve Makinesi', 'Philips 2200 Serisi Kahve Makinesi', 1599.99, 3, 'philips-coffee.jpg', 6),
('Fitness Seti', 'Evde Spor Seti - Dambıl + Mat', 499.99, 4, 'fitness-set.jpg', 30),
('Yoga Matı', 'Premium Yoga Matı', 149.99, 4, 'yoga-mat.jpg', 40);