<?php

namespace App\Controllers;

use App\Models\Charge;
use App\Models\Suspect;
use App\Models\CaseModel;
use App\Services\NotificationService;

class ChargeController extends BaseController
{
    private Charge $chargeModel;
    private Suspect $suspectModel;
    private CaseModel $caseModel;
    private NotificationService $notificationService;
    
    public function __construct()
    {
        $this->chargeModel = new Charge();
        $this->suspectModel = new Suspect();
        $this->caseModel = new CaseModel();
        $this->notificationService = new NotificationService();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        
        $sql = "
            SELECT 
                ch.*,
                c.case_number,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as charged_by_name
            FROM charges ch
            JOIN cases c ON ch.case_id = c.id
            JOIN suspects s ON ch.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON ch.charged_by = u.id
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE ch.charge_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ch.charge_date DESC LIMIT 100";
        
        $charges = $this->chargeModel->query($sql, $params);
        
        return $this->view('charges/index', [
            'title' => 'Charges',
            'charges' => $charges,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $charge = $this->chargeModel->getWithDetails($id);
        
        if (!$charge) {
            $this->setFlash('error', 'Charge not found');
            $this->redirect('/charges');
        }
        
        return $this->view('charges/view', [
            'title' => 'Charge Details',
            'charge' => $charge
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
        
        return $this->view('charges/create', [
            'title' => 'File Charge',
            'case' => $case,
            'suspect' => $suspect,
            'suspects' => $suspects
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
            'offence_name' => 'required',
            'charge_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $chargeId = $this->chargeModel->create([
                'case_id' => $_POST['case_id'],
                'suspect_id' => $_POST['suspect_id'],
                'offence_name' => $_POST['offence_name'],
                'law_section' => $_POST['law_section'] ?? null,
                'charge_date' => $_POST['charge_date'],
                'charged_by' => auth_id(),
                'charge_status' => 'Pending'
            ]);
            
            // TODO: Implement notifyCaseTeam method in NotificationService
            // $this->notificationService->notifyCaseTeam(
            //     $_POST['case_id'],
            //     'Charge Filed',
            //     'A new charge has been filed in this case'
            // );
            
            logger("Charge filed: ID {$chargeId} for case {$_POST['case_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Charge filed successfully',
                'charge_id' => $chargeId
            ]);
        } catch (\Exception $e) {
            logger("Error filing charge: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to file charge'], 500);
        }
    }
    
    public function file(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->chargeModel->fileCharge($id, auth_id());
            
            $charge = $this->chargeModel->find($id);
            $this->notificationService->notifyCaseTeam(
                $charge['case_id'],
                'Charge Filed in Court',
                'A charge has been officially filed in court'
            );
            
            $this->json(['success' => true, 'message' => 'Charge filed in court successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to file charge in court'], 500);
        }
    }
    
    public function withdraw(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Withdrawal reason is required'], 422);
        }
        
        try {
            $this->chargeModel->withdrawCharge($id, $reason);
            
            $charge = $this->chargeModel->find($id);
            $this->notificationService->notifyCaseTeam(
                $charge['case_id'],
                'Charge Withdrawn',
                'A charge has been withdrawn from this case'
            );
            
            $this->json(['success' => true, 'message' => 'Charge withdrawn successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to withdraw charge'], 500);
        }
    }
    
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->chargeModel->update($id, [
                'offence_name' => $_POST['offence_name'],
                'law_section' => $_POST['law_section'] ?? null,
                'charge_date' => $_POST['charge_date']
            ]);
            
            $this->json(['success' => true, 'message' => 'Charge updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update charge'], 500);
        }
    }
}
