<?php

namespace App\Models;

class Unit extends BaseModel
{
    protected string $table = 'units';
    
    /**
     * Get all units with station info
     */
    public function getAllWithStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                u.*,
                s.station_name,
                COUNT(DISTINCT o.id) as officer_count
            FROM units u
            LEFT JOIN stations s ON u.station_id = s.id
            LEFT JOIN officers o ON u.id = o.current_unit_id
            GROUP BY u.id
            ORDER BY s.station_name, u.unit_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get units by station
     */
    public function getByStation(int $stationId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.*,
                COUNT(o.id) as officer_count
            FROM units u
            LEFT JOIN officers o ON u.id = o.current_unit_id
            WHERE u.station_id = ?
            GROUP BY u.id
            ORDER BY u.unit_name
        ");
        $stmt->execute([$stationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get unit with station info
     */
    public function getWithStation(int $unitId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.*,
                s.station_name,
                s.station_code
            FROM units u
            LEFT JOIN stations s ON u.station_id = s.id
            WHERE u.id = ?
        ");
        $stmt->execute([$unitId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
