<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-warehouse"></i> Asset Registry</h3>
            </div>
            <div class="card-body">
                <table id="assetsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Serial Number</th>
                            <th>Type</th>
                            <th>Current Location</th>
                            <th>Condition</th>
                            <th>Case Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($assets as $asset) {
    $conditionClass = match($asset['condition_status']) {
        'Excellent' => 'success',
        'Good' => 'info',
        'Fair' => 'warning',
        'Poor' => 'danger',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . sanitize($asset['asset_name']) . '</strong></td>
                            <td>' . sanitize($asset['serial_number'] ?? 'N/A') . '</td>
                            <td>' . sanitize($asset['asset_type']) . '</td>
                            <td>' . sanitize($asset['current_location']) . '</td>
                            <td>
                                <span class="badge badge-' . $conditionClass . '">
                                    ' . sanitize($asset['condition_status']) . '
                                </span>
                            </td>
                            <td>' . ($asset['case_number'] ? '<a href="' . url('/cases/view/' . $asset['case_id']) . '">' . sanitize($asset['case_number']) . '</a>' : 'N/A') . '</td>
                            <td>
                                <a href="' . url('/assets/view/' . $asset['id']) . '" class="btn btn-sm btn-info">
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
    $("#assetsTable").DataTable({
        "responsive": true,
        "order": [[0, "asc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Assets']
];

include __DIR__ . '/../layouts/main.php';
?>
