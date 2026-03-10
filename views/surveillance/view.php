<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Surveillance Operation Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/surveillance') ?>">Surveillance</a></li>
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
                        <div class="card-header bg-dark">
                            <h3 class="card-title">Surveillance Information</h3>
                            <div class="card-tools">
                                <?php
                                $statusClass = match($surveillance['operation_status']) {
                                    'Planned' => 'info',
                                    'In Progress' => 'warning',
                                    'Completed' => 'success',
                                    'Suspended' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge badge-<?= $statusClass ?> badge-lg">
                                    <?= sanitize($surveillance['operation_status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Operation Code:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($surveillance['operation_code']) ?></strong></dd>

                                <dt class="col-sm-4">Operation Name:</dt>
                                <dd class="col-sm-8"><h5><?= sanitize($surveillance['operation_name']) ?></h5></dd>

                                <dt class="col-sm-4">Surveillance Type:</dt>
                                <dd class="col-sm-8"><?= sanitize($surveillance['surveillance_type']) ?></dd>

                                <dt class="col-sm-4">Target Description:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($surveillance['target_description'])) ?></dd>

                                <?php if ($surveillance['target_location']): ?>
                                <dt class="col-sm-4">Target Location:</dt>
                                <dd class="col-sm-8"><?= sanitize($surveillance['target_location']) ?></dd>
                                <?php endif; ?>

                                <dt class="col-sm-4">Start Date:</dt>
                                <dd class="col-sm-8"><?= date('l, d F Y', strtotime($surveillance['start_date'])) ?></dd>

                                <?php if ($surveillance['end_date']): ?>
                                <dt class="col-sm-4">End Date:</dt>
                                <dd class="col-sm-8"><?= date('d M Y', strtotime($surveillance['end_date'])) ?></dd>
                                <?php endif; ?>

                                <?php if ($surveillance['case_number']): ?>
                                <dt class="col-sm-4">Related Case:</dt>
                                <dd class="col-sm-8">
                                    <a href="<?= url('/cases/view/' . $surveillance['case_id']) ?>">
                                        <?= sanitize($surveillance['case_number']) ?>
                                    </a>
                                </dd>
                                <?php endif; ?>

                                <?php if ($surveillance['objectives']): ?>
                                <dt class="col-sm-4">Objectives:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($surveillance['objectives'])) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Surveillance Team</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Officer</th>
                                        <th>Rank</th>
                                        <th>Service Number</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_members as $member): ?>
                                    <tr>
                                        <td><?= sanitize($member['officer_name']) ?></td>
                                        <td><?= sanitize($member['rank_name']) ?></td>
                                        <td><?= sanitize($member['service_number']) ?></td>
                                        <td><?= sanitize($member['role_in_surveillance'] ?? 'Team Member') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-dark">
                        <div class="card-header">
                            <h3 class="card-title">Operation Commander</h3>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Name:</dt>
                                <dd><?= sanitize($surveillance['commander_name']) ?></dd>

                                <dt>Rank:</dt>
                                <dd><?= sanitize($surveillance['rank_name']) ?></dd>

                                <dt>Service Number:</dt>
                                <dd><?= sanitize($surveillance['service_number']) ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Classified</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">
                                <strong>CONFIDENTIAL OPERATION</strong><br>
                                Access restricted to authorized personnel only.
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/surveillance') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button class="btn btn-info btn-block" id="updateStatusBtn">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                            <button type="button" class="btn btn-dark btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
