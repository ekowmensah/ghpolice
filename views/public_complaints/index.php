<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Public Complaints</h3>
                <div class="card-tools">
                    <a href="' . url('/public-complaints/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> File New Complaint
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="complaintsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Complaint Number</th>
                            <th>Date</th>
                            <th>Complainant</th>
                            <th>Type</th>
                            <th>Officer</th>
                            <th>Station</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($complaints as $complaint) {
    $statusClass = 'secondary';
    switch($complaint['complaint_status']) {
        case 'Received':
            $statusClass = 'info';
            break;
        case 'Under Investigation':
            $statusClass = 'warning';
            break;
        case 'Resolved':
            $statusClass = 'success';
            break;
        case 'Dismissed':
            $statusClass = 'secondary';
            break;
        case 'Referred to CHRAJ':
            $statusClass = 'danger';
            break;
    }
    
    $content .= '
                        <tr>
                            <td><strong>' . htmlspecialchars($complaint['complaint_number']) . '</strong></td>
                            <td>' . date('d M Y', strtotime($complaint['created_at'])) . '</td>
                            <td>' . htmlspecialchars($complaint['complainant_name']) . '</td>
                            <td>' . htmlspecialchars($complaint['complaint_type']) . '</td>
                            <td>' . htmlspecialchars($complaint['officer_name'] ?? 'General') . '</td>
                            <td>' . htmlspecialchars($complaint['station_name']) . '</td>
                            <td>
                                <span class="badge badge-' . $statusClass . '">
                                    ' . htmlspecialchars($complaint['complaint_status']) . '
                                </span>
                            </td>
                            <td>
                                <a href="' . url('/public-complaints/' . $complaint['id']) . '" class="btn btn-sm btn-info">
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
</div>

<script>
$(document).ready(function() {
    $("#complaintsTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[1, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo("#complaintsTable_wrapper .col-md-6:eq(0)");
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
