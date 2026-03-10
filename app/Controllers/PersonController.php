<?php

namespace App\Controllers;

use App\Models\Person;
use App\Services\PersonService;
use App\Models\SensitiveDataAccessLog;

class PersonController extends BaseController
{
    private Person $personModel;
    private PersonService $personService;
    
    public function __construct()
    {
        $this->personModel = new Person();
        $this->personService = new PersonService();
    }
    
    /**
     * Display list of all persons
     */
    public function index(): string
    {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $wanted = isset($_GET['wanted']) ? (bool)$_GET['wanted'] : null;
        
        if ($search) {
            $persons = $this->personModel->search($search);
            $pagination = null;
        } else {
            $result = $this->personModel->paginate($page, 25);
            $persons = $result['data'];
            $pagination = $result;
        }
        
        return $this->view('persons/index', [
            'title' => 'Person Registry',
            'persons' => $persons,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }
    
    /**
     * AJAX person search for autocomplete
     */
    public function search(): void
    {
        $keyword = $_GET['q'] ?? '';
        $results = [];
        
        if ($keyword && strlen($keyword) >= 2) {
            $results = $this->personModel->search($keyword);
            
            // Format results for JSON response
            $persons = array_map(function($person) {
                return [
                    'id' => $person['id'],
                    'full_name' => trim($person['first_name'] . ' ' . ($person['middle_name'] ?? '') . ' ' . $person['last_name']),
                    'ghana_card_number' => $person['ghana_card_number'] ?? null,
                    'phone_number' => $person['contact'] ?? null
                ];
            }, $results);
            
            $this->json(['success' => true, 'persons' => $persons]);
        } else {
            $this->json(['success' => false, 'persons' => []]);
        }
    }
    
    /**
     * AJAX create person from modal
     */
    public function ajaxCreate(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'ghana_card_number' => $_POST['ghana_card_number'] ?? null,
            'phone_number' => $_POST['phone_number'] ?? $_POST['contact'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? 'Male',
            'address' => $_POST['address'] ?? null
        ];
        
        try {
            $personId = $this->personModel->create($data);
            $person = $this->personModel->find($personId);
            
            $this->json([
                'success' => true,
                'message' => 'Person registered successfully',
                'person' => [
                    'id' => $person['id'],
                    'full_name' => trim($person['first_name'] . ' ' . ($person['middle_name'] ?? '') . ' ' . $person['last_name']),
                    'ghana_card_number' => $person['ghana_card_number'] ?? null,
                    'phone_number' => $person['phone_number'] ?? null
                ]
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show person registration form
     */
    public function create(): string
    {
        return $this->view('persons/register', [
            'title' => 'Register Person'
        ]);
    }
    
    /**
     * Store new person
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/persons/create');
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'gender' => $_POST['gender'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'contact' => $_POST['contact'] ?? null,
            'email' => $_POST['email'] ?? null,
            'alternative_contact' => $_POST['alternative_contact'] ?? null,
            'address' => $_POST['address'] ?? null,
            'ghana_card' => $_POST['ghana_card_number'] ?? null,
            'passport' => $_POST['passport_number'] ?? null,
            'drivers_license' => $_POST['drivers_license'] ?? null
        ];
        
        // Convert empty strings to NULL for unique fields to prevent duplicate constraint violations
        if (empty(trim($data['ghana_card'] ?? ''))) {
            $data['ghana_card'] = null;
        }
        if (empty(trim($data['passport'] ?? ''))) {
            $data['passport'] = null;
        }
        if (empty(trim($data['drivers_license'] ?? ''))) {
            $data['drivers_license'] = null;
        }
        
        // Validate required fields
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/persons/create');
        }
        
        // Register person with duplicate detection
        $result = $this->personService->registerPerson($data);
        
        if ($result['is_duplicate']) {
            if ($result['person_id']) {
                $this->setFlash('warning', $result['message']);
                $this->redirect('/persons/' . $result['person_id']);
            } else {
                // Duplicate detected but couldn't find the existing person
                $this->setFlash('error', $result['message'] . '. Please search for the existing person.');
                $this->redirect('/persons');
            }
        } else {
            $this->setFlash('success', 'Person registered successfully');
            $this->redirect('/persons/' . $result['person_id']);
        }
    }
    
    /**
     * Show person profile - ENHANCED with Phase 1 integration
     */
    public function show(int $id): string
    {
        // Use enhanced service method to get complete profile
        $profile = $this->personService->getPersonFullProfile($id);
        
        if (!$profile) {
            $this->setFlash('error', 'Person not found');
            $this->redirect('/persons');
        }
        
        // Get relationship types for dropdown
        $relationshipTypes = \App\Models\PersonRelationship::getRelationshipTypes();
        
        return $this->view('persons/profile', [
            'title' => 'Person Profile - ' . trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')),
            'person' => $profile,
            'criminal_history' => $profile['criminal_history'] ?? [],
            'alerts' => $profile['alerts'] ?? [],
            'aliases' => $profile['aliases'] ?? [],
            'relationships' => $profile['relationships'] ?? [],
            'cases_as_suspect' => $profile['cases_as_suspect'] ?? [],
            'cases_as_witness' => $profile['cases_as_witness'] ?? [],
            'cases_as_complainant' => $profile['cases_as_complainant'] ?? [],
            'relationship_types' => $relationshipTypes
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $person = $this->personModel->find($id);
        
        if (!$person) {
            $this->setFlash('error', 'Person not found');
            $this->redirect('/persons');
        }
        
        return $this->view('persons/edit', [
            'title' => 'Edit Person',
            'person' => $person
        ]);
    }
    
    /**
     * Update person
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/persons/' . $id . '/edit');
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'gender' => $_POST['gender'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'contact' => $_POST['contact'] ?? null,
            'email' => $_POST['email'] ?? null,
            'alternative_contact' => $_POST['alternative_contact'] ?? null,
            'address' => $_POST['address'] ?? null,
            'ghana_card_number' => $_POST['ghana_card_number'] ?? null,
            'passport_number' => $_POST['passport_number'] ?? null,
            'drivers_license' => $_POST['drivers_license'] ?? null
        ];
        
        // Convert empty strings to NULL for unique fields to prevent duplicate constraint violations
        if (empty(trim($data['ghana_card_number'] ?? ''))) {
            $data['ghana_card_number'] = null;
        }
        if (empty(trim($data['passport_number'] ?? ''))) {
            $data['passport_number'] = null;
        }
        if (empty(trim($data['drivers_license'] ?? ''))) {
            $data['drivers_license'] = null;
        }
        
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/persons/' . $id . '/edit');
        }
        
        $success = $this->personModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Person updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update person');
        }
        
        $this->redirect('/persons/' . $id);
    }
    
    /**
     * Crime check search page or direct person check - WORKFLOW 2: Person Background Check
     * 
     * This implements comprehensive background check workflow:
     * 1. Search/identify person
     * 2. Get complete profile with all related data
     * 3. Display criminal history, alerts, cases
     * 4. Log access for audit trail
     */
    public function crimeCheck(?int $personId = null): string
    {
        $profile = null;
        $searchPerformed = false;
        $result = null; // Initialize result for view
        
        // Check for ID parameter in URL (for multiple matches selection)
        if (!$personId && isset($_GET['id'])) {
            $personId = (int)$_GET['id'];
        }
        
        // If person ID provided, perform direct background check
        if ($personId !== null) {
            $profile = $this->personService->getPersonFullProfile($personId);
            
            if (!$profile) {
                $this->setFlash('error', 'Person not found');
                $this->redirect('/persons');
            }
            
            $searchPerformed = true;
            
            // Log access to sensitive data
            $this->logSensitiveAccess('persons', $personId, 'VIEW', 'Background check performed');
            
            logger("Background check performed on person ID {$personId} by user " . auth_id());
        }
        // If POST request, perform search-based background check
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
            $identifier = trim($_POST['identifier'] ?? '');
            
            if (empty($identifier)) {
                $this->setFlash('error', 'Please enter a unique identifier');
                $this->redirect('/persons/crime-check');
            }
            
            $searchPerformed = true;
            
            // Try to identify the identifier type and search accordingly
            $searchData = [
                'ghana_card' => null,
                'contact' => null,
                'passport' => null,
                'drivers_license' => null,
                'first_name' => null,
                'last_name' => null
            ];
            
            // Ghana Card pattern: GHA-XXXXXXXXX-X
            if (preg_match('/^GHA-\d{9}-\d$/', strtoupper($identifier))) {
                $searchData['ghana_card'] = strtoupper($identifier);
            }
            // Phone pattern: 0XX XXX XXXX or 0XXXXXXXXX
            elseif (preg_match('/^0\d{2} ?\d{3} ?\d{4}$/', $identifier)) {
                $searchData['contact'] = preg_replace('/\s+/', '', $identifier);
            }
            // Passport pattern (letters and numbers, typically longer)
            elseif (preg_match('/^[A-Z0-9]{7,12}$/', strtoupper($identifier)) && strlen($identifier) >= 7) {
                $searchData['passport'] = strtoupper($identifier);
            }
            // Driver's License pattern (assuming shorter alphanumeric)
            else {
                $searchData['drivers_license'] = strtoupper($identifier);
            }
            
            // Perform crime check using stored procedure
            $result = $this->personService->performCrimeCheck($searchData);
            
            if ($result && $result['found']) {
                // Get full profile for matched person
                $profile = $this->personService->getPersonFullProfile($result['person']['id']);
                
                // Log access
                $this->logSensitiveAccess('persons', $result['person']['id'], 'VIEW', 'Background check via identifier search');
                
                logger("Background check performed via identifier search by user " . auth_id());
            }
        }
        
        return $this->view('persons/crime-check', [
            'title' => 'Crime Check & Background Verification',
            'profile' => $profile,
            'result' => $result, // Pass the result to view
            'searchPerformed' => $searchPerformed,
            'hasAlerts' => !empty($profile['alerts']),
            'hasCriminalHistory' => !empty($profile['criminal_history']),
            'isWanted' => isset($profile['is_wanted']) && $profile['is_wanted'],
            'riskLevel' => $profile['risk_level'] ?? 'Unknown'
        ]);
    }
    
    /**
     * Log access to sensitive data for audit trail
     */
    private function logSensitiveAccess(string $table, int $recordId, string $accessType, string $reason): void
    {
        try {
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO sensitive_data_access_log 
                (user_id, table_name, record_id, access_type, access_reason, ip_address, access_timestamp)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                auth_id(),
                $table,
                $recordId,
                $accessType,
                $reason,
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
        } catch (\Exception $e) {
            logger("Failed to log sensitive data access: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Add relationship between two persons
     */
    public function addRelationship(int $personId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $relatedPersonId = (int)($_POST['related_person_id'] ?? 0);
        $relationshipType = $_POST['relationship_type'] ?? '';
        $notes = $_POST['notes'] ?? null;
        
        if (!$relatedPersonId || !$relationshipType) {
            $this->json(['success' => false, 'message' => 'Related person and relationship type are required'], 400);
            return;
        }
        
        if ($personId === $relatedPersonId) {
            $this->json(['success' => false, 'message' => 'Cannot create relationship with self'], 400);
            return;
        }
        
        try {
            $relationshipModel = new \App\Models\PersonRelationship(\App\Config\Database::getConnection());
            $relationshipModel->createRelationship($personId, $relatedPersonId, $relationshipType, $notes, auth_id());
            
            $this->json(['success' => true, 'message' => 'Relationship created successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete relationship
     */
    public function deleteRelationship(int $relationshipId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        try {
            $relationshipModel = new \App\Models\PersonRelationship(\App\Config\Database::getConnection());
            $result = $relationshipModel->deleteRelationship($relationshipId);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Relationship deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Relationship not found'], 404);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Issue alert for person
     */
    public function issueAlert(int $personId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        try {
            // Validate required fields
            $alertType = $_POST['alert_type'] ?? null;
            $alertPriority = $_POST['alert_priority'] ?? 'Medium';
            $alertMessage = $_POST['alert_message'] ?? null;
            
            if (!$alertType || !$alertMessage) {
                $this->json(['success' => false, 'message' => 'Alert type and message are required'], 400);
                return;
            }
            
            // Prepare alert data
            $alertData = [
                'person_id' => $personId,
                'alert_type' => $alertType,
                'alert_priority' => $alertPriority,
                'alert_message' => $alertMessage,
                'alert_details' => $_POST['alert_details'] ?? null,
                'issued_by' => auth_id(),
                'issued_date' => date('Y-m-d'),
                'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null
            ];
            
            // Create alert using PersonService
            $alertId = $this->personService->createAlert($alertData);
            
            $this->json([
                'success' => true,
                'message' => 'Alert issued successfully',
                'alert_id' => $alertId
            ]);
            
        } catch (\Exception $e) {
            logger("Failed to issue alert: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get alert details
     */
    public function getAlert(int $alertId): void
    {
        try {
            $alertModel = new \App\Models\PersonAlert(\App\Config\Database::getConnection());
            $alert = $alertModel->find($alertId);
            
            if (!$alert) {
                $this->json(['success' => false, 'message' => 'Alert not found'], 404);
                return;
            }
            
            $this->json([
                'success' => true,
                'alert' => $alert
            ]);
            
        } catch (\Exception $e) {
            logger("Failed to get alert: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update alert
     */
    public function updateAlert(int $alertId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        try {
            $alertModel = new \App\Models\PersonAlert(\App\Config\Database::getConnection());
            $alert = $alertModel->find($alertId);
            
            if (!$alert) {
                $this->json(['success' => false, 'message' => 'Alert not found'], 404);
                return;
            }
            
            // Validate required fields
            $alertType = $_POST['alert_type'] ?? null;
            $alertPriority = $_POST['alert_priority'] ?? null;
            $alertMessage = $_POST['alert_message'] ?? null;
            
            if (!$alertType || !$alertPriority || !$alertMessage) {
                $this->json(['success' => false, 'message' => 'Alert type, priority, and message are required'], 400);
                return;
            }
            
            // Update alert data
            $updateData = [
                'alert_type' => $alertType,
                'alert_priority' => $alertPriority,
                'alert_message' => $alertMessage,
                'alert_details' => $_POST['alert_details'] ?? null,
                'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null
            ];
            
            $alertModel->update($alertId, $updateData);
            
            // Update person risk level
            $this->personService->updatePersonRiskLevel($alert['person_id']);
            
            logger("Alert {$alertId} updated by user " . auth_id());
            
            $this->json([
                'success' => true,
                'message' => 'Alert updated successfully'
            ]);
            
        } catch (\Exception $e) {
            logger("Failed to update alert: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Deactivate alert
     */
    public function deactivateAlert(int $alertId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        try {
            $alertModel = new \App\Models\PersonAlert(\App\Config\Database::getConnection());
            $alert = $alertModel->find($alertId);
            
            if (!$alert) {
                $this->json(['success' => false, 'message' => 'Alert not found'], 404);
                return;
            }
            
            // Deactivate alert
            $result = $this->personService->deactivateAlert($alertId);
            
            if ($result) {
                // Update person risk level after deactivating alert
                $this->personService->updatePersonRiskLevel($alert['person_id']);
                
                logger("Alert {$alertId} deactivated by user " . auth_id());
                
                $this->json([
                    'success' => true,
                    'message' => 'Alert deactivated successfully'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to deactivate alert'], 500);
            }
            
        } catch (\Exception $e) {
            logger("Failed to deactivate alert: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
