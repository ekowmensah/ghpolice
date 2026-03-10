<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Incident Reports</h3>
            </div>
            <div class="card-body">
                <table id="incidentsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Incident Number</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Attending Officer</th>
                            <th>Station</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($incidents as $incident) {
    $statusClass = $incident['status'] == 'Open' ? 'warning' : 'success';
    
    $content .= '
                        <tr>
                            <td><strong>' . sanitize($incident['incident_number']) . '</strong></td>
                            <td>' . date('d M Y H:i', strtotime($incident['incident_date'])) . '</td>
                            <td>' . sanitize($incident['incident_type']) . '</td>
                            <td>' . sanitize($incident['incident_location']) . '</td>
                            <td>' . sanitize($incident['attending_officer_name']) . '</td>
                            <td>' . sanitize($incident['station_name']) . '</td>
                            <td>
                                <span class="badge badge-' . $statusClass . '">
                                    ' . sanitize($incident['status']) . '
                                </span>
                            </td>
                            <td>
                                <a href="' . url('/incidents/view/' . $incident['id']) . '" class="btn btn-sm btn-info">
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
    $("#incidentsTable").DataTable({
        "responsive": true,
        "order": [[1, "desc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Incidents']
];

include __DIR__ . '/../layouts/main.php';
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Incident Reports' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Incidents</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Incident Reports</h3>
                    <div class="card-tools">
                        <a href="<?= url('/incidents/create') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Create Report
                        </a>
                        <div class="btn-group ml-2">
                            <a href="<?= url('/incidents?status=Open') ?>" class="btn btn-sm btn-warning">Open</a>
                            <a href="<?= url('/incidents?status=Closed') ?>" class="btn btn-sm btn-success">Closed</a>
                            <a href="<?= url('/incidents') ?>" class="btn btn-sm btn-secondary">All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="incidentsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Incident Number</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Attending Officer</th>
                                <th>Station</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><strong><?= sanitize($incident['incident_number']) ?></strong></td>
                                <td><?= date('d M Y H:i', strtotime($incident['incident_date'])) ?></td>
                                <td><?= sanitize($incident['incident_type']) ?></td>
                                <td><?= sanitize($incident['incident_location']) ?></td>
                                <td><?= sanitize($incident['attending_officer_name']) ?></td>
                                <td><?= sanitize($incident['station_name']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($incident['status']) {
                                        'Open' => 'warning',
                                        'Under Review' => 'info',
                                        'Closed' => 'success',
                                        'Escalated' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= sanitize($incident['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/incidents/view/' . $incident['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($incident['status'] === 'Open' && !$incident['case_id']): ?>
                                    <button class="btn btn-sm btn-warning escalate-incident" data-id="<?= $incident['id'] ?>">
                                        <i class="fas fa-level-up-alt"></i> Escalate
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#incidentsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[1, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#incidentsTable_wrapper .col-md-6:eq(0)');
});
</script>
