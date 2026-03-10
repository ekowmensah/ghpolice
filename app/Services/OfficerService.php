<?php

namespace App\Services;

use App\Models\Officer;
use App\Models\CaseAssignment;
use App\Config\Database;
use PDO;

class OfficerService
{
    private Officer $officerModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->officerModel = new Officer();
        $this->db = Database::getConnection();
    }
    
    /**
     * Register new officer
     */
    public function registerOfficer(array $data): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create officer record
            $officerId = $this->officerModel->create($data);
            
            // Create initial posting record if station provided
            if (!empty($data['current_station_id'])) {
                $this->createPosting($officerId, $data['current_station_id'], $data['date_joined'] ?? date('Y-m-d'));
            }
            
            $this->db->commit();
            
            logger("Officer registered: {$data['service_number']} (ID: {$officerId})");
            
            return $officerId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Officer registration failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get complete officer details
     */
    public function getOfficerDetails(int $officerId): array
    {
        return [
            'postings' => $this->getPostings($officerId),
            'promotions' => $this->getPromotions($officerId),
            'assignments' => $this->getCaseAssignments($officerId),
            'performance' => $this->getPerformanceMetrics($officerId)
        ];
    }
    
    /**
     * Get officer postings history
     */
    private function getPostings(int $officerId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                op.*,
                s.station_name,
                s.station_code,
                d.district_name,
                r.region_name
            FROM officer_postings op
            JOIN stations s ON op.station_id = s.id
            LEFT JOIN districts d ON s.district_id = d.id
            LEFT JOIN regions r ON s.region_id = r.id
            WHERE op.officer_id = ?
            ORDER BY op.start_date DESC
        ");
        $stmt->execute([$officerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get officer promotions history
     */
    private function getPromotions(int $officerId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                op.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM officer_promotions op
            LEFT JOIN users u ON op.approved_by = u.id
            WHERE op.officer_id = ?
            ORDER BY op.promotion_date DESC
        ");
        $stmt->execute([$officerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get officer case assignments
     */
    private function getCaseAssignments(int $officerId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ca.*,
                c.case_number,
                c.description,
                c.status as case_status,
                c.case_priority
            FROM case_assignments ca
            JOIN cases c ON ca.case_id = c.id
            WHERE ca.assigned_to = ?
            ORDER BY ca.assignment_date DESC
            LIMIT 20
        ");
        $stmt->execute([$officerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get officer performance metrics
     */
    private function getPerformanceMetrics(int $officerId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_cases,
                SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as closed_cases,
                SUM(CASE WHEN c.status = 'Under Investigation' THEN 1 ELSE 0 END) as active_cases
            FROM case_assignments ca
            JOIN cases c ON ca.case_id = c.id
            WHERE ca.assigned_to = ?
        ");
        $stmt->execute([$officerId]);
        return $stmt->fetch() ?: ['total_cases' => 0, 'closed_cases' => 0, 'active_cases' => 0];
    }
    
    /**
     * Transfer officer to new station
     */
    public function transferOfficer(int $officerId, int $newStationId, string $effectiveDate, string $reason, int $authorizedBy): bool
    {
        try {
            $this->db->beginTransaction();
            
            // End current posting
            $stmt = $this->db->prepare("
                UPDATE officer_postings
                SET end_date = ?
                WHERE officer_id = ? AND end_date IS NULL
            ");
            $stmt->execute([$effectiveDate, $officerId]);
            
            // Create new posting
            $this->createPosting($officerId, $newStationId, $effectiveDate, $reason);
            
            // Update officer's current station
            $this->officerModel->update($officerId, ['current_station_id' => $newStationId]);
            
            $this->db->commit();
            
            logger("Officer {$officerId} transferred to station {$newStationId}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Officer transfer failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Create posting record
     */
    private function createPosting(int $officerId, int $stationId, string $startDate, ?string $reason = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO officer_postings (officer_id, station_id, start_date, posting_type, remarks, is_current)
            VALUES (?, ?, ?, 'Initial Posting', ?, 1)
        ");
        $stmt->execute([$officerId, $stationId, $startDate, $reason]);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Promote officer
     */
    public function promoteOfficer(int $officerId, string $newRank, string $effectiveDate, string $notes, int $approvedBy): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Get current rank
            $officer = $this->officerModel->find($officerId);
            $oldRankId = $officer['rank_id'];
            
            // Create promotion record
            $stmt = $this->db->prepare("
                INSERT INTO officer_promotions (officer_id, from_rank_id, to_rank_id, promotion_date, effective_date, remarks, approved_by)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?)
            ");
            $stmt->execute([$officerId, $oldRankId, $newRank, $effectiveDate, $notes, $approvedBy]);
            
            // Update officer's rank
            $this->officerModel->update($officerId, ['rank_id' => $newRank]);
            
            $this->db->commit();
            
            logger("Officer {$officerId} promoted from {$oldRankId} to {$newRank}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Officer promotion failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get all ranks
     */
    public function getAllRanks(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM police_ranks
            ORDER BY rank_level DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get officers by station
     */
    public function getOfficersByStation(int $stationId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                pr.rank_name,
                pr.rank_level
            FROM officers o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.current_station_id = ? AND o.employment_status = 'Active'
            ORDER BY pr.rank_level DESC, o.last_name
        ");
        $stmt->execute([$stationId]);
        return $stmt->fetchAll();
    }
    
    // ==================== PHASE 1 INTEGRATION METHODS ====================
    
    /**
     * Get complete officer profile with all relationships (Phase 1)
     */
    public function getOfficerFullProfile(int $officer_id): ?array
    {
        return $this->officerModel->getFullProfile($officer_id);
        // Returns: officer + assigned_cases + posting_history + promotion_history +
        //          current_posting + patrols + arrests + performance_metrics +
        //          training_records + leave_records
    }
    
    /**
     * Get comprehensive performance metrics (Phase 1)
     */
    public function getOfficerPerformanceMetrics(int $officer_id): array
    {
        return $this->officerModel->getPerformanceMetrics($officer_id);
        // Returns: cases (total/closed/open), arrests, patrols, training,
        //          commendations, disciplinary actions
    }
    
    /**
     * Get officer workload for assignment decisions (Phase 2)
     */
    public function checkOfficerWorkload(int $officer_id): int
    {
        $assignment = new CaseAssignment();
        return $assignment->countActiveByOfficerId($officer_id);
    }
    
    /**
     * Get assigned cases for officer (Phase 1)
     */
    public function getAssignedCases(int $officer_id, string $status = null): array
    {
        return $this->officerModel->getAssignedCases($officer_id, $status);
    }
    
    /**
     * Get posting history (Phase 1)
     */
    public function getPostingHistory(int $officer_id): array
    {
        return $this->officerModel->getPostingHistory($officer_id);
    }
    
    /**
     * Get promotion history (Phase 1)
     */
    public function getPromotionHistory(int $officer_id): array
    {
        return $this->officerModel->getPromotionHistory($officer_id);
    }
    
    /**
     * Get current posting (Phase 1)
     */
    public function getCurrentPosting(int $officer_id): ?array
    {
        return $this->officerModel->getCurrentPosting($officer_id);
    }
    
    /**
     * Get duty roster (Phase 1)
     */
    public function getDutyRoster(int $officer_id, string $start_date = null, string $end_date = null): array
    {
        return $this->officerModel->getDutyRoster($officer_id, $start_date, $end_date);
    }
    
    /**
     * Get patrol logs (Phase 1)
     */
    public function getPatrolLogs(int $officer_id, int $limit = 50): array
    {
        return $this->officerModel->getPatrolLogs($officer_id, $limit);
    }
    
    /**
     * Get arrests made by officer (Phase 1)
     */
    public function getArrestsMade(int $officer_id, int $limit = 50): array
    {
        return $this->officerModel->getArrestsMade($officer_id, $limit);
    }
    
    /**
     * Get training records (Phase 1)
     */
    public function getTrainingRecords(int $officer_id): array
    {
        return $this->officerModel->getTrainingRecords($officer_id);
    }
    
    /**
     * Get leave records (Phase 1)
     */
    public function getLeaveRecords(int $officer_id): array
    {
        return $this->officerModel->getLeaveRecords($officer_id);
    }
    
    /**
     * Find best officer for case assignment based on workload
     */
    public function findBestOfficerForAssignment(int $station_id, int $max_workload = 10): ?array
    {
        $officers = $this->getOfficersByStation($station_id);
        $assignment = new CaseAssignment();
        
        foreach ($officers as $officer) {
            $workload = $assignment->countActiveByOfficerId($officer['id']);
            if ($workload < $max_workload) {
                $officer['current_workload'] = $workload;
                return $officer;
            }
        }
        
        return null;
    }
}
