<?php

namespace App\Models;

class Bail extends BaseModel
{
    protected string $table = 'bail_records';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                b.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.contact,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM bail_records b
            JOIN cases c ON b.case_id = c.id
            JOIN suspects s ON b.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON b.approved_by = u.id
            WHERE b.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                b.*,
                p.first_name,
                p.middle_name,
                p.last_name,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM bail_records b
            JOIN suspects s ON b.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON b.approved_by = u.id
            WHERE b.case_id = ?
            ORDER BY b.bail_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getBySuspectId(int $suspectId): array
    {
        $sql = "
            SELECT 
                b.*,
                c.case_number,
                c.description as case_description
            FROM bail_records b
            JOIN cases c ON b.case_id = c.id
            WHERE b.suspect_id = ?
            ORDER BY b.bail_date DESC
        ";
        
        return $this->query($sql, [$suspectId]);
    }
    
    public function getActiveBail(int $suspectId, int $caseId): ?array
    {
        $sql = "
            SELECT * FROM bail_records 
            WHERE suspect_id = ? AND case_id = ? AND bail_status = 'Granted'
            ORDER BY bail_date DESC
            LIMIT 1
        ";
        
        $result = $this->query($sql, [$suspectId, $caseId]);
        return $result[0] ?? null;
    }
    
    public function revokeBail(int $id, int $revokedBy, string $reason): bool
    {
        return $this->update($id, [
            'bail_status' => 'Revoked',
            'revocation_reason' => $reason,
            'revoked_by' => $revokedBy,
            'revoked_date' => date('Y-m-d H:i:s')
        ]);
    }
}
