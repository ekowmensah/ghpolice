<?php

namespace App\Models;

class IncidentReport extends BaseModel
{
    protected string $table = 'incident_reports';
    
    public function getByStation(int $stationId): array
    {
        $sql = "
            SELECT 
                ir.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as attending_officer_name,
                c.case_number
            FROM incident_reports ir
            JOIN officers o ON ir.attending_officer_id = o.id
            LEFT JOIN cases c ON ir.case_id = c.id
            WHERE ir.station_id = ?
            ORDER BY ir.incident_date DESC
        ";
        
        return $this->query($sql, [$stationId]);
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                ir.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as attending_officer_name
            FROM incident_reports ir
            JOIN stations s ON ir.station_id = s.id
            JOIN officers o ON ir.attending_officer_id = o.id
            WHERE ir.status = ?
            ORDER BY ir.incident_date DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function getByType(string $type): array
    {
        $sql = "
            SELECT 
                ir.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as attending_officer_name
            FROM incident_reports ir
            JOIN stations s ON ir.station_id = s.id
            JOIN officers o ON ir.attending_officer_id = o.id
            WHERE ir.incident_type = ?
            ORDER BY ir.incident_date DESC
        ";
        
        return $this->query($sql, [$type]);
    }
    
    public function escalateToCase(int $id, int $caseId): bool
    {
        return $this->update($id, [
            'escalated_to_case' => 1,
            'case_id' => $caseId,
            'status' => 'Closed'
        ]);
    }
}
