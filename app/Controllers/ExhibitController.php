<?php

namespace App\Controllers;

use App\Models\Exhibit;
use App\Models\CaseModel;
use App\Helpers\FileHelper;

class ExhibitController extends BaseController
{
    private Exhibit $exhibitModel;
    private CaseModel $caseModel;
    
    public function __construct()
    {
        $this->exhibitModel = new Exhibit();
        $this->caseModel = new CaseModel();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $caseId = $_GET['case_id'] ?? null;
        
        if ($caseId) {
            $exhibits = $this->exhibitModel->getByCaseId($caseId);
            $case = $this->caseModel->find($caseId);
        } else {
            $exhibits = $status 
                ? $this->exhibitModel->getByStatus($status)
                : $this->exhibitModel->query("
                    SELECT 
                        e.*,
                        c.case_number,
                        CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as seized_by_name
                    FROM exhibits e
                    JOIN cases c ON e.case_id = c.id
                    JOIN officers o ON e.seized_by = o.id
                    ORDER BY e.seized_date DESC
                    LIMIT 100
                ");
            $case = null;
        }
        
        return $this->view('exhibits/index', [
            'title' => 'Exhibits',
            'exhibits' => $exhibits,
            'case' => $case,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $exhibit = $this->exhibitModel->getWithDetails($id);
        
        if (!$exhibit) {
            $this->setFlash('error', 'Exhibit not found');
            $this->redirect('/exhibits');
        }
        
        $movements = $this->exhibitModel->getMovementHistory($id);
        
        return $this->view('exhibits/view', [
            'title' => 'Exhibit Details',
            'exhibit' => $exhibit,
            'movements' => $movements
        ]);
    }
    
    public function create(): string
    {
        $caseId = $_GET['case_id'] ?? null;
        
        if (!$caseId) {
            $this->setFlash('error', 'Case ID is required');
            $this->redirect('/cases');
        }
        
        $case = $this->caseModel->find($caseId);
        
        $officers = $this->exhibitModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('exhibits/create', [
            'title' => 'Register Exhibit',
            'case' => $case,
            'officers' => $officers
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'case_id' => 'required',
            'exhibit_type' => 'required',
            'description' => 'required',
            'seized_date' => 'required',
            'seized_by' => 'required',
            'current_location' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $exhibitNumber = 'EXH-' . $_POST['case_id'] . '-' . time();
            
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = FileHelper::uploadFile($_FILES['photo'], 'exhibits');
            }
            
            $exhibitId = $this->exhibitModel->create([
                'exhibit_number' => $exhibitNumber,
                'case_id' => $_POST['case_id'],
                'exhibit_type' => $_POST['exhibit_type'],
                'description' => $_POST['description'],
                'quantity' => $_POST['quantity'] ?? 1,
                'seized_from' => $_POST['seized_from'] ?? null,
                'seized_location' => $_POST['seized_location'] ?? null,
                'seized_date' => $_POST['seized_date'],
                'seized_by' => $_POST['seized_by'],
                'current_location' => $_POST['current_location'],
                'storage_condition' => $_POST['storage_condition'] ?? null,
                'exhibit_status' => 'In Custody',
                'photo_path' => $photoPath,
                'remarks' => $_POST['remarks'] ?? null
            ]);
            
            logger("Exhibit registered: ID {$exhibitId} for case {$_POST['case_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Exhibit registered successfully',
                'exhibit_id' => $exhibitId,
                'exhibit_number' => $exhibitNumber
            ]);
        } catch (\Exception $e) {
            logger("Error registering exhibit: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to register exhibit'], 500);
        }
    }
    
    public function move(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'moved_to' => 'required',
            'moved_by' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $exhibit = $this->exhibitModel->find($id);
            
            $this->exhibitModel->recordMovement($id, [
                'moved_from' => $exhibit['current_location'],
                'moved_to' => $_POST['moved_to'],
                'moved_by' => $_POST['moved_by'],
                'received_by' => $_POST['received_by'] ?? null,
                'movement_date' => $_POST['movement_date'] ?? date('Y-m-d H:i:s'),
                'purpose' => $_POST['purpose'] ?? null,
                'condition_notes' => $_POST['condition_notes'] ?? null
            ]);
            
            $this->json(['success' => true, 'message' => 'Exhibit moved successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to move exhibit'], 500);
        }
    }
    
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        $remarks = $_POST['remarks'] ?? null;
        
        if (empty($status)) {
            $this->json(['success' => false, 'message' => 'Status is required'], 422);
        }
        
        try {
            $this->exhibitModel->updateStatus($id, $status, $remarks);
            $this->json(['success' => true, 'message' => 'Exhibit status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update exhibit status'], 500);
        }
    }
}
