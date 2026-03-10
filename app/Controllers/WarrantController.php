<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class WarrantController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Display all warrants
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        
        $warrants = $this->getWarrants($status, $type);
        
        return $this->view('warrants/index', [
            'title' => 'Warrant Management',
            'warrants' => $warrants,
            'selected_status' => $status,
            'selected_type' => $type
        ]);
    }
    
    /**
     * Show warrant creation form
     */
    public function create(): string
    {
        // Get active cases for selection
        $cases = $this->getActiveCases();
        
        // Get all suspects for selection
        $suspects = $this->getAllSuspects();
        
        return $this->view('warrants/create', [
            'title' => 'Create New Warrant',
            'cases' => $cases,
            'suspects' => $suspects
        ]);
    }
    
    /**
     * Get suspects for a specific case
     */
    public function getCaseSuspects(int $caseId): void
    {
        $suspects = $this->getSuspectsByCase($caseId);
        $this->json(['success' => true, 'suspects' => $suspects]);
    }
    
    /**
     * Store new warrant
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $data = [
            'warrant_number' => $this->generateWarrantNumber(),
            'warrant_type' => $_POST['warrant_type'] ?? '',
            'case_id' => (int)($_POST['case_id'] ?? 0),
            'suspect_id' => !empty($_POST['suspect_id']) ? (int)$_POST['suspect_id'] : null,
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
            'issued_by' => $this->getCurrentOfficerName(),
            'issuing_court' => $_POST['issuing_court'] ?? null,
            'warrant_details' => $_POST['warrant_details'] ?? '',
            'execution_instructions' => $_POST['execution_instructions'] ?? null,
            'status' => 'Active'
        ];
        
        // Validate required fields
        if (empty($data['warrant_type']) || empty($data['case_id']) || empty($data['warrant_details'])) {
            $this->json(['success' => false, 'message' => 'Please fill all required fields'], 400);
            return;
        }
        
        // Validate case exists
        if (!$this->caseExists($data['case_id'])) {
            $this->json(['success' => false, 'message' => 'Selected case does not exist'], 400);
            return;
        }
        
        // Validate suspect if provided
        if ($data['suspect_id'] && !$this->suspectExists($data['suspect_id'])) {
            $this->json(['success' => false, 'message' => 'Selected suspect does not exist'], 400);
            return;
        }
        
        try {
            $warrantId = $this->createWarrant($data);
            
            $this->setFlash('success', 'Warrant created successfully with number: ' . $data['warrant_number']);
            $this->json([
                'success' => true, 
                'message' => 'Warrant created successfully',
                'redirect' => url('/warrants/view/' . $warrantId)
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create warrant: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Show warrant edit form
     */
    public function edit(int $id): string
    {
        $warrant = $this->getWarrantDetails($id);
        
        if (!$warrant) {
            $this->setFlash('error', 'Warrant not found');
            $this->redirect('/warrants');
        }
        
        // Get active cases for selection
        $cases = $this->getActiveCases();
        
        // Get all suspects for selection
        $suspects = $this->getAllSuspects();
        
        return $this->view('warrants/edit', [
            'title' => 'Edit Warrant',
            'warrant' => $warrant,
            'cases' => $cases,
            'suspects' => $suspects
        ]);
    }
    
    /**
     * Update warrant
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $data = [
            'warrant_type' => $_POST['warrant_type'] ?? '',
            'case_id' => (int)($_POST['case_id'] ?? 0),
            'suspect_id' => !empty($_POST['suspect_id']) ? (int)$_POST['suspect_id'] : null,
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
            'issuing_court' => $_POST['issuing_court'] ?? null,
            'warrant_details' => $_POST['warrant_details'] ?? '',
            'execution_instructions' => $_POST['execution_instructions'] ?? null
        ];
        
        // Validate required fields
        if (empty($data['warrant_type']) || empty($data['case_id']) || empty($data['warrant_details'])) {
            $this->json(['success' => false, 'message' => 'Please fill all required fields'], 400);
            return;
        }
        
        // Validate case exists
        if (!$this->caseExists($data['case_id'])) {
            $this->json(['success' => false, 'message' => 'Selected case does not exist'], 400);
            return;
        }
        
        // Validate suspect if provided
        if ($data['suspect_id'] && !$this->suspectExists($data['suspect_id'])) {
            $this->json(['success' => false, 'message' => 'Selected suspect does not exist'], 400);
            return;
        }
        
        try {
            $this->updateWarrant($id, $data);
            
            $this->setFlash('success', 'Warrant updated successfully');
            $this->json([
                'success' => true, 
                'message' => 'Warrant updated successfully',
                'redirect' => url('/warrants/view/' . $id)
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update warrant: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Show warrant details
     */
    public function show(int $id): string
    {
        $warrant = $this->getWarrantDetails($id);
        
        if (!$warrant) {
            $this->setFlash('error', 'Warrant not found');
            $this->redirect('/warrants');
        }
        
        $executionLogs = $this->getExecutionLogs($id);
        
        return $this->view('warrants/view', [
            'title' => 'Warrant Details',
            'warrant' => $warrant,
            'execution_logs' => $executionLogs
        ]);
    }
    
    /**
     * Execute warrant
     */
    public function execute(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $executionDate = $_POST['execution_date'] ?? date('Y-m-d H:i:s');
        $executionLocation = $_POST['execution_location'] ?? '';
        $executingOfficer = $_POST['executing_officer'] ?? '';
        $executingOfficerId = $_POST['executing_officer_id'] ?? null;
        $executionNotes = $_POST['execution_notes'] ?? '';
        $suspectStatus = $_POST['suspect_status'] ?? 'arrested';
        $evidenceItems = json_decode($_POST['evidence_items'] ?? '[]', true);
        
        // Validate required fields
        if (empty($executionDate) || empty($executionLocation) || empty($executingOfficer)) {
            $this->json(['success' => false, 'message' => 'Please fill in all required fields'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get warrant details to link evidence to case
            $warrant = $this->getWarrantDetails($id);
            if (!$warrant) {
                throw new \Exception('Warrant not found');
            }
            
            // Update warrant status
            $stmt = $this->db->prepare("
                UPDATE warrants
                SET status = 'Executed', executed_date = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$executionDate, $id]);
            
            // Create comprehensive execution log
            $stmt = $this->db->prepare("
                INSERT INTO warrant_execution_logs (
                    warrant_id, executed_by, execution_date, execution_location, notes
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            // Create detailed notes including all execution information
            $detailedNotes = "Executing Officer: " . $executingOfficer . "\n";
            $detailedNotes .= "Suspect Status: " . $suspectStatus . "\n";
            
            // Add evidence information
            if (!empty($evidenceItems)) {
                $detailedNotes .= "Evidence Seized:\n";
                foreach ($evidenceItems as $index => $evidence) {
                    $detailedNotes .= ($index + 1) . ". " . $evidence['type'] . " - " . $evidence['description'];
                    if ($evidence['quantity']) {
                        $detailedNotes .= " (Qty: " . $evidence['quantity'] . ")";
                    }
                    $detailedNotes .= "\n";
                    
                    // Add evidence to case evidence table
                    $this->addEvidenceToCase($warrant['case_id'], $evidence, $executionDate, $executionLocation);
                }
            }
            
            if ($executionNotes) {
                $detailedNotes .= "\nExecution Details: " . $executionNotes;
            }
            
            $stmt->execute([$id, $executingOfficerId ?? $_SESSION['user_id'] ?? 1, $executionDate, $executionLocation, $detailedNotes]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true, 
                'message' => 'Warrant executed successfully. Execution logged, warrant status updated, and evidence added to case.',
                'execution_details' => [
                    'execution_date' => $executionDate,
                    'execution_location' => $executionLocation,
                    'executing_officer' => $executingOfficer,
                    'suspect_status' => $suspectStatus,
                    'evidence_count' => count($evidenceItems)
                ]
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to execute warrant: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Cancel warrant
     */
    public function cancel(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE warrants
                SET status = 'Cancelled', cancellation_reason = ?, cancelled_date = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $id]);
            
            $this->json(['success' => true, 'message' => 'Warrant cancelled successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get active warrants
     */
    public function active(): string
    {
        $warrants = $this->getWarrants('Active', null);
        
        return $this->view('warrants/active', [
            'title' => 'Active Warrants',
            'warrants' => $warrants
        ]);
    }
    
    /**
     * Get warrants with filters
     */
    private function getWarrants(?string $status, ?string $type): array
    {
        $sql = "
            SELECT 
                w.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth
            FROM warrants w
            JOIN cases c ON w.case_id = c.id
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND w.status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $sql .= " AND w.warrant_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY w.issue_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get warrant details
     */
    private function getWarrantDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                w.*,
                c.case_number,
                c.description as case_description,
                c.status as case_status,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth,
                p.contact,
                p.address
            FROM warrants w
            JOIN cases c ON w.case_id = c.id
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE w.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get execution logs
     */
    private function getExecutionLogs(int $warrantId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                wel.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as executed_by_name,
                pr.rank_name as rank,
                o.service_number
            FROM warrant_execution_logs wel
            JOIN officers o ON wel.executed_by = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE wel.warrant_id = ?
            ORDER BY wel.execution_date DESC
        ");
        $stmt->execute([$warrantId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active cases for warrant creation
     */
    private function getActiveCases(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.case_number, c.description, c.status
            FROM cases c
            WHERE c.status IN ('Open', 'Investigation', 'Prosecution')
            ORDER BY c.case_number
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get all suspects for selection
     */
    private function getAllSuspects(): array
    {
        $stmt = $this->db->prepare("
            SELECT s.id, CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as name
            FROM suspects s
            JOIN persons p ON s.person_id = p.id
            ORDER BY p.last_name, p.first_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get suspects for a specific case
     */
    private function getSuspectsByCase(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.id, CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as name
            FROM case_suspects cs
            JOIN suspects s ON cs.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            WHERE cs.case_id = ?
            ORDER BY p.last_name, p.first_name
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate unique warrant number
     */
    private function generateWarrantNumber(): string
    {
        $year = date('Y');
        $sequence = $this->getNextWarrantSequence($year);
        return 'GW/' . $year . '/' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get next warrant sequence for the year
     */
    private function getNextWarrantSequence(string $year): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM warrants 
            WHERE YEAR(issue_date) = ?
        ");
        $stmt->execute([$year]);
        return (int)$stmt->fetch()['count'] + 1;
    }
    
    /**
     * Add evidence to case
     */
    private function addEvidenceToCase(int $caseId, array $evidence, string $executionDate, string $executionLocation): bool
    {
        // Generate evidence number
        $evidenceNumber = 'EVID/' . date('Y') . '/' . str_pad($this->getNextEvidenceSequence(), 4, '0', STR_PAD_LEFT);
        
        $stmt = $this->db->prepare("
            INSERT INTO evidence (
                case_id, evidence_type, evidence_number, description, 
                collection_date, collection_location, uploaded_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'In Custody')
        ");
        
        return $stmt->execute([
            $caseId,
            $evidence['type'],
            $evidenceNumber,
            $evidence['description'] . ($evidence['quantity'] ? " (Quantity: {$evidence['quantity']})" : ''),
            $executionDate,
            $executionLocation,
            $_SESSION['user_id'] ?? 1
        ]);
    }
    
    /**
     * Get next evidence sequence for the year
     */
    private function getNextEvidenceSequence(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM evidence 
            WHERE YEAR(collection_date) = ?
        ");
        $stmt->execute([date('Y')]);
        return (int)$stmt->fetch()['count'] + 1;
    }
    
    /**
     * Update warrant in database
     */
    private function updateWarrant(int $id, array $data): bool
    {
        $sql = "
            UPDATE warrants SET
                warrant_type = ?,
                case_id = ?,
                suspect_id = ?,
                issue_date = ?,
                expiry_date = ?,
                issuing_court = ?,
                warrant_details = ?,
                execution_instructions = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['warrant_type'],
            $data['case_id'],
            $data['suspect_id'],
            $data['issue_date'],
            $data['expiry_date'],
            $data['issuing_court'],
            $data['warrant_details'],
            $data['execution_instructions'],
            $id
        ]);
    }
    
    /**
     * Create warrant in database
     */
    private function createWarrant(array $data): int
    {
        $sql = "
            INSERT INTO warrants (
                warrant_number, warrant_type, case_id, suspect_id, 
                issue_date, expiry_date, issued_by, issuing_court,
                warrant_details, execution_instructions, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['warrant_number'],
            $data['warrant_type'],
            $data['case_id'],
            $data['suspect_id'],
            $data['issue_date'],
            $data['expiry_date'],
            $data['issued_by'],
            $data['issuing_court'],
            $data['warrant_details'],
            $data['execution_instructions'],
            $data['status']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Check if case exists
     */
    private function caseExists(int $caseId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if suspect exists
     */
    private function suspectExists(int $suspectId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM suspects WHERE id = ?");
        $stmt->execute([$suspectId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get current officer name
     */
    private function getCurrentOfficerName(): string
    {
        // This should get the current logged-in officer's name
        // For now, return a placeholder
        return $_SESSION['officer_name'] ?? 'System Administrator';
    }
}
