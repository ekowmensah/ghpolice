<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Commendations' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Commendations</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Commendations & Awards</h3>
                    <div class="card-tools">
                        <a href="<?= url('/officers/commendations/create') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Record Commendation
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="commendationsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Officer</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Awarded By</th>
                                <th>Certificate No.</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commendations as $commendation): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($commendation['commendation_date'])) ?></td>
                                <td><?= sanitize($commendation['officer_name']) ?></td>
                                <td><?= sanitize($commendation['commendation_type']) ?></td>
                                <td><strong><?= sanitize($commendation['commendation_title']) ?></strong></td>
                                <td><?= sanitize($commendation['awarded_by'] ?? 'N/A') ?></td>
                                <td><?= sanitize($commendation['certificate_number'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="<?= url('/officers/commendations/view/' . $commendation['id']) ?>" class="btn btn-sm btn-info">
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
    $('#commendationsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#commendationsTable_wrapper .col-md-6:eq(0)');
});
</script>
