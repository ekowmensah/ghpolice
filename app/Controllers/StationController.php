<?php

namespace App\Controllers;

use App\Models\Station;
use App\Config\Database;
use PDO;

class StationController extends BaseController
{
    private Station $stationModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->stationModel = new Station();
        $this->db = Database::getConnection();
    }
    
    /**
     * Display list of stations
     */
    public function index(): string
    {
        $region = $_GET['region'] ?? null;
        $district = $_GET['district'] ?? null;
        
        $stations = $region || $district 
            ? $this->getFilteredStations($region, $district)
            : $this->stationModel->all();
        
        $regions = $this->getRegions();
        $districts = $this->getDistricts();
        
        return $this->view('stations/index', [
            'title' => 'Station Management',
            'stations' => $stations,
            'regions' => $regions,
            'districts' => $districts,
            'selected_region' => $region,
            'selected_district' => $district
        ]);
    }
    
    /**
     * Show station creation form
     */
    public function create(): string
    {
        $regions = $this->getRegions();
        $divisions = $this->getDivisions();
        $districts = $this->getDistricts();
        
        return $this->view('stations/create', [
            'title' => 'Register Station',
            'regions' => $regions,
            'divisions' => $divisions,
            'districts' => $districts
        ]);
    }
    
    /**
     * Store new station
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/stations/create');
        }
        
        $data = [
            'station_name' => $_POST['station_name'] ?? '',
            'station_code' => $_POST['station_code'] ?? '',
            'region_id' => $_POST['region_id'] ?? null,
            'division_id' => $_POST['division_id'] ?? null,
            'district_id' => $_POST['district_id'] ?? null,
            'address' => $_POST['address'] ?? null,
            'contact_number' => $_POST['contact_number'] ?? null
        ];
        
        $errors = $this->validate($data, [
            'station_name' => 'required|min:3',
            'station_code' => 'required',
            'district_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/stations/create');
        }
        
        try {
            $stationId = $this->stationModel->create($data);
            $this->setFlash('success', 'Station registered successfully');
            $this->redirect('/stations/' . $stationId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to register station: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/stations/create');
        }
    }
    
    /**
     * Show station details
     */
    public function show(int $id): string
    {
        $station = $this->stationModel->getWithHierarchy($id);
        
        if (!$station) {
            $this->setFlash('error', 'Station not found');
            $this->redirect('/stations');
        }
        
        $caseCount = $this->stationModel->getCaseCount($id);
        $officers = $this->getStationOfficers($id);
        
        return $this->view('stations/view', [
            'title' => 'Station Details',
            'station' => $station,
            'case_count' => $caseCount,
            'officers' => $officers
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $station = $this->stationModel->find($id);
        
        if (!$station) {
            $this->setFlash('error', 'Station not found');
            $this->redirect('/stations');
        }
        
        $regions = $this->getRegions();
        $districts = $this->getDistricts();
        
        return $this->view('stations/edit', [
            'title' => 'Edit Station',
            'station' => $station,
            'regions' => $regions,
            'districts' => $districts
        ]);
    }
    
    /**
     * Update station
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/stations/' . $id . '/edit');
        }
        
        $data = [
            'station_name' => $_POST['station_name'] ?? '',
            'station_code' => $_POST['station_code'] ?? '',
            'address' => $_POST['address'] ?? null,
            'contact_number' => $_POST['contact_number'] ?? null
        ];
        
        $success = $this->stationModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Station updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update station');
        }
        
        $this->redirect('/stations/' . $id);
    }
    
    /**
     * Get regions
     */
    private function getRegions(): array
    {
        $stmt = $this->db->query("SELECT * FROM regions ORDER BY region_name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get divisions
     */
    private function getDivisions(): array
    {
        $stmt = $this->db->query("
            SELECT division.*, r.region_name
            FROM divisions division
            LEFT JOIN regions r ON division.region_id = r.id
            ORDER BY r.region_name, division.division_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get districts
     */
    private function getDistricts(): array
    {
        $stmt = $this->db->query("
            SELECT d.*, division.division_name, r.region_name
            FROM districts d
            LEFT JOIN divisions division ON d.division_id = division.id
            LEFT JOIN regions r ON division.region_id = r.id
            ORDER BY r.region_name, division.division_name, d.district_name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get filtered stations
     */
    private function getFilteredStations(?string $regionId, ?string $districtId): array
    {
        $sql = "SELECT * FROM stations WHERE 1=1";
        $params = [];
        
        if ($regionId) {
            $sql .= " AND region_id = ?";
            $params[] = $regionId;
        }
        
        if ($districtId) {
            $sql .= " AND district_id = ?";
            $params[] = $districtId;
        }
        
        $sql .= " ORDER BY station_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
}
