# Real-World Scenario: Complainant with Known Suspect

## 📋 Scenario Overview

**Date:** December 17, 2024  
**Time:** 11:00 AM  
**Location:** Accra Central Police Station  
**Complainant:** Kofi Asante (Male, 42 years old)  
**Complaint:** Assault and theft by known person  
**Suspect:** Kwabena Boateng (neighbor)

---

## 🎬 Complete Workflow with Suspect

### **Step 1: Complainant Arrives with Suspect Information**

**Kofi Asante walks into Accra Central Police Station**

**Desk Officer:** "Good morning, sir. How can we help you?"

**Kofi:** "Good morning, officer. I was attacked and robbed yesterday evening. I know who did it - it's my neighbor Kwabena Boateng. He took my phone and GHS 2,000."

**Desk Officer:** "I'm sorry to hear that. Let me take your details and we'll register your complaint. Do you have any injuries?"

**Kofi:** "Yes, he hit me on the head. I went to the hospital last night. Here's my medical report."

**Desk Officer:** "Okay, please have a seat. Let me get all the details."

---

### **Step 2: Register Complainant**

```sql
-- Check if complainant exists
CALL sp_check_person_criminal_record(
    'GHA-456789123-2',  -- Kofi's Ghana Card
    '0201234567',       -- Phone
    NULL, NULL,
    'Kofi',             -- first_name
    'Asante'            -- last_name
);

-- Result: Person not found, create new person
CALL sp_register_person(
    'Kofi',                 -- first_name
    NULL,                   -- middle_name
    'Asante',               -- last_name
    'Male',
    '1982-06-20',
    '0201234567',
    'kofi.asante@email.com',
    'House 12, Dansoman, Accra',
    'GHA-456789123-2',
    NULL, NULL,
    @person_id, @is_duplicate, @message
);
-- person_id = 150

-- Create complainant record
INSERT INTO complainants (person_id, complainant_type)
VALUES (150, 'Individual');
-- complainant_id = 50
```

---

### **Step 3: Create Case**

```sql
INSERT INTO cases (
    case_number,
    case_type,
    case_priority,
    description,
    complainant_id,
    station_id,
    district_id,
    division_id,
    region_id,
    status,
    investigation_deadline,
    created_by
) VALUES (
    'ACC/2024/1524',
    'Complaint',
    'High',                    -- High priority (assault + known suspect)
    'Assault and theft. Complainant attacked by neighbor Kwabena Boateng. Stolen: iPhone 14, GHS 2,000 cash. Complainant sustained head injury (medical report attached).',
    50,                        -- Kofi Asante
    5,                         -- Accra Central Station
    3,                         -- Accra Metro District
    2,                         -- Greater Accra Division
    1,                         -- Greater Accra Region
    'Open',
    '2024-12-24',             -- 7 days (high priority)
    10                         -- Desk Officer
);
-- case_id = 1524
```

---

### **Step 4: Record Statement with Suspect Details**

```sql
INSERT INTO statements (
    case_id,
    complainant_id,
    statement_type,
    statement_text,
    recorded_by
) VALUES (
    1524,
    50,
    'Complainant',
    'My name is Kofi Asante. On December 16, 2024, at approximately 7:30 PM, I was returning home from work. As I was entering my gate at House 12, Dansoman, my neighbor Kwabena Boateng approached me.
    
    He asked to borrow money. I told him I did not have any cash on me. He became angry and grabbed my shirt. He said "I know you have money, give it to me now!" I tried to push him away but he punched me in the face and head multiple times.
    
    I fell to the ground and he took my phone (iPhone 14, black color) from my pocket and my wallet which contained GHS 2,000 cash. He then ran away towards his house which is House 15, same street.
    
    I went to the hospital where I received treatment for head injury and facial bruises. I have the medical report.
    
    SUSPECT DETAILS:
    Name: Kwabena Boateng
    Address: House 15, Dansoman, Accra (my neighbor)
    Phone: 0244888999 (I have his number)
    Description: Male, about 35 years old, dark complexion, about 5\'10" tall, medium build
    
    I can identify him. We have been neighbors for 2 years. I want to press charges.',
    10
);
```

---

### **Step 5: Check if Suspect Already in System**

**CRITICAL STEP: Officer checks if suspect is known to police**

```sql
-- Check suspect in system
CALL sp_check_person_criminal_record(
    NULL,               -- No Ghana Card yet
    '0244888999',       -- Suspect's phone from complainant
    NULL, NULL,
    'Kwabena',          -- first_name
    'Boateng'           -- last_name
);
```

**System Response:**
```
🚨 PERSON FOUND IN SYSTEM!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Name: Kwabena Boateng
Phone: 0244888999
Ghana Card: GHA-111222333-4
Gender: Male
DOB: 1989-08-10 (35 years)
Address: House 15, Dansoman, Accra

⚠️ HAS CRIMINAL RECORD: YES
Risk Level: HIGH

📋 CRIMINAL HISTORY:
1. Case CID/2023/089 - Suspect (Assault) - Convicted
   Date: 2023-05-15
   Outcome: 6 months imprisonment, released 2023-11-15

2. Case CID/2022/156 - Suspect (Theft) - Convicted
   Date: 2022-03-20
   Outcome: 1 year imprisonment

3. Case CID/2021/234 - Suspect (Assault) - Acquitted
   Date: 2021-09-10

🚨 ACTIVE ALERTS:
1. [HIGH] Repeat Offender - 2 previous convictions
2. [MEDIUM] Known to be violent when confronted

⚠️ CURRENT CASES:
None (last case closed 2023-11-15)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RECOMMENDATION: High-risk suspect. Request backup for arrest.
```

**Officer:** "Sir, I see this person has a criminal record. He's been convicted twice before for similar offenses. We'll handle this carefully."

**Kofi:** "I didn't know he had been to prison! That explains his behavior."

---

### **Step 6: Register Suspect (Link to Existing Person)**

```sql
-- Person already exists (person_id = 75)
-- Add as suspect to this case

CALL sp_add_suspect_to_case(
    1524,               -- case_id
    75,                 -- person_id (Kwabena Boateng)
    10                  -- added_by (Desk Officer)
);

-- This procedure automatically:
-- 1. Creates suspect record (if not exists)
-- 2. Links suspect to case
-- 3. Updates person.has_criminal_record = TRUE
-- 4. Updates person.risk_level = 'High'
-- 5. Adds to person_criminal_history
-- 6. Logs in audit_logs

-- Returns:
-- suspect_id = 35
-- Alerts: "Repeat Offender", "Known to be violent"
```

**System automatically creates:**
```sql
-- Suspect record
INSERT INTO suspects (person_id, current_status)
VALUES (75, 'Suspect');
-- suspect_id = 35

-- Link to case
INSERT INTO case_suspects (case_id, suspect_id)
VALUES (1524, 35);

-- Add to criminal history
INSERT INTO person_criminal_history (
    person_id, case_id, involvement_type, 
    offence_category, case_date
) VALUES (
    75, 1524, 'Suspect', 'Assault and Theft', '2024-12-17'
);

-- Update person risk level
UPDATE persons 
SET has_criminal_record = TRUE,
    risk_level = 'High'
WHERE id = 75;
```

---

### **Step 7: Record Evidence (Medical Report)**

```sql
-- Upload medical report
INSERT INTO case_documents (
    case_id,
    document_type,
    document_title,
    document_number,
    file_path,
    uploaded_by,
    description
) VALUES (
    1524,
    'Medical Report',
    'Head Injury Medical Report - Kofi Asante',
    'MED-2024-12-16-789',
    '/uploads/cases/1524/medical_report_kofi_asante.pdf',
    10,
    'Medical report from Ridge Hospital dated 16/12/2024. Diagnosis: Head contusion, facial bruises. Treatment: Pain medication, observation.'
);

-- Record stolen items
INSERT INTO exhibits (
    case_id,
    exhibit_number,
    exhibit_type,
    description,
    status,
    location,
    recorded_by
) VALUES 
(1524, 'EXH/2024/1524-001', 'Stolen Property', 
 'iPhone 14, Black color, IMEI: 987654321098765', 
 'Missing', 'Unknown - Stolen by suspect', 10),
 
(1524, 'EXH/2024/1524-002', 'Stolen Property',
 'Cash - GHS 2,000',
 'Missing', 'Unknown - Stolen by suspect', 10);
```

---

### **Step 8: Assign to CID with High Priority**

```sql
-- Assign to senior investigator (assault + known violent suspect)
INSERT INTO case_assignments (
    case_id,
    assigned_to,
    assigned_by,
    status
) VALUES (
    1524,
    30,      -- Detective Inspector Ama Mensah (senior)
    10,
    'Active'
);

-- Send urgent notification
INSERT INTO notifications (
    user_id,
    case_id,
    notification_type,
    message
) VALUES (
    30,
    1524,
    'Case Assignment',
    '🚨 URGENT: High-priority assault case assigned. Known violent suspect with criminal record. Victim has medical report. Suspect: Kwabena Boateng (House 15, Dansoman). Request backup for arrest.'
);
```

---

### **Step 9: Create Investigation Plan with Suspect Info**

```sql
-- Investigation timeline - Case opened
INSERT INTO case_investigation_timeline (
    case_id,
    milestone_id,
    activity_type,
    activity_title,
    activity_description,
    activity_date,
    completed_by,
    is_milestone
) VALUES (
    1524,
    1,
    'Administrative',
    'Case Opened - Suspect Identified',
    'Assault and theft complaint received. Suspect identified as Kwabena Boateng (known offender with 2 prior convictions). High-risk arrest required.',
    '2024-12-17 11:00:00',
    10,
    TRUE
);

-- Investigation checklist (modified for known suspect)
INSERT INTO case_investigation_checklist (case_id, checklist_item, item_category) VALUES
(1524, 'Review suspect criminal history', 'Initial Response'),
(1524, 'Interview complainant in detail', 'Witnesses'),
(1524, 'Verify suspect address', 'Investigation'),
(1524, 'Obtain arrest warrant', 'Documentation'),
(1524, 'Plan arrest operation (backup required)', 'Investigation'),
(1524, 'Execute arrest', 'Suspects'),
(1524, 'Recover stolen items', 'Evidence'),
(1524, 'Interview suspect', 'Suspects'),
(1524, 'Prepare charges', 'Documentation'),
(1524, 'Court proceedings', 'Court Preparation');

-- Investigation tasks (immediate actions)
INSERT INTO case_investigation_tasks (
    case_id, task_title, task_description, task_type,
    assigned_to, assigned_by, priority, due_date, status
) VALUES 
(1524, 'Obtain Arrest Warrant', 
 'Prepare warrant application for Kwabena Boateng. Include criminal history and medical evidence.',
 'Court Preparation', 30, 10, 'Urgent', '2024-12-17', 'Pending'),
 
(1524, 'Verify Suspect Location',
 'Confirm suspect is at House 15, Dansoman. Surveillance if necessary.',
 'Follow-up', 30, 10, 'Urgent', '2024-12-17', 'Pending'),
 
(1524, 'Plan Arrest Operation',
 'Coordinate with backup team. Suspect has history of violence. Safety priority.',
 'Other', 30, 10, 'Urgent', '2024-12-18', 'Pending');
```

---

### **Step 10: Print Complaint Receipt**

```sql
SELECT 
    c.case_number,
    c.description,
    c.case_priority,
    c.created_at,
    CONCAT_WS(' ', p_comp.first_name, p_comp.last_name) as complainant_name,
    p_comp.contact as complainant_phone,
    CONCAT_WS(' ', p_susp.first_name, p_susp.last_name) as suspect_name,
    p_susp.contact as suspect_phone,
    p_susp.has_criminal_record,
    s.station_name,
    CONCAT_WS(' ', u.first_name, u.last_name) as received_by,
    CONCAT_WS(' ', o.first_name, o.last_name) as investigating_officer
FROM cases c
JOIN complainants comp ON c.complainant_id = comp.id
JOIN persons p_comp ON comp.person_id = p_comp.id
JOIN case_suspects cs ON c.id = cs.case_id
JOIN suspects susp ON cs.suspect_id = susp.id
JOIN persons p_susp ON susp.person_id = p_susp.id
JOIN stations s ON c.station_id = s.id
JOIN users u ON c.created_by = u.id
LEFT JOIN case_assignments ca ON c.id = ca.case_id AND ca.status = 'Active'
LEFT JOIN officers o ON ca.assigned_to = o.id
WHERE c.id = 1524;
```

**Receipt Printed:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        GHANA POLICE SERVICE
        ACCRA CENTRAL POLICE STATION
        COMPLAINT RECEIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Case Number: ACC/2024/1524
Priority: 🚨 HIGH
Date: December 17, 2024 at 11:00 AM

COMPLAINANT DETAILS:
Name: Kofi Asante
Phone: 0201234567
Ghana Card: GHA-456789123-2

COMPLAINT:
Assault and theft by known person. Complainant attacked
and robbed of iPhone 14 and GHS 2,000 cash. Medical 
treatment received for head injury.

⚠️ SUSPECT IDENTIFIED:
Name: Kwabena Boateng
Address: House 15, Dansoman, Accra
Phone: 0244888999
⚠️ Known Offender - Criminal Record: YES

INVESTIGATING OFFICER:
Detective Inspector Ama Mensah
CID Unit - Senior Investigator

RECEIVED BY:
Constable Joseph Adu
Badge No: 12345

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IMPORTANT INFORMATION:
- Keep this receipt safe
- Quote case number for all inquiries
- Investigation deadline: December 24, 2024
- Contact: 0302-123456 for updates

⚠️ URGENT CASE: Arrest warrant being processed.
You will be contacted within 24 hours.

For your safety, please avoid contact with the suspect.
If you see the suspect, call us immediately.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Officer:** "Mr. Asante, we've registered your case as high priority. The suspect has a criminal record, so we're taking this very seriously. Detective Inspector Ama Mensah will handle your case. We'll be obtaining an arrest warrant today. Please stay safe and avoid the suspect. If you see him, call us immediately. Don't try to confront him."

**Kofi:** "Thank you, officer. I'm relieved you're taking this seriously. I'll wait for your call."

---

## 📊 What Happened in the Database

### **Tables Updated (15 tables):**

1. ✅ **persons** - Complainant created (Kofi), Suspect found (Kwabena)
2. ✅ **complainants** - Kofi registered as complainant
3. ✅ **suspects** - Kwabena added as suspect
4. ✅ **case_suspects** - Linked Kwabena to case
5. ✅ **person_criminal_history** - Updated Kwabena's history
6. ✅ **person_alerts** - Active alerts for Kwabena shown
7. ✅ **cases** - New case created (HIGH priority)
8. ✅ **statements** - Detailed statement with suspect info
9. ✅ **case_documents** - Medical report uploaded
10. ✅ **exhibits** - Stolen items recorded (2 items)
11. ✅ **case_assignments** - Assigned to senior investigator
12. ✅ **notifications** - Urgent notification sent
13. ✅ **case_investigation_checklist** - 10 items created
14. ✅ **case_investigation_timeline** - Milestone logged
15. ✅ **case_investigation_tasks** - 3 urgent tasks created
16. ✅ **audit_logs** - Complete audit trail

---

## 🚨 Key Differences When Suspect is Known

### **1. Instant Criminal History Check** ✅
- System immediately shows suspect's prior convictions
- Risk level assessment (High/Medium/Low)
- Active alerts displayed
- Officer can assess danger level

### **2. Higher Priority** ✅
- Case marked as HIGH priority (known violent offender)
- Assigned to senior investigator
- Shorter deadline (7 days vs 14 days)
- Urgent notification sent

### **3. Safety Measures** ✅
- Backup required for arrest
- Complainant warned to avoid suspect
- Arrest warrant prioritized
- Safety protocols activated

### **4. Faster Investigation** ✅
- Suspect already in system (no need to identify)
- Address known
- Phone number available
- Can proceed directly to arrest warrant

### **5. Evidence Collection** ✅
- Medical report immediately uploaded
- Stolen items documented
- Suspect's prior cases reviewed
- Pattern of behavior established

---

## 📱 Detective's Mobile View

**Detective Inspector Ama Mensah receives:**
```
🚨 URGENT CASE ASSIGNED

Case: ACC/2024/1524
Type: Assault & Theft
Priority: 🚨 HIGH
Deadline: Dec 24, 2024 (7 days)

Complainant: Kofi Asante (0201234567)
Injuries: Head contusion, facial bruises
Medical Report: ✅ Attached

⚠️ SUSPECT IDENTIFIED:
Name: Kwabena Boateng
Address: House 15, Dansoman
Phone: 0244888999

🚨 CRIMINAL RECORD:
- 2 Prior Convictions (Assault, Theft)
- Last Release: Nov 2023
- Risk Level: HIGH
- Known to be violent

Stolen Items:
- iPhone 14 (IMEI: 987654321098765)
- GHS 2,000 cash

⚠️ URGENT TASKS:
1. Obtain arrest warrant (TODAY)
2. Verify suspect location (TODAY)
3. Plan arrest operation (TOMORROW)

⚠️ SAFETY ALERT: Request backup for arrest

[VIEW FULL DETAILS] [START INVESTIGATION]
```

---

## 🎯 Investigation Next Steps (24-48 Hours)

### **Hour 1-2: Warrant Application**
```sql
-- Detective prepares warrant
INSERT INTO case_documents (
    case_id, document_type, document_title, file_path
) VALUES (
    1524, 'Warrant', 'Arrest Warrant Application - Kwabena Boateng',
    '/uploads/cases/1524/warrant_application.pdf'
);

-- Update task
UPDATE case_investigation_tasks
SET status = 'Completed',
    completion_date = NOW(),
    completion_notes = 'Warrant application submitted to court. Includes criminal history and medical evidence.'
WHERE id = (SELECT id FROM case_investigation_tasks 
            WHERE case_id = 1524 AND task_title = 'Obtain Arrest Warrant');
```

### **Hour 3-4: Surveillance**
```sql
-- Log surveillance activity
INSERT INTO case_updates (case_id, update_note, updated_by)
VALUES (1524, 'Surveillance conducted at House 15, Dansoman. Suspect confirmed at location. Vehicle: White Toyota Corolla, Reg: GR-5678-20. Suspect appears to be home.', 30);
```

### **Hour 6: Warrant Approved**
```sql
-- Record warrant issued
INSERT INTO case_documents (
    case_id, document_type, document_title, document_number, file_path
) VALUES (
    1524, 'Warrant', 'Arrest Warrant - Kwabena Boateng',
    'WRT/2024/1524', '/uploads/cases/1524/arrest_warrant.pdf'
);

-- Update checklist
UPDATE case_investigation_checklist
SET is_completed = TRUE,
    completed_by = 30,
    completed_date = NOW()
WHERE case_id = 1524 AND checklist_item = 'Obtain arrest warrant';
```

### **Day 2: Arrest Operation**
```sql
-- Plan operation
INSERT INTO operations (
    operation_code, operation_name, operation_type,
    operation_date, start_time, target_location,
    operation_commander_id, station_id, case_id
) VALUES (
    'OP-2024-1524', 'Arrest Kwabena Boateng', 'Arrest Operation',
    '2024-12-18', '2024-12-18 06:00:00', 'House 15, Dansoman',
    30, 5, 1524
);

-- Assign backup officers
INSERT INTO operation_officers (operation_id, officer_id, role_in_operation)
VALUES 
(1, 30, 'Operation Commander'),
(1, 31, 'Backup Officer 1'),
(1, 32, 'Backup Officer 2');
```

### **Arrest Executed**
```sql
-- Record arrest
INSERT INTO arrests (
    case_id, suspect_id, arresting_officer_id,
    arrest_date, arrest_location, reason, warrant_number, arrest_type
) VALUES (
    1524, 35, 30, '2024-12-18 06:15:00',
    'House 15, Dansoman, Accra',
    'Assault and theft as per complaint ACC/2024/1524',
    'WRT/2024/1524', 'With Warrant'
);

-- Update suspect status
UPDATE suspects SET current_status = 'Arrested' WHERE id = 35;

-- Log milestone
INSERT INTO case_investigation_timeline (
    case_id, milestone_id, activity_type, activity_title,
    activity_description, activity_date, completed_by, is_milestone
) VALUES (
    1524, 5, 'Arrest', 'Suspect Arrested',
    'Kwabena Boateng arrested at 06:15 without incident. Taken into custody. Recovered: iPhone 14, GHS 1,500 cash.',
    '2024-12-18 06:15:00', 30, TRUE
);

-- Notify complainant
INSERT INTO notifications (user_id, case_id, notification_type, message)
VALUES (
    (SELECT user_id FROM officers WHERE id = 30),
    1524, 'Status Change',
    'Suspect arrested. Please contact complainant Kofi Asante to inform him.'
);
```

---

## ✅ Summary

**With Known Suspect, the system provides:**

1. ✅ **Instant Background Check** - Criminal history immediately available
2. ✅ **Risk Assessment** - Automatic risk level calculation
3. ✅ **Safety Alerts** - Warnings about violent history
4. ✅ **Faster Processing** - No need to identify suspect
5. ✅ **Better Planning** - Address, phone, patterns known
6. ✅ **Evidence Linking** - Connect to previous cases
7. ✅ **Informed Decisions** - Officers know what to expect
8. ✅ **Victim Safety** - Appropriate warnings given
9. ✅ **Resource Allocation** - Backup assigned based on risk
10. ✅ **Complete Timeline** - From complaint to arrest tracked

**Time to Arrest:** ~30 hours (vs weeks for unknown suspects)

The system's **person registry** and **criminal history tracking** make a huge difference when the suspect is known, enabling faster, safer, and more effective police response!
