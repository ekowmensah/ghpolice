<?php

namespace App\Controllers;

use App\Models\Bail;
use App\Models\Suspect;
use App\Models\CaseModel;
use App\Models\Custody;
use App\Services\NotificationService;
use App\Config\Database;
use PDO;

class BailController extends BaseController
{
    private Bail $bailModel;
    private Suspect $suspectModel;
    private CaseModel $caseModel;
    private NotificationService $notificationService;
    private PDO $db;
    
    public function __construct()
    {
        $this->bailModel = new Bail();
        $this->suspectModel = new Suspect();
        $this->caseModel = new CaseModel();
        $this->notificationService = new NotificationService();
        $this->db = Database::getConnection();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        
        $sql = "
            SELECT 
                b.*,
                c.case_number,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM bail_records b
            JOIN cases c ON b.case_id = c.id
            JOIN suspects s ON b.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON b.approved_by = u.id
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE b.bail_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY b.bail_date DESC LIMIT 100";
        
        $bails = $this->bailModel->query($sql, $params);
        
        return $this->view('bail/index', [
            'title' => 'Bail Records',
            'bails' => $bails,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $bail = $this->bailModel->getWithDetails($id);
        
        if (!$bail) {
            $this->setFlash('error', 'Bail record not found');
            $this->redirect('/bail');
        }
        
        return $this->view('bail/view', [
            'title' => 'Bail Details',
            'bail' => $bail
        ]);
    }
    
    public function create(): string
    {
        $caseId = $_GET['case_id'] ?? null;
        $suspectId = $_GET['suspect_id'] ?? null;
        
        if (!$caseId || !$suspectId) {
            $this->setFlash('error', 'Case and suspect are required');
            $this->redirect('/cases');
        }
        
        $case = $this->caseModel->find($caseId);
        $suspect = $this->suspectModel->getWithPersonDetails($suspectId);
        
        return $this->view('bail/create', [
            'title' => 'Record Bail',
            'case' => $case,
            'suspect' => $suspect
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
            'bail_status' => 'required',
            'bail_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $this->db->beginTransaction();
            
            $bailId = $this->bailModel->create([
                'case_id' => $_POST['case_id'],
                'suspect_id' => $_POST['suspect_id'],
                'bail_status' => $_POST['bail_status'],
                'bail_amount' => $_POST['bail_amount'] ?? null,
                'bail_conditions' => $_POST['bail_conditions'] ?? null,
                'bail_date' => $_POST['bail_date'],
                'approved_by' => auth_id()
            ]);
            
            // If bail is granted, automatically release from custody
            if ($_POST['bail_status'] === 'Granted') {
                // Update any active custody records to "Released" status
                $custodyModel = new Custody();
                $stmt = $this->db->prepare("
                    UPDATE custody_records 
                    SET custody_status = 'Released',
                        custody_end = NOW(),
                        released_by = ?
                    WHERE suspect_id = ? 
                    AND case_id = ? 
                    AND custody_status = 'In Custody'
                ");
                $stmt->execute([auth_id(), $_POST['suspect_id'], $_POST['case_id']]);
                
                // Notify case team
                // $this->notificationService->notifyCaseTeam(
                //     $_POST['case_id'],
                //     'Bail Granted',
                //     'Bail has been granted for a suspect in this case'
                // );
            }
            
            $this->db->commit();
            
            logger("Bail recorded: ID {$bailId} for case {$_POST['case_id']}, status: {$_POST['bail_status']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Bail record created successfully',
                'bail_id' => $bailId
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error recording bail: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to record bail: ' . $e->getMessage()], 500);
        }
    }
    
    public function revoke(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Revocation reason is required'], 422);
        }
        
        try {
            // Get bail record to find suspect
            $bail = $this->bailModel->find($id);
            if (!$bail) {
                $this->json(['success' => false, 'message' => 'Bail record not found'], 404);
                return;
            }
            
            // Revoke the bail
            $this->bailModel->revokeBail($id, auth_id(), $reason);
            
            // Update suspect status back to "Arrested"
            $suspectModel = new \App\Models\Suspect();
            $suspectModel->update($bail['suspect_id'], [
                'current_status' => 'Arrested'
            ]);
            
            logger("Bail revoked: ID {$id}, Suspect ID {$bail['suspect_id']} status changed to Arrested, Reason: {$reason}", 'info');
            
            $this->json(['success' => true, 'message' => 'Bail revoked successfully']);
        } catch (\Exception $e) {
            logger("Error revoking bail: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to revoke bail: ' . $e->getMessage()], 500);
        }
    }
    
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->bailModel->update($id, [
                'bail_amount' => $_POST['bail_amount'] ?? null,
                'bail_conditions' => $_POST['bail_conditions'] ?? null,
                'bail_date' => $_POST['bail_date']
            ]);
            
            $this->json(['success' => true, 'message' => 'Bail record updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update bail record'], 500);
        }
    }
}
