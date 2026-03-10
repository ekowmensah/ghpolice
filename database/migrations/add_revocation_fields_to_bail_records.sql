-- Add revocation fields to bail_records table
-- Run this migration to fix the revoke bail functionality

ALTER TABLE `bail_records` 
ADD COLUMN `revocation_reason` TEXT NULL AFTER `bail_conditions`,
ADD COLUMN `revoked_by` INT(11) NULL AFTER `revocation_reason`,
ADD COLUMN `revoked_date` DATETIME NULL AFTER `revoked_by`,
ADD CONSTRAINT `fk_bail_revoked_by` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
