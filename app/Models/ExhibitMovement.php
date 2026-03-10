<?php

namespace App\Models;

use PDO;

/**
 * ExhibitMovement Model
 * 
 * Handles exhibit movement tracking and chain of custody
 */
class ExhibitMovement extends BaseModel
{
    protected $table = 'exhibit_movements';

    /**
     * Get movements for an exhibit
     */
    public function getByExhibit(int $exhibitId): array
    {
        $sql = "SELECT em.*, 
                       o1.first_name as moved_by_first, o1.last_name as moved_by_last,
                       o2.first_name as received_by_first, o2.last_name as received_by_last
                FROM {$this->table} em
                LEFT JOIN officers o1 ON em.moved_by = o1.id
                LEFT JOIN officers o2 ON em.received_by = o2.id
                WHERE em.exhibit_id = ?
                ORDER BY em.movement_date DESC";
        
        return $this->query($sql, [$exhibitId]);
    }

    /**
     * Record exhibit movement
     */
    public function recordMovement(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (exhibit_id, moved_from, moved_to, moved_by, received_by, movement_date, purpose, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['exhibit_id'],
            $data['moved_from'],
            $data['moved_to'],
            $data['moved_by'],
            $data['received_by'] ?? null,
            $data['movement_date'],
            $data['purpose'] ?? null,
            $data['notes'] ?? null
        ]);
    }

    /**
     * Get recent movements
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT em.*, e.exhibit_number,
                       o1.first_name as moved_by_first, o1.last_name as moved_by_last
                FROM {$this->table} em
                JOIN exhibits e ON em.exhibit_id = e.id
                LEFT JOIN officers o1 ON em.moved_by = o1.id
                ORDER BY em.movement_date DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }
}
