-- ============================================================
-- Migration 003 — align delivery tables with delivery module
-- Adds columns the module's models expect.
-- Safe to run twice (uses IF NOT EXISTS).
-- ============================================================

-- delivery_agents: module expects name + status ENUM
ALTER TABLE delivery_agents
    ADD COLUMN IF NOT EXISTS name VARCHAR(100) NOT NULL DEFAULT '' AFTER id,
    ADD COLUMN IF NOT EXISTS status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER phone,
    MODIFY COLUMN user_id INT NULL;

-- delivery_assignments: module expects zone_id, failure_reason, updated_at
ALTER TABLE delivery_assignments
    ADD COLUMN IF NOT EXISTS zone_id INT NULL AFTER agent_id,
    ADD COLUMN IF NOT EXISTS failure_reason TEXT NULL AFTER status,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER assigned_at;

-- Add the zone_id FK (only if not already present)
SET @fk_exists := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'delivery_assignments'
      AND CONSTRAINT_NAME = 'fk_delivery_assignments_zone'
);
SET @sql := IF(@fk_exists = 0,
    'ALTER TABLE delivery_assignments ADD CONSTRAINT fk_delivery_assignments_zone FOREIGN KEY (zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Seed a delivery_manager account so the role can actually be logged in
-- Email: delivery@shophub.com   Password: delivery123 (bcrypt hash below)
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
    ('Delivery Manager', 'delivery@shophub.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uSccAt/m2',
     'delivery_manager');
