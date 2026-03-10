<?php

namespace App\Controllers;

use App\Models\OfficerDisciplinary;
use App\Models\Officer;
use App\Services\NotificationService;

/**
 * OfficerDisciplinaryController
 * 
 * Handles officer disciplinary actions
 */
class OfficerDisciplinaryController extends BaseController
{
    private $disciplinaryModel;
    private $officerModel;
    private $notificationService;

    public function __construct()
    {
        $this->disciplinaryModel = new OfficerDisciplinary();
        $this->officerModel = new Officer();
        $this->notificationService = new NotificationService();
    }

    /**
     * List disciplinary records
     */
    public function index()
    {
        $status = $_GET['status'] ?? null;
        
        if ($status) {
            $records = $this->disciplinaryModel->getByStatus($status);
        } else {
            $records = $this->disciplinaryModel->all();
        }

        return $this->view('officers/disciplinary/index', [
            'title' => 'Officer Disciplinary Records',
            'records' => $records
        ]);
    }

    /**
     * Show disciplinary record details
     */
    public function show($id)
    {
        $record = $this->disciplinaryModel->find($id);
        
        if (!$record) {
            $this->setFlash('error', 'Disciplinary record not found');
            return $this->redirect('/officers/disciplinary');
        }

        return $this->view('officers/disciplinary/view', [
            'title' => 'Disciplinary Record Details',
            'record' => $record
        ]);
    }

    /**
     * Create disciplinary record form
     */
    public function create()
    {
        $officerId = $_GET['officer_id'] ?? null;
        $officer = $officerId ? $this->officerModel->find($officerId) : null;
        $officers = $this->officerModel->all();

        return $this->view('officers/disciplinary/create', [
            'title' => 'Record Disciplinary Action',
            'officer' => $officer,
            'officers' => $officers
        ]);
    }

    /**
     * Store disciplinary record
     */
    public function store()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = $this->validate($_POST, [
            'officer_id' => 'required|integer',
            'offence_type' => 'required',
            'offence_description' => 'required',
            'action_taken' => 'required',
            'incident_date' => 'required|date'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
        }

        try {
            $data = [
                'officer_id' => $_POST['officer_id'],
                'offence_type' => $_POST['offence_type'],
                'offence_description' => $_POST['offence_description'],
                'action_taken' => $_POST['action_taken'],
                'incident_date' => $_POST['incident_date'],
                'action_date' => $_POST['action_date'] ?? date('Y-m-d'),
                'duration_days' => $_POST['duration_days'] ?? null,
                'status' => 'Active',
                'recorded_by' => $_SESSION['user_id']
            ];

            $recordId = $this->disciplinaryModel->create($data);

            // Send notification
            $this->notificationService->send(
                $_POST['officer_id'],
                null,
                'Disciplinary Action',
                'A disciplinary action has been recorded against you'
            );

            // Audit log
            audit_log('CREATE', 'officers', 'Officer Disciplinary', $recordId, 
                     "Disciplinary action recorded for officer ID: {$_POST['officer_id']}");

            return $this->json([
                'success' => true,
                'message' => 'Disciplinary record created successfully',
                'record_id' => $recordId
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to create record: ' . $e->getMessage()]);
        }
    }

    /**
     * Update disciplinary status
     */
    public function updateStatus($id)
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $status = $_POST['status'] ?? null;

        if (!$status) {
            return $this->json(['success' => false, 'message' => 'Status is required']);
        }

        try {
            $this->disciplinaryModel->update($id, ['status' => $status]);

            audit_log('UPDATE', 'officers', 'Officer Disciplinary', $id, 
                     "Disciplinary status updated to: {$status}");

            return $this->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to update status']);
        }
    }
}
