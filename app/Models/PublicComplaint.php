<?php

namespace App\Models;

class PublicComplaint extends BaseModel
{
    protected string $table = 'public_complaints';
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                pc.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name
            FROM public_complaints pc
            JOIN stations s ON pc.station_id = s.id
            LEFT JOIN officers o ON pc.officer_complained_against = o.id
            WHERE pc.complaint_status = ?
            ORDER BY pc.created_at DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function getByStation(int $stationId): array
    {
        $sql = "
            SELECT 
                pc.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name
            FROM public_complaints pc
            LEFT JOIN officers o ON pc.officer_complained_against = o.id
            WHERE pc.station_id = ?
            ORDER BY pc.created_at DESC
        ";
        
        return $this->query($sql, [$stationId]);
    }
    
    public function getByOfficer(int $officerId): array
    {
        $sql = "
            SELECT 
                pc.*,
                s.station_name
            FROM public_complaints pc
            JOIN stations s ON pc.station_id = s.id
            WHERE pc.officer_complained_against = ?
            ORDER BY pc.created_at DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function updateStatus(int $id, string $status, ?string $resolution = null): bool
    {
        $data = ['complaint_status' => $status];
        
        if ($resolution) {
            $data['resolution'] = $resolution;
            $data['resolved_date'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id, $data);
    }
}
