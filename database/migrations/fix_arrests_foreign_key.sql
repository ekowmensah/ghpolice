-- Fix arrests table foreign key constraint
-- The arresting_officer_id should reference officers.id, not users.id

-- Drop the incorrect foreign key constraint
ALTER TABLE `arrests` DROP FOREIGN KEY `arrests_ibfk_3`;

-- Add the correct foreign key constraint
ALTER TABLE `arrests` 
ADD CONSTRAINT `arrests_ibfk_3` 
FOREIGN KEY (`arresting_officer_id`) 
REFERENCES `officers` (`id`) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

-- Verify the change
SHOW CREATE TABLE `arrests`;
