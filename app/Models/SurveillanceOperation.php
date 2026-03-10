<?php

namespace App\Models;

class SurveillanceOperation extends BaseModel
{
    protected string $table = 'surveillance_operations';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                so.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                pr.rank_name,
                o.service_number
            FROM surveillance_operations so
            LEFT JOIN cases c ON so.case_id = c.id
            JOIN officers o ON so.operation_commander_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE so.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getActive(): array
    {
        $sql = "
            SELECT 
                so.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                pr.rank_name
            FROM surveillance_operations so
            JOIN officers o ON so.operation_commander_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE so.operation_status IN ('Planned', 'In Progress')
            ORDER BY so.start_date DESC
        ";
        
        return $this->query($sql);
    }
    
    public function getTeamMembers(int $surveillanceId): array
    {
        $sql = "
            SELECT 
                sof.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number
            FROM surveillance_officers sof
            JOIN officers o ON sof.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE sof.surveillance_id = ?
            ORDER BY o.first_name
        ";
        
        return $this->query($sql, [$surveillanceId]);
    }
    
    public function addTeamMember(int $surveillanceId, int $officerId, ?string $role = null): bool
    {
        $sql = "
            INSERT INTO surveillance_officers (surveillance_id, officer_id, role_in_surveillance)
            VALUES (?, ?, ?)
        ";
        
        return $this->execute($sql, [$surveillanceId, $officerId, $role]);
    }
    
    public function removeTeamMember(int $surveillanceId, int $officerId): bool
    {
        $sql = "DELETE FROM surveillance_officers WHERE surveillance_id = ? AND officer_id = ?";
        return $this->execute($sql, [$surveillanceId, $officerId]);
    }
}
