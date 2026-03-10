<?php

namespace App\Controllers;

use App\Models\PatrolLog;
use App\Models\Officer;
use App\Models\Station;
use App\Config\Database;
use PDO;

class PatrolLogController extends BaseController
{
    private PatrolLog $patrolModel;
    private Officer $officerModel;
    private Station $stationModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->patrolModel = new PatrolLog();
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
        $this->db = Database::getConnection();
    }
    
    /**
     * Display patrol logs
     */
    public function index(): string
    {
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        $status = $_GET['status'] ?? null;
        $date = $_GET['date'] ?? null;
        
        $patrols = $this->getFilteredPatrols($stationId, $status, $date);
        $stations = $this->stationModel->all();
        
        return $this->view('patrol_logs/index', [
            'title' => 'Patrol Logs',
            'patrols' => $patrols,
            'stations' => $stations,
            'selected_station' => $stationId,
            'selected_status' => $status,
            'selected_date' => $date
        ]);
    }
    
    /**
     * Show patrol creation form
     */
    public function create(): string
    {
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        
        $officers = $stationId ? $this->getStationOfficers($stationId) : [];
        $stations = $this->stationModel->all();
        $vehicles = $this->getVehicles($stationId);
        
        return $this->view('patrol_logs/create', [
            'title' => 'Start Patrol',
            'officers' => $officers,
            'stations' => $stations,
            'vehicles' => $vehicles,
            'selected_station' => $stationId
        ]);
    }
    
    /**
     * Store new patrol
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/patrol-logs/create');
        }
        
        $patrolNumber = $this->generatePatrolNumber($_POST['station_id']);
        
        $data = [
            'patrol_number' => $patrolNumber,
            'station_id' => $_POST['station_id'] ?? null,
            'patrol_type' => $_POST['patrol_type'] ?? 'Foot Patrol',
            'patrol_area' => $_POST['patrol_area'] ?? '',
            'start_time' => $_POST['start_time'] ?? date('Y-m-d H:i:s'),
            'patrol_leader_id' => $_POST['patrol_leader_id'] ?? null,
            'vehicle_id' => $_POST['vehicle_id'] ?? null,
            'patrol_status' => 'In Progress'
        ];
        
        $errors = $this->validate($data, [
            'station_id' => 'required',
            'patrol_type' => 'required',
            'patrol_area' => 'required',
            'patrol_leader_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/patrol-logs/create');
        }
        
        try {
            $this->db->beginTransaction();
            
            $patrolId = $this->patrolModel->create($data);
            
            // Add patrol officers
            if (!empty($_POST['officer_ids'])) {
                $this->addPatrolOfficers($patrolId, $_POST['officer_ids']);
            }
            
            $this->db->commit();
            
            $this->setFlash('success', 'Patrol started successfully');
            $this->redirect('/patrol-logs/' . $patrolId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', 'Failed to start patrol: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/patrol-logs/create');
        }
    }
    
    /**
     * Show patrol details
     */
    public function show(int $id): string
    {
        $patrol = $this->getPatrolDetails($id);
        
        if (!$patrol) {
            $this->setFlash('error', 'Patrol not found');
            $this->redirect('/patrol-logs');
        }
        
        $officers = $this->getPatrolOfficers($id);
        $incidents = $this->getPatrolIncidents($id);
        
        return $this->view('patrol_logs/view', [
            'title' => 'Patrol Details',
            'patrol' => $patrol,
            'officers' => $officers,
            'incidents' => $incidents
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $patrol = $this->patrolModel->find($id);
        
        if (!$patrol) {
            $this->setFlash('error', 'Patrol not found');
            $this->redirect('/patrol-logs');
        }
        
        $officers = $this->getStationOfficers($patrol['station_id']);
        $stations = $this->stationModel->all();
        $vehicles = $this->getVehicles($patrol['station_id']);
        $patrolOfficers = $this->getPatrolOfficers($id);
        
        return $this->view('patrol_logs/edit', [
            'title' => 'Edit Patrol',
            'patrol' => $patrol,
            'officers' => $officers,
            'stations' => $stations,
            'vehicles' => $vehicles,
            'patrol_officers' => $patrolOfficers
        ]);
    }
    
    /**
     * Update patrol
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/patrol-logs/' . $id . '/edit');
        }
        
        $data = [
            'patrol_type' => $_POST['patrol_type'] ?? 'Foot Patrol',
            'patrol_area' => $_POST['patrol_area'] ?? '',
            'patrol_status' => $_POST['patrol_status'] ?? 'In Progress',
            'report_summary' => $_POST['report_summary'] ?? null
        ];
        
        if (!empty($_POST['end_time']) && $_POST['patrol_status'] === 'Completed') {
            $data['end_time'] = $_POST['end_time'];
        }
        
        $success = $this->patrolModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Patrol updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update patrol');
        }
        
        $this->redirect('/patrol-logs/' . $id);
    }
    
    /**
     * Complete patrol
     */
    public function complete(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $data = [
            'patrol_status' => 'Completed',
            'end_time' => date('Y-m-d H:i:s'),
            'report_summary' => $_POST['report_summary'] ?? null
        ];
        
        $success = $this->patrolModel->update($id, $data);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Patrol completed successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to complete patrol'], 500);
        }
    }
    
    /**
     * Add incident to patrol
     */
    public function addIncident(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $data = [
            'patrol_id' => $id,
            'incident_time' => $_POST['incident_time'] ?? date('Y-m-d H:i:s'),
            'incident_location' => $_POST['incident_location'] ?? '',
            'incident_type' => $_POST['incident_type'] ?? '',
            'incident_description' => $_POST['incident_description'] ?? '',
            'action_taken' => $_POST['action_taken'] ?? '',
            'case_id' => $_POST['case_id'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO patrol_incidents (
                    patrol_id, incident_time, incident_location, incident_type,
                    incident_description, action_taken, case_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['patrol_id'],
                $data['incident_time'],
                $data['incident_location'],
                $data['incident_type'],
                $data['incident_description'],
                $data['action_taken'],
                $data['case_id']
            ]);
            
            // Update incident count
            $this->db->prepare("
                UPDATE patrol_logs 
                SET incidents_reported = incidents_reported + 1 
                WHERE id = ?
            ")->execute([$id]);
            
            $this->json(['success' => true, 'message' => 'Incident added successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get filtered patrols
     */
    private function getFilteredPatrols(?int $stationId, ?string $status, ?string $date): array
    {
        $sql = "
            SELECT 
                pl.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as leader_name,
                pr.rank_name as leader_rank
            FROM patrol_logs pl
            JOIN stations s ON pl.station_id = s.id
            JOIN officers o ON pl.patrol_leader_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($stationId) {
            $sql .= " AND pl.station_id = ?";
            $params[] = $stationId;
        }
        
        if ($status) {
            $sql .= " AND pl.patrol_status = ?";
            $params[] = $status;
        }
        
        if ($date) {
            $sql .= " AND DATE(pl.start_time) = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY pl.start_time DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get patrol details
     */
    private function getPatrolDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                pl.*,
                s.station_name,
                s.station_code,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as leader_name,
                pr.rank_name as leader_rank,
                o.service_number as leader_service_number,
                v.vehicle_registration,
                v.vehicle_type
            FROM patrol_logs pl
            JOIN stations s ON pl.station_id = s.id
            JOIN officers o ON pl.patrol_leader_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN vehicles v ON pl.vehicle_id = v.id
            WHERE pl.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get patrol officers
     */
    private function getPatrolOfficers(int $patrolId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                po.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                pr.rank_level,
                o.service_number
            FROM patrol_officers po
            JOIN officers o ON po.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE po.patrol_id = ?
            ORDER BY pr.rank_level DESC
        ");
        $stmt->execute([$patrolId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get patrol incidents
     */
    private function getPatrolIncidents(int $patrolId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                pi.*,
                c.case_number
            FROM patrol_incidents pi
            LEFT JOIN cases c ON pi.case_id = c.id
            WHERE pi.patrol_id = ?
            ORDER BY pi.incident_time
        ");
        $stmt->execute([$patrolId]);
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
     * Get vehicles
     */
    private function getVehicles(?int $stationId): array
    {
        $sql = "SELECT * FROM vehicles WHERE vehicle_status = 'Active'";
        
        if ($stationId) {
            $sql .= " AND current_station_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$stationId]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Generate patrol number
     */
    private function generatePatrolNumber(int $stationId): string
    {
        $station = $this->stationModel->find($stationId);
        $stationCode = $station['station_code'] ?? 'PTL';
        $date = date('Ymd');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM patrol_logs 
            WHERE station_id = ? AND DATE(start_time) = CURDATE()
        ");
        $stmt->execute([$stationId]);
        $count = $stmt->fetch()['count'] + 1;
        
        return sprintf('%s-PTL-%s-%03d', $stationCode, $date, $count);
    }
    
    /**
     * Add patrol officers
     */
    private function addPatrolOfficers(int $patrolId, array $officerIds): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO patrol_officers (patrol_id, officer_id) VALUES (?, ?)
        ");
        
        foreach ($officerIds as $officerId) {
            $stmt->execute([$patrolId, $officerId]);
        }
    }
}
