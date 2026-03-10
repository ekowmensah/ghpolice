<?php

namespace App\Models;

class OfficerTraining extends BaseModel
{
    protected string $table = 'officer_training';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT * FROM officer_training
            WHERE officer_id = ?
            ORDER BY start_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getUpcomingTrainings(?int $officerId = null): array
    {
        $sql = "
            SELECT 
                ot.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number
            FROM officer_training ot
            JOIN officers o ON ot.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE ot.start_date >= CURDATE()
        ";
        
        $params = [];
        
        if ($officerId) {
            $sql .= " AND ot.officer_id = ?";
            $params[] = $officerId;
        }
        
        $sql .= " ORDER BY ot.start_date ASC";
        
        return $this->query($sql, $params);
    }
    
    public function getByType(string $type): array
    {
        $sql = "
            SELECT 
                ot.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name
            FROM officer_training ot
            JOIN officers o ON ot.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE ot.training_type = ?
            ORDER BY ot.start_date DESC
        ";
        
        return $this->query($sql, [$type]);
    }
}
