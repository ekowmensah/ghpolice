<?php

namespace App\Models;

class DutyRoster extends BaseModel
{
    protected string $table = 'duty_roster';
    
    /**
     * Get roster by officer
     */
    public function getByOfficer(int $officerId, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "
            SELECT 
                dr.*,
                s.station_name,
                ds.shift_name,
                ds.start_time,
                ds.end_time
            FROM {$this->table} dr
            JOIN stations s ON dr.station_id = s.id
            JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.officer_id = ?
        ";
        
        $params = [$officerId];
        
        if ($startDate && $endDate) {
            $sql .= " AND dr.duty_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY dr.duty_date DESC, ds.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get roster by station and date
     */
    public function getByStationAndDate(int $stationId, string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                dr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                o.rank,
                o.service_number,
                ds.shift_name,
                ds.start_time,
                ds.end_time
            FROM {$this->table} dr
            JOIN officers o ON dr.officer_id = o.id
            JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.station_id = ? AND dr.duty_date = ?
            ORDER BY ds.start_time, o.rank
        ");
        $stmt->execute([$stationId, $date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if officer has duty on date
     */
    public function hasOfficerDuty(int $officerId, string $date, ?int $shiftId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE officer_id = ? AND duty_date = ?";
        $params = [$officerId, $date];
        
        if ($shiftId) {
            $sql .= " AND shift_id = ?";
            $params[] = $shiftId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Get upcoming duties for officer
     */
    public function getUpcomingDuties(int $officerId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                dr.*,
                s.station_name,
                ds.shift_name,
                ds.start_time,
                ds.end_time
            FROM {$this->table} dr
            JOIN stations s ON dr.station_id = s.id
            JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.officer_id = ? AND dr.duty_date >= CURDATE()
            ORDER BY dr.duty_date, ds.start_time
            LIMIT ?
        ");
        $stmt->execute([$officerId, $limit]);
        return $stmt->fetchAll();
    }
}
