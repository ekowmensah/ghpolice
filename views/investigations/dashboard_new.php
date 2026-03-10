<?php
$content = '
<style>
    .gps-investigation {
        background: #f8f9fa;
        min-height: 100vh;
        padding-bottom: 30px;
    }
    .gps-inv-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .gps-inv-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .gps-inv-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .gps-inv-card-header {
        background: #2c3e50;
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .gps-task-card {
        border-left: 4px solid #3498db;
        margin-bottom: 15px;
        padding: 15px;
        background: white;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    .gps-task-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }
    .gps-task-urgent { border-left-color: #e74c3c; }
    .gps-task-high { border-left-color: #f39c12; }
    .gps-task-medium { border-left-color: #3498db; }
    .gps-task-low { border-left-color: #95a5a6; }
    .gps-task-completed { opacity: 0.6; background: #f8f9fa; }
    .gps-progress-bar {
        height: 25px;
        border-radius: 20px;
        background: #e9ecef;
        overflow: hidden;
    }
    .gps-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #27ae60 0%, #2ecc71 100%);
        transition: width 0.5s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .gps-checklist-item {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }
    .gps-checklist-item:hover {
        background: #f8f9fa;
    }
    .gps-checklist-item:last-child {
        border-bottom: none;
    }
    .gps-timeline-item {
        position: relative;
        padding-left: 40px;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
    }
    .gps-timeline-item:last-child {
        border-left: none;
    }
    .gps-timeline-icon {
        position: absolute;
        left: -12px;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%';

$content .= ';
        background: #3498db;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .gps-milestone-card {
        border-left: 4px solid #16a085;
        padding: 15px;
        background: white;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .gps-milestone-achieved {
        border-left-color: #27ae60;
        background: #f0fff4;
    }
    @media (max-width: 768px) {
        .gps-inv-card-header {
            flex-direction: column;
            gap: 10px;
        }
        .gps-inv-header h2 {
            font-size: 1.2rem !important;
        }
    }
</style>

<div class="gps-investigation">
    <div class="gps-inv-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem;"><i class="fas fa-search"></i> Investigation Management</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Case: ' . sanitize($case['case_number']) . ' - ' . sanitize($case['case_type']) . '</p>
            </div>
            <a href="' . url('/cases/' . $case['id']) . '" class="gps-btn gps-btn-dark" style="margin-top: 10px;">
                <i class="fas fa-arrow-left"></i> Back to Case
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Investigation Checklist -->
        <div class="col-lg-6 col-md-12">
            <div class="gps-inv-card">
                <div class="gps-inv-card-header">
                    <h3 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-tasks"></i> Investigation Checklist</h3>
                </div>
                <div style="padding: 20px;">';

if (!empty($checklist)) {
    $completed = array_filter($checklist, fn($item) => $item['is_completed']);
    $progress = count($checklist) > 0 ? (count($completed) / count($checklist)) * 100 : 0;
    
    $content .= '
                    <div class="gps-progress-bar mb-3">
                        <div class="gps-progress-fill" style="width: ' . round($progress) . '%">
                            ' . round($progress) . '% Complete (' . count($completed) . '/' . count($checklist) . ')
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">';
    
    foreach ($checklist as $item) {
        $checked = $item['is_completed'] ? 'checked' : '';
        $strikethrough = $item['is_completed'] ? 'text-decoration: line-through; color: #95a5a6;' : '';
        
        $content .= '
                        <div class="gps-checklist-item">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checklist-item" 
                                       id="check_' . $item['id'] . '" 
                                       data-item-id="' . $item['id'] . '" 
                                       data-case-id="' . $case['id'] . '"
                                       ' . $checked . '>
                                <label class="custom-control-label" for="check_' . $item['id'] . '" style="' . $strikethrough . '">
                                    <strong>' . sanitize($item['checklist_item'] ?? $item['item_description'] ?? 'Checklist Item') . '</strong>
                                    <span class="badge badge-secondary ml-2">' . sanitize($item['item_category'] ?? 'General') . '</span>
                                </label>';
        
        if ($item['is_completed']) {
            $content .= '
                                <br><small class="text-muted">
                                    <i class="fas fa-check-circle text-success"></i> Completed by ' . sanitize($item['completed_by_name'] ?? 'Unknown') . ' on ' . format_date($item['completed_date'] ?? $item['completed_at'], 'd M Y') . '
                                </small>';
        }
        
        $content .= '
                            </div>
                        </div>';
    }
    
    $content .= '
                    </div>';
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No checklist items yet. Checklist will be initialized automatically.</p>';
}

$content .= '
                </div>
            </div>

            <!-- Milestones -->
            <div class="gps-inv-card">
                <div class="gps-inv-card-header">
                    <h3 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-flag-checkered"></i> Investigation Milestones</h3>
                    <button class="gps-btn gps-btn-primary" style="padding: 6px 12px;" data-toggle="modal" data-target="#addMilestoneModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <div style="padding: 20px;">';

if (!empty($milestones)) {
    foreach ($milestones as $milestone) {
        $isAchieved = $milestone['is_achieved'] ?? false;
        $cardClass = $isAchieved ? 'gps-milestone-achieved' : '';
        $icon = $isAchieved ? 'check-circle' : 'clock';
        $iconColor = $isAchieved ? 'success' : 'warning';
        
        $content .= '
                    <div class="gps-milestone-card ' . $cardClass . '">
                        <h6 style="margin: 0 0 10px 0;">
                            <i class="fas fa-' . $icon . ' text-' . $iconColor . '"></i>
                            ' . sanitize($milestone['milestone_title']) . '
                        </h6>
                        <p class="mb-2" style="color: #6c757d;">' . sanitize($milestone['milestone_description'] ?? '') . '</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> Target: ' . format_date($milestone['target_date'], 'd M Y') . '
                            </small>';
        
        if ($isAchieved) {
            $content .= '
                            <small class="text-success">
                                <i class="fas fa-check"></i> Achieved: ' . format_date($milestone['achieved_date'], 'd M Y') . '
                            </small>';
        }
        
        $content .= '
                        </div>
                    </div>';
    }
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No milestones set yet</p>';
}

$content .= '
                </div>
            </div>
        </div>

        <!-- Investigation Tasks -->
        <div class="col-lg-6 col-md-12">
            <div class="gps-inv-card">
                <div class="gps-inv-card-header">
                    <h3 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-clipboard-list"></i> Investigation Tasks</h3>
                    <button class="gps-btn gps-btn-primary" style="padding: 6px 12px;" data-toggle="modal" data-target="#addTaskModal">
                        <i class="fas fa-plus"></i> Add Task
                    </button>
                </div>
                <div style="padding: 20px; max-height: 600px; overflow-y: auto;">';

if (!empty($tasks)) {
    $tasksByStatus = [
        'Pending' => [],
        'In Progress' => [],
        'Completed' => [],
        'Cancelled' => []
    ];
    
    foreach ($tasks as $task) {
        $status = $task['status'] ?? 'Pending';
        $tasksByStatus[$status][] = $task;
    }
    
    foreach (['Pending', 'In Progress', 'Completed'] as $status) {
        if (empty($tasksByStatus[$status])) continue;
        
        $content .= '
                    <h6 style="margin: 20px 0 15px 0; color: #2c3e50; border-bottom: 2px solid #e9ecef; padding-bottom: 8px;">
                        ' . $status . ' (' . count($tasksByStatus[$status]) . ')
                    </h6>';
        
        foreach ($tasksByStatus[$status] as $task) {
            $priority = strtolower($task['priority'] ?? 'medium');
            $isCompleted = $status === 'Completed';
            $completedClass = $isCompleted ? 'gps-task-completed' : '';
            
            $content .= '
                    <div class="gps-task-card gps-task-' . $priority . ' ' . $completedClass . '">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <h6 style="margin: 0; flex: 1;">' . sanitize($task['task_title']) . '</h6>
                            <span class="gps-badge gps-badge-' . match($task['priority']) {
                                'Urgent' => 'danger',
                                'High' => 'warning',
                                'Medium' => 'info',
                                'Low' => 'secondary',
                                default => 'secondary'
                            } . '">' . sanitize($task['priority']) . '</span>
                        </div>
                        <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 0.9rem;">' . sanitize($task['task_description'] ?? '') . '</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> ' . sanitize($task['assigned_to_name'] ?? 'Unassigned') . '
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> Due: ' . format_date($task['due_date'], 'd M Y') . '
                                </small>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info update-task-status" 
                                        data-task-id="' . $task['id'] . '" 
                                        data-current-status="' . $status . '"
                                        title="Update Status">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
        }
    }
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No tasks assigned yet</p>';
}

$content .= '
                </div>
            </div>

            <!-- Investigation Timeline -->
            <div class="gps-inv-card">
                <div class="gps-inv-card-header">
                    <h3 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-history"></i> Investigation Timeline</h3>
                </div>
                <div style="padding: 20px; max-height: 400px; overflow-y: auto;">';

if (!empty($timeline)) {
    foreach ($timeline as $entry) {
        $content .= '
                    <div class="gps-timeline-item">
                        <div class="gps-timeline-icon">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div>
                            <strong>' . sanitize($entry['activity_title'] ?? $entry['event_type']) . '</strong>
                            <p style="margin: 5px 0; color: #6c757d; font-size: 0.9rem;">' . sanitize($entry['activity_description'] ?? $entry['event_description'] ?? '') . '</p>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> ' . format_date($entry['activity_date'] ?? $entry['event_date'], 'd M Y H:i') . '
                                | <i class="fas fa-user"></i> ' . sanitize($entry['recorded_by_name'] ?? 'System') . '
                            </small>
                        </div>
                    </div>';
    }
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No timeline entries yet</p>';
}

$content .= '
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #2c3e50; color: white;">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add Investigation Task</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addTaskForm">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="task_title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="task_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Type</label>
                                <select class="form-control" name="task_type">
                                    <option value="Interview">Interview</option>
                                    <option value="Evidence Collection">Evidence Collection</option>
                                    <option value="Document Review">Document Review</option>
                                    <option value="Follow-up">Follow-up</option>
                                    <option value="Court Preparation">Court Preparation</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select class="form-control" name="priority">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Assign To</label>
                                <select class="form-control" name="assigned_to">
                                    <option value="">Select Officer...</option>
                                    <!-- Officers will be loaded dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="gps-btn gps-btn-primary">
                        <i class="fas fa-save"></i> Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #2c3e50; color: white;">
                <h5 class="modal-title"><i class="fas fa-flag"></i> Add Milestone</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/investigations/' . $case['id'] . '/milestones') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Milestone Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="milestone_title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="milestone_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Date</label>
                        <input type="date" class="form-control" name="target_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="gps-btn gps-btn-primary">
                        <i class="fas fa-save"></i> Add Milestone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle checklist item toggle
    $(".checklist-item").on("change", function() {
        const itemId = $(this).data("item-id");
        const caseId = $(this).data("case-id");
        const completed = $(this).is(":checked");
        
        $.ajax({
            url: "' . url('/investigations/') . '" + caseId + "/checklist",
            method: "POST",
            data: {
                item_id: itemId,
                completed: completed,
                csrf_token: $("input[name=csrf_token]").val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Handle task form submission
    $("#addTaskForm").on("submit", function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/investigations/' . $case['id'] . '/tasks') . '",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to create task");
                }
            },
            error: function() {
                alert("Failed to create task");
            }
        });
    });
    
    // Handle task status update
    $(".update-task-status").on("click", function() {
        const taskId = $(this).data("task-id");
        const currentStatus = $(this).data("current-status");
        
        const newStatus = prompt("Update task status to:", currentStatus);
        if (!newStatus) return;
        
        $.ajax({
            url: "' . url('/investigations/tasks/') . '" + taskId + "/status",
            method: "POST",
            data: {
                status: newStatus,
                notes: "",
                csrf_token: $("input[name=csrf_token]").val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
';

require_once __DIR__ . '/../layout.php';
