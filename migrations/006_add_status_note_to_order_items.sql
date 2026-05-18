-- ============================================================
-- Migration 006 — order_items.status_note
-- Seller's tracking note / status update message for an item.
-- Referenced by order_item_update_status() and the seller's
-- order-detail view.
-- ============================================================

ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS status_note TEXT NULL AFTER item_status;
