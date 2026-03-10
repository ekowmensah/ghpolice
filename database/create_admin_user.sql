-- ==============================
-- CREATE DEFAULT ADMIN USER
-- ==============================
-- This creates a default admin user for initial system access
-- Username: admin
-- Password: admin123
-- ==============================

USE ghpims;

-- First, ensure we have a Super Admin role
INSERT INTO roles (role_name, description, access_level, can_manage_cases, can_manage_officers, 
                   can_manage_evidence, can_manage_firearms, can_view_intelligence, 
                   can_approve_operations, can_manage_users, can_view_reports, 
                   can_export_data, is_system_admin)
VALUES ('Super Admin', 'Full system access with all permissions', 'National', 
        TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE)
ON DUPLICATE KEY UPDATE role_name = role_name;

-- Get the role ID
SET @role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);

-- Create admin user
-- Password hash for 'admin123' using bcrypt cost 10
INSERT INTO users (
    service_number, 
    first_name, 
    middle_name,
    last_name, 
    rank, 
    role_id, 
    username, 
    password_hash, 
    email, 
    status
) VALUES (
    'ADMIN001', 
    'System', 
    NULL,
    'Administrator', 
    'Administrator', 
    @role_id,
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@ghpims.local', 
    'Active'
) ON DUPLICATE KEY UPDATE username = username;

-- Verify user was created
SELECT 
    u.id,
    u.username,
    u.email,
    u.status,
    r.role_name,
    r.access_level
FROM users u
JOIN roles r ON u.role_id = r.id
WHERE u.username = 'admin';
