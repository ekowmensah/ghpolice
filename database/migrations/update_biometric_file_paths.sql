-- Update biometric file paths from suspects to persons
-- This updates the file_path column to use the new person-based directory structure

-- Update file paths that reference suspects folder to use persons folder
UPDATE person_biometrics 
SET file_path = REPLACE(file_path, 'storage/biometrics/suspects/', 'storage/biometrics/persons/')
WHERE file_path LIKE 'storage/biometrics/suspects/%';

-- Verify the update
SELECT 
    id,
    person_id,
    biometric_type,
    file_path,
    captured_at
FROM person_biometrics
WHERE file_path LIKE 'storage/biometrics/persons/%'
ORDER BY captured_at DESC
LIMIT 20;

-- Note: You will also need to physically move the files on the file system
-- Run this PowerShell command to move the files:
-- robocopy "C:\xampp\htdocs\ghpims\storage\biometrics\suspects" "C:\xampp\htdocs\ghpims\storage\biometrics\persons" /E /MOVE
