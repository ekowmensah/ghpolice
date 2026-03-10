<?php

namespace App\Models;

class CaseUpdate extends BaseModel
{
    protected string $table = 'case_updates';
    
    /**
     * Get all updates for a case
     */
    public function getByCaseId(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, 
                   CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as updated_by_name,
                   u.service_number,
                   u.rank
            FROM case_updates cu
            LEFT JOIN users u ON cu.updated_by = u.id
            WHERE cu.case_id = ?
            ORDER BY cu.update_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add update to case
     */
    public function addUpdate(int $case_id, string $update_note, int $updated_by, string $update_type = 'General'): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_updates (case_id, update_note, updated_by, update_date, update_type)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$case_id, $update_note, $updated_by, $update_type]);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get recent updates (across all cases)
     */
    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, 
                   c.case_number, c.case_type,
                   CONCAT_WS(' ', u.first_name, u.last_name) as updated_by_name
            FROM case_updates cu
            INNER JOIN cases c ON cu.case_id = c.id
            LEFT JOIN users u ON cu.updated_by = u.id
            ORDER BY cu.update_date DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get updates by officer
     */
    public function getByOfficerId(int $officer_id, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, 
                   c.case_number, c.case_type, c.status as case_status
            FROM case_updates cu
            INNER JOIN cases c ON cu.case_id = c.id
            INNER JOIN users u ON cu.updated_by = u.id
            WHERE u.officer_id = ?
            ORDER BY cu.update_date DESC
            LIMIT ?
        ");
        $stmt->execute([$officer_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count updates for a case
     */
    public function countByCaseId(int $case_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM case_updates 
            WHERE case_id = ?
        ");
        $stmt->execute([$case_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Delete update
     */
    public function deleteUpdate(int $update_id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM case_updates WHERE id = ?
        ");
        return $stmt->execute([$update_id]);
    }
    
    /**
     * Search updates
     */
    public function search(string $keyword, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, 
                   c.case_number, c.case_type,
                   CONCAT_WS(' ', u.first_name, u.last_name) as updated_by_name
            FROM case_updates cu
            INNER JOIN cases c ON cu.case_id = c.id
            LEFT JOIN users u ON cu.updated_by = u.id
            WHERE cu.update_note LIKE ?
            ORDER BY cu.update_date DESC
            LIMIT ?
        ");
        $searchTerm = "%{$keyword}%";
        $stmt->execute([$searchTerm, $limit]);
        return $stmt->fetchAll();
    }
}
