<?php

namespace App\Models;

class CaseMilestone extends BaseModel
{
    protected string $table = 'case_milestones';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cm.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as created_by_name
            FROM case_milestones cm
            LEFT JOIN users u ON cm.created_by = u.id
            WHERE cm.case_id = ?
            ORDER BY cm.target_date ASC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getUpcoming(int $caseId): array
    {
        $sql = "
            SELECT * FROM case_milestones
            WHERE case_id = ?
            AND is_achieved = 0
            AND target_date >= CURDATE()
            ORDER BY target_date ASC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getOverdue(int $caseId): array
    {
        $sql = "
            SELECT * FROM case_milestones
            WHERE case_id = ?
            AND is_achieved = 0
            AND target_date < CURDATE()
            ORDER BY target_date ASC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function achieveMilestone(int $id): bool
    {
        return $this->update($id, [
            'is_achieved' => 1,
            'achieved_date' => date('Y-m-d H:i:s')
        ]);
    }
}
