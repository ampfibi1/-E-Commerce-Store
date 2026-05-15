-- ============================================================
-- Migration 002: add zone_id and delivery_fee to orders
-- ============================================================
-- Purpose: supports the Customer checkout feature which lets
-- the buyer pick a delivery zone and shows the delivery fee
-- in the order summary (final_project.md Role 1 — Checkout).
--
-- Apply with:
--     mysql -u root ecommerce < migrations/002_add_zone_delivery_fee_to_orders.sql
-- ============================================================

USE ecommerce;

ALTER TABLE orders
    ADD COLUMN zone_id      INT              NULL AFTER shipping_address,
    ADD COLUMN delivery_fee DECIMAL(10,2)    NOT NULL DEFAULT 0.00 AFTER discount_amount,
    ADD CONSTRAINT fk_orders_zone
        FOREIGN KEY (zone_id) REFERENCES delivery_zones (id) ON DELETE SET NULL;
