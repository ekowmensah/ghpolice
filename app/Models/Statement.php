<?php

namespace App\Models;

class Statement extends BaseModel
{
    protected string $table = 'statements';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                s.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as person_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name,
                CASE 
                    WHEN s.suspect_id IS NOT NULL THEN 'Suspect'
                    WHEN s.witness_id IS NOT NULL THEN 'Witness'
                    WHEN s.complainant_id IS NOT NULL THEN 'Complainant'
                    ELSE 'Unknown'
                END as statement_from
            FROM statements s
            LEFT JOIN suspects su ON s.suspect_id = su.id
            LEFT JOIN witnesses w ON s.witness_id = w.id
            LEFT JOIN complainants c ON s.complainant_id = c.id
            LEFT JOIN persons p ON (su.person_id = p.id OR w.person_id = p.id OR c.person_id = p.id)
            LEFT JOIN users u ON s.recorded_by = u.id
            WHERE s.case_id = ?
            ORDER BY s.created_at DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getActiveStatements(int $caseId): array
    {
        $sql = "
            SELECT 
                s.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as person_name,
                CASE 
                    WHEN s.suspect_id IS NOT NULL THEN 'Suspect'
                    WHEN s.witness_id IS NOT NULL THEN 'Witness'
                    WHEN s.complainant_id IS NOT NULL THEN 'Complainant'
                    ELSE 'Unknown'
                END as statement_from
            FROM statements s
            LEFT JOIN suspects su ON s.suspect_id = su.id
            LEFT JOIN witnesses w ON s.witness_id = w.id
            LEFT JOIN complainants c ON s.complainant_id = c.id
            LEFT JOIN persons p ON (su.person_id = p.id OR w.person_id = p.id OR c.person_id = p.id)
            WHERE s.case_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function cancelStatement(int $id, int $cancelledBy, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'cancelled',
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getStatementVersions(int $caseId, ?int $suspectId = null, ?int $witnessId = null, ?int $complainantId = null): array
    {
        $sql = "
            SELECT * FROM statements 
            WHERE case_id = ?
        ";
        
        $params = [$caseId];
        
        if ($suspectId) {
            $sql .= " AND suspect_id = ?";
            $params[] = $suspectId;
        }
        
        if ($witnessId) {
            $sql .= " AND witness_id = ?";
            $params[] = $witnessId;
        }
        
        if ($complainantId) {
            $sql .= " AND complainant_id = ?";
            $params[] = $complainantId;
        }
        
        $sql .= " ORDER BY version DESC";
        
        return $this->query($sql, $params);
    }
}
