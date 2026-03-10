<?php

namespace App\Models;

use PDO;

class PersonRelationship
{
    private PDO $db;
    
    // Relationship type mappings with their inverses
    private const RELATIONSHIP_INVERSES = [
        // Symmetric relationships (same both ways)
        'Friend' => 'Friend',
        'Colleague' => 'Colleague',
        'Neighbor' => 'Neighbor',
        'Acquaintance' => 'Acquaintance',
        'Sibling' => 'Sibling',
        'Twin' => 'Twin',
        'Spouse' => 'Spouse',
        'Partner' => 'Partner',
        'Cousin' => 'Cousin',
        
        // Asymmetric relationships (different inverse)
        'Parent' => 'Child',
        'Child' => 'Parent',
        'Father' => 'Child',
        'Mother' => 'Child',
        'Son' => 'Parent',
        'Daughter' => 'Parent',
        
        'Grandparent' => 'Grandchild',
        'Grandchild' => 'Grandparent',
        'Grandfather' => 'Grandchild',
        'Grandmother' => 'Grandchild',
        'Grandson' => 'Grandparent',
        'Granddaughter' => 'Grandparent',
        
        'Uncle' => 'Nephew/Niece',
        'Aunt' => 'Nephew/Niece',
        'Nephew' => 'Uncle/Aunt',
        'Niece' => 'Uncle/Aunt',
        'Nephew/Niece' => 'Uncle/Aunt',
        'Uncle/Aunt' => 'Nephew/Niece',
        
        'Employer' => 'Employee',
        'Employee' => 'Employer',
        'Guardian' => 'Ward',
        'Ward' => 'Guardian',
    ];
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get all available relationship types
     */
    public static function getRelationshipTypes(): array
    {
        return [
            'Family' => [
                'Parent', 'Father', 'Mother', 'Child', 'Son', 'Daughter',
                'Sibling', 'Twin',
                'Grandparent', 'Grandfather', 'Grandmother', 'Grandchild', 'Grandson', 'Granddaughter',
                'Uncle', 'Aunt', 'Nephew', 'Niece',
                'Cousin',
                'Spouse', 'Partner'
            ],
            'Social' => [
                'Friend', 'Colleague', 'Neighbor', 'Acquaintance'
            ],
            'Legal' => [
                'Guardian', 'Ward', 'Employer', 'Employee'
            ]
        ];
    }
    
    /**
     * Create bidirectional relationship between two persons
     */
    public function createRelationship(int $personId1, int $personId2, string $relationshipType, ?string $notes = null, ?int $createdBy = null): bool
    {
        if ($personId1 === $personId2) {
            throw new \Exception("Cannot create relationship with self");
        }
        
        // Get inverse relationship type
        $inverseType = self::RELATIONSHIP_INVERSES[$relationshipType] ?? $relationshipType;
        
        try {
            $this->db->beginTransaction();
            
            // Create forward relationship (Person1 -> Person2)
            $stmt = $this->db->prepare("
                INSERT INTO person_relationships (person_id_1, person_id_2, relationship_type, notes, created_by)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE notes = VALUES(notes)
            ");
            $stmt->execute([$personId1, $personId2, $relationshipType, $notes, $createdBy]);
            
            // Create reverse relationship (Person2 -> Person1)
            $stmt = $this->db->prepare("
                INSERT INTO person_relationships (person_id_1, person_id_2, relationship_type, notes, created_by)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE notes = VALUES(notes)
            ");
            $stmt->execute([$personId2, $personId1, $inverseType, $notes, $createdBy]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get all relationships for a person
     */
    public function getRelationshipsForPerson(int $personId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                pr.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as related_person_name,
                p.ghana_card_number,
                p.contact,
                p.email,
                CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
            FROM person_relationships pr
            JOIN persons p ON pr.person_id_2 = p.id
            LEFT JOIN users u ON pr.created_by = u.id
            WHERE pr.person_id_1 = ?
            ORDER BY pr.relationship_type, p.first_name
        ");
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Delete bidirectional relationship
     */
    public function deleteRelationship(int $relationshipId): bool
    {
        // Get the relationship details first
        $stmt = $this->db->prepare("
            SELECT person_id_1, person_id_2, relationship_type 
            FROM person_relationships 
            WHERE id = ?
        ");
        $stmt->execute([$relationshipId]);
        $relationship = $stmt->fetch();
        
        if (!$relationship) {
            return false;
        }
        
        $inverseType = self::RELATIONSHIP_INVERSES[$relationship['relationship_type']] ?? $relationship['relationship_type'];
        
        try {
            $this->db->beginTransaction();
            
            // Delete forward relationship
            $stmt = $this->db->prepare("DELETE FROM person_relationships WHERE id = ?");
            $stmt->execute([$relationshipId]);
            
            // Delete reverse relationship
            $stmt = $this->db->prepare("
                DELETE FROM person_relationships 
                WHERE person_id_1 = ? 
                AND person_id_2 = ? 
                AND relationship_type = ?
            ");
            $stmt->execute([
                $relationship['person_id_2'],
                $relationship['person_id_1'],
                $inverseType
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Check if relationship exists
     */
    public function relationshipExists(int $personId1, int $personId2, string $relationshipType): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM person_relationships 
            WHERE person_id_1 = ? AND person_id_2 = ? AND relationship_type = ?
        ");
        $stmt->execute([$personId1, $personId2, $relationshipType]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
