<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Config\Database;
use PDO;

class InvestigationService
{
    private PDO $db;
    private CaseService $caseService;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->caseService = new CaseService();
    }
    
    /**
     * Get complete investigation details for a case
     */
    public function getInvestigationDetails(int $caseId): array
    {
        return [
            'checklist' => $this->getChecklist($caseId),
            'tasks' => $this->getTasks($caseId),
            'timeline' => $this->getTimeline($caseId),
            'milestones' => $this->getMilestones($caseId)
        ];
    }
    
    /**
     * Get investigation checklist
     */
    private function getChecklist(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cic.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as completed_by_name
            FROM case_investigation_checklist cic
            LEFT JOIN users u ON cic.completed_by = u.id
            WHERE cic.case_id = ?
            ORDER BY cic.item_order
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get investigation tasks
     */
    private function getTasks(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cit.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as assigned_to_name,
                pr.rank_name as assigned_to_rank,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as assigned_by_name
            FROM case_investigation_tasks cit
            LEFT JOIN officers o ON cit.assigned_to = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN users u ON cit.assigned_by = u.id
            WHERE cit.case_id = ?
            ORDER BY 
                CASE cit.priority 
                    WHEN 'Urgent' THEN 1
                    WHEN 'High' THEN 2 
                    WHEN 'Medium' THEN 3 
                    WHEN 'Low' THEN 4 
                END,
                cit.due_date ASC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get investigation timeline
     */
    private function getTimeline(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cit.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name
            FROM case_investigation_timeline cit
            LEFT JOIN users u ON cit.recorded_by = u.id
            WHERE cit.case_id = ?
            ORDER BY cit.event_date DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get investigation milestones
     */
    private function getMilestones(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cm.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as created_by_name
            FROM case_milestones cm
            LEFT JOIN users u ON cm.created_by = u.id
            WHERE cm.case_id = ?
            ORDER BY cm.target_date ASC, cm.created_at DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create investigation task
     */
    public function createTask(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_investigation_tasks (
                case_id, task_title, task_description, task_type, assigned_to,
                assigned_by, priority, due_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['task_title'],
            $data['task_description'] ?? '',
            $data['task_type'] ?? 'Other',
            $data['assigned_to'] ?? null,
            $data['assigned_by'] ?? auth_id(),
            $data['priority'] ?? 'Medium',
            $data['due_date'] ?? null
        ]);
        
        $taskId = (int)$this->db->lastInsertId();
        
        logger("Investigation task created for case {$data['case_id']}: Task ID {$taskId}");
        
        return $taskId;
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId, string $status, string $notes, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE case_investigation_tasks
            SET status = ?, 
                completion_notes = ?,
                completed_at = CASE WHEN ? = 'Completed' THEN NOW() ELSE completed_at END,
                completed_by = CASE WHEN ? = 'Completed' THEN ? ELSE completed_by END
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$status, $notes, $status, $status, $userId, $taskId]);
        
        if ($result) {
            logger("Investigation task {$taskId} status updated to: {$status}");
        }
        
        return $result;
    }
    
    /**
     * Update checklist item
     */
    public function updateChecklistItem(int $itemId, bool $completed, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE case_investigation_checklist
            SET is_completed = ?,
                completed_at = CASE WHEN ? = 1 THEN NOW() ELSE NULL END,
                completed_by = CASE WHEN ? = 1 THEN ? ELSE NULL END
            WHERE id = ?
        ");
        
        return $stmt->execute([$completed, $completed, $completed, $userId, $itemId]);
    }
    
    /**
     * Create milestone
     */
    public function createMilestone(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_milestones (
                case_id, milestone_title, milestone_description,
                target_date, created_by
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['case_id'],
            $data['milestone_title'],
            $data['milestone_description'] ?? '',
            $data['target_date'] ?? null,
            $data['created_by'] ?? auth_id()
        ]);
        
        $milestoneId = (int)$this->db->lastInsertId();
        
        logger("Investigation milestone created for case {$data['case_id']}: Milestone ID {$milestoneId}");
        
        return $milestoneId;
    }
    
    /**
     * Add timeline entry
     */
    public function addTimelineEntry(int $caseId, string $eventType, string $description, int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_investigation_timeline (
                case_id, activity_type, activity_title, activity_description, 
                activity_date, completed_by, is_milestone
            ) VALUES (?, ?, ?, ?, NOW(), ?, 0)
        ");
        
        $stmt->execute([$caseId, $eventType, $eventType, $description, $userId]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Initialize default checklist for a case
     */
    public function initializeChecklist(int $caseId): void
    {
        $defaultItems = [
            ['Initial complaint recorded', 1],
            ['Scene visited and documented', 2],
            ['Witnesses identified', 3],
            ['Statements recorded', 4],
            ['Evidence collected', 5],
            ['Suspects identified', 6],
            ['Forensic analysis requested', 7],
            ['Investigation report prepared', 8],
            ['Case file reviewed', 9],
            ['Prosecution recommendation made', 10]
        ];
        
        $stmt = $this->db->prepare("
            INSERT INTO case_investigation_checklist (
                case_id, item_description, item_order
            ) VALUES (?, ?, ?)
        ");
        
        foreach ($defaultItems as $item) {
            $stmt->execute([$caseId, $item[0], $item[1]]);
        }
        
        logger("Investigation checklist initialized for case {$caseId}");
    }
    
    // ==================== PHASE 1 INTEGRATION METHODS ====================
    
    /**
     * Get investigation timeline using Phase 1 method
     */
    public function getCaseTimeline(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getTimeline($case_id);
    }
    
    /**
     * Get investigation tasks using Phase 1 method
     */
    public function getCaseTasks(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getTasks($case_id);
    }
    
    /**
     * Get case updates using Phase 1 method
     */
    public function getCaseUpdates(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getUpdates($case_id);
    }
    
    /**
     * Get case statements using Phase 1 method
     */
    public function getCaseStatements(int $case_id): array
    {
        $caseModel = new CaseModel();
        return $caseModel->getStatements($case_id);
    }
    
    /**
     * Get combined timeline (case updates + investigation timeline + status changes)
     */
    public function getCombinedTimeline(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                'investigation' as source,
                cit.id,
                cit.event_type as type,
                cit.activity_title as title,
                cit.activity_description as description,
                cit.activity_date as date,
                cit.location,
                cit.outcome,
                cit.is_milestone,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as user_name
            FROM case_investigation_timeline cit
            LEFT JOIN users u ON cit.completed_by = u.id
            WHERE cit.case_id = ?
            
            UNION ALL
            
            SELECT 
                'update' as source,
                cu.id,
                'Case Update' as type,
                NULL as title,
                cu.update_note as description,
                cu.update_date as date,
                NULL as location,
                NULL as outcome,
                0 as is_milestone,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as user_name
            FROM case_updates cu
            LEFT JOIN users u ON cu.updated_by = u.id
            WHERE cu.case_id = ?
            
            UNION ALL
            
            SELECT 
                'status' as source,
                csh.id,
                'Status Change' as type,
                CONCAT('Status changed to: ', csh.new_status) as title,
                csh.remarks as description,
                csh.change_date as date,
                NULL as location,
                NULL as outcome,
                1 as is_milestone,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as user_name
            FROM case_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.case_id = ?
            
            ORDER BY date DESC
        ");
        
        $stmt->execute([$caseId, $caseId, $caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate investigation progress metrics
     */
    public function calculateProgress(int $caseId): array
    {
        // Checklist progress
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed
            FROM case_investigation_checklist
            WHERE case_id = ?
        ");
        $stmt->execute([$caseId]);
        $checklist = $stmt->fetch();
        
        // Tasks progress
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN due_date < NOW() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue
            FROM case_investigation_tasks
            WHERE case_id = ?
        ");
        $stmt->execute([$caseId]);
        $tasks = $stmt->fetch();
        
        // Milestones progress
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_achieved = 1 THEN 1 ELSE 0 END) as achieved
            FROM case_milestones
            WHERE case_id = ?
        ");
        $stmt->execute([$caseId]);
        $milestones = $stmt->fetch();
        
        // Evidence count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM evidence WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $evidence = $stmt->fetch();
        
        // Statements count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM statements WHERE case_id = ? AND status = 'active'");
        $stmt->execute([$caseId]);
        $statements = $stmt->fetch();
        
        // Suspects count
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT s.id) as count 
            FROM suspects s
            INNER JOIN case_suspects cs ON s.id = cs.suspect_id
            WHERE cs.case_id = ?
        ");
        $stmt->execute([$caseId]);
        $suspects = $stmt->fetch();
        
        // Witnesses count
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT w.id) as count 
            FROM witnesses w
            INNER JOIN case_witnesses cw ON w.id = cw.witness_id
            WHERE cw.case_id = ?
        ");
        $stmt->execute([$caseId]);
        $witnesses = $stmt->fetch();
        
        // Calculate overall progress percentage
        $checklistPercent = $checklist['total'] > 0 ? ($checklist['completed'] / $checklist['total']) * 100 : 0;
        $tasksPercent = $tasks['total'] > 0 ? ($tasks['completed'] / $tasks['total']) * 100 : 0;
        $milestonesPercent = $milestones['total'] > 0 ? ($milestones['achieved'] / $milestones['total']) * 100 : 0;
        
        $overallPercent = ($checklistPercent + $tasksPercent + $milestonesPercent) / 3;
        
        return [
            'checklist' => [
                'total' => (int)$checklist['total'],
                'completed' => (int)$checklist['completed'],
                'percent' => round($checklistPercent, 1)
            ],
            'tasks' => [
                'total' => (int)$tasks['total'],
                'completed' => (int)$tasks['completed'],
                'in_progress' => (int)$tasks['in_progress'],
                'pending' => (int)$tasks['pending'],
                'overdue' => (int)$tasks['overdue'],
                'percent' => round($tasksPercent, 1)
            ],
            'milestones' => [
                'total' => (int)$milestones['total'],
                'achieved' => (int)$milestones['achieved'],
                'percent' => round($milestonesPercent, 1)
            ],
            'evidence_count' => (int)$evidence['count'],
            'statements_count' => (int)$statements['count'],
            'suspects_count' => (int)$suspects['count'],
            'witnesses_count' => (int)$witnesses['count'],
            'overall_percent' => round($overallPercent, 1)
        ];
    }
    
    /**
     * Initialize investigation workflow for a case
     */
    public function initializeInvestigation(int $caseId, string $caseType = 'General'): void
    {
        // Initialize checklist
        $this->initializeChecklist($caseId);
        
        // Create initial tasks based on case type
        $this->createInitialTasks($caseId, $caseType);
        
        // Add timeline entry
        $this->addTimelineEntry($caseId, 'Investigation Started', 'Investigation workflow initialized', auth_id());
        
        logger("Investigation initialized for case {$caseId}");
    }
    
    /**
     * Create initial tasks based on case type
     */
    private function createInitialTasks(int $caseId, string $caseType): void
    {
        $taskTemplates = [
            'General' => [
                ['title' => 'Visit crime scene', 'type' => 'Evidence Collection', 'priority' => 'High'],
                ['title' => 'Interview complainant', 'type' => 'Interview', 'priority' => 'High'],
                ['title' => 'Identify and interview witnesses', 'type' => 'Interview', 'priority' => 'Medium'],
                ['title' => 'Collect physical evidence', 'type' => 'Evidence Collection', 'priority' => 'High'],
                ['title' => 'Review CCTV footage (if available)', 'type' => 'Document Review', 'priority' => 'Medium']
            ]
        ];
        
        $tasks = $taskTemplates[$caseType] ?? $taskTemplates['General'];
        
        foreach ($tasks as $index => $task) {
            $dueDate = date('Y-m-d', strtotime('+' . (($index + 1) * 2) . ' days'));
            
            $this->createTask([
                'case_id' => $caseId,
                'task_title' => $task['title'],
                'task_description' => '',
                'task_type' => $task['type'],
                'assigned_to' => null,
                'assigned_by' => auth_id(),
                'priority' => $task['priority'],
                'due_date' => $dueDate
            ]);
        }
    }
    
    /**
     * Get investigation stage based on checklist and task completion
     */
    public function getInvestigationStage(int $caseId): string
    {
        $progress = $this->calculateProgress($caseId);
        
        if ($progress['overall_percent'] >= 90) {
            return 'Prosecution Preparation';
        } elseif ($progress['overall_percent'] >= 70) {
            return 'Analysis & Review';
        } elseif ($progress['overall_percent'] >= 40) {
            return 'Evidence Collection';
        } elseif ($progress['overall_percent'] >= 10) {
            return 'Initial Response';
        } else {
            return 'Not Started';
        }
    }
}

