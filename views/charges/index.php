<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Charges Filed</h3>
            </div>
            <div class="card-body">
                <table id="chargesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Charge Date</th>
                            <th>Case Number</th>
                            <th>Suspect Name</th>
                            <th>Offence</th>
                            <th>Law Section</th>
                            <th>Status</th>
                            <th>Charged By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($charges as $charge) {
    $badgeClass = match($charge['charge_status']) {
        'Pending' => 'warning',
        'Filed' => 'success',
        'Withdrawn' => 'danger',
        'Dismissed' => 'secondary',
        default => 'info'
    };
    
    $content .= '
                        <tr>
                            <td>' . date('d M Y', strtotime($charge['charge_date'])) . '</td>
                            <td>
                                <a href="' . url('/cases/view/' . $charge['case_id']) . '">
                                    ' . sanitize($charge['case_number']) . '
                                </a>
                            </td>
                            <td>' . sanitize($charge['suspect_name']) . '</td>
                            <td><strong>' . sanitize($charge['offence_name']) . '</strong></td>
                            <td>' . sanitize($charge['law_section'] ?? 'N/A') . '</td>
                            <td>
                                <span class="badge badge-' . $badgeClass . '">
                                    ' . sanitize($charge['charge_status']) . '
                                </span>
                            </td>
                            <td>' . sanitize($charge['charged_by_name']) . '</td>
                            <td>
                                <a href="' . url('/charges/view/' . $charge['id']) . '" class="btn btn-sm btn-info">
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
<!-- DataTables CSS -->
<link rel="stylesheet" href="' . url('/AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') . '">
<link rel="stylesheet" href="' . url('/AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') . '">
<link rel="stylesheet" href="' . url('/AdminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') . '">

<!-- DataTables JS -->
<script src="' . url('/AdminLTE/plugins/datatables/jquery.dataTables.min.js') . '"></script>
<script src="' . url('/AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') . '"></script>
<script src="' . url('/AdminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js') . '"></script>
<script src="' . url('/AdminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') . '"></script>
<script src="' . url('/AdminLTE/plugins/datatables-buttons/js/dataTables.buttons.min.js') . '"></script>
<script src="' . url('/AdminLTE/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') . '"></script>

<script>
$(document).ready(function() {
    $("#chargesTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#chargesTable_wrapper .col-md-6:eq(0)");
});
</script>';

$breadcrumbs = [
    ['title' => 'Charges']
];

include __DIR__ . '/../layouts/main.php';
?>
