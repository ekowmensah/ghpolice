<?php

namespace App\Models;

use PDO;

/**
 * PatrolIncident Model
 * 
 * Handles incidents encountered during patrols
 */
class PatrolIncident extends BaseModel
{
    protected $table = 'patrol_incidents';

    /**
     * Get incidents for patrol
     */
    public function getByPatrol(int $patrolId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE patrol_id = ?
                ORDER BY incident_time ASC";
        
        return $this->query($sql, [$patrolId]);
    }

    /**
     * Record incident
     */
    public function recordIncident(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (patrol_id, incident_time, incident_location, incident_type, action_taken, case_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['patrol_id'],
            $data['incident_time'],
            $data['incident_location'],
            $data['incident_type'],
            $data['action_taken'] ?? null,
            $data['case_id'] ?? null
        ]);
    }

    /**
     * Get incidents by type
     */
    public function getByType(string $incidentType, int $limit = 100): array
    {
        $sql = "SELECT pi.*, pl.patrol_number, pl.patrol_area
                FROM {$this->table} pi
                JOIN patrol_logs pl ON pi.patrol_id = pl.id
                WHERE pi.incident_type = ?
                ORDER BY pi.incident_time DESC
                LIMIT ?";
        
        return $this->query($sql, [$incidentType, $limit]);
    }
}
