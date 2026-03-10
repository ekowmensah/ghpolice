<?php

namespace App\Models;

class CaseSuspect extends BaseModel
{
    protected string $table = 'case_suspects';
    
    /**
     * Get all suspects for a case
     */
    public function getByCaseId(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, 
                   s.current_status as suspect_status,
                   p.first_name, p.middle_name, p.last_name, p.gender, 
                   p.date_of_birth, p.contact, p.ghana_card_number, p.photo_path,
                   CONCAT_WS(' ', u.first_name, u.last_name) as added_by_name
            FROM case_suspects cs
            INNER JOIN suspects s ON cs.suspect_id = s.id
            INNER JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON cs.added_by = u.id
            WHERE cs.case_id = ?
            ORDER BY cs.added_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all cases for a suspect
     */
    public function getBySuspectId(int $suspect_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, 
                   c.case_number, c.case_type, c.status as case_status,
                   c.incident_date, c.description as case_description,
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as added_by_name
            FROM case_suspects cs
            INNER JOIN cases c ON cs.case_id = c.id
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN users u ON cs.added_by = u.id
            WHERE cs.suspect_id = ?
            ORDER BY cs.added_date DESC
        ");
        $stmt->execute([$suspect_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if suspect is already linked to case
     */
    public function exists(int $case_id, int $suspect_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM case_suspects 
            WHERE case_id = ? AND suspect_id = ?
        ");
        $stmt->execute([$case_id, $suspect_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Add suspect to case
     */
    public function addSuspectToCase(int $case_id, int $suspect_id, int $added_by): bool
    {
        // Check if already exists
        if ($this->exists($case_id, $suspect_id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO case_suspects (case_id, suspect_id, added_by, added_date)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$case_id, $suspect_id, $added_by]);
    }
    
    /**
     * Remove suspect from case
     */
    public function removeSuspectFromCase(int $case_id, int $suspect_id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM case_suspects 
            WHERE case_id = ? AND suspect_id = ?
        ");
        return $stmt->execute([$case_id, $suspect_id]);
    }
    
    /**
     * Get suspect count for a case
     */
    public function countByCaseId(int $case_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM case_suspects 
            WHERE case_id = ?
        ");
        $stmt->execute([$case_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
