<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-folder-open"></i> Case Management</h3>
                <div class="card-tools">
                    <a href="' . url('/cases/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Register New Case
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/cases') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Open" ' . (($status ?? '') === 'Open' ? 'selected' : '') . '>Open</option>
                                <option value="Under Investigation" ' . (($status ?? '') === 'Under Investigation' ? 'selected' : '') . '>Under Investigation</option>
                                <option value="Closed" ' . (($status ?? '') === 'Closed' ? 'selected' : '') . '>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="priority" class="form-control">
                                <option value="">All Priorities</option>
                                <option value="High" ' . (($priority ?? '') === 'High' ? 'selected' : '') . '>High</option>
                                <option value="Medium" ' . (($priority ?? '') === 'Medium' ? 'selected' : '') . '>Medium</option>
                                <option value="Low" ' . (($priority ?? '') === 'Low' ? 'selected' : '') . '>Low</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="' . url('/cases') . '" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Complainant</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($cases)) {
    foreach ($cases as $case) {
        $statusClass = match($case['status']) {
            'Open' => 'badge-warning',
            'Under Investigation' => 'badge-info',
            'Closed' => 'badge-success',
            default => 'badge-secondary'
        };
        
        $priorityClass = match($case['case_priority']) {
            'High' => 'badge-danger',
            'Medium' => 'badge-warning',
            'Low' => 'badge-info',
            default => 'badge-secondary'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($case['case_number']) . '</strong></td>
                            <td>' . sanitize($case['complainant_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize(substr($case['description'], 0, 50)) . '...</td>
                            <td><span class="badge ' . $statusClass . '">' . sanitize($case['status']) . '</span></td>
                            <td><span class="badge ' . $priorityClass . '">' . sanitize($case['case_priority']) . '</span></td>
                            <td>' . format_date($case['created_at'], 'M d, Y') . '</td>
                            <td>
                                <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/cases/' . $case['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center">No cases found</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>';

if ($pagination) {
    $content .= '
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">';
    
    if ($pagination['current_page'] > 1) {
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/cases?page=' . ($pagination['current_page'] - 1)) . '">«</a></li>';
    }
    
    for ($i = 1; $i <= $pagination['last_page']; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $content .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . url('/cases?page=' . $i) . '">' . $i . '</a></li>';
    }
    
    if ($pagination['current_page'] < $pagination['last_page']) {
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/cases?page=' . ($pagination['current_page'] + 1)) . '">»</a></li>';
    }
    
    $content .= '
                </ul>
                <div class="float-left">
                    Showing ' . count($cases) . ' of ' . $pagination['total'] . ' cases
                </div>
            </div>';
}

$content .= '
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Cases']
];

include __DIR__ . '/../layouts/main.php';
?>
