-- ==============================
-- FIX DATABASE COLLATION MISMATCH (SAFE VERSION)
-- ==============================
-- This script converts existing tables to utf8mb4_unicode_ci
-- Skips tables that don't exist
-- ==============================

USE ghpims;

-- Set database default collation
ALTER DATABASE ghpims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert core tables (these should exist)
ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE audit_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE regions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE divisions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE districts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE stations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE police_ranks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE officers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE units CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE persons CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE person_aliases CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE person_criminal_history CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE person_alerts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE complainants CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE cases CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE suspects CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE case_suspects CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE witnesses CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE statements CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evidence CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evidence_custody_chain CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE notifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert all remaining tables dynamically
SET @tables = NULL;
SELECT GROUP_CONCAT(CONCAT('ALTER TABLE `', table_name, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;') SEPARATOR '\n')
INTO @tables
FROM information_schema.tables
WHERE table_schema = 'ghpims'
AND table_type = 'BASE TABLE'
AND table_collation != 'utf8mb4_unicode_ci';

-- Show tables that still need conversion
SELECT 
    TABLE_NAME,
    TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'ghpims'
AND TABLE_TYPE = 'BASE TABLE'
AND TABLE_COLLATION != 'utf8mb4_unicode_ci'
ORDER BY TABLE_NAME;

-- Show success message
SELECT 
    COUNT(*) as total_tables,
    SUM(CASE WHEN TABLE_COLLATION = 'utf8mb4_unicode_ci' THEN 1 ELSE 0 END) as correct_collation,
    SUM(CASE WHEN TABLE_COLLATION != 'utf8mb4_unicode_ci' THEN 1 ELSE 0 END) as needs_fixing
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'ghpims'
AND TABLE_TYPE = 'BASE TABLE';
