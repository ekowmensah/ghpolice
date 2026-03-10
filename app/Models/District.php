<?php

namespace App\Models;

class District extends BaseModel
{
    protected string $table = 'districts';
    
    /**
     * Get all districts with station count
     */
    public function getAllWithStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                d.*,
                division.division_name,
                r.region_name,
                COUNT(s.id) as station_count
            FROM districts d
            LEFT JOIN divisions division ON d.division_id = division.id
            LEFT JOIN regions r ON division.region_id = r.id
            LEFT JOIN stations s ON d.id = s.district_id
            GROUP BY d.id
            ORDER BY r.region_name, division.division_name, d.district_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get districts by division
     */
    public function getByDivision(int $divisionId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                COUNT(s.id) as station_count
            FROM districts d
            LEFT JOIN stations s ON d.id = s.district_id
            WHERE d.division_id = ?
            GROUP BY d.id
            ORDER BY d.district_name
        ");
        $stmt->execute([$divisionId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get districts by region (through divisions)
     */
    public function getByRegion(int $regionId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                division.division_name,
                COUNT(s.id) as station_count
            FROM districts d
            LEFT JOIN divisions division ON d.division_id = division.id
            LEFT JOIN stations s ON d.id = s.district_id
            WHERE division.region_id = ?
            GROUP BY d.id
            ORDER BY division.division_name, d.district_name
        ");
        $stmt->execute([$regionId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get district with division and region info
     */
    public function getWithRegion(int $districtId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                division.division_name,
                r.region_name
            FROM districts d
            LEFT JOIN divisions division ON d.division_id = division.id
            LEFT JOIN regions r ON division.region_id = r.id
            WHERE d.id = ?
        ");
        $stmt->execute([$districtId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
