<?php

namespace App\Models;

class MissingPerson extends BaseModel
{
    protected string $table = 'missing_persons';
    
    public function getActive(): array
    {
        $sql = "
            SELECT 
                mp.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as investigating_officer_name
            FROM missing_persons mp
            JOIN stations s ON mp.station_id = s.id
            LEFT JOIN officers o ON mp.investigating_officer_id = o.id
            WHERE mp.status = 'Missing'
            ORDER BY mp.last_seen_date DESC
        ";
        
        return $this->query($sql);
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                mp.*,
                s.station_name
            FROM missing_persons mp
            JOIN stations s ON mp.station_id = s.id
            WHERE mp.status = ?
            ORDER BY mp.last_seen_date DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function markFound(int $id, string $status, array $data): bool
    {
        return $this->update($id, [
            'status' => $status,
            'found_date' => $data['found_date'] ?? date('Y-m-d'),
            'found_location' => $data['found_location'] ?? null,
            'found_condition' => $data['found_condition'] ?? null
        ]);
    }
}
