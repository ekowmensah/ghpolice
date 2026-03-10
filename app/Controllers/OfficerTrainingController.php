<?php

namespace App\Controllers;

use App\Models\OfficerTraining;
use App\Models\Officer;

class OfficerTrainingController extends BaseController
{
    private OfficerTraining $trainingModel;
    private Officer $officerModel;
    
    public function __construct()
    {
        $this->trainingModel = new OfficerTraining();
        $this->officerModel = new Officer();
    }
    
    public function index(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        $type = $_GET['type'] ?? null;
        
        if ($officerId) {
            $trainings = $this->trainingModel->getByOfficerId($officerId);
            $officer = $this->officerModel->query("
                SELECT 
                    o.*,
                    pr.rank_name
                FROM officers o
                JOIN police_ranks pr ON o.rank_id = pr.id
                WHERE o.id = ?
            ", [$officerId]);
            $officer = $officer[0] ?? null;
        } elseif ($type) {
            $trainings = $this->trainingModel->getByType($type);
            $officer = null;
        } else {
            $trainings = $this->trainingModel->query("
                SELECT 
                    ot.*,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                    pr.rank_name,
                    o.service_number
                FROM officer_training ot
                JOIN officers o ON ot.officer_id = o.id
                JOIN police_ranks pr ON o.rank_id = pr.id
                ORDER BY ot.start_date DESC
                LIMIT 100
            ");
            $officer = null;
        }
        
        return $this->view('officers/training/index', [
            'title' => 'Officer Training',
            'trainings' => $trainings,
            'officer' => $officer,
            'selected_type' => $type
        ]);
    }
    
    public function upcoming(): string
    {
        $trainings = $this->trainingModel->getUpcomingTrainings();
        
        return $this->view('officers/training/upcoming', [
            'title' => 'Upcoming Training',
            'trainings' => $trainings
        ]);
    }
    
    public function create(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('officers/training/create', [
            'title' => 'Record Training',
            'officers' => $officers,
            'selected_officer_id' => $officerId
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'officer_id' => 'required',
            'training_name' => 'required',
            'training_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $trainingId = $this->trainingModel->create([
                'officer_id' => $_POST['officer_id'],
                'training_name' => $_POST['training_name'],
                'training_type' => $_POST['training_type'],
                'training_institution' => $_POST['training_institution'] ?? null,
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'certificate_number' => $_POST['certificate_number'] ?? null,
                'grade_score' => $_POST['grade_score'] ?? null,
                'remarks' => $_POST['remarks'] ?? null
            ]);
            
            logger("Training recorded: ID {$trainingId} for officer {$_POST['officer_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Training recorded successfully',
                'training_id' => $trainingId
            ]);
        } catch (\Exception $e) {
            logger("Error recording training: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to record training'], 500);
        }
    }
}
