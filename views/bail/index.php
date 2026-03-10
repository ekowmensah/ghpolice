<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-balance-scale"></i> Bail Records</h3>
            </div>
            <div class="card-body">
                <table id="bailTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bail Date</th>
                            <th>Case Number</th>
                            <th>Suspect Name</th>
                            <th>Bail Status</th>
                            <th>Bail Amount</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($bail_records as $bail) {
    $statusClass = $bail['bail_status'] === 'Granted' ? 'success' : ($bail['bail_status'] === 'Denied' ? 'danger' : 'warning');
    $content .= '
                        <tr>
                            <td>' . date('d M Y', strtotime($bail['bail_date'])) . '</td>
                            <td>
                                <a href="' . url('/cases/view/' . $bail['case_id']) . '">
                                    ' . sanitize($bail['case_number']) . '
                                </a>
                            </td>
                            <td>' . sanitize($bail['suspect_name']) . '</td>
                            <td>
                                <span class="badge badge-' . $statusClass . '">
                                    ' . sanitize($bail['bail_status']) . '
                                </span>
                            </td>
                            <td>' . ($bail['bail_amount'] ? 'GHS ' . number_format($bail['bail_amount'], 2) : 'N/A') . '</td>
                            <td>' . sanitize($bail['approved_by_name'] ?? 'N/A') . '</td>
                            <td>
                                <a href="' . url('/bail/view/' . $bail['id']) . '" class="btn btn-sm btn-info">
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
    $("#bailTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#bailTable_wrapper .col-md-6:eq(0)");
});
</script>';

$breadcrumbs = [
    ['title' => 'Bail Records']
];

include __DIR__ . '/../layouts/main.php';
?>
