<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Disciplinary Records' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Disciplinary</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Disciplinary Records</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="<?= url('/officers/disciplinary?status=Active') ?>" class="btn btn-sm btn-danger">Active</a>
                            <a href="<?= url('/officers/disciplinary?status=Completed') ?>" class="btn btn-sm btn-success">Completed</a>
                            <a href="<?= url('/officers/disciplinary') ?>" class="btn btn-sm btn-secondary">All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="disciplinaryTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Officer</th>
                                <th>Offence Type</th>
                                <th>Action Taken</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($record['incident_date'])) ?></td>
                                <td><?= sanitize($record['officer_name']) ?></td>
                                <td><?= sanitize($record['offence_type']) ?></td>
                                <td><?= sanitize($record['action_taken']) ?></td>
                                <td><?= $record['duration_days'] ? $record['duration_days'] . ' days' : 'N/A' ?></td>
                                <td>
                                    <span class="badge badge-<?= $record['status'] === 'Active' ? 'danger' : 'success' ?>">
                                        <?= sanitize($record['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/officers/disciplinary/view/' . $record['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
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

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#disciplinaryTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#disciplinaryTable_wrapper .col-md-6:eq(0)');
});
</script>
