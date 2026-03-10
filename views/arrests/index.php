<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-handcuffs"></i> Arrest Records</h3>
            </div>
            <div class="card-body">
                <table id="arrestsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Arrest Date</th>
                            <th>Case Number</th>
                            <th>Suspect Name</th>
                            <th>Arresting Officer</th>
                            <th>Arrest Location</th>
                            <th>Arrest Type</th>
                            <th>Station</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($arrests as $arrest) {
    $badgeClass = $arrest['arrest_type'] === 'With Warrant' ? 'success' : 'warning';
    $content .= '
                        <tr>
                            <td>' . date('d M Y', strtotime($arrest['arrest_date'])) . '</td>
                            <td>
                                <a href="' . url('/cases/view/' . $arrest['case_id']) . '">
                                    ' . sanitize($arrest['case_number']) . '
                                </a>
                            </td>
                            <td>' . sanitize($arrest['suspect_name']) . '</td>
                            <td>' . sanitize($arrest['arresting_officer_name']) . ' (' . sanitize($arrest['rank_name']) . ')</td>
                            <td>' . sanitize($arrest['arrest_location']) . '</td>
                            <td>
                                <span class="badge badge-' . $badgeClass . '">
                                    ' . sanitize($arrest['arrest_type']) . '
                                </span>
                            </td>
                            <td>' . sanitize($arrest['station_name']) . '</td>
                            <td>
                                <a href="' . url('/arrests/view/' . $arrest['id']) . '" class="btn btn-sm btn-info">
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
    $("#arrestsTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#arrestsTable_wrapper .col-md-6:eq(0)");
});
</script>';

$breadcrumbs = [
    ['title' => 'Arrests']
];

include __DIR__ . '/../layouts/main.php';
?>
