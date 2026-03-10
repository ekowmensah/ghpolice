<?php

namespace App\Models;

class PatrolLog extends BaseModel
{
    protected string $table = 'patrol_logs';
    
    /**
     * Get patrols by station
     */
    public function getByStation(int $stationId, ?string $status = null, int $limit = 50): array
    {
        $sql = "
            SELECT 
                pl.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as leader_name,
                o.rank as leader_rank
            FROM {$this->table} pl
            JOIN officers o ON pl.patrol_leader_id = o.id
            WHERE pl.station_id = ?
        ";
        
        $params = [$stationId];
        
        if ($status) {
            $sql .= " AND pl.patrol_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY pl.start_time DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active patrols
     */
    public function getActivePatrols(?int $stationId = null): array
    {
        $sql = "
            SELECT 
                pl.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as leader_name,
                o.rank as leader_rank
            FROM {$this->table} pl
            JOIN stations s ON pl.station_id = s.id
            JOIN officers o ON pl.patrol_leader_id = o.id
            WHERE pl.patrol_status = 'In Progress'
        ";
        
        $params = [];
        
        if ($stationId) {
            $sql .= " AND pl.station_id = ?";
            $params[] = $stationId;
        }
        
        $sql .= " ORDER BY pl.start_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get patrol statistics
     */
    public function getStatistics(int $stationId, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_patrols,
                SUM(CASE WHEN patrol_status = 'Completed' THEN 1 ELSE 0 END) as completed_patrols,
                SUM(CASE WHEN patrol_status = 'In Progress' THEN 1 ELSE 0 END) as active_patrols,
                SUM(incidents_reported) as total_incidents,
                SUM(arrests_made) as total_arrests
            FROM {$this->table}
            WHERE station_id = ?
        ";
        
        $params = [$stationId];
        
        if ($startDate && $endDate) {
            $sql .= " AND DATE(start_time) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: [
            'total_patrols' => 0,
            'completed_patrols' => 0,
            'active_patrols' => 0,
            'total_incidents' => 0,
            'total_arrests' => 0
        ];
    }
    
    /**
     * Get patrols by officer
     */
    public function getByOfficer(int $officerId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                pl.*,
                s.station_name
            FROM {$this->table} pl
            JOIN stations s ON pl.station_id = s.id
            WHERE pl.patrol_leader_id = ? 
               OR pl.id IN (
                   SELECT patrol_id FROM patrol_officers WHERE officer_id = ?
               )
            ORDER BY pl.start_time DESC
            LIMIT ?
        ");
        $stmt->execute([$officerId, $officerId, $limit]);
        return $stmt->fetchAll();
    }
}
