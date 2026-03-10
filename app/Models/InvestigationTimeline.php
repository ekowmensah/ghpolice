<?php

namespace App\Models;

use PDO;

/**
 * InvestigationTimeline Model
 * 
 * Handles case investigation timeline tracking
 */
class InvestigationTimeline extends BaseModel
{
    protected $table = 'case_investigation_timeline';

    /**
     * Get timeline for case
     */
    public function getByCase(int $caseId): array
    {
        $sql = "SELECT cit.*, o.first_name, o.last_name, o.service_number
                FROM {$this->table} cit
                LEFT JOIN officers o ON cit.completed_by = o.id
                WHERE cit.case_id = ?
                ORDER BY cit.activity_date DESC";
        
        return $this->query($sql, [$caseId]);
    }

    /**
     * Add timeline entry
     */
    public function addEntry(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (case_id, activity_type, activity_title, activity_description, 
                 activity_date, completed_by, location, outcome, is_milestone)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['case_id'],
            $data['activity_type'],
            $data['activity_title'],
            $data['activity_description'] ?? null,
            $data['activity_date'],
            $data['completed_by'],
            $data['location'] ?? null,
            $data['outcome'] ?? null,
            $data['is_milestone'] ?? 0
        ]);
    }

    /**
     * Get milestones only
     */
    public function getMilestones(int $caseId): array
    {
        $sql = "SELECT cit.*, o.first_name, o.last_name
                FROM {$this->table} cit
                LEFT JOIN officers o ON cit.completed_by = o.id
                WHERE cit.case_id = ? AND cit.is_milestone = 1
                ORDER BY cit.activity_date DESC";
        
        return $this->query($sql, [$caseId]);
    }

    /**
     * Get by activity type
     */
    public function getByType(int $caseId, string $activityType): array
    {
        $sql = "SELECT cit.*, o.first_name, o.last_name
                FROM {$this->table} cit
                LEFT JOIN officers o ON cit.completed_by = o.id
                WHERE cit.case_id = ? AND cit.activity_type = ?
                ORDER BY cit.activity_date DESC";
        
        return $this->query($sql, [$caseId, $activityType]);
    }
}
