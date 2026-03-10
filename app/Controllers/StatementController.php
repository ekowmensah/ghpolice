<?php

namespace App\Controllers;

use App\Models\Statement;
use App\Models\CaseModel;
use App\Models\Suspect;
use App\Models\Witness;
use App\Models\Complainant;

/**
 * StatementController
 * 
 * Handles statement recording and management
 */
class StatementController extends BaseController
{
    private $statementModel;
    private $caseModel;
    private $suspectModel;
    private $witnessModel;
    private $complainantModel;

    public function __construct()
    {
        $this->statementModel = new Statement();
        $this->caseModel = new CaseModel();
        $this->suspectModel = new Suspect();
        $this->witnessModel = new Witness();
        $this->complainantModel = new Complainant();
    }

    /**
     * List statements for case
     */
    public function index()
    {
        $caseId = $_GET['case_id'] ?? null;
        
        if (!$caseId) {
            $this->setFlash('error', 'Case ID is required');
            return $this->redirect('/cases');
        }

        $case = $this->caseModel->find($caseId);
        $statements = $this->statementModel->getByCase($caseId);

        $this->view('statements/index', [
            'title' => 'Case Statements',
            'case' => $case,
            'statements' => $statements
        ]);
    }

    /**
     * Show statement details
     */
    public function show($id)
    {
        $statement = $this->statementModel->find($id);
        
        if (!$statement) {
            $this->setFlash('error', 'Statement not found');
            return $this->redirect('/cases');
        }

        $case = $this->caseModel->find($statement['case_id']);

        $this->view('statements/view', [
            'title' => 'Statement Details',
            'statement' => $statement,
            'case' => $case
        ]);
    }

    /**
     * Record statement form
     */
    public function create()
    {
        $caseId = $_GET['case_id'] ?? null;
        $statementType = $_GET['type'] ?? null;
        
        if (!$caseId) {
            $this->setFlash('error', 'Case ID is required');
            return $this->redirect('/cases');
        }

        $case = $this->caseModel->find($caseId);
        $suspects = $this->suspectModel->getByCaseId($caseId);
        $witnesses = $this->witnessModel->getByCaseId($caseId);
        $complainants = $this->complainantModel->getByCaseId($caseId);

        $this->view('statements/create', [
            'title' => 'Record Statement',
            'case' => $case,
            'statement_type' => $statementType,
            'suspects' => $suspects,
            'witnesses' => $witnesses,
            'complainants' => $complainants
        ]);
    }

    /**
     * Store statement
     */
    public function store()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = $this->validate($_POST, [
            'case_id' => 'required|integer',
            'statement_type' => 'required',
            'statement_text' => 'required'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
        }

        try {
            $data = [
                'case_id' => $_POST['case_id'],
                'statement_type' => $_POST['statement_type'],
                'statement_text' => $_POST['statement_text'],
                'suspect_id' => $_POST['suspect_id'] ?? null,
                'witness_id' => $_POST['witness_id'] ?? null,
                'complainant_id' => $_POST['complainant_id'] ?? null,
                'recorded_by' => $_SESSION['user_id'],
                'statement_date' => $_POST['statement_date'] ?? date('Y-m-d'),
                'status' => 'active',
                'version' => 1
            ];

            $statementId = $this->statementModel->create($data);

            // Audit log
            audit_log('CREATE', 'cases', 'Statement', $statementId, 
                     "Statement recorded for case ID: {$_POST['case_id']}, Type: {$_POST['statement_type']}");

            return $this->json([
                'success' => true,
                'message' => 'Statement recorded successfully',
                'statement_id' => $statementId
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to record statement: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel statement
     */
    public function cancel($id)
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $reason = $_POST['reason'] ?? null;

        if (!$reason) {
            return $this->json(['success' => false, 'message' => 'Cancellation reason is required']);
        }

        try {
            $this->statementModel->cancel($id, $_SESSION['user_id'], $reason);

            audit_log('UPDATE', 'cases', 'Statement', $id, 
                     "Statement cancelled. Reason: {$reason}");

            return $this->json([
                'success' => true,
                'message' => 'Statement cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to cancel statement']);
        }
    }

    /**
     * Get statement versions
     */
    public function versions($id)
    {
        $versions = $this->statementModel->getVersions($id);

        return $this->json([
            'success' => true,
            'versions' => $versions
        ]);
    }
}
