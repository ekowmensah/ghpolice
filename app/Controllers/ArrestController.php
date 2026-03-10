<?php

namespace App\Controllers;

use App\Models\Arrest;
use App\Models\Suspect;
use App\Models\CaseModel;
use App\Services\NotificationService;

class ArrestController extends BaseController
{
    private Arrest $arrestModel;
    private Suspect $suspectModel;
    private CaseModel $caseModel;
    private NotificationService $notificationService;
    
    public function __construct()
    {
        $this->arrestModel = new Arrest();
        $this->suspectModel = new Suspect();
        $this->caseModel = new CaseModel();
        $this->notificationService = new NotificationService();
    }
    
    public function index(): string
    {
        $arrests = $this->arrestModel->query("
            SELECT 
                a.*,
                c.case_number,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name,
                pr.rank_name,
                s.station_name
            FROM arrests a
            JOIN cases c ON a.case_id = c.id
            JOIN suspects su ON a.suspect_id = su.id
            JOIN persons p ON su.person_id = p.id
            JOIN officers o ON a.arresting_officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            JOIN stations s ON c.station_id = s.id
            ORDER BY a.arrest_date DESC
            LIMIT 100
        ");
        
        return $this->view('arrests/index', [
            'title' => 'Arrest Records',
            'arrests' => $arrests
        ]);
    }
    
    public function show(int $id): string
    {
        $arrest = $this->arrestModel->getWithDetails($id);
        
        if (!$arrest) {
            $this->setFlash('error', 'Arrest record not found');
            $this->redirect('/arrests');
        }
        
        return $this->view('arrests/view', [
            'title' => 'Arrest Details',
            'arrest' => $arrest
        ]);
    }
    
    public function create(): string
    {
        $caseId = $_GET['case_id'] ?? null;
        $suspectId = $_GET['suspect_id'] ?? null;
        
        if (!$caseId) {
            $this->setFlash('error', 'Case ID is required');
            $this->redirect('/cases');
        }
        
        $case = $this->caseModel->find($caseId);
        
        // Get suspects for this case
        $suspects = $this->caseModel->getSuspects($caseId);
        
        // If suspect_id provided, get specific suspect details
        $suspect = null;
        if ($suspectId) {
            $suspect = $this->suspectModel->getWithPerson($suspectId);
        }
        
        $officers = $this->arrestModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('arrests/create', [
            'title' => 'Record Arrest',
            'case' => $case,
            'suspect' => $suspect,
            'suspects' => $suspects,
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
            'suspect_id' => 'required',
            'arresting_officer_id' => 'required',
            'arrest_date' => 'required',
            'arrest_location' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $arrestId = $this->arrestModel->recordArrest([
                'case_id' => $_POST['case_id'],
                'suspect_id' => $_POST['suspect_id'],
                'arresting_officer_id' => $_POST['arresting_officer_id'],
                'arrest_date' => $_POST['arrest_date'],
                'arrest_location' => $_POST['arrest_location'],
                'reason' => $_POST['reason'] ?? null,
                'warrant_number' => $_POST['warrant_number'] ?? null,
                'arrest_type' => $_POST['arrest_type'] ?? 'Without Warrant'
            ]);
            
            // TODO: Implement notifyCaseTeam method in NotificationService
            // $this->notificationService->notifyCaseTeam(
            //     $_POST['case_id'],
            //     'Suspect Arrested',
            //     'A suspect has been arrested in this case'
            // );
            
            logger("Arrest recorded: ID {$arrestId} for case {$_POST['case_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Arrest recorded successfully',
                'arrest_id' => $arrestId
            ]);
        } catch (\Exception $e) {
            logger("Error recording arrest: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to record arrest'], 500);
        }
    }
    
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'arrest_date' => 'required',
            'arrest_location' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $this->arrestModel->update($id, [
                'arrest_date' => $_POST['arrest_date'],
                'arrest_location' => $_POST['arrest_location'],
                'reason' => $_POST['reason'] ?? null,
                'warrant_number' => $_POST['warrant_number'] ?? null,
                'arrest_type' => $_POST['arrest_type']
            ]);
            
            $this->json(['success' => true, 'message' => 'Arrest record updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update arrest record'], 500);
        }
    }
}
