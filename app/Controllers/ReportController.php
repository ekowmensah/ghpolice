<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\Person;
use App\Models\Officer;
use App\Models\Station;
use App\Config\Database;
use PDO;

class ReportController extends BaseController
{
    private PDO $db;
    private CaseModel $caseModel;
    private Person $personModel;
    private Officer $officerModel;
    private Station $stationModel;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->caseModel = new CaseModel();
        $this->personModel = new Person();
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
    }
    
    /**
     * Reports dashboard
     */
    public function index(): string
    {
        $stats = $this->getSystemStats();
        
        return $this->view('reports/index', [
            'title' => 'Reports & Analytics',
            'stats' => $stats
        ]);
    }
    
    /**
     * Case reports
     */
    public function cases(): string
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'station_id' => $_GET['station_id'] ?? null
        ];
        
        $caseStats = $this->getCaseStatistics($filters);
        $stations = $this->stationModel->all();
        
        return $this->view('reports/cases', [
            'title' => 'Case Reports',
            'stats' => $caseStats,
            'filters' => $filters,
            'stations' => $stations
        ]);
    }
    
    /**
     * Statistics dashboard
     */
    public function statistics(): string
    {
        $period = $_GET['period'] ?? 'month';
        
        $stats = [
            'cases' => $this->getCaseStats($period),
            'persons' => $this->getPersonStats($period),
            'officers' => $this->getOfficerStats(),
            'crime_trends' => $this->getCrimeTrends($period)
        ];
        
        return $this->view('reports/statistics', [
            'title' => 'Statistics Dashboard',
            'stats' => $stats,
            'period' => $period
        ]);
    }
    
    /**
     * Get system-wide statistics
     */
    private function getSystemStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM cases) as total_cases,
                (SELECT COUNT(*) FROM cases WHERE status = 'Open') as open_cases,
                (SELECT COUNT(*) FROM cases WHERE status = 'Under Investigation') as investigating_cases,
                (SELECT COUNT(*) FROM cases WHERE status = 'Closed') as closed_cases,
                (SELECT COUNT(*) FROM persons) as total_persons,
                (SELECT COUNT(*) FROM persons WHERE has_criminal_record = TRUE) as persons_with_records,
                (SELECT COUNT(*) FROM officers WHERE employment_status = 'Active') as active_officers,
                (SELECT COUNT(*) FROM evidence) as total_evidence
        ");
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get case statistics with filters
     */
    private function getCaseStatistics(array $filters): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as `open`,
                SUM(CASE WHEN status = 'Under Investigation' THEN 1 ELSE 0 END) as investigating,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as `closed`,
                SUM(CASE WHEN case_priority = 'High' THEN 1 ELSE 0 END) as `high_priority`,
                SUM(CASE WHEN case_priority = 'Medium' THEN 1 ELSE 0 END) as `medium_priority`,
                SUM(CASE WHEN case_priority = 'Low' THEN 1 ELSE 0 END) as `low_priority`
            FROM cases
            WHERE created_at BETWEEN ? AND ?
        ";
        
        $params = [$filters['start_date'], $filters['end_date'] . ' 23:59:59'];
        
        if ($filters['status']) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if ($filters['station_id']) {
            $sql .= " AND station_id = ?";
            $params[] = $filters['station_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get case statistics by period
     */
    private function getCaseStats(string $period): array
    {
        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };
        
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(created_at, ?) as period,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed
            FROM cases
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY period
            ORDER BY period DESC
            LIMIT 12
        ");
        
        $stmt->execute([$dateFormat]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get person statistics
     */
    private function getPersonStats(string $period): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_registered,
                SUM(CASE WHEN has_criminal_record = TRUE THEN 1 ELSE 0 END) as with_records,
                SUM(CASE WHEN is_wanted = TRUE THEN 1 ELSE 0 END) as wanted,
                (SELECT COUNT(*) FROM person_alerts WHERE is_active = TRUE) as active_alerts
            FROM persons
        ");
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get officer statistics
     */
    private function getOfficerStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_officers,
                SUM(CASE WHEN employment_status = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN employment_status = 'On Leave' THEN 1 ELSE 0 END) as on_leave,
                (SELECT COUNT(DISTINCT assigned_to) FROM case_assignments) as assigned_to_cases
            FROM officers
        ");
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get crime trends
     */
    private function getCrimeTrends(string $period): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                case_type,
                COUNT(*) as count
            FROM cases
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY case_type
            ORDER BY count DESC
            LIMIT 10
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
