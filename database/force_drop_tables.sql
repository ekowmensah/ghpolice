-- ==============================
-- FORCE DROP ALL TABLES IN GHPIMS DATABASE
-- ==============================
-- This script drops all tables individually to avoid directory lock issues
-- ==============================

USE ghpims;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables in reverse dependency order
DROP TABLE IF EXISTS case_investigation_tasks;
DROP TABLE IF EXISTS case_investigation_timeline;
DROP TABLE IF EXISTS case_investigation_checklist;
DROP TABLE IF EXISTS investigation_milestones;
DROP TABLE IF EXISTS missing_persons;
DROP TABLE IF EXISTS operation_officers;
DROP TABLE IF EXISTS operations;
DROP TABLE IF EXISTS public_complaint_actions;
DROP TABLE IF EXISTS public_complaints;
DROP TABLE IF EXISTS informant_intelligence;
DROP TABLE IF EXISTS informants;
DROP TABLE IF EXISTS exhibit_movements;
DROP TABLE IF EXISTS exhibits;
DROP TABLE IF EXISTS firearms_assignments;
DROP TABLE IF EXISTS firearms_registry;
DROP TABLE IF EXISTS incident_reports;
DROP TABLE IF EXISTS patrol_logs;
DROP TABLE IF EXISTS duty_roster;
DROP TABLE IF EXISTS intelligence_bulletin_recipients;
DROP TABLE IF EXISTS intelligence_bulletins;
DROP TABLE IF EXISTS public_intelligence_tips;
DROP TABLE IF EXISTS threat_assessments;
DROP TABLE IF EXISTS surveillance_operations;
DROP TABLE IF EXISTS intelligence_reports;
DROP TABLE IF EXISTS case_crimes;
DROP TABLE IF EXISTS crime_categories;
DROP TABLE IF EXISTS case_documents;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS suspect_biometrics;
DROP TABLE IF EXISTS officer_biometrics;
DROP TABLE IF EXISTS case_updates;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS asset_movements;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS evidence_custody_chain;
DROP TABLE IF EXISTS evidence;
DROP TABLE IF EXISTS case_referrals;
DROP TABLE IF EXISTS custody_records;
DROP TABLE IF EXISTS bail_records;
DROP TABLE IF EXISTS statements;
DROP TABLE IF EXISTS witnesses;
DROP TABLE IF EXISTS court_proceedings;
DROP TABLE IF EXISTS charges;
DROP TABLE IF EXISTS arrests;
DROP TABLE IF EXISTS case_suspects;
DROP TABLE IF EXISTS suspect_status_history;
DROP TABLE IF EXISTS suspects;
DROP TABLE IF EXISTS case_status_history;
DROP TABLE IF EXISTS case_assignments;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS complainants;
DROP TABLE IF EXISTS person_alerts;
DROP TABLE IF EXISTS person_criminal_history;
DROP TABLE IF EXISTS person_aliases;
DROP TABLE IF EXISTS persons;
DROP TABLE IF EXISTS unit_officer_assignments;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS officer_promotions;
DROP TABLE IF EXISTS officer_postings;
DROP TABLE IF EXISTS officers;
DROP TABLE IF EXISTS police_ranks;
DROP TABLE IF EXISTS stations;
DROP TABLE IF EXISTS districts;
DROP TABLE IF EXISTS divisions;
DROP TABLE IF EXISTS regions;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show remaining tables (should be empty)
SHOW TABLES;
