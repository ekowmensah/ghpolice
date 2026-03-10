<?php

namespace App\Models;

use PDO;

/**
 * SensitiveDataAccessLog Model
 * 
 * Handles sensitive data access logging for compliance
 */
class SensitiveDataAccessLog extends BaseModel
{
    protected $table = 'sensitive_data_access_log';

    /**
     * Log access
     */
    public function logAccess(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, table_name, record_id, access_type, access_reason, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['user_id'],
            $data['table_name'],
            $data['record_id'],
            $data['access_type'],
            $data['access_reason'] ?? null,
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get access logs by user
     */
    public function getByUser(int $userId, int $limit = 100): array
    {
        $sql = "SELECT sdal.*, u.username
                FROM {$this->table} sdal
                LEFT JOIN users u ON sdal.user_id = u.id
                WHERE sdal.user_id = ?
                ORDER BY sdal.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$userId, $limit]);
    }

    /**
     * Get access logs for record
     */
    public function getByRecord(string $tableName, int $recordId): array
    {
        $sql = "SELECT sdal.*, u.username
                FROM {$this->table} sdal
                LEFT JOIN users u ON sdal.user_id = u.id
                WHERE sdal.table_name = ? AND sdal.record_id = ?
                ORDER BY sdal.created_at DESC";
        
        return $this->query($sql, [$tableName, $recordId]);
    }

    /**
     * Get by access type
     */
    public function getByAccessType(string $accessType, int $limit = 100): array
    {
        $sql = "SELECT sdal.*, u.username
                FROM {$this->table} sdal
                LEFT JOIN users u ON sdal.user_id = u.id
                WHERE sdal.access_type = ?
                ORDER BY sdal.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$accessType, $limit]);
    }

    /**
     * Get recent access
     */
    public function getRecent(int $limit = 100): array
    {
        $sql = "SELECT sdal.*, u.username
                FROM {$this->table} sdal
                LEFT JOIN users u ON sdal.user_id = u.id
                ORDER BY sdal.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }
}
