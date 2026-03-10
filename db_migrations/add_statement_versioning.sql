-- Add statement versioning and status tracking
-- Run this migration to add versioning support to statements

ALTER TABLE statements 
ADD COLUMN status ENUM('active', 'cancelled', 'superseded') DEFAULT 'active' AFTER statement_text,
ADD COLUMN parent_statement_id INT NULL AFTER status,
ADD COLUMN version INT DEFAULT 1 AFTER parent_statement_id,
ADD COLUMN cancelled_at TIMESTAMP NULL AFTER version,
ADD COLUMN cancelled_by INT NULL AFTER cancelled_at,
ADD COLUMN cancellation_reason TEXT NULL AFTER cancelled_by,
ADD CONSTRAINT fk_statement_parent FOREIGN KEY (parent_statement_id) REFERENCES statements(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_statement_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
ADD INDEX idx_statement_status (status),
ADD INDEX idx_parent_statement (parent_statement_id);
