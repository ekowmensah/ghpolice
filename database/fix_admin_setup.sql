-- ==============================
-- FIX ADMIN SETUP
-- ==============================
-- This fixes the Super Admin role and creates the admin user
-- Password: admin123
-- ==============================

USE ghpims;

-- Fix Super Admin role permissions (currently all FALSE)
UPDATE roles 
SET access_level = 'National',
    can_manage_cases = TRUE,
    can_manage_officers = TRUE,
    can_manage_evidence = TRUE,
    can_manage_firearms = TRUE,
    can_view_intelligence = TRUE,
    can_approve_operations = TRUE,
    can_manage_users = TRUE,
    can_view_reports = TRUE,
    can_export_data = TRUE,
    is_system_admin = TRUE
WHERE role_name = 'Super Admin';

-- Delete existing admin user if exists
DELETE FROM users WHERE username = 'admin';

-- Create admin user with NEW password hash
-- Password: admin123
-- Hash generated from test: $2y$10$UvULeKrXhYfFBHnhkqHpr.uGEdroyQbLZAOlQIHsCMxPvqCzdX0Ne
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
    (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1),
    'admin', 
    '$2y$10$UvULeKrXhYfFBHnhkqHpr.uGEdroyQbLZAOlQIHsCMxPvqCzdX0Ne',
    'admin@ghpims.local', 
    'Active'
);

-- Verify everything is correct
SELECT '=== SUPER ADMIN ROLE ===' as '';
SELECT * FROM roles WHERE role_name = 'Super Admin';

SELECT '=== ADMIN USER ===' as '';
SELECT 
    u.id,
    u.username,
    u.email,
    u.status,
    r.role_name,
    r.access_level,
    r.is_system_admin
FROM users u
JOIN roles r ON u.role_id = r.id
WHERE u.username = 'admin';
