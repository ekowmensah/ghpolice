<?php

namespace App\Models;

class Operation extends BaseModel
{
    protected string $table = 'operations';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                op.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                pr.rank_name,
                o.service_number,
                s.station_name
            FROM operations op
            LEFT JOIN cases c ON op.case_id = c.id
            JOIN officers o ON op.operation_commander_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN stations s ON op.station_id = s.id
            WHERE op.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                op.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                pr.rank_name,
                s.station_name
            FROM operations op
            JOIN officers o ON op.operation_commander_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN stations s ON op.station_id = s.id
            WHERE op.operation_status = ?
            ORDER BY op.operation_date DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function getUpcoming(): array
    {
        $sql = "
            SELECT 
                op.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                pr.rank_name,
                s.station_name
            FROM operations op
            JOIN officers o ON op.operation_commander_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN stations s ON op.station_id = s.id
            WHERE op.operation_status = 'Planned'
            AND op.operation_date >= CURDATE()
            ORDER BY op.operation_date ASC
        ";
        
        return $this->query($sql);
    }
    
    public function getTeamMembers(int $operationId): array
    {
        $sql = "
            SELECT 
                oo.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number
            FROM operation_officers oo
            JOIN officers o ON oo.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE oo.operation_id = ?
            ORDER BY o.first_name
        ";
        
        return $this->query($sql, [$operationId]);
    }
    
    public function addTeamMember(int $operationId, int $officerId, ?string $role = null): bool
    {
        $sql = "
            INSERT INTO operation_officers (operation_id, officer_id, role_in_operation)
            VALUES (?, ?, ?)
        ";
        
        return $this->execute($sql, [$operationId, $officerId, $role]);
    }
    
    public function completeOperation(int $id, array $completionData): bool
    {
        return $this->update($id, [
            'operation_status' => 'Completed',
            'end_time' => $completionData['end_time'] ?? date('Y-m-d H:i:s'),
            'outcome_summary' => $completionData['outcome_summary'],
            'arrests_made' => $completionData['arrests_made'] ?? 0,
            'exhibits_seized' => $completionData['exhibits_seized'] ?? 0
        ]);
    }
}
