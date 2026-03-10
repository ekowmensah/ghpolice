<?php

namespace App\Models;

class Witness extends BaseModel
{
    protected string $table = 'witnesses';
    
    /**
     * Get witness with person details
     */
    public function getWithPerson(int $witnessId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                w.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                p.contact,
                p.email,
                p.address
            FROM witnesses w
            JOIN persons p ON w.person_id = p.id
            WHERE w.id = ?
        ");
        $stmt->execute([$witnessId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get witnesses for a case
     */
    public function getByCaseId(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                w.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                p.contact,
                p.email,
                cw.added_date
            FROM case_witnesses cw
            JOIN witnesses w ON cw.witness_id = w.id
            JOIN persons p ON w.person_id = p.id
            WHERE cw.case_id = ?
            ORDER BY cw.added_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create witness from person
     */
    public function createFromPerson(int $personId, string $witnessType = 'Eye Witness'): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO witnesses (person_id, witness_type)
            VALUES (?, ?)
        ");
        $stmt->execute([$personId, $witnessType]);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Link witness to case
     */
    public function linkToCase(int $witnessId, int $caseId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_witnesses (case_id, witness_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$caseId, $witnessId]);
    }
    
    /**
     * Unlink witness from case
     */
    public function unlinkFromCase(int $witnessId, int $caseId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM case_witnesses 
            WHERE case_id = ? AND witness_id = ?
        ");
        return $stmt->execute([$caseId, $witnessId]);
    }
}
