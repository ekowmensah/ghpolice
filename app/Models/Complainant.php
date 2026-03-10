<?php

namespace App\Models;

class Complainant extends BaseModel
{
    protected string $table = 'complainants';
    
    /**
     * Get complainant with person details
     */
    public function getWithPerson(int $complainantId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                p.ghana_card_number,
                p.contact,
                p.email,
                p.address
            FROM complainants c
            JOIN persons p ON c.person_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$complainantId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get cases for a complainant
     */
    public function getCases(int $complainantId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                s.station_name
            FROM cases c
            LEFT JOIN stations s ON c.station_id = s.id
            WHERE c.complainant_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$complainantId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create complainant from person
     */
    public function createFromPerson(int $personId, string $type = 'Individual', ?string $organizationName = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO complainants (person_id, complainant_type, organization_name)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$personId, $type, $organizationName]);
        return (int)$this->db->lastInsertId();
    }
}
