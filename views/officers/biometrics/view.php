<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Officer Biometric Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Biometrics</li>
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
                        <div class="card-header bg-primary">
                            <h3 class="card-title">
                                <i class="fas fa-fingerprint"></i> Biometric Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Officer:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($officer['first_name'] . ' ' . $officer['last_name']) ?></strong></dd>

                                <dt class="col-sm-4">Service Number:</dt>
                                <dd class="col-sm-8"><?= sanitize($officer['service_number']) ?></dd>

                                <dt class="col-sm-4">Rank:</dt>
                                <dd class="col-sm-8"><?= sanitize($officer['rank']) ?></dd>

                                <dt class="col-sm-4">Station:</dt>
                                <dd class="col-sm-8"><?= sanitize($officer['station_name']) ?></dd>
                            </dl>

                            <hr>

                            <h5 class="mb-3">Captured Biometrics</h5>
                            
                            <?php if (empty($biometrics)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No biometric data captured yet.
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Biometric Type</th>
                                            <th>Captured Date</th>
                                            <th>Captured By</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($biometrics as $biometric): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-<?= $biometric['biometric_type'] == 'Fingerprint' ? 'fingerprint' : 
                                                    ($biometric['biometric_type'] == 'Face' ? 'user-circle' : 
                                                    ($biometric['biometric_type'] == 'Iris' ? 'eye' : 'hand-paper')) ?>"></i>
                                                <?= sanitize($biometric['biometric_type']) ?>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($biometric['captured_date'])) ?></td>
                                            <td><?= sanitize($biometric['captured_by_name']) ?></td>
                                            <td><span class="badge badge-success">Captured</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Biometric Status</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $biometricTypes = ['Fingerprint', 'Face', 'Iris', 'Palm Print', 'Voice'];
                            $capturedTypes = array_column($biometrics, 'biometric_type');
                            ?>
                            <ul class="list-unstyled">
                                <?php foreach ($biometricTypes as $type): ?>
                                <li class="mb-2">
                                    <?php if (in_array($type, $capturedTypes)): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <?php endif; ?>
                                    <?= $type ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="progress mb-3">
                                <?php 
                                $completionRate = (count($biometrics) / count($biometricTypes)) * 100;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $completionRate ?>%" 
                                     aria-valuenow="<?= $completionRate ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= round($completionRate) ?>%
                                </div>
                            </div>

                            <p class="text-muted small">
                                <?= count($biometrics) ?> of <?= count($biometricTypes) ?> biometrics captured
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/officers/biometrics?officer_id=' . $officer['id']) ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <a href="<?= url('/officers/biometrics/create?officer_id=' . $officer['id']) ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Capture New Biometric
                            </a>
                            <button type="button" class="btn btn-info btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
