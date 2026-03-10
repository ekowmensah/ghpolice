<?php

namespace App\Models;

class OfficerDisciplinary extends BaseModel
{
    protected string $table = 'officer_disciplinary_records';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT 
                odr.*,
                c.case_number,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name
            FROM officer_disciplinary_records odr
            LEFT JOIN cases c ON odr.case_id = c.id
            LEFT JOIN users u ON odr.recorded_by = u.id
            WHERE odr.officer_id = ?
            ORDER BY odr.incident_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                odr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number,
                s.station_name
            FROM officer_disciplinary_records odr
            JOIN officers o ON odr.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE odr.status = ?
            ORDER BY odr.incident_date DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function getActiveActions(int $officerId): array
    {
        $sql = "
            SELECT * FROM officer_disciplinary_records
            WHERE officer_id = ?
            AND status = 'Action Taken'
            AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY start_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
}
