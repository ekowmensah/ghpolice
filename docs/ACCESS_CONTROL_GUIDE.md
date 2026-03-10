# User Access Control Implementation Guide

## Overview

The GHPIMS implements a comprehensive **Role-Based Access Control (RBAC)** system with **hierarchical data access** to ensure proper security and data segregation across the Ghana Police Service.

---

## Access Control Architecture

### **3-Layer Security Model**

1. **Role-Based Permissions** - What actions can a role perform?
2. **Hierarchical Data Access** - What data can a role see?
3. **Sensitive Data Logging** - Who accessed confidential information?

---

## 1. Role-Based Access Control (RBAC)

### **Components**

#### **Modules** (`modules` table)
Organize system features into logical groups.

**Examples:**
- Case Management
- Officer Management
- Evidence Management
- Firearms Registry
- Intelligence Operations
- Reports & Analytics

#### **Permissions** (`permissions` table)
Specific actions within each module.

**Examples:**
- `case.create` - Create new cases
- `case.view` - View case details
- `case.update` - Update case information
- `case.delete` - Delete cases
- `evidence.export` - Export evidence records
- `firearm.assign` - Assign firearms to officers

#### **Role Permissions** (`role_permissions` table)
Define what each role can do.

**CRUD + Extras:**
- `can_create` - Create new records
- `can_read` - View records
- `can_update` - Modify records
- `can_delete` - Delete records
- `can_export` - Export data
- `can_approve` - Approve actions (e.g., leave requests)

---

## 2. Hierarchical Data Access

### **Access Levels**

| Level | Description | Example |
|-------|-------------|---------|
| **Own** | Only own records | Officer sees only their own duty roster |
| **Unit** | Own unit's data | CID officer sees CID unit cases |
| **Station** | Station-level data | Station Officer sees all station cases |
| **District** | District-level data | District Commander sees all district cases |
| **Division** | Division-level data | Divisional Commander sees division data |
| **Region** | Regional data | Regional Commander sees regional data |
| **National** | All data | IGP and Super Admin see everything |

### **Rule Types**

#### **1. Hierarchical Access**
Based on organizational structure (Region → Division → District → Station)

**Example:** District Commander at Accra District sees:
- All cases at stations under Accra District
- All officers posted to Accra District
- All operations in Accra District

#### **2. Ownership Access**
User sees only records they created or are assigned to.

**Example:** Investigator sees:
- Cases assigned to them
- Their own duty roster
- Their own leave requests

#### **3. Unit-Based Access**
Access based on unit membership.

**Example:** CID officer sees:
- Cases handled by CID unit
- CID unit members
- CID operations

#### **4. Station-Based Access**
Access limited to specific station.

**Example:** Station Officer sees:
- All cases at their station
- All officers at their station
- Station resources (firearms, vehicles)

---

## Role Definitions & Access Rules

### **1. Super Admin**
**Access Level:** National  
**Permissions:** Full CRUD on all modules  
**Data Access:** All records nationwide

**Can:**
- Manage all users and roles
- Access all cases and operations
- View all confidential data (informants, intelligence)
- System configuration
- Generate all reports

---

### **2. Inspector General of Police (IGP)**
**Access Level:** National  
**Permissions:** Read all, approve major operations  
**Data Access:** All records nationwide

**Can:**
- View all cases and operations
- Approve major operations
- View national statistics
- Access strategic intelligence

**Cannot:**
- Delete system data
- Modify user permissions (Super Admin only)

---

### **3. Regional Commander (COP)**
**Access Level:** Region  
**Permissions:** Full CRUD within region  
**Data Access:** All data in their region

**Can:**
- Manage all cases in region
- Approve operations in region
- Transfer officers within region
- View regional reports
- Access regional intelligence

**Cannot:**
- Access other regions' data
- Modify national policies
- Delete closed cases

---

### **4. Divisional Commander (ACP/DCOP)**
**Access Level:** Division  
**Permissions:** Full CRUD within division  
**Data Access:** All data in their division

**Can:**
- Manage division cases
- Approve division operations
- Assign officers within division
- View division reports

**Cannot:**
- Access other divisions' data
- Approve regional operations

---

### **5. District Commander (Chief Superintendent)**
**Access Level:** District  
**Permissions:** Full CRUD within district  
**Data Access:** All data in their district

**Can:**
- Manage district cases
- Approve district operations
- Assign officers within district
- Manage district resources

**Cannot:**
- Access other districts' data
- Transfer officers outside district

---

### **6. Station Officer**
**Access Level:** Station  
**Permissions:** Full CRUD at station  
**Data Access:** All data at their station

**Can:**
- Manage station cases
- Assign officers to duties
- Issue firearms
- Approve leave (station level)
- Manage station resources

**Cannot:**
- Access other stations' data
- Approve operations outside station
- Transfer officers

---

### **7. Investigator (CID Officer)**
**Access Level:** Own + Unit  
**Permissions:** Full CRUD on assigned cases  
**Data Access:** Cases assigned to them + CID unit cases

**Can:**
- Create and manage assigned cases
- Update investigation timeline
- Record evidence
- Interview suspects/witnesses
- Request warrants

**Cannot:**
- Delete cases
- Access cases not assigned to them
- Access confidential intelligence (unless assigned)
- Approve operations

---

### **8. Records Officer**
**Access Level:** Station  
**Permissions:** Read all, Create/Update records  
**Data Access:** All station records

**Can:**
- View all station cases
- Create case files
- Update case information
- Generate reports
- Manage documents

**Cannot:**
- Delete cases
- Approve operations
- Access confidential intelligence
- Modify closed cases

---

### **9. Evidence Officer**
**Access Level:** Station  
**Permissions:** Full CRUD on evidence/exhibits  
**Data Access:** All station evidence

**Can:**
- Manage evidence registry
- Track chain of custody
- Issue evidence to court
- Dispose of evidence (with approval)
- Generate evidence reports

**Cannot:**
- Access case investigation details
- Modify case status
- Delete evidence records

---

### **10. Armory Officer**
**Access Level:** Station  
**Permissions:** Full CRUD on firearms  
**Data Access:** Station firearms only

**Can:**
- Issue firearms
- Track ammunition
- Record maintenance
- Generate armory reports

**Cannot:**
- Access cases
- View intelligence
- Transfer firearms to other stations

---

## Implementation Examples

### **Example 1: Case Access Control**

```sql
-- District Commander viewing cases
SELECT c.*
FROM cases c
WHERE c.district_id = (SELECT district_id FROM users WHERE id = ?)
  OR c.division_id = (SELECT division_id FROM users WHERE id = ?)
  OR c.region_id = (SELECT region_id FROM users WHERE id = ?);

-- Investigator viewing assigned cases
SELECT c.*
FROM cases c
JOIN case_assignments ca ON c.id = ca.case_id
WHERE ca.assigned_to = (SELECT id FROM officers WHERE user_id = ?);

-- Station Officer viewing station cases
SELECT c.*
FROM cases c
WHERE c.station_id = (SELECT station_id FROM users WHERE id = ?);
```

### **Example 2: Officer Access Control**

```sql
-- Regional Commander viewing officers in region
SELECT o.*
FROM officers o
WHERE o.current_region_id = (SELECT region_id FROM users WHERE id = ?);

-- Station Officer viewing station officers
SELECT o.*
FROM officers o
WHERE o.current_station_id = (SELECT station_id FROM users WHERE id = ?);

-- Unit member viewing unit officers
SELECT o.*
FROM officers o
JOIN unit_officer_assignments uoa ON o.id = uoa.officer_id
WHERE uoa.unit_id = (SELECT current_unit_id FROM officers WHERE user_id = ?)
  AND uoa.is_current = TRUE;
```

### **Example 3: Sensitive Data Access**

```sql
-- Log access to informant data
INSERT INTO sensitive_data_access_log (
    user_id, table_name, record_id, access_type, access_reason
) VALUES (
    ?, 'informants', ?, 'VIEW', 'Case investigation review'
);

-- Only handler can view informant details
SELECT i.*
FROM informants i
WHERE i.handler_officer_id = (SELECT id FROM officers WHERE user_id = ?)
  OR ? IN (SELECT id FROM users WHERE role_id = 1); -- Super Admin
```

---

## Permission Matrix

### **Case Management Module**

| Role | Create | Read | Update | Delete | Export | Approve |
|------|--------|------|--------|--------|--------|---------|
| Super Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Regional Commander | ✓ | ✓ (Region) | ✓ | ✗ | ✓ | ✓ |
| District Commander | ✓ | ✓ (District) | ✓ | ✗ | ✓ | ✓ |
| Station Officer | ✓ | ✓ (Station) | ✓ | ✗ | ✓ | ✗ |
| Investigator | ✓ | ✓ (Assigned) | ✓ | ✗ | ✗ | ✗ |
| Records Officer | ✓ | ✓ (Station) | ✓ | ✗ | ✓ | ✗ |

### **Intelligence Module**

| Role | Create | Read | Update | Delete | Export | Approve |
|------|--------|------|--------|--------|---------|---------|
| Super Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Regional Commander | ✓ | ✓ (Region) | ✓ | ✗ | ✓ | ✓ |
| Intelligence Officer | ✓ | ✓ (Own) | ✓ | ✗ | ✗ | ✗ |
| Investigator | ✗ | ✓ (Assigned) | ✗ | ✗ | ✗ | ✗ |
| Others | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

### **Firearms Module**

| Role | Create | Read | Update | Delete | Export | Approve |
|------|--------|------|--------|--------|--------|---------|
| Super Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Station Officer | ✗ | ✓ (Station) | ✗ | ✗ | ✓ | ✓ |
| Armory Officer | ✓ | ✓ (Station) | ✓ | ✗ | ✓ | ✗ |
| Others | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

---

## Special Access Rules

### **Confidential Data**

#### **Informants** (`informants` table)
- **Access:** Handler officer + Super Admin only
- **Logging:** All access logged in `sensitive_data_access_log`
- **Reason Required:** Must provide reason for access

#### **Intelligence Reports** (`informant_intelligence` table)
- **Access:** Handler + assigned investigators + commanders
- **Logging:** All access logged
- **Verification:** Intelligence verification status checked

#### **Public Complaints** (`public_complaints` table)
- **Access:** Professional Standards Unit + Super Admin + investigating officer
- **Logging:** All access logged
- **Restriction:** Officer complained against cannot access

#### **Officer Disciplinary Records** (`officer_disciplinary_records` table)
- **Access:** Professional Standards Unit + Super Admin + subject officer (own only)
- **Logging:** All access logged

---

## Data Access Query Examples

### **Check User Permission**
```sql
-- Check if user has permission
SELECT 
    COALESCE(up.can_read, rp.can_read, FALSE) as can_read,
    COALESCE(up.can_create, rp.can_create, FALSE) as can_create,
    COALESCE(up.can_update, rp.can_update, FALSE) as can_update,
    COALESCE(up.can_delete, rp.can_delete, FALSE) as can_delete
FROM users u
JOIN roles r ON u.role_id = r.id
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
LEFT JOIN user_permissions up ON u.id = up.user_id AND p.id = up.permission_id
WHERE u.id = ? AND p.permission_code = ?;
```

### **Get User's Accessible Stations**
```sql
-- Based on hierarchical level
SELECT s.*
FROM stations s
JOIN districts d ON s.district_id = d.id
JOIN divisions dv ON d.division_id = dv.id
JOIN regions r ON dv.region_id = r.id
WHERE 
    (s.id = (SELECT station_id FROM users WHERE id = ?)) OR  -- Own station
    (d.id = (SELECT district_id FROM users WHERE id = ?)) OR  -- District level
    (dv.id = (SELECT division_id FROM users WHERE id = ?)) OR  -- Division level
    (r.id = (SELECT region_id FROM users WHERE id = ?)) OR  -- Region level
    (? IN (SELECT id FROM users WHERE role_id = 1));  -- Super Admin
```

---

## Security Best Practices

### **1. Principle of Least Privilege**
- Grant minimum permissions needed
- Use temporary permissions for special tasks
- Regular permission audits

### **2. Separation of Duties**
- Evidence officers don't investigate
- Armory officers don't handle cases
- Records officers don't approve operations

### **3. Audit Everything**
- Log all access to sensitive data
- Track permission changes
- Monitor unusual access patterns

### **4. Time-Limited Access**
- Use `expiry_date` in `user_permissions`
- Automatic revocation of expired permissions
- Temporary elevation for special operations

### **5. Hierarchical Enforcement**
- Application layer enforces access rules
- Database views for role-based data filtering
- Stored procedures validate access

---

## Implementation Checklist

### **Application Layer (PHP/Backend)**

```php
// Example: Check permission
function hasPermission($userId, $permissionCode, $action) {
    // Check role permissions + user-specific permissions
    // Return true/false
}

// Example: Filter data by hierarchy
function getAccessibleCases($userId) {
    $user = getUser($userId);
    
    if ($user->role === 'Super Admin') {
        return getAllCases();
    } elseif ($user->role === 'Regional Commander') {
        return getCasesByRegion($user->region_id);
    } elseif ($user->role === 'Investigator') {
        return getAssignedCases($userId);
    }
    // ... etc
}

// Example: Log sensitive access
function logSensitiveAccess($userId, $table, $recordId, $reason) {
    // Insert into sensitive_data_access_log
}
```

### **Database Layer (Views)**

```sql
-- Create view for user's accessible cases
CREATE VIEW user_accessible_cases AS
SELECT c.*, u.id as user_id
FROM cases c
CROSS JOIN users u
WHERE 
    -- Super Admin sees all
    (u.role_id = 1) OR
    -- Regional level
    (c.region_id = u.region_id AND u.role_id IN (2)) OR
    -- Division level
    (c.division_id = u.division_id AND u.role_id IN (3)) OR
    -- District level
    (c.district_id = u.district_id AND u.role_id IN (4)) OR
    -- Station level
    (c.station_id = u.station_id AND u.role_id IN (5,7,8)) OR
    -- Assigned cases
    (c.id IN (SELECT case_id FROM case_assignments ca 
              JOIN officers o ON ca.assigned_to = o.id 
              WHERE o.user_id = u.id));
```

---

## Summary

The GHPIMS access control system provides:

✅ **Role-Based Permissions** - Fine-grained control over actions  
✅ **Hierarchical Data Access** - Automatic data filtering by organizational level  
✅ **Sensitive Data Protection** - Special rules for confidential information  
✅ **Audit Logging** - Complete access tracking  
✅ **Flexible Rules** - Custom access rules per role  
✅ **Temporary Permissions** - Time-limited access grants  
✅ **Separation of Duties** - Prevent conflicts of interest  

**Total Access Control Tables:** 6  
**Supported Roles:** 8+ (extensible)  
**Access Levels:** 7 (Own to National)  
**Protected Tables:** 74+ (all tables)

The system ensures that officers only see data relevant to their role and organizational level while maintaining complete audit trails for accountability.
