<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Training' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Training</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($officer)): ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Officer Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= sanitize($officer['first_name'] . ' ' . $officer['last_name']) ?></p>
                    <p><strong>Service Number:</strong> <?= sanitize($officer['service_number']) ?></p>
                    <p><strong>Rank:</strong> <?= sanitize($officer['rank_name']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Training Records</h3>
                    <div class="card-tools">
                        <a href="<?= url('/officers/training/create') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Record Training
                        </a>
                        <a href="<?= url('/officers/training/upcoming') ?>" class="btn btn-sm btn-info ml-2">
                            <i class="fas fa-calendar"></i> Upcoming
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="trainingTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Training Name</th>
                                <th>Type</th>
                                <th>Institution</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Certificate</th>
                                <th>Grade/Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $training): ?>
                            <tr>
                                <td><?= sanitize($training['officer_name'] ?? '') ?> (<?= sanitize($training['rank_name'] ?? '') ?>)</td>
                                <td><strong><?= sanitize($training['training_name']) ?></strong></td>
                                <td><?= sanitize($training['training_type']) ?></td>
                                <td><?= sanitize($training['training_institution'] ?? 'N/A') ?></td>
                                <td><?= date('d M Y', strtotime($training['start_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($training['end_date'])) ?></td>
                                <td><?= sanitize($training['certificate_number'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($training['grade_score']): ?>
                                        <span class="badge badge-success"><?= sanitize($training['grade_score']) ?></span>
                                    <?php else: ?>
                                        N/A
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

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#trainingTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[4, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#trainingTable_wrapper .col-md-6:eq(0)');
});
</script>
