-- Migration 004 — delivery_zones.created_at
-- The delivery module reads created_at; the original schema didn't include it.

ALTER TABLE delivery_zones
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
