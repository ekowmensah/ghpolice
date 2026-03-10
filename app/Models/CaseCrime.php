<?php

namespace App\Models;

use PDO;

/**
 * CaseCrime Model
 * 
 * Handles case-crime associations
 */
class CaseCrime extends BaseModel
{
    protected string $table = 'case_crimes';

    /**
     * Get crimes for a case
     */
    public function getByCase(int $caseId): array
    {
        $sql = "SELECT cc.*, c.category_name, c.severity_level
                FROM {$this->table} cc
                JOIN crime_categories c ON cc.crime_category_id = c.id
                WHERE cc.case_id = ?
                ORDER BY c.severity_level DESC";
        
        return $this->query($sql, [$caseId]);
    }

    /**
     * Add crime to case
     */
    public function addToCase(int $caseId, int $crimeCategoryId, ?string $description = null): int
    {
        $sql = "INSERT INTO {$this->table} (case_id, crime_category_id, crime_description)
                VALUES (?, ?, ?)";
        
        return $this->execute($sql, [$caseId, $crimeCategoryId, $description]);
    }

    /**
     * Remove crime from case
     */
    public function removeFromCase(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Get cases by crime category
     */
    public function getByCrimeCategory(int $categoryId): array
    {
        $sql = "SELECT cc.*, c.case_number, c.description, c.status
                FROM {$this->table} cc
                JOIN cases c ON cc.case_id = c.id
                WHERE cc.crime_category_id = ?
                ORDER BY c.created_at DESC";
        
        return $this->query($sql, [$categoryId]);
    }
}
