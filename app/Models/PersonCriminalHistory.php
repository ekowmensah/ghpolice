<?php

namespace App\Models;

use PDO;

/**
 * PersonCriminalHistory Model
 * 
 * Handles person criminal history records
 */
class PersonCriminalHistory extends BaseModel
{
    protected $table = 'person_criminal_history';

    /**
     * Get history for person
     */
    public function getByPerson(int $personId): array
    {
        $sql = "SELECT pch.*, c.case_number, c.description, c.status
                FROM {$this->table} pch
                LEFT JOIN cases c ON pch.case_id = c.id
                WHERE pch.person_id = ?
                ORDER BY pch.case_date DESC";
        
        return $this->query($sql, [$personId]);
    }

    /**
     * Add history record
     */
    public function addRecord(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (person_id, case_id, involvement_type, offence_category, case_status, case_date, outcome)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['person_id'],
            $data['case_id'],
            $data['involvement_type'],
            $data['offence_category'] ?? null,
            $data['case_status'],
            $data['case_date'],
            $data['outcome'] ?? null
        ]);
    }

    /**
     * Get by involvement type
     */
    public function getByInvolvementType(int $personId, string $involvementType): array
    {
        $sql = "SELECT pch.*, c.case_number, c.description
                FROM {$this->table} pch
                LEFT JOIN cases c ON pch.case_id = c.id
                WHERE pch.person_id = ? AND pch.involvement_type = ?
                ORDER BY pch.case_date DESC";
        
        return $this->query($sql, [$personId, $involvementType]);
    }

    /**
     * Check if person has criminal record
     */
    public function hasCriminalRecord(int $personId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE person_id = ?";
        $result = $this->query($sql, [$personId]);
        return ($result[0]['count'] ?? 0) > 0;
    }
}
