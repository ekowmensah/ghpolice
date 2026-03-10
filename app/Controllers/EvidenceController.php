<?php

namespace App\Controllers;

use App\Models\Evidence;
use App\Models\CaseModel;
use App\Services\EvidenceService;

class EvidenceController extends BaseController
{
    private Evidence $evidenceModel;
    private CaseModel $caseModel;
    private EvidenceService $evidenceService;
    
    public function __construct()
    {
        $this->evidenceModel = new Evidence();
        $this->caseModel = new CaseModel();
        $this->evidenceService = new EvidenceService();
    }
    
    /**
     * List all evidence
     */
    public function list(): string
    {
        $evidence = $this->evidenceModel->all();
        
        return $this->view('evidence/list', [
            'title' => 'Evidence Management',
            'evidence' => $evidence
        ]);
    }
    
    /**
     * Show evidence for a case
     */
    public function index(int $caseId): string
    {
        $case = $this->caseModel->find($caseId);
        
        if (!$case) {
            $this->setFlash('error', 'Case not found');
            $this->redirect('/cases');
        }
        
        $evidence = $this->evidenceModel->getByCaseId($caseId);
        
        return $this->view('evidence/index', [
            'title' => 'Evidence - ' . $case['case_number'],
            'case' => $case,
            'evidence' => $evidence
        ]);
    }
    
    /**
     * Add evidence to case
     */
    public function store(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId . '/evidence');
        }
        
        $data = [
            'case_id' => $caseId,
            'evidence_type' => $_POST['evidence_type'] ?? '',
            'evidence_description' => $_POST['evidence_description'] ?? '',
            'collected_by' => auth_id(),
            'collected_date' => $_POST['collected_date'] ?? date('Y-m-d H:i:s'),
            'collected_location' => $_POST['collected_location'] ?? null,
            'storage_location' => $_POST['storage_location'] ?? null,
            'status' => 'Collected'
        ];
        
        $errors = $this->validate($data, [
            'evidence_type' => 'required',
            'evidence_description' => 'required|min:10'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/cases/' . $caseId . '/evidence');
        }
        
        try {
            $evidenceId = $this->evidenceService->addEvidence($data);
            $this->setFlash('success', 'Evidence added successfully');
            $this->redirect('/evidence/' . $evidenceId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add evidence: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/cases/' . $caseId . '/evidence');
        }
    }
    
    /**
     * Show evidence details with custody chain
     */
    public function show(int $id): string
    {
        $evidence = $this->evidenceModel->find($id);
        
        if (!$evidence) {
            $this->setFlash('error', 'Evidence not found');
            $this->redirect('/cases');
        }
        
        $custodyChain = $this->evidenceModel->getCustodyChain($id);
        
        return $this->view('evidence/view', [
            'title' => 'Evidence Details',
            'evidence' => $evidence,
            'custodyChain' => $custodyChain
        ]);
    }
    
    /**
     * Transfer evidence custody
     */
    public function transferCustody(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $data = [
            'evidence_id' => $id,
            'transferred_to' => $_POST['transferred_to'] ?? null,
            'transfer_reason' => $_POST['transfer_reason'] ?? '',
            'transferred_by' => auth_id()
        ];
        
        try {
            $this->evidenceService->transferCustody($data);
            $this->json(['success' => true, 'message' => 'Custody transferred successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update evidence status
     */
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        try {
            $this->evidenceService->updateStatus($id, $status, $notes, auth_id());
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
