<?php

namespace App\Models;

use PDO;

/**
 * UnitType Model
 * 
 * Handles police unit types (CID, Traffic, SWAT, etc.)
 */
class UnitType extends BaseModel
{
    protected $table = 'unit_types';

    /**
     * Get all unit types
     */
    public function getAllTypes(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY unit_type_name ASC";
        return $this->query($sql);
    }

    /**
     * Get units by type
     */
    public function getUnits(int $unitTypeId): array
    {
        $sql = "SELECT u.*, s.station_name
                FROM units u
                LEFT JOIN stations s ON u.station_id = s.id
                WHERE u.unit_type_id = ?
                ORDER BY u.unit_name ASC";
        
        return $this->query($sql, [$unitTypeId]);
    }

    /**
     * Get type statistics
     */
    public function getStatistics(): array
    {
        $sql = "SELECT ut.*, COUNT(u.id) as unit_count
                FROM {$this->table} ut
                LEFT JOIN units u ON ut.id = u.unit_type_id
                GROUP BY ut.id
                ORDER BY unit_count DESC";
        
        return $this->query($sql);
    }
}
