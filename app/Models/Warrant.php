<?php

namespace App\Models;

class Warrant extends BaseModel
{
    protected string $table = 'warrants';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
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
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                w.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM warrants w
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE w.case_id = ?
            ORDER BY w.issue_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getActiveWarrants(?string $type = null): array
    {
        $sql = "
            SELECT 
                w.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number
            FROM warrants w
            JOIN cases c ON w.case_id = c.id
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE w.status = 'Active'
        ";
        
        $params = [];
        
        if ($type) {
            $sql .= " AND w.warrant_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY w.issue_date DESC";
        
        return $this->query($sql, $params);
    }
    
    public function executeWarrant(int $id, array $executionData): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->update($id, [
                'status' => 'Executed',
                'executed_date' => $executionData['execution_date']
            ]);
            
            $sql = "
                INSERT INTO warrant_execution_logs 
                (warrant_id, executed_by, execution_date, execution_location, notes)
                VALUES (?, ?, ?, ?, ?)
            ";
            
            $this->execute($sql, [
                $id,
                $executionData['executed_by'],
                $executionData['execution_date'],
                $executionData['execution_location'] ?? null,
                $executionData['notes'] ?? null
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function cancelWarrant(int $id, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'Cancelled',
            'cancellation_reason' => $reason,
            'cancelled_date' => date('Y-m-d H:i:s')
        ]);
    }
}
