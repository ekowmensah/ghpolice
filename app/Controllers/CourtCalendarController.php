<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class CourtCalendarController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Display court calendar
     */
    public function index(): string
    {
        $month = $_GET['month'] ?? date('Y-m');
        $courtName = $_GET['court'] ?? null;
        
        $hearings = $this->getHearingsByMonth($month, $courtName);
        $courts = $this->getCourts();
        
        return $this->view('court/calendar', [
            'title' => 'Court Calendar',
            'hearings' => $hearings,
            'courts' => $courts,
            'selected_month' => $month,
            'selected_court' => $courtName
        ]);
    }
    
    /**
     * Get upcoming hearings
     */
    public function upcoming(): string
    {
        $days = $_GET['days'] ?? 30;
        $courtName = $_GET['court'] ?? null;
        
        $hearings = $this->getUpcomingHearings($days, $courtName);
        $courts = $this->getCourts();
        
        return $this->view('court/upcoming', [
            'title' => 'Upcoming Court Hearings',
            'hearings' => $hearings,
            'courts' => $courts,
            'days' => $days,
            'selected_court' => $courtName
        ]);
    }
    
    /**
     * Get hearings for specific date
     */
    public function daily(): string
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $courtName = $_GET['court'] ?? null;
        
        $hearings = $this->getHearingsByDate($date, $courtName);
        $courts = $this->getCourts();
        
        return $this->view('court/daily', [
            'title' => 'Daily Court Schedule - ' . date('F d, Y', strtotime($date)),
            'hearings' => $hearings,
            'courts' => $courts,
            'selected_date' => $date,
            'selected_court' => $courtName
        ]);
    }
    
    /**
     * Update hearing outcome
     */
    public function updateOutcome(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $outcome = $_POST['outcome'] ?? '';
        $nextHearingDate = $_POST['next_hearing_date'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE court_proceedings
                SET outcome = ?, next_hearing_date = ?, notes = CONCAT(notes, '\n\n', ?)
                WHERE id = ?
            ");
            
            $stmt->execute([$outcome, $nextHearingDate, $notes, $id]);
            
            $this->json(['success' => true, 'message' => 'Hearing outcome updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get hearings by month
     */
    private function getHearingsByMonth(string $month, ?string $courtName): array
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description,
                c.case_priority,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            LEFT JOIN case_suspects cs ON c.id = cs.case_id
            LEFT JOIN suspects s ON cs.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE cp.court_date BETWEEN ? AND ?
        ";
        
        $params = [$startDate, $endDate];
        
        if ($courtName) {
            $sql .= " AND cp.court_name = ?";
            $params[] = $courtName;
        }
        
        $sql .= " ORDER BY cp.court_date, cp.court_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get upcoming hearings
     */
    private function getUpcomingHearings(int $days, ?string $courtName): array
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description,
                c.case_priority,
                c.status as case_status,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            LEFT JOIN case_suspects cs ON c.id = cs.case_id
            LEFT JOIN suspects s ON cs.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON cp.recorded_by = u.id
            WHERE cp.hearing_date BETWEEN ? AND ?
            AND (cp.outcome IS NULL OR cp.outcome = '')
        ";
        
        $params = [$startDate, $endDate];
        
        if ($courtName) {
            $sql .= " AND cp.court_name = ?";
            $params[] = $courtName;
        }
        
        $sql .= " ORDER BY cp.hearing_date, cp.court_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get hearings by date
     */
    private function getHearingsByDate(string $date, ?string $courtName): array
    {
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description,
                c.case_priority,
                c.status as case_status,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            LEFT JOIN case_suspects cs ON c.id = cs.case_id
            LEFT JOIN suspects s ON cs.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE DATE(cp.hearing_date) = ?
        ";
        
        $params = [$date];
        
        if ($courtName) {
            $sql .= " AND cp.court_name = ?";
            $params[] = $courtName;
        }
        
        $sql .= " ORDER BY cp.hearing_date, cp.court_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get list of courts
     */
    private function getCourts(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT court_name
            FROM court_proceedings
            WHERE court_name IS NOT NULL
            ORDER BY court_name
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get court statistics
     */
    public function statistics(): string
    {
        $stats = $this->getCourtStatistics();
        
        return $this->view('court/statistics', [
            'title' => 'Court Statistics',
            'stats' => $stats
        ]);
    }
    
    /**
     * Get court statistics data
     */
    private function getCourtStatistics(): array
    {
        // Total hearings
        $stmt = $this->db->query("
            SELECT COUNT(*) as total FROM court_proceedings
        ");
        $totalHearings = $stmt->fetch()['total'];
        
        // Pending hearings
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM court_proceedings
            WHERE hearing_date >= CURDATE() AND (outcome IS NULL OR outcome = '')
        ");
        $pendingHearings = $stmt->fetch()['total'];
        
        // Completed hearings
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM court_proceedings
            WHERE outcome IS NOT NULL AND outcome != ''
        ");
        $completedHearings = $stmt->fetch()['total'];
        
        // Hearings by court
        $stmt = $this->db->query("
            SELECT court_name, COUNT(*) as count
            FROM court_proceedings
            GROUP BY court_name
            ORDER BY count DESC
            LIMIT 10
        ");
        $hearingsByCourt = $stmt->fetchAll();
        
        // Hearings by outcome
        $stmt = $this->db->query("
            SELECT outcome, COUNT(*) as count
            FROM court_proceedings
            WHERE outcome IS NOT NULL AND outcome != ''
            GROUP BY outcome
            ORDER BY count DESC
        ");
        $hearingsByOutcome = $stmt->fetchAll();
        
        return [
            'total_hearings' => $totalHearings,
            'pending_hearings' => $pendingHearings,
            'completed_hearings' => $completedHearings,
            'hearings_by_court' => $hearingsByCourt,
            'hearings_by_outcome' => $hearingsByOutcome
        ];
    }
}
