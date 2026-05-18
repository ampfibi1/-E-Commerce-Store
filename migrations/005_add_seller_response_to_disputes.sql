-- ============================================================
-- Migration 005 — add seller response fields to disputes
-- Lets sellers reply to customer disputes and optionally mark
-- them resolved from the seller panel.
-- Safe to run twice (uses IF NOT EXISTS).
-- ============================================================

ALTER TABLE disputes
    ADD COLUMN IF NOT EXISTS seller_response TEXT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS seller_responded_at TIMESTAMP NULL DEFAULT NULL AFTER seller_response,
    ADD COLUMN IF NOT EXISTS seller_action ENUM('none','accepted','rejected') NOT NULL DEFAULT 'none' AFTER seller_responded_at;
