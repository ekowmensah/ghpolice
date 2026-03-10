<?php

namespace App\Models;

class Vehicle extends BaseModel
{
    protected string $table = 'vehicles';
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                v.*,
                c.case_number
            FROM vehicles v
            LEFT JOIN cases c ON v.case_id = c.id
            WHERE v.vehicle_status = ?
            ORDER BY v.created_at DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function searchByRegistration(string $registration): ?array
    {
        $sql = "
            SELECT 
                v.*,
                c.case_number,
                c.description as case_description
            FROM vehicles v
            LEFT JOIN cases c ON v.case_id = c.id
            WHERE v.registration_number = ?
        ";
        
        $result = $this->query($sql, [$registration]);
        return $result[0] ?? null;
    }
    
    public function searchByChassis(string $chassis): ?array
    {
        $sql = "
            SELECT 
                v.*,
                c.case_number
            FROM vehicles v
            LEFT JOIN cases c ON v.case_id = c.id
            WHERE v.chassis_number = ?
        ";
        
        $result = $this->query($sql, [$chassis]);
        return $result[0] ?? null;
    }
    
    public function updateStatus(int $id, string $status, ?int $caseId = null): bool
    {
        $data = ['vehicle_status' => $status];
        if ($caseId) {
            $data['case_id'] = $caseId;
        }
        return $this->update($id, $data);
    }
}
