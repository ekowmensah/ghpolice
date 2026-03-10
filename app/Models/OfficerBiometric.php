<?php

namespace App\Models;

use PDO;

/**
 * OfficerBiometric Model
 * 
 * Handles officer biometric data
 */
class OfficerBiometric extends BaseModel
{
    protected string $table = 'officer_biometrics';

    /**
     * Get biometrics for officer
     */
    public function getByOfficer(int $officerId): array
    {
        $sql = "SELECT ob.*, o.first_name as captured_by_first, o.last_name as captured_by_last
                FROM {$this->table} ob
                LEFT JOIN officers o ON ob.captured_by = o.id
                WHERE ob.officer_id = ?
                ORDER BY ob.captured_date DESC";
        
        return $this->query($sql, [$officerId]);
    }

    /**
     * Register biometric
     */
    public function register(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (officer_id, biometric_type, file_path, captured_by, captured_date)
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['officer_id'],
            $data['biometric_type'],
            $data['file_path'],
            $data['captured_by'],
            $data['captured_date'] ?? date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get by biometric type
     */
    public function getByType(int $officerId, string $biometricType): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE officer_id = ? AND biometric_type = ?
                ORDER BY captured_date DESC
                LIMIT 1";
        
        $result = $this->query($sql, [$officerId, $biometricType]);
        return $result[0] ?? null;
    }

    /**
     * Check if biometric exists
     */
    public function hasBiometric(int $officerId, string $biometricType): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE officer_id = ? AND biometric_type = ?";
        
        $result = $this->query($sql, [$officerId, $biometricType]);
        return ($result[0]['count'] ?? 0) > 0;
    }
}
