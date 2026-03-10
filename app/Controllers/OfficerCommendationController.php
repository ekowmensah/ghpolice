<?php

namespace App\Controllers;

use App\Models\OfficerCommendation;
use App\Models\Officer;
use App\Services\NotificationService;

/**
 * OfficerCommendationController
 * 
 * Handles officer commendations and awards
 */
class OfficerCommendationController extends BaseController
{
    private $commendationModel;
    private $officerModel;
    private $notificationService;

    public function __construct()
    {
        $this->commendationModel = new OfficerCommendation();
        $this->officerModel = new Officer();
        $this->notificationService = new NotificationService();
    }

    /**
     * List commendations
     */
    public function index()
    {
        $type = $_GET['type'] ?? null;
        
        if ($type) {
            $commendations = $this->commendationModel->getByType($type);
        } else {
            $commendations = $this->commendationModel->all();
        }

        return $this->view('officers/commendations/index', [
            'title' => 'Officer Commendations',
            'commendations' => $commendations
        ]);
    }

    /**
     * Show commendation details
     */
    public function show($id)
    {
        $commendation = $this->commendationModel->find($id);
        
        if (!$commendation) {
            $this->setFlash('error', 'Commendation not found');
            return $this->redirect('/officers/commendations');
        }

        return $this->view('officers/commendations/view', [
            'title' => 'Commendation Details',
            'commendation' => $commendation
        ]);
    }

    /**
     * Create commendation form
     */
    public function create()
    {
        $officerId = $_GET['officer_id'] ?? null;
        $officer = $officerId ? $this->officerModel->find($officerId) : null;
        $officers = $this->officerModel->all();

        return $this->view('officers/commendations/create', [
            'title' => 'Record Commendation',
            'officer' => $officer,
            'officers' => $officers
        ]);
    }

    /**
     * Store commendation
     */
    public function store()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = $this->validate($_POST, [
            'officer_id' => 'required|integer',
            'commendation_type' => 'required',
            'commendation_title' => 'required',
            'commendation_date' => 'required|date'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
        }

        try {
            $data = [
                'officer_id' => $_POST['officer_id'],
                'commendation_type' => $_POST['commendation_type'],
                'commendation_title' => $_POST['commendation_title'],
                'description' => $_POST['description'] ?? null,
                'commendation_date' => $_POST['commendation_date'],
                'awarded_by' => $_POST['awarded_by'] ?? null,
                'certificate_number' => $_POST['certificate_number'] ?? null,
                'recorded_by' => $_SESSION['user_id']
            ];

            $commendationId = $this->commendationModel->create($data);

            // Send notification
            $this->notificationService->send(
                $_POST['officer_id'],
                null,
                'Commendation Awarded',
                "You have been awarded: {$_POST['commendation_title']}"
            );

            // Audit log
            audit_log('CREATE', 'officers', 'Officer Commendation', $commendationId, 
                     "Commendation recorded for officer ID: {$_POST['officer_id']}");

            return $this->json([
                'success' => true,
                'message' => 'Commendation recorded successfully',
                'commendation_id' => $commendationId
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to record commendation: ' . $e->getMessage()]);
        }
    }
}
