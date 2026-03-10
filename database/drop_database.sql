-- ==============================
-- DROP AND RECREATE GHPIMS DATABASE
-- ==============================
-- WARNING: This will delete ALL data in the database!
-- Use this script to clean up before importing db_improved.sql
-- ==============================

-- Drop database if exists
DROP DATABASE IF EXISTS ghpims;

-- Create fresh database
CREATE DATABASE ghpims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE ghpims;

-- Database is now ready for importing db_improved.sql
