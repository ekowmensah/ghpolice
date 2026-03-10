<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Operations</h3>
                    <div class="card-tools">
                        <a href="<?= url('/intelligence/surveillance/create') ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus"></i> New Operation
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Active" <?= $selected_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Completed" <?= $selected_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Suspended" <?= $selected_status == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($operations)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No surveillance operations found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Operation Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Lead Officer</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($operations as $op): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($op['operation_code']) ?></strong></td>
                                            <td><?= htmlspecialchars($op['operation_name']) ?></td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars($op['operation_type']) ?></span></td>
                                            <td><?= htmlspecialchars($op['location']) ?></td>
                                            <td><?= htmlspecialchars($op['commander_name']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($op['start_date'])) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Active' => 'success',
                                                    'Completed' => 'info',
                                                    'Suspended' => 'warning'
                                                ];
                                                $class = $statusClass[$op['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($op['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= url('/intelligence/surveillance/' . $op['id']) ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Surveillance Operations';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Surveillance']
];

include __DIR__ . '/../layouts/main.php';
?>
