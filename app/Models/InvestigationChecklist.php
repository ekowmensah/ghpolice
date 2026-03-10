<?php

namespace App\Models;

class InvestigationChecklist extends BaseModel
{
    protected string $table = 'case_investigation_checklist';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cic.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as completed_by_name
            FROM case_investigation_checklist cic
            LEFT JOIN users u ON cic.completed_by = u.id
            WHERE cic.case_id = ?
            ORDER BY cic.item_order ASC, cic.item_category
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getByCategory(int $caseId, string $category): array
    {
        $sql = "
            SELECT * FROM case_investigation_checklist
            WHERE case_id = ? AND item_category = ?
            ORDER BY item_order ASC
        ";
        
        return $this->query($sql, [$caseId, $category]);
    }
    
    public function getProgress(int $caseId): array
    {
        $sql = "
            SELECT 
                item_category,
                COUNT(*) as total_items,
                SUM(is_completed) as completed_items,
                ROUND((SUM(is_completed) / COUNT(*)) * 100, 2) as completion_percentage
            FROM case_investigation_checklist
            WHERE case_id = ?
            GROUP BY item_category
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function completeItem(int $id, int $completedBy, ?string $notes = null): bool
    {
        return $this->update($id, [
            'is_completed' => 1,
            'completed_by' => $completedBy,
            'completed_date' => date('Y-m-d H:i:s'),
            'notes' => $notes
        ]);
    }
    
    public function uncompleteItem(int $id): bool
    {
        return $this->update($id, [
            'is_completed' => 0,
            'completed_by' => null,
            'completed_date' => null
        ]);
    }
}
