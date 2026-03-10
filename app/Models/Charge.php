<?php

namespace App\Models;

class Charge extends BaseModel
{
    protected string $table = 'charges';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                ch.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as charged_by_name
            FROM charges ch
            JOIN cases c ON ch.case_id = c.id
            JOIN suspects s ON ch.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON ch.charged_by = u.id
            WHERE ch.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                ch.*,
                p.first_name,
                p.middle_name,
                p.last_name,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as charged_by_name
            FROM charges ch
            JOIN suspects s ON ch.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON ch.charged_by = u.id
            WHERE ch.case_id = ?
            ORDER BY ch.charge_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getBySuspectId(int $suspectId): array
    {
        $sql = "
            SELECT 
                ch.*,
                c.case_number,
                c.description as case_description
            FROM charges ch
            JOIN cases c ON ch.case_id = c.id
            WHERE ch.suspect_id = ?
            ORDER BY ch.charge_date DESC
        ";
        
        return $this->query($sql, [$suspectId]);
    }
    
    public function getActiveCharges(int $caseId, int $suspectId): array
    {
        $sql = "
            SELECT * FROM charges 
            WHERE case_id = ? AND suspect_id = ? AND charge_status IN ('Pending', 'Filed')
            ORDER BY charge_date DESC
        ";
        
        return $this->query($sql, [$caseId, $suspectId]);
    }
    
    public function fileCharge(int $id, int $filedBy): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->update($id, [
                'charge_status' => 'Filed',
                'filed_date' => date('Y-m-d H:i:s'),
                'filed_by' => $filedBy
            ]);
            
            $charge = $this->find($id);
            $this->execute(
                "UPDATE suspects SET current_status = 'Charged' WHERE id = ?",
                [$charge['suspect_id']]
            );
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function withdrawCharge(int $id, string $reason): bool
    {
        return $this->update($id, [
            'charge_status' => 'Withdrawn',
            'withdrawal_reason' => $reason,
            'withdrawn_date' => date('Y-m-d H:i:s')
        ]);
    }
}
