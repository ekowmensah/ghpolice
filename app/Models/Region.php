<?php

namespace App\Models;

class Region extends BaseModel
{
    protected string $table = 'regions';
    
    /**
     * Get all regions with station count
     */
    public function getAllWithStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                r.*,
                COUNT(DISTINCT division.id) as division_count,
                COUNT(DISTINCT dist.id) as district_count,
                COUNT(DISTINCT s.id) as station_count
            FROM regions r
            LEFT JOIN divisions division ON r.id = division.region_id
            LEFT JOIN districts dist ON division.id = dist.division_id
            LEFT JOIN stations s ON dist.id = s.district_id
            GROUP BY r.id
            ORDER BY r.region_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get region with divisions
     */
    public function getWithDivisions(int $regionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM regions WHERE id = ?
        ");
        $stmt->execute([$regionId]);
        $region = $stmt->fetch();
        
        if (!$region) {
            return null;
        }
        
        // Get divisions
        $stmt = $this->db->prepare("
            SELECT * FROM divisions WHERE region_id = ? ORDER BY division_name
        ");
        $stmt->execute([$regionId]);
        $region['divisions'] = $stmt->fetchAll();
        
        return $region;
    }
}
