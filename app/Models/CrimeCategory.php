<?php

namespace App\Models;

use PDO;

/**
 * CrimeCategory Model
 * 
 * Handles crime categorization
 */
class CrimeCategory extends BaseModel
{
    protected $table = 'crime_categories';

    /**
     * Get all categories
     */
    public function getAllCategories(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY severity_level DESC, category_name ASC";
        
        return $this->query($sql);
    }

    /**
     * Get by severity
     */
    public function getBySeverity(string $severity): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE severity_level = ?
                ORDER BY category_name ASC";
        
        return $this->query($sql, [$severity]);
    }

    /**
     * Search categories
     */
    public function search(string $term): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE category_name LIKE ? OR description LIKE ?
                ORDER BY category_name ASC";
        
        $searchTerm = "%{$term}%";
        return $this->query($sql, [$searchTerm, $searchTerm]);
    }

    /**
     * Get category statistics
     */
    public function getStatistics(): array
    {
        $sql = "SELECT cc.*, COUNT(csc.id) as case_count
                FROM {$this->table} cc
                LEFT JOIN case_crimes csc ON cc.id = csc.crime_category_id
                GROUP BY cc.id
                ORDER BY case_count DESC";
        
        return $this->query($sql);
    }
}
