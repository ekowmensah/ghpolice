<?php

namespace App\Controllers;

use App\Models\IntelligenceBulletin;

class IntelligenceBulletinController extends BaseController
{
    private IntelligenceBulletin $bulletinModel;
    
    public function __construct()
    {
        $this->bulletinModel = new IntelligenceBulletin();
    }
    
    public function index(): string
    {
        $type = $_GET['type'] ?? null;
        $priority = $_GET['priority'] ?? null;
        
        if ($type) {
            $bulletins = $this->bulletinModel->getByType($type);
        } elseif ($priority) {
            $bulletins = $this->bulletinModel->getByPriority($priority);
        } else {
            $bulletins = $this->bulletinModel->getActive();
        }
        
        return $this->view('intelligence/bulletins/index', [
            'title' => 'Intelligence Bulletins',
            'bulletins' => $bulletins,
            'selected_type' => $type,
            'selected_priority' => $priority
        ]);
    }
    
    public function show(int $id): string
    {
        $bulletin = $this->bulletinModel->query("
            SELECT 
                ib.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
            FROM intelligence_bulletins ib
            LEFT JOIN users u ON ib.issued_by = u.id
            WHERE ib.id = ?
        ", [$id]);
        
        if (empty($bulletin)) {
            $this->setFlash('error', 'Bulletin not found');
            $this->redirect('/intelligence/bulletins');
        }
        
        return $this->view('intelligence/bulletins/view', [
            'title' => 'Bulletin Details',
            'bulletin' => $bulletin[0]
        ]);
    }
    
    public function create(): string
    {
        return $this->view('intelligence/bulletins/create', [
            'title' => 'Issue Intelligence Bulletin'
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'bulletin_type' => 'required',
            'priority' => 'required',
            'subject' => 'required',
            'bulletin_content' => 'required',
            'valid_from' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $bulletinNumber = 'IB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $bulletinId = $this->bulletinModel->create([
                'bulletin_number' => $bulletinNumber,
                'bulletin_type' => $_POST['bulletin_type'],
                'priority' => $_POST['priority'],
                'subject' => $_POST['subject'],
                'bulletin_content' => $_POST['bulletin_content'],
                'action_required' => $_POST['action_required'] ?? null,
                'valid_from' => $_POST['valid_from'],
                'valid_until' => $_POST['valid_until'] ?? null,
                'issued_by' => auth_id(),
                'target_audience' => $_POST['target_audience'] ?? 'All Stations',
                'is_public' => isset($_POST['is_public']) ? 1 : 0,
                'status' => 'Active'
            ]);
            
            logger("Intelligence bulletin issued: {$bulletinNumber}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Intelligence bulletin issued successfully',
                'bulletin_id' => $bulletinId,
                'bulletin_number' => $bulletinNumber
            ]);
        } catch (\Exception $e) {
            logger("Error issuing bulletin: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to issue bulletin'], 500);
        }
    }
    
    public function expire(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->bulletinModel->expireBulletin($id);
            $this->json(['success' => true, 'message' => 'Bulletin expired successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to expire bulletin'], 500);
        }
    }
    
    public function cancel(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Cancellation reason is required'], 422);
        }
        
        try {
            $this->bulletinModel->cancelBulletin($id, $reason);
            $this->json(['success' => true, 'message' => 'Bulletin cancelled successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to cancel bulletin'], 500);
        }
    }
}
