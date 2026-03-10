<?php

namespace App\Models;

class Suspect extends BaseModel
{
    protected string $table = 'suspects';
    
    /**
     * Get suspect with person details
     */
    public function getWithPerson(int $suspectId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                p.first_name,
                p.middle_name,
                p.last_name,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                p.ghana_card_number,
                p.contact,
                p.address,
                p.has_criminal_record,
                p.is_wanted,
                p.risk_level
            FROM suspects s
            JOIN persons p ON s.person_id = p.id
            WHERE s.id = ?
        ");
        $stmt->execute([$suspectId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get suspects for a case
     */
    public function getByCaseId(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                p.ghana_card_number,
                p.contact,
                p.has_criminal_record,
                p.is_wanted,
                p.risk_level,
                cs.added_date
            FROM case_suspects cs
            JOIN suspects s ON cs.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE cs.case_id = ?
            ORDER BY cs.added_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Link suspect to case
     */
    public function linkToCase(int $suspectId, int $caseId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_suspects (case_id, suspect_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$caseId, $suspectId]);
    }
    
    /**
     * Unlink suspect from case
     */
    public function unlinkFromCase(int $suspectId, int $caseId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM case_suspects 
            WHERE case_id = ? AND suspect_id = ?
        ");
        return $stmt->execute([$caseId, $suspectId]);
    }
    
    /**
     * Get all cases where person is a suspect
     */
    public function getByPersonId(int $personId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.case_id
            FROM case_suspects cs
            JOIN suspects s ON cs.suspect_id = s.id
            WHERE s.person_id = ?
        ");
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }
}
