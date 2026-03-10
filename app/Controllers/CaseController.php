<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\Complainant;
use App\Models\Suspect;
use App\Models\Witness;
use App\Models\Evidence;
use App\Models\Station;
use App\Models\Person;
use App\Models\Arrest;
use App\Models\Charge;
use App\Models\CourtProceeding;
use App\Models\Custody;
use App\Models\Bail;
use App\Services\CaseService;
use App\Services\PersonService;
use App\Services\OfficerService;
use App\Services\NotificationService;

class CaseController extends BaseController
{
    private CaseModel $caseModel;
    private CaseService $caseService;
    private PersonService $personService;
    private OfficerService $officerService;
    private NotificationService $notificationService;
    private Complainant $complainantModel;
    private Suspect $suspectModel;
    private Witness $witnessModel;
    private Evidence $evidenceModel;
    private Station $stationModel;
    private Person $personModel;
    private Bail $bailModel;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->caseService = new CaseService();
        $this->personService = new PersonService();
        $this->officerService = new OfficerService();
        $this->notificationService = new NotificationService();
        $this->complainantModel = new Complainant();
        $this->suspectModel = new Suspect();
        $this->witnessModel = new Witness();
        $this->evidenceModel = new Evidence();
        $this->stationModel = new Station();
        $this->personModel = new Person();
        $this->bailModel = new Bail();
    }
    
    /**
     * Display list of all cases
     */
    public function index(): string
    {
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;
        
        $result = $this->caseModel->paginate($page, 25);
        $cases = $result['data'];
        $pagination = $result;
        
        return $this->view('cases/index', [
            'title' => 'Case Management',
            'cases' => $cases,
            'pagination' => $pagination,
            'status' => $status,
            'priority' => $priority
        ]);
    }
    
    /**
     * Display specialized units dashboard
     */
    public function specializedUnits(): string
    {
        return $this->view('cases/specialized_units', [
            'title' => 'Specialized Units Dashboard'
        ]);
    }
    
    /**
     * Display DOVVSU workflow guide
     */
    public function dovvsuWorkflow(): string
    {
        return $this->view('cases/dovvsu_workflow', [
            'title' => 'DOVVSU Case Workflow'
        ]);
    }
    
    /**
     * Show case creation form
     */
    public function create(): string
    {
        $db = \App\Config\Database::getConnection();
        
        // Get all regions
        $stmt = $db->query("SELECT * FROM regions ORDER BY region_name");
        $regions = $stmt->fetchAll();
        
        // Get all divisions with region info
        $stmt = $db->query("SELECT division.*, r.region_name FROM divisions division LEFT JOIN regions r ON division.region_id = r.id ORDER BY r.region_name, division.division_name");
        $divisions = $stmt->fetchAll();
        
        // Get all districts with division info
        $stmt = $db->query("SELECT d.*, division.division_name FROM districts d LEFT JOIN divisions division ON d.division_id = division.id ORDER BY division.division_name, d.district_name");
        $districts = $stmt->fetchAll();
        
        // Get all stations with district info
        $stmt = $db->query("SELECT s.*, d.district_name FROM stations s LEFT JOIN districts d ON s.district_id = d.id ORDER BY d.district_name, s.station_name");
        $stations = $stmt->fetchAll();
        
        return $this->view('cases/create', [
            'title' => 'Register New Case',
            'regions' => $regions,
            'divisions' => $divisions,
            'districts' => $districts,
            'stations' => $stations
        ]);
    }
    
    /**
     * Store new case - WORKFLOW 1: Complete Case Registration
     * 
     * This implements the full case registration workflow:
     * 1. Handle complainant (check existing or create new person)
     * 2. Create case with all details
     * 3. Auto-assign officer based on workload
     * 4. Send notifications
     * 5. Log all actions
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/create');
        }
        
        // Validate required fields
        $errors = $this->validate($_POST, [
            'description' => 'required|min:10',
            'station_id' => 'required',
            'incident_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('/cases/create');
        }
        
        try {
            $db = \App\Config\Database::getConnection();
            $db->beginTransaction();
            
            // STEP 1: Handle Complainant
            $complainantId = $this->handleComplainant($_POST);
            
            if (!$complainantId) {
                throw new \Exception('Failed to create complainant. Please provide complainant details.');
            }
            
            // STEP 2: Generate case number and prepare data
            $caseNumber = $this->generateCaseNumber((int)$_POST['station_id']);
            
            // Check if new columns exist in database
            $db = \App\Config\Database::getConnection();
            $stmt = $db->query("SHOW COLUMNS FROM cases LIKE 'case_origin'");
            $newColumnsExist = $stmt->rowCount() > 0;
            
            $caseData = [
                'case_number' => $caseNumber,
                'case_type' => $_POST['case_type'] ?? 'Complaint',
                'case_priority' => $_POST['case_priority'] ?? 'Medium',
                'description' => $_POST['description'],
                'incident_location' => $_POST['incident_location'] ?? null,
                'incident_date' => $_POST['incident_date'],
                'complainant_id' => $complainantId,
                'station_id' => (int)$_POST['station_id'],
                'region_id' => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
                'division_id' => !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null,
                'district_id' => !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null,
                'status' => 'Open',
                'created_by' => auth_id()
            ];
            
            // Add new fields only if migration has been run
            if ($newColumnsExist) {
                $caseData['case_origin'] = $_POST['case_origin'] ?? null;
                $caseData['case_category'] = $_POST['case_category'] ?? null;
                $caseData['complainant_present'] = $_POST['complainant_present'] ?? 'Yes';
                $caseData['arrest_made'] = $_POST['arrest_made'] ?? 'No';
                $caseData['specialized_unit'] = $_POST['specialized_unit'] ?? null;
                $caseData['is_dovvsu_case'] = isset($_POST['is_dovvsu_case']) ? 1 : 0;
                $caseData['is_intelligence_led'] = ($_POST['case_origin'] ?? '') === 'Intelligence' ? 1 : 0;
                $caseData['is_exhibit_based'] = ($_POST['case_origin'] ?? '') === 'Exhibit-Based' ? 1 : 0;
            }
            
            // STEP 3: Create case
            $caseId = $this->caseModel->create($caseData);
            
            if (!$caseId) {
                throw new \Exception('Failed to create case record');
            }
            
            // STEP 4: Auto-assign officer if requested
            $assignedOfficer = null;
            if (!empty($_POST['auto_assign_officer']) && $_POST['auto_assign_officer'] === '1') {
                $officer = $this->officerService->findBestOfficerForAssignment(
                    (int)$_POST['station_id'],
                    10 // max workload threshold
                );
                
                if ($officer) {
                    $assigned = $this->caseService->assignOfficerToCase(
                        $caseId,
                        $officer['id'],
                        auth_id(),
                        'Lead Investigator'
                    );
                    
                    if ($assigned) {
                        $assignedOfficer = $officer;
                        
                        // STEP 5: Send notification
                        $this->notificationService->notifyCaseAssignment(
                            $officer['id'],
                            $caseId
                        );
                        
                        logger("Officer {$officer['service_number']} auto-assigned to case {$caseNumber}");
                    }
                }
            }
            
            $db->commit();
            
            // Log success
            logger("Case created: {$caseNumber} (ID: {$caseId}) by user " . auth_id());
            
            // Build success message
            $message = "Case registered successfully. Case Number: {$caseNumber}";
            if ($assignedOfficer) {
                $officerName = $assignedOfficer['first_name'] . ' ' . $assignedOfficer['last_name'];
                $message .= " | Assigned to: {$officerName} ({$assignedOfficer['rank_name']})";
            }
            
            $this->setFlash('success', $message);
            $this->redirect('/cases/' . $caseId);
            
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            logger("Failed to create case: " . $e->getMessage(), 'error');
            logger("Stack trace: " . $e->getTraceAsString(), 'error');
            $this->setFlash('error', 'Failed to register case: ' . $e->getMessage());
            $_SESSION['old'] = $_POST;
            $this->redirect('/cases/create');
        }
    }
    
    /**
     * Handle complainant - check existing or create new person
     * 
     * Supports three modes:
     * 1. Use existing complainant (existing_complainant_id)
     * 2. Create complainant from existing person (complainant_person_id)
     * 3. Create new person and complainant (complainant details)
     */
    private function handleComplainant(array $data): ?int
    {
        // Mode 1: Use existing complainant
        if (!empty($data['existing_complainant_id'])) {
            return (int)$data['existing_complainant_id'];
        }
        
        // Mode 2: Create complainant from existing person
        if (!empty($data['complainant_person_id'])) {
            $complainantData = [
                'person_id' => (int)$data['complainant_person_id'],
                'complainant_type' => $data['complainant_type'] ?? 'Individual',
                'organization_name' => $data['organization_name'] ?? null
            ];
            
            return $this->complainantModel->create($complainantData);
        }
        
        // Mode 3: Create new person and complainant
        if (!empty($data['complainant_first_name']) && !empty($data['complainant_last_name'])) {
            // Check for similar persons to avoid duplicates
            $similar = $this->personService->findSimilarPersons(
                $data['complainant_first_name'],
                $data['complainant_last_name'],
                $data['complainant_dob'] ?? null,
                $data['complainant_contact'] ?? null
            );
            
            // If high match found, use existing person
            if (!empty($similar) && isset($similar[0]['match_score']) && $similar[0]['match_score'] >= 90) {
                $personId = $similar[0]['id'];
                logger("Using existing person (ID: {$personId}) as complainant - match score: {$similar[0]['match_score']}");
            } else {
                // Create new person
                $personData = [
                    'first_name' => $data['complainant_first_name'],
                    'middle_name' => $data['complainant_middle_name'] ?? null,
                    'last_name' => $data['complainant_last_name'],
                    'gender' => $data['complainant_gender'] ?? null,
                    'date_of_birth' => $data['complainant_dob'] ?? null,
                    'contact' => $data['complainant_contact'] ?? null,
                    'email' => $data['complainant_email'] ?? null,
                    'address' => $data['complainant_address'] ?? null,
                    'ghana_card_number' => $data['complainant_ghana_card'] ?? null
                ];
                
                $personId = $this->personModel->create($personData);
                
                if (!$personId) {
                    throw new \Exception('Failed to create person record for complainant');
                }
                
                logger("New person created (ID: {$personId}) for complainant");
            }
            
            // Create complainant record
            $complainantData = [
                'person_id' => $personId,
                'complainant_type' => $data['complainant_type'] ?? 'Individual',
                'organization_name' => $data['organization_name'] ?? null
            ];
            
            return $this->complainantModel->create($complainantData);
        }
        
        return null;
    }
    
    /**
     * Generate unique case number based on station and year
     * Format: STATION_CODE-YEAR-SEQUENCE (e.g., ACC-2025-0001)
     */
    private function generateCaseNumber(int $stationId): string
    {
        $station = $this->stationModel->find($stationId);
        $stationCode = $station['station_code'] ?? 'GH';
        $year = date('Y');
        
        // Get count of cases this year for this station
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM cases 
            WHERE station_id = ? 
            AND YEAR(created_at) = ?
        ");
        $stmt->execute([$stationId, $year]);
        $result = $stmt->fetch();
        $count = ($result['count'] ?? 0) + 1;
        
        return $stationCode . '-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Show case details - ENHANCED with Phase 1 integration
     */
    public function show(int $id): string
    {
        // Use enhanced service method to get EVERYTHING in one call
        $fullCase = $this->caseService->getCaseFullDetails($id);
        
        if (!$fullCase) {
            $this->setFlash('error', 'Case not found');
            $this->redirect('/cases');
        }
        
        // Get complainant details
        $complainant = $fullCase['complainant_id'] ? $this->caseService->getComplainantDetails($fullCase['complainant_id']) : null;
        
        // Get combined timeline (updates + status changes)
        $timeline = $this->caseService->getCaseTimeline($id);
        
        // Get crimes for this case
        $caseCrimeModel = new \App\Models\CaseCrime();
        $crimes = $caseCrimeModel->getByCase($id);
        
        // Get referrals for this case
        $caseReferralModel = new \App\Models\CaseReferral();
        $referrals = $caseReferralModel->getByCaseId($id);
        
        // Get arrests for this case
        $arrestModel = new Arrest();
        $arrests = $arrestModel->getByCaseId($id);
        
        // Get charges for this case
        $chargeModel = new Charge();
        $charges = $chargeModel->getByCaseId($id);
        
        // Get court proceedings for this case
        $courtModel = new CourtProceeding();
        $courtProceedings = $courtModel->getByCaseId($id);
        
        // Get custody records for suspects in this case
        $custodyModel = new Custody();
        $custodyRecords = $custodyModel->getByCaseId($id);
        
        // Get bail records for this case
        $bailRecords = $this->bailModel->getByCaseId($id);
        
        return $this->view('cases/view', [
            'title' => 'Case Details - ' . $fullCase['case_number'],
            'case' => $fullCase,
            'complainant' => $complainant,
            'suspects' => $fullCase['suspects'] ?? [],
            'witnesses' => $fullCase['witnesses'] ?? [],
            'evidence' => $fullCase['evidence'] ?? [],
            'exhibits' => $fullCase['exhibits'] ?? [],
            'statements' => $fullCase['statements'] ?? [],
            'timeline' => $timeline,
            'tasks' => $fullCase['tasks'] ?? [],
            'updates' => $fullCase['updates'] ?? [],
            'assigned_officers' => $fullCase['assigned_officers'] ?? [],
            'crimes' => $crimes,
            'referrals' => $referrals,
            'status_history' => $fullCase['status_history'] ?? [],
            'arrests' => $arrests,
            'charges' => $charges,
            'court_proceedings' => $courtProceedings,
            'custody_records' => $custodyRecords,
            'bail_records' => $bailRecords
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $case = $this->caseModel->find($id);
        
        if (!$case) {
            $this->setFlash('error', 'Case not found');
            $this->redirect('/cases');
        }
        
        $stations = $this->stationModel->all();
        
        return $this->view('cases/edit', [
            'title' => 'Edit Case',
            'case' => $case,
            'stations' => $stations
        ]);
    }
    
    /**
     * Update case
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $id . '/edit');
        }
        
        $data = [
            'case_type' => $_POST['case_type'] ?? null,
            'case_priority' => $_POST['case_priority'] ?? null,
            'description' => $_POST['description'] ?? '',
            'incident_location' => $_POST['location'] ?? null,
            'status' => $_POST['status'] ?? null
        ];
        
        $errors = $this->validate($data, [
            'description' => 'required|min:10'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/cases/' . $id . '/edit');
        }
        
        $success = $this->caseModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Case updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update case');
        }
        
        $this->redirect('/cases/' . $id);
    }
    
    /**
     * Add suspect to case (known person or unknown suspect)
     */
    public function addSuspect(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $suspectType = $_POST['suspect_type'] ?? 'known';
        
        // Validate based on suspect type
        if ($suspectType === 'known') {
            $personId = $_POST['person_id'] ?? null;
            if (!$personId) {
                $this->setFlash('error', 'Please select a person for known suspect');
                $this->redirect('/cases/' . $caseId);
            }
        } else {
            $description = $_POST['unknown_description'] ?? null;
            if (!$description) {
                $this->setFlash('error', 'Please provide a description for unknown suspect');
                $this->redirect('/cases/' . $caseId);
            }
        }
        
        try {
            $data = [
                'suspect_type' => $suspectType,
                'person_id' => $_POST['person_id'] ?? null,
                'current_status' => $_POST['current_status'] ?? 'Identified',
                'alias' => $_POST['alias'] ?? null,
                'last_known_location' => $_POST['last_known_location'] ?? null,
                'arrest_date' => $_POST['arrest_date'] ?? null,
                'identifying_marks' => $_POST['identifying_marks'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                // Unknown suspect fields
                'unknown_description' => $_POST['unknown_description'] ?? null,
                'estimated_age' => $_POST['estimated_age'] ?? null,
                'unknown_gender' => $_POST['unknown_gender'] ?? null,
                'height_build' => $_POST['height_build'] ?? null,
                'complexion' => $_POST['complexion'] ?? null,
                'clothing' => $_POST['clothing'] ?? null
            ];
            
            $suspectId = $this->caseService->addSuspectToCase($caseId, $data);
            
            $this->setFlash('success', 'Suspect added to case successfully');
            $this->redirect('/cases/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add suspect: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId);
        }
    }
    
    /**
     * Remove suspect from case (soft delete)
     */
    public function removeSuspect(int $caseId, int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        try {
            // Get suspect details
            $suspect = $this->suspectModel->find($suspectId);
            if (!$suspect) {
                $this->json(['success' => false, 'message' => 'Suspect not found'], 404);
                return;
            }
            
            // Check if suspect status has changed from "Suspect"
            if ($suspect['current_status'] !== 'Suspect') {
                $this->json([
                    'success' => false, 
                    'message' => 'Cannot remove suspect with status "' . $suspect['current_status'] . '". Only suspects with status "Suspect" can be removed.'
                ], 422);
                return;
            }
            
            // Check for foreign key relationships
            $db = \App\Config\Database::getConnection();
            
            // Check arrests
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM arrests WHERE suspect_id = ?");
            $stmt->execute([$suspectId]);
            if ($stmt->fetch()['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot remove suspect who has arrest records'], 422);
                return;
            }
            
            // Check charges
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM charges WHERE suspect_id = ?");
            $stmt->execute([$suspectId]);
            if ($stmt->fetch()['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot remove suspect who has been charged'], 422);
                return;
            }
            
            // Check statements
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM statements WHERE suspect_id = ?");
            $stmt->execute([$suspectId]);
            if ($stmt->fetch()['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot remove suspect who has given statements'], 422);
                return;
            }
            
            // Check bail records
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bail_records WHERE suspect_id = ?");
            $stmt->execute([$suspectId]);
            if ($stmt->fetch()['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot remove suspect who has bail records'], 422);
                return;
            }
            
            // Check custody records
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM custody_records WHERE suspect_id = ?");
            $stmt->execute([$suspectId]);
            if ($stmt->fetch()['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot remove suspect who has custody records'], 422);
                return;
            }
            
            // Soft delete: Update case_suspects with removal info
            $stmt = $db->prepare("
                UPDATE case_suspects 
                SET removed_at = NOW(),
                    removed_by = ?,
                    removal_reason = ?
                WHERE case_id = ? AND suspect_id = ?
            ");
            $stmt->execute([auth_id(), 'Removed from case', $caseId, $suspectId]);
            
            logger("Suspect {$suspectId} soft-deleted from case {$caseId}", 'info');
            
            $this->json(['success' => true, 'message' => 'Suspect removed from case successfully']);
        } catch (\Exception $e) {
            logger("Error removing suspect: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to remove suspect: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Show upgrade form for unknown suspect
     */
    public function upgradeSuspect(int $caseId, int $suspectId): string
    {
        $suspect = $this->suspectModel->find($suspectId);
        
        if (!$suspect) {
            $this->setFlash('error', 'Suspect not found');
            $this->redirect('/cases/' . $caseId);
        }
        
        // Get case details
        $case = $this->caseModel->find($caseId);
        
        return $this->view('cases/upgrade-suspect', [
            'title' => 'Upgrade Suspect to Known Person',
            'case' => $case,
            'suspect' => $suspect
        ]);
    }
    
    /**
     * Process upgrade of unknown suspect to known person
     */
    public function processUpgrade(int $caseId, int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $personId = $_POST['person_id'] ?? null;
        
        if (!$personId) {
            $this->setFlash('error', 'Please select a person');
            $this->redirect('/cases/' . $caseId . '/suspects/' . $suspectId . '/upgrade');
        }
        
        try {
            // Update suspect with person_id
            $this->suspectModel->update($suspectId, [
                'person_id' => $personId,
                'current_status' => 'Identified'
            ]);
            
            $this->setFlash('success', 'Suspect upgraded to known person successfully');
            $this->redirect('/cases/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to upgrade suspect: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId . '/suspects/' . $suspectId . '/upgrade');
        }
    }
    
    /**
     * Update suspect status via AJAX
     */
    public function updateSuspectStatus(int $caseId, int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? null;
        
        if (!$status) {
            $this->json(['success' => false, 'message' => 'Status is required'], 400);
        }
        
        try {
            $this->suspectModel->update($suspectId, [
                'current_status' => $status
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show edit form for suspect
     */
    public function editSuspect(int $caseId, int $suspectId): string
    {
        $suspect = $this->suspectModel->find($suspectId);
        
        if (!$suspect) {
            $this->setFlash('error', 'Suspect not found');
            $this->redirect('/cases/' . $caseId);
        }
        
        $case = $this->caseModel->find($caseId);
        
        return $this->view('cases/edit-suspect', [
            'title' => 'Edit Suspect Details',
            'case' => $case,
            'suspect' => $suspect
        ]);
    }
    
    /**
     * Update suspect details
     */
    public function updateSuspect(int $caseId, int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $data = [
                'current_status' => $_POST['current_status'] ?? null,
                'alias' => $_POST['alias'] ?? null,
                'last_known_location' => $_POST['last_known_location'] ?? null,
                'arrest_date' => $_POST['arrest_date'] ?? null,
                'identifying_marks' => $_POST['identifying_marks'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'unknown_description' => $_POST['unknown_description'] ?? null,
                'estimated_age' => $_POST['estimated_age'] ?? null,
                'unknown_gender' => $_POST['unknown_gender'] ?? null,
                'height_build' => $_POST['height_build'] ?? null,
                'complexion' => $_POST['complexion'] ?? null,
                'clothing' => $_POST['clothing'] ?? null
            ];
            
            $this->suspectModel->update($suspectId, $data);
            
            $this->setFlash('success', 'Suspect details updated successfully');
            $this->redirect('/cases/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update suspect: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId . '/suspects/' . $suspectId . '/edit');
        }
    }
    
    /**
     * Add witness to case - ENHANCED with Phase 2 integration
     */
    public function addWitness(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $personId = $_POST['person_id'] ?? null;
        $witnessType = $_POST['witness_type'] ?? 'Eye Witness';
        
        if (!$personId) {
            $this->setFlash('error', 'Please select a person');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            // Create witness record first
            $witnessId = $this->witnessModel->create([
                'person_id' => $personId,
                'witness_type' => $witnessType
            ]);
            
            // Use enhanced service method to add witness to case
            $success = $this->caseService->addWitnessToCase($caseId, $witnessId, auth_id());
            
            if ($success) {
                $this->setFlash('success', 'Witness added to case successfully');
            } else {
                $this->setFlash('error', 'Witness is already linked to this case');
            }
            
            $this->redirect('/cases/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add witness: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId);
        }
    }
    
    /**
     * Remove witness from case
     */
    public function removeWitness(int $caseId, int $witnessId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $this->witnessModel->unlinkFromCase($witnessId, $caseId);
            $this->setFlash('success', 'Witness removed from case successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to remove witness: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Upload evidence to case
     */
    public function uploadEvidence(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        if (!isset($_FILES['evidence_file']) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a file to upload');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $file = $_FILES['evidence_file'];
            $uploadDir = __DIR__ . '/../../storage/evidence/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('evidence_') . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to upload file');
            }
            
            // Generate evidence number
            $evidenceNumber = 'EV-' . $caseId . '-' . time();
            
            // Save to database
            $data = [
                'case_id' => $caseId,
                'evidence_type' => $_POST['evidence_type'] ?? 'Physical',
                'evidence_number' => $evidenceNumber,
                'description' => $_POST['description'] ?? '',
                'file_path' => 'evidence/' . $filename,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'collection_date' => $_POST['collection_date'] ?? date('Y-m-d'),
                'collection_location' => $_POST['collection_location'] ?? null,
                'uploaded_by' => auth_id()
            ];
            
            $this->evidenceModel->create($data);
            
            $this->setFlash('success', 'Evidence uploaded successfully. Evidence Number: ' . $evidenceNumber);
            $this->redirect('/cases/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to upload evidence: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId);
        }
    }
    
    /**
     * Download evidence file
     */
    public function downloadEvidence(int $caseId, int $evidenceId): void
    {
        $evidence = $this->evidenceModel->find($evidenceId);
        
        if (!$evidence || $evidence['case_id'] != $caseId) {
            $this->setFlash('error', 'Evidence not found');
            $this->redirect('/cases/' . $caseId);
        }
        
        $filepath = __DIR__ . '/../../storage/' . $evidence['file_path'];
        
        if (!file_exists($filepath)) {
            $this->setFlash('error', 'Evidence file not found');
            $this->redirect('/cases/' . $caseId);
        }
        
        // Set headers for download
        header('Content-Type: ' . $evidence['mime_type']);
        header('Content-Disposition: attachment; filename="' . basename($evidence['file_path']) . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit;
    }
    
    /**
     * Delete evidence
     */
    public function deleteEvidence(int $caseId, int $evidenceId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $evidence = $this->evidenceModel->find($evidenceId);
            
            if ($evidence && $evidence['case_id'] == $caseId) {
                // Delete file
                $filepath = __DIR__ . '/../../storage/' . $evidence['file_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                
                // Delete from database
                $this->evidenceModel->delete($evidenceId);
                
                $this->setFlash('success', 'Evidence deleted successfully');
            } else {
                $this->setFlash('error', 'Evidence not found');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to delete evidence: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Assign officer to case
     */
    public function assignOfficer(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $officerId = (int)($_POST['officer_id'] ?? 0);
        $role = $_POST['role'] ?? 'Investigator';
        
        if (!$officerId) {
            $this->setFlash('error', 'Please select an officer');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $caseAssignmentModel = new \App\Models\CaseAssignment();
            
            if ($caseAssignmentModel->exists($caseId, $officerId)) {
                $this->setFlash('warning', 'Officer is already assigned to this case');
                $this->redirect('/cases/' . $caseId);
            }
            
            $caseAssignmentModel->assignOfficer($caseId, $officerId, auth_id(), $role);
            $this->setFlash('success', 'Officer assigned successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to assign officer: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Reassign case to another officer
     */
    public function reassignOfficer(int $caseId, int $assignmentId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $newOfficerId = (int)($_POST['new_officer_id'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';
        
        if (!$newOfficerId) {
            $this->json(['success' => false, 'message' => 'Please select a new officer'], 400);
            return;
        }
        
        try {
            $caseAssignmentModel = new \App\Models\CaseAssignment();
            
            // Get current assignment details
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("SELECT assigned_to FROM case_assignments WHERE id = ?");
            $stmt->execute([$assignmentId]);
            $currentAssignment = $stmt->fetch();
            
            if (!$currentAssignment) {
                $this->json(['success' => false, 'message' => 'Assignment not found'], 404);
                return;
            }
            
            // Reassign to new officer
            $caseAssignmentModel->reassignCase($caseId, $currentAssignment['assigned_to'], $newOfficerId, auth_id());
            
            // Add case update using CaseModel
            $caseModel = new \App\Models\CaseModel();
            $caseModel->addUpdate($caseId, "Case reassigned to new officer. Reason: " . ($remarks ?: 'Not specified'), auth_id());
            
            $this->json(['success' => true, 'message' => 'Officer reassigned successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Complete assignment
     */
    public function completeAssignment(int $caseId, int $assignmentId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $caseAssignmentModel = new \App\Models\CaseAssignment();
            $caseAssignmentModel->updateStatus($assignmentId, 'Completed');
            $this->setFlash('success', 'Assignment marked as completed');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to complete assignment: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Add statement to case
     */
    public function addStatement(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        // Helper function to convert empty strings to null
        $toNullIfEmpty = function($value) {
            return (empty($value) || $value === '') ? null : $value;
        };
        
        // Handle statement_type_override for rewrite (when field is disabled)
        $statementType = $_POST['statement_type'] ?? $_POST['statement_type_override'] ?? 'Complainant';
        
        $data = [
            'case_id' => $caseId,
            'statement_type' => $statementType,
            'complainant_id' => $toNullIfEmpty($_POST['complainant_id'] ?? null),
            'suspect_id' => $toNullIfEmpty($_POST['suspect_id'] ?? null),
            'witness_id' => $toNullIfEmpty($_POST['witness_id'] ?? null),
            'statement_text' => $_POST['statement_text'] ?? '',
            'recorded_by' => auth_id(),
            'parent_statement_id' => $toNullIfEmpty($_POST['parent_statement_id'] ?? null)
        ];
        
        try {
            $statementId = $this->caseService->addStatement($data);
            
            // If this is a rewrite (has parent), mark parent as superseded
            if ($data['parent_statement_id']) {
                $this->caseService->markStatementSuperseded($data['parent_statement_id']);
            }
            
            $this->setFlash('success', 'Statement recorded successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to record statement: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Get single statement details
     */
    public function getStatement(int $caseId, int $statementId): void
    {
        try {
            $statement = $this->caseService->getStatementDetails($statementId, $caseId);
            
            if (!$statement) {
                $this->json(['success' => false, 'message' => 'Statement not found'], 404);
                return;
            }
            
            $this->json(['success' => true, 'statement' => $statement]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Cancel a statement
     */
    public function cancelStatement(int $caseId, int $statementId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $reason = $_POST['cancellation_reason'] ?? '';
        
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Cancellation reason is required'], 400);
            return;
        }
        
        try {
            $this->caseService->cancelStatement($statementId, $caseId, $reason, auth_id());
            $this->json(['success' => true, 'message' => 'Statement cancelled successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Add crime(s) to case - supports multiple crimes
     */
    public function addCrime(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $crimeCategoryIds = $_POST['crime_category_ids'] ?? [];
        $description = $_POST['crime_description'] ?? '';
        $crimeDate = $_POST['crime_date'] ?? null;
        $crimeLocation = $_POST['crime_location'] ?? '';
        
        if (empty($crimeCategoryIds) || !is_array($crimeCategoryIds)) {
            $this->setFlash('error', 'Please select at least one crime category');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $caseCrimeModel = new \App\Models\CaseCrime();
            $addedCount = 0;
            
            foreach ($crimeCategoryIds as $crimeCategoryId) {
                $crimeCategoryId = (int)$crimeCategoryId;
                if ($crimeCategoryId > 0) {
                    $caseCrimeModel->addToCase($caseId, $crimeCategoryId, auth_id());
                    $addedCount++;
                }
            }
            
            if ($addedCount > 0) {
                $this->setFlash('success', $addedCount . ' crime(s) added to case successfully');
            } else {
                $this->setFlash('warning', 'No crimes were added');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add crimes: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Delete crime from case
     */
    public function deleteCrime(int $caseId, int $crimeId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $caseCrimeModel = new \App\Models\CaseCrime();
            $caseCrimeModel->removeFromCase($crimeId);
            $this->setFlash('success', 'Crime removed from case');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to remove crime: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Refer case to another station/unit
     */
    public function referCase(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $toLevel = $_POST['to_level'] ?? '';
        $toStationId = (int)($_POST['to_station_id'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';
        
        if (empty($toLevel) || empty($remarks)) {
            $this->setFlash('error', 'Please fill in all required fields');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $caseReferralModel = new \App\Models\CaseReferral();
            $caseReferralModel->createReferral($caseId, $toLevel, $toStationId, $remarks, auth_id());
            
            $this->setFlash('success', 'Case referred successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to refer case: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId);
    }
}
