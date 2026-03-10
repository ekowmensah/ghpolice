<?php

namespace App\Models;

class CaseAssignment extends BaseModel
{
    protected string $table = 'case_assignments';
    
    /**
     * Get all assignments for a case
     */
    public function getByCaseId(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name,
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as assigned_by_name
            FROM case_assignments ca
            INNER JOIN officers o ON ca.assigned_to = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            LEFT JOIN users u ON ca.assigned_by = u.id
            WHERE ca.case_id = ?
            ORDER BY ca.assignment_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all assignments for an officer
     */
    public function getByOfficerId(int $officer_id, string $status = null): array
    {
        $sql = "
            SELECT ca.*, 
                   c.case_number, c.case_type, c.status as case_status,
                   c.case_priority, c.incident_date,
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as assigned_by_name
            FROM case_assignments ca
            INNER JOIN cases c ON ca.case_id = c.id
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN users u ON ca.assigned_by = u.id
            WHERE ca.assigned_to = ?
        ";
        
        $params = [$officer_id];
        
        if ($status) {
            $sql .= " AND ca.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ca.assignment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if officer is already assigned to case
     */
    public function exists(int $case_id, int $officer_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM case_assignments 
            WHERE case_id = ? AND assigned_to = ? AND status = 'Active'
        ");
        $stmt->execute([$case_id, $officer_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Assign officer to case
     */
    public function assignOfficer(int $case_id, int $officer_id, int $assigned_by, string $role = 'Investigator'): bool
    {
        // Check if already assigned
        if ($this->exists($case_id, $officer_id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO case_assignments (case_id, assigned_to, assigned_by, assignment_date, status, role)
            VALUES (?, ?, ?, NOW(), 'Active', ?)
        ");
        return $stmt->execute([$case_id, $officer_id, $assigned_by, $role]);
    }
    
    /**
     * Update assignment status
     */
    public function updateStatus(int $assignment_id, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE case_assignments 
            SET status = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $assignment_id]);
    }
    
    /**
     * Reassign case from one officer to another
     */
    public function reassignCase(int $case_id, int $from_officer_id, int $to_officer_id, int $reassigned_by): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Mark old assignment as Reassigned
            $stmt = $this->db->prepare("
                UPDATE case_assignments 
                SET status = 'Reassigned'
                WHERE case_id = ? AND assigned_to = ? AND status = 'Active'
            ");
            $stmt->execute([$case_id, $from_officer_id]);
            
            // Create new assignment
            $stmt = $this->db->prepare("
                INSERT INTO case_assignments (case_id, assigned_to, assigned_by, assignment_date, status)
                VALUES (?, ?, ?, NOW(), 'Active')
            ");
            $stmt->execute([$case_id, $to_officer_id, $reassigned_by]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get active assignments count for an officer
     */
    public function countActiveByOfficerId(int $officer_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM case_assignments ca
            INNER JOIN cases c ON ca.case_id = c.id
            WHERE ca.assigned_to = ? AND ca.status = 'Active' AND c.status != 'Closed'
        ");
        $stmt->execute([$officer_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Get assignment by case and officer
     */
    public function getAssignment(int $case_id, int $officer_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, 
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name
            FROM case_assignments ca
            INNER JOIN officers o ON ca.assigned_to = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE ca.case_id = ? AND ca.assigned_to = ?
            ORDER BY ca.assignment_date DESC
            LIMIT 1
        ");
        $stmt->execute([$case_id, $officer_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
