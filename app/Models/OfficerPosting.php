<?php

namespace App\Models;

class OfficerPosting extends BaseModel
{
    protected string $table = 'officer_postings';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT 
                op.*,
                s.station_name,
                d.district_name,
                dv.division_name,
                r.region_name
            FROM officer_postings op
            LEFT JOIN stations s ON op.station_id = s.id
            LEFT JOIN districts d ON op.district_id = d.id
            LEFT JOIN divisions dv ON op.division_id = dv.id
            LEFT JOIN regions r ON op.region_id = r.id
            WHERE op.officer_id = ?
            ORDER BY op.start_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function getCurrentPosting(int $officerId): ?array
    {
        $sql = "
            SELECT 
                op.*,
                s.station_name,
                d.district_name,
                dv.division_name,
                r.region_name
            FROM officer_postings op
            LEFT JOIN stations s ON op.station_id = s.id
            LEFT JOIN districts d ON op.district_id = d.id
            LEFT JOIN divisions dv ON op.division_id = dv.id
            LEFT JOIN regions r ON op.region_id = r.id
            WHERE op.officer_id = ? AND op.is_current = 1
            ORDER BY op.start_date DESC
            LIMIT 1
        ";
        
        $result = $this->query($sql, [$officerId]);
        return $result[0] ?? null;
    }
    
    public function transferOfficer(int $officerId, array $postingData): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->execute(
                "UPDATE officer_postings SET is_current = 0, end_date = ? WHERE officer_id = ? AND is_current = 1",
                [date('Y-m-d'), $officerId]
            );
            
            // Get station details to populate district, division, and region
            $stationId = $postingData['station_id'] ?? null;
            $districtId = $postingData['district_id'] ?? null;
            $divisionId = $postingData['division_id'] ?? null;
            $regionId = $postingData['region_id'] ?? null;
            
            if ($stationId) {
                $station = $this->query(
                    "SELECT district_id, division_id, region_id FROM stations WHERE id = ?",
                    [$stationId]
                );
                
                if (!empty($station)) {
                    $districtId = $station[0]['district_id'];
                    $divisionId = $station[0]['division_id'];
                    $regionId = $station[0]['region_id'];
                }
            }
            
            $this->create([
                'officer_id' => $officerId,
                'station_id' => $stationId,
                'district_id' => $districtId,
                'division_id' => $divisionId,
                'region_id' => $regionId,
                'posting_type' => $postingData['posting_type'],
                'position_title' => $postingData['position_title'] ?? null,
                'start_date' => $postingData['start_date'],
                'posting_order_number' => $postingData['posting_order_number'] ?? null,
                'is_current' => 1,
                'remarks' => $postingData['remarks'] ?? null,
                'posted_by' => $postingData['posted_by']
            ]);
            
            $this->execute(
                "UPDATE officers SET current_station_id = ?, current_district_id = ?, current_division_id = ?, current_region_id = ? WHERE id = ?",
                [
                    $stationId,
                    $districtId,
                    $divisionId,
                    $regionId,
                    $officerId
                ]
            );
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Create initial posting for an officer
     */
    public function createInitialPosting(int $officerId, array $postingData): bool
    {
        // Get station details to populate district, division, and region
        $stationId = $postingData['station_id'] ?? null;
        $districtId = $postingData['district_id'] ?? null;
        $divisionId = $postingData['division_id'] ?? null;
        $regionId = $postingData['region_id'] ?? null;
        
        if ($stationId) {
            $station = $this->query(
                "SELECT district_id, division_id, region_id FROM stations WHERE id = ?",
                [$stationId]
            );
            
            if (!empty($station)) {
                $districtId = $station[0]['district_id'];
                $divisionId = $station[0]['division_id'];
                $regionId = $station[0]['region_id'];
            }
        }
        
        $result = $this->create([
            'officer_id' => $officerId,
            'station_id' => $stationId,
            'district_id' => $districtId,
            'division_id' => $divisionId,
            'region_id' => $regionId,
            'posting_type' => $postingData['posting_type'] ?? 'Initial Posting',
            'position_title' => $postingData['position_title'] ?? null,
            'start_date' => $postingData['start_date'] ?? date('Y-m-d'),
            'posting_order_number' => $postingData['posting_order_number'] ?? null,
            'is_current' => 1,
            'remarks' => $postingData['remarks'] ?? null,
            'posted_by' => $postingData['posted_by'] ?? null
        ]);
        
        if ($result) {
            // Update officer's current location
            $this->execute(
                "UPDATE officers SET current_station_id = ?, current_district_id = ?, current_division_id = ?, current_region_id = ? WHERE id = ?",
                [
                    $stationId,
                    $districtId,
                    $divisionId,
                    $regionId,
                    $officerId
                ]
            );
        }
        
        return $result;
    }
}
