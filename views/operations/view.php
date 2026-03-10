<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Operation Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/operations') ?>">Operations</a></li>
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
                        <div class="card-header">
                            <h3 class="card-title">Operation Information</h3>
                            <div class="card-tools">
                                <?php
                                $statusClass = match($operation['operation_status']) {
                                    'Planned' => 'info',
                                    'In Progress' => 'warning',
                                    'Completed' => 'success',
                                    'Cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge badge-<?= $statusClass ?> badge-lg">
                                    <?= sanitize($operation['operation_status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Operation Code:</dt>
                                <dd class="col-sm-8"><strong><?= sanitize($operation['operation_code']) ?></strong></dd>

                                <dt class="col-sm-4">Operation Name:</dt>
                                <dd class="col-sm-8"><h5><?= sanitize($operation['operation_name']) ?></h5></dd>

                                <dt class="col-sm-4">Operation Type:</dt>
                                <dd class="col-sm-8"><?= sanitize($operation['operation_type']) ?></dd>

                                <dt class="col-sm-4">Date & Time:</dt>
                                <dd class="col-sm-8"><?= date('l, d F Y H:i', strtotime($operation['operation_date'] . ' ' . $operation['start_time'])) ?></dd>

                                <dt class="col-sm-4">Target Location:</dt>
                                <dd class="col-sm-8"><?= sanitize($operation['target_location']) ?></dd>

                                <dt class="col-sm-4">Station:</dt>
                                <dd class="col-sm-8"><?= sanitize($operation['station_name']) ?></dd>

                                <?php if ($operation['case_number']): ?>
                                <dt class="col-sm-4">Related Case:</dt>
                                <dd class="col-sm-8">
                                    <a href="<?= url('/cases/view/' . $operation['case_id']) ?>">
                                        <?= sanitize($operation['case_number']) ?>
                                    </a>
                                </dd>
                                <?php endif; ?>

                                <?php if ($operation['objectives']): ?>
                                <dt class="col-sm-4">Objectives:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($operation['objectives'])) ?></dd>
                                <?php endif; ?>

                                <?php if ($operation['operation_status'] === 'Completed'): ?>
                                <dt class="col-sm-4">End Time:</dt>
                                <dd class="col-sm-8"><?= date('d M Y H:i', strtotime($operation['end_time'])) ?></dd>

                                <dt class="col-sm-4">Outcome:</dt>
                                <dd class="col-sm-8"><?= nl2br(sanitize($operation['outcome_summary'])) ?></dd>

                                <dt class="col-sm-4">Arrests Made:</dt>
                                <dd class="col-sm-8"><strong><?= $operation['arrests_made'] ?? 0 ?></strong></dd>

                                <dt class="col-sm-4">Exhibits Seized:</dt>
                                <dd class="col-sm-8"><strong><?= $operation['exhibits_seized'] ?? 0 ?></strong></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Team Members</h3>
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
                                        <td><?= sanitize($member['role_in_operation'] ?? 'Team Member') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Operation Commander</h3>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Name:</dt>
                                <dd><?= sanitize($operation['commander_name']) ?></dd>

                                <dt>Rank:</dt>
                                <dd><?= sanitize($operation['rank_name']) ?></dd>

                                <dt>Service Number:</dt>
                                <dd><?= sanitize($operation['service_number']) ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('/operations') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <?php if ($operation['operation_status'] === 'Planned'): ?>
                            <button class="btn btn-success btn-block" id="startOperationBtn">
                                <i class="fas fa-play"></i> Start Operation
                            </button>
                            <?php endif; ?>
                            <?php if ($operation['operation_status'] === 'In Progress'): ?>
                            <button class="btn btn-primary btn-block" id="completeOperationBtn">
                                <i class="fas fa-check"></i> Complete Operation
                            </button>
                            <?php endif; ?>
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

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
$('#startOperationBtn').click(function() {
    Swal.fire({
        title: 'Start Operation?',
        text: 'This will mark the operation as in progress',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Start',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('/operations/start/' . $operation['id']) ?>', {
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

$('#completeOperationBtn').click(function() {
    Swal.fire({
        title: 'Complete Operation',
        html: `
            <textarea id="outcome" class="swal2-textarea" placeholder="Outcome summary"></textarea>
            <input type="number" id="arrests" class="swal2-input" placeholder="Arrests made" min="0">
            <input type="number" id="exhibits" class="swal2-input" placeholder="Exhibits seized" min="0">
        `,
        showCancelButton: true,
        confirmButtonText: 'Complete',
        confirmButtonColor: '#007bff',
        preConfirm: () => {
            return {
                outcome_summary: document.getElementById('outcome').value,
                arrests_made: document.getElementById('arrests').value,
                exhibits_seized: document.getElementById('exhibits').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('/operations/complete/' . $operation['id']) ?>', {
                csrf_token: '<?= csrf_token() ?>',
                ...result.value
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
