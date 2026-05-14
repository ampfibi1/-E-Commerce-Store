-- ============================================================
-- ecommerce_db  –  Full schema + seed data
-- PHP 8.2 / LAMP stack  |  Engine: InnoDB  |  Charset: utf8mb4
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Database
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS ecommerce_db
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

-- ------------------------------------------------------------
-- 1. users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL,
    password_hash   VARCHAR(255)    NOT NULL,
    phone           VARCHAR(20)     DEFAULT NULL,
    role            ENUM('customer','seller','delivery_manager','admin') NOT NULL DEFAULT 'customer',
    profile_pic     VARCHAR(255)    DEFAULT NULL,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. sellers
-- ------------------------------------------------------------
DROP TABLE IF EXISTS sellers;
CREATE TABLE sellers (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT             NOT NULL,
    shop_name           VARCHAR(150)    NOT NULL,
    shop_description    TEXT            DEFAULT NULL,
    shop_logo_path      VARCHAR(255)    DEFAULT NULL,
    address             TEXT            DEFAULT NULL,
    is_approved         TINYINT(1)      NOT NULL DEFAULT 0,
    commission_rate     DECIMAL(5,2)    NOT NULL DEFAULT 10.00,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sellers_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. categories
-- ------------------------------------------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    parent_id       INT             DEFAULT NULL,
    name            VARCHAR(100)    NOT NULL,
    description     TEXT            DEFAULT NULL,
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. products
-- ------------------------------------------------------------
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    seller_id           INT             NOT NULL,
    category_id         INT             NOT NULL,
    name                VARCHAR(200)    NOT NULL,
    description         TEXT            DEFAULT NULL,
    price               DECIMAL(10,2)   NOT NULL,
    stock_qty           INT             NOT NULL DEFAULT 0,
    primary_image_path  VARCHAR(255)    DEFAULT NULL,
    is_available        TINYINT(1)      NOT NULL DEFAULT 1,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_seller   FOREIGN KEY (seller_id)   REFERENCES sellers    (id) ON DELETE CASCADE,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
    INDEX idx_products_seller   (seller_id),
    INDEX idx_products_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. product_images
-- ------------------------------------------------------------
DROP TABLE IF EXISTS product_images;
CREATE TABLE product_images (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    product_id      INT             NOT NULL,
    image_path      VARCHAR(255)    NOT NULL,
    display_order   INT             NOT NULL DEFAULT 0,
    CONSTRAINT fk_pimages_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    INDEX idx_pimages_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. coupons
-- ------------------------------------------------------------
DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    seller_id       INT             NOT NULL,
    code            VARCHAR(50)     NOT NULL,
    discount_pct    DECIMAL(5,2)    NOT NULL,
    max_uses        INT             NOT NULL DEFAULT 1,
    uses_count      INT             NOT NULL DEFAULT 0,
    valid_until     DATE            NOT NULL,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    CONSTRAINT fk_coupons_seller FOREIGN KEY (seller_id) REFERENCES sellers (id) ON DELETE CASCADE,
    UNIQUE KEY uq_coupon_seller_code (seller_id, code),
    INDEX idx_coupons_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. customer_addresses
-- ------------------------------------------------------------
DROP TABLE IF EXISTS customer_addresses;
CREATE TABLE customer_addresses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT             NOT NULL,
    label           VARCHAR(100)    DEFAULT NULL,
    address_line    TEXT            NOT NULL,
    city            VARCHAR(100)    NOT NULL,
    zip             VARCHAR(20)     DEFAULT NULL,
    is_default      TINYINT(1)      NOT NULL DEFAULT 0,
    CONSTRAINT fk_addresses_customer FOREIGN KEY (customer_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_addresses_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. delivery_zones
-- ------------------------------------------------------------
DROP TABLE IF EXISTS delivery_zones;
CREATE TABLE delivery_zones (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    zone_name       VARCHAR(100)    NOT NULL,
    delivery_fee    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    estimated_days  INT             NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. orders
-- ------------------------------------------------------------
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    customer_id         INT             NOT NULL,
    shipping_address    TEXT            NOT NULL,
    zone_id             INT             NOT NULL,
    payment_method      ENUM('cash_on_delivery','card') NOT NULL DEFAULT 'cash_on_delivery',
    subtotal            DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    discount_amount     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    delivery_fee        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    total_amount        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status              ENUM('pending','confirmed','processing','shipped','delivered','cancelled','return_requested','returned') NOT NULL DEFAULT 'pending',
    coupon_id           INT             DEFAULT NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES users          (id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_zone     FOREIGN KEY (zone_id)     REFERENCES delivery_zones (id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_coupon   FOREIGN KEY (coupon_id)   REFERENCES coupons        (id) ON DELETE SET NULL,
    INDEX idx_orders_customer (customer_id),
    INDEX idx_orders_status   (status),
    INDEX idx_orders_zone     (zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. order_items
-- ------------------------------------------------------------
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT             NOT NULL,
    product_id      INT             NOT NULL,
    seller_id       INT             NOT NULL,
    quantity        INT             NOT NULL DEFAULT 1,
    unit_price      DECIMAL(10,2)   NOT NULL,
    item_status     ENUM('pending','confirmed','shipped','delivered') NOT NULL DEFAULT 'pending',
    tracking_note   VARCHAR(255)    DEFAULT NULL,
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders   (id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
    CONSTRAINT fk_oi_seller  FOREIGN KEY (seller_id)  REFERENCES sellers  (id) ON DELETE RESTRICT,
    INDEX idx_oi_order   (order_id),
    INDEX idx_oi_product (product_id),
    INDEX idx_oi_seller  (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. delivery_agents
-- ------------------------------------------------------------
DROP TABLE IF EXISTS delivery_agents;
CREATE TABLE delivery_agents (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT             NOT NULL,
    vehicle_type    VARCHAR(100)    DEFAULT NULL,
    phone           VARCHAR(20)     DEFAULT NULL,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dagents_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_dagents_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. delivery_assignments
-- ------------------------------------------------------------
DROP TABLE IF EXISTS delivery_assignments;
CREATE TABLE delivery_assignments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT             NOT NULL,
    agent_id        INT             NOT NULL,
    assigned_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('assigned','picked_up','in_transit','delivered','failed') NOT NULL DEFAULT 'assigned',
    delivery_zone   VARCHAR(100)    DEFAULT NULL,
    CONSTRAINT fk_da_order FOREIGN KEY (order_id)  REFERENCES orders           (id) ON DELETE CASCADE,
    CONSTRAINT fk_da_agent FOREIGN KEY (agent_id)  REFERENCES delivery_agents  (id) ON DELETE RESTRICT,
    INDEX idx_da_order (order_id),
    INDEX idx_da_agent (agent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. reviews
-- ------------------------------------------------------------
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    product_id      INT             NOT NULL,
    order_id        INT             NOT NULL,
    customer_id     INT             NOT NULL,
    rating          TINYINT         NOT NULL,
    review_text     TEXT            DEFAULT NULL,
    seller_reply    TEXT            DEFAULT NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product  FOREIGN KEY (product_id)  REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_order    FOREIGN KEY (order_id)    REFERENCES orders   (id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES users    (id) ON DELETE CASCADE,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    UNIQUE KEY uq_review_order_product (order_id, product_id, customer_id),
    INDEX idx_reviews_product  (product_id),
    INDEX idx_reviews_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. wishlists
-- ------------------------------------------------------------
DROP TABLE IF EXISTS wishlists;
CREATE TABLE wishlists (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT             NOT NULL,
    product_id      INT             NOT NULL,
    added_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wish_customer FOREIGN KEY (customer_id) REFERENCES users    (id) ON DELETE CASCADE,
    CONSTRAINT fk_wish_product  FOREIGN KEY (product_id)  REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_wish (customer_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 15. return_requests
-- ------------------------------------------------------------
DROP TABLE IF EXISTS return_requests;
CREATE TABLE return_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT             NOT NULL,
    order_item_id   INT             NOT NULL,
    customer_id     INT             NOT NULL,
    reason          TEXT            DEFAULT NULL,
    status          ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rr_order     FOREIGN KEY (order_id)      REFERENCES orders      (id) ON DELETE CASCADE,
    CONSTRAINT fk_rr_item      FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE,
    CONSTRAINT fk_rr_customer  FOREIGN KEY (customer_id)   REFERENCES users       (id) ON DELETE CASCADE,
    INDEX idx_rr_order    (order_id),
    INDEX idx_rr_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 16. disputes
-- ------------------------------------------------------------
DROP TABLE IF EXISTS disputes;
CREATE TABLE disputes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT             NOT NULL,
    seller_id       INT             NOT NULL,
    order_id        INT             NOT NULL,
    description     TEXT            DEFAULT NULL,
    status          ENUM('open','resolved') NOT NULL DEFAULT 'open',
    admin_note      TEXT            DEFAULT NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_disp_customer FOREIGN KEY (customer_id) REFERENCES users   (id) ON DELETE CASCADE,
    CONSTRAINT fk_disp_seller   FOREIGN KEY (seller_id)   REFERENCES sellers (id) ON DELETE CASCADE,
    CONSTRAINT fk_disp_order    FOREIGN KEY (order_id)    REFERENCES orders  (id) ON DELETE CASCADE,
    INDEX idx_disp_customer (customer_id),
    INDEX idx_disp_seller   (seller_id),
    INDEX idx_disp_order    (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- ------------------------------------------------------------
-- Admin user
-- Password for this hash is: admin123
-- Hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO users (name, email, password_hash, phone, role, is_active) VALUES
('Admin', 'admin@store.com', '$2y$10$TKh8H1.PfKkqHCBncqHNT.apkdqmHSZkXkfHkbj5pCHrpJv.JFoJm', '01700000000', 'admin', 1);

-- ------------------------------------------------------------
-- Categories (parent categories first, then children)
-- ------------------------------------------------------------
INSERT INTO categories (id, parent_id, name, description) VALUES
(1, NULL, 'Electronics', 'Electronic devices and accessories'),
(2, NULL, 'Clothing',    'Apparel and fashion items'),
(3,    1, 'Mobile Phones', 'Smartphones and accessories'),
(4,    1, 'Laptops',     'Laptops, notebooks and accessories');

-- ------------------------------------------------------------
-- Delivery zones
-- ------------------------------------------------------------
INSERT INTO delivery_zones (zone_name, delivery_fee, estimated_days) VALUES
('Dhaka',          60.00, 1),
('Chittagong',     80.00, 2),
('Outside Dhaka', 100.00, 4);

SET FOREIGN_KEY_CHECKS = 1;
