<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Investigation Dashboard - ' . sanitize($case['case_number']) . '</h3>
                <div class="card-tools">
                    <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Case
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Investigation Checklist -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Investigation Checklist</h3>
            </div>
            <div class="card-body">';

if (!empty($checklist)) {
    $completed = array_filter($checklist, fn($item) => $item['is_completed']);
    $progress = count($checklist) > 0 ? (count($completed) / count($checklist)) * 100 : 0;
    
    $content .= '
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ' . round($progress) . '%">
                        ' . round($progress) . '% Complete
                    </div>
                </div>
                
                <div class="checklist">';
    
    foreach ($checklist as $item) {
        $checked = $item['is_completed'] ? 'checked' : '';
        $strikethrough = $item['is_completed'] ? 'text-muted' : '';
        
        $content .= '
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input checklist-item" 
                               id="check_' . $item['id'] . '" 
                               data-item-id="' . $item['id'] . '" 
                               ' . $checked . '>
                        <label class="custom-control-label ' . $strikethrough . '" for="check_' . $item['id'] . '">
                            ' . sanitize($item['item_description']) . '
                        </label>';
        
        if ($item['is_completed']) {
            $content .= '
                        <br><small class="text-muted">
                            Completed by ' . sanitize($item['completed_by_name']) . ' on ' . format_date($item['completed_at'], 'd M Y') . '
                        </small>';
        }
        
        $content .= '
                    </div>';
    }
    
    $content .= '
                </div>';
} else {
    $content .= '<p class="text-muted">No checklist items yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Milestones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-flag"></i> Investigation Milestones</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addMilestoneModal">
                        <i class="fas fa-plus"></i> Add Milestone
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($milestones)) {
    foreach ($milestones as $milestone) {
        $statusClass = $milestone['is_achieved'] ? 'success' : 'warning';
        $icon = $milestone['is_achieved'] ? 'check-circle' : 'clock';
        
        $content .= '
                <div class="card mb-2">
                    <div class="card-body">
                        <h6>
                            <i class="fas fa-' . $icon . ' text-' . $statusClass . '"></i>
                            ' . sanitize($milestone['milestone_title']) . '
                        </h6>
                        <p class="mb-1">' . sanitize($milestone['milestone_description']) . '</p>
                        <small class="text-muted">
                            Target: ' . format_date($milestone['target_date'], 'd M Y') . '
                        </small>';
        
        if ($milestone['is_achieved']) {
            $content .= '
                        <br><small class="text-success">
                            Achieved on ' . format_date($milestone['achieved_date'], 'd M Y') . '
                        </small>';
        }
        
        $content .= '
                    </div>
                </div>';
    }
} else {
    $content .= '<p class="text-muted">No milestones set</p>';
}

$content .= '
            </div>
        </div>
    </div>

    <!-- Investigation Tasks -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Investigation Tasks</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTaskModal">
                        <i class="fas fa-plus"></i> Add Task
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($tasks)) {
    foreach ($tasks as $task) {
        $statusClass = match($task['status']) {
            'Completed' => 'success',
            'In Progress' => 'info',
            'Pending' => 'warning',
            default => 'secondary'
        };
        
        $priorityClass = match($task['priority']) {
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'info',
            default => 'secondary'
        };
        
        $overdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status'] !== 'Completed';
        
        $content .= '
                <div class="card mb-2 ' . ($overdue ? 'border-danger' : '') . '">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6>' . sanitize($task['task_title']) . '</h6>
                            <span class="badge badge-' . $priorityClass . '">' . sanitize($task['priority']) . '</span>
                        </div>
                        <p class="mb-1">' . sanitize($task['task_description']) . '</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Assigned to: ' . sanitize($task['assigned_to_name'] ?? 'Unassigned') . '
                            </small>
                            <span class="badge badge-' . $statusClass . '">' . sanitize($task['status']) . '</span>
                        </div>';
        
        if ($task['due_date']) {
            $content .= '
                        <small class="text-muted">
                            Due: ' . format_date($task['due_date'], 'd M Y') . '
                            ' . ($overdue ? '<span class="text-danger">(Overdue)</span>' : '') . '
                        </small>';
        }
        
        $content .= '
                    </div>
                </div>';
    }
} else {
    $content .= '<p class="text-muted">No investigation tasks yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Investigation Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Investigation Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline">';

if (!empty($timeline)) {
    foreach ($timeline as $entry) {
        $iconClass = match($entry['event_type']) {
            'Evidence Collected' => 'bg-success',
            'Statement Recorded' => 'bg-info',
            'Suspect Identified' => 'bg-warning',
            'Analysis Complete' => 'bg-primary',
            default => 'bg-secondary'
        };
        
        $content .= '
                    <div>
                        <i class="fas fa-circle ' . $iconClass . '"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . format_date($entry['event_date'], 'd M Y H:i') . '</span>
                            <h3 class="timeline-header">' . sanitize($entry['event_type']) . '</h3>
                            <div class="timeline-body">' . sanitize($entry['event_description']) . '</div>
                            <div class="timeline-footer">
                                <small>Recorded by: ' . sanitize($entry['recorded_by_name']) . '</small>
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>';
} else {
    $content .= '<p class="text-muted">No timeline entries yet</p>';
}

$content .= '
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle checklist item toggle
    $(".checklist-item").change(function() {
        const itemId = $(this).data("item-id");
        const completed = $(this).is(":checked");
        
        $.post("' . url('/investigations/' . $case['id'] . '/checklist') . '", {
            csrf_token: "' . csrf_token() . '",
            item_id: itemId,
            completed: completed
        }).done(function() {
            location.reload();
        }).fail(function() {
            alert("Failed to update checklist");
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Investigation']
];

include __DIR__ . '/../layouts/main.php';
?>
