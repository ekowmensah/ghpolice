<?php

namespace App\Models;

class Station extends BaseModel
{
    protected string $table = 'stations';
    
    /**
     * Get station with hierarchical information
     */
    public function getWithHierarchy(int $stationId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                d.district_name,
                dv.division_name,
                r.region_name
            FROM stations s
            LEFT JOIN districts d ON s.district_id = d.id
            LEFT JOIN divisions dv ON s.division_id = dv.id
            LEFT JOIN regions r ON s.region_id = r.id
            WHERE s.id = ?
        ");
        $stmt->execute([$stationId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get all stations by district
     */
    public function getByDistrict(int $districtId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM stations
            WHERE district_id = ?
            ORDER BY station_name
        ");
        $stmt->execute([$districtId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all stations by region
     */
    public function getByRegion(int $regionId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, d.district_name, dv.division_name
            FROM stations s
            LEFT JOIN districts d ON s.district_id = d.id
            LEFT JOIN divisions dv ON s.division_id = dv.id
            WHERE s.region_id = ?
            ORDER BY s.station_name
        ");
        $stmt->execute([$regionId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case count for station
     */
    public function getCaseCount(int $stationId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM cases
            WHERE station_id = ?
        ");
        $stmt->execute([$stationId]);
        return (int)$stmt->fetch()['total'];
    }
}
