<?php

namespace App\Models;

class CaseWitness extends BaseModel
{
    protected string $table = 'case_witnesses';
    
    /**
     * Get all witnesses for a case
     */
    public function getByCaseId(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cw.*, 
                   w.witness_type,
                   p.first_name, p.middle_name, p.last_name, p.gender, 
                   p.contact, p.email, p.address,
                   CONCAT_WS(' ', u.first_name, u.last_name) as added_by_name
            FROM case_witnesses cw
            INNER JOIN witnesses w ON cw.witness_id = w.id
            INNER JOIN persons p ON w.person_id = p.id
            LEFT JOIN users u ON cw.added_by = u.id
            WHERE cw.case_id = ?
            ORDER BY cw.added_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all cases for a witness
     */
    public function getByWitnessId(int $witness_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cw.*, 
                   c.case_number, c.case_type, c.status as case_status,
                   c.incident_date, c.description as case_description,
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as added_by_name
            FROM case_witnesses cw
            INNER JOIN cases c ON cw.case_id = c.id
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN users u ON cw.added_by = u.id
            WHERE cw.witness_id = ?
            ORDER BY cw.added_date DESC
        ");
        $stmt->execute([$witness_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if witness is already linked to case
     */
    public function exists(int $case_id, int $witness_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM case_witnesses 
            WHERE case_id = ? AND witness_id = ?
        ");
        $stmt->execute([$case_id, $witness_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Add witness to case
     */
    public function addWitnessToCase(int $case_id, int $witness_id, int $added_by): bool
    {
        // Check if already exists
        if ($this->exists($case_id, $witness_id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO case_witnesses (case_id, witness_id, added_date)
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$case_id, $witness_id]);
    }
    
    /**
     * Remove witness from case
     */
    public function removeWitnessFromCase(int $case_id, int $witness_id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM case_witnesses 
            WHERE case_id = ? AND witness_id = ?
        ");
        return $stmt->execute([$case_id, $witness_id]);
    }
    
    /**
     * Get witness count for a case
     */
    public function countByCaseId(int $case_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM case_witnesses 
            WHERE case_id = ?
        ");
        $stmt->execute([$case_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Update witness type
     */
    public function updateWitnessType(int $case_id, int $witness_id, string $witness_type): bool
    {
        // Update in witnesses table
        $stmt = $this->db->prepare("
            UPDATE witnesses w
            INNER JOIN case_witnesses cw ON w.id = cw.witness_id
            SET w.witness_type = ?
            WHERE cw.case_id = ? AND cw.witness_id = ?
        ");
        return $stmt->execute([$witness_type, $case_id, $witness_id]);
    }
}
