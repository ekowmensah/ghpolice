-- Migration: Convert suspect_biometrics to person_biometrics
-- This migration makes biometrics person-based instead of suspect-based
-- Run this migration to update the database schema

-- Step 1: Create new person_biometrics table
CREATE TABLE IF NOT EXISTS person_biometrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    person_id INT NOT NULL,
    biometric_type ENUM('Fingerprint', 'Face', 'Iris', 'Palm Print', 'Voice') NOT NULL,
    biometric_data LONGBLOB,
    biometric_template TEXT,
    file_path VARCHAR(500),
    capture_device VARCHAR(100) DEFAULT 'Manual Upload',
    capture_quality ENUM('Poor', 'Fair', 'Good', 'Excellent') NOT NULL,
    captured_by INT NOT NULL,
    captured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_status ENUM('Pending', 'Verified', 'Failed') DEFAULT 'Pending',
    remarks TEXT,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (captured_by) REFERENCES users(id),
    INDEX idx_person_id (person_id),
    INDEX idx_biometric_type (biometric_type),
    INDEX idx_captured_at (captured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Migrate existing data from suspect_biometrics to person_biometrics
INSERT INTO person_biometrics (
    person_id,
    biometric_type,
    biometric_data,
    biometric_template,
    file_path,
    capture_device,
    capture_quality,
    captured_by,
    captured_at,
    verification_status,
    remarks
)
SELECT 
    s.person_id,
    sb.biometric_type,
    sb.biometric_data,
    sb.biometric_template,
    sb.file_path,
    sb.capture_device,
    sb.capture_quality,
    sb.captured_by,
    sb.captured_at,
    sb.verification_status,
    sb.remarks
FROM suspect_biometrics sb
INNER JOIN suspects s ON sb.suspect_id = s.id
WHERE s.person_id IS NOT NULL;

-- Step 3: Backup old table (rename instead of drop)
RENAME TABLE suspect_biometrics TO suspect_biometrics_backup;

-- Step 4: Verify migration
SELECT 
    'Migration Summary' as info,
    (SELECT COUNT(*) FROM person_biometrics) as new_records,
    (SELECT COUNT(*) FROM suspect_biometrics_backup) as old_records;

-- Note: After verifying the migration is successful, you can drop the backup table:
-- DROP TABLE suspect_biometrics_backup;
