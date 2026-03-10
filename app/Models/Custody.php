<?php

namespace App\Models;

class Custody extends BaseModel
{
    protected string $table = 'custody_records';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                cr.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.contact,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as released_by_name
            FROM custody_records cr
            JOIN suspects s ON cr.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            JOIN cases c ON cr.case_id = c.id
            LEFT JOIN users u ON cr.released_by = u.id
            WHERE cr.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cr.*,
                p.first_name,
                p.middle_name,
                p.last_name,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as released_by_name
            FROM custody_records cr
            JOIN suspects s ON cr.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON cr.released_by = u.id
            WHERE cr.case_id = ?
            ORDER BY cr.custody_start DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getBySuspectId(int $suspectId): array
    {
        $sql = "
            SELECT 
                cr.*,
                c.case_number,
                c.description as case_description
            FROM custody_records cr
            JOIN cases c ON cr.case_id = c.id
            WHERE cr.suspect_id = ?
            ORDER BY cr.custody_start DESC
        ";
        
        return $this->query($sql, [$suspectId]);
    }
    
    public function getActiveCustody(int $suspectId): ?array
    {
        $sql = "
            SELECT * FROM custody_records 
            WHERE suspect_id = ? AND custody_status = 'In Custody'
            ORDER BY custody_start DESC
            LIMIT 1
        ";
        
        $result = $this->query($sql, [$suspectId]);
        return $result[0] ?? null;
    }
    
    public function releaseSuspect(int $id, int $releasedBy, string $reason): bool
    {
        return $this->update($id, [
            'custody_status' => 'Released',
            'custody_end' => date('Y-m-d H:i:s'),
            'released_by' => $releasedBy,
            'reason' => $reason
        ]);
    }
    
    public function transferCustody(int $id, string $newLocation, string $reason): bool
    {
        return $this->update($id, [
            'custody_status' => 'Transferred',
            'custody_location' => $newLocation,
            'transfer_reason' => $reason,
            'transfer_date' => date('Y-m-d H:i:s')
        ]);
    }
}
