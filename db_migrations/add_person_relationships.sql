-- Add person relationships table for bidirectional linking
-- Run this migration to enable person-to-person relationship tracking

CREATE TABLE IF NOT EXISTS person_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id_1 INT NOT NULL,
    person_id_2 INT NOT NULL,
    relationship_type VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (person_id_1) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id_2) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_relationship (person_id_1, person_id_2, relationship_type),
    INDEX idx_person_1 (person_id_1),
    INDEX idx_person_2 (person_id_2),
    CHECK (person_id_1 != person_id_2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relationship types with their inverses:
-- Symmetric (same both ways):
--   - Friend, Colleague, Neighbor, Acquaintance, Sibling, Twin, Spouse, Partner
-- 
-- Asymmetric (different inverse):
--   - Parent <-> Child
--   - Grandparent <-> Grandchild
--   - Uncle <-> Nephew/Niece
--   - Aunt <-> Nephew/Niece
--   - Cousin <-> Cousin (symmetric)
--   - Employer <-> Employee
--   - Guardian <-> Ward
