<?php

namespace App\Controllers;

use App\Models\OfficerBiometric;
use App\Models\Officer;
use App\Helpers\FileHelper;

/**
 * OfficerBiometricController
 * 
 * Handles officer biometric data management
 */
class OfficerBiometricController extends BaseController
{
    private $biometricModel;
    private $officerModel;

    public function __construct()
    {
        $this->biometricModel = new OfficerBiometric();
        $this->officerModel = new Officer();
    }

    /**
     * List biometric records
     */
    public function index()
    {
        $officerId = $_GET['officer_id'] ?? null;
        
        if ($officerId) {
            $biometrics = $this->biometricModel->getByOfficer($officerId);
            $officer = $this->officerModel->find($officerId);
        } else {
            $biometrics = [];
            $officer = null;
        }

        $this->view('officers/biometrics/index', [
            'title' => 'Officer Biometrics',
            'biometrics' => $biometrics,
            'officer' => $officer
        ]);
    }

    /**
     * Capture biometric form
     */
    public function create()
    {
        $officerId = $_GET['officer_id'] ?? null;
        $officer = $officerId ? $this->officerModel->find($officerId) : null;
        $officers = $this->officerModel->all();

        $this->view('officers/biometrics/create', [
            'title' => 'Capture Biometric',
            'officer' => $officer,
            'officers' => $officers
        ]);
    }

    /**
     * Store biometric data
     */
    public function store()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = $this->validate($_POST, [
            'officer_id' => 'required|integer',
            'biometric_type' => 'required'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
        }

        // Validate file upload
        if (!isset($_FILES['biometric_file']) || $_FILES['biometric_file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['success' => false, 'message' => 'Biometric file is required']);
        }

        try {
            // Upload file
            $uploadPath = 'uploads/biometrics/officers/';
            $fileName = FileHelper::upload($_FILES['biometric_file'], $uploadPath);

            if (!$fileName) {
                return $this->json(['success' => false, 'message' => 'Failed to upload biometric file']);
            }

            $data = [
                'officer_id' => $_POST['officer_id'],
                'biometric_type' => $_POST['biometric_type'],
                'file_path' => $uploadPath . $fileName,
                'captured_by' => $_SESSION['user_id'],
                'captured_date' => date('Y-m-d H:i:s')
            ];

            $biometricId = $this->biometricModel->register($data);

            // Audit log
            audit_log('CREATE', 'officers', 'Officer Biometric', $biometricId, 
                     "Biometric captured for officer ID: {$_POST['officer_id']}, Type: {$_POST['biometric_type']}");

            return $this->json([
                'success' => true,
                'message' => 'Biometric captured successfully',
                'biometric_id' => $biometricId
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to capture biometric: ' . $e->getMessage()]);
        }
    }

    /**
     * Check biometric status
     */
    public function checkStatus($officerId)
    {
        $biometrics = $this->biometricModel->getByOfficer($officerId);
        
        $status = [
            'Fingerprint' => false,
            'Face' => false,
            'Iris' => false,
            'Palm Print' => false,
            'Voice' => false
        ];

        foreach ($biometrics as $biometric) {
            $status[$biometric['biometric_type']] = true;
        }

        return $this->json([
            'success' => true,
            'status' => $status,
            'total' => count($biometrics)
        ]);
    }
}
