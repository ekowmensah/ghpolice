<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Active Investigations</h3>
                <div class="card-tools">
                    <form method="GET" class="form-inline">
                        <div class="input-group input-group-sm">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="Under Investigation" ' . ($selected_status === 'Under Investigation' ? 'selected' : '') . '>Under Investigation</option>
                                <option value="Open" ' . ($selected_status === 'Open' ? 'selected' : '') . '>Open</option>
                                <option value="Referred" ' . ($selected_status === 'Referred' ? 'selected' : '') . '>Referred</option>
                                <option value="Closed" ' . ($selected_status === 'Closed' ? 'selected' : '') . '>Closed</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Incident Date</th>
                            <th>Status</th>
                            <th>Assigned Officers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($investigations)) {
    foreach ($investigations as $case) {
        $priorityClass = match($case['case_priority']) {
            'Critical' => 'badge-danger',
            'High' => 'badge-warning',
            'Medium' => 'badge-info',
            'Low' => 'badge-secondary',
            default => 'badge-secondary'
        };
        
        $statusClass = match($case['status']) {
            'Under Investigation' => 'badge-primary',
            'Open' => 'badge-success',
            'Referred' => 'badge-warning',
            'Closed' => 'badge-secondary',
            default => 'badge-info'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($case['case_number']) . '</strong></td>
                            <td>' . sanitize(substr($case['description'], 0, 80)) . (strlen($case['description']) > 80 ? '...' : '') . '</td>
                            <td><span class="badge ' . $priorityClass . '">' . sanitize($case['case_priority']) . '</span></td>
                            <td>' . format_date($case['incident_date'], 'd M Y') . '</td>
                            <td><span class="badge ' . $statusClass . '">' . sanitize($case['status']) . '</span></td>
                            <td>
                                <small class="text-muted">View details</small>
                            </td>
                            <td>
                                <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-info" title="View Case">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/investigations/' . $case['id']) . '" class="btn btn-sm btn-primary" title="Investigation Dashboard">
                                    <i class="fas fa-tasks"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> No investigations found with status: ' . sanitize($selected_status) . '
                            </td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Investigations', 'url' => '/investigations'],
    ['title' => 'Active Investigations']
];

include __DIR__ . '/../layouts/main.php';
?>
