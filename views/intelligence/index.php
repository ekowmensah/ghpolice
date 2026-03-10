<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['active_reports'] ?? 0 ?></h3>
                            <p>Active Reports</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <a href="<?= url('/intelligence/reports') ?>" class="small-box-footer">
                            View Reports <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $stats['active_surveillance'] ?? 0 ?></h3>
                            <p>Active Operations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-binoculars"></i>
                        </div>
                        <a href="<?= url('/intelligence/surveillance') ?>" class="small-box-footer">
                            View Operations <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $stats['active_bulletins'] ?? 0 ?></h3>
                            <p>Active Bulletins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <a href="<?= url('/intelligence/bulletins') ?>" class="small-box-footer">
                            View Bulletins <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['pending_tips'] ?? 0 ?></h3>
                            <p>Pending Tips</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <a href="<?= url('/intelligence/tips') ?>" class="small-box-footer">
                            View Tips <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Intelligence Reports</h3>
                            <div class="card-tools">
                                <a href="<?= url('/intelligence/reports/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> New Report
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recent_reports)): ?>
                                <p class="p-3 text-muted">No recent reports</p>
                            <?php else: ?>
                                <table class="table table-sm">
                                    <tbody>
                                        <?php foreach ($recent_reports as $report): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= url('/intelligence/reports/' . $report['id']) ?>">
                                                        <?= htmlspecialchars($report['report_number']) ?>
                                                    </a>
                                                    <br>
                                                    <small><?= htmlspecialchars($report['title']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info"><?= htmlspecialchars($report['report_type']) ?></span>
                                                </td>
                                                <td><small><?= date('Y-m-d', strtotime($report['report_date'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Active Surveillance Operations</h3>
                            <div class="card-tools">
                                <a href="<?= url('/intelligence/surveillance/create') ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-plus"></i> New Operation
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($active_surveillance)): ?>
                                <p class="p-3 text-muted">No active operations</p>
                            <?php else: ?>
                                <table class="table table-sm">
                                    <tbody>
                                        <?php foreach ($active_surveillance as $op): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= url('/intelligence/surveillance/' . $op['id']) ?>">
                                                        <?= htmlspecialchars($op['operation_code']) ?>
                                                    </a>
                                                    <br>
                                                    <small><?= htmlspecialchars($op['operation_name']) ?></small>
                                                </td>
                                                <td><small><?= htmlspecialchars($op['commander_name']) ?></small></td>
                                                <td><small><?= date('Y-m-d', strtotime($op['start_date'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Intelligence Bulletins</h3>
                    <div class="card-tools">
                        <a href="<?= url('/intelligence/bulletins/create') ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-plus"></i> New Bulletin
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bulletins)): ?>
                        <p class="text-muted">No recent bulletins</p>
                    <?php else: ?>
                        <?php foreach ($recent_bulletins as $bulletin): ?>
                            <div class="alert alert-<?= $bulletin['priority'] == 'High' ? 'danger' : ($bulletin['priority'] == 'Medium' ? 'warning' : 'info') ?>">
                                <h5><?= htmlspecialchars($bulletin['title']) ?></h5>
                                <p class="mb-0"><?= htmlspecialchars(substr($bulletin['content'], 0, 200)) ?>...</p>
                                <small>
                                    <strong><?= htmlspecialchars($bulletin['bulletin_type']) ?></strong> | 
                                    Valid: <?= date('Y-m-d', strtotime($bulletin['valid_from'])) ?>
                                    <?php if ($bulletin['valid_until']): ?>
                                        to <?= date('Y-m-d', strtotime($bulletin['valid_until'])) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Intelligence Dashboard';
$breadcrumbs = [
    ['title' => 'Intelligence']
];

include __DIR__ . '/../layouts/main.php';
?>
