<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;
use Throwable;

class DashboardController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function index(): string
    {
        $openCases = $this->scalar("SELECT COUNT(*) FROM cases WHERE status = 'Open'");
        $investigatingCases = $this->scalar("SELECT COUNT(*) FROM cases WHERE status = 'Under Investigation'");
        $closedCases = $this->scalar("SELECT COUNT(*) FROM cases WHERE status = 'Closed'");
        $highPriorityCases = $this->scalar("SELECT COUNT(*) FROM cases WHERE case_priority IN ('High', 'Critical')");
        $activeOfficers = $this->scalar("SELECT COUNT(*) FROM officers WHERE employment_status = 'Active'");
        $unreadNotifications = $this->scalar("SELECT COUNT(*) FROM notifications WHERE is_read = 0");

        $stats = [
            'overview' => [
                'total_cases' => $this->scalar("SELECT COUNT(*) FROM cases"),
                'open_cases' => $openCases,
                'investigating_cases' => $investigatingCases,
                'closed_cases' => $closedCases,
                'high_priority_cases' => $highPriorityCases,
                'total_persons' => $this->scalar("SELECT COUNT(*) FROM persons"),
                'wanted_persons' => $this->scalar("SELECT COUNT(*) FROM persons WHERE is_wanted = 1"),
                'missing_persons' => $this->scalar("SELECT COUNT(*) FROM missing_persons WHERE status = 'Missing'"),
                'total_suspects' => $this->scalar("SELECT COUNT(*) FROM suspects"),
                'total_officers' => $this->scalar("SELECT COUNT(*) FROM officers"),
                'active_officers' => $activeOfficers,
                'total_evidence' => $this->scalar("SELECT COUNT(*) FROM evidence"),
                'total_vehicles' => $this->scalar("SELECT COUNT(*) FROM vehicles"),
                'total_firearms' => $this->scalar("SELECT COUNT(*) FROM firearms"),
                'total_assets' => $this->scalar("SELECT COUNT(*) FROM assets")
            ],
            'workflow' => [
                'operations' => [
                    'active_operations' => $this->scalar("SELECT COUNT(*) FROM operations WHERE operation_status IN ('Planned', 'In Progress')"),
                    'patrols_in_progress' => $this->scalar("SELECT COUNT(*) FROM patrol_logs WHERE patrol_status = 'In Progress'"),
                    'duty_scheduled_today' => $this->scalar("SELECT COUNT(*) FROM duty_roster WHERE duty_date = CURDATE()"),
                    'incident_open' => $this->scalar("SELECT COUNT(*) FROM incident_reports WHERE status IN ('Open', 'Under Review')")
                ],
                'legal' => [
                    'arrests' => $this->scalar("SELECT COUNT(*) FROM arrests"),
                    'charges_pending' => $this->scalar("SELECT COUNT(*) FROM charges WHERE charge_status = 'Pending'"),
                    'bail_active' => $this->scalar("SELECT COUNT(*) FROM bail_records WHERE bail_status = 'Granted'"),
                    'warrants_active' => $this->scalar("SELECT COUNT(*) FROM warrants WHERE status = 'Active'")
                ],
                'intelligence' => [
                    'active_bulletins' => $this->scalar("SELECT COUNT(*) FROM intelligence_bulletins WHERE status = 'Active'"),
                    'intelligence_reports' => $this->scalar("SELECT COUNT(*) FROM intelligence_reports"),
                    'public_tips_pending' => $this->scalar("SELECT COUNT(*) FROM public_intelligence_tips WHERE tip_status IN ('Received', 'Under Review')"),
                    'informants' => $this->scalar("SELECT COUNT(*) FROM informants")
                ],
                'public_services' => [
                    'public_complaints_open' => $this->scalar("SELECT COUNT(*) FROM public_complaints WHERE complaint_status IN ('Received', 'Under Investigation')"),
                    'missing_persons_open' => $this->scalar("SELECT COUNT(*) FROM missing_persons WHERE status = 'Missing'"),
                    'statements' => $this->scalar("SELECT COUNT(*) FROM statements"),
                    'notifications_unread' => $unreadNotifications
                ]
            ],
            'distribution' => [
                'case_status' => $this->rows("
                    SELECT status, COUNT(*) AS total
                    FROM cases
                    GROUP BY status
                    ORDER BY total DESC
                "),
                'case_priority' => $this->rows("
                    SELECT case_priority, COUNT(*) AS total
                    FROM cases
                    GROUP BY case_priority
                    ORDER BY total DESC
                "),
                'operation_status' => $this->rows("
                    SELECT operation_status, COUNT(*) AS total
                    FROM operations
                    GROUP BY operation_status
                    ORDER BY total DESC
                ")
            ],
            'recent' => [
                'cases' => $this->rows("
                    SELECT id, case_number, case_priority, status, description, created_at
                    FROM cases
                    ORDER BY created_at DESC
                    LIMIT 6
                "),
                'incidents' => $this->rows("
                    SELECT id, incident_number, incident_type, status, incident_location, created_at
                    FROM incident_reports
                    ORDER BY created_at DESC
                    LIMIT 6
                "),
                'complaints' => $this->rows("
                    SELECT id, complaint_number, complainant_name, complaint_type, complaint_status, created_at
                    FROM public_complaints
                    ORDER BY created_at DESC
                    LIMIT 6
                "),
                'alerts' => $this->rows("
                    SELECT id, first_name, last_name, risk_level, created_at
                    FROM persons
                    WHERE is_wanted = 1
                    ORDER BY created_at DESC
                    LIMIT 5
                ")
            ],
            'kpis' => [
                'case_resolution_rate' => $this->rate($closedCases, max($openCases + $investigatingCases + $closedCases, 1)),
                'officer_availability_rate' => $this->rate($activeOfficers, max($this->scalar("SELECT COUNT(*) FROM officers"), 1)),
                'high_priority_pressure' => $this->rate($highPriorityCases, max($openCases + $investigatingCases, 1))
            ]
        ];
        
        return $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats
        ]);
    }

    private function scalar(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)($stmt->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function rows(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function rate(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
