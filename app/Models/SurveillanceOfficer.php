<?php

namespace App\Models;

class SurveillanceOfficer extends BaseModel
{
    protected string $table = 'surveillance_officers';
    
    /**
     * Get all officers for a surveillance operation
     */
    public function getBySurveillanceId(int $surveillance_id): array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name,
                   s.station_name
            FROM surveillance_officers so
            INNER JOIN officers o ON so.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE so.surveillance_id = ?
            ORDER BY so.role_in_surveillance, o.rank_id DESC
        ");
        $stmt->execute([$surveillance_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all surveillance operations for an officer
     */
    public function getByOfficerId(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, 
                   surv.operation_code, surv.operation_name, surv.surveillance_type,
                   surv.operation_status, surv.start_date, surv.end_date,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name
            FROM surveillance_officers so
            INNER JOIN surveillance_operations surv ON so.surveillance_id = surv.id
            LEFT JOIN officers o ON surv.operation_commander_id = o.id
            WHERE so.officer_id = ?
            ORDER BY surv.start_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if officer is already assigned to surveillance
     */
    public function exists(int $surveillance_id, int $officer_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM surveillance_officers 
            WHERE surveillance_id = ? AND officer_id = ?
        ");
        $stmt->execute([$surveillance_id, $officer_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Assign officer to surveillance operation
     */
    public function assignOfficer(int $surveillance_id, int $officer_id, string $role = 'Observer'): bool
    {
        // Check if already assigned
        if ($this->exists($surveillance_id, $officer_id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO surveillance_officers (surveillance_id, officer_id, role_in_surveillance, assigned_date)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$surveillance_id, $officer_id, $role]);
    }
    
    /**
     * Remove officer from surveillance operation
     */
    public function removeOfficer(int $surveillance_id, int $officer_id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM surveillance_officers 
            WHERE surveillance_id = ? AND officer_id = ?
        ");
        return $stmt->execute([$surveillance_id, $officer_id]);
    }
    
    /**
     * Update officer role in surveillance
     */
    public function updateRole(int $surveillance_id, int $officer_id, string $role): bool
    {
        $stmt = $this->db->prepare("
            UPDATE surveillance_officers 
            SET role_in_surveillance = ?
            WHERE surveillance_id = ? AND officer_id = ?
        ");
        return $stmt->execute([$role, $surveillance_id, $officer_id]);
    }
    
    /**
     * Count officers in surveillance operation
     */
    public function countBySurveillanceId(int $surveillance_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM surveillance_officers 
            WHERE surveillance_id = ?
        ");
        $stmt->execute([$surveillance_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Get officers by role
     */
    public function getByRole(int $surveillance_id, string $role): array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name
            FROM surveillance_officers so
            INNER JOIN officers o ON so.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE so.surveillance_id = ? AND so.role_in_surveillance = ?
            ORDER BY o.rank_id DESC
        ");
        $stmt->execute([$surveillance_id, $role]);
        return $stmt->fetchAll();
    }
}
