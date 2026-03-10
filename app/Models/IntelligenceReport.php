<?php

namespace App\Models;

use PDO;

/**
 * IntelligenceReport Model
 * 
 * Handles intelligence reports
 */
class IntelligenceReport extends BaseModel
{
    protected $table = 'intelligence_reports';

    /**
     * Get all reports
     */
    public function getAllReports(int $limit = 100): array
    {
        $sql = "SELECT ir.*, o.first_name, o.last_name, o.service_number
                FROM {$this->table} ir
                LEFT JOIN officers o ON ir.created_by = o.id
                ORDER BY ir.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }

    /**
     * Get by classification
     */
    public function getByClassification(string $classification): array
    {
        $sql = "SELECT ir.*, o.first_name, o.last_name
                FROM {$this->table} ir
                LEFT JOIN officers o ON ir.created_by = o.id
                WHERE ir.classification_level = ?
                ORDER BY ir.created_at DESC";
        
        return $this->query($sql, [$classification]);
    }

    /**
     * Get by report type
     */
    public function getByType(string $reportType): array
    {
        $sql = "SELECT ir.*, o.first_name, o.last_name
                FROM {$this->table} ir
                LEFT JOIN officers o ON ir.created_by = o.id
                WHERE ir.report_type = ?
                ORDER BY ir.created_at DESC";
        
        return $this->query($sql, [$reportType]);
    }

    /**
     * Get by status
     */
    public function getByStatus(string $status): array
    {
        $sql = "SELECT ir.*, o.first_name, o.last_name
                FROM {$this->table} ir
                LEFT JOIN officers o ON ir.created_by = o.id
                WHERE ir.status = ?
                ORDER BY ir.created_at DESC";
        
        return $this->query($sql, [$status]);
    }

    /**
     * Search reports
     */
    public function search(string $term): array
    {
        $sql = "SELECT ir.*, o.first_name, o.last_name
                FROM {$this->table} ir
                LEFT JOIN officers o ON ir.created_by = o.id
                WHERE ir.report_title LIKE ? OR ir.intelligence_summary LIKE ?
                ORDER BY ir.created_at DESC";
        
        $searchTerm = "%{$term}%";
        return $this->query($sql, [$searchTerm, $searchTerm]);
    }
}
