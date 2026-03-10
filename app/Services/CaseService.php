<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Complainant;
use App\Models\Suspect;
use App\Models\Person;
use App\Models\CaseSuspect;
use App\Models\CaseWitness;
use App\Models\CaseAssignment;
use App\Models\CaseUpdate;
use App\Models\CaseStatusHistory;
use App\Config\Database;
use PDO;

class CaseService
{
    private CaseModel $caseModel;
    private Complainant $complainantModel;
    private Suspect $suspectModel;
    private Person $personModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->complainantModel = new Complainant();
        $this->suspectModel = new Suspect();
        $this->personModel = new Person();
        $this->db = Database::getConnection();
    }
    
    /**
     * Register new case with complainant
     */
    public function registerCase(array $data): array
    {
        try {
            $this->db->beginTransaction();
            
            // Create or get complainant
            $complainantId = $this->getOrCreateComplainant(
                $data['complainant_person_id'],
                $data['complainant_type'],
                $data['organization_name'] ?? null
            );
            
            // Generate case number
            $caseNumber = $this->generateCaseNumber($data['station_id']);
            
            // Create case
            $caseId = $this->caseModel->create([
                'case_number' => $caseNumber,
                'complainant_id' => $complainantId,
                'case_type' => $data['case_type'],
                'case_priority' => $data['case_priority'],
                'description' => $data['description'],
                'incident_location' => $data['incident_location'] ?? null,
                'incident_date' => $data['incident_date'] ?? null,
                'station_id' => $data['station_id'],
                'region_id' => $data['region_id'],
                'division_id' => $data['division_id'],
                'district_id' => $data['district_id'],
                'created_by' => $data['reported_by']
            ]);
            
            // Create initial status history
            $this->addStatusHistory($caseId, 'Open', 'Case registered', $data['reported_by']);
            
            // Assign to reporting officer
            $this->assignOfficer($caseId, $data['reported_by'], 'Investigating Officer');
            
            $this->db->commit();
            
            logger("Case registered: {$caseNumber} (ID: {$caseId})");
            
            return [
                'case_id' => $caseId,
                'case_number' => $caseNumber,
                'complainant_id' => $complainantId
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Case registration failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get or create complainant
     */
    private function getOrCreateComplainant(int $personId, string $type, ?string $organizationName): int
    {
        // Check if complainant already exists for this person
        $stmt = $this->db->prepare("SELECT id FROM complainants WHERE person_id = ? LIMIT 1");
        $stmt->execute([$personId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            return (int)$existing['id'];
        }
        
        // Create new complainant
        return $this->complainantModel->createFromPerson($personId, $type, $organizationName);
    }
    
    /**
     * Generate unique case number
     */
    private function generateCaseNumber(int $stationId): string
    {
        // Get station code
        $stmt = $this->db->prepare("SELECT station_code FROM stations WHERE id = ?");
        $stmt->execute([$stationId]);
        $station = $stmt->fetch();
        $stationCode = $station['station_code'] ?? 'UNK';
        
        // Format: STATION-YEAR-SEQUENCE
        $year = date('Y');
        $sequence = $this->getNextCaseSequence($stationId, $year);
        
        return sprintf('%s-%s-%04d', $stationCode, $year, $sequence);
    }
    
    /**
     * Get next case sequence number for station
     */
    private function getNextCaseSequence(int $stationId, int $year): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM cases 
            WHERE station_id = ? 
            AND YEAR(created_at) = ?
        ");
        $stmt->execute([$stationId, $year]);
        $result = $stmt->fetch();
        
        return ((int)$result['count']) + 1;
    }
    
    /**
     * Add status history entry
     */
    private function addStatusHistory(int $caseId, string $status, string $notes, int $changedBy): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_status_history (case_id, new_status, remarks, changed_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$caseId, $status, $notes, $changedBy]);
    }
    
    /**
     * Assign officer to case
     */
    private function assignOfficer(int $caseId, int $officerId, string $remarks): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_assignments (case_id, assigned_to, assigned_by, remarks)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$caseId, $officerId, $officerId, $remarks]);
    }
    
    /**
     * Get complainant details with person info
     */
    public function getComplainantDetails(int $complainantId): ?array
    {
        return $this->complainantModel->getWithPerson($complainantId);
    }
    
    /**
     * Get complete case details
     */
    public function getCaseDetails(int $caseId): array
    {
        return [
            'statements' => $this->getStatements($caseId),
            'evidence' => $this->getEvidence($caseId),
            'timeline' => $this->getTimeline($caseId),
            'assignments' => $this->getAssignments($caseId)
        ];
    }
    
    /**
     * Get statements for case
     */
    private function getStatements(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name,
                CASE 
                    WHEN s.statement_type = 'Complainant' THEN CONCAT_WS(' ', cp.first_name, cp.middle_name, cp.last_name)
                    WHEN s.statement_type = 'Suspect' THEN CONCAT_WS(' ', sp.first_name, sp.middle_name, sp.last_name)
                    WHEN s.statement_type = 'Witness' THEN CONCAT_WS(' ', wp.first_name, wp.middle_name, wp.last_name)
                END as person_name,
                CASE 
                    WHEN s.statement_type = 'Complainant' THEN cp.contact
                    WHEN s.statement_type = 'Suspect' THEN sp.contact
                    WHEN s.statement_type = 'Witness' THEN wp.contact
                END as person_contact
            FROM statements s
            LEFT JOIN users u ON s.recorded_by = u.id
            LEFT JOIN complainants c ON s.complainant_id = c.id
            LEFT JOIN persons cp ON c.person_id = cp.id
            LEFT JOIN suspects su ON s.suspect_id = su.id
            LEFT JOIN persons sp ON su.person_id = sp.id
            LEFT JOIN witnesses w ON s.witness_id = w.id
            LEFT JOIN persons wp ON w.person_id = wp.id
            WHERE s.case_id = ?
            ORDER BY s.recorded_at DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get evidence for case
     */
    private function getEvidence(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as uploaded_by_name
            FROM evidence e
            LEFT JOIN users u ON e.uploaded_by = u.id
            WHERE e.case_id = ?
            ORDER BY e.collection_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case timeline
     */
    private function getTimeline(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                csh.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as changed_by_name
            FROM case_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.case_id = ?
            ORDER BY csh.change_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case assignments
     */
    private function getAssignments(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ca.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as assigned_to_name
            FROM case_assignments ca
            LEFT JOIN users u ON ca.assigned_to = u.id
            WHERE ca.case_id = ?
            ORDER BY ca.assignment_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add suspect to case (known person or unknown suspect)
     */
    public function addSuspectToCase(int $caseId, array $data): int
    {
        try {
            $this->db->beginTransaction();
            
            $suspectType = $data['suspect_type'] ?? 'known';
            $personId = $data['person_id'] ?? null;
            
            // Convert empty string to NULL for person_id
            if ($personId === '' || $personId === '0') {
                $personId = null;
            }
            
            // For known suspects, check if person is already a suspect in this case
            if ($suspectType === 'known' && $personId) {
                $stmt = $this->db->prepare("
                    SELECT s.id 
                    FROM suspects s
                    JOIN case_suspects cs ON s.id = cs.suspect_id
                    WHERE s.person_id = ? AND cs.case_id = ?
                ");
                $stmt->execute([$personId, $caseId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    throw new \Exception('Person is already a suspect in this case');
                }
            }
            
            // Prepare suspect data - ensure NULL for unknown suspects
            $suspectData = [
                'person_id' => ($suspectType === 'unknown') ? null : $personId,
                'current_status' => $data['current_status'] ?? 'Identified',
                'alias' => $data['alias'] ?? null,
                'last_known_location' => $data['last_known_location'] ?? null,
                'arrest_date' => $data['arrest_date'] ?? null,
                'identifying_marks' => $data['identifying_marks'] ?? null,
                'notes' => $data['notes'] ?? null
            ];
            
            // Add unknown suspect specific fields
            if ($suspectType === 'unknown') {
                $suspectData['unknown_description'] = $data['unknown_description'] ?? null;
                $suspectData['estimated_age'] = $data['estimated_age'] ?? null;
                $suspectData['unknown_gender'] = $data['unknown_gender'] ?? null;
                $suspectData['height_build'] = $data['height_build'] ?? null;
                $suspectData['complexion'] = $data['complexion'] ?? null;
                $suspectData['clothing'] = $data['clothing'] ?? null;
            }
            
            // Create suspect record
            $suspectId = $this->suspectModel->create($suspectData);
            
            // Link to case
            $this->suspectModel->linkToCase($suspectId, $caseId);
            
            // Update person flags only for known suspects
            if ($suspectType === 'known' && $personId) {
                $this->personModel->update($personId, [
                    'has_criminal_record' => true
                ]);
            }
            
            $this->db->commit();
            
            $logMsg = $suspectType === 'known' 
                ? "Known suspect added to case {$caseId}: Person ID {$personId}"
                : "Unknown suspect added to case {$caseId}: {$data['unknown_description']}";
            logger($logMsg);
            
            return $suspectId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to add suspect: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Add statement to case
     */
    public function addStatement(array $data): int
    {
        // Calculate version number if this is a rewrite
        $version = 1;
        if (!empty($data['parent_statement_id'])) {
            $parentStmt = $this->db->prepare("SELECT version FROM statements WHERE id = ?");
            $parentStmt->execute([$data['parent_statement_id']]);
            $parent = $parentStmt->fetch();
            $version = ($parent['version'] ?? 0) + 1;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO statements (
                case_id, statement_type, complainant_id, suspect_id, 
                witness_id, statement_text, recorded_by, parent_statement_id, version
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['statement_type'],
            $data['complainant_id'] ?? null,
            $data['suspect_id'] ?? null,
            $data['witness_id'] ?? null,
            $data['statement_text'],
            $data['recorded_by'],
            $data['parent_statement_id'] ?? null,
            $version
        ]);
        
        $statementId = (int)$this->db->lastInsertId();
        
        logger("Statement recorded for case {$data['case_id']}: Statement ID {$statementId}, Version {$version}");
        
        return $statementId;
    }
    
    /**
     * Get statement details with metadata
     */
    public function getStatementDetails(int $statementId, int $caseId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                CONCAT_WS(' ', u.first_name, u.last_name) as recorded_by_name,
                CONCAT_WS(' ', cu.first_name, cu.last_name) as cancelled_by_name,
                (SELECT id FROM statements WHERE parent_statement_id = s.id LIMIT 1) as child_statement_id,
                CASE 
                    WHEN s.statement_type = 'Complainant' THEN CONCAT_WS(' ', cp.first_name, cp.middle_name, cp.last_name)
                    WHEN s.statement_type = 'Suspect' THEN CONCAT_WS(' ', sp.first_name, sp.middle_name, sp.last_name)
                    WHEN s.statement_type = 'Witness' THEN CONCAT_WS(' ', wp.first_name, wp.middle_name, wp.last_name)
                END as person_name,
                CASE 
                    WHEN s.statement_type = 'Complainant' THEN cp.contact
                    WHEN s.statement_type = 'Suspect' THEN sp.contact
                    WHEN s.statement_type = 'Witness' THEN wp.contact
                END as person_contact
            FROM statements s
            LEFT JOIN users u ON s.recorded_by = u.id
            LEFT JOIN users cu ON s.cancelled_by = cu.id
            LEFT JOIN complainants c ON s.complainant_id = c.id
            LEFT JOIN persons cp ON c.person_id = cp.id
            LEFT JOIN suspects su ON s.suspect_id = su.id
            LEFT JOIN persons sp ON su.person_id = sp.id
            LEFT JOIN witnesses w ON s.witness_id = w.id
            LEFT JOIN persons wp ON w.person_id = wp.id
            WHERE s.id = ? AND s.case_id = ?
        ");
        
        $stmt->execute([$statementId, $caseId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Mark statement as superseded
     */
    public function markStatementSuperseded(int $statementId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE statements 
            SET status = 'superseded' 
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$statementId]);
        
        logger("Statement {$statementId} marked as superseded");
        
        return $result;
    }
    
    /**
     * Cancel a statement
     */
    public function cancelStatement(int $statementId, int $caseId, string $reason, int $userId): bool
    {
        // Verify statement belongs to case
        $checkStmt = $this->db->prepare("SELECT id FROM statements WHERE id = ? AND case_id = ?");
        $checkStmt->execute([$statementId, $caseId]);
        
        if (!$checkStmt->fetch()) {
            throw new \Exception("Statement not found or does not belong to this case");
        }
        
        $stmt = $this->db->prepare("
            UPDATE statements 
            SET status = 'cancelled',
                cancelled_at = NOW(),
                cancelled_by = ?,
                cancellation_reason = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$userId, $reason, $statementId]);
        
        logger("Statement {$statementId} cancelled by user {$userId}");
        
        return $result;
    }
    
    /**
     * Update case status
     */
    public function updateStatus(int $caseId, string $newStatus, string $notes, int $userId): bool
    {
        try {
            // Use Phase 1 method - automatically handles status history
            $result = $this->caseModel->updateStatus($caseId, $newStatus, $userId, $notes);
            
            if ($result) {
                logger("Case {$caseId} status updated to: {$newStatus}");
            }
            
            return $result;
        } catch (\Exception $e) {
            logger("Failed to update case status: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // ==================== PHASE 1 & 2 INTEGRATION METHODS ====================
    
    /**
     * Get complete case details with all relationships (Phase 1)
     */
    public function getCaseFullDetails(int $caseId): ?array
    {
        return $this->caseModel->getFullDetails($caseId);
        // Returns: case + suspects + witnesses + officers + evidence + exhibits + 
        //          statements + timeline + tasks + updates + status_history
    }
    
    /**
     * Add suspect to case using Phase 2 model
     */
    public function addSuspectToCaseV2(int $caseId, int $suspect_id, int $added_by): bool
    {
        $caseSuspect = new CaseSuspect();
        
        if ($caseSuspect->exists($caseId, $suspect_id)) {
            logger("Suspect already linked to case {$caseId}", 'warning');
            return false;
        }
        
        $result = $caseSuspect->addSuspectToCase($caseId, $suspect_id, $added_by);
        
        if ($result) {
            logger("Suspect {$suspect_id} added to case {$caseId} by user {$added_by}");
        }
        
        return $result;
    }
    
    /**
     * Add witness to case using Phase 2 model
     */
    public function addWitnessToCase(int $caseId, int $witness_id, int $added_by): bool
    {
        $caseWitness = new CaseWitness();
        
        if ($caseWitness->exists($caseId, $witness_id)) {
            logger("Witness already linked to case {$caseId}", 'warning');
            return false;
        }
        
        $result = $caseWitness->addWitnessToCase($caseId, $witness_id, $added_by);
        
        if ($result) {
            logger("Witness {$witness_id} added to case {$caseId} by user {$added_by}");
        }
        
        return $result;
    }
    
    /**
     * Assign officer to case using Phase 2 model
     */
    public function assignOfficerToCase(int $caseId, int $officer_id, int $assigned_by, string $role = 'Investigator'): bool
    {
        $assignment = new CaseAssignment();
        
        if ($assignment->exists($caseId, $officer_id)) {
            logger("Officer {$officer_id} already assigned to case {$caseId}", 'warning');
            return false;
        }
        
        $result = $assignment->assignOfficer($caseId, $officer_id, $assigned_by, $role);
        
        if ($result) {
            logger("Officer {$officer_id} assigned to case {$caseId} as {$role}");
        }
        
        return $result;
    }
    
    /**
     * Reassign case from one officer to another (Phase 2)
     */
    public function reassignCase(int $caseId, int $from_officer_id, int $to_officer_id, int $reassigned_by): bool
    {
        $assignment = new CaseAssignment();
        
        $result = $assignment->reassignCase($caseId, $from_officer_id, $to_officer_id, $reassigned_by);
        
        if ($result) {
            logger("Case {$caseId} reassigned from officer {$from_officer_id} to {$to_officer_id}");
        }
        
        return $result;
    }
    
    /**
     * Add case progress update using Phase 2 model
     */
    public function addCaseUpdate(int $caseId, string $update_note, int $updated_by, string $update_type = 'General'): int
    {
        $caseUpdate = new CaseUpdate();
        
        $updateId = $caseUpdate->addUpdate($caseId, $update_note, $updated_by, $update_type);
        
        logger("Update added to case {$caseId}: {$update_type}");
        
        return $updateId;
    }
    
    /**
     * Get case timeline (combines updates + status history)
     */
    public function getCaseTimeline(int $caseId): array
    {
        $caseUpdate = new CaseUpdate();
        $statusHistory = new CaseStatusHistory();
        
        $updates = $caseUpdate->getByCaseId($caseId);
        $history = $statusHistory->getByCaseId($caseId);
        
        // Combine and sort by date
        $timeline = [];
        
        foreach ($updates as $update) {
            $timeline[] = [
                'type' => 'update',
                'date' => $update['update_date'],
                'content' => $update['update_note'],
                'by' => $update['updated_by_name'],
                'update_type' => $update['update_type'] ?? 'General'
            ];
        }
        
        foreach ($history as $status) {
            $timeline[] = [
                'type' => 'status_change',
                'date' => $status['change_date'],
                'content' => "Status changed from {$status['old_status']} to {$status['new_status']}",
                'by' => $status['changed_by_name'],
                'remarks' => $status['remarks']
            ];
        }
        
        // Sort by date descending
        usort($timeline, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $timeline;
    }
    
    /**
     * Get officer workload before assignment
     */
    public function getOfficerWorkload(int $officer_id): int
    {
        $assignment = new CaseAssignment();
        return $assignment->countActiveByOfficerId($officer_id);
    }
    
    /**
     * Get all suspects for a case (Phase 2)
     */
    public function getCaseSuspects(int $caseId): array
    {
        $caseSuspect = new CaseSuspect();
        return $caseSuspect->getByCaseId($caseId);
    }
    
    /**
     * Get all witnesses for a case (Phase 2)
     */
    public function getCaseWitnesses(int $caseId): array
    {
        $caseWitness = new CaseWitness();
        return $caseWitness->getByCaseId($caseId);
    }
    
    /**
     * Get all assigned officers for a case (Phase 2)
     */
    public function getCaseOfficers(int $caseId): array
    {
        $assignment = new CaseAssignment();
        return $assignment->getByCaseId($caseId);
    }
    
    /**
     * Close case workflow with validation
     */
    public function closeCaseWorkflow(int $caseId, int $closed_by, string $outcome, string $remarks): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get case details
            $case = $this->caseModel->find($caseId);
            if (!$case) {
                throw new \Exception("Case not found");
            }
            
            // Validation checks
            $validation = [
                'can_close' => true,
                'issues' => []
            ];
            
            // Check if there are suspects
            $caseSuspect = new CaseSuspect();
            $suspectCount = $caseSuspect->countByCaseId($caseId);
            if ($suspectCount === 0) {
                $validation['issues'][] = 'No suspects linked to case';
                $validation['can_close'] = false;
            }
            
            // Check if there are statements
            $statements = $this->caseModel->getStatements($caseId);
            if (empty($statements)) {
                $validation['issues'][] = 'No statements recorded';
                $validation['can_close'] = false;
            }
            
            if (!$validation['can_close']) {
                $this->db->rollBack();
                return $validation;
            }
            
            // Update case status
            $this->caseModel->updateStatus($caseId, 'Closed', $closed_by, $remarks);
            
            // Add case update
            $caseUpdate = new CaseUpdate();
            $caseUpdate->addUpdate($caseId, "Case closed: {$outcome}", $closed_by, 'Case Closure');
            
            // Mark all assignments as completed
            $assignment = new CaseAssignment();
            $assignments = $assignment->getByCaseId($caseId);
            foreach ($assignments as $assign) {
                if ($assign['status'] === 'Active') {
                    $assignment->updateStatus($assign['id'], 'Completed');
                }
            }
            
            // Create criminal history records for all suspects
            $suspects = $caseSuspect->getByCaseId($caseId);
            $criminalHistory = new \App\Models\PersonCriminalHistory();
            
            foreach ($suspects as $suspect) {
                // Only create criminal history for known persons (not unknown suspects)
                if (!empty($suspect['person_id'])) {
                    $involvementType = $suspect['suspect_status'] ?? 'Suspect';
                    
                    // Only create history for certain statuses
                    $recordableStatuses = ['Arrested', 'Charged', 'Convicted', 'Acquitted'];
                    if (in_array($involvementType, $recordableStatuses)) {
                        $criminalHistory->addRecord([
                            'person_id' => $suspect['person_id'],
                            'case_id' => $caseId,
                            'involvement_type' => $involvementType,
                            'offence_category' => $case['case_type'] ?? null,
                            'case_status' => 'Closed',
                            'case_date' => $case['incident_date'] ?? date('Y-m-d'),
                            'outcome' => $outcome
                        ]);
                        
                        logger("Criminal history record created for person {$suspect['person_id']} in case {$caseId}");
                    }
                }
            }
            
            $this->db->commit();
            
            logger("Case {$caseId} closed by user {$closed_by}: {$outcome}");
            
            $validation['closed'] = true;
            return $validation;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to close case: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
}
