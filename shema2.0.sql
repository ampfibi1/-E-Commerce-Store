-- ============================================================
-- ShopHub — shema2.0.sql  (CONSOLIDATED — fresh install only)
--
-- Use this file for a clean DB install. It already includes
-- everything that migrations/001 through 007 add, so you do
-- NOT need to run any migration files on top of this.
--
-- What's bundled:
--   001  customer_addresses table
--   002  orders.zone_id + orders.delivery_fee
--   003  delivery_agents.name/status + delivery_assignments.zone_id/
--        failure_reason/updated_at + delivery_manager seed user
--   004  delivery_zones.created_at
--   005  disputes.seller_response / seller_responded_at / seller_action
--   006  order_items.status_note
--   007  delivery_assignments.seller_id + composite index
-- ============================================================

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

-- Customer saved addresses (migration 001)
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
    seller_id INT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_pct DECIMAL(5,2) NOT NULL,
    max_uses INT,
    uses_count INT DEFAULT 0,
    valid_until DATETIME,
    is_active BOOLEAN DEFAULT TRUE
);

-- Delivery zones table (migration 004 adds created_at)
CREATE TABLE delivery_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    estimated_days INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table (migration 002 adds zone_id + delivery_fee)
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

-- Order items table (migration 006 adds status_note)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    item_status ENUM('pending', 'confirmed', 'shipped', 'delivered') DEFAULT 'pending',
    status_note TEXT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id)
);

-- Delivery agents table (migration 003 adds name + status; user_id nullable)
CREATE TABLE delivery_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL DEFAULT '',
    user_id INT NULL,
    vehicle_type VARCHAR(50),
    phone VARCHAR(20),
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Delivery assignments table
--   migration 003: adds zone_id + failure_reason + updated_at
--   migration 007: adds seller_id + composite index (one row per seller's
--                  shipment within an order)
CREATE TABLE delivery_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    seller_id INT NULL,
    agent_id INT NOT NULL,
    zone_id INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('assigned', 'picked_up', 'in_transit', 'delivered', 'failed'),
    failure_reason TEXT NULL,
    delivery_zone VARCHAR(100),
    INDEX idx_da_order_seller (order_id, seller_id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (agent_id) REFERENCES delivery_agents(id),
    CONSTRAINT fk_delivery_assignments_zone
        FOREIGN KEY (zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL
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

-- Disputes table (migration 005 adds seller_response / seller_responded_at /
-- seller_action so a seller can Accept/Reject and reply to a complaint)
CREATE TABLE disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    seller_id INT NOT NULL,
    order_id INT NOT NULL,
    description TEXT,
    seller_response TEXT NULL,
    seller_responded_at TIMESTAMP NULL DEFAULT NULL,
    seller_action ENUM('none','accepted','rejected') NOT NULL DEFAULT 'none',
    status ENUM('open', 'resolved') DEFAULT 'open',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Announcements table (admin)
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Seed: delivery manager login (from migration 003)
-- Email: delivery@shophub.com   Password: delivery123
-- ============================================================
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
    ('Delivery Manager', 'delivery@shophub.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uSccAt/m2',
     'delivery_manager');
