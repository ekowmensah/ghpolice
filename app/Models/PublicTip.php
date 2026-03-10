<?php

namespace App\Models;

use PDO;

/**
 * PublicTip Model
 * 
 * Handles public intelligence tips
 */
class PublicTip extends BaseModel
{
    protected $table = 'public_intelligence_tips';

    /**
     * Get all tips
     */
    public function getAllTips(int $limit = 100): array
    {
        $sql = "SELECT pt.*, o.first_name as assigned_first, o.last_name as assigned_last,
                       c.case_number
                FROM {$this->table} pt
                LEFT JOIN officers o ON pt.assigned_to = o.id
                LEFT JOIN cases c ON pt.case_id = c.id
                ORDER BY pt.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }

    /**
     * Get by verification status
     */
    public function getByVerificationStatus(string $status): array
    {
        $sql = "SELECT pt.*, o.first_name as assigned_first, o.last_name as assigned_last
                FROM {$this->table} pt
                LEFT JOIN officers o ON pt.assigned_to = o.id
                WHERE pt.verification_status = ?
                ORDER BY pt.created_at DESC";
        
        return $this->query($sql, [$status]);
    }

    /**
     * Get by tip source
     */
    public function getBySource(string $source): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE tip_source = ?
                ORDER BY created_at DESC";
        
        return $this->query($sql, [$source]);
    }

    /**
     * Get anonymous tips
     */
    public function getAnonymous(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_anonymous = 1
                ORDER BY created_at DESC";
        
        return $this->query($sql);
    }

    /**
     * Assign tip to officer
     */
    public function assignToOfficer(int $tipId, int $officerId): bool
    {
        $sql = "UPDATE {$this->table} SET assigned_to = ? WHERE id = ?";
        return $this->execute($sql, [$officerId, $tipId]) > 0;
    }

    /**
     * Update verification status
     */
    public function updateVerification(int $id, string $status): bool
    {
        $sql = "UPDATE {$this->table} SET verification_status = ? WHERE id = ?";
        return $this->execute($sql, [$status, $id]) > 0;
    }
}
