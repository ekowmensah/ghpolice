<?php

namespace App\Controllers;

use App\Models\SurveillanceOperation;
use App\Models\Officer;

class SurveillanceController extends BaseController
{
    private SurveillanceOperation $surveillanceModel;
    private Officer $officerModel;
    
    public function __construct()
    {
        $this->surveillanceModel = new SurveillanceOperation();
        $this->officerModel = new Officer();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        
        $surveillances = $status === 'active'
            ? $this->surveillanceModel->getActive()
            : $this->surveillanceModel->query("
                SELECT 
                    so.*,
                    c.case_number,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as commander_name,
                    pr.rank_name
                FROM surveillance_operations so
                LEFT JOIN cases c ON so.case_id = c.id
                JOIN officers o ON so.operation_commander_id = o.id
                JOIN police_ranks pr ON o.rank_id = pr.id
                ORDER BY so.start_date DESC
                LIMIT 100
            ");
        
        return $this->view('surveillance/index', [
            'title' => 'Surveillance Operations',
            'surveillances' => $surveillances,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $surveillance = $this->surveillanceModel->getWithDetails($id);
        
        if (!$surveillance) {
            $this->setFlash('error', 'Surveillance operation not found');
            $this->redirect('/surveillance');
        }
        
        $teamMembers = $this->surveillanceModel->getTeamMembers($id);
        
        return $this->view('surveillance/view', [
            'title' => 'Surveillance Operation Details',
            'surveillance' => $surveillance,
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
        
        return $this->view('surveillance/create', [
            'title' => 'Plan Surveillance Operation',
            'officers' => $officers
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'operation_name' => 'required',
            'surveillance_type' => 'required',
            'target_description' => 'required',
            'operation_commander_id' => 'required',
            'start_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $operationCode = 'SURV-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
            
            $surveillanceId = $this->surveillanceModel->create([
                'operation_code' => $operationCode,
                'operation_name' => $_POST['operation_name'],
                'surveillance_type' => $_POST['surveillance_type'],
                'target_description' => $_POST['target_description'],
                'target_location' => $_POST['target_location'] ?? null,
                'operation_commander_id' => $_POST['operation_commander_id'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null,
                'operation_status' => 'Planned',
                'objectives' => $_POST['objectives'] ?? null,
                'case_id' => $_POST['case_id'] ?? null
            ]);
            
            if (!empty($_POST['team_members'])) {
                foreach ($_POST['team_members'] as $member) {
                    $this->surveillanceModel->addTeamMember(
                        $surveillanceId,
                        $member['officer_id'],
                        $member['role'] ?? null
                    );
                }
            }
            
            logger("Surveillance operation planned: ID {$surveillanceId}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Surveillance operation planned successfully',
                'surveillance_id' => $surveillanceId
            ]);
        } catch (\Exception $e) {
            logger("Error planning surveillance: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to plan surveillance operation'], 500);
        }
    }
    
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        
        if (empty($status)) {
            $this->json(['success' => false, 'message' => 'Status is required'], 422);
        }
        
        try {
            $this->surveillanceModel->update($id, ['operation_status' => $status]);
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
}
