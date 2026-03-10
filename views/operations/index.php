<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Police Operations</h3>
            </div>
            <div class="card-body">
                <table id="operationsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Operation Code</th>
                            <th>Operation Name</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Commander</th>
                            <th>Station</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($operations as $operation) {
    $statusClass = match($operation['operation_status']) {
        'Planned' => 'info',
        'In Progress' => 'warning',
        'Completed' => 'success',
        'Cancelled' => 'danger',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . sanitize($operation['operation_code']) . '</strong></td>
                            <td>' . sanitize($operation['operation_name']) . '</td>
                            <td>' . sanitize($operation['operation_type']) . '</td>
                            <td>' . date('d M Y H:i', strtotime($operation['operation_date'] . ' ' . $operation['start_time'])) . '</td>
                            <td>' . sanitize($operation['commander_name']) . ' (' . sanitize($operation['rank_name']) . ')</td>
                            <td>' . sanitize($operation['station_name']) . '</td>
                            <td>
                                <span class="badge badge-' . $statusClass . '">
                                    ' . sanitize($operation['operation_status']) . '
                                </span>
                            </td>
                            <td>
                                <a href="' . url('/operations/view/' . $operation['id']) . '" class="btn btn-sm btn-info">
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
    $("#operationsTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[3, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#operationsTable_wrapper .col-md-6:eq(0)");
});
</script>';

$breadcrumbs = [
    ['title' => 'Operations']
];

include __DIR__ . '/../layouts/main.php';
?>
