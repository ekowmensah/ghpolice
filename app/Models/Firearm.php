<?php

namespace App\Models;

class Firearm extends BaseModel
{
    protected string $table = 'firearms';
    
    public function getByStation(int $stationId): array
    {
        $sql = "
            SELECT 
                f.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as current_holder_name,
                pr.rank_name
            FROM firearms f
            LEFT JOIN officers o ON f.current_holder_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE f.station_id = ?
            ORDER BY f.serial_number
        ";
        
        return $this->query($sql, [$stationId]);
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                f.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as current_holder_name
            FROM firearms f
            JOIN stations s ON f.station_id = s.id
            LEFT JOIN officers o ON f.current_holder_id = o.id
            WHERE f.firearm_status = ?
            ORDER BY f.serial_number
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function getAvailable(int $stationId): array
    {
        $sql = "
            SELECT * FROM firearms
            WHERE station_id = ?
            AND firearm_status = 'In Armory'
            AND current_holder_id IS NULL
            ORDER BY serial_number
        ";
        
        return $this->query($sql, [$stationId]);
    }
}
