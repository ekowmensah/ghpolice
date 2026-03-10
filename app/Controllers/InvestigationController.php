<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Services\InvestigationService;
use App\Services\CaseService;

class InvestigationController extends BaseController
{
    private CaseModel $caseModel;
    private InvestigationService $investigationService;
    private CaseService $caseService;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->investigationService = new InvestigationService();
        $this->caseService = new CaseService();
    }
    
    /**
     * List all active investigations
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? 'Under Investigation';
        
        // Get cases that are under investigation
        $investigations = $this->caseModel->where('status', $status);
        
        return $this->view('investigations/index', [
            'title' => 'Active Investigations',
            'investigations' => $investigations,
            'selected_status' => $status
        ]);
    }
    
    /**
     * Show investigation dashboard for a case
     * Enhanced with full case integration
     */
    public function show(int $caseId): string
    {
        // Get full case details using CaseService
        $fullCase = $this->caseService->getCaseFullDetails($caseId);
        
        if (!$fullCase) {
            $this->setFlash('error', 'Case not found');
            $this->redirect('/cases');
        }
        
        // Get complainant details
        $complainant = $fullCase['complainant_id'] 
            ? $this->caseService->getComplainantDetails($fullCase['complainant_id']) 
            : null;
        
        // Get investigation-specific data
        $investigation = $this->investigationService->getInvestigationDetails($caseId);
        
        // Get combined timeline (case updates + investigation timeline)
        $timeline = $this->investigationService->getCombinedTimeline($caseId);
        
        // Calculate investigation progress
        $progress = $this->investigationService->calculateProgress($caseId);
        
        return $this->view('investigations/dashboard', [
            'title' => 'Investigation - ' . $fullCase['case_number'],
            'case' => $fullCase,
            'complainant' => $complainant,
            'suspects' => $fullCase['suspects'] ?? [],
            'witnesses' => $fullCase['witnesses'] ?? [],
            'evidence' => $fullCase['evidence'] ?? [],
            'statements' => $fullCase['statements'] ?? [],
            'checklist' => $investigation['checklist'],
            'tasks' => $investigation['tasks'],
            'timeline' => $timeline,
            'milestones' => $investigation['milestones'],
            'progress' => $progress,
            'assigned_officers' => $fullCase['assigned_officers'] ?? []
        ]);
    }
    
    /**
     * Add investigation task
     */
    public function addTask(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/investigations/' . $caseId);
        }
        
        // Handle empty assigned_to - convert to null if empty string
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        
        $data = [
            'case_id' => $caseId,
            'task_title' => $_POST['task_title'] ?? '',
            'task_description' => $_POST['task_description'] ?? '',
            'task_type' => $_POST['task_type'] ?? 'Other',
            'assigned_to' => $assignedTo,
            'assigned_by' => auth_id(),
            'priority' => $_POST['priority'] ?? 'Medium',
            'due_date' => $_POST['due_date'] ?? null
        ];
        
        try {
            $taskId = $this->investigationService->createTask($data);
            $this->setFlash('success', 'Task created successfully');
            $this->redirect('/investigations/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create task: ' . $e->getMessage());
            $this->redirect('/investigations/' . $caseId);
        }
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $addTimelineEntry = !empty($_POST['add_timeline_entry']) && $_POST['add_timeline_entry'] === '1';
        
        try {
            // Get task details before updating
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("SELECT case_id, task_title FROM case_investigation_tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task) {
                $this->json(['success' => false, 'message' => 'Task not found'], 404);
                return;
            }
            
            // Update task status
            $this->investigationService->updateTaskStatus($taskId, $status, $notes, auth_id());
            
            // Add timeline entry if requested (usually for completed tasks)
            if ($addTimelineEntry && $task) {
                $timelineDescription = "Task completed: {$task['task_title']}";
                if ($notes) {
                    $timelineDescription .= " - " . $notes;
                }
                
                $this->investigationService->addTimelineEntry(
                    $task['case_id'],
                    'Task ' . $status,
                    $timelineDescription,
                    auth_id()
                );
            }
            
            $this->json(['success' => true, 'message' => 'Task status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update checklist item
     */
    public function updateChecklistItem(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $itemId = $_POST['item_id'] ?? null;
        $completed = isset($_POST['completed']) && $_POST['completed'] === 'true';
        
        try {
            $this->investigationService->updateChecklistItem($itemId, $completed, auth_id());
            $this->json(['success' => true, 'message' => 'Checklist updated']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Add milestone
     */
    public function addMilestone(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/investigations/' . $caseId);
        }
        
        $data = [
            'case_id' => $caseId,
            'milestone_title' => $_POST['milestone_title'] ?? '',
            'milestone_description' => $_POST['milestone_description'] ?? '',
            'target_date' => $_POST['target_date'] ?? null,
            'created_by' => auth_id()
        ];
        
        try {
            $this->investigationService->createMilestone($data);
            $this->setFlash('success', 'Milestone added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add milestone: ' . $e->getMessage());
        }
        
        $this->redirect('/investigations/' . $caseId);
    }
    
    /**
     * Initialize investigation workflow for a case
     */
    public function initializeWorkflow(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $case = $this->caseModel->find($caseId);
            
            if (!$case) {
                $this->setFlash('error', 'Case not found');
                $this->redirect('/cases');
            }
            
            // Initialize investigation workflow
            $this->investigationService->initializeInvestigation($caseId, $case['case_type'] ?? 'General');
            
            // Update case status to Under Investigation if not already
            if ($case['status'] !== 'Under Investigation') {
                $this->caseModel->update($caseId, ['status' => 'Under Investigation']);
            }
            
            $this->setFlash('success', 'Investigation workflow initialized successfully');
            $this->redirect('/investigations/' . $caseId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to initialize investigation: ' . $e->getMessage());
            $this->redirect('/cases/' . $caseId);
        }
    }
    
    /**
     * Mark milestone as achieved
     */
    public function achieveMilestone(int $caseId, int $milestoneId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("
                UPDATE case_milestones 
                SET is_achieved = 1, achieved_date = NOW()
                WHERE id = ? AND case_id = ?
            ");
            $stmt->execute([$milestoneId, $caseId]);
            
            // Add timeline entry
            $stmt = $db->prepare("SELECT milestone_title FROM case_milestones WHERE id = ?");
            $stmt->execute([$milestoneId]);
            $milestone = $stmt->fetch();
            
            if ($milestone) {
                $this->investigationService->addTimelineEntry(
                    $caseId,
                    'Milestone Achieved',
                    'Milestone achieved: ' . $milestone['milestone_title'],
                    auth_id()
                );
            }
            
            $this->json(['success' => true, 'message' => 'Milestone marked as achieved']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
