<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class VehicleController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * List vehicles
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $vehicles = $this->getVehicles($status, $search);
        
        return $this->view('vehicles/index', [
            'title' => 'Vehicle Registry',
            'vehicles' => $vehicles,
            'selected_status' => $status,
            'search_term' => $search
        ]);
    }
    
    /**
     * Register vehicle
     */
    public function create(): string
    {
        $stations = $this->getStations();
        
        return $this->view('vehicles/create', [
            'title' => 'Register Vehicle',
            'stations' => $stations
        ]);
    }
    
    /**
     * Store vehicle
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/vehicles/create');
        }
        
        $data = [
            'registration_number' => $_POST['registration_number'] ?? '',
            'vehicle_make' => $_POST['vehicle_make'] ?? null,
            'vehicle_model' => $_POST['vehicle_model'] ?? null,
            'vehicle_year' => $_POST['vehicle_year'] ?? null,
            'vehicle_color' => $_POST['vehicle_color'] ?? null,
            'vehicle_type' => $_POST['vehicle_type'] ?? 'Sedan',
            'chassis_number' => $_POST['chassis_number'] ?? null,
            'engine_number' => $_POST['engine_number'] ?? null,
            'owner_type' => $_POST['owner_type'] ?? 'Police',
            'current_station_id' => $_POST['current_station_id'] ?? null,
            'vehicle_status' => 'Active',
            'acquisition_date' => $_POST['acquisition_date'] ?? date('Y-m-d')
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vehicles (
                    registration_number, vehicle_make, vehicle_model, vehicle_year,
                    vehicle_color, vehicle_type, chassis_number, engine_number,
                    owner_type, current_station_id, vehicle_status, acquisition_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['registration_number'], $data['vehicle_make'],
                $data['vehicle_model'], $data['vehicle_year'],
                $data['vehicle_color'], $data['vehicle_type'],
                $data['chassis_number'], $data['engine_number'],
                $data['owner_type'], $data['current_station_id'],
                $data['vehicle_status'], $data['acquisition_date']
            ]);
            
            $vehicleId = (int)$this->db->lastInsertId();
            
            $this->setFlash('success', 'Vehicle registered successfully');
            $this->redirect('/vehicles/' . $vehicleId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to register vehicle: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/vehicles/create');
        }
    }
    
    /**
     * View vehicle details
     */
    public function show(int $id): string
    {
        $vehicle = $this->getVehicleDetails($id);
        
        if (!$vehicle) {
            $this->setFlash('error', 'Vehicle not found');
            $this->redirect('/vehicles');
        }
        
        $assignments = $this->getVehicleAssignments($id);
        
        return $this->view('vehicles/view', [
            'title' => 'Vehicle Details',
            'vehicle' => $vehicle,
            'assignments' => $assignments
        ]);
    }
    
    /**
     * Search vehicles
     */
    public function search(): string
    {
        $query = $_GET['q'] ?? '';
        $results = [];
        
        if ($query) {
            $results = $this->searchVehicles($query);
        }
        
        return $this->view('vehicles/search', [
            'title' => 'Vehicle Search',
            'query' => $query,
            'results' => $results
        ]);
    }
    
    private function getVehicles(?string $status, ?string $search): array
    {
        $sql = "SELECT v.*
                FROM vehicles v
                WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND v.vehicle_status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $sql .= " AND (v.registration_number LIKE ? OR v.vehicle_make LIKE ? OR v.vehicle_model LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY v.registration_number LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getVehicleDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*
            FROM vehicles v
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function getVehicleAssignments(int $vehicleId): array
    {
        $stmt = $this->db->prepare("
            SELECT va.*, CONCAT_WS(' ', o.first_name, o.last_name) as officer_name,
                   o.rank, o.service_number
            FROM vehicle_assignments va
            JOIN officers o ON va.officer_id = o.id
            WHERE va.vehicle_id = ?
            ORDER BY va.assignment_date DESC
        ");
        $stmt->execute([$vehicleId]);
        return $stmt->fetchAll();
    }
    
    private function searchVehicles(string $query): array
    {
        $stmt = $this->db->prepare("
            SELECT v.*, s.station_name
            FROM vehicles v
            LEFT JOIN stations s ON v.current_station_id = s.id
            WHERE v.registration_number LIKE ? 
               OR v.vehicle_make LIKE ?
               OR v.vehicle_model LIKE ?
               OR v.chassis_number LIKE ?
            LIMIT 50
        ");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    private function getStations(): array
    {
        $stmt = $this->db->query("SELECT id, station_name FROM stations ORDER BY station_name");
        return $stmt->fetchAll();
    }
}
