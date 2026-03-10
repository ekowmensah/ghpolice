<?php
ob_start();

// Helper functions for safe data handling
$safePersonName = static function (?array $record, string $fallback = 'Unknown Person'): string {
    if (!$record) return $fallback;
    $name = trim((string)($record['full_name'] ?? ''));
    if (!$name) {
        $first = trim((string)($record['first_name'] ?? ''));
        $last = trim((string)($record['last_name'] ?? ''));
        $name = trim($first . ' ' . $last);
    }
    return $name ?: $fallback;
};

// Progress metrics
$progressData = $progress ?? [
    'checklist' => ['total' => 0, 'completed' => 0, 'percent' => 0],
    'tasks' => ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'pending' => 0, 'overdue' => 0, 'percent' => 0],
    'milestones' => ['total' => 0, 'achieved' => 0, 'percent' => 0],
    'evidence_count' => 0,
    'statements_count' => 0,
    'suspects_count' => 0,
    'witnesses_count' => 0,
    'overall_percent' => 0
];

// Group tasks by status
$tasksByStatus = [
    'Pending' => [],
    'In Progress' => [],
    'Completed' => []
];
foreach ($tasks ?? [] as $task) {
    $status = $task['status'] ?? 'Pending';
    if (!isset($tasksByStatus[$status])) {
        $tasksByStatus[$status] = [];
    }
    $tasksByStatus[$status][] = $task;
}

// Determine investigation stage
$investigationStage = 'Initial Response';
if ($progressData['overall_percent'] >= 90) {
    $investigationStage = 'Prosecution Preparation';
} elseif ($progressData['overall_percent'] >= 70) {
    $investigationStage = 'Analysis & Review';
} elseif ($progressData['overall_percent'] >= 40) {
    $investigationStage = 'Evidence Collection';
} elseif ($progressData['overall_percent'] >= 10) {
    $investigationStage = 'Initial Response';
}

// Priority badge colors
$priorityColors = [
    'Critical' => 'danger',
    'High' => 'warning',
    'Medium' => 'info',
    'Low' => 'secondary'
];

// Status badge colors
$statusColors = [
    'Pending' => 'warning',
    'In Progress' => 'info',
    'Completed' => 'success',
    'Cancelled' => 'secondary'
];

// Ensure variables exist for view
$timelineEntries = $timeline ?? [];
$milestoneEntries = $milestones ?? [];

?>
<style>
    .inv-dashboard {
        background: #f5f7fb;
        padding-bottom: 32px;
    }
    .inv-hero {
        background: linear-gradient(115deg, #0f172a, #0f4c75);
        border-radius: 24px;
        color: #fff;
        padding: 32px;
        margin-bottom: 28px;
        box-shadow: 0 20px 40px rgba(15, 76, 117, .35);
    }
    .inv-hero h1 {
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .hero-actions a,
    .hero-actions button {
        border-radius: 999px;
        padding: 10px 18px;
        font-weight: 600;
    }
    .hero-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-top: 24px;
    }
    .hero-pill {
        background: rgba(255, 255, 255, 0.13);
        border-radius: 16px;
        padding: 14px 18px;
        backdrop-filter: blur(8px);
    }
    .hero-pill span {
        font-size: .75rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .8;
    }
    .hero-pill strong {
        font-size: 1.2rem;
        margin-top: 4px;
        display: block;
    }
    .module-card {
        border: none;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(10, 20, 70, .08);
        margin-bottom: 24px;
    }
    .module-card .card-header {
        border: none;
        background: transparent;
        padding: 20px 24px 0;
    }
    .module-card .card-body {
        padding: 24px;
    }
    .checklist-item {
        border: 1px solid #edf1f7;
        border-radius: 14px;
        padding: 12px 16px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .checklist-item.completed {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }
    .progress-track {
        height: 10px;
        border-radius: 999px;
        background: #e8edf4;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #84cc16);
        border-radius: 999px;
    }
    .task-column {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        height: 100%;
    }
    .task-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        padding: 14px;
        margin-bottom: 13px;
    }
    .timeline-track {
        border-left: 2px solid #e5e7eb;
        margin-left: 16px;
        padding-left: 22px;
    }
    .timeline-entry {
        position: relative;
        margin-bottom: 18px;
    }
    .timeline-entry::before {
        content: "";
        position: absolute;
        left: -29px;
        top: 8px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #0f4c75;
        box-shadow: 0 0 0 6px rgba(15, 76, 117, .15);
    }
    .milestone-card {
        border-radius: 16px;
        border: 1px solid #edf2f7;
        padding: 16px;
        margin-bottom: 12px;
    }
    .milestone-card.achieved {
        border-color: #34d399;
        background: #ecfdf5;
    }
    @media (max-width: 992px) {
        .hero-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
    }
</style>

<!-- CSRF Token for AJAX requests -->
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

<div class="inv-dashboard container-fluid">
    <div class="inv-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <p class="text-uppercase small mb-2 text-white-60">Case Investigation</p>
                <h1 class="mb-1"><?= sanitize($case['case_number']) ?> · <?= sanitize($case['case_type']) ?></h1>
                <p class="mb-0 text-white-75"><?= sanitize($case['description'] ?? 'No summary provided') ?></p>
            </div>
            <div class="hero-actions">
                <a href="<?= url('/cases/' . $case['id']) ?>" class="btn btn-outline-light btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Case
                </a>
                <button class="btn btn-light btn-sm mr-2" data-toggle="modal" data-target="#addTaskModal">
                    <i class="fas fa-plus mr-1"></i> New Task
                </button>
                <button class="btn btn-warning btn-sm text-dark" data-toggle="modal" data-target="#addMilestoneModal">
                    <i class="fas fa-flag mr-1"></i> Milestone
                </button>
            </div>
        </div>
        <div class="hero-grid mt-4">
            <div class="hero-pill">
                <span>Investigation Stage</span>
                <strong><?= sanitize($investigationStage) ?></strong>
            </div>
            <div class="hero-pill">
                <span>Overall Progress</span>
                <strong><?= $progressData['overall_percent'] ?>%</strong>
            </div>
            <div class="hero-pill">
                <span>Suspects</span>
                <strong><?= $progressData['suspects_count'] ?></strong>
            </div>
            <div class="hero-pill">
                <span>Evidence</span>
                <strong><?= $progressData['evidence_count'] ?></strong>
            </div>
            <div class="hero-pill">
                <span>Witnesses</span>
                <strong><?= $progressData['witnesses_count'] ?></strong>
            </div>
            <div class="hero-pill">
                <span>Statements</span>
                <strong><?= $progressData['statements_count'] ?></strong>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card module-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-route text-primary mr-2"></i> Investigation Checklist</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 8px; border-radius: 999px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= $progressData['checklist']['percent'] ?>%" 
                             aria-valuenow="<?= $progressData['checklist']['percent'] ?>" 
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-muted small mb-3">
                        <?= $progressData['checklist']['total'] ? "{$progressData['checklist']['percent']}% complete · {$progressData['checklist']['completed']}/{$progressData['checklist']['total']} items" : 'Checklist not initialized yet' ?>
                    </p>
                    <?php if ($progressData['checklist']['total']): ?>
                        <?php foreach ($checklist as $item): ?>
                            <?php $isDone = !empty($item['is_completed']); ?>
                            <div class="checklist-item <?= $isDone ? 'completed' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-checkbox mr-3">
                                        <input type="checkbox"
                                               class="custom-control-input checklist-toggle"
                                               id="check_<?= $item['id'] ?>"
                                               data-item-id="<?= $item['id'] ?>"
                                               data-case-id="<?= $case['id'] ?>"
                                               <?= $isDone ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="check_<?= $item['id'] ?>"></label>
                                    </div>
                                    <div>
                                        <strong><?= sanitize($item['checklist_item'] ?? $item['item_description'] ?? 'Checklist Item') ?></strong><br>
                                        <small class="text-muted"><?= sanitize($item['item_category'] ?? 'General') ?></small>
                                    </div>
                                </div>
                                <?php if ($isDone): ?>
                                    <small class="text-success">
                                        <i class="fas fa-check mr-1"></i><?= sanitize($item['completed_by_name'] ?? 'Officer') ?> ·
                                        <?= format_date($item['completed_date'] ?? $item['completed_at'], 'd M Y') ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle mb-2"></i><br>
                            Checklist items will populate automatically when investigation phases begin.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-lg-8">
            <div class="card module-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list text-primary mr-2"></i> Tasks by Stage</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="progress" style="height: 8px; border-radius: 999px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $progressData['tasks']['percent'] ?>%" 
                                 aria-valuenow="<?= $progressData['tasks']['percent'] ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-muted small mt-2 mb-3">
                            <?= $progressData['tasks']['completed'] ?> completed · 
                            <?= $progressData['tasks']['in_progress'] ?> in progress · 
                            <?= $progressData['tasks']['pending'] ?> pending
                            <?php if ($progressData['tasks']['overdue']): ?>
                                <span class="text-danger"> · <?= $progressData['tasks']['overdue'] ?> overdue</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="row">
                        <?php foreach ($tasksByStatus as $stage => $list): ?>
                            <div class="col-md-4 mb-3">
                                <div class="task-column h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?= $stage ?></strong>
                                        <span class="badge badge-light text-primary"><?= count($list) ?></span>
                                    </div>
                                    <?php if ($list): ?>
                                        <?php foreach ($list as $task): ?>
                                            <div class="task-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="mb-1"><?= sanitize($task['task_title']) ?></h6>
                                                    <span class="badge badge-pill badge-<?= match($task['priority'] ?? 'Medium') {
                                                        'Urgent' => 'danger',
                                                        'High' => 'warning',
                                                        'Low' => 'secondary',
                                                        default => 'info'
                                                    } ?>">
                                                        <?= sanitize($task['priority'] ?? 'Medium') ?>
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-2"><?= sanitize($task['task_description'] ?? '') ?></p>
                                                <small class="text-muted d-block mb-1">
                                                    <i class="fas fa-user mr-1"></i><?= sanitize($task['assigned_to_name'] ?? 'Unassigned') ?>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar mr-1"></i><?= format_date($task['due_date'], 'd M Y') ?>
                                                </small>
                                                <button class="btn btn-link btn-sm px-0 mt-2 update-task-status"
                                                        data-task-id="<?= $task['id'] ?>"
                                                        data-task-title="<?= sanitize($task['task_title']) ?>"
                                                        data-task-description="<?= sanitize($task['task_description'] ?? '') ?>"
                                                        data-current-status="<?= $stage ?>"
                                                        data-toggle="modal"
                                                        data-target="#updateTaskModal">
                                                    Update Status →
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small">No tasks in this stage</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card module-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-flag-checkered text-primary mr-2"></i> Milestones</h5>
                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addMilestoneModal">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($milestoneEntries): ?>
                        <?php foreach ($milestoneEntries as $milestone): ?>
                            <?php $achieved = !empty($milestone['is_achieved']); ?>
                            <div class="milestone-card <?= $achieved ? 'achieved' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= sanitize($milestone['milestone_title']) ?></h6>
                                        <p class="text-muted small mb-2"><?= sanitize($milestone['milestone_description'] ?? '') ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar mr-1"></i>Target <?= format_date($milestone['target_date'], 'd M Y') ?>
                                            <?php if ($achieved && !empty($milestone['achieved_date'])): ?>
                                                <br><i class="fas fa-check-circle text-success mr-1"></i>Achieved <?= format_date($milestone['achieved_date'], 'd M Y') ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="ml-2">
                                        <?php if (!$achieved): ?>
                                            <button class="btn btn-sm btn-success mb-1 mark-milestone-achieved" 
                                                    data-milestone-id="<?= $milestone['id'] ?>"
                                                    data-case-id="<?= $case['id'] ?>"
                                                    title="Mark as Achieved">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <span class="badge badge-<?= $achieved ? 'success' : 'warning' ?> d-block">
                                            <?= $achieved ? 'Achieved' : 'Pending' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle mb-2"></i><br>No milestones have been defined yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card module-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history text-primary mr-2"></i> Case Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if ($timelineEntries): ?>
                        <div class="timeline-track">
                            <?php foreach ($timelineEntries as $entry): ?>
                                <div class="timeline-entry">
                                    <h6 class="mb-1"><?= sanitize($entry['title'] ?? $entry['activity_title'] ?? $entry['type'] ?? $entry['event_type'] ?? 'Timeline Entry') ?></h6>
                                    <p class="text-muted small mb-2"><?= sanitize($entry['description'] ?? $entry['activity_description'] ?? $entry['event_description'] ?? '') ?></p>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock mr-1"></i><?= format_date($entry['date'] ?? $entry['activity_date'] ?? $entry['event_date'] ?? date('Y-m-d H:i:s'), 'd M Y • H:i') ?>
                                        &nbsp;|&nbsp;
                                        <i class="fas fa-user mr-1"></i><?= sanitize($entry['user_name'] ?? $entry['recorded_by_name'] ?? 'System') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle mb-2"></i><br>No timeline entries yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Get CSRF token safely
    const getCsrfToken = function() {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : '';
    };

    // Checklist toggle handler
    document.querySelectorAll('.checklist-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const itemId = this.dataset.itemId;
            const caseId = this.dataset.caseId;
            const completed = this.checked ? 'true' : 'false';
            const csrfToken = getCsrfToken();
            
            if (!csrfToken) {
                alert('Security token not found. Please refresh the page.');
                return;
            }
            
            fetch(`<?= url('/investigations/') ?>${caseId}/checklist`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    item_id: itemId,
                    completed,
                    csrf_token: csrfToken
                })
            }).then(() => location.reload())
            .catch(err => {
                console.error('Failed to update checklist:', err);
                alert('Failed to update checklist. Please try again.');
            });
        });
    });

    // Task status update modal handler
    document.querySelectorAll('.update-task-status').forEach(function (button) {
        button.addEventListener('click', function () {
            const taskId = this.dataset.taskId;
            const taskTitle = this.dataset.taskTitle;
            const taskDescription = this.dataset.taskDescription;
            const currentStatus = this.dataset.currentStatus;
            
            // Populate modal
            document.getElementById('modal_task_id').value = taskId;
            document.getElementById('modal_task_title').textContent = taskTitle;
            document.getElementById('modal_task_description').textContent = taskDescription || 'No description provided';
            document.getElementById('modal_current_status').value = currentStatus;
            document.getElementById('modal_new_status').value = '';
            
            // Reset form
            document.querySelector('#updateTaskForm textarea[name="notes"]').value = '';
        });
    });
    
    // Handle form submission
    document.getElementById('updateTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const taskId = document.getElementById('modal_task_id').value;
        const formData = new FormData(this);
        const csrfToken = getCsrfToken();
        
        if (!csrfToken) {
            alert('Security token not found. Please refresh the page.');
            return;
        }
        
        fetch(`<?= url('/investigations/tasks/') ?>${taskId}/status`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                status: formData.get('status'),
                notes: formData.get('notes'),
                add_timeline_entry: formData.get('add_timeline_entry') || '0',
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Failed to update task status:', err);
            alert('Failed to update task status. Please try again.');
        });
    });
    
    // Milestone achievement handler
    document.querySelectorAll('.mark-milestone-achieved').forEach(function (button) {
        button.addEventListener('click', function () {
            const milestoneId = this.dataset.milestoneId;
            const caseId = this.dataset.caseId;
            
            if (!confirm('Mark this milestone as achieved?')) return;
            
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('Security token not found. Please refresh the page.');
                return;
            }
            
            fetch(`<?= url('/investigations/') ?>${caseId}/milestones/${milestoneId}/achieve`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    csrf_token: csrfToken
                })
            }).then(() => location.reload())
            .catch(err => {
                console.error('Failed to mark milestone as achieved:', err);
                alert('Failed to update milestone. Please try again.');
            });
        });
    });
});
</script>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Investigation Task</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= url('/investigations/' . $case['id'] . '/tasks') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="task_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="task_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Task Type</label>
                        <select name="task_type" class="form-control">
                            <option value="Interview">Interview</option>
                            <option value="Evidence Collection">Evidence Collection</option>
                            <option value="Document Review">Document Review</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Court Preparation">Court Preparation</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To (Officer)</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">Unassigned</option>
                            <?php if (!empty($assigned_officers)): ?>
                                <?php foreach ($assigned_officers as $officer): ?>
                                    <option value="<?= $officer['assigned_to'] ?? $officer['officer_id'] ?? $officer['id'] ?>">
                                        <?= sanitize($officer['officer_name']) ?> (<?= sanitize($officer['rank_name'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Task Status Modal -->
<div class="modal fade" id="updateTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tasks mr-2"></i>Update Task Status
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="updateTaskForm" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="task_id" id="modal_task_id">
                
                <div class="modal-body">
                    <!-- Task Info -->
                    <div class="alert alert-info" style="min-height: 80px; display: block !important; visibility: visible !important;">
                        <h6 class="mb-2" id="modal_task_title">Task Title</h6>
                        <p class="mb-0 small" id="modal_task_description">Task description will appear here</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Status</label>
                                <input type="text" class="form-control" id="modal_current_status" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" id="modal_new_status" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Completion Notes / Update Details</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Add notes about this status update, actions taken, findings, or next steps..."></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Document what was done, findings, obstacles, or next actions needed
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="add_timeline_entry" name="add_timeline_entry" value="1" checked>
                            <label class="custom-control-label" for="add_timeline_entry">
                                <strong>Add entry to investigation timeline</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Automatically log this status update in the case timeline</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Milestone</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= url('/investigations/' . $case['id'] . '/milestones') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Milestone Title <span class="text-danger">*</span></label>
                        <input type="text" name="milestone_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="milestone_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Date</label>
                        <input type="date" name="target_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Investigation']
];

// Include CSRF token in the layout
include __DIR__ . '/../layouts/main.php';
