-- =====================================================
-- Add organization_name field to persons table
-- Purpose: Support organization/agency complainants
-- Date: 2025-12-28
-- =====================================================

-- Add organization_name field
ALTER TABLE persons 
ADD COLUMN organization_name VARCHAR(200) NULL COMMENT 'Organization or agency name (for non-individual persons)' AFTER last_name;

-- Add person_type field to distinguish individuals from organizations
ALTER TABLE persons 
ADD COLUMN person_type ENUM('Individual', 'Organization', 'Government') DEFAULT 'Individual' COMMENT 'Type of person record' AFTER organization_name;

-- Update existing records to be 'Individual' type
UPDATE persons SET person_type = 'Individual' WHERE person_type IS NULL;

-- =====================================================
-- Migration Complete
-- =====================================================
