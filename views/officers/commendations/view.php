<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Commendation Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers/commendations') ?>">Commendations</a></li>
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
                        <div class="card-header bg-success">
                            <h3 class="card-title">
                                <i class="fas fa-award"></i> <?= sanitize($commendation['commendation_title']) ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Officer:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($commendation['officer_name']) ?></strong></dd>

                                <dt class="col-sm-4">Service Number:</dt>
                                <dd class="col-sm-8"><?= sanitize($commendation['service_number']) ?></dd>

                                <dt class="col-sm-4">Commendation Type:</dt>
                                <dd class="col-sm-8"><?= sanitize($commendation['commendation_type']) ?></dd>

                                <dt class="col-sm-4">Commendation Date:</dt>
                                <dd class="col-sm-8"><?= date('l, d F Y', strtotime($commendation['commendation_date'])) ?></dd>

                                <?php if ($commendation['description']): ?>
                                <dt class="col-sm-4">Description:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($commendation['description'])) ?></dd>
                                <?php endif; ?>

                                <?php if ($commendation['awarded_by']): ?>
                                <dt class="col-sm-4">Awarded By:</dt>
                                <dd class="col-sm-8"><?= sanitize($commendation['awarded_by']) ?></dd>
                                <?php endif; ?>

                                <?php if ($commendation['certificate_number']): ?>
                                <dt class="col-sm-4">Certificate Number:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($commendation['certificate_number']) ?></strong></dd>
                                <?php endif; ?>

                                <dt class="col-sm-4">Recorded By:</dt>
                                <dd class="col-sm-8"><?= sanitize($commendation['recorded_by_name']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Recognition</h3>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-medal fa-5x text-warning mb-3"></i>
                            <h4><?= sanitize($commendation['commendation_type']) ?></h4>
                            <p class="text-muted"><?= date('F Y', strtotime($commendation['commendation_date'])) ?></p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/officers/commendations') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button type="button" class="btn btn-primary btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Certificate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
