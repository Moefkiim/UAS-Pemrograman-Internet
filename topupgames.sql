-- Database schema for E-Commerce Game Top-Up Website

CREATE DATABASE IF NOT EXISTS game_topup;
USE game_topup;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Games table
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    publisher VARCHAR(255),
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Digital products table
CREATE TABLE digital_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    server_id INT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Product packages table
CREATE TABLE product_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    amount VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES digital_products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    server_id INT NULL,
    game_user_id VARCHAR(255) NOT NULL,
    nickname VARCHAR(255),
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    package_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES digital_products(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES product_packages(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method VARCHAR(255) NOT NULL,
    status ENUM('unpaid', 'paid', 'expired', 'failed') DEFAULT 'unpaid',
    payment_ref VARCHAR(255),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Topup logs table
CREATE TABLE topup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    request_data TEXT,
    response_data TEXT,
    status ENUM('success', 'failed') DEFAULT 'failed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$examplehashedpassword', 'admin'),
('Regular User', 'user@example.com', '$2y$10$examplehashedpassword', 'user');

INSERT INTO games (name, slug, publisher) VALUES
('Mobile Legends', 'mobile-legends', 'Moonton');

INSERT INTO digital_products (game_id, name, price) VALUES
(1, 'Diamonds', 10000);

INSERT INTO product_packages (product_id, amount, price) VALUES
(1, '86 Diamonds', 10000),
(1, '172 Diamonds', 20000),
(1, '257 Diamonds', 30000),
(1, '344 Diamonds', 40000),
(1, '430 Diamonds', 50000),
(1, '720 Diamonds', 80000);

-- Add PUBG Mobile Game
INSERT INTO games (name, slug, publisher) VALUES
('PUBG Mobile', 'pubg-mobile', 'Tencent Games');

-- Add UC (Unknown Cash) Product for PUBGM
INSERT INTO digital_products (game_id, name, price) VALUES
(
    (SELECT id FROM games WHERE slug = 'pubg-mobile'),
    'UC (Unknown Cash)',
    10000
);

-- Add UC Package Options
INSERT INTO product_packages (product_id, amount, price) VALUES
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '60 UC',
    9000
),
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '325 UC',
    45000
),
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '660 UC',
    90000
),
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '1320 UC',
    180000
),
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '3850 UC',
    450000
),
(
    (SELECT id FROM digital_products WHERE game_id = (SELECT id FROM games WHERE slug = 'pubg-mobile') AND name = 'UC (Unknown Cash)'),
    '7420 UC',
    900000
);
