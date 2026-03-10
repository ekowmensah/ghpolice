<?php

namespace App\Controllers;

use App\Models\OfficerLeave;
use App\Models\Officer;
use App\Services\NotificationService;

class OfficerLeaveController extends BaseController
{
    private OfficerLeave $leaveModel;
    private Officer $officerModel;
    private NotificationService $notificationService;
    
    public function __construct()
    {
        $this->leaveModel = new OfficerLeave();
        $this->officerModel = new Officer();
        $this->notificationService = new NotificationService();
    }
    
    public function index(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if ($officerId) {
            $leaves = $this->leaveModel->getByOfficerId($officerId);
            $officer = $this->officerModel->query("
                SELECT 
                    o.*,
                    pr.rank_name
                FROM officers o
                JOIN police_ranks pr ON o.rank_id = pr.id
                WHERE o.id = ?
            ", [$officerId]);
            $officer = $officer[0] ?? null;
        } elseif ($status === 'pending') {
            $leaves = $this->leaveModel->getPendingLeaves();
            $officer = null;
        } elseif ($status === 'active') {
            $leaves = $this->leaveModel->getActiveLeaves();
            $officer = null;
        } else {
            $leaves = $this->leaveModel->query("
                SELECT 
                    olr.*,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                    pr.rank_name,
                    o.service_number,
                    s.station_name
                FROM officer_leave_records olr
                JOIN officers o ON olr.officer_id = o.id
                JOIN police_ranks pr ON o.rank_id = pr.id
                LEFT JOIN stations s ON o.current_station_id = s.id
                ORDER BY olr.created_at DESC
                LIMIT 100
            ");
            $officer = null;
        }
        
        return $this->view('officers/leave/index', [
            'title' => 'Officer Leave',
            'leaves' => $leaves,
            'officer' => $officer,
            'selected_status' => $status
        ]);
    }
    
    public function create(): string
    {
        $officerId = $_GET['officer_id'] ?? auth()['officer_id'] ?? null;
        
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('officers/leave/create', [
            'title' => 'Request Leave',
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
            'leave_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $startDate = new \DateTime($_POST['start_date']);
            $endDate = new \DateTime($_POST['end_date']);
            $totalDays = $endDate->diff($startDate)->days + 1;
            
            $leaveId = $this->leaveModel->create([
                'officer_id' => $_POST['officer_id'],
                'leave_type' => $_POST['leave_type'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'total_days' => $totalDays,
                'reason' => $_POST['reason'] ?? null,
                'leave_status' => 'Pending'
            ]);
            
            logger("Leave request submitted: ID {$leaveId} for officer {$_POST['officer_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Leave request submitted successfully',
                'leave_id' => $leaveId
            ]);
        } catch (\Exception $e) {
            logger("Error submitting leave request: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to submit leave request'], 500);
        }
    }
    
    public function approve(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->leaveModel->approveLeave($id, auth_id());
            
            $leave = $this->leaveModel->find($id);
            $this->notificationService->notifyOfficer(
                $leave['officer_id'],
                'Leave Approved',
                'Your leave request has been approved'
            );
            
            $this->json(['success' => true, 'message' => 'Leave approved successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to approve leave'], 500);
        }
    }
    
    public function reject(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Rejection reason is required'], 422);
        }
        
        try {
            $this->leaveModel->rejectLeave($id, auth_id(), $reason);
            
            $leave = $this->leaveModel->find($id);
            $this->notificationService->notifyOfficer(
                $leave['officer_id'],
                'Leave Rejected',
                'Your leave request has been rejected: ' . $reason
            );
            
            $this->json(['success' => true, 'message' => 'Leave rejected']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to reject leave'], 500);
        }
    }
}
