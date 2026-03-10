<?php

namespace App\Models;

class Evidence extends BaseModel
{
    protected string $table = 'evidence';
    
    /**
     * Get all evidence with related information
     */
    public function all(array $columns = ['*']): array
    {
        $stmt = $this->db->query("
            SELECT 
                e.*,
                c.case_number,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as uploaded_by_name,
                (SELECT CONCAT_WS(' ', u2.first_name, u2.last_name)
                 FROM evidence_custody_chain ecc
                 LEFT JOIN users u2 ON ecc.transferred_to = u2.id
                 WHERE ecc.evidence_id = e.id
                 ORDER BY ecc.transfer_date DESC
                 LIMIT 1) as current_holder_name,
                (SELECT ecc.transfer_date
                 FROM evidence_custody_chain ecc
                 WHERE ecc.evidence_id = e.id
                 ORDER BY ecc.transfer_date DESC
                 LIMIT 1) as last_transfer_date
            FROM evidence e
            LEFT JOIN cases c ON e.case_id = c.id
            LEFT JOIN users u ON e.uploaded_by = u.id
            ORDER BY e.collection_date DESC, e.uploaded_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get evidence for a case
     */
    public function getByCaseId(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as uploaded_by_name
            FROM evidence e
            LEFT JOIN users u ON e.uploaded_by = u.id
            WHERE e.case_id = ?
            ORDER BY e.collection_date DESC, e.uploaded_at DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get custody chain for evidence
     */
    public function getCustodyChain(int $evidenceId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ecc.*,
                CONCAT_WS(' ', u1.first_name, u1.middle_name, u1.last_name) as transferred_from_name,
                CONCAT_WS(' ', u2.first_name, u2.middle_name, u2.last_name) as transferred_to_name
            FROM evidence_custody_chain ecc
            LEFT JOIN users u1 ON ecc.transferred_from = u1.id
            LEFT JOIN users u2 ON ecc.transferred_to = u2.id
            WHERE ecc.evidence_id = ?
            ORDER BY ecc.transfer_date DESC
        ");
        $stmt->execute([$evidenceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add custody chain entry
     */
    public function addCustodyEntry(int $evidenceId, int $transferredFrom, int $transferredTo, string $reason): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO evidence_custody_chain (
                evidence_id, transferred_from, transferred_to, 
                transfer_date, reason
            ) VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$evidenceId, $transferredFrom, $transferredTo, $reason]);
        return (int)$this->db->lastInsertId();
    }
    
    // ==================== RELATIONSHIP METHODS ====================
    
    /**
     * Get current custodian of evidence
     */
    public function getCurrentCustodian(int $evidence_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ecc.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as custodian_name,
                   o.service_number, pr.rank_name
            FROM evidence_custody_chain ecc
            LEFT JOIN users u ON ecc.transferred_to = u.id
            LEFT JOIN officers o ON u.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE ecc.evidence_id = ?
            ORDER BY ecc.transfer_date DESC
            LIMIT 1
        ");
        $stmt->execute([$evidence_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Transfer evidence custody with full audit trail
     */
    public function transferCustody(int $evidence_id, int $from_user_id, int $to_user_id, string $purpose, string $location = null): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Record custody transfer
            $stmt = $this->db->prepare("
                INSERT INTO evidence_custody_chain (
                    evidence_id, transferred_from, transferred_to, 
                    transfer_date, purpose, location
                ) VALUES (?, ?, ?, NOW(), ?, ?)
            ");
            $stmt->execute([$evidence_id, $from_user_id, $to_user_id, $purpose, $location]);
            
            // Update evidence location if provided
            if ($location) {
                $stmt = $this->db->prepare("
                    UPDATE evidence SET current_location = ? WHERE id = ?
                ");
                $stmt->execute([$location, $evidence_id]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get case associated with this evidence
     */
    public function getCase(int $evidence_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
            FROM evidence e
            INNER JOIN cases c ON e.case_id = c.id
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN users u ON c.created_by = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$evidence_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Verify chain of custody integrity
     */
    public function verifyChainOfCustody(int $evidence_id): array
    {
        $chain = $this->getCustodyChain($evidence_id);
        
        $verification = [
            'is_valid' => true,
            'total_transfers' => count($chain),
            'issues' => [],
            'chain' => $chain
        ];
        
        // Check for gaps in custody
        for ($i = 0; $i < count($chain) - 1; $i++) {
            $current = $chain[$i];
            $next = $chain[$i + 1];
            
            // Check if transferred_from of current matches transferred_to of next
            if ($current['transferred_from'] != $next['transferred_to']) {
                $verification['is_valid'] = false;
                $verification['issues'][] = [
                    'type' => 'custody_gap',
                    'message' => 'Gap in custody chain between transfers',
                    'transfer_index' => $i
                ];
            }
        }
        
        // Check for missing information
        foreach ($chain as $index => $transfer) {
            if (!$transfer['transferred_from'] || !$transfer['transferred_to']) {
                $verification['is_valid'] = false;
                $verification['issues'][] = [
                    'type' => 'missing_custodian',
                    'message' => 'Missing custodian information',
                    'transfer_index' => $index
                ];
            }
            
            if (!$transfer['purpose']) {
                $verification['issues'][] = [
                    'type' => 'missing_purpose',
                    'message' => 'Transfer purpose not documented',
                    'transfer_index' => $index
                ];
            }
        }
        
        return $verification;
    }
    
    /**
     * Get full evidence details with relationships
     */
    public function getFullDetails(int $evidence_id): ?array
    {
        $evidence = $this->find($evidence_id);
        if (!$evidence) {
            return null;
        }
        
        // Add relationships
        $evidence['case'] = $this->getCase($evidence_id);
        $evidence['custody_chain'] = $this->getCustodyChain($evidence_id);
        $evidence['current_custodian'] = $this->getCurrentCustodian($evidence_id);
        $evidence['chain_verification'] = $this->verifyChainOfCustody($evidence_id);
        
        return $evidence;
    }
}
