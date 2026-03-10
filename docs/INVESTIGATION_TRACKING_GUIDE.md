# Case Investigation Tracking Guide

## Overview

The GHPIMS provides **multiple layers** of investigation tracking to support officers throughout the case lifecycle.

---

## Investigation Tracking Components

### 1. **Case Updates** (Investigation Diary)
**Table:** `case_updates`  
**Purpose:** Free-form chronological log of all investigation activities

### 2. **Investigation Timeline** (Structured Activities)
**Table:** `case_investigation_timeline`  
**Purpose:** Structured timeline with categorized activities and milestones

### 3. **Investigation Tasks** (Task Management)
**Table:** `case_investigation_tasks`  
**Purpose:** Assign and track specific investigation tasks

### 4. **Investigation Checklist** (Procedural Compliance)
**Table:** `case_investigation_checklist`  
**Purpose:** Ensure all required steps are completed

### 5. **Milestones** (Major Progress Points)
**Table:** `investigation_milestones`  
**Purpose:** Track major investigation milestones

---

## Complete Investigation Workflow Example

### **Case: Armed Robbery at ABC Bank**

---

## Day 1: Case Opened

### **1. Create Case**
```sql
INSERT INTO cases (
    case_number, case_type, case_priority, description,
    station_id, created_by, investigation_deadline
) VALUES (
    'CID/2024/001', 'Complaint', 'High',
    'Armed robbery at ABC Bank, 3 suspects, GHS 50,000 stolen',
    5, 10, '2024-02-15'
);
```

### **2. Assign Investigating Officer**
```sql
INSERT INTO case_assignments (
    case_id, assigned_to, assigned_by
) VALUES (
    1, 25, 10  -- Assign to Detective Mensah
);
```

### **3. Log Case Opening (Diary Entry)**
```sql
INSERT INTO case_updates (
    case_id, update_note, updated_by
) VALUES (
    1, 'Case opened. Armed robbery reported at ABC Bank, Ridge Branch. 
    Initial complaint received from Bank Manager. 3 masked suspects, 
    firearms used, GHS 50,000 stolen. No injuries reported.',
    25
);
```

### **4. Record Milestone**
```sql
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by, is_milestone
) VALUES (
    1, 1, 'Administrative', 'Case Opened',
    'Armed robbery case registered and assigned to CID',
    '2024-01-15 09:00:00', 25, TRUE
);
```

### **5. Create Investigation Checklist**
```sql
INSERT INTO case_investigation_checklist (case_id, checklist_item, item_category) VALUES
(1, 'Visit crime scene', 'Initial Response'),
(1, 'Interview bank manager', 'Witnesses'),
(1, 'Interview bank staff', 'Witnesses'),
(1, 'Collect CCTV footage', 'Evidence'),
(1, 'Process fingerprints', 'Evidence'),
(1, 'Check similar cases', 'Investigation'),
(1, 'Identify suspects', 'Suspects'),
(1, 'Obtain arrest warrants', 'Documentation'),
(1, 'Prepare case file for court', 'Court Preparation');
```

### **6. Assign Initial Tasks**
```sql
-- Task 1: Visit crime scene
INSERT INTO case_investigation_tasks (
    case_id, task_title, task_description, task_type,
    assigned_to, assigned_by, priority, due_date
) VALUES (
    1, 'Process Crime Scene',
    'Visit ABC Bank, document scene, collect physical evidence',
    'Evidence Collection', 25, 10, 'Urgent', '2024-01-15'
);

-- Task 2: Collect CCTV
INSERT INTO case_investigation_tasks (
    case_id, task_title, task_description, task_type,
    assigned_to, assigned_by, priority, due_date
) VALUES (
    1, 'Obtain CCTV Footage',
    'Collect all CCTV footage from bank and surrounding area',
    'Evidence Collection', 25, 10, 'Urgent', '2024-01-15'
);
```

---

## Day 1 Afternoon: Crime Scene Processing

### **1. Update Task Status**
```sql
UPDATE case_investigation_tasks
SET status = 'In Progress'
WHERE id = 1;
```

### **2. Log Activity**
```sql
INSERT INTO case_updates (case_id, update_note, updated_by) VALUES (
    1, 'Crime scene visited at 14:00. Evidence collected: 
    - 3 shell casings (9mm)
    - Fingerprints from counter
    - Witness statements from 5 bank staff
    - CCTV footage (4 cameras, 2 hours)
    Scene photographed and documented.',
    25
);
```

### **3. Record Timeline Activity**
```sql
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by,
    location, outcome, next_steps, is_milestone
) VALUES (
    1, 2, 'Evidence', 'Crime Scene Processed',
    'Complete crime scene examination conducted at ABC Bank',
    '2024-01-15 14:00:00', 25,
    'ABC Bank, Ridge Branch',
    'Evidence collected: shell casings, fingerprints, CCTV footage',
    'Submit evidence to forensics lab, review CCTV footage',
    TRUE
);
```

### **4. Update Checklist**
```sql
UPDATE case_investigation_checklist
SET is_completed = TRUE, completed_by = 25, 
    completed_date = '2024-01-15 16:00:00',
    notes = 'Crime scene fully processed. Evidence secured.'
WHERE case_id = 1 AND checklist_item = 'Visit crime scene';
```

### **5. Complete Task**
```sql
UPDATE case_investigation_tasks
SET status = 'Completed',
    completion_date = '2024-01-15 16:00:00',
    completion_notes = 'Crime scene processed. All evidence collected and secured.'
WHERE id = 1;
```

---

## Day 2: Witness Interviews

### **1. Interview Bank Manager**
```sql
-- Record statement
INSERT INTO statements (
    case_id, statement_type, statement_text, recorded_by
) VALUES (
    1, 'Witness', 'I was in my office when I heard shouting. 
    Three men in masks entered with guns. They demanded money...',
    25
);

-- Log in diary
INSERT INTO case_updates (case_id, update_note, updated_by) VALUES (
    1, 'Interviewed Bank Manager Kwame Asante. Statement recorded. 
    Described 3 suspects: all male, approximately 5\'8"-6\'0", 
    armed with pistols. Spoke Twi. Escaped in white Toyota Corolla.',
    25
);

-- Add to timeline
INSERT INTO case_investigation_timeline (
    case_id, activity_type, activity_title, activity_description,
    activity_date, completed_by, outcome
) VALUES (
    1, 'Interview', 'Bank Manager Interview',
    'Recorded detailed statement from Kwame Asante (Bank Manager)',
    '2024-01-16 10:00:00', 25,
    'Key details: 3 male suspects, white Toyota Corolla getaway vehicle'
);
```

---

## Day 3: Evidence Analysis

### **1. Forensics Results**
```sql
-- Log results
INSERT INTO case_updates (case_id, update_note, updated_by) VALUES (
    1, 'Forensics report received:
    - Fingerprints match known offender: Kofi Mensah (ID: 12345)
    - Shell casings match weapon used in previous robbery (Case CID/2023/089)
    - CCTV analysis shows clear image of getaway vehicle: GR-1234-20',
    25
);

-- Timeline entry
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by,
    outcome, next_steps, is_milestone
) VALUES (
    1, 6, 'Evidence', 'Evidence Analyzed',
    'Forensics lab completed analysis of physical evidence',
    '2024-01-17 15:00:00', 25,
    'Fingerprints match Kofi Mensah. Vehicle identified: GR-1234-20',
    'Obtain arrest warrant for Kofi Mensah. Locate vehicle.',
    TRUE
);
```

### **2. Suspect Identified**
```sql
-- Create suspect record
INSERT INTO suspects (
    full_name, gender, address, current_status
) VALUES (
    'Kofi Mensah', 'Male', 'Nima, Accra', 'Suspect'
);

-- Link to case
INSERT INTO case_suspects (case_id, suspect_id) VALUES (1, 1);

-- Record milestone
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by, is_milestone
) VALUES (
    1, 4, 'Investigation', 'Suspect Identified',
    'Primary suspect identified through fingerprint match',
    '2024-01-17 16:00:00', 25, TRUE
);
```

### **3. Create New Tasks**
```sql
-- Task: Obtain warrant
INSERT INTO case_investigation_tasks (
    case_id, task_title, task_description, task_type,
    assigned_to, assigned_by, priority, due_date
) VALUES (
    1, 'Obtain Arrest Warrant',
    'Prepare warrant application for Kofi Mensah',
    'Court Preparation', 25, 10, 'High', '2024-01-18'
);

-- Task: Locate suspect
INSERT INTO case_investigation_tasks (
    case_id, task_title, task_description, task_type,
    assigned_to, assigned_by, priority, due_date
) VALUES (
    1, 'Locate Suspect',
    'Surveillance on known addresses. Locate Kofi Mensah.',
    'Follow-up', 26, 10, 'Urgent', '2024-01-20'
);
```

---

## Day 5: Arrest Operation

### **1. Plan Operation**
```sql
INSERT INTO operations (
    operation_code, operation_name, operation_type,
    operation_date, start_time, target_location,
    operation_commander_id, station_id, case_id
) VALUES (
    'OP-2024-001', 'Arrest Kofi Mensah', 'Arrest Operation',
    '2024-01-19', '2024-01-19 06:00:00', 'House 23, Nima',
    30, 5, 1
);
```

### **2. Execute Arrest**
```sql
-- Record arrest
INSERT INTO arrests (
    case_id, suspect_id, arresting_officer_id,
    arrest_date, arrest_location, reason
) VALUES (
    1, 1, 25, '2024-01-19 06:30:00',
    'House 23, Nima, Accra',
    'Arrest warrant for armed robbery at ABC Bank'
);

-- Update suspect status
UPDATE suspects SET current_status = 'Arrested' WHERE id = 1;

-- Log in diary
INSERT INTO case_updates (case_id, update_note, updated_by) VALUES (
    1, 'Suspect Kofi Mensah arrested at 06:30 at his residence in Nima. 
    No resistance. Taken into custody at Central Police Station. 
    Recovered stolen money (GHS 15,000) and one firearm.',
    25
);

-- Timeline milestone
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by,
    location, outcome, is_milestone
) VALUES (
    1, 5, 'Arrest', 'Suspect Arrested',
    'Kofi Mensah arrested without incident',
    '2024-01-19 06:30:00', 25,
    'House 23, Nima, Accra',
    'Suspect in custody. GHS 15,000 recovered. Firearm seized.',
    TRUE
);
```

---

## Day 7: Charges Filed

### **1. File Charges**
```sql
INSERT INTO charges (
    case_id, suspect_id, offence_name, law_section,
    charge_date, charged_by
) VALUES (
    1, 1, 'Armed Robbery', 'Section 149, Criminal Offences Act 1960',
    '2024-01-21', 25
);

-- Timeline entry
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by, is_milestone
) VALUES (
    1, 7, 'Court', 'Charges Filed',
    'Formal charges filed against Kofi Mensah',
    '2024-01-21 10:00:00', 25, TRUE
);
```

---

## Viewing Investigation Progress

### **Complete Case Timeline**
```sql
SELECT 
    cit.activity_date,
    cit.activity_type,
    cit.activity_title,
    cit.activity_description,
    cit.outcome,
    u.full_name as completed_by,
    cit.is_milestone
FROM case_investigation_timeline cit
JOIN users u ON cit.completed_by = u.id
WHERE cit.case_id = 1
ORDER BY cit.activity_date ASC;
```

### **Pending Tasks**
```sql
SELECT 
    task_title,
    task_type,
    priority,
    due_date,
    o.full_name as assigned_to
FROM case_investigation_tasks cit
JOIN officers o ON cit.assigned_to = o.id
WHERE cit.case_id = 1 AND cit.status IN ('Pending', 'In Progress')
ORDER BY cit.due_date ASC;
```

### **Checklist Progress**
```sql
SELECT 
    item_category,
    checklist_item,
    is_completed,
    completed_date,
    u.full_name as completed_by
FROM case_investigation_checklist cic
LEFT JOIN users u ON cic.completed_by = u.id
WHERE cic.case_id = 1
ORDER BY item_category, is_completed;
```

### **Investigation Diary**
```sql
SELECT 
    update_date,
    update_note,
    u.full_name as updated_by
FROM case_updates cu
JOIN users u ON cu.updated_by = u.id
WHERE cu.case_id = 1
ORDER BY cu.update_date DESC;
```

---

## Investigation Dashboard Query

```sql
-- Complete case overview
SELECT 
    c.case_number,
    c.description,
    c.status,
    c.case_priority,
    c.investigation_deadline,
    COUNT(DISTINCT cit.id) as timeline_entries,
    COUNT(DISTINCT task.id) as total_tasks,
    SUM(CASE WHEN task.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
    COUNT(DISTINCT chk.id) as checklist_items,
    SUM(CASE WHEN chk.is_completed = TRUE THEN 1 ELSE 0 END) as completed_checklist,
    COUNT(DISTINCT cu.id) as diary_entries
FROM cases c
LEFT JOIN case_investigation_timeline cit ON c.id = cit.case_id
LEFT JOIN case_investigation_tasks task ON c.id = task.case_id
LEFT JOIN case_investigation_checklist chk ON c.id = chk.case_id
LEFT JOIN case_updates cu ON c.id = cu.case_id
WHERE c.id = 1
GROUP BY c.id;
```

---

## Benefits of This System

### **1. Multiple Tracking Levels**
- **Diary:** Free-form notes for flexibility
- **Timeline:** Structured activities for reporting
- **Tasks:** Actionable items with deadlines
- **Checklist:** Procedural compliance
- **Milestones:** Major progress tracking

### **2. Accountability**
- Every action tracked with officer ID
- Timestamps for all activities
- Complete audit trail

### **3. Management Oversight**
- Supervisors see pending tasks
- Deadline tracking
- Progress monitoring
- Workload distribution

### **4. Court Preparation**
- Complete chronological record
- Evidence chain documented
- Witness interview timeline
- Professional presentation

### **5. Performance Metrics**
- Case resolution time
- Task completion rates
- Officer productivity
- Investigation quality

---

## Summary

The investigation tracking system provides:

✅ **Flexibility** - Free-form diary + structured timeline  
✅ **Task Management** - Assign and track investigation tasks  
✅ **Compliance** - Checklist ensures all steps completed  
✅ **Milestones** - Track major progress points  
✅ **Accountability** - Complete audit trail  
✅ **Reporting** - Generate investigation reports  
✅ **Oversight** - Management visibility  
✅ **Court-Ready** - Professional documentation  

Officers can use as much or as little structure as needed, from simple diary entries to complete task management with checklists.
