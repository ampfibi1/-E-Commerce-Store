-- ============================================================
-- Migration 007 — per-seller delivery assignments
-- Each row in delivery_assignments now scopes to ONE seller's
-- shipment within an order. A multi-seller order produces
-- multiple delivery_assignments rows (one per seller).
-- ============================================================

ALTER TABLE delivery_assignments
    ADD COLUMN IF NOT EXISTS seller_id INT NULL AFTER order_id,
    ADD INDEX IF NOT EXISTS idx_da_order_seller (order_id, seller_id);

-- Backfill historical rows with the first seller_id from each order.
UPDATE delivery_assignments da
JOIN (
    SELECT order_id, MIN(seller_id) AS sid
    FROM order_items
    GROUP BY order_id
) oi ON oi.order_id = da.order_id
SET da.seller_id = oi.sid
WHERE da.seller_id IS NULL;
