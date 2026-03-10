# Ghana Police Operations - Model Integration Plan

**Date:** December 19, 2025  
**Updated:** December 19, 2025 (Post-Audit)  
**Purpose:** Create realistic interconnections between all models to support actual Ghana Police operations

## 📊 IMPLEMENTATION STATUS

**✅ COMPLETED:**
- All 70 models exist and are implemented
- All 50 controllers exist and are implemented
- Database schema is complete with all tables
- Stored procedures exist (sp_add_suspect_to_case, sp_check_person_criminal_record, etc.)
- Basic CRUD operations functional
- Core modules: Cases, Persons, Officers, Organizational Structure (Regions, Divisions, Districts, Stations, Units)

**❌ MISSING:**
- Relationship methods in existing models (models don't "talk" to each other)
- Junction table models (CaseSuspect, CaseWitness, CaseAssignment, etc.)
- Service layer for complex workflows
- Stored procedure integration into models/services
- Proper cascade operations and status history tracking

**🎯 FOCUS:** Add relationship methods and service layer to connect existing models

---

## 🎯 CORE OPERATIONAL WORKFLOWS

### 1. CASE INVESTIGATION WORKFLOW

**Primary Flow:**
```
Complaint/Incident → Case Creation → Suspect Identification → Arrest → Custody → Charges → Court → Outcome
```

**Model Interconnections:**

#### A. Case Initiation
- **complainants** → **cases** (complainant_id)
- **public_complaints** → **cases** (can generate case_id)
- **intelligence_reports** → **cases** (intelligence-led operations)
- **public_intelligence_tips** → **cases** (tip verification leads to case)
- **patrol_incidents** → **cases** (patrol discovers incident)

#### B. Person Management
- **persons** (central registry) connects to:
  - **suspects** (person_id) - criminal involvement
  - **witnesses** (person_id) - case witnesses
  - **complainants** (person_id) - case complainants
  - **informants** (person_id) - intelligence sources
  - **missing_persons** (person_id) - missing person reports

#### C. Investigation Process
- **cases** → **case_suspects** → **suspects** → **persons**
- **cases** → **case_witnesses** → **witnesses** → **persons**
- **cases** → **case_assignments** → **officers**
- **cases** → **case_investigation_tasks** → **officers** (assigned_to)
- **cases** → **case_investigation_timeline** (activity tracking)
- **cases** → **case_updates** (progress notes)
- **cases** → **statements** (from suspects/witnesses/complainants)

#### D. Evidence Management
- **cases** → **evidence** (case_id)
- **cases** → **exhibits** (case_id)
- **evidence** → **evidence_custody_chain** (tracking transfers)
- **exhibits** → **exhibit_movements** (tracking location changes)

#### E. Arrest & Custody
- **cases** → **arrests** → **suspects** → **persons**
- **arrests** → **arresting_officer_id** → **officers**
- **arrests** → **custody_records** (custody tracking)
- **custody_records** → **suspects** → **persons**
- **custody_records** → **stations** (custody location)

#### F. Legal Process
- **cases** → **charges** → **suspects**
- **charges** → **charged_by** → **officers**
- **charges** → **court_proceedings**
- **court_proceedings** → **suspects** → **persons**
- **bail_records** → **suspects** → **cases**

---

### 2. INTELLIGENCE OPERATIONS WORKFLOW

**Primary Flow:**
```
Intelligence Gathering → Analysis → Bulletin/Report → Surveillance → Operation → Case Creation
```

**Model Interconnections:**

#### A. Intelligence Sources
- **informants** → **informant_intelligence** (intelligence from sources)
- **informant_intelligence** → **cases** (verified intelligence creates case)
- **informant_intelligence** → **handler_officer_id** → **officers**
- **public_intelligence_tips** → **assigned_to** → **officers**
- **public_intelligence_tips** → **cases** (verified tips)

#### B. Intelligence Products
- **intelligence_reports** → **created_by** → **users** → **officers**
- **intelligence_reports** → **cases** (strategic/tactical intelligence)
- **intelligence_bulletins** → **issued_by** → **users** → **officers**
- **intelligence_bulletins** → **cases** (crime alerts, wanted persons)

#### C. Surveillance Operations
- **surveillance_operations** → **case_id** → **cases**
- **surveillance_operations** → **operation_commander_id** → **officers**
- **surveillance_operations** → **surveillance_officers** → **officers**
- **surveillance_operations** → **intelligence_reports** (surveillance findings)

---

### 3. OPERATIONAL DEPLOYMENT WORKFLOW

**Primary Flow:**
```
Duty Roster → Patrol Deployment → Incident Response → Case Creation → Investigation
```

**Model Interconnections:**

#### A. Duty Management
- **duty_roster** → **officer_id** → **officers**
- **duty_roster** → **station_id** → **stations**
- **duty_roster** → **shift_id** → **duty_shifts**

#### B. Patrol Operations
- **patrol_logs** → **station_id** → **stations**
- **patrol_logs** → **patrol_leader_id** → **officers**
- **patrol_logs** → **vehicle_id** → **vehicles**
- **patrol_logs** → **patrol_officers** → **officers**
- **patrol_logs** → **patrol_incidents** → **cases**

#### C. Incident Response
- **patrol_incidents** → **patrol_id** → **patrol_logs**
- **patrol_incidents** → **case_id** → **cases**
- **patrol_incidents** → **incident_location** (geographic data)

---

### 4. OFFICER MANAGEMENT WORKFLOW

**Primary Flow:**
```
Recruitment → Posting → Duty Assignment → Performance → Promotion/Disciplinary → Retirement
```

**Model Interconnections:**

#### A. Officer Profile
- **officers** → **rank_id** → **police_ranks**
- **officers** → **current_station_id** → **stations**
- **officers** → **current_district_id** → **districts**
- **officers** → **current_division_id** → **divisions**
- **officers** → **current_region_id** → **regions**
- **officers** → **current_unit_id** → **units**
- **officers** → **user_id** → **users** (system access)

#### B. Career Management
- **officer_postings** → **officer_id** → **officers**
- **officer_postings** → **station_id/district_id/division_id/region_id**
- **officer_promotions** → **officer_id** → **officers**
- **officer_promotions** → **from_rank_id/to_rank_id** → **police_ranks**

#### C. Biometrics & Security
- **officer_biometrics** → **officer_id** → **officers**
- **officer_biometrics** → **captured_by** → **officers**

---

### 5. ASSET & RESOURCE MANAGEMENT WORKFLOW

**Primary Flow:**
```
Asset Registration → Assignment → Usage → Maintenance → Decommission
```

**Model Interconnections:**

#### A. Firearms Management
- **firearms** → **current_holder_id** → **officers**
- **firearms** → **station_id** → **stations**
- **firearm_assignments** → **firearm_id** → **firearms**
- **firearm_assignments** → **officer_id** → **officers**
- **firearm_assignments** → **issued_by** → **officers**

#### B. Vehicle Management
- **vehicles** → **case_id** → **cases** (if stolen/evidence)
- **vehicles** → **patrol_logs** (patrol vehicle assignment)

#### C. Ammunition Management
- **ammunition** (station inventory tracking)
- **firearm_assignments** (ammunition_issued, ammunition_returned)

---

### 6. ORGANIZATIONAL STRUCTURE WORKFLOW

**Hierarchical Flow:**
```
National → Regions → Divisions → Districts → Stations → Units
```

**Model Interconnections:**

#### A. Geographic Hierarchy
- **regions** (top level)
- **divisions** → **region_id** → **regions**
- **districts** → **division_id** → **divisions**
- **stations** → **district_id** → **districts**
- **stations** → **division_id** → **divisions**
- **stations** → **region_id** → **regions**

#### B. Functional Units
- **units** → **unit_type_id** → **unit_types**
- **units** → **station_id/district_id/division_id/region_id**
- **units** → **unit_head_officer_id** → **officers**

#### C. Access Control
- **users** → **role_id** → **roles**
- **users** → **station_id/district_id/division_id/region_id**
- **roles** → **access_level** (Own/Unit/Station/District/Division/Region/National)

---

## 🔗 CRITICAL RELATIONSHIPS TO IMPLEMENT

### 1. Person-Centric Relationships

```sql
-- Central person registry connects to all person-related tables
persons (id)
  ├── suspects (person_id)
  ├── witnesses (person_id)
  ├── complainants (person_id)
  ├── informants (person_id) [if linked]
  └── missing_persons (person_id) [if found]

-- Criminal history tracking
person_criminal_history
  ├── person_id → persons
  ├── case_id → cases
  └── involvement_type (Suspect/Witness/Victim/Complainant)
```

### 2. Case-Centric Relationships

```sql
-- Cases are the central hub for investigations
cases (id)
  ├── complainant_id → complainants → persons
  ├── case_suspects → suspects → persons
  ├── case_witnesses → witnesses → persons
  ├── case_assignments → officers
  ├── evidence (case_id)
  ├── exhibits (case_id)
  ├── arrests (case_id)
  ├── charges (case_id)
  ├── court_proceedings (case_id)
  ├── custody_records (case_id)
  ├── statements (case_id)
  ├── case_updates (case_id)
  ├── case_investigation_tasks (case_id)
  ├── case_investigation_timeline (case_id)
  └── case_status_history (case_id)
```

### 3. Officer-Centric Relationships

```sql
-- Officers are involved in all operations
officers (id)
  ├── user_id → users (system access)
  ├── rank_id → police_ranks
  ├── current_station_id → stations
  ├── officer_postings (officer_id)
  ├── officer_promotions (officer_id)
  ├── officer_biometrics (officer_id)
  ├── duty_roster (officer_id)
  ├── patrol_logs (patrol_leader_id)
  ├── patrol_officers (officer_id)
  ├── case_assignments (assigned_to)
  ├── arrests (arresting_officer_id)
  ├── charges (charged_by)
  ├── firearm_assignments (officer_id)
  ├── surveillance_operations (operation_commander_id)
  └── informants (handler_officer_id)
```

### 4. Station-Centric Relationships

```sql
-- Stations are operational hubs
stations (id)
  ├── district_id → districts
  ├── division_id → divisions
  ├── region_id → regions
  ├── officers (current_station_id)
  ├── cases (station_id)
  ├── duty_roster (station_id)
  ├── patrol_logs (station_id)
  ├── firearms (station_id)
  ├── units (station_id)
  ├── public_complaints (station_id)
  └── missing_persons (station_id)
```

---

## 📋 REALISTIC OPERATIONAL SCENARIOS

### Scenario 1: Armed Robbery Investigation

**Workflow:**
1. **Incident Report** → patrol_incidents → cases (created)
2. **Victim Statement** → persons (victim) → complainants → statements
3. **Witness Identification** → persons (witnesses) → case_witnesses → statements
4. **Evidence Collection** → evidence, exhibits (weapons, stolen items)
5. **Intelligence Check** → person_criminal_history (known offenders)
6. **Surveillance** → surveillance_operations → surveillance_officers
7. **Arrest** → arrests → custody_records
8. **Suspect Statement** → statements (from suspect)
9. **Charges Filed** → charges → court_proceedings
10. **Court Process** → bail_records, court_proceedings

**Models Involved:** 15+ interconnected models

### Scenario 2: Missing Person Case

**Workflow:**
1. **Report Filed** → missing_persons → persons
2. **Case Created** → cases (linked to missing_persons)
3. **Investigation** → case_assignments → officers
4. **Intelligence Bulletin** → intelligence_bulletins (public alert)
5. **Patrol Alerts** → patrol_logs (officers briefed)
6. **Tips Received** → public_intelligence_tips → case_id
7. **Person Found** → missing_persons (status updated)
8. **Case Closed** → case_status_history

**Models Involved:** 10+ interconnected models

### Scenario 3: Intelligence-Led Operation

**Workflow:**
1. **Informant Intel** → informants → informant_intelligence
2. **Intelligence Report** → intelligence_reports → cases (potential)
3. **Surveillance Approved** → surveillance_operations → officers assigned
4. **Surveillance Findings** → intelligence_reports (updated)
5. **Operation Planned** → duty_roster (special assignment)
6. **Raid Executed** → arrests, evidence, exhibits
7. **Suspects Processed** → custody_records → charges
8. **Court Process** → court_proceedings

**Models Involved:** 12+ interconnected models

---

## 🔧 UPDATED IMPLEMENTATION STRATEGY

### Phase 1: Model Relationship Methods (Week 1-2) ⭐ HIGH PRIORITY

**Goal:** Make existing models talk to each other

#### 1.1 CaseModel.php Enhancements
```php
// Add these methods to CaseModel.php
public function getSuspects() // Get all suspects via case_suspects
public function getWitnesses() // Get all witnesses via case_witnesses
public function getAssignedOfficers() // Get assigned officers
public function getEvidence() // Get all evidence for case
public function getExhibits() // Get all exhibits for case
public function getStatements() // Get all statements
public function getTimeline() // Get investigation timeline
public function getTasks() // Get investigation tasks
public function getUpdates() // Get case updates
public function getStatusHistory() // Get status change history
public function addSuspect($person_id, $added_by) // Add suspect with history
public function addWitness($person_id, $added_by) // Add witness with history
public function assignOfficer($officer_id, $assigned_by) // Assign officer
public function updateStatus($new_status, $changed_by, $remarks) // Update with history
```

#### 1.2 Person.php Enhancements
```php
// Add these methods to Person.php
public function getCriminalHistory() // Get all criminal involvements
public function getAlerts() // Get active alerts
public function getAliases() // Get known aliases
public function getRelationships() // Get person relationships
public function getCasesAsSuspect() // Cases where person is suspect
public function getCasesAsWitness() // Cases where person is witness
public function getCasesAsComplainant() // Cases where person is complainant
public function checkDuplicates() // Find potential duplicates using sp_find_similar_persons
public function addToCase($case_id, $role, $added_by) // Add to case with role
```

#### 1.3 Officer.php Enhancements
```php
// Add these methods to Officer.php
public function getAssignedCases() // Get assigned cases
public function getPostingHistory() // Get posting history
public function getPromotionHistory() // Get promotion history
public function getCurrentPosting() // Get current posting
public function getDutyRoster($date_range) // Get duty schedule
public function getPatrolLogs($date_range) // Get patrol logs
public function getArrestsMade($date_range) // Get arrests made
public function getPerformanceMetrics() // Get performance data
```

#### 1.4 Evidence.php & Exhibit.php Enhancements
```php
// Evidence.php
public function getCustodyChain() // Get full custody chain
public function getCurrentCustodian() // Get current custodian
public function transferCustody($from, $to, $purpose, $transferred_by) // Transfer custody
public function getCase() // Get associated case

// Exhibit.php
public function getMovementHistory() // Get movement history
public function getCurrentLocation() // Get current location
public function recordMovement($from, $to, $moved_by, $purpose) // Record movement
public function getCase() // Get associated case
```

### Phase 2: Junction Table Models (Week 3-4) ⭐ MEDIUM PRIORITY

**Goal:** Create models for junction tables that are missing

#### 2.1 Create New Models
1. **CaseSuspect.php** - Links cases to suspects with metadata
2. **CaseWitness.php** - Links cases to witnesses with metadata
3. **CaseAssignment.php** - Links cases to assigned officers
4. **CaseUpdate.php** - Stores case progress updates
5. **CaseStatusHistory.php** - Tracks case status changes
6. **SurveillanceOfficer.php** - Links surveillance operations to officers
7. **PatrolOfficer.php** - Links patrol logs to officers

Each model should include:
- Proper foreign key relationships
- Timestamps
- Created_by/Updated_by tracking
- Validation rules

### Phase 3: Service Layer (Week 5-6) ⭐ HIGH PRIORITY

**Goal:** Create service classes for complex workflows

#### 3.1 CaseService.php
```php
class CaseService {
    public function createCase($data) // Create case with all relationships
    public function addSuspectToCase($case_id, $person_id, $added_by) // Use sp_add_suspect_to_case
    public function addWitnessToCase($case_id, $person_id, $added_by)
    public function assignOfficer($case_id, $officer_id, $assigned_by) // With notification
    public function updateCaseStatus($case_id, $new_status, $changed_by, $remarks) // With history
    public function closeCaseWorkflow($case_id, $outcome, $closed_by) // Complete closure
    public function getCaseFullDetails($case_id) // Get case with all relationships
}
```

#### 3.2 PersonService.php
```php
class PersonService {
    public function registerPerson($data) // Use sp_register_person with duplicate check
    public function checkCriminalRecord($identifiers) // Use sp_check_person_criminal_record
    public function findSimilarPersons($data) // Use sp_find_similar_persons
    public function linkPersonToCase($person_id, $case_id, $role, $linked_by)
    public function getPersonFullProfile($person_id) // Get person with all relationships
}
```

#### 3.3 EvidenceService.php
```php
class EvidenceService {
    public function registerEvidence($data) // Register with initial custody
    public function transferEvidenceCustody($evidence_id, $from, $to, $purpose, $transferred_by)
    public function verifyChainOfCustody($evidence_id) // Verify integrity
    public function getEvidenceByCaseId($case_id) // Get all evidence for case
}
```

#### 3.4 IntelligenceService.php
```php
class IntelligenceService {
    public function createIntelligenceReport($data) // Create with classification
    public function issueBulletin($data) // Issue with distribution
    public function createSurveillanceOperation($data) // Create with officer assignment
    public function processPublicTip($tip_id, $assigned_to) // Process and verify tip
    public function linkIntelligenceToCase($intelligence_id, $case_id)
}
```

#### 3.5 OperationService.php
```php
class OperationService {
    public function planOperation($data) // Plan with officer assignment
    public function deployOfficers($operation_id, $officer_ids) // Deploy with roster update
    public function recordOperationOutcome($operation_id, $data) // Record results
    public function linkOperationToCase($operation_id, $case_id)
}
```

### Phase 4: Stored Procedure Integration (Week 7-8) ⭐ HIGH PRIORITY

**Goal:** Integrate existing stored procedures into services

#### 4.1 Integrate Stored Procedures
1. **sp_add_suspect_to_case** → CaseService::addSuspectToCase()
2. **sp_check_person_criminal_record** → PersonService::checkCriminalRecord()
3. **sp_register_person** → PersonService::registerPerson()
4. **sp_find_similar_persons** → PersonService::findSimilarPersons()
5. **sp_convert_officer_to_user** → OfficerService::convertToUser()

Each integration should:
- Handle stored procedure calls properly
- Process result sets correctly
- Handle errors gracefully
- Log operations in audit_logs

### Phase 5: Controller Updates (Week 9-10) ⭐ MEDIUM PRIORITY

**Goal:** Update controllers to use new service layer

#### 5.1 Update Existing Controllers
- CaseController → Use CaseService
- PersonController → Use PersonService
- EvidenceController → Use EvidenceService
- IntelligenceController → Use IntelligenceService
- OperationsController → Use OperationService

#### 5.2 Add Missing Controller Methods
- Relationship endpoints (e.g., /cases/{id}/suspects)
- Workflow endpoints (e.g., /cases/{id}/close)
- Bulk operations (e.g., /cases/assign-multiple)

### Phase 6: Testing & Refinement (Week 11-12)

**Goal:** Test all integrations and workflows

#### 6.1 Testing Checklist
- [ ] Test all model relationship methods
- [ ] Test service layer workflows
- [ ] Test stored procedure integrations
- [ ] Verify data integrity
- [ ] Test cascade operations
- [ ] Verify audit trails
- [ ] Performance testing
- [ ] Load testing

#### 6.2 Documentation
- API documentation for new endpoints
- Service layer documentation
- Model relationship documentation
- Workflow diagrams

---

## 📊 KEY METRICS TO TRACK

### Operational Metrics
- Cases opened vs closed (by station/district/region)
- Average investigation time
- Arrest-to-charge conversion rate
- Court conviction rate
- Evidence custody chain compliance

### Resource Metrics
- Officer deployment efficiency
- Patrol coverage (geographic)
- Firearm assignment tracking
- Vehicle utilization
- Ammunition inventory levels

### Intelligence Metrics
- Intelligence reports generated
- Tip verification rate
- Surveillance operation success rate
- Informant reliability scores

### Performance Metrics
- Officer case load
- Response times (patrol incidents)
- Evidence collection compliance
- Statement recording timeliness

---

## 🎯 SUCCESS CRITERIA

### Data Integrity
- ✅ All foreign keys properly enforced
- ✅ No orphaned records
- ✅ Audit trails complete
- ✅ Cascade deletes handled properly

### Operational Efficiency
- ✅ Case workflow streamlined
- ✅ Evidence tracking automated
- ✅ Intelligence sharing enabled
- ✅ Resource allocation optimized

### Compliance
- ✅ Chain of custody maintained
- ✅ Access control enforced
- ✅ Audit logs comprehensive
- ✅ Data privacy protected

---

## 📝 NEXT STEPS

1. **Review & Validate** - Review this plan with stakeholders
2. **Database Schema Updates** - Add missing foreign keys and indexes
3. **Model Implementation** - Create/update PHP models with relationships
4. **Controller Logic** - Implement workflow logic in controllers
5. **UI Integration** - Update views to reflect relationships
6. **Testing** - Test all workflows end-to-end
7. **Training** - Train officers on new integrated system

---

**Status:** 📋 **Plan Ready for Implementation**

This plan provides a comprehensive, realistic integration of all models based on actual Ghana Police operations and workflows.
