<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-video"></i> Surveillance Operations</h3>
            </div>
            <div class="card-body">
                <table id="surveillanceTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Operation Code</th>
                            <th>Operation Name</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Start Date</th>
                            <th>Commander</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($surveillances as $surveillance) {
    $statusClass = match($surveillance['operation_status']) {
        'Active' => 'warning',
        'Completed' => 'success',
        'Suspended' => 'danger',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . sanitize($surveillance['operation_code']) . '</strong></td>
                            <td>' . sanitize($surveillance['operation_name']) . '</td>
                            <td>' . sanitize($surveillance['surveillance_type']) . '</td>
                            <td>' . sanitize(substr($surveillance['target_description'], 0, 40)) . '...</td>
                            <td>' . date('d M Y', strtotime($surveillance['start_date'])) . '</td>
                            <td>' . sanitize($surveillance['commander_name']) . ' (' . sanitize($surveillance['rank_name']) . ')</td>
                            <td>
                                <span class="badge badge-' . $statusClass . '">
                                    ' . sanitize($surveillance['operation_status']) . '
                                </span>
                            </td>
                            <td>
                                <a href="' . url('/surveillance/view/' . $surveillance['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
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

$scripts = '
<script>
$(document).ready(function() {
    $("#surveillanceTable").DataTable({
        "responsive": true,
        "order": [[4, "desc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Surveillance']
];

include __DIR__ . '/../layouts/main.php';
?>
