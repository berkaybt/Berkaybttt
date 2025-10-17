-- Seed data for local development
INSERT INTO users (name, email, password_hash, is_admin) VALUES
('Admin', 'admin@example.com', '$2y$10$H9u2OeZr4yY9o6b8qQ76cu3c9Z4lqUeI0J5J5d6Jt0q0j2kC4y7v6', 1), -- password: admin123
('Ali Veli', 'ali@example.com', '$2y$10$A8u2OeZr4yY9o6b8qQ76cujL8H4kqMeP0L2L1d6Ut1p1x9QF5h8p6', 0); -- password: user123

INSERT INTO products (name, description, price, image, stock) VALUES
('Klasik Tişört', 'Yumuşak pamuklu kumaştan klasik tişört.', 199.90, '/public/images/placeholder.svg', 100),
('Şık Ayakkabı', 'Günlük kullanım için rahat ve şık.', 749.00, '/public/images/placeholder.svg', 50),
('Akıllı Saat', 'Adım sayar, kalp atış hızı izleme ve daha fazlası.', 1499.00, '/public/images/placeholder.svg', 30);
