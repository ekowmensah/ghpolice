<?php

namespace App\Models;

use PDO;

/**
 * CustodyChain Model
 * 
 * Handles evidence custody chain tracking
 */
class CustodyChain extends BaseModel
{
    protected string $table = 'evidence_custody_chain';

    /**
     * Get custody chain for evidence
     */
    public function getByEvidence(int $evidenceId): array
    {
        $sql = "SELECT ecc.*, 
                       o1.first_name as from_first, o1.last_name as from_last,
                       o2.first_name as to_first, o2.last_name as to_last
                FROM {$this->table} ecc
                LEFT JOIN officers o1 ON ecc.transferred_from = o1.id
                LEFT JOIN officers o2 ON ecc.transferred_to = o2.id
                WHERE ecc.evidence_id = ?
                ORDER BY ecc.transfer_date DESC";
        
        return $this->query($sql, [$evidenceId]);
    }

    /**
     * Record custody transfer
     */
    public function recordTransfer(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (evidence_id, transferred_from, transferred_to, transfer_date, purpose, location, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['evidence_id'],
            $data['transferred_from'],
            $data['transferred_to'],
            $data['transfer_date'],
            $data['purpose'] ?? null,
            $data['location'] ?? null,
            $data['notes'] ?? null
        ]);
    }

    /**
     * Get current custody holder
     */
    public function getCurrentHolder(int $evidenceId): ?array
    {
        $sql = "SELECT ecc.*, o.first_name, o.last_name, o.service_number
                FROM {$this->table} ecc
                JOIN officers o ON ecc.transferred_to = o.id
                WHERE ecc.evidence_id = ?
                ORDER BY ecc.transfer_date DESC
                LIMIT 1";
        
        $result = $this->query($sql, [$evidenceId]);
        return $result[0] ?? null;
    }
}
