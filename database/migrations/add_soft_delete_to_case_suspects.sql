-- Add soft delete fields to case_suspects table
-- This allows us to mark suspects as removed while keeping the history

ALTER TABLE `case_suspects` 
ADD COLUMN `removed_at` DATETIME NULL AFTER `added_date`,
ADD COLUMN `removed_by` INT(11) NULL AFTER `removed_at`,
ADD COLUMN `removal_reason` TEXT NULL AFTER `removed_by`,
ADD CONSTRAINT `fk_case_suspects_removed_by` FOREIGN KEY (`removed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
