<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Exhibit Registry</h3>
            </div>
            <div class="card-body">
                <table id="exhibitsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Exhibit Number</th>
                            <th>Case Number</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Seized Date</th>
                            <th>Seized By</th>
                            <th>Current Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($exhibits as $exhibit) {
    $badgeClass = match($exhibit['exhibit_status']) {
        'In Custody' => 'success',
        'In Court' => 'warning',
        'Released' => 'info',
        'Destroyed' => 'danger',
        'Missing' => 'dark',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . sanitize($exhibit['exhibit_number']) . '</strong></td>
                            <td>
                                <a href="' . url('/cases/view/' . $exhibit['case_id']) . '">
                                    ' . sanitize($exhibit['case_number']) . '
                                </a>
                            </td>
                            <td>' . sanitize($exhibit['exhibit_type']) . '</td>
                            <td>' . sanitize(substr($exhibit['description'], 0, 50)) . '...</td>
                            <td>' . date('d M Y', strtotime($exhibit['seized_date'])) . '</td>
                            <td>' . sanitize($exhibit['seized_by_name']) . '</td>
                            <td>' . sanitize($exhibit['current_location']) . '</td>
                            <td>
                                <span class="badge badge-' . $badgeClass . '">
                                    ' . sanitize($exhibit['exhibit_status']) . '
                                </span>
                            </td>
                            <td>
                                <a href="' . url('/exhibits/view/' . $exhibit['id']) . '" class="btn btn-sm btn-info">
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
    $("#exhibitsTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[4, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#exhibitsTable_wrapper .col-md-6:eq(0)");
});
</script>';

$breadcrumbs = [
    ['title' => 'Exhibits']
];

include __DIR__ . '/../layouts/main.php';
?>
