-- Fix investigation management tables schema
-- Run this migration to align with the InvestigationService expectations

-- Fix case_investigation_checklist table
ALTER TABLE case_investigation_checklist 
ADD COLUMN IF NOT EXISTS item_description VARCHAR(200) AFTER checklist_item,
ADD COLUMN IF NOT EXISTS item_order INT DEFAULT 0 AFTER item_category,
ADD COLUMN IF NOT EXISTS completed_at DATETIME AFTER completed_date;

-- Update existing data if needed
UPDATE case_investigation_checklist 
SET item_description = checklist_item 
WHERE item_description IS NULL OR item_description = '';

-- Fix case_investigation_tasks table - add missing columns
ALTER TABLE case_investigation_tasks 
ADD COLUMN IF NOT EXISTS task_type ENUM('Interview','Evidence Collection','Document Review','Follow-up','Court Preparation','Other') DEFAULT 'Other' AFTER task_description,
ADD COLUMN IF NOT EXISTS assigned_by INT AFTER assigned_to,
ADD COLUMN IF NOT EXISTS created_by INT AFTER assigned_by,
ADD COLUMN IF NOT EXISTS completed_at DATETIME AFTER completion_date,
ADD COLUMN IF NOT EXISTS completed_by INT AFTER completion_notes;

-- Add foreign key for created_by if not exists
ALTER TABLE case_investigation_tasks
ADD CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Fix investigation_milestones table - add case-specific milestone tracking
CREATE TABLE IF NOT EXISTS case_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    milestone_title VARCHAR(200) NOT NULL,
    milestone_description TEXT,
    target_date DATE,
    achieved_date DATETIME,
    is_achieved BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_case_milestones_case (case_id),
    INDEX idx_case_milestones_achieved (is_achieved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fix case_investigation_timeline table - add missing columns
ALTER TABLE case_investigation_timeline
ADD COLUMN IF NOT EXISTS event_type VARCHAR(100) AFTER milestone_id,
ADD COLUMN IF NOT EXISTS event_description TEXT AFTER activity_description,
ADD COLUMN IF NOT EXISTS event_date DATETIME AFTER activity_date,
ADD COLUMN IF NOT EXISTS recorded_by INT AFTER completed_by;

-- Update existing data
UPDATE case_investigation_timeline 
SET event_type = activity_type,
    event_description = activity_description,
    event_date = activity_date,
    recorded_by = completed_by
WHERE event_type IS NULL;

-- Initialize default checklist items for existing cases without checklist
INSERT INTO case_investigation_checklist (case_id, checklist_item, item_description, item_category, item_order)
SELECT 
    c.id,
    'Initial complaint recorded',
    'Initial complaint recorded',
    'Initial Response',
    1
FROM cases c
LEFT JOIN case_investigation_checklist cic ON c.id = cic.case_id
WHERE cic.id IS NULL
GROUP BY c.id;

-- Add more default checklist items
INSERT INTO case_investigation_checklist (case_id, checklist_item, item_description, item_category, item_order)
SELECT c.id, item, item, category, ord
FROM cases c
CROSS JOIN (
    SELECT 'Scene visited and documented' as item, 'Initial Response' as category, 2 as ord
    UNION ALL SELECT 'Witnesses identified', 'Witnesses', 3
    UNION ALL SELECT 'Statements recorded', 'Witnesses', 4
    UNION ALL SELECT 'Evidence collected', 'Evidence', 5
    UNION ALL SELECT 'Suspects identified', 'Suspects', 6
    UNION ALL SELECT 'Forensic analysis requested', 'Evidence', 7
    UNION ALL SELECT 'Investigation report prepared', 'Documentation', 8
    UNION ALL SELECT 'Case file reviewed', 'Documentation', 9
    UNION ALL SELECT 'Prosecution recommendation made', 'Case Closure', 10
) items
WHERE NOT EXISTS (
    SELECT 1 FROM case_investigation_checklist cic2 
    WHERE cic2.case_id = c.id AND cic2.item_description = items.item
);
