<?php

namespace App\Controllers;

use App\Models\Officer;
use App\Models\Station;
use App\Services\OfficerService;

class OfficerController extends BaseController
{
    private Officer $officerModel;
    private Station $stationModel;
    private OfficerService $officerService;
    
    public function __construct()
    {
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
        $this->officerService = new OfficerService();
    }
    
    /**
     * Display list of all officers
     */
    public function index(): string
    {
        $page = (int)($_GET['page'] ?? 1);
        $rank = $_GET['rank'] ?? null;
        $station = $_GET['station'] ?? null;
        
        $result = $this->officerModel->paginate($page, 25);
        $officers = $result['data'];
        $pagination = $result;
        
        $ranks = $this->officerService->getAllRanks();
        $stations = $this->stationModel->all();
        
        return $this->view('officers/index', [
            'title' => 'Officer Management',
            'officers' => $officers,
            'pagination' => $pagination,
            'ranks' => $ranks,
            'stations' => $stations,
            'selected_rank' => $rank,
            'selected_station' => $station
        ]);
    }
    
    /**
     * Show officer creation form
     */
    public function create(): string
    {
        $ranks = $this->officerService->getAllRanks();
        $stations = $this->stationModel->all();
        
        return $this->view('officers/create', [
            'title' => 'Register Officer',
            'ranks' => $ranks,
            'stations' => $stations
        ]);
    }
    
    /**
     * Store new officer
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/officers/create');
        }
        
        $data = [
            'service_number' => $_POST['service_number'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'rank_id' => $_POST['rank_id'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'phone_number' => $_POST['phone_number'] ?? null,
            'email' => $_POST['email'] ?? null,
            'residential_address' => $_POST['residential_address'] ?? null,
            'date_of_enlistment' => $_POST['date_of_enlistment'] ?? null,
            'current_station_id' => $_POST['current_station_id'] ?? null,
            'employment_status' => 'Active'
        ];
        
        $errors = $this->validate($data, [
            'service_number' => 'required',
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'rank_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/officers/create');
        }
        
        try {
            $officerId = $this->officerService->registerOfficer($data);
            $this->setFlash('success', 'Officer registered successfully');
            $this->redirect('/officers/' . $officerId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to register officer: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/officers/create');
        }
    }
    
    /**
     * Show officer profile - ENHANCED with Phase 1 integration
     */
    public function show(int $id): string
    {
        // Use enhanced service method to get complete profile
        $profile = $this->officerService->getOfficerFullProfile($id);
        
        if (!$profile) {
            $this->setFlash('error', 'Officer not found');
            $this->redirect('/officers');
        }
        
        // Get workload for display
        $workload = $this->officerService->checkOfficerWorkload($id);
        
        return $this->view('officers/profile', [
            'title' => 'Officer Profile',
            'officer' => $profile,
            'assigned_cases' => $profile['assigned_cases'] ?? [],
            'postings' => $profile['posting_history'] ?? [],
            'promotions' => $profile['promotion_history'] ?? [],
            'current_posting' => $profile['current_posting'] ?? null,
            'patrols' => $profile['patrol_logs'] ?? [],
            'arrests' => $profile['arrests_made'] ?? [],
            'performance' => $profile['performance_metrics'] ?? [],
            'training' => $profile['training_records'] ?? [],
            'leave' => $profile['leave_records'] ?? [],
            'commendations' => $profile['commendation_records'] ?? [],
            'disciplinary' => $profile['disciplinary_records'] ?? [],
            'workload' => $workload
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $officer = $this->officerModel->find($id);
        
        if (!$officer) {
            $this->setFlash('error', 'Officer not found');
            $this->redirect('/officers');
        }
        
        $ranks = $this->officerService->getAllRanks();
        $stations = $this->stationModel->all();
        
        return $this->view('officers/edit', [
            'title' => 'Edit Officer',
            'officer' => $officer,
            'ranks' => $ranks,
            'stations' => $stations
        ]);
    }
    
    /**
     * Update officer
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/officers/' . $id . '/edit');
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'rank_id' => $_POST['rank_id'] ?? null,
            'phone_number' => $_POST['phone_number'] ?? null,
            'email' => $_POST['email'] ?? null,
            'residential_address' => $_POST['residential_address'] ?? null,
            'current_station_id' => $_POST['current_station_id'] ?? null,
            'employment_status' => 'Active'
        ];
        
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/officers/' . $id . '/edit');
        }
        
        $success = $this->officerModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Officer updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update officer');
        }
        
        $this->redirect('/officers/' . $id);
    }
    
    /**
     * Transfer officer to new station
     */
    public function transfer(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $newStationId = $_POST['station_id'] ?? null;
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
        $reason = $_POST['reason'] ?? '';
        
        try {
            $this->officerService->transferOfficer($id, $newStationId, $effectiveDate, $reason, auth_id());
            $this->json(['success' => true, 'message' => 'Officer transferred successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Promote officer
     */
    public function promote(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $newRank = $_POST['new_rank'] ?? '';
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        try {
            $this->officerService->promoteOfficer($id, $newRank, $effectiveDate, $notes, auth_id());
            $this->json(['success' => true, 'message' => 'Officer promoted successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get active officers for dropdowns (API endpoint)
     */
    public function getActiveOfficers(): void
    {
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        header('Content-Type: application/json');
        echo json_encode($officers);
        exit;
    }
    
    /**
     * Search officers by name or service number
     */
    public function search(string $query): void
    {
        $officers = $this->officerModel->searchOfficers($query);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'officers' => $officers]);
        exit;
    }
}
