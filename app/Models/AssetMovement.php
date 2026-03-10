<?php

namespace App\Models;

use PDO;

/**
 * AssetMovement Model
 * 
 * Handles asset movement tracking
 */
class AssetMovement extends BaseModel
{
    protected $table = 'asset_movements';

    /**
     * Get movements for asset
     */
    public function getByAsset(int $assetId): array
    {
        $sql = "SELECT am.*, o.first_name, o.last_name, o.service_number
                FROM {$this->table} am
                LEFT JOIN officers o ON am.moved_by = o.id
                WHERE am.asset_id = ?
                ORDER BY am.movement_date DESC";
        
        return $this->query($sql, [$assetId]);
    }

    /**
     * Record movement
     */
    public function recordMovement(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (asset_id, from_location, to_location, movement_date, moved_by, purpose, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['asset_id'],
            $data['from_location'],
            $data['to_location'],
            $data['movement_date'],
            $data['moved_by'],
            $data['purpose'] ?? null,
            $data['notes'] ?? null
        ]);
    }

    /**
     * Get recent movements
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT am.*, a.asset_name, o.first_name, o.last_name
                FROM {$this->table} am
                JOIN assets a ON am.asset_id = a.id
                LEFT JOIN officers o ON am.moved_by = o.id
                ORDER BY am.movement_date DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }
}
