<?php

namespace App\Models;

use PDO;

/**
 * DutyShift Model
 * 
 * Handles duty shift definitions
 */
class DutyShift extends BaseModel
{
    protected $table = 'duty_shifts';

    /**
     * Get all shifts
     */
    public function getAllShifts(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY start_time ASC";
        return $this->query($sql);
    }

    /**
     * Get shift by name
     */
    public function getByName(string $shiftName): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE shift_name = ?";
        $result = $this->query($sql, [$shiftName]);
        return $result[0] ?? null;
    }

    /**
     * Get current shift
     */
    public function getCurrentShift(): ?array
    {
        $currentTime = date('H:i:s');
        $sql = "SELECT * FROM {$this->table} 
                WHERE ? BETWEEN start_time AND end_time
                LIMIT 1";
        
        $result = $this->query($sql, [$currentTime]);
        return $result[0] ?? null;
    }
}
