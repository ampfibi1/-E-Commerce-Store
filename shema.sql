-- E-Commerce Store Database Schema

CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'seller', 'delivery_manager', 'admin') NOT NULL,
    profile_pic VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customer saved addresses
CREATE TABLE customer_addresses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT             NOT NULL,
    label           VARCHAR(100)    DEFAULT NULL,
    address_line    TEXT            NOT NULL,
    city            VARCHAR(100)    NOT NULL,
    zip             VARCHAR(20)     DEFAULT NULL,
    is_default      TINYINT(1)      NOT NULL DEFAULT 0,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sellers table
CREATE TABLE sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shop_name VARCHAR(255) NOT NULL,
    shop_description TEXT,
    shop_logo_path VARCHAR(255),
    address TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_qty INT NOT NULL,
    primary_image_path VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Product images table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT, -- NULL for platform coupons
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_pct DECIMAL(5,2) NOT NULL,
    max_uses INT,
    uses_count INT DEFAULT 0,
    valid_until DATETIME,
    is_active BOOLEAN DEFAULT TRUE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    shipping_address TEXT,
    zone_id INT NULL,
    payment_method VARCHAR(50),
    subtotal DECIMAL(10,2),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested', 'returned') DEFAULT 'pending',
    coupon_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    item_status ENUM('pending', 'confirmed', 'shipped', 'delivered') DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id)
);

-- Delivery agents table
CREATE TABLE delivery_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_type VARCHAR(50),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Delivery assignments table
CREATE TABLE delivery_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    agent_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('assigned', 'picked_up', 'in_transit', 'delivered', 'failed'),
    delivery_zone VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (agent_id) REFERENCES delivery_agents(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    seller_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Wishlists table
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Return requests table
CREATE TABLE return_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    order_item_id INT NOT NULL,
    customer_id INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Delivery zones table
CREATE TABLE delivery_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    estimated_days INT NOT NULL
);

-- Disputes table
CREATE TABLE disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    seller_id INT NOT NULL,
    order_id INT NOT NULL,
    description TEXT,
    status ENUM('open', 'resolved') DEFAULT 'open',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user
INSERT INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@example.com', 'admin123', 'admin');

-- Sample users for visualization
INSERT INTO users (name, email, password_hash, role) VALUES
('Tamjid', 'tamjid@example.com', 'admin123', 'seller'),
('Mahin', 'mahin@example.com', 'admin123', 'seller'),
('Ayesha', 'ayesha@example.com', 'admin123', 'customer'),
('Rashed', 'rashed@example.com', 'admin123', 'customer');

-- Sample seller shops
INSERT INTO sellers (user_id, shop_name, shop_description, is_approved, commission_rate) VALUES
(2, 'Tamjid Electronics', 'Trusted electronics & accessories from BD', 1, 12.50),
(3, 'Mahin Boutique', 'Stylish Bangladeshi fashion with heart', 1, 10.00);

-- Sample categories
INSERT INTO categories (id, parent_id, name, description) VALUES
(1, NULL, 'Electronics', 'Mobile, audio, and accessories'),
(2, NULL, 'Fashion', 'Clothing and lifestyle products'),
(3, NULL, 'Home & Kitchen', 'Home essentials inspired by BD');

-- Sample products
INSERT INTO products (seller_id, category_id, name, description, price, stock_qty, primary_image_path, is_available) VALUES
(1, 1, 'BD Smart TV', '42-inch smart TV for home entertainment', 34999.00, 15, 'images/tv.jpg', 1),
(1, 1, 'Wireless Earbuds', 'Comfortable earbuds with long battery life', 1999.00, 35, 'images/earbuds.jpg', 1),
(2, 2, 'Cotton Panjabi', 'Traditional Bangladeshi panjabi made from soft cotton', 1299.00, 20, 'images/panjabi.jpg', 1),
(2, 2, 'Handcrafted Shawl', 'Warm and stylish handmade shawl from BD weavers', 1499.00, 12, 'images/shawl.jpg', 1),
(2, 3, 'Ceramic Dinner Set', 'Elegant dinner set for family gatherings', 2599.00, 10, 'images/dinnerset.jpg', 1);

-- Sample coupons
INSERT INTO coupons (seller_id, code, discount_pct, max_uses, uses_count, valid_until, is_active) VALUES
(0, 'BDBONUS10', 10.00, 100, 0, '2026-12-31 23:59:59', 1),
(0, 'FREEDELBD', 5.00, 50, 0, '2026-11-30 23:59:59', 1);

-- Sample orders and order items
INSERT INTO orders (customer_id, shipping_address, payment_method, subtotal, discount_amount, total_amount, status, coupon_id) VALUES
(4, 'House 12, Road 5, Dhaka', 'Cash on Delivery', 34999.00, 0.00, 34999.00, 'delivered', NULL),
(5, 'Apartment 3A, Gulshan, Dhaka', 'bKash', 2798.00, 0.00, 2798.00, 'confirmed', 1);

INSERT INTO order_items (order_id, product_id, seller_id, quantity, unit_price, item_status) VALUES
(1, 1, 1, 1, 34999.00, 'delivered'),
(2, 4, 2, 2, 1499.00, 'confirmed');

-- Sample announcements
INSERT INTO announcements (title, content) VALUES
('Welcome to BD Marketplace', 'Discover quality Bangladesh-made products from top sellers like Tamjid and Mahin.'),
('Holiday Sale', 'Enjoy exclusive discounts on electronics and fashion throughout December.');

--sample disputes values
INSERT INTO disputes (
    customer_id,
    seller_id,
    order_id,
    description,
    status,
    admin_note
)
VALUES
(
    4,
    1,
    1,
    'Received damaged Smart TV screen',
    'open',
    NULL
),

(
    5,
    2,
    2,
    'Seller delivered wrong shawl color',
    'resolved',
    'Refund processed successfully'
);