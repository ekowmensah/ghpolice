<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Services\CourtService;

class CourtController extends BaseController
{
    private CaseModel $caseModel;
    private CourtService $courtService;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->courtService = new CourtService();
    }
    
    /**
     * Show court proceedings for a case
     */
    public function index(int $caseId): string
    {
        $case = $this->caseModel->find($caseId);
        
        if (!$case) {
            $this->setFlash('error', 'Case not found');
            $this->redirect('/cases');
        }
        
        $courtData = $this->courtService->getCourtData($caseId);
        
        return $this->view('court/index', [
            'title' => 'Court Proceedings - ' . $case['case_number'],
            'case' => $case,
            'proceedings' => $courtData['proceedings'],
            'charges' => $courtData['charges'],
            'warrants' => $courtData['warrants'],
            'bail' => $courtData['bail']
        ]);
    }
    
    /**
     * Add court proceeding
     */
    public function addProceeding(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId . '/court');
        }
        
        $data = [
            'case_id' => $caseId,
            'court_name' => $_POST['court_name'] ?? '',
            'hearing_date' => $_POST['hearing_date'] ?? null,
            'hearing_type' => $_POST['hearing_type'] ?? '',
            'judge_name' => $_POST['judge_name'] ?? null,
            'outcome' => $_POST['outcome'] ?? null,
            'next_hearing_date' => $_POST['next_hearing_date'] ?? null,
            'notes' => $_POST['notes'] ?? '',
            'recorded_by' => auth_id()
        ];
        
        try {
            $this->courtService->addProceeding($data);
            $this->setFlash('success', 'Court proceeding recorded successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to record proceeding: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId . '/court');
    }
    
    /**
     * Add charges
     */
    public function addCharges(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId . '/court');
        }
        
        $data = [
            'case_id' => $caseId,
            'suspect_id' => $_POST['suspect_id'] ?? null,
            'charge_description' => $_POST['charge_description'] ?? '',
            'charge_type' => $_POST['charge_type'] ?? '',
            'statute_reference' => $_POST['statute_reference'] ?? null,
            'filed_date' => $_POST['filed_date'] ?? date('Y-m-d'),
            'filed_by' => auth_id()
        ];
        
        try {
            $this->courtService->addCharges($data);
            $this->setFlash('success', 'Charges filed successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to file charges: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId . '/court');
    }
    
    /**
     * Issue warrant
     */
    public function issueWarrant(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId . '/court');
        }
        
        $data = [
            'case_id' => $caseId,
            'suspect_id' => $_POST['suspect_id'] ?? null,
            'warrant_type' => $_POST['warrant_type'] ?? '',
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'issued_by' => $_POST['issued_by'] ?? '',
            'warrant_details' => $_POST['warrant_details'] ?? '',
            'status' => 'Active'
        ];
        
        try {
            $this->courtService->issueWarrant($data);
            $this->setFlash('success', 'Warrant issued successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to issue warrant: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId . '/court');
    }
    
    /**
     * Record bail
     */
    public function recordBail(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId . '/court');
        }
        
        $data = [
            'case_id' => $caseId,
            'suspect_id' => $_POST['suspect_id'] ?? null,
            'bail_amount' => $_POST['bail_amount'] ?? 0,
            'bail_conditions' => $_POST['bail_conditions'] ?? '',
            'granted_date' => $_POST['granted_date'] ?? date('Y-m-d'),
            'granted_by' => $_POST['granted_by'] ?? '',
            'status' => 'Granted'
        ];
        
        try {
            $this->courtService->recordBail($data);
            $this->setFlash('success', 'Bail recorded successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to record bail: ' . $e->getMessage());
        }
        
        $this->redirect('/cases/' . $caseId . '/court');
    }
}
