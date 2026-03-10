<?php

namespace App\Models;

class PatrolOfficer extends BaseModel
{
    protected string $table = 'patrol_officers';
    
    /**
     * Get all officers for a patrol
     */
    public function getByPatrolId(int $patrol_id): array
    {
        $stmt = $this->db->prepare("
            SELECT po.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name,
                   s.station_name
            FROM patrol_officers po
            INNER JOIN officers o ON po.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE po.patrol_id = ?
            ORDER BY po.role, o.rank_id DESC
        ");
        $stmt->execute([$patrol_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all patrols for an officer
     */
    public function getByOfficerId(int $officer_id, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT po.*, 
                   pl.patrol_number, pl.patrol_type, pl.patrol_area,
                   pl.start_time, pl.end_time, pl.patrol_status,
                   s.station_name,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as patrol_leader_name
            FROM patrol_officers po
            INNER JOIN patrol_logs pl ON po.patrol_id = pl.id
            LEFT JOIN stations s ON pl.station_id = s.id
            LEFT JOIN officers o ON pl.patrol_leader_id = o.id
            WHERE po.officer_id = ?
            ORDER BY pl.start_time DESC
            LIMIT ?
        ");
        $stmt->execute([$officer_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if officer is already assigned to patrol
     */
    public function exists(int $patrol_id, int $officer_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM patrol_officers 
            WHERE patrol_id = ? AND officer_id = ?
        ");
        $stmt->execute([$patrol_id, $officer_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Assign officer to patrol
     */
    public function assignOfficer(int $patrol_id, int $officer_id, string $role = 'Patrol Officer'): bool
    {
        // Check if already assigned
        if ($this->exists($patrol_id, $officer_id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO patrol_officers (patrol_id, officer_id, role)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$patrol_id, $officer_id, $role]);
    }
    
    /**
     * Remove officer from patrol
     */
    public function removeOfficer(int $patrol_id, int $officer_id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM patrol_officers 
            WHERE patrol_id = ? AND officer_id = ?
        ");
        return $stmt->execute([$patrol_id, $officer_id]);
    }
    
    /**
     * Update officer role in patrol
     */
    public function updateRole(int $patrol_id, int $officer_id, string $role): bool
    {
        $stmt = $this->db->prepare("
            UPDATE patrol_officers 
            SET role = ?
            WHERE patrol_id = ? AND officer_id = ?
        ");
        return $stmt->execute([$role, $patrol_id, $officer_id]);
    }
    
    /**
     * Count officers in patrol
     */
    public function countByPatrolId(int $patrol_id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM patrol_officers 
            WHERE patrol_id = ?
        ");
        $stmt->execute([$patrol_id]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Get officers by role
     */
    public function getByRole(int $patrol_id, string $role): array
    {
        $stmt = $this->db->prepare("
            SELECT po.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name
            FROM patrol_officers po
            INNER JOIN officers o ON po.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE po.patrol_id = ? AND po.role = ?
            ORDER BY o.rank_id DESC
        ");
        $stmt->execute([$patrol_id, $role]);
        return $stmt->fetchAll();
    }
    
    /**
     * Bulk assign officers to patrol
     */
    public function bulkAssign(int $patrol_id, array $officer_ids, string $default_role = 'Patrol Officer'): bool
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($officer_ids as $officer_id) {
                if (!$this->exists($patrol_id, $officer_id)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO patrol_officers (patrol_id, officer_id, role)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$patrol_id, $officer_id, $default_role]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
