<?php

namespace App\Models;

class PersonAlias extends BaseModel
{
    protected string $table = 'person_aliases';
    
    public function getByPersonId(int $personId): array
    {
        $sql = "
            SELECT * FROM person_aliases
            WHERE person_id = ?
            ORDER BY created_at DESC
        ";
        
        return $this->query($sql, [$personId]);
    }
    
    public function searchByAlias(string $alias): array
    {
        $sql = "
            SELECT 
                pa.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as person_name,
                p.ghana_card_number,
                p.contact
            FROM person_aliases pa
            JOIN persons p ON pa.person_id = p.id
            WHERE pa.alias_name LIKE ?
            ORDER BY pa.alias_name
        ";
        
        return $this->query($sql, ['%' . $alias . '%']);
    }
}
