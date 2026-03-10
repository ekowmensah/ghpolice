<?php

namespace App\Models;

class CaseReferral extends BaseModel
{
    protected string $table = 'case_referrals';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cr.*,
                s1.station_name as from_station_name,
                s2.station_name as to_station_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as referred_by_name
            FROM case_referrals cr
            LEFT JOIN stations s1 ON cr.from_station_id = s1.id
            LEFT JOIN stations s2 ON cr.to_station_id = s2.id
            LEFT JOIN users u ON cr.referred_by = u.id
            WHERE cr.case_id = ?
            ORDER BY cr.referral_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getPending(?int $stationId = null): array
    {
        $sql = "
            SELECT 
                cr.*,
                c.case_number,
                c.description,
                s1.station_name as from_station_name,
                s2.station_name as to_station_name
            FROM case_referrals cr
            JOIN cases c ON cr.case_id = c.id
            LEFT JOIN stations s1 ON cr.from_station_id = s1.id
            LEFT JOIN stations s2 ON cr.to_station_id = s2.id
            WHERE cr.status = 'Pending'
        ";
        
        $params = [];
        
        if ($stationId) {
            $sql .= " AND cr.to_station_id = ?";
            $params[] = $stationId;
        }
        
        $sql .= " ORDER BY cr.referral_date DESC";
        
        return $this->query($sql, $params);
    }
    
    public function acceptReferral(int $id, int $acceptedBy): bool
    {
        return $this->update($id, [
            'status' => 'Accepted',
            'accepted_by' => $acceptedBy,
            'accepted_date' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function rejectReferral(int $id, int $rejectedBy, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'Rejected',
            'rejected_by' => $rejectedBy,
            'rejection_reason' => $reason,
            'rejected_date' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Create a new case referral
     */
    public function createReferral(int $caseId, string $toLevel, int $toStationId, string $remarks, int $referredBy): bool
    {
        $db = \App\Config\Database::getConnection();
        
        // Get current case station
        $stmt = $db->prepare("SELECT station_id FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        
        if (!$case) {
            return false;
        }
        
        $stmt = $db->prepare("
            INSERT INTO case_referrals (
                case_id, 
                from_station_id, 
                to_station_id, 
                to_level, 
                remarks, 
                referred_by, 
                referral_date, 
                status
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Pending')
        ");
        
        return $stmt->execute([
            $caseId,
            $case['station_id'],
            $toStationId,
            $toLevel,
            $remarks,
            $referredBy
        ]);
    }
}
