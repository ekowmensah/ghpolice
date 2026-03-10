<?php

namespace App\Models;

use PDO;

/**
 * AuditLog Model
 * 
 * Handles system audit logging
 */
class AuditLog extends BaseModel
{
    protected $table = 'audit_logs';

    /**
     * Log action
     */
    public function logAction(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, module, table_name, record_id, action_type, action_description, 
                 case_id, officer_id, suspect_id, evidence_id, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['user_id'],
            $data['module'],
            $data['table_name'] ?? null,
            $data['record_id'] ?? null,
            $data['action_type'],
            $data['action_description'],
            $data['case_id'] ?? null,
            $data['officer_id'] ?? null,
            $data['suspect_id'] ?? null,
            $data['evidence_id'] ?? null,
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get logs by user
     */
    public function getByUser(int $userId, int $limit = 100): array
    {
        $sql = "SELECT al.*, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.user_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$userId, $limit]);
    }

    /**
     * Get logs by module
     */
    public function getByModule(string $module, int $limit = 100): array
    {
        $sql = "SELECT al.*, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.module = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$module, $limit]);
    }

    /**
     * Get logs by action type
     */
    public function getByActionType(string $actionType, int $limit = 100): array
    {
        $sql = "SELECT al.*, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.action_type = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$actionType, $limit]);
    }

    /**
     * Get logs for case
     */
    public function getByCase(int $caseId): array
    {
        $sql = "SELECT al.*, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.case_id = ?
                ORDER BY al.created_at DESC";
        
        return $this->query($sql, [$caseId]);
    }

    /**
     * Search logs
     */
    public function search(array $filters, int $limit = 100): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $conditions[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['module'])) {
            $conditions[] = "al.module = ?";
            $params[] = $filters['module'];
        }

        if (!empty($filters['action_type'])) {
            $conditions[] = "al.action_type = ?";
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "al.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "al.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT al.*, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $params[] = $limit;
        return $this->query($sql, $params);
    }
}
