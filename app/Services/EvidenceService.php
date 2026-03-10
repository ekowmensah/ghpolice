<?php

namespace App\Services;

use App\Models\Evidence;
use App\Config\Database;
use PDO;

class EvidenceService
{
    private Evidence $evidenceModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->evidenceModel = new Evidence();
        $this->db = Database::getConnection();
    }
    
    /**
     * Add evidence to case
     */
    public function addEvidence(array $data): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create evidence record
            $evidenceId = $this->evidenceModel->create($data);
            
            // Create initial custody chain entry
            $this->addCustodyEntry($evidenceId, $data['collected_by'], 'Initial Collection', 'Evidence collected from scene');
            
            $this->db->commit();
            
            logger("Evidence added to case {$data['case_id']}: Evidence ID {$evidenceId}");
            
            return $evidenceId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to add evidence: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Transfer evidence custody
     */
    public function transferCustody(array $data): bool
    {
        try {
            $this->db->beginTransaction();
            
            // End current custody
            $stmt = $this->db->prepare("
                UPDATE evidence_custody_chain
                SET custody_end_date = NOW()
                WHERE evidence_id = ? AND custody_end_date IS NULL
            ");
            $stmt->execute([$data['evidence_id']]);
            
            // Create new custody entry
            $this->addCustodyEntry(
                $data['evidence_id'],
                $data['transferred_to'],
                'Transfer',
                $data['transfer_reason'],
                $data['transferred_by']
            );
            
            // Update evidence current custodian
            $this->evidenceModel->update($data['evidence_id'], [
                'current_custodian' => $data['transferred_to']
            ]);
            
            $this->db->commit();
            
            logger("Evidence {$data['evidence_id']} custody transferred to officer {$data['transferred_to']}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to transfer custody: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Add custody chain entry
     */
    private function addCustodyEntry(int $evidenceId, int $custodianId, string $action, string $notes, ?int $transferredBy = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO evidence_custody_chain (
                evidence_id, custodian_id, action_taken, notes, transferred_by
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$evidenceId, $custodianId, $action, $notes, $transferredBy]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update evidence status
     */
    public function updateStatus(int $evidenceId, string $status, string $notes, int $userId): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Update evidence status
            $this->evidenceModel->update($evidenceId, ['status' => $status]);
            
            // Add custody chain entry for status change
            $this->addCustodyEntry($evidenceId, $userId, 'Status Change', "Status changed to: {$status}. {$notes}");
            
            $this->db->commit();
            
            logger("Evidence {$evidenceId} status updated to: {$status}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to update evidence status: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get evidence statistics for a case
     */
    public function getCaseEvidenceStats(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN status = 'Collected' THEN 1 ELSE 0 END) as collected,
                SUM(CASE WHEN status = 'In Storage' THEN 1 ELSE 0 END) as in_storage,
                SUM(CASE WHEN status = 'In Lab' THEN 1 ELSE 0 END) as in_lab,
                SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned
            FROM evidence
            WHERE case_id = ?
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetch() ?: [];
    }
    
    // ==================== PHASE 1 INTEGRATION METHODS ====================
    
    /**
     * Get complete evidence details with all relationships (Phase 1)
     */
    public function getEvidenceFullDetails(int $evidence_id): ?array
    {
        return $this->evidenceModel->getFullDetails($evidence_id);
    }
    
    /**
     * Transfer evidence custody using Phase 1 method
     */
    public function transferEvidenceCustody(int $evidence_id, int $from_user_id, int $to_user_id, string $purpose, string $location = null): bool
    {
        try {
            $result = $this->evidenceModel->transferCustody($evidence_id, $from_user_id, $to_user_id, $purpose, $location);
            
            if ($result) {
                logger("Evidence {$evidence_id} custody transferred from user {$from_user_id} to {$to_user_id}");
            }
            
            return $result;
        } catch (\Exception $e) {
            logger("Failed to transfer evidence custody: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Verify chain of custody integrity (Phase 1)
     */
    public function verifyChainOfCustody(int $evidence_id): array
    {
        return $this->evidenceModel->verifyChainOfCustody($evidence_id);
    }
    
    /**
     * Get current custodian of evidence (Phase 1)
     */
    public function getCurrentCustodian(int $evidence_id): ?array
    {
        return $this->evidenceModel->getCurrentCustodian($evidence_id);
    }
    
    /**
     * Get evidence by case ID (Phase 1)
     */
    public function getEvidenceByCaseId(int $case_id): array
    {
        return $this->evidenceModel->getByCaseId($case_id);
    }
    
    /**
     * Get case associated with evidence (Phase 1)
     */
    public function getEvidenceCase(int $evidence_id): ?array
    {
        return $this->evidenceModel->getCase($evidence_id);
    }
    
    /**
     * Get custody chain for evidence (Phase 1)
     */
    public function getCustodyChain(int $evidence_id): array
    {
        return $this->evidenceModel->getCustodyChain($evidence_id);
    }
}
