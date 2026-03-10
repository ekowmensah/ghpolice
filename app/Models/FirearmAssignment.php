<?php

namespace App\Models;

class FirearmAssignment extends BaseModel
{
    protected string $table = 'firearm_assignments';
    
    public function getByFirearm(int $firearmId): array
    {
        $sql = "
            SELECT 
                fa.*,
                CONCAT_WS(' ', o1.first_name, o1.middle_name, o1.last_name) as officer_name,
                pr1.rank_name as officer_rank,
                CONCAT_WS(' ', o2.first_name, o2.middle_name, o2.last_name) as issued_by_name,
                pr2.rank_name as issued_by_rank
            FROM firearm_assignments fa
            JOIN officers o1 ON fa.officer_id = o1.id
            JOIN police_ranks pr1 ON o1.rank_id = pr1.id
            JOIN officers o2 ON fa.issued_by = o2.id
            JOIN police_ranks pr2 ON o2.rank_id = pr2.id
            WHERE fa.firearm_id = ?
            ORDER BY fa.issue_date DESC
        ";
        
        return $this->query($sql, [$firearmId]);
    }
    
    public function getByOfficer(int $officerId): array
    {
        $sql = "
            SELECT 
                fa.*,
                f.serial_number,
                f.firearm_type,
                f.make,
                f.model,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as issued_by_name
            FROM firearm_assignments fa
            JOIN firearms f ON fa.firearm_id = f.id
            JOIN officers o ON fa.issued_by = o.id
            WHERE fa.officer_id = ?
            ORDER BY fa.issue_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getActive(int $officerId): array
    {
        $sql = "
            SELECT 
                fa.*,
                f.serial_number,
                f.firearm_type,
                f.make,
                f.model
            FROM firearm_assignments fa
            JOIN firearms f ON fa.firearm_id = f.id
            WHERE fa.officer_id = ?
            AND fa.return_date IS NULL
            ORDER BY fa.issue_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function returnFirearm(int $id, array $returnData): bool
    {
        return $this->update($id, [
            'return_date' => $returnData['return_date'] ?? date('Y-m-d H:i:s'),
            'ammunition_returned' => $returnData['ammunition_returned'] ?? 0,
            'condition_on_return' => $returnData['condition_on_return'] ?? null,
            'remarks' => $returnData['remarks'] ?? null
        ]);
    }
}
