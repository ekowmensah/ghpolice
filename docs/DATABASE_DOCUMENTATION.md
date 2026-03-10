# Ghana Police Integrated Management System (GHPIMS)
## Database Documentation

---

## Table of Contents
1. [Overview](#overview)
2. [Database Files](#database-files)
3. [Schema Structure](#schema-structure)
4. [Key Improvements](#key-improvements)
5. [Table Descriptions](#table-descriptions)
6. [Relationships](#relationships)
7. [Security Features](#security-features)
8. [Performance Optimizations](#performance-optimizations)
9. [Usage Instructions](#usage-instructions)

---

## Overview

GHPIMS is a comprehensive case management system for the Ghana Police Service that manages:
- **Case lifecycle** from complaint/initiation to closure
- **Suspect tracking** with complete status history
- **Evidence management** with chain of custody
- **Court proceedings** and legal documentation
- **Hierarchical police structure** (Region → Division → District → Station)
- **Role-based access control** with audit trails
- **Notifications and alerts** for critical events

---

## Database Files

### 1. `db.sql` (Original)
The initial database schema with basic functionality.

### 2. `db_improved.sql` (Recommended for New Installations)
Complete enhanced schema with all improvements including:
- Performance indexes
- Security features
- Additional tables for court proceedings, notifications, and chain of custody
- Enhanced data validation
- Audit trail improvements

### 3. `migration.sql` (For Existing Databases)
Migration script to upgrade from `db.sql` to the improved schema without data loss.

---

## Schema Structure

### Core Modules

#### 1. **User Management**
- `roles` - User role definitions
- `users` - Police officers and system users
- `user_sessions` - Active login sessions

#### 2. **Officers Management**
- `police_ranks` - Official Ghana Police Service ranks
- `officers` - Complete officer database with personal details
- `officer_postings` - Posting and transfer history
- `officer_promotions` - Promotion records
- `officer_leave_records` - Leave applications and approvals
- `officer_disciplinary_records` - Disciplinary actions
- `officer_training` - Training and certifications
- `officer_commendations` - Awards and commendations

#### 3. **Police Structure**
- `regions` - Top-level administrative regions
- `divisions` - Divisions within regions
- `districts` - Districts within divisions
- `stations` - Police stations within districts

#### 3.5. **Units / Departments**
- `unit_types` - Types of specialized units (CID, Traffic, SWAT, etc.)
- `units` - Specific unit instances at stations/districts/divisions/regions
- `unit_officer_assignments` - Officer assignments to units

#### 4. **Case Management**
- `cases` - Main case records
- `case_assignments` - Officer assignments to cases
- `case_updates` - Case diary/progress notes
- `case_status_history` - Audit trail of status changes
- `case_referrals` - Case escalations between levels

#### 5. **People Management**
- `complainants` - People filing complaints
- `suspects` - Persons of interest/accused
- `witnesses` - Case witnesses
- `case_suspects` - Links suspects to cases
- `case_witnesses` - Links witnesses to cases

#### 6. **Investigation**
- `arrests` - Arrest records
- `charges` - Criminal charges filed
- `statements` - Recorded statements
- `suspect_status_history` - Suspect status audit trail

#### 7. **Legal Proceedings**
- `court_proceedings` - Court hearings and outcomes
- `bail_records` - Bail applications and decisions
- `custody_records` - Custody tracking

#### 8. **Evidence & Assets**
- `evidence` - Digital and physical evidence
- `evidence_custody_chain` - Chain of custody tracking
- `assets` - Police assets and seized items
- `asset_movements` - Asset transfer history

#### 9. **System**
- `notifications` - User alerts and notifications
- `audit_logs` - Complete system activity log

---

## Key Improvements

### 1. **Performance Enhancements**
- **50+ strategic indexes** on foreign keys and frequently queried columns
- Optimized for hierarchical queries (station → district → division → region)
- Indexed timestamps for date-range queries
- Composite indexes for common join patterns

### 2. **Security Features**
- Password hashing support (VARCHAR(255) for bcrypt/argon2)
- Session management with token-based authentication
- Failed login attempt tracking
- Account lockout mechanism
- Two-factor authentication support
- IP address and user agent logging
- Comprehensive audit trail with JSON old/new values

### 3. **Data Integrity**
- Foreign key constraints with CASCADE deletes where appropriate
- UNIQUE constraints to prevent duplicates
- NOT NULL constraints on critical fields
- ENUM types for controlled vocabularies
- Default values for status fields

### 4. **New Critical Features**
- **Court Proceedings Tracking** - Complete court case management
- **Notifications System** - Real-time alerts for users
- **Case Assignments** - Formal officer assignment tracking
- **Evidence Chain of Custody** - Legal compliance for evidence handling
- **Case Status History** - Audit trail for case status changes
- **Enhanced Audit Logs** - JSON-based change tracking

### 5. **Enhanced Data Model**
- Email fields for password recovery
- National ID/Ghana Card numbers for identification
- Contact alternatives for reliability
- Priority levels for cases
- Investigation deadlines
- File metadata (size, MIME type, hash) for evidence
- Custody status tracking
- Bail amount tracking

---

## Table Descriptions

### Users & Authentication

#### `users`
Stores police officers and system users with hierarchical assignments.

**Key Fields:**
- `service_number` - Unique police service number
- `role_id` - Links to role (Super Admin, Regional Commander, etc.)
- `station_id`, `district_id`, `division_id`, `region_id` - Hierarchical assignment
- `failed_login_attempts` - Brute force protection
- `account_locked_until` - Temporary lockout timestamp
- `two_factor_enabled` - 2FA status

#### `user_sessions`
Tracks active login sessions for security and auditing.

**Key Fields:**
- `session_token` - Unique session identifier
- `ip_address` - Login IP for security
- `last_activity` - Auto-updates on activity
- `logout_time` - NULL for active sessions

### Units / Departments

#### `unit_types`
Pre-defined types of specialized police units.

**Key Fields:**
- `unit_type_name` - Name of unit type (e.g., "CID", "Traffic Unit", "SWAT")
- `description` - Purpose and function of the unit type

**Pre-loaded Unit Types (20):**
- **Investigation:** CID, Cybercrime Unit, Financial Crimes Unit, Intelligence Unit
- **Traffic & Transport:** Traffic Unit, MTTD
- **Specialized Operations:** SWAT, K-9 Unit, Rapid Response Unit, Counter Terrorism Unit
- **Crime Prevention:** Anti-Armed Robbery Unit, Narcotics Control Unit
- **Support Services:** DOVVSU, Juvenile and Child Welfare Unit
- **Operational:** Patrol Unit, Operations Unit, FPU, Marine Police Unit
- **Administrative:** Administration Unit, Public Relations Unit

#### `units`
Specific unit instances at any organizational level (station, district, division, or region).

**Key Fields:**
- `unit_name` - Specific name (e.g., "Accra Central CID", "Greater Accra Traffic Unit")
- `unit_code` - Unique identifier for the unit
- `unit_type_id` - Links to unit_types
- `station_id`, `district_id`, `division_id`, `region_id` - Where unit is based (at least one required)
- `parent_unit_id` - For hierarchical units (e.g., sub-units within larger units)
- `unit_head_officer_id` - Officer in charge of the unit
- `is_active` - Whether unit is currently operational

**Flexibility:**
- Units can exist at any level: station-level CID, district-level SWAT, regional Intelligence Unit
- Units can have sub-units via `parent_unit_id` (e.g., CID with sub-units for homicide, fraud, etc.)
- One officer can be unit head while others are assigned as members

#### `unit_officer_assignments`
Tracks which officers are assigned to which units.

**Key Fields:**
- `assignment_type` - Permanent, Temporary, Secondment
- `position_in_unit` - Role within unit (e.g., "Lead Investigator", "Team Leader", "Analyst")
- `start_date`, `end_date` - Duration of assignment
- `is_current` - Boolean for current assignment

**Usage:**
- Officers can be assigned to multiple units over time (history tracked)
- Temporary assignments for special operations
- Secondment for inter-unit collaboration
- Update officer's `current_unit_id` when assignment changes

**Workflow Example:**
1. Create unit (e.g., "Accra Central CID")
2. Assign unit head via `unit_head_officer_id`
3. Assign officers via `unit_officer_assignments`
4. Update each officer's `current_unit_id` field
5. Track all assignment changes with dates

### Officers Management

#### `police_ranks`
Official Ghana Police Service rank structure with 15 ranks from Recruit Constable to Inspector General of Police.

**Key Fields:**
- `rank_name` - Official rank title
- `rank_level` - Numeric hierarchy (1-15)
- `rank_category` - Junior Officer, Senior Officer, Commissioned Officer, Senior Command

**Pre-loaded Ranks:**
- **Junior Officers (1-4):** Recruit Constable, General Constable, Lance Corporal, Corporal
- **Senior Officers (5-9):** Sergeant, Station Sergeant, Inspector, Chief Inspector, Superintendent
- **Commissioned Officers (10-13):** Chief Superintendent, ACP, DCOP, COP
- **Senior Command (14-15):** DIGP, IGP

#### `officers`
Complete database of all police officers (separate from system users).

**Key Fields:**
- `service_number` - Unique officer identifier
- `rank_id` - Current rank (links to police_ranks)
- `date_of_enlistment` - When officer joined the service
- `current_station_id`, `current_district_id`, `current_division_id`, `current_region_id` - Current posting
- `employment_status` - Active, On Leave, Suspended, Retired, Deceased, Dismissed
- `specialization` - e.g., CID, SWAT, Traffic, K-9
- `user_id` - Links to users table if officer has system access (NULL if no system access)
- `next_of_kin_name`, `next_of_kin_contact` - Emergency contact information
- `blood_group` - Medical information

**Important:** Not all officers need system access. The `user_id` field links officers who have login credentials.

#### `officer_postings`
Complete history of officer assignments and transfers.

**Key Fields:**
- `posting_type` - Initial Posting, Transfer, Promotion Transfer, Temporary Assignment
- `position_title` - e.g., "Station Commander", "CID Officer", "Traffic Supervisor"
- `start_date`, `end_date` - Duration of posting
- `is_current` - Boolean flag for current posting
- `posting_order_number` - Official posting order reference

**Usage:**
- Create new posting record for each transfer
- Set previous posting's `is_current` to FALSE and add `end_date`
- Update officer's `current_station_id` etc. in officers table

#### `officer_promotions`
Tracks all rank promotions throughout officer's career.

**Key Fields:**
- `from_rank_id`, `to_rank_id` - Rank change
- `promotion_date` - When promotion was announced
- `effective_date` - When promotion takes effect
- `promotion_order_number` - Official promotion order reference

**Workflow:**
1. Create promotion record
2. Update officer's `rank_id` in officers table
3. Optionally create new posting if promotion includes transfer

#### `officer_leave_records`
Leave applications and approvals.

**Leave Types:**
- Annual Leave, Sick Leave, Maternity Leave, Paternity Leave, Study Leave, Compassionate Leave, Other

**Key Fields:**
- `total_days` - Calculated leave duration
- `leave_status` - Pending, Approved, Rejected, Cancelled
- `approved_by` - Supervisor who approved/rejected

**Best Practices:**
- Update officer's `employment_status` to 'On Leave' when approved leave is active
- Revert to 'Active' when leave ends

#### `officer_disciplinary_records`
Disciplinary actions and investigations.

**Key Fields:**
- `case_id` - Links to cases table if related to a criminal case
- `offence_type` - Type of misconduct
- `disciplinary_action` - Warning, Suspension, Demotion, Dismissal, Fine, Other
- `status` - Under Investigation, Action Taken, Cleared, Appeal Pending
- `start_date`, `end_date` - Duration of suspension/action

**Usage:**
- Link to case if officer misconduct is related to a case
- Update officer's `employment_status` to 'Suspended' during suspension period
- Track appeals and outcomes

#### `officer_training`
Training courses, workshops, and certifications.

**Training Types:**
- Basic Training, Advanced Training, Specialized Course, Workshop, Seminar, Certification

**Key Fields:**
- `training_name` - e.g., "Advanced Criminal Investigation", "Firearms Training"
- `training_institution` - Training provider
- `certificate_number` - Official certificate reference
- `certificate_path` - Scanned certificate file
- `grade_score` - Performance in training

**Usage:**
- Track officer qualifications for specialized assignments
- Verify training requirements for promotions
- Maintain certification currency

#### `officer_commendations`
Awards, medals, and recognition.

**Commendation Types:**
- Award, Medal, Certificate of Merit, Letter of Commendation, Other

**Key Fields:**
- `title` - Name of award/commendation
- `award_date` - When award was given
- `awarded_by` - Authority who gave the award
- `certificate_path` - Award certificate/photo

**Usage:**
- Track officer achievements
- Support promotion decisions
- Maintain service record

### Case Management

#### `cases`
Central table for all criminal cases.

**Key Fields:**
- `case_number` - Unique case identifier (e.g., CID/2024/001)
- `case_type` - Complaint or Police Initiated
- `case_priority` - Low, Medium, High, Critical
- `status` - Open, Under Investigation, Referred, Closed, Archived
- `investigation_deadline` - Target completion date
- `closed_date` - Timestamp when case closed

**Best Practices:**
- Always assign cases via `case_assignments` table
- Log status changes in `case_status_history`
- Update `case_updates` regularly with progress notes

#### `case_assignments`
Formal assignment of officers to cases.

**Key Fields:**
- `assigned_to` - Officer assigned to case
- `assigned_by` - Supervisor who made assignment
- `status` - Active, Completed, Reassigned

### Investigation

#### `suspects`
Persons of interest or accused individuals.

**Key Fields:**
- `current_status` - Suspect, Arrested, Charged, Convicted, etc.
- `national_id` / `ghana_card_number` - Official identification
- `biometric_ref` - Reference to biometric system
- `photo_path` - Mugshot or identification photo

#### `arrests`
Arrest records with warrant information.

**Key Fields:**
- `arrest_type` - With Warrant or Without Warrant
- `warrant_number` - Court warrant reference
- `arresting_officer_id` - Officer who made arrest

#### `charges`
Criminal charges filed against suspects.

**Key Fields:**
- `offence_name` - Name of the crime
- `law_section` - Legal reference (e.g., "Section 123 of Criminal Code")
- `charge_status` - Pending, Filed, Withdrawn, Dismissed

### Legal Proceedings

#### `court_proceedings`
Complete court case tracking.

**Key Fields:**
- `hearing_type` - Arraignment, Hearing, Verdict, Sentencing, Appeal
- `court_name` - Which court is handling the case
- `next_hearing_date` - Scheduled next appearance
- `judge_name` - Presiding judge

**Usage:**
- Create record for each court appearance
- Link to notifications for upcoming court dates
- Track outcomes and verdicts

#### `bail_records`
Bail applications and decisions.

**Key Fields:**
- `bail_status` - Granted, Denied, Revoked
- `bail_amount` - Monetary value (DECIMAL for precision)
- `bail_conditions` - Terms of bail

#### `custody_records`
Tracks suspects in custody.

**Key Fields:**
- `custody_status` - In Custody, Released, Transferred, Escaped
- `custody_location` - Where suspect is held
- `custody_start` / `custody_end` - Duration in custody

### Evidence Management

#### `evidence`
Physical and digital evidence storage.

**Key Fields:**
- `evidence_number` - Unique evidence identifier
- `file_path` - Location of digital evidence
- `verification_hash` - SHA256 hash for integrity
- `mime_type` - File type for digital evidence
- `collection_date` / `collection_location` - Where/when collected

**Security:**
- Always log transfers in `evidence_custody_chain`
- Verify hash before court presentation
- Track file size for storage management

#### `evidence_custody_chain`
Legal chain of custody for evidence.

**Key Fields:**
- `transferred_from` / `transferred_to` - Officers involved
- `purpose` - Reason for transfer (e.g., "Lab Analysis", "Court Presentation")
- `location` - Current physical location

**Critical:** Every evidence transfer must be logged for legal validity.

### Notifications

#### `notifications`
Real-time alerts for users.

**Types:**
- Case Assignment - New case assigned
- Status Change - Case status updated
- Court Date - Upcoming court appearance
- Custody Alert - Custody status change
- Escalation - Case referred to higher level
- System Alert - System notifications

**Key Fields:**
- `is_read` - Boolean flag
- `read_at` - Timestamp when read

### Audit & Compliance

#### `audit_logs`
Comprehensive activity logging.

**Key Fields:**
- `action_type` - CREATE, UPDATE, DELETE, LOGIN, LOGOUT, VIEW, EXPORT
- `table_name` / `record_id` - What was affected
- `old_values` / `new_values` - JSON change tracking
- `ip_address` / `user_agent` - Security context

**Usage:**
- Automatically log all sensitive operations
- Store before/after values in JSON format
- Track exports for compliance

---

## Relationships

### Hierarchical Structure
```
Region (1) ──→ (N) Division
Division (1) ──→ (N) District
District (1) ──→ (N) Station
Station (1) ──→ (N) Users
```

### Case Flow
```
User creates → Case
Case links to → Complainant (optional)
Case links to → Suspects (many-to-many)
Case links to → Witnesses (many-to-many)
Case has → Evidence
Case has → Arrests
Case has → Charges
Case has → Court Proceedings
```

### Evidence Chain
```
Evidence created → Initial custody (uploaded_by)
Evidence transferred → Custody Chain Entry
Evidence transferred → Custody Chain Entry
... (complete audit trail)
```

---

## Security Features

### 1. **Authentication**
- Password hashing (use bcrypt or argon2)
- Session token management
- Failed login tracking (lock after 5 attempts)
- Account lockout (configurable duration)
- Two-factor authentication support

### 2. **Authorization**
- Role-based access control (8 predefined roles)
- Hierarchical data access (users see their level and below)
- Audit logging of all actions

### 3. **Data Protection**
- Foreign key constraints prevent orphaned records
- CASCADE deletes for dependent records
- JSON audit trail for change tracking
- Evidence integrity verification (hash)

### 4. **Compliance**
- Complete audit trail
- Chain of custody for evidence
- Status change history
- Session tracking with IP/user agent

---

## Performance Optimizations

### Indexes Created

**Users & Structure:**
- `idx_users_station`, `idx_users_district`, `idx_users_division`, `idx_users_region`
- `idx_users_status`
- `idx_division_region`, `idx_district_division`, `idx_station_district`

**Cases:**
- `idx_cases_status`, `idx_cases_created_at`, `idx_cases_priority`
- `idx_cases_station`, `idx_cases_district`
- `idx_case_updates_case`, `idx_case_updates_date`

**Investigation:**
- `idx_suspect_name`, `idx_suspect_status`, `idx_suspect_national_id`
- `idx_case_suspects_case`, `idx_case_suspects_suspect`
- `idx_arrests_case`, `idx_arrests_suspect`, `idx_arrests_date`
- `idx_charges_case`, `idx_charges_suspect`

**Evidence:**
- `idx_evidence_case`, `idx_evidence_type`
- `idx_custody_chain_evidence`

**Audit:**
- `idx_audit_user`, `idx_audit_table`, `idx_audit_action`, `idx_audit_time`

### Query Optimization Tips

1. **Hierarchical Queries:** Use indexes on station_id, district_id, division_id, region_id
2. **Date Ranges:** Indexes on created_at, updated_at, arrest_date, court_date
3. **Status Filtering:** Indexes on status fields for dashboard queries
4. **Joins:** Foreign key indexes optimize JOIN operations

---

## Usage Instructions

### For New Installations

1. **Create Database:**
```sql
CREATE DATABASE ghpims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ghpims;
```

2. **Import Improved Schema:**
```bash
mysql -u root -p ghpims < db_improved.sql
```

3. **Verify Installation:**
```sql
SHOW TABLES;
SELECT COUNT(*) FROM roles;  -- Should return 8 default roles
```

### For Existing Databases

1. **Backup Current Database:**
```bash
mysqldump -u root -p ghpims > ghpims_backup_$(date +%Y%m%d).sql
```

2. **Run Migration Script:**
```bash
mysql -u root -p ghpims < migration.sql
```

3. **Verify Migration:**
```sql
-- Check new tables exist
SHOW TABLES LIKE 'user_sessions';
SHOW TABLES LIKE 'court_proceedings';
SHOW TABLES LIKE 'notifications';

-- Check new columns
DESCRIBE users;
DESCRIBE cases;
DESCRIBE suspects;
```

### Creating First Admin User

```sql
-- Insert first admin user (password should be hashed in application)
INSERT INTO users (
    service_number, full_name, rank, role_id, 
    username, password_hash, status
) VALUES (
    'ADM001', 'System Administrator', 'Commissioner', 
    1, 'admin', '$2y$10$...', 'Active'
);
```

### Common Queries

**Get all open cases in a station:**
```sql
SELECT c.case_number, c.description, c.status, c.case_priority,
       u.full_name as created_by_name
FROM cases c
JOIN users u ON c.created_by = u.id
WHERE c.station_id = ? AND c.status = 'Open'
ORDER BY c.case_priority DESC, c.created_at DESC;
```

**Get suspect's complete history:**
```sql
SELECT s.full_name, s.current_status,
       c.case_number, c.description,
       a.arrest_date, ch.offence_name,
       cp.court_name, cp.hearing_type, cp.outcome
FROM suspects s
LEFT JOIN case_suspects cs ON s.id = cs.suspect_id
LEFT JOIN cases c ON cs.case_id = c.id
LEFT JOIN arrests a ON s.id = a.suspect_id AND c.id = a.case_id
LEFT JOIN charges ch ON s.id = ch.suspect_id AND c.id = ch.case_id
LEFT JOIN court_proceedings cp ON s.id = cp.suspect_id AND c.id = cp.case_id
WHERE s.id = ?
ORDER BY a.arrest_date DESC, cp.court_date DESC;
```

**Evidence chain of custody:**
```sql
SELECT e.evidence_number, e.description,
       u1.full_name as transferred_from_name,
       u2.full_name as transferred_to_name,
       ecc.transfer_date, ecc.purpose, ecc.location
FROM evidence e
JOIN evidence_custody_chain ecc ON e.id = ecc.evidence_id
LEFT JOIN users u1 ON ecc.transferred_from = u1.id
JOIN users u2 ON ecc.transferred_to = u2.id
WHERE e.id = ?
ORDER BY ecc.transfer_date ASC;
```

**User's unread notifications:**
```sql
SELECT n.notification_type, n.message, n.created_at,
       c.case_number
FROM notifications n
LEFT JOIN cases c ON n.case_id = c.id
WHERE n.user_id = ? AND n.is_read = FALSE
ORDER BY n.created_at DESC;
```

**Get all officers at a station with their ranks:**
```sql
SELECT o.service_number, o.full_name, pr.rank_name, pr.rank_level,
       o.specialization, o.phone_number, o.employment_status
FROM officers o
JOIN police_ranks pr ON o.rank_id = pr.id
WHERE o.current_station_id = ? AND o.employment_status = 'Active'
ORDER BY pr.rank_level DESC, o.full_name ASC;
```

**Get officer's complete service record:**
```sql
SELECT o.service_number, o.full_name, pr.rank_name,
       o.date_of_enlistment, o.employment_status,
       o.specialization, o.current_station_id
FROM officers o
JOIN police_ranks pr ON o.rank_id = pr.id
WHERE o.service_number = ?;
```

**Get officer's posting history:**
```sql
SELECT op.posting_type, op.position_title,
       s.station_name, d.district_name, dv.division_name, r.region_name,
       op.start_date, op.end_date, op.is_current,
       u.full_name as posted_by_name
FROM officer_postings op
LEFT JOIN stations s ON op.station_id = s.id
LEFT JOIN districts d ON op.district_id = d.id
LEFT JOIN divisions dv ON op.division_id = dv.id
LEFT JOIN regions r ON op.region_id = r.id
LEFT JOIN users u ON op.posted_by = u.id
WHERE op.officer_id = ?
ORDER BY op.start_date DESC;
```

**Get officer's promotion history:**
```sql
SELECT pr1.rank_name as from_rank, pr2.rank_name as to_rank,
       op.promotion_date, op.effective_date,
       op.promotion_order_number, u.full_name as approved_by_name
FROM officer_promotions op
JOIN police_ranks pr1 ON op.from_rank_id = pr1.id
JOIN police_ranks pr2 ON op.to_rank_id = pr2.id
LEFT JOIN users u ON op.approved_by = u.id
WHERE op.officer_id = ?
ORDER BY op.promotion_date DESC;
```

**Get officers on leave:**
```sql
SELECT o.service_number, o.full_name, pr.rank_name,
       olr.leave_type, olr.start_date, olr.end_date, olr.total_days
FROM officer_leave_records olr
JOIN officers o ON olr.officer_id = o.id
JOIN police_ranks pr ON o.rank_id = pr.id
WHERE olr.leave_status = 'Approved'
  AND olr.start_date <= CURDATE()
  AND olr.end_date >= CURDATE()
ORDER BY olr.end_date ASC;
```

**Get officers due for promotion (example: 5+ years in current rank):**
```sql
SELECT o.service_number, o.full_name, pr.rank_name,
       o.date_of_enlistment,
       TIMESTAMPDIFF(YEAR, COALESCE(
           (SELECT MAX(promotion_date) FROM officer_promotions WHERE officer_id = o.id),
           o.date_of_enlistment
       ), CURDATE()) as years_in_rank
FROM officers o
JOIN police_ranks pr ON o.rank_id = pr.id
WHERE o.employment_status = 'Active'
  AND pr.rank_level < 15
HAVING years_in_rank >= 5
ORDER BY years_in_rank DESC;
```

**Get all units at a station with their heads:**
```sql
SELECT u.unit_name, u.unit_code, ut.unit_type_name,
       o.full_name as unit_head_name, pr.rank_name as unit_head_rank,
       u.is_active,
       (SELECT COUNT(*) FROM unit_officer_assignments 
        WHERE unit_id = u.id AND is_current = TRUE) as officer_count
FROM units u
JOIN unit_types ut ON u.unit_type_id = ut.id
LEFT JOIN officers o ON u.unit_head_officer_id = o.id
LEFT JOIN police_ranks pr ON o.rank_id = pr.id
WHERE u.station_id = ? AND u.is_active = TRUE
ORDER BY ut.unit_type_name;
```

**Get all officers in a specific unit:**
```sql
SELECT o.service_number, o.full_name, pr.rank_name,
       uoa.position_in_unit, uoa.assignment_type,
       uoa.start_date
FROM unit_officer_assignments uoa
JOIN officers o ON uoa.officer_id = o.id
JOIN police_ranks pr ON o.rank_id = pr.id
WHERE uoa.unit_id = ? AND uoa.is_current = TRUE
ORDER BY pr.rank_level DESC, o.full_name;
```

**Get officer's unit assignment history:**
```sql
SELECT u.unit_name, ut.unit_type_name,
       uoa.position_in_unit, uoa.assignment_type,
       uoa.start_date, uoa.end_date, uoa.is_current
FROM unit_officer_assignments uoa
JOIN units u ON uoa.unit_id = u.id
JOIN unit_types ut ON u.unit_type_id = ut.id
WHERE uoa.officer_id = ?
ORDER BY uoa.start_date DESC;
```

**Get all CID officers across all stations:**
```sql
SELECT o.service_number, o.full_name, pr.rank_name,
       u.unit_name, s.station_name, d.district_name,
       uoa.position_in_unit
FROM unit_officer_assignments uoa
JOIN officers o ON uoa.officer_id = o.id
JOIN police_ranks pr ON o.rank_id = pr.id
JOIN units u ON uoa.unit_id = u.id
JOIN unit_types ut ON u.unit_type_id = ut.id
LEFT JOIN stations s ON u.station_id = s.id
LEFT JOIN districts d ON u.district_id = d.id
WHERE ut.unit_type_name = 'Criminal Investigation Department (CID)'
  AND uoa.is_current = TRUE
  AND o.employment_status = 'Active'
ORDER BY d.district_name, s.station_name, pr.rank_level DESC;
```

---

## Best Practices

### 1. **Units Management**
- Create units at appropriate organizational level (station, district, division, or region)
- Assign unit head via `unit_head_officer_id` before assigning members
- Use `unit_officer_assignments` for all officer-to-unit assignments
- Update officer's `current_unit_id` when assignment changes
- Set `is_current = FALSE` and add `end_date` when officer leaves unit
- Use `parent_unit_id` for hierarchical units (sub-units within larger units)
- Track temporary assignments and secondments separately
- Specify `position_in_unit` for role clarity (e.g., "Lead Investigator", "Team Leader")
- Deactivate units via `is_active = FALSE` rather than deleting

### 2. **Officers Management**
- Maintain separate records for all officers (not just system users)
- Link officers to users table only if they need system access
- Always create posting record when transferring officers
- Update officer's current location fields when posting changes
- Create promotion record before updating officer's rank
- Track leave status and update employment_status accordingly
- Link disciplinary records to cases when applicable
- Maintain complete training history for qualification tracking
- Document all commendations for career progression

### 3. **Case Management**
- Always create case assignments when assigning officers
- Log every significant update in case_updates
- Update case status through application to trigger status_history
- Set investigation_deadline for tracking
- Use case_priority for workload management

### 3. **Evidence Handling**
- Generate unique evidence_number (e.g., EVD/2024/001)
- Calculate and store verification_hash for digital evidence
- Log initial custody when evidence is collected
- Log every transfer in evidence_custody_chain
- Store file metadata (size, MIME type) for management

### 3. **Suspect Tracking**
- Update current_status when status changes
- Log status changes in suspect_status_history
- Link suspects to cases via case_suspects (many-to-many)
- Record all arrests with proper documentation
- Track custody status in custody_records

### 4. **Court Proceedings**
- Create entry for each court appearance
- Set next_hearing_date for scheduling
- Create notification for upcoming court dates
- Record outcomes for case closure
- Link to charges for complete picture

### 5. **Security**
- Hash passwords using bcrypt or argon2 (cost factor 10+)
- Implement session timeout (e.g., 30 minutes)
- Log all sensitive operations in audit_logs
- Track failed login attempts
- Implement account lockout after 5 failed attempts
- Store IP address for security analysis

### 6. **Performance**
- Use indexes for WHERE, JOIN, and ORDER BY clauses
- Paginate large result sets
- Archive old closed cases to separate table
- Regular OPTIMIZE TABLE on large tables
- Monitor slow query log

---

## Maintenance

### Regular Tasks

**Daily:**
- Clean up expired sessions
- Send court date reminders

**Weekly:**
- Review audit logs for anomalies
- Check custody records for overdue releases

**Monthly:**
- Archive closed cases older than 6 months
- Generate performance reports
- Review and optimize slow queries

**Quarterly:**
- Database backup verification
- Security audit
- Performance tuning

### Backup Strategy

```bash
# Daily backup
mysqldump -u root -p --single-transaction ghpims > backup_daily.sql

# Weekly full backup with compression
mysqldump -u root -p --single-transaction ghpims | gzip > backup_weekly_$(date +%Y%m%d).sql.gz

# Monthly archive
mysqldump -u root -p --single-transaction ghpims > backup_monthly_$(date +%Y%m).sql
```

---

## Support & Maintenance

### Database Version
- MySQL 5.7+ or MariaDB 10.2+
- InnoDB storage engine
- UTF8MB4 character set for international support

### Future Enhancements
- Full-text search on case descriptions
- Geospatial data for crime mapping
- Document versioning for evidence
- Integration with biometric systems
- Mobile app synchronization tables
- Analytics and reporting tables

---

## Contact & Support

For technical support or questions about the database schema, refer to this documentation or contact the development team.

**Last Updated:** December 2024  
**Version:** 2.0 (Improved Schema)
