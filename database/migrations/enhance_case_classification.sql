-- =====================================================
-- GHPIMS Case Classification Enhancement Migration
-- Purpose: Add enhanced case classification fields
-- Date: 2025-12-28
-- Based on: Ghana Police Service operational requirements
-- =====================================================

-- Step 1: Add new classification fields to cases table
ALTER TABLE cases 
ADD COLUMN case_origin VARCHAR(50) NULL COMMENT 'How the case entered the system' AFTER case_type,
ADD COLUMN complainant_present ENUM('Yes', 'No') DEFAULT 'Yes' COMMENT 'Whether a complainant exists',
ADD COLUMN arrest_made ENUM('Yes', 'No', 'Pending') DEFAULT 'No' COMMENT 'Whether arrest has been made',
ADD COLUMN specialized_unit VARCHAR(50) NULL COMMENT 'DOVVSU, CID, Cybercrime, etc.',
ADD COLUMN case_category ENUM(
    'General Crime',
    'Domestic Violence',
    'Sexual Offence',
    'Cybercrime',
    'Drug Offence',
    'Organized Crime',
    'Traffic Offence',
    'Administrative',
    'Armed Robbery',
    'Fraud',
    'Theft',
    'Assault',
    'Murder',
    'Other'
) NULL COMMENT 'Broad category of the case',
ADD COLUMN is_dovvsu_case TINYINT(1) DEFAULT 0 COMMENT 'Flag for DOVVSU cases requiring special handling',
ADD COLUMN is_intelligence_led TINYINT(1) DEFAULT 0 COMMENT 'Flag for intelligence-led cases',
ADD COLUMN is_exhibit_based TINYINT(1) DEFAULT 0 COMMENT 'Flag for cases initiated by evidence seizure';

-- Step 2: Create index for performance
CREATE INDEX idx_case_origin ON cases(case_origin);
CREATE INDEX idx_specialized_unit ON cases(specialized_unit);
CREATE INDEX idx_case_category ON cases(case_category);
CREATE INDEX idx_dovvsu_cases ON cases(is_dovvsu_case);

-- Step 3: Populate new fields from existing data
UPDATE cases 
SET case_origin = CASE 
    WHEN case_type = 'Complaint' THEN 'Complaint'
    WHEN case_type = 'Police Initiated' THEN 'Arrest'
    ELSE 'Complaint'
END;

UPDATE cases 
SET complainant_present = CASE 
    WHEN complainant_id IS NOT NULL THEN 'Yes'
    ELSE 'No'
END;

-- Step 4: Create specialized_units lookup table
CREATE TABLE IF NOT EXISTS specialized_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_code VARCHAR(20) NOT NULL UNIQUE,
    unit_name VARCHAR(100) NOT NULL,
    description TEXT,
    requires_special_handling TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 5: Insert default specialized units
INSERT INTO specialized_units (unit_code, unit_name, description, requires_special_handling) VALUES
('DOVVSU', 'Domestic Violence and Victim Support Unit', 'Handles domestic violence, sexual offences, child abuse', 1),
('CID', 'Criminal Investigation Department', 'Major crime investigations', 0),
('CYBER', 'Cybercrime Unit', 'Cybercrime and digital forensics', 0),
('NARCO', 'Narcotics Control', 'Drug-related offences', 0),
('SWAT', 'Special Weapons and Tactics', 'High-risk operations', 0),
('INTEL', 'Intelligence Unit', 'Intelligence gathering and analysis', 1),
('TRAFFIC', 'Motor Traffic and Transport Department', 'Traffic offences and accidents', 0),
('FRAUD', 'Fraud Unit', 'Financial crimes and fraud', 0),
('HOMICIDE', 'Homicide Unit', 'Murder and manslaughter cases', 0),
('ROBBERY', 'Armed Robbery Unit', 'Armed robbery investigations', 0);

-- Step 6: Create case_origin_types lookup table for consistency
CREATE TABLE IF NOT EXISTS case_origin_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_code VARCHAR(30) NOT NULL UNIQUE,
    origin_name VARCHAR(100) NOT NULL,
    description TEXT,
    requires_complainant TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 7: Insert case origin types
INSERT INTO case_origin_types (origin_code, origin_name, description, requires_complainant) VALUES
('Complaint', 'Complaint Case', 'Civilian reports an offence against another person', 1),
('Arrest', 'Arrest Case', 'Police arrest suspect before complaint is made', 0),
('Intelligence', 'Intelligence-Led Case', 'Case initiated based on intelligence reports', 0),
('DOVVSU', 'DOVVSU Case', 'Domestic violence or victim support case', 1),
('Suspect-Only', 'Suspect-Only Case', 'Suspect arrested but no complainant comes forward', 0),
('Exhibit-Based', 'Exhibit-Based Case', 'Case built primarily on physical/digital evidence', 0),
('Transferred', 'Transferred Case', 'Case originated elsewhere and transferred', 1),
('Court-Ordered', 'Court-Ordered Case', 'Case initiated by court directive', 0),
('Administrative', 'Administrative Case', 'Regulatory enforcement, not criminal', 0);

-- Step 8: Create audit log for case classification changes
CREATE TABLE IF NOT EXISTS case_classification_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    field_changed VARCHAR(50) NOT NULL,
    old_value VARCHAR(100),
    new_value VARCHAR(100),
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason TEXT,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_case_classification_history ON case_classification_history(case_id, changed_at);

-- Step 9: Create view for enhanced case reporting
CREATE OR REPLACE VIEW vw_cases_enhanced AS
SELECT 
    c.*,
    cot.origin_name,
    cot.description as origin_description,
    su.unit_name as specialized_unit_name,
    su.requires_special_handling,
    CONCAT_WS(' ', comp.first_name, comp.middle_name, comp.last_name) as complainant_name,
    comp.contact as complainant_contact,
    s.station_name,
    d.district_name,
    div.division_name,
    r.region_name,
    CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as created_by_name,
    -- Count related records
    (SELECT COUNT(*) FROM case_suspects WHERE case_id = c.id) as suspect_count,
    (SELECT COUNT(*) FROM case_witnesses WHERE case_id = c.id) as witness_count,
    (SELECT COUNT(*) FROM case_assignments WHERE case_id = c.id AND status = 'Active') as active_officer_count,
    (SELECT COUNT(*) FROM case_crimes WHERE case_id = c.id) as crime_count
FROM cases c
LEFT JOIN case_origin_types cot ON c.case_origin = cot.origin_code
LEFT JOIN specialized_units su ON c.specialized_unit = su.unit_code
LEFT JOIN persons comp ON c.complainant_id = comp.id
LEFT JOIN stations s ON c.station_id = s.id
LEFT JOIN districts d ON c.district_id = d.id
LEFT JOIN divisions div ON c.division_id = div.id
LEFT JOIN regions r ON c.region_id = r.id
LEFT JOIN users u ON c.created_by = u.id;

-- Step 10: Add comments to document the schema
ALTER TABLE cases 
MODIFY COLUMN case_type ENUM('Complaint','Police Initiated') NOT NULL 
    COMMENT 'Legacy field - use case_origin for new classifications';

-- =====================================================
-- Migration Complete
-- =====================================================
-- Next Steps:
-- 1. Run this migration on your database
-- 2. Update case creation forms to use new fields
-- 3. Update reports to leverage enhanced classification
-- 4. Train users on new case types
-- =====================================================
