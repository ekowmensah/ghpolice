<?php

namespace App\Models;

class Division extends BaseModel
{
    protected string $table = 'divisions';
    
    /**
     * Get all divisions with region info
     */
    public function getAllWithStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                division.*,
                r.region_name,
                COUNT(DISTINCT d.id) as district_count
            FROM divisions division
            LEFT JOIN regions r ON division.region_id = r.id
            LEFT JOIN districts d ON division.id = d.division_id
            GROUP BY division.id
            ORDER BY r.region_name, division.division_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get divisions by region
     */
    public function getByRegion(int $regionId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM divisions WHERE region_id = ? ORDER BY division_name
        ");
        $stmt->execute([$regionId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get division with region info
     */
    public function getWithRegion(int $divisionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                division.*,
                r.region_name
            FROM divisions division
            LEFT JOIN regions r ON division.region_id = r.id
            WHERE division.id = ?
        ");
        $stmt->execute([$divisionId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
