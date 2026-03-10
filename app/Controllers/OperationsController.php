<?php

namespace App\Controllers;

use App\Models\Operation;
use App\Models\Officer;
use App\Services\NotificationService;

class OperationsController extends BaseController
{
    private Operation $operationModel;
    private Officer $officerModel;
    private NotificationService $notificationService;
    
    public function __construct()
    {
        $this->operationModel = new Operation();
        $this->officerModel = new Officer();
        $this->notificationService = new NotificationService();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        
        $operations = $status 
            ? $this->operationModel->getByStatus($status)
            : $this->operationModel->query("
                SELECT 
                    op.*,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                    pr.rank_name,
                    s.station_name
                FROM operations op
                JOIN officers o ON op.operation_commander_id = o.id
                JOIN police_ranks pr ON o.rank_id = pr.id
                JOIN stations s ON op.station_id = s.id
                ORDER BY op.operation_date DESC
                LIMIT 100
            ");
        
        return $this->view('operations/index', [
            'title' => 'Police Operations',
            'operations' => $operations,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $operation = $this->operationModel->getWithDetails($id);
        
        if (!$operation) {
            $this->setFlash('error', 'Operation not found');
            $this->redirect('/operations');
        }
        
        $teamMembers = $this->operationModel->getTeamMembers($id);
        
        return $this->view('operations/view', [
            'title' => 'Operation Details',
            'operation' => $operation,
            'team_members' => $teamMembers
        ]);
    }
    
    public function create(): string
    {
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY pr.rank_level DESC, o.first_name
        ");
        
        $stations = $this->operationModel->query("SELECT id, station_name FROM stations ORDER BY station_name");
        
        return $this->view('operations/create', [
            'title' => 'Plan Operation',
            'officers' => $officers,
            'stations' => $stations
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'operation_name' => 'required',
            'operation_type' => 'required',
            'operation_date' => 'required',
            'start_time' => 'required',
            'target_location' => 'required',
            'operation_commander_id' => 'required',
            'station_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $operationCode = 'OP-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
            
            $operationId = $this->operationModel->create([
                'operation_code' => $operationCode,
                'operation_name' => $_POST['operation_name'],
                'operation_type' => $_POST['operation_type'],
                'operation_date' => $_POST['operation_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'] ?? null,
                'target_location' => $_POST['target_location'],
                'operation_commander_id' => $_POST['operation_commander_id'],
                'station_id' => $_POST['station_id'],
                'officers_deployed' => $_POST['officers_deployed'] ?? null,
                'operation_status' => 'Planned',
                'objectives' => $_POST['objectives'] ?? null,
                'case_id' => $_POST['case_id'] ?? null
            ]);
            
            if (!empty($_POST['team_members'])) {
                foreach ($_POST['team_members'] as $officerId) {
                    $this->operationModel->addTeamMember($operationId, $officerId);
                }
            }
            
            logger("Operation planned: ID {$operationId} - {$_POST['operation_name']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Operation planned successfully',
                'operation_id' => $operationId,
                'operation_code' => $operationCode
            ]);
        } catch (\Exception $e) {
            logger("Error planning operation: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to plan operation'], 500);
        }
    }
    
    public function start(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->operationModel->update($id, [
                'operation_status' => 'In Progress',
                'actual_start_time' => date('Y-m-d H:i:s')
            ]);
            
            $this->json(['success' => true, 'message' => 'Operation started']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to start operation'], 500);
        }
    }
    
    public function complete(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->operationModel->completeOperation($id, [
                'end_time' => $_POST['end_time'] ?? date('Y-m-d H:i:s'),
                'outcome_summary' => $_POST['outcome_summary'],
                'arrests_made' => $_POST['arrests_made'] ?? 0,
                'exhibits_seized' => $_POST['exhibits_seized'] ?? 0
            ]);
            
            $this->json(['success' => true, 'message' => 'Operation completed successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to complete operation'], 500);
        }
    }
    
    public function addTeamMember(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $officerId = $_POST['officer_id'] ?? null;
        $role = $_POST['role'] ?? null;
        
        if (!$officerId) {
            $this->json(['success' => false, 'message' => 'Officer ID is required'], 422);
        }
        
        try {
            $this->operationModel->addTeamMember($id, $officerId, $role);
            $this->json(['success' => true, 'message' => 'Team member added successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to add team member'], 500);
        }
    }
}
