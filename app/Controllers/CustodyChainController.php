<?php

namespace App\Controllers;

use App\Models\CustodyChain;
use App\Models\Evidence;
use App\Models\Officer;

/**
 * CustodyChainController
 * 
 * Handles evidence custody chain tracking
 */
class CustodyChainController extends BaseController
{
    private $custodyChainModel;
    private $evidenceModel;
    private $officerModel;

    public function __construct()
    {
        $this->custodyChainModel = new CustodyChain();
        $this->evidenceModel = new Evidence();
        $this->officerModel = new Officer();
    }

    /**
     * List all custody chains
     */
    public function listAll(): string
    {
        $allEvidence = $this->evidenceModel->all();

        return $this->view('custody/list', [
            'title' => 'Custody Chain Management',
            'evidence' => $allEvidence
        ]);
    }

    /**
     * View custody chain for evidence
     */
    public function index(): string
    {
        $evidenceId = $_GET['evidence_id'] ?? null;
        
        if (!$evidenceId) {
            $this->setFlash('error', 'Evidence ID is required');
            return $this->redirect('/evidence');
        }

        $evidence = $this->evidenceModel->find((int)$evidenceId);
        if (!$evidence) {
            $this->setFlash('error', 'Evidence not found');
            return $this->redirect('/evidence');
        }
        $chain = $this->custodyChainModel->getByEvidence($evidenceId);
        $currentHolder = $this->custodyChainModel->getCurrentHolder($evidenceId);

        return $this->view('evidence/custody-chain', [
            'title' => 'Evidence Custody Chain',
            'evidence' => $evidence,
            'chain' => $chain,
            'current_holder' => $currentHolder
        ]);
    }

    /**
     * Record custody transfer form
     */
    public function create(): string
    {
        $evidenceId = $_GET['evidence_id'] ?? null;
        
        if (!$evidenceId) {
            $this->setFlash('error', 'Evidence ID is required');
            return $this->redirect('/evidence');
        }

        $evidence = $this->evidenceModel->find($evidenceId);
        $officers = $this->officerModel->all();
        $currentHolder = $this->custodyChainModel->getCurrentHolder($evidenceId);

        return $this->view('evidence/custody-transfer', [
            'title' => 'Record Custody Transfer',
            'evidence' => $evidence,
            'officers' => $officers,
            'current_holder' => $currentHolder
        ]);
    }

    /**
     * Store custody transfer
     */
    public function store()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = $this->validate($_POST, [
            'evidence_id' => 'required|integer',
            'transferred_from' => 'required|integer',
            'transferred_to' => 'required|integer',
            'transfer_date' => 'required|datetime'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
        }

        try {
            $data = [
                'evidence_id' => $_POST['evidence_id'],
                'transferred_from' => $_POST['transferred_from'],
                'transferred_to' => $_POST['transferred_to'],
                'transfer_date' => $_POST['transfer_date'],
                'purpose' => $_POST['purpose'] ?? null,
                'location' => $_POST['location'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];

            $transferId = $this->custodyChainModel->recordTransfer($data);

            // Audit log
            audit_log('CREATE', 'evidence', 'Custody Transfer', $transferId, 
                     "Evidence custody transferred from officer {$_POST['transferred_from']} to {$_POST['transferred_to']}");

            return $this->json([
                'success' => true,
                'message' => 'Custody transfer recorded successfully',
                'transfer_id' => $transferId
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to record transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Get custody chain (AJAX)
     */
    public function getChain($evidenceId)
    {
        $chain = $this->custodyChainModel->getByEvidence($evidenceId);
        $currentHolder = $this->custodyChainModel->getCurrentHolder($evidenceId);

        return $this->json([
            'success' => true,
            'chain' => $chain,
            'current_holder' => $currentHolder
        ]);
    }
}
