<?php

namespace App\Models;

class OfficerLeave extends BaseModel
{
    protected string $table = 'officer_leave_records';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT 
                olr.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM officer_leave_records olr
            LEFT JOIN users u ON olr.approved_by = u.id
            WHERE olr.officer_id = ?
            ORDER BY olr.start_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getPendingLeaves(?int $officerId = null): array
    {
        $sql = "
            SELECT 
                olr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number,
                s.station_name
            FROM officer_leave_records olr
            JOIN officers o ON olr.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE olr.leave_status = 'Pending'
        ";
        
        $params = [];
        
        if ($officerId) {
            $sql .= " AND olr.officer_id = ?";
            $params[] = $officerId;
        }
        
        $sql .= " ORDER BY olr.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    public function approveLeave(int $id, int $approvedBy): bool
    {
        return $this->update($id, [
            'leave_status' => 'Approved',
            'approved_by' => $approvedBy,
            'approval_date' => date('Y-m-d')
        ]);
    }
    
    public function rejectLeave(int $id, int $rejectedBy, string $reason): bool
    {
        return $this->update($id, [
            'leave_status' => 'Rejected',
            'approved_by' => $rejectedBy,
            'approval_date' => date('Y-m-d'),
            'rejection_reason' => $reason
        ]);
    }
    
    public function getActiveLeaves(): array
    {
        $sql = "
            SELECT 
                olr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                s.station_name
            FROM officer_leave_records olr
            JOIN officers o ON olr.officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE olr.leave_status = 'Approved'
            AND olr.start_date <= CURDATE()
            AND olr.end_date >= CURDATE()
            ORDER BY olr.start_date
        ";
        
        return $this->query($sql);
    }
}
