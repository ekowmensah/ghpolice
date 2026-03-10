<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Leave' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Leave</li>
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
                    <h3 class="card-title">Leave Records</h3>
                    <div class="card-tools">
                        <a href="<?= url('/officers/leave/create') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Request Leave
                        </a>
                        <div class="btn-group ml-2">
                            <a href="<?= url('/officers/leave?status=pending') ?>" class="btn btn-sm btn-warning">Pending</a>
                            <a href="<?= url('/officers/leave?status=active') ?>" class="btn btn-sm btn-success">Active</a>
                            <a href="<?= url('/officers/leave') ?>" class="btn btn-sm btn-secondary">All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="leaveTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Days</th>
                                <th>Station</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave): ?>
                            <tr>
                                <td><?= sanitize($leave['officer_name'] ?? '') ?> (<?= sanitize($leave['rank_name'] ?? '') ?>)</td>
                                <td><?= sanitize($leave['leave_type']) ?></td>
                                <td><?= date('d M Y', strtotime($leave['start_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($leave['end_date'])) ?></td>
                                <td><strong><?= $leave['total_days'] ?> days</strong></td>
                                <td><?= sanitize($leave['station_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($leave['leave_status']) {
                                        'Pending' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Cancelled' => 'secondary',
                                        default => 'info'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= sanitize($leave['leave_status']) ?>
                                    </span>
                                </td>
                                <td><?= sanitize($leave['approved_by_name'] ?? 'Pending') ?></td>
                                <td>
                                    <?php if ($leave['leave_status'] === 'Pending'): ?>
                                    <button class="btn btn-sm btn-success approve-leave" data-id="<?= $leave['id'] ?>">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-leave" data-id="<?= $leave['id'] ?>">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
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
    $('#leaveTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[2, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#leaveTable_wrapper .col-md-6:eq(0)');
});
</script>
