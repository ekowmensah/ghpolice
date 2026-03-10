# Real-World Scenario: Complainant Files a Complaint at Police Station

## 📋 Scenario Overview

**Date:** December 17, 2024  
**Time:** 10:30 AM  
**Location:** Accra Central Police Station  
**Complainant:** Ama Owusu (Female, 35 years old)  
**Complaint:** Theft of mobile phone and handbag

---

## 🎬 Step-by-Step Process

### **Step 1: Complainant Arrives at Station**

**Ama Owusu walks into Accra Central Police Station**

**Desk Officer:** "Good morning, madam. How can we help you?"

**Ama:** "Good morning, officer. Someone stole my phone and handbag this morning at the market."

**Desk Officer:** "I'm sorry to hear that. Let me take your details and register your complaint. Please have a seat."

---

### **Step 2: Check if Person Exists in System**

**Officer opens GHPIMS and searches for complainant**

```sql
-- Officer enters phone number or Ghana Card
CALL sp_check_person_criminal_record(
    'GHA-789456123-5',  -- Ama's Ghana Card
    '0244567890',       -- Ama's phone (alternative number)
    NULL,               -- No passport
    NULL,               -- No driver's license
    'Ama Owusu'         -- Name
);
```

**System Response:**
```
⚠️ PERSON FOUND IN SYSTEM
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Name: Ama Owusu
Ghana Card: GHA-789456123-5
Phone: 0244567890
Gender: Female
DOB: 1989-03-15 (35 years)
Address: House 45, Dansoman, Accra

Criminal Record: NO
Risk Level: None

📋 PREVIOUS INTERACTIONS:
1. Case CID/2023/234 - Complainant (Theft) - Closed
   Date: 2023-08-10
   Outcome: Suspect arrested and convicted

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Person has filed complaint before. Details loaded.
```

**Officer:** "I see you've reported a case with us before. Let me use your existing details."

---

### **Step 3: Register/Update Person Details**

**Since person exists, officer verifies and updates if needed**

```sql
-- Person already exists (person_id = 123)
-- Officer just needs to create complainant record if not already exists

-- Check if already a complainant
SELECT * FROM complainants WHERE person_id = 123;

-- If not exists, create complainant record
INSERT INTO complainants (person_id, complainant_type)
VALUES (123, 'Individual');
-- complainant_id = 45
```

**Officer:** "Can you confirm your current phone number is 0244567890?"

**Ama:** "Yes, that's correct."

**Officer:** "And you still live at House 45, Dansoman?"

**Ama:** "Yes, officer."

---

### **Step 4: Create New Case**

**Officer creates case record**

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
    'ACC/2024/1523',           -- Auto-generated case number
    'Complaint',               -- Case type
    'Medium',                  -- Priority
    'Theft of mobile phone (iPhone 13 Pro) and handbag containing GHS 500 cash, national ID, and bank cards at Makola Market',
    45,                        -- complainant_id (Ama Owusu)
    5,                         -- Accra Central Station
    3,                         -- Accra Metro District
    2,                         -- Greater Accra Division
    1,                         -- Greater Accra Region
    'Open',                    -- Status
    '2024-12-31',             -- Investigation deadline (14 days)
    10                         -- created_by (Desk Officer user_id)
);
-- case_id = 1523
```

**System automatically logs:**
```sql
INSERT INTO audit_logs (
    user_id, module, action_type, action_description,
    case_id, ip_address
) VALUES (
    10, 'Case Management', 'CREATE',
    'New complaint case registered: ACC/2024/1523',
    1523, '192.168.1.45'
);
```

---

### **Step 5: Record Detailed Statement**

**Officer takes detailed statement**

**Officer:** "Please tell me exactly what happened."

**Ama:** "I was at Makola Market around 8:30 AM this morning. I was buying vegetables when someone bumped into me from behind. When I turned around, my handbag was gone. It happened so fast. There were many people around."

```sql
INSERT INTO statements (
    case_id,
    complainant_id,
    statement_type,
    statement_text,
    recorded_by
) VALUES (
    1523,
    45,
    'Complainant',
    'I was at Makola Market around 8:30 AM on December 17, 2024. I was at the vegetable section buying tomatoes when I felt someone bump into me from behind. When I turned around to look, I noticed my handbag was missing from my shoulder. The bag contained:
    - iPhone 13 Pro (Gold color, IMEI: 123456789012345)
    - GHS 500 cash
    - Ghana Card (GHA-789456123-5)
    - Access Bank ATM card
    - House keys
    - Small purse with photos
    
    I did not see who took it. There were many people in the market at that time. I immediately looked around but could not find anyone suspicious or my bag. I reported to the market security who advised me to come to the police station.',
    10  -- recorded_by (Desk Officer)
);
```

---

### **Step 6: Record Stolen Items as Evidence/Exhibits**

**Officer records stolen items**

```sql
-- Record stolen phone
INSERT INTO exhibits (
    case_id,
    exhibit_number,
    exhibit_type,
    description,
    status,
    location,
    recorded_by
) VALUES (
    1523,
    'EXH/2024/1523-001',
    'Stolen Property',
    'iPhone 13 Pro, Gold color, IMEI: 123456789012345',
    'Missing',
    'Unknown - Stolen from Makola Market',
    10
);

-- Record stolen cash
INSERT INTO exhibits (
    case_id,
    exhibit_number,
    exhibit_type,
    description,
    status,
    location,
    recorded_by
) VALUES (
    1523,
    'EXH/2024/1523-002',
    'Stolen Property',
    'Cash - GHS 500',
    'Missing',
    'Unknown - Stolen from Makola Market',
    10
);

-- Record stolen documents
INSERT INTO exhibits (
    case_id,
    exhibit_number,
    exhibit_type,
    description,
    status,
    location,
    recorded_by
) VALUES (
    1523,
    'EXH/2024/1523-003',
    'Document',
    'Ghana Card (GHA-789456123-5), Access Bank ATM card, House keys',
    'Missing',
    'Unknown - Stolen from Makola Market',
    10
);
```

---

### **Step 7: Assign Case to Investigator**

**Desk Officer assigns case to CID officer**

```sql
-- Find available CID investigator
SELECT o.id, o.full_name, pr.rank_name
FROM officers o
JOIN police_ranks pr ON o.rank_id = pr.id
JOIN unit_officer_assignments uoa ON o.id = uoa.officer_id
JOIN units u ON uoa.unit_id = u.id
WHERE u.unit_code = 'CID'
  AND o.employment_status = 'Active'
  AND uoa.is_current = TRUE
LIMIT 1;

-- Assign to Detective Sergeant Kwame Mensah (officer_id = 25)
INSERT INTO case_assignments (
    case_id,
    assigned_to,
    assigned_by,
    status
) VALUES (
    1523,
    25,      -- Detective Sergeant Kwame Mensah
    10,      -- Assigned by Desk Officer
    'Active'
);
```

**System sends notification:**
```sql
INSERT INTO notifications (
    user_id,
    case_id,
    notification_type,
    message
) VALUES (
    25,  -- Detective Kwame Mensah's user_id
    1523,
    'Case Assignment',
    'New case assigned to you: ACC/2024/1523 - Theft at Makola Market. Priority: Medium. Deadline: 2024-12-31'
);
```

---

### **Step 8: Create Investigation Checklist**

**System auto-generates investigation checklist**

```sql
INSERT INTO case_investigation_checklist (case_id, checklist_item, item_category) VALUES
(1523, 'Visit crime scene (Makola Market)', 'Initial Response'),
(1523, 'Interview complainant in detail', 'Witnesses'),
(1523, 'Check market CCTV footage', 'Evidence'),
(1523, 'Interview market security', 'Witnesses'),
(1523, 'Check for similar theft cases in area', 'Investigation'),
(1523, 'Track stolen phone IMEI', 'Investigation'),
(1523, 'Alert banks about stolen ATM card', 'Administrative'),
(1523, 'Identify suspects from CCTV', 'Suspects'),
(1523, 'Prepare case file', 'Documentation');
```

---

### **Step 9: Log Initial Investigation Timeline**

```sql
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
    1523,
    1,  -- Case Opened milestone
    'Administrative',
    'Case Opened',
    'Complaint received from Ama Owusu regarding theft at Makola Market. Case registered and assigned to CID.',
    '2024-12-17 10:30:00',
    10,  -- Desk Officer
    TRUE
);
```

---

### **Step 10: Print Complaint Receipt for Complainant**

**Officer prints receipt**

```sql
-- Generate complaint receipt
SELECT 
    c.case_number,
    c.description,
    c.created_at,
    p.full_name as complainant_name,
    p.contact as complainant_phone,
    s.station_name,
    u.full_name as received_by,
    ca.assigned_to,
    o.full_name as investigating_officer
FROM cases c
JOIN complainants comp ON c.complainant_id = comp.id
JOIN persons p ON comp.person_id = p.id
JOIN stations s ON c.station_id = s.id
JOIN users u ON c.created_by = u.id
LEFT JOIN case_assignments ca ON c.id = ca.case_id AND ca.status = 'Active'
LEFT JOIN officers o ON ca.assigned_to = o.id
WHERE c.id = 1523;
```

**Receipt Printed:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        GHANA POLICE SERVICE
        ACCRA CENTRAL POLICE STATION
        COMPLAINT RECEIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Case Number: ACC/2024/1523
Date: December 17, 2024 at 10:30 AM

COMPLAINANT DETAILS:
Name: Ama Owusu
Phone: 0244567890
Ghana Card: GHA-789456123-5

COMPLAINT:
Theft of mobile phone (iPhone 13 Pro) and handbag 
containing GHS 500 cash, national ID, and bank cards 
at Makola Market

INVESTIGATING OFFICER:
Detective Sergeant Kwame Mensah
CID Unit

RECEIVED BY:
Constable Joseph Adu
Badge No: 12345

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IMPORTANT INFORMATION:
- Keep this receipt safe
- Quote case number for all inquiries
- Investigation deadline: December 31, 2024
- Contact: 0302-123456 for updates

You will be contacted by the investigating officer 
within 48 hours.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Officer:** "Here is your complaint receipt, madam. Your case number is ACC/2024/1523. Detective Sergeant Kwame Mensah will be investigating your case. He will contact you within 48 hours. Please keep this receipt safe."

**Ama:** "Thank you, officer. When should I come back?"

**Officer:** "The detective will call you. But if you don't hear from us in 2 days, you can call this number or come back with your receipt. Also, if you remember anything else or if someone contacts you about your items, please call us immediately."

**Ama:** "Okay, thank you very much."

---

## 📊 What Happened in the Database

### **Tables Updated:**

1. ✅ **persons** - Complainant details (already existed, verified)
2. ✅ **complainants** - Linked person to complainant role
3. ✅ **cases** - New case created
4. ✅ **statements** - Complainant statement recorded
5. ✅ **exhibits** - Stolen items recorded
6. ✅ **case_assignments** - Case assigned to investigator
7. ✅ **notifications** - Notification sent to investigator
8. ✅ **case_investigation_checklist** - Checklist created
9. ✅ **case_investigation_timeline** - Initial milestone logged
10. ✅ **audit_logs** - All actions logged

### **Automatic Processes:**

- ✅ Case number auto-generated
- ✅ Exhibit numbers auto-generated
- ✅ Notification sent to investigator
- ✅ Investigation checklist created
- ✅ Timeline milestone logged
- ✅ Audit trail created
- ✅ Investigation deadline set (14 days)

---

## 🔄 Next Steps (Investigator's Actions)

### **Within 24-48 Hours:**

1. **Detective Kwame Mensah receives notification**
   ```sql
   SELECT * FROM notifications 
   WHERE user_id = 25 AND is_read = FALSE;
   ```

2. **Reviews case details**
   ```sql
   SELECT * FROM cases WHERE id = 1523;
   SELECT * FROM statements WHERE case_id = 1523;
   SELECT * FROM exhibits WHERE case_id = 1523;
   ```

3. **Calls complainant**
   ```sql
   -- Log in diary
   INSERT INTO case_updates (case_id, update_note, updated_by)
   VALUES (1523, 'Called complainant Ama Owusu. Arranged to meet at Makola Market tomorrow at 9 AM to visit crime scene and review CCTV footage.', 25);
   ```

4. **Creates investigation tasks**
   ```sql
   INSERT INTO case_investigation_tasks (
       case_id, task_title, task_type, assigned_to, 
       assigned_by, priority, due_date
   ) VALUES 
   (1523, 'Visit Makola Market crime scene', 'Evidence Collection', 25, 25, 'High', '2024-12-18'),
   (1523, 'Obtain CCTV footage from market', 'Evidence Collection', 25, 25, 'High', '2024-12-18'),
   (1523, 'Track stolen phone IMEI', 'Follow-up', 25, 25, 'High', '2024-12-19');
   ```

---

## 📱 Mobile App View (Officer's Phone)

**Detective Kwame Mensah's Dashboard:**
```
🔔 NEW CASE ASSIGNED

Case: ACC/2024/1523
Type: Theft
Priority: ⚠️ MEDIUM
Deadline: Dec 31, 2024 (14 days)

Complainant: Ama Owusu
Phone: 0244123456

Stolen Items:
- iPhone 13 Pro (IMEI: 123456789012345)
- GHS 500 cash
- Documents

Location: Makola Market
Date: Dec 17, 2024, 8:30 AM

✅ Tasks (0/9 completed)
📋 Checklist (0/9 items)
📝 Statement recorded
👤 No suspects yet

[VIEW DETAILS] [START INVESTIGATION]
```

---

## 🎯 Summary

This scenario demonstrates:

1. ✅ **Person Registry** - Complainant found in system (previous case)
2. ✅ **Duplicate Prevention** - System recognized existing person
3. ✅ **Case Creation** - Complete case workflow
4. ✅ **Statement Recording** - Detailed statement captured
5. ✅ **Evidence Tracking** - Stolen items recorded as exhibits
6. ✅ **Case Assignment** - Automatic assignment to CID
7. ✅ **Notifications** - Real-time alert to investigator
8. ✅ **Investigation Tools** - Checklist and timeline auto-created
9. ✅ **Audit Trail** - Complete logging of all actions
10. ✅ **Receipt Generation** - Professional documentation for complainant

**Time Taken:** ~15 minutes from arrival to receipt

**Database Operations:** 15+ INSERT statements, multiple SELECT queries, all executed seamlessly with proper relationships and constraints.

The system ensures **nothing is missed**, **everyone is accountable**, and the **investigation can begin immediately** with all necessary information properly structured and accessible.
