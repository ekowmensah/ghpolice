<?php

namespace App\Models;

class CaseStatusHistory extends BaseModel
{
    protected string $table = 'case_status_history';
    
    /**
     * Get status history for a case
     */
    public function getByCaseId(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT csh.*, 
                   CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as changed_by_name,
                   u.service_number,
                   u.rank
            FROM case_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.case_id = ?
            ORDER BY csh.change_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Record status change
     */
    public function recordStatusChange(int $case_id, string $old_status, string $new_status, int $changed_by, string $remarks = ''): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_status_history (case_id, old_status, new_status, changed_by, change_date, remarks)
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$case_id, $old_status, $new_status, $changed_by, $remarks]);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get latest status change for a case
     */
    public function getLatestChange(int $case_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT csh.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as changed_by_name
            FROM case_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.case_id = ?
            ORDER BY csh.change_date DESC
            LIMIT 1
        ");
        $stmt->execute([$case_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get status changes by officer
     */
    public function getByOfficerId(int $officer_id, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT csh.*, 
                   c.case_number, c.case_type
            FROM case_status_history csh
            INNER JOIN cases c ON csh.case_id = c.id
            INNER JOIN users u ON csh.changed_by = u.id
            WHERE u.officer_id = ?
            ORDER BY csh.change_date DESC
            LIMIT ?
        ");
        $stmt->execute([$officer_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count status changes for a case
     */
    public function countByCaseId(int $case_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM case_status_history 
            WHERE case_id = ?
        ");
        $stmt->execute([$case_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Get cases by status transition
     */
    public function getCasesByTransition(string $from_status, string $to_status, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT csh.*, 
                   c.case_number, c.case_type, c.status as current_status,
                   CONCAT_WS(' ', u.first_name, u.last_name) as changed_by_name
            FROM case_status_history csh
            INNER JOIN cases c ON csh.case_id = c.id
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.old_status = ? AND csh.new_status = ?
            ORDER BY csh.change_date DESC
            LIMIT ?
        ");
        $stmt->execute([$from_status, $to_status, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get status change statistics
     */
    public function getStatistics(string $start_date = null, string $end_date = null): array
    {
        $sql = "
            SELECT 
                old_status,
                new_status,
                COUNT(*) as count,
                DATE(change_date) as change_day
            FROM case_status_history
        ";
        
        $params = [];
        
        if ($start_date && $end_date) {
            $sql .= " WHERE change_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        $sql .= " GROUP BY old_status, new_status, DATE(change_date)
                  ORDER BY change_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
