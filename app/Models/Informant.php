<?php

namespace App\Models;

use PDO;

/**
 * Informant Model
 * 
 * Handles confidential informant management
 */
class Informant extends BaseModel
{
    protected $table = 'informants';

    /**
     * Get all informants
     */
    public function getAllInformants(): array
    {
        $sql = "SELECT i.*, o.first_name as handler_first, o.last_name as handler_last,
                       s.station_name
                FROM {$this->table} i
                LEFT JOIN officers o ON i.handler_officer_id = o.id
                LEFT JOIN stations s ON i.station_id = s.id
                ORDER BY i.created_at DESC";
        
        return $this->query($sql);
    }

    /**
     * Get by handler
     */
    public function getByHandler(int $officerId): array
    {
        $sql = "SELECT i.*, s.station_name
                FROM {$this->table} i
                LEFT JOIN stations s ON i.station_id = s.id
                WHERE i.handler_officer_id = ?
                ORDER BY i.created_at DESC";
        
        return $this->query($sql, [$officerId]);
    }

    /**
     * Get active informants
     */
    public function getActive(): array
    {
        $sql = "SELECT i.*, o.first_name as handler_first, o.last_name as handler_last
                FROM {$this->table} i
                LEFT JOIN officers o ON i.handler_officer_id = o.id
                WHERE i.status = 'Active'
                ORDER BY i.reliability_rating DESC";
        
        return $this->query($sql);
    }

    /**
     * Get by reliability
     */
    public function getByReliability(string $reliability): array
    {
        $sql = "SELECT i.*, o.first_name as handler_first, o.last_name as handler_last
                FROM {$this->table} i
                LEFT JOIN officers o ON i.handler_officer_id = o.id
                WHERE i.reliability_rating = ?
                ORDER BY i.created_at DESC";
        
        return $this->query($sql, [$reliability]);
    }

    /**
     * Update reliability rating
     */
    public function updateReliability(int $id, string $rating): bool
    {
        $sql = "UPDATE {$this->table} SET reliability_rating = ? WHERE id = ?";
        return $this->execute($sql, [$rating, $id]) > 0;
    }
}
