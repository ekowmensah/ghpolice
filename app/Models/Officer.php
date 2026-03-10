<?php

namespace App\Models;

class Officer extends BaseModel
{
    protected string $table = 'officers';
    
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        return (int)$stmt->fetch()['total'];
    }
    
    /**
     * Paginate officers with rank and station information
     */
    public function paginate(int $page = 1, int $perPage = 25, array $columns = ['*']): array
    {
        $offset = ($page - 1) * $perPage;
        
        $countStmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $countStmt->fetch()['total'];
        
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                pr.rank_name,
                pr.rank_level,
                pr.rank_category,
                s.station_name,
                s.station_code
            FROM {$this->table} o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            ORDER BY o.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Find officer with rank information
     */
    public function find(int $id, array $columns = ['*']): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                pr.rank_name,
                pr.rank_level,
                pr.rank_category,
                s.station_name,
                s.station_code
            FROM {$this->table} o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    // ==================== RELATIONSHIP METHODS ====================
    
    /**
     * Get cases assigned to this officer
     */
    public function getAssignedCases(int $officer_id, string $status = null): array
    {
        $sql = "
            SELECT c.*, ca.assignment_date, ca.status as assignment_status, ca.role,
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as complainant_name,
                   s.station_name
            FROM case_assignments ca
            INNER JOIN cases c ON ca.case_id = c.id
            LEFT JOIN complainants comp ON c.complainant_id = comp.id
            LEFT JOIN persons p ON comp.person_id = p.id
            LEFT JOIN stations s ON c.station_id = s.id
            WHERE ca.assigned_to = ?
        ";
        
        $params = [$officer_id];
        
        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ca.assignment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get posting history for this officer
     */
    public function getPostingHistory(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT op.*, s.station_name, s.station_code, d.district_name, divisions.division_name, r.region_name
            FROM officer_postings op
            LEFT JOIN stations s ON op.station_id = s.id
            LEFT JOIN districts d ON op.district_id = d.id
            LEFT JOIN divisions divisions ON op.division_id = divisions.id
            LEFT JOIN regions r ON op.region_id = r.id
            WHERE op.officer_id = ?
            ORDER BY op.start_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get promotion history for this officer
     */
    public function getPromotionHistory(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT op.*, 
                   pr_from.rank_name as from_rank_name,
                   pr_to.rank_name as to_rank_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as approved_by_name
            FROM officer_promotions op
            LEFT JOIN police_ranks pr_from ON op.from_rank_id = pr_from.id
            LEFT JOIN police_ranks pr_to ON op.to_rank_id = pr_to.id
            LEFT JOIN users u ON op.approved_by = u.id
            WHERE op.officer_id = ?
            ORDER BY op.promotion_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get current posting for this officer
     */
    public function getCurrentPosting(int $officer_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT op.*, s.station_name, s.station_code, d.district_name, divisions.division_name, r.region_name
            FROM officer_postings op
            LEFT JOIN stations s ON op.station_id = s.id
            LEFT JOIN districts d ON op.district_id = d.id
            LEFT JOIN divisions divisions ON op.division_id = divisions.id
            LEFT JOIN regions r ON op.region_id = r.id
            WHERE op.officer_id = ? AND op.is_current = 1
            LIMIT 1
        ");
        $stmt->execute([$officer_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get duty roster for this officer
     */
    public function getDutyRoster(int $officer_id, string $start_date = null, string $end_date = null): array
    {
        $sql = "
            SELECT dr.*, 
                   ds.shift_name, ds.start_time, ds.end_time,
                   s.station_name
            FROM duty_roster dr
            LEFT JOIN duty_shifts ds ON dr.shift_id = ds.id
            LEFT JOIN stations s ON dr.station_id = s.id
            WHERE dr.officer_id = ?
        ";
        
        $params = [$officer_id];
        
        if ($start_date && $end_date) {
            $sql .= " AND dr.duty_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        $sql .= " ORDER BY dr.duty_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get patrol logs where officer was involved
     */
    public function getPatrolLogs(int $officer_id, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT pl.*, 
                   s.station_name,
                   v.registration_number as vehicle_registration,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as patrol_leader_name
            FROM patrol_logs pl
            LEFT JOIN patrol_officers po ON pl.id = po.patrol_id
            LEFT JOIN stations s ON pl.station_id = s.id
            LEFT JOIN vehicles v ON pl.vehicle_id = v.id
            LEFT JOIN officers o ON pl.patrol_leader_id = o.id
            WHERE pl.patrol_leader_id = ? OR po.officer_id = ?
            ORDER BY pl.start_time DESC
            LIMIT ?
        ");
        $stmt->execute([$officer_id, $officer_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get arrests made by this officer
     */
    public function getArrestsMade(int $officer_id, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   c.case_number, c.description as case_description,
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                   s.current_status as suspect_status
            FROM arrests a
            INNER JOIN cases c ON a.case_id = c.id
            LEFT JOIN suspects s ON a.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE a.arresting_officer_id = ?
            ORDER BY a.arrest_date DESC
            LIMIT ?
        ");
        $stmt->execute([$officer_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get performance metrics for this officer
     */
    public function getPerformanceMetrics(int $officer_id): array
    {
        $metrics = [];
        
        // Cases assigned
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as closed,
                   SUM(CASE WHEN c.status = 'Open' THEN 1 ELSE 0 END) as open
            FROM case_assignments ca
            INNER JOIN cases c ON ca.case_id = c.id
            WHERE ca.assigned_to = ? AND ca.status = 'Active'
        ");
        $stmt->execute([$officer_id]);
        $metrics['cases'] = $stmt->fetch();
        
        // Arrests made
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   COUNT(DISTINCT YEAR(arrest_date)) as years_active
            FROM arrests
            WHERE arresting_officer_id = ?
        ");
        $stmt->execute([$officer_id]);
        $metrics['arrests'] = $stmt->fetch();
        
        // Patrol logs
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT pl.id) as total_patrols,
                   COUNT(DISTINCT pi.id) as incidents_responded
            FROM patrol_logs pl
            LEFT JOIN patrol_officers po ON pl.id = po.patrol_id
            LEFT JOIN patrol_incidents pi ON pl.id = pi.patrol_id
            WHERE pl.patrol_leader_id = ? OR po.officer_id = ?
        ");
        $stmt->execute([$officer_id, $officer_id]);
        $metrics['patrols'] = $stmt->fetch();
        
        // Training completed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM officer_training
            WHERE officer_id = ?
        ");
        $stmt->execute([$officer_id]);
        $metrics['training'] = $stmt->fetch();
        
        // Commendations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM officer_commendations
            WHERE officer_id = ?
        ");
        $stmt->execute([$officer_id]);
        $metrics['commendations'] = $stmt->fetch();
        
        // Disciplinary actions
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM officer_disciplinary_records
            WHERE officer_id = ?
        ");
        $stmt->execute([$officer_id]);
        $metrics['disciplinary'] = $stmt->fetch();
        
        return $metrics;
    }
    
    /**
     * Get training records for this officer
     */
    public function getTrainingRecords(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM officer_training
            WHERE officer_id = ?
            ORDER BY start_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get leave records for this officer
     */
    public function getLeaveRecords(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT ol.*,
                   CONCAT_WS(' ', u.first_name, u.last_name) as approved_by_name
            FROM officer_leave_records ol
            LEFT JOIN users u ON ol.approved_by = u.id
            WHERE ol.officer_id = ?
            ORDER BY ol.start_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get full officer profile with all relationships
     */
    public function getFullProfile(int $officer_id): ?array
    {
        $officer = $this->find($officer_id);
        if (!$officer) {
            return null;
        }
        
        // Add all relationships
        $officer['assigned_cases'] = $this->getAssignedCases($officer_id);
        $officer['posting_history'] = $this->getPostingHistory($officer_id);
        $officer['promotion_history'] = $this->getPromotionHistory($officer_id);
        $officer['current_posting'] = $this->getCurrentPosting($officer_id);
        $officer['recent_patrols'] = $this->getPatrolLogs($officer_id, 10);
        $officer['recent_arrests'] = $this->getArrestsMade($officer_id, 10);
        $officer['performance_metrics'] = $this->getPerformanceMetrics($officer_id);
        $officer['training_records'] = $this->getTrainingRecords($officer_id);
        $officer['leave_records'] = $this->getLeaveRecords($officer_id);
        $officer['commendation_records'] = $this->getCommendationRecords($officer_id);
        $officer['disciplinary_records'] = $this->getDisciplinaryRecords($officer_id);
        
        return $officer;
    }
    
    /**
     * Get commendation records for this officer
     */
    public function getCommendationRecords(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT oc.*, 
                   oc.awarded_by as awarded_by_name,
                   oc.awarded_by as approved_by_name
            FROM officer_commendations oc
            WHERE oc.officer_id = ?
            ORDER BY oc.award_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get disciplinary records for this officer
     */
    public function getDisciplinaryRecords(int $officer_id): array
    {
        $stmt = $this->db->prepare("
            SELECT odr.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as recorded_by_name
            FROM officer_disciplinary_records odr
            LEFT JOIN users u ON odr.recorded_by = u.id
            WHERE odr.officer_id = ?
            ORDER BY odr.incident_date DESC
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Search officers by name or service number
     */
    public function searchOfficers(string $query): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as name,
                o.service_number,
                pr.rank_name as rank
            FROM officers o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            AND (
                o.service_number LIKE ? 
                OR CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) LIKE ?
                OR o.first_name LIKE ?
                OR o.last_name LIKE ?
            )
            ORDER BY o.first_name
            LIMIT 10
        ");
        
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
