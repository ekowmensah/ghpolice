<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Disciplinary Record Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers/disciplinary') ?>">Disciplinary</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title">Disciplinary Information</h3>
                            <div class="card-tools">
                                <span class="badge badge-<?= $record['status'] === 'Active' ? 'warning' : 'success' ?> badge-lg">
                                    <?= sanitize($record['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Officer:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($record['officer_name']) ?></strong></dd>

                                <dt class="col-sm-4">Service Number:</dt>
                                <dd class="col-sm-8"><?= sanitize($record['service_number']) ?></dd>

                                <dt class="col-sm-4">Offence Type:</dt>
                                <dd class="col-sm-8"><?= sanitize($record['offence_type']) ?></dd>

                                <dt class="col-sm-4">Incident Date:</dt>
                                <dd class="col-sm-8"><?= date('l, d F Y', strtotime($record['incident_date'])) ?></dd>

                                <dt class="col-sm-4">Offence Description:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($record['offence_description'])) ?></dd>

                                <dt class="col-sm-4">Action Taken:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($record['action_taken']) ?></strong></dd>

                                <dt class="col-sm-4">Action Date:</dt>
                                <dd class="col-sm-8"><?= date('d M Y', strtotime($record['action_date'])) ?></dd>

                                <?php if ($record['duration_days']): ?>
                                <dt class="col-sm-4">Duration:</dt>
                                <dd class="col-sm-8"><?= $record['duration_days'] ?> days</dd>
                                <?php endif; ?>

                                <dt class="col-sm-4">Recorded By:</dt>
                                <dd class="col-sm-8"><?= sanitize($record['recorded_by_name']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/officers/disciplinary') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <?php if ($record['status'] === 'Active'): ?>
                            <button class="btn btn-success btn-block" id="completeBtn">
                                <i class="fas fa-check"></i> Mark as Completed
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-primary btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$('#completeBtn').click(function() {
    Swal.fire({
        title: 'Mark as Completed?',
        text: 'This will close this disciplinary action',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Complete',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('/officers/disciplinary/update-status/' . $record['id']) ?>', {
                csrf_token: '<?= csrf_token() ?>',
                status: 'Completed'
            }, function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        }
    });
});
</script>
