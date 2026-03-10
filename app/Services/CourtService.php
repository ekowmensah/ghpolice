<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Config\Database;
use PDO;

class CourtService
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Get all court data for a case
     */
    public function getCourtData(int $caseId): array
    {
        return [
            'proceedings' => $this->getProceedings($caseId),
            'charges' => $this->getCharges($caseId),
            'warrants' => $this->getWarrants($caseId),
            'bail' => $this->getBailRecords($caseId)
        ];
    }
    
    /**
     * Get court proceedings
     */
    private function getProceedings(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cp.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name
            FROM court_proceedings cp
            LEFT JOIN users u ON cp.recorded_by = u.id
            WHERE cp.case_id = ?
            ORDER BY cp.hearing_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get charges
     */
    private function getCharges(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as filed_by_name
            FROM charges c
            LEFT JOIN suspects s ON c.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON c.filed_by = u.id
            WHERE c.case_id = ?
            ORDER BY c.filed_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get warrants
     */
    private function getWarrants(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                w.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM warrants w
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE w.case_id = ?
            ORDER BY w.issue_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get bail records
     */
    private function getBailRecords(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                b.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM bail_records b
            LEFT JOIN suspects s ON b.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE b.case_id = ?
            ORDER BY b.granted_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add court proceeding
     */
    public function addProceeding(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO court_proceedings (
                case_id, court_name, hearing_date, hearing_type,
                judge_name, outcome, next_hearing_date, notes, recorded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['court_name'],
            $data['hearing_date'],
            $data['hearing_type'],
            $data['judge_name'] ?? null,
            $data['outcome'] ?? null,
            $data['next_hearing_date'] ?? null,
            $data['notes'],
            $data['recorded_by']
        ]);
        
        $proceedingId = (int)$this->db->lastInsertId();
        
        logger("Court proceeding recorded for case {$data['case_id']}: Proceeding ID {$proceedingId}");
        
        return $proceedingId;
    }
    
    /**
     * Add charges
     */
    public function addCharges(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO charges (
                case_id, suspect_id, charge_description, charge_type,
                statute_reference, filed_date, filed_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Filed')
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['suspect_id'],
            $data['charge_description'],
            $data['charge_type'],
            $data['statute_reference'] ?? null,
            $data['filed_date'],
            $data['filed_by']
        ]);
        
        $chargeId = (int)$this->db->lastInsertId();
        
        logger("Charges filed for case {$data['case_id']}: Charge ID {$chargeId}");
        
        return $chargeId;
    }
    
    /**
     * Issue warrant
     */
    public function issueWarrant(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO warrants (
                case_id, suspect_id, warrant_type, issue_date,
                issued_by, warrant_details, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['suspect_id'],
            $data['warrant_type'],
            $data['issue_date'],
            $data['issued_by'],
            $data['warrant_details'],
            $data['status']
        ]);
        
        $warrantId = (int)$this->db->lastInsertId();
        
        logger("Warrant issued for case {$data['case_id']}: Warrant ID {$warrantId}");
        
        return $warrantId;
    }
    
    /**
     * Record bail
     */
    public function recordBail(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO bail_records (
                case_id, suspect_id, bail_amount, bail_conditions,
                granted_date, granted_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['suspect_id'],
            $data['bail_amount'],
            $data['bail_conditions'],
            $data['granted_date'],
            $data['granted_by'],
            $data['status']
        ]);
        
        $bailId = (int)$this->db->lastInsertId();
        
        logger("Bail recorded for case {$data['case_id']}: Bail ID {$bailId}");
        
        return $bailId;
    }
    
    /**
     * Update warrant status
     */
    public function updateWarrantStatus(int $warrantId, string $status, ?string $executedDate = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE warrants
            SET status = ?, executed_date = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $executedDate, $warrantId]);
    }
    
    // ==================== PHASE 1 & 2 INTEGRATION METHODS ====================
    
    /**
     * Get case suspects for court (Phase 2)
     */
    public function getCaseSuspectsForCourt(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getSuspects($case_id);
    }
    
    /**
     * Get case evidence for court (Phase 1)
     */
    public function getCaseEvidenceForCourt(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getEvidence($case_id);
    }
    
    /**
     * Get case exhibits for court (Phase 1)
     */
    public function getCaseExhibitsForCourt(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getExhibits($case_id);
    }
    
    /**
     * Get case statements for court (Phase 1)
     */
    public function getCaseStatementsForCourt(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getStatements($case_id);
    }
}
