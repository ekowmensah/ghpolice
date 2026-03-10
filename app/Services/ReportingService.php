<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Officer;
use App\Models\Person;
use App\Models\Evidence;
use App\Models\Arrest;
use App\Models\PatrolLog;
use App\Models\IntelligenceReport;
use App\Config\Database;
use PDO;

class ReportingService
{
    private CaseModel $caseModel;
    private Officer $officerModel;
    private Person $personModel;
    private Evidence $evidenceModel;
    private Arrest $arrestModel;
    private PatrolLog $patrolModel;
    private IntelligenceReport $intelligenceModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->officerModel = new Officer();
        $this->personModel = new Person();
        $this->evidenceModel = new Evidence();
        $this->arrestModel = new Arrest();
        $this->patrolModel = new PatrolLog();
        $this->intelligenceModel = new IntelligenceReport();
        $this->db = Database::getConnection();
    }
    
    // ==================== CASE STATISTICS ====================
    
    /**
     * Get comprehensive case statistics
     */
    public function getCaseStatistics(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE created_at BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_cases,
                SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_cases,
                SUM(CASE WHEN status = 'Under Investigation' THEN 1 ELSE 0 END) as under_investigation,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_cases,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_cases,
                SUM(CASE WHEN case_priority = 'High' THEN 1 ELSE 0 END) as high_priority,
                SUM(CASE WHEN case_priority = 'Urgent' THEN 1 ELSE 0 END) as urgent_cases,
                SUM(CASE WHEN case_type = 'Police Initiated' THEN 1 ELSE 0 END) as police_initiated,
                AVG(DATEDIFF(COALESCE(closed_at, NOW()), created_at)) as avg_resolution_days
            FROM cases
            {$dateFilter}
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get case statistics by crime category
     */
    public function getCasesByCrimeCategory(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'AND c.created_at BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                cc.category_name,
                COUNT(DISTINCT c.id) as case_count,
                SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as closed_count,
                ROUND(SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT c.id), 2) as closure_rate
            FROM crime_categories cc
            LEFT JOIN case_crimes ccr ON cc.id = ccr.crime_category_id
            LEFT JOIN cases c ON ccr.case_id = c.id
            WHERE c.id IS NOT NULL {$dateFilter}
            GROUP BY cc.id, cc.category_name
            ORDER BY case_count DESC
            LIMIT 20
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case statistics by station
     */
    public function getCasesByStation(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE c.created_at BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                s.station_name,
                d.district_name,
                r.region_name,
                COUNT(c.id) as total_cases,
                SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as closed_cases,
                SUM(CASE WHEN c.status = 'Under Investigation' THEN 1 ELSE 0 END) as active_cases,
                ROUND(SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) * 100.0 / COUNT(c.id), 2) as closure_rate
            FROM stations s
            LEFT JOIN cases c ON s.id = c.station_id
            LEFT JOIN districts d ON s.district_id = d.id
            LEFT JOIN regions r ON s.region_id = r.id
            {$dateFilter}
            GROUP BY s.id, s.station_name, d.district_name, r.region_name
            HAVING total_cases > 0
            ORDER BY total_cases DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // ==================== OFFICER PERFORMANCE ====================
    
    /**
     * Get officer performance report
     */
    public function getOfficerPerformanceReport(int $officer_id = null, string $start_date = null, string $end_date = null): array
    {
        $officerFilter = $officer_id ? 'AND ca.assigned_to = ?' : '';
        $dateFilter = '';
        $params = [];
        
        if ($officer_id) {
            $params[] = $officer_id;
        }
        
        if ($start_date && $end_date) {
            $dateFilter = 'AND ca.assignment_date BETWEEN ? AND ?';
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                o.id as officer_id,
                o.service_number,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                s.station_name,
                COUNT(DISTINCT ca.case_id) as total_cases_assigned,
                SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as cases_closed,
                SUM(CASE WHEN c.status = 'Under Investigation' THEN 1 ELSE 0 END) as cases_active,
                COUNT(DISTINCT a.id) as arrests_made,
                COUNT(DISTINCT pl.id) as patrols_conducted,
                ROUND(SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) * 100.0 / 
                      NULLIF(COUNT(DISTINCT ca.case_id), 0), 2) as case_closure_rate
            FROM officers o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            LEFT JOIN case_assignments ca ON o.id = ca.assigned_to
            LEFT JOIN cases c ON ca.case_id = c.id
            LEFT JOIN arrests a ON o.id = a.arresting_officer_id
            LEFT JOIN patrol_logs pl ON o.id = pl.patrol_leader_id
            WHERE o.employment_status = 'Active' {$officerFilter} {$dateFilter}
            GROUP BY o.id, o.service_number, officer_name, pr.rank_name, s.station_name
            HAVING total_cases_assigned > 0
            ORDER BY case_closure_rate DESC, cases_closed DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get top performing officers
     */
    public function getTopPerformingOfficers(int $limit = 10, string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'AND ca.assignment_date BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $params[] = $limit;
        
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                o.service_number,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                s.station_name,
                COUNT(DISTINCT ca.case_id) as cases_handled,
                SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) as cases_solved,
                ROUND(SUM(CASE WHEN c.status = 'Closed' THEN 1 ELSE 0 END) * 100.0 / 
                      COUNT(DISTINCT ca.case_id), 2) as success_rate
            FROM officers o
            INNER JOIN police_ranks pr ON o.rank_id = pr.id
            INNER JOIN stations s ON o.current_station_id = s.id
            INNER JOIN case_assignments ca ON o.id = ca.assigned_to
            INNER JOIN cases c ON ca.case_id = c.id
            WHERE o.employment_status = 'Active' {$dateFilter}
            GROUP BY o.id, o.service_number, officer_name, pr.rank_name, s.station_name
            HAVING cases_handled >= 5
            ORDER BY success_rate DESC, cases_solved DESC
            LIMIT ?
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // ==================== ARREST STATISTICS ====================
    
    /**
     * Get arrest statistics
     */
    public function getArrestStatistics(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE arrest_date BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_arrests,
                COUNT(DISTINCT arresting_officer_id) as officers_involved,
                COUNT(DISTINCT case_id) as cases_with_arrests,
                SUM(CASE WHEN arrest_type = 'Warrant' THEN 1 ELSE 0 END) as warrant_arrests,
                SUM(CASE WHEN arrest_type = 'Without Warrant' THEN 1 ELSE 0 END) as warrantless_arrests,
                AVG(TIMESTAMPDIFF(HOUR, arrest_date, created_at)) as avg_processing_hours
            FROM arrests
            {$dateFilter}
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }
    
    // ==================== EVIDENCE & EXHIBIT STATISTICS ====================
    
    /**
     * Get evidence statistics
     */
    public function getEvidenceStatistics(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE collected_date BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_evidence_items,
                COUNT(DISTINCT case_id) as cases_with_evidence,
                SUM(CASE WHEN status = 'Collected' THEN 1 ELSE 0 END) as collected,
                SUM(CASE WHEN status = 'In Storage' THEN 1 ELSE 0 END) as in_storage,
                SUM(CASE WHEN status = 'In Lab' THEN 1 ELSE 0 END) as in_lab,
                SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned,
                COUNT(DISTINCT collected_by) as officers_collecting
            FROM evidence
            {$dateFilter}
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }
    
    // ==================== PATROL & OPERATIONS ====================
    
    /**
     * Get patrol statistics
     */
    public function getPatrolStatistics(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE start_time BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_patrols,
                COUNT(DISTINCT patrol_leader_id) as officers_on_patrol,
                COUNT(DISTINCT station_id) as stations_involved,
                SUM(CASE WHEN patrol_status = 'Completed' THEN 1 ELSE 0 END) as completed_patrols,
                SUM(CASE WHEN patrol_status = 'In Progress' THEN 1 ELSE 0 END) as active_patrols,
                SUM(CASE WHEN incidents_reported > 0 THEN 1 ELSE 0 END) as patrols_with_incidents,
                SUM(incidents_reported) as total_incidents_reported,
                AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_patrol_duration_hours
            FROM patrol_logs
            {$dateFilter}
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }
    
    // ==================== INTELLIGENCE STATISTICS ====================
    
    /**
     * Get intelligence statistics
     */
    public function getIntelligenceStatistics(string $start_date = null, string $end_date = null): array
    {
        $dateFilter = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $dateFilter = 'WHERE report_date BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_reports,
                SUM(CASE WHEN classification = 'Confidential' THEN 1 ELSE 0 END) as confidential,
                SUM(CASE WHEN classification = 'Secret' THEN 1 ELSE 0 END) as secret,
                SUM(CASE WHEN classification = 'Top Secret' THEN 1 ELSE 0 END) as top_secret,
                SUM(CASE WHEN reliability_rating = 'High' THEN 1 ELSE 0 END) as high_reliability,
                COUNT(DISTINCT source_type) as source_types,
                COUNT(DISTINCT created_by) as analysts_involved
            FROM intelligence_reports
            {$dateFilter}
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }
    
    // ==================== COMPREHENSIVE DASHBOARD ====================
    
    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStatistics(string $period = 'month'): array
    {
        // Calculate date range based on period
        $end_date = date('Y-m-d 23:59:59');
        switch ($period) {
            case 'today':
                $start_date = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'year':
                $start_date = date('Y-m-d 00:00:00', strtotime('-365 days'));
                break;
            default:
                $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
        }
        
        return [
            'period' => $period,
            'date_range' => ['start' => $start_date, 'end' => $end_date],
            'cases' => $this->getCaseStatistics($start_date, $end_date),
            'arrests' => $this->getArrestStatistics($start_date, $end_date),
            'evidence' => $this->getEvidenceStatistics($start_date, $end_date),
            'patrols' => $this->getPatrolStatistics($start_date, $end_date),
            'intelligence' => $this->getIntelligenceStatistics($start_date, $end_date),
            'top_officers' => $this->getTopPerformingOfficers(5, $start_date, $end_date),
            'cases_by_category' => $this->getCasesByCrimeCategory($start_date, $end_date)
        ];
    }
    
    /**
     * Generate custom report
     */
    public function generateCustomReport(array $metrics, string $start_date, string $end_date, array $filters = []): array
    {
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => ['start' => $start_date, 'end' => $end_date],
            'filters' => $filters,
            'data' => []
        ];
        
        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'cases':
                    $report['data']['cases'] = $this->getCaseStatistics($start_date, $end_date);
                    break;
                case 'officers':
                    $report['data']['officers'] = $this->getOfficerPerformanceReport(null, $start_date, $end_date);
                    break;
                case 'arrests':
                    $report['data']['arrests'] = $this->getArrestStatistics($start_date, $end_date);
                    break;
                case 'evidence':
                    $report['data']['evidence'] = $this->getEvidenceStatistics($start_date, $end_date);
                    break;
                case 'patrols':
                    $report['data']['patrols'] = $this->getPatrolStatistics($start_date, $end_date);
                    break;
                case 'intelligence':
                    $report['data']['intelligence'] = $this->getIntelligenceStatistics($start_date, $end_date);
                    break;
            }
        }
        
        return $report;
    }
}
