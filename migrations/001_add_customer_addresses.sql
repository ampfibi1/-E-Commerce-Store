-- ============================================================
-- Migration 001: add customer_addresses table
-- ============================================================
-- Purpose: supports the Customer "saved shipping addresses" feature
-- (final_project.md Role 1 — "Manage saved shipping addresses:
--  add, edit, delete, set default").
--
-- This table is NOT in shema.sql because it was added by the
-- Customer/Seller MVP (sakib2588) after the canonical schema was
-- agreed. Apply this AFTER importing shema.sql.
--
-- Apply with:
--     mysql -u root ecommerce < migrations/001_add_customer_addresses.sql
-- ============================================================

USE ecommerce;

CREATE TABLE IF NOT EXISTS customer_addresses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT             NOT NULL,
    label           VARCHAR(100)    DEFAULT NULL,
    address_line    TEXT            NOT NULL,
    city            VARCHAR(100)    NOT NULL,
    zip             VARCHAR(20)     DEFAULT NULL,
    is_default      TINYINT(1)      NOT NULL DEFAULT 0,
    CONSTRAINT fk_addresses_customer
        FOREIGN KEY (customer_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_addresses_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
