<?php

namespace App\Models;

class OfficerCommendation extends BaseModel
{
    protected string $table = 'officer_commendations';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT * FROM officer_commendations
            WHERE officer_id = ?
            ORDER BY award_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getByType(string $type): array
    {
        $sql = "
            SELECT 
                oc.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number,
                s.station_name
            FROM officer_commendations oc
            JOIN officers o ON oc.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE oc.commendation_type = ?
            ORDER BY oc.award_date DESC
        ";
        
        return $this->query($sql, [$type]);
    }
    
    public function getRecentCommendations(int $limit = 10): array
    {
        $sql = "
            SELECT 
                oc.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number
            FROM officer_commendations oc
            JOIN officers o ON oc.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            ORDER BY oc.award_date DESC
            LIMIT ?
        ";
        
        return $this->query($sql, [$limit]);
    }
}
