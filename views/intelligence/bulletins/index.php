<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Intelligence Bulletins' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item active">Bulletins</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Intelligence Bulletins</h3>
                    <div class="card-tools">
                        <a href="<?= url('/intelligence/bulletins/create') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Issue Bulletin
                        </a>
                        <div class="btn-group ml-2">
                            <a href="<?= url('/intelligence/bulletins?priority=Critical') ?>" class="btn btn-sm btn-danger">Critical</a>
                            <a href="<?= url('/intelligence/bulletins?priority=High') ?>" class="btn btn-sm btn-warning">High</a>
                            <a href="<?= url('/intelligence/bulletins') ?>" class="btn btn-sm btn-secondary">All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="bulletinsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Bulletin Number</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Subject</th>
                                <th>Valid From</th>
                                <th>Valid Until</th>
                                <th>Issued By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bulletins as $bulletin): ?>
                            <tr>
                                <td><strong><?= sanitize($bulletin['bulletin_number']) ?></strong></td>
                                <td><?= sanitize($bulletin['bulletin_type']) ?></td>
                                <td>
                                    <?php
                                    $priorityClass = match($bulletin['priority']) {
                                        'Critical' => 'danger',
                                        'High' => 'warning',
                                        'Medium' => 'info',
                                        'Low' => 'secondary',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $priorityClass ?>">
                                        <?= sanitize($bulletin['priority']) ?>
                                    </span>
                                </td>
                                <td><?= sanitize($bulletin['subject']) ?></td>
                                <td><?= date('d M Y', strtotime($bulletin['valid_from'])) ?></td>
                                <td><?= $bulletin['valid_until'] ? date('d M Y', strtotime($bulletin['valid_until'])) : 'Indefinite' ?></td>
                                <td><?= sanitize($bulletin['issued_by_name']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $bulletin['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                        <?= sanitize($bulletin['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/intelligence/bulletins/view/' . $bulletin['id']) ?>" class="btn btn-sm btn-info">
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
    $('#bulletinsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[4, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#bulletinsTable_wrapper .col-md-6:eq(0)');
});
</script>
