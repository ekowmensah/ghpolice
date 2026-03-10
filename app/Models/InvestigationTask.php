<?php

namespace App\Models;

class InvestigationTask extends BaseModel
{
    protected string $table = 'case_investigation_tasks';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cit.*,
                CONCAT_WS(' ', u1.first_name, u1.middle_name, u1.last_name) as assigned_to_name,
                CONCAT_WS(' ', u2.first_name, u2.middle_name, u2.last_name) as assigned_by_name,
                CONCAT_WS(' ', u3.first_name, u3.middle_name, u3.last_name) as completed_by_name
            FROM case_investigation_tasks cit
            LEFT JOIN users u1 ON cit.assigned_to = u1.id
            LEFT JOIN users u2 ON cit.assigned_by = u2.id
            LEFT JOIN users u3 ON cit.completed_by = u3.id
            WHERE cit.case_id = ?
            ORDER BY cit.due_date ASC, cit.priority DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getByOfficer(int $officerId, ?string $status = null): array
    {
        $sql = "
            SELECT 
                cit.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as assigned_by_name
            FROM case_investigation_tasks cit
            JOIN cases c ON cit.case_id = c.id
            LEFT JOIN users u ON cit.assigned_by = u.id
            WHERE cit.assigned_to = ?
        ";
        
        $params = [$officerId];
        
        if ($status) {
            $sql .= " AND cit.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY cit.due_date ASC, cit.priority DESC";
        
        return $this->query($sql, $params);
    }
    
    public function getOverdue(): array
    {
        $sql = "
            SELECT 
                cit.*,
                c.case_number,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as assigned_to_name
            FROM case_investigation_tasks cit
            JOIN cases c ON cit.case_id = c.id
            LEFT JOIN users u ON cit.assigned_to = u.id
            WHERE cit.status IN ('Pending', 'In Progress')
            AND cit.due_date < CURDATE()
            ORDER BY cit.due_date ASC
        ";
        
        return $this->query($sql);
    }
    
    public function completeTask(int $id, int $completedBy, ?string $notes = null): bool
    {
        return $this->update($id, [
            'status' => 'Completed',
            'completed_by' => $completedBy,
            'completion_date' => date('Y-m-d H:i:s'),
            'completion_notes' => $notes
        ]);
    }
}
