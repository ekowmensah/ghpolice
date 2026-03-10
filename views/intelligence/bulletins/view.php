<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Intelligence Bulletin</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence/bulletins') ?>">Bulletins</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">
                                <?php
                                $priorityClass = match($bulletin['priority']) {
                                    'Critical' => 'danger',
                                    'High' => 'warning',
                                    'Medium' => 'info',
                                    'Low' => 'secondary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge badge-<?= $priorityClass ?> badge-lg mr-2">
                                    <?= sanitize($bulletin['priority']) ?>
                                </span>
                                <?= sanitize($bulletin['subject']) ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-<?= $bulletin['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                    <?= sanitize($bulletin['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Bulletin Number:</dt>
                                <dd class="col-sm-9"><strong><?= sanitize($bulletin['bulletin_number']) ?></strong></dd>

                                <dt class="col-sm-3">Bulletin Type:</dt>
                                <dd class="col-sm-9"><?= sanitize($bulletin['bulletin_type']) ?></dd>

                                <dt class="col-sm-3">Issued By:</dt>
                                <dd class="col-sm-9"><?= sanitize($bulletin['issued_by_name']) ?></dd>

                                <dt class="col-sm-3">Valid From:</dt>
                                <dd class="col-sm-9"><?= date('l, d F Y', strtotime($bulletin['valid_from'])) ?></dd>

                                <dt class="col-sm-3">Valid Until:</dt>
                                <dd class="col-sm-9">
                                    <?= $bulletin['valid_until'] ? date('d M Y', strtotime($bulletin['valid_until'])) : '<span class="badge badge-info">Indefinite</span>' ?>
                                </dd>

                                <dt class="col-sm-3">Target Audience:</dt>
                                <dd class="col-sm-9"><?= sanitize($bulletin['target_audience']) ?></dd>

                                <dt class="col-sm-3">Public:</dt>
                                <dd class="col-sm-9">
                                    <?= $bulletin['is_public'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                </dd>
                            </dl>

                            <hr>

                            <h5>Bulletin Content</h5>
                            <div class="alert alert-light">
                                <?= nl2br(sanitize($bulletin['bulletin_content'])) ?>
                            </div>

                            <?php if ($bulletin['action_required']): ?>
                            <h5>Action Required</h5>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= nl2br(sanitize($bulletin['action_required'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/intelligence/bulletins') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <?php if ($bulletin['status'] === 'Active'): ?>
                            <button class="btn btn-warning btn-block" id="expireBulletinBtn">
                                <i class="fas fa-clock"></i> Expire Bulletin
                            </button>
                            <button class="btn btn-danger btn-block" id="cancelBulletinBtn">
                                <i class="fas fa-ban"></i> Cancel Bulletin
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-primary btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" class="btn btn-info btn-block">
                                <i class="fas fa-share"></i> Distribute
                            </button>
                        </div>
                    </div>

                    <div class="card card-<?= $priorityClass ?>">
                        <div class="card-header">
                            <h3 class="card-title">Priority Level</h3>
                        </div>
                        <div class="card-body text-center">
                            <h2><?= sanitize($bulletin['priority']) ?></h2>
                            <p class="mb-0">
                                <?php
                                $priorityText = match($bulletin['priority']) {
                                    'Critical' => 'Immediate action required',
                                    'High' => 'Urgent attention needed',
                                    'Medium' => 'Normal priority',
                                    'Low' => 'For information',
                                    default => 'Standard'
                                };
                                ?>
                                <?= $priorityText ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$('#expireBulletinBtn').click(function() {
    Swal.fire({
        title: 'Expire Bulletin?',
        text: 'This will mark the bulletin as expired',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Expire',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('/intelligence/bulletins/expire/' . $bulletin['id']) ?>', {
                csrf_token: '<?= csrf_token() ?>'
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

$('#cancelBulletinBtn').click(function() {
    Swal.fire({
        title: 'Cancel Bulletin?',
        input: 'textarea',
        inputLabel: 'Cancellation reason',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel Bulletin',
        confirmButtonColor: '#dc3545',
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('Reason is required');
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('/intelligence/bulletins/cancel/' . $bulletin['id']) ?>', {
                csrf_token: '<?= csrf_token() ?>',
                reason: result.value
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
