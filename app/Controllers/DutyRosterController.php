<?php

namespace App\Controllers;

use App\Models\DutyRoster;
use App\Models\Officer;
use App\Models\Station;
use App\Config\Database;
use PDO;

class DutyRosterController extends BaseController
{
    private DutyRoster $rosterModel;
    private Officer $officerModel;
    private Station $stationModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->rosterModel = new DutyRoster();
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
        $this->db = Database::getConnection();
    }
    
    /**
     * Display duty roster
     */
    public function index(): string
    {
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        $date = $_GET['date'] ?? date('Y-m-d');
        $shiftId = $_GET['shift'] ?? null;
        
        $roster = $this->getRosterByDate($stationId, $date, $shiftId);
        $stations = $this->stationModel->all();
        $shifts = $this->getShifts();
        
        return $this->view('duty_roster/index', [
            'title' => 'Duty Roster',
            'roster' => $roster,
            'stations' => $stations,
            'shifts' => $shifts,
            'selected_station' => $stationId,
            'selected_date' => $date,
            'selected_shift' => $shiftId
        ]);
    }
    
    /**
     * Show roster creation form
     */
    public function create(): string
    {
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        
        $officers = $stationId ? $this->getStationOfficers($stationId) : [];
        $stations = $this->stationModel->all();
        $shifts = $this->getShifts();
        
        return $this->view('duty_roster/create', [
            'title' => 'Schedule Duty',
            'officers' => $officers,
            'stations' => $stations,
            'shifts' => $shifts,
            'selected_station' => $stationId
        ]);
    }
    
    /**
     * Store new duty assignment
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/duty-roster/create');
        }
        
        $data = [
            'officer_id' => $_POST['officer_id'] ?? null,
            'station_id' => $_POST['station_id'] ?? null,
            'shift_id' => $_POST['shift_id'] ?? null,
            'duty_date' => $_POST['duty_date'] ?? null,
            'duty_type' => $_POST['duty_type'] ?? 'Regular',
            'duty_location' => $_POST['duty_location'] ?? null,
            'supervisor_id' => $_POST['supervisor_id'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'created_by' => auth_id()
        ];
        
        $errors = $this->validate($data, [
            'officer_id' => 'required',
            'station_id' => 'required',
            'shift_id' => 'required',
            'duty_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/duty-roster/create');
        }
        
        try {
            $rosterId = $this->rosterModel->create($data);
            $this->setFlash('success', 'Duty scheduled successfully');
            $this->redirect('/duty-roster?date=' . $data['duty_date']);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to schedule duty: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/duty-roster/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $roster = $this->rosterModel->find($id);
        
        if (!$roster) {
            $this->setFlash('error', 'Duty assignment not found');
            $this->redirect('/duty-roster');
        }
        
        $officers = $this->getStationOfficers($roster['station_id']);
        $stations = $this->stationModel->all();
        $shifts = $this->getShifts();
        
        return $this->view('duty_roster/edit', [
            'title' => 'Edit Duty Assignment',
            'roster' => $roster,
            'officers' => $officers,
            'stations' => $stations,
            'shifts' => $shifts
        ]);
    }
    
    /**
     * Update duty assignment
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/duty-roster/' . $id . '/edit');
        }
        
        $data = [
            'shift_id' => $_POST['shift_id'] ?? null,
            'duty_type' => $_POST['duty_type'] ?? 'Regular',
            'duty_location' => $_POST['duty_location'] ?? null,
            'supervisor_id' => $_POST['supervisor_id'] ?? null,
            'status' => $_POST['status'] ?? 'Scheduled',
            'notes' => $_POST['notes'] ?? null
        ];
        
        $success = $this->rosterModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Duty assignment updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update duty assignment');
        }
        
        $this->redirect('/duty-roster');
    }
    
    /**
     * Delete duty assignment
     */
    public function delete(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $success = $this->rosterModel->delete($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Duty assignment deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete duty assignment'], 500);
        }
    }
    
    /**
     * Get roster by date
     */
    private function getRosterByDate(?int $stationId, string $date, ?int $shiftId): array
    {
        $sql = "
            SELECT 
                dr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                o.service_number,
                pr.rank_name,
                pr.rank_level,
                s.station_name,
                ds.shift_name,
                ds.start_time,
                ds.end_time,
                CONCAT_WS(' ', sup.first_name, sup.middle_name, sup.last_name) as supervisor_name
            FROM duty_roster dr
            JOIN officers o ON dr.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN stations s ON dr.station_id = s.id
            JOIN duty_shifts ds ON dr.shift_id = ds.id
            LEFT JOIN officers sup ON dr.supervisor_id = sup.id
            WHERE dr.duty_date = ?
        ";
        
        $params = [$date];
        
        if ($stationId) {
            $sql .= " AND dr.station_id = ?";
            $params[] = $stationId;
        }
        
        if ($shiftId) {
            $sql .= " AND dr.shift_id = ?";
            $params[] = $shiftId;
        }
        
        $sql .= " ORDER BY ds.start_time, pr.rank_level DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all shifts
     */
    private function getShifts(): array
    {
        $stmt = $this->db->query("SELECT * FROM duty_shifts ORDER BY start_time");
        return $stmt->fetchAll();
    }
    
    /**
     * Get station officers
     */
    private function getStationOfficers(int $stationId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as full_name,
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
    
    /**
     * Get weekly roster view
     */
    public function weekly(): string
    {
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
        
        $weeklyRoster = $this->getWeeklyRoster($stationId, $startDate);
        $stations = $this->stationModel->all();
        
        return $this->view('duty_roster/weekly', [
            'title' => 'Weekly Duty Roster',
            'roster' => $weeklyRoster,
            'stations' => $stations,
            'selected_station' => $stationId,
            'start_date' => $startDate
        ]);
    }
    
    /**
     * Get weekly roster data
     */
    private function getWeeklyRoster(?int $stationId, string $startDate): array
    {
        $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));
        
        $sql = "
            SELECT 
                dr.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                pr.rank_level,
                ds.shift_name,
                ds.start_time,
                ds.end_time
            FROM duty_roster dr
            JOIN officers o ON dr.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN duty_shifts ds ON dr.shift_id = ds.id
            WHERE dr.duty_date BETWEEN ? AND ?
        ";
        
        $params = [$startDate, $endDate];
        
        if ($stationId) {
            $sql .= " AND dr.station_id = ?";
            $params[] = $stationId;
        }
        
        $sql .= " ORDER BY dr.duty_date, ds.start_time, pr.rank_level DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
