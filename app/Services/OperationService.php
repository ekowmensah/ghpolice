<?php

namespace App\Services;

use App\Models\PatrolLog;
use App\Models\PatrolOfficer;
use App\Models\SurveillanceOperation;
use App\Models\SurveillanceOfficer;
use App\Models\DutyRoster;
use App\Models\Officer;
use App\Config\Database;
use PDO;

class OperationService
{
    private PatrolLog $patrolModel;
    private PatrolOfficer $patrolOfficerModel;
    private SurveillanceOperation $surveillanceModel;
    private SurveillanceOfficer $surveillanceOfficerModel;
    private DutyRoster $dutyRosterModel;
    private Officer $officerModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->patrolModel = new PatrolLog();
        $this->patrolOfficerModel = new PatrolOfficer();
        $this->surveillanceModel = new SurveillanceOperation();
        $this->surveillanceOfficerModel = new SurveillanceOfficer();
        $this->dutyRosterModel = new DutyRoster();
        $this->officerModel = new Officer();
        $this->db = Database::getConnection();
    }
    
    // ==================== PATROL MANAGEMENT ====================
    
    /**
     * Create patrol with team assignment
     */
    public function createPatrolWithTeam(array $patrolData, array $officer_ids, string $default_role = 'Patrol Officer'): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create patrol log
            $patrolId = $this->patrolModel->create($patrolData);
            
            // Bulk assign officers to patrol
            $this->patrolOfficerModel->bulkAssign($patrolId, $officer_ids, $default_role);
            
            $this->db->commit();
            
            logger("Patrol created with team: Patrol ID {$patrolId}, " . count($officer_ids) . " officers assigned");
            
            return $patrolId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to create patrol with team: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get patrol with complete team details
     */
    public function getPatrolWithTeam(int $patrol_id): ?array
    {
        $patrol = $this->patrolModel->find($patrol_id);
        
        if (!$patrol) {
            return null;
        }
        
        // Get patrol team
        $team = $this->patrolOfficerModel->getByPatrolId($patrol_id);
        
        $patrol['team'] = $team;
        $patrol['team_size'] = count($team);
        
        return $patrol;
    }
    
    /**
     * Assign additional officer to patrol
     */
    public function addOfficerToPatrol(int $patrol_id, int $officer_id, string $role = 'Patrol Officer'): bool
    {
        $result = $this->patrolOfficerModel->assignOfficer($patrol_id, $officer_id, $role);
        
        if ($result) {
            logger("Officer {$officer_id} added to patrol {$patrol_id} as {$role}");
        }
        
        return $result;
    }
    
    /**
     * Get officer's patrol history
     */
    public function getOfficerPatrolHistory(int $officer_id, int $limit = 50): array
    {
        return $this->patrolOfficerModel->getByOfficerId($officer_id, $limit);
    }
    
    /**
     * Get active patrols for a station
     */
    public function getActivePatrolsByStation(int $station_id): array
    {
        $stmt = $this->db->prepare("
            SELECT pl.*, 
                   COUNT(po.id) as team_size,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as leader_name,
                   pr.rank_name as leader_rank
            FROM patrol_logs pl
            LEFT JOIN patrol_officers po ON pl.id = po.patrol_id
            LEFT JOIN officers o ON pl.patrol_leader_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE pl.station_id = ? AND pl.patrol_status = 'In Progress'
            GROUP BY pl.id
            ORDER BY pl.start_time DESC
        ");
        $stmt->execute([$station_id]);
        return $stmt->fetchAll();
    }
    
    // ==================== SURVEILLANCE MANAGEMENT ====================
    
    /**
     * Create surveillance operation with team
     */
    public function createSurveillanceWithTeam(array $operationData, array $officers): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create surveillance operation
            $operationId = $this->surveillanceModel->create($operationData);
            
            // Assign officers with roles
            foreach ($officers as $officer) {
                $this->surveillanceOfficerModel->assignOfficer(
                    $operationId,
                    $officer['officer_id'],
                    $officer['role'] ?? 'Observer'
                );
            }
            
            $this->db->commit();
            
            logger("Surveillance operation created: Operation ID {$operationId}, " . count($officers) . " officers assigned");
            
            return $operationId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to create surveillance operation: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get surveillance operation with team
     */
    public function getSurveillanceWithTeam(int $operation_id): ?array
    {
        $operation = $this->surveillanceModel->find($operation_id);
        
        if (!$operation) {
            return null;
        }
        
        // Get operation team
        $team = $this->surveillanceOfficerModel->getBySurveillanceId($operation_id);
        
        $operation['team'] = $team;
        $operation['team_size'] = count($team);
        
        return $operation;
    }
    
    /**
     * Get active surveillance operations
     */
    public function getActiveSurveillanceOperations(): array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, 
                   COUNT(sof.id) as team_size,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                   pr.rank_name as commander_rank
            FROM surveillance_operations so
            LEFT JOIN surveillance_officers sof ON so.id = sof.surveillance_id
            LEFT JOIN officers o ON so.operation_commander_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE so.operation_status IN ('Planned', 'Active')
            GROUP BY so.id
            ORDER BY so.start_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // ==================== DUTY ROSTER MANAGEMENT ====================
    
    /**
     * Create duty roster for multiple officers
     */
    public function createBulkDutyRoster(array $roster_entries): int
    {
        try {
            $this->db->beginTransaction();
            
            $count = 0;
            foreach ($roster_entries as $entry) {
                $this->dutyRosterModel->create($entry);
                $count++;
            }
            
            $this->db->commit();
            
            logger("Bulk duty roster created: {$count} entries");
            
            return $count;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to create bulk duty roster: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get duty roster for a station and date range
     */
    public function getStationDutyRoster(int $station_id, string $start_date, string $end_date): array
    {
        $stmt = $this->db->prepare("
            SELECT dr.*, 
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   o.service_number,
                   pr.rank_name,
                   ds.shift_name,
                   ds.start_time,
                   ds.end_time
            FROM duty_roster dr
            INNER JOIN officers o ON dr.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.station_id = ? 
              AND dr.duty_date BETWEEN ? AND ?
            ORDER BY dr.duty_date, ds.start_time, pr.rank_level DESC
        ");
        $stmt->execute([$station_id, $start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get officer's duty schedule
     */
    public function getOfficerDutySchedule(int $officer_id, string $start_date, string $end_date): array
    {
        $stmt = $this->db->prepare("
            SELECT dr.*, 
                   s.station_name,
                   ds.shift_name,
                   ds.start_time,
                   ds.end_time
            FROM duty_roster dr
            LEFT JOIN stations s ON dr.station_id = s.id
            LEFT JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.officer_id = ? 
              AND dr.duty_date BETWEEN ? AND ?
            ORDER BY dr.duty_date, ds.start_time
        ");
        $stmt->execute([$officer_id, $start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    // ==================== OPERATION STATISTICS ====================
    
    /**
     * Get operation statistics for a date range
     */
    public function getOperationStatistics(string $start_date, string $end_date): array
    {
        // Patrol statistics
        $patrolStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_patrols,
                SUM(CASE WHEN patrol_status = 'Completed' THEN 1 ELSE 0 END) as completed_patrols,
                SUM(CASE WHEN patrol_status = 'In Progress' THEN 1 ELSE 0 END) as active_patrols,
                COUNT(DISTINCT patrol_leader_id) as officers_on_patrol
            FROM patrol_logs
            WHERE start_time BETWEEN ? AND ?
        ");
        $patrolStmt->execute([$start_date, $end_date]);
        $patrolStats = $patrolStmt->fetch();
        
        // Surveillance statistics
        $survStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_operations,
                SUM(CASE WHEN operation_status = 'Completed' THEN 1 ELSE 0 END) as completed_operations,
                SUM(CASE WHEN operation_status = 'Active' THEN 1 ELSE 0 END) as active_operations
            FROM surveillance_operations
            WHERE start_date BETWEEN ? AND ?
        ");
        $survStmt->execute([$start_date, $end_date]);
        $survStats = $survStmt->fetch();
        
        return [
            'patrols' => $patrolStats,
            'surveillance' => $survStats,
            'period' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ];
    }
    
    /**
     * Get officer availability for operations
     */
    public function getAvailableOfficers(int $station_id, string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                o.service_number,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                dr.shift_id,
                ds.shift_name,
                CASE 
                    WHEN po.patrol_id IS NOT NULL THEN 'On Patrol'
                    WHEN so.surveillance_id IS NOT NULL THEN 'On Surveillance'
                    WHEN dr.id IS NOT NULL THEN 'On Duty'
                    ELSE 'Available'
                END as status
            FROM officers o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN duty_roster dr ON o.id = dr.officer_id AND dr.duty_date = ?
            LEFT JOIN duty_shifts ds ON dr.shift_id = ds.id
            LEFT JOIN patrol_officers po ON o.id = po.officer_id 
                AND EXISTS (
                    SELECT 1 FROM patrol_logs pl 
                    WHERE pl.id = po.patrol_id 
                    AND DATE(pl.start_time) = ? 
                    AND pl.patrol_status = 'In Progress'
                )
            LEFT JOIN surveillance_officers so ON o.id = so.officer_id
                AND EXISTS (
                    SELECT 1 FROM surveillance_operations sop
                    WHERE sop.id = so.surveillance_id
                    AND DATE(sop.start_date) <= ?
                    AND (sop.end_date IS NULL OR DATE(sop.end_date) >= ?)
                    AND sop.operation_status = 'Active'
                )
            WHERE o.current_station_id = ? 
              AND o.employment_status = 'Active'
            ORDER BY pr.rank_level DESC, o.last_name
        ");
        $stmt->execute([$date, $date, $date, $date, $station_id]);
        return $stmt->fetchAll();
    }
}
