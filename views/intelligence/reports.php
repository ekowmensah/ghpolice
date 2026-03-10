<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Intelligence Reports</h3>
                    <div class="card-tools">
                        <a href="<?= url('/intelligence/reports/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Strategic" <?= $selected_type == 'Strategic' ? 'selected' : '' ?>>Strategic</option>
                                    <option value="Tactical" <?= $selected_type == 'Tactical' ? 'selected' : '' ?>>Tactical</option>
                                    <option value="Operational" <?= $selected_type == 'Operational' ? 'selected' : '' ?>>Operational</option>
                                    <option value="Crime Pattern" <?= $selected_type == 'Crime Pattern' ? 'selected' : '' ?>>Crime Pattern</option>
                                    <option value="Threat Assessment" <?= $selected_type == 'Threat Assessment' ? 'selected' : '' ?>>Threat Assessment</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="classification" class="form-control">
                                    <option value="">All Classifications</option>
                                    <option value="Unclassified" <?= $selected_classification == 'Unclassified' ? 'selected' : '' ?>>Unclassified</option>
                                    <option value="Confidential" <?= $selected_classification == 'Confidential' ? 'selected' : '' ?>>Confidential</option>
                                    <option value="Secret" <?= $selected_classification == 'Secret' ? 'selected' : '' ?>>Secret</option>
                                    <option value="Top Secret" <?= $selected_classification == 'Top Secret' ? 'selected' : '' ?>>Top Secret</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($reports)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No intelligence reports found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Report Number</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Classification</th>
                                        <th>Date</th>
                                        <th>Prepared By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($report['report_number']) ?></strong></td>
                                            <td><?= htmlspecialchars($report['title']) ?></td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($report['report_type']) ?></span></td>
                                            <td>
                                                <?php
                                                $classClass = [
                                                    'Unclassified' => 'success',
                                                    'Confidential' => 'warning',
                                                    'Secret' => 'danger',
                                                    'Top Secret' => 'dark'
                                                ];
                                                $class = $classClass[$report['classification']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($report['classification']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($report['report_date'])) ?></td>
                                            <td><?= htmlspecialchars($report['created_by_name']) ?></td>
                                            <td><span class="badge badge-primary"><?= htmlspecialchars($report['status']) ?></span></td>
                                            <td>
                                                <a href="<?= url('/intelligence/reports/' . $report['id']) ?>" class="btn btn-sm btn-info">
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

$title = 'Intelligence Reports';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Reports']
];

include __DIR__ . '/../layouts/main.php';
?>
