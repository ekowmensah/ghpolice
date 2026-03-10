-- Add 'On Bail' status to suspects table current_status enum
-- Run this migration to fix the status update issue

ALTER TABLE `suspects` 
MODIFY COLUMN `current_status` ENUM(
    'Suspect',
    'Arrested',
    'On Bail',
    'Charged',
    'Discharged',
    'Acquitted',
    'Convicted',
    'Released',
    'Deceased'
) DEFAULT 'Suspect';
