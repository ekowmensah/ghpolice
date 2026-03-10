<?php

namespace App\Models;

use PDO;

/**
 * InformantIntelligence Model
 * 
 * Handles intelligence provided by informants
 */
class InformantIntelligence extends BaseModel
{
    protected $table = 'informant_intelligence';

    /**
     * Get intelligence by informant
     */
    public function getByInformant(int $informantId): array
    {
        $sql = "SELECT ii.*, o.first_name as handler_first, o.last_name as handler_last,
                       c.case_number
                FROM {$this->table} ii
                LEFT JOIN officers o ON ii.handler_officer_id = o.id
                LEFT JOIN cases c ON ii.case_id = c.id
                WHERE ii.informant_id = ?
                ORDER BY ii.intelligence_date DESC";
        
        return $this->query($sql, [$informantId]);
    }

    /**
     * Record intelligence
     */
    public function recordIntelligence(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (informant_id, intelligence_date, intelligence_type, intelligence_details, 
                 verification_status, case_id, handler_officer_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['informant_id'],
            $data['intelligence_date'],
            $data['intelligence_type'],
            $data['intelligence_details'],
            $data['verification_status'] ?? 'Unverified',
            $data['case_id'] ?? null,
            $data['handler_officer_id']
        ]);
    }

    /**
     * Get by verification status
     */
    public function getByVerificationStatus(string $status): array
    {
        $sql = "SELECT ii.*, i.informant_code, i.alias,
                       o.first_name as handler_first, o.last_name as handler_last
                FROM {$this->table} ii
                JOIN informants i ON ii.informant_id = i.id
                LEFT JOIN officers o ON ii.handler_officer_id = o.id
                WHERE ii.verification_status = ?
                ORDER BY ii.intelligence_date DESC";
        
        return $this->query($sql, [$status]);
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
