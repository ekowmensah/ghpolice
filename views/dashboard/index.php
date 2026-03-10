<?php
$overview = $stats['overview'] ?? [];
$workflow = $stats['workflow'] ?? [];
$distribution = $stats['distribution'] ?? [];
$recent = $stats['recent'] ?? [];
$kpis = $stats['kpis'] ?? [];

$firstName = $_SESSION['user']['first_name'] ?? 'Officer';
$today = date('l, F j, Y');
$timeNow = date('g:i A');

ob_start();
?>
<style>
    .dash-hero {
        background: linear-gradient(120deg, #112c4d, #1a406d);
        border: 0;
        border-radius: 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 16px 35px rgba(11, 30, 54, 0.2);
    }

    .dash-hero::after {
        content: "";
        position: absolute;
        right: -35px;
        top: -25px;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(199, 161, 63, 0.38), rgba(199, 161, 63, 0));
    }

    .dash-hero h2 {
        color: #fff;
        margin: 0;
    }

    .dash-hero .meta {
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.95rem;
    }

    .kpi-card {
        border: 1px solid #dce5f0;
        border-radius: 12px;
        padding: 14px 14px 10px;
        background: #fff;
        height: 100%;
        box-shadow: 0 8px 18px rgba(15, 37, 64, 0.08);
    }

    .kpi-card .label {
        color: #5f7187;
        font-size: 0.86rem;
        text-transform: uppercase;
        letter-spacing: 0.45px;
        margin-bottom: 4px;
    }

    .kpi-card .value {
        color: #112c4d;
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .kpi-card .sub {
        color: #6f8095;
        font-size: 0.88rem;
    }

    .kpi-accent-cases { border-top: 4px solid #1a406d; }
    .kpi-accent-risk { border-top: 4px solid #d94a3a; }
    .kpi-accent-persons { border-top: 4px solid #1f7a3d; }
    .kpi-accent-assets { border-top: 4px solid #c7a13f; }

    .quick-grid a {
        border-radius: 10px;
        border: 1px solid #d8e2ef;
        display: block;
        padding: 10px 12px;
        color: #1d2f47;
        background: #fff;
        font-weight: 600;
    }

    .quick-grid a:hover {
        text-decoration: none;
        border-color: #b9cadf;
        background: #f7faff;
    }

    .module-card {
        border-radius: 12px;
        border: 1px solid #dce5f0;
        background: #fff;
    }

    .module-card .card-header {
        border-bottom: 1px solid #e8eef7;
        background: #fff;
    }

    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        border-bottom: 1px dashed #e8edf5;
        font-size: 0.94rem;
    }

    .metric-row:last-child {
        border-bottom: 0;
    }

    .metric-row .name {
        color: #51667f;
    }

    .metric-row .count {
        color: #12365f;
        font-weight: 700;
    }

    .metric-block {
        border: 1px solid #dce5f0;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }

    .metric-block .title {
        font-size: 0.9rem;
        color: #576b83;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .metric-block .score {
        font-size: 1.6rem;
        color: #112c4d;
        font-weight: 700;
        line-height: 1;
    }

    .list-compact .item {
        padding: 10px 0;
        border-bottom: 1px solid #edf2f8;
    }

    .list-compact .item:last-child {
        border-bottom: 0;
    }

    .list-compact .title {
        color: #152f4f;
        font-weight: 600;
    }

    .list-compact .meta {
        color: #6a7e95;
        font-size: 0.87rem;
    }

    .badge-risk {
        background: #fce7e4;
        color: #a93227;
        border: 1px solid #f0bcb4;
    }

    .badge-ops {
        background: #e8f0fb;
        color: #244f7f;
        border: 1px solid #bfd2ea;
    }

    .table th {
        color: #587089;
        font-size: 0.83rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .table td {
        color: #1e324b;
        vertical-align: middle;
    }

    .dist-bar .progress {
        height: 8px;
        border-radius: 999px;
        background: #e8eef7;
    }
</style>

<div class="card dash-hero mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h2><i class="fas fa-shield-alt mr-2"></i>National Command Dashboard</h2>
                <div class="meta mt-1">Welcome back, <strong><?= sanitize($firstName) ?></strong> • <?= sanitize($today) ?> • <?= sanitize($timeNow) ?></div>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge badge-light p-2">Unread Notifications: <?= (int)($workflow['public_services']['notifications_unread'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card kpi-accent-cases">
            <div class="label">Cases</div>
            <div class="value"><?= (int)($overview['total_cases'] ?? 0) ?></div>
            <div class="sub"><?= (int)($overview['open_cases'] ?? 0) ?> Open • <?= (int)($overview['investigating_cases'] ?? 0) ?> Under Investigation</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card kpi-accent-risk">
            <div class="label">Risk Pressure</div>
            <div class="value"><?= (int)($overview['high_priority_cases'] ?? 0) ?></div>
            <div class="sub">High/Critical Active Cases</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card kpi-accent-persons">
            <div class="label">Persons</div>
            <div class="value"><?= (int)($overview['total_persons'] ?? 0) ?></div>
            <div class="sub"><?= (int)($overview['wanted_persons'] ?? 0) ?> Wanted • <?= (int)($overview['missing_persons'] ?? 0) ?> Missing</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card kpi-accent-assets">
            <div class="label">Evidence & Assets</div>
            <div class="value"><?= (int)($overview['total_evidence'] ?? 0) ?></div>
            <div class="sub"><?= (int)($overview['total_vehicles'] ?? 0) ?> Vehicles • <?= (int)($overview['total_firearms'] ?? 0) ?> Firearms</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bolt mr-1"></i>Quick Actions</h3>
    </div>
    <div class="card-body">
        <div class="row quick-grid">
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/cases/create') ?>"><i class="fas fa-plus-circle mr-1"></i> New Case</a></div>
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/persons/create') ?>"><i class="fas fa-user-plus mr-1"></i> Register Person</a></div>
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/persons/crime-check') ?>"><i class="fas fa-search mr-1"></i> Crime Check</a></div>
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/missing-persons/create') ?>"><i class="fas fa-user-slash mr-1"></i> Missing Report</a></div>
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/public-complaints/create') ?>"><i class="fas fa-comment-dots mr-1"></i> Complaint Intake</a></div>
            <div class="col-lg-2 col-md-3 col-6 mb-2"><a href="<?= url('/intelligence/bulletins') ?>"><i class="fas fa-bullhorn mr-1"></i> Bulletins</a></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-network-wired mr-1"></i>Operational Workflows</h3>
            </div>
            <div class="card-body">
                <div class="metric-row"><span class="name">Active Operations</span><span class="count"><?= (int)($workflow['operations']['active_operations'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Patrols In Progress</span><span class="count"><?= (int)($workflow['operations']['patrols_in_progress'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Duty Roster (Today)</span><span class="count"><?= (int)($workflow['operations']['duty_scheduled_today'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Open Incidents</span><span class="count"><?= (int)($workflow['operations']['incident_open'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Active Officers</span><span class="count"><?= (int)($overview['active_officers'] ?? 0) ?> / <?= (int)($overview['total_officers'] ?? 0) ?></span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-balance-scale mr-1"></i>Legal & Intelligence Pulse</h3>
            </div>
            <div class="card-body">
                <div class="metric-row"><span class="name">Active Warrants</span><span class="count"><?= (int)($workflow['legal']['warrants_active'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Pending Charges</span><span class="count"><?= (int)($workflow['legal']['charges_pending'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Granted Bail Records</span><span class="count"><?= (int)($workflow['legal']['bail_active'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Active Intelligence Bulletins</span><span class="count"><?= (int)($workflow['intelligence']['active_bulletins'] ?? 0) ?></span></div>
                <div class="metric-row"><span class="name">Public Tips Pending Review</span><span class="count"><?= (int)($workflow['intelligence']['public_tips_pending'] ?? 0) ?></span></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="metric-block">
            <div class="title">Case Resolution Rate</div>
            <div class="score"><?= number_format((float)($kpis['case_resolution_rate'] ?? 0), 1) ?>%</div>
            <div class="progress mt-2"><div class="progress-bar bg-success" style="width: <?= min((float)($kpis['case_resolution_rate'] ?? 0), 100) ?>%"></div></div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="metric-block">
            <div class="title">Officer Availability</div>
            <div class="score"><?= number_format((float)($kpis['officer_availability_rate'] ?? 0), 1) ?>%</div>
            <div class="progress mt-2"><div class="progress-bar bg-info" style="width: <?= min((float)($kpis['officer_availability_rate'] ?? 0), 100) ?>%"></div></div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="metric-block">
            <div class="title">High-Priority Case Pressure</div>
            <div class="score"><?= number_format((float)($kpis['high_priority_pressure'] ?? 0), 1) ?>%</div>
            <div class="progress mt-2"><div class="progress-bar bg-danger" style="width: <?= min((float)($kpis['high_priority_pressure'] ?? 0), 100) ?>%"></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-folder-open mr-1"></i>Recent Cases</h3>
                <a href="<?= url('/cases') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($recent['cases'])): ?>
                        <?php foreach ($recent['cases'] as $case): ?>
                            <tr>
                                <td><a href="<?= url('/cases/' . (int)$case['id']) ?>"><?= sanitize($case['case_number'] ?? '-') ?></a></td>
                                <td><span class="badge badge-ops"><?= sanitize($case['status'] ?? '-') ?></span></td>
                                <td><span class="badge badge-risk"><?= sanitize($case['case_priority'] ?? '-') ?></span></td>
                                <td><?= !empty($case['created_at']) ? date('M j, Y', strtotime($case['created_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No case records available.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-siren-on mr-1"></i>Wanted / High-Risk Persons</h3>
                <a href="<?= url('/persons?wanted=1') ?>" class="btn btn-sm btn-outline-danger">View List</a>
            </div>
            <div class="card-body list-compact">
                <?php if (!empty($recent['alerts'])): ?>
                    <?php foreach ($recent['alerts'] as $alert): ?>
                        <div class="item">
                            <div class="d-flex justify-content-between">
                                <div class="title"><?= sanitize(trim(($alert['first_name'] ?? '') . ' ' . ($alert['last_name'] ?? ''))) ?></div>
                                <span class="badge badge-risk"><?= sanitize($alert['risk_level'] ?? 'Unknown') ?></span>
                            </div>
                            <div class="meta">Flagged wanted profile • <?= !empty($alert['created_at']) ? date('M j, Y', strtotime($alert['created_at'])) : '-' ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No wanted persons currently listed.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card module-card h-100">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i>Case Status Mix</h3></div>
            <div class="card-body dist-bar">
                <?php
                $totalCaseStatus = array_sum(array_map(static fn($r) => (int)$r['total'], $distribution['case_status'] ?? []));
                ?>
                <?php foreach (($distribution['case_status'] ?? []) as $row): ?>
                    <?php $pct = $totalCaseStatus > 0 ? round(((int)$row['total'] / $totalCaseStatus) * 100, 1) : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span><?= sanitize($row['status'] ?? '-') ?></span><span><?= (int)$row['total'] ?> (<?= $pct ?>%)</span></div>
                        <div class="progress"><div class="progress-bar bg-primary" style="width: <?= $pct ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card module-card h-100">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-circle mr-1"></i>Priority Mix</h3></div>
            <div class="card-body dist-bar">
                <?php
                $totalCasePriority = array_sum(array_map(static fn($r) => (int)$r['total'], $distribution['case_priority'] ?? []));
                ?>
                <?php foreach (($distribution['case_priority'] ?? []) as $row): ?>
                    <?php $pct = $totalCasePriority > 0 ? round(((int)$row['total'] / $totalCasePriority) * 100, 1) : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span><?= sanitize($row['case_priority'] ?? '-') ?></span><span><?= (int)$row['total'] ?> (<?= $pct ?>%)</span></div>
                        <div class="progress"><div class="progress-bar bg-warning" style="width: <?= $pct ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card module-card h-100">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tasks mr-1"></i>Operation Status</h3></div>
            <div class="card-body dist-bar">
                <?php
                $totalOperations = array_sum(array_map(static fn($r) => (int)$r['total'], $distribution['operation_status'] ?? []));
                ?>
                <?php foreach (($distribution['operation_status'] ?? []) as $row): ?>
                    <?php $pct = $totalOperations > 0 ? round(((int)$row['total'] / $totalOperations) * 100, 1) : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span><?= sanitize($row['operation_status'] ?? '-') ?></span><span><?= (int)$row['total'] ?> (<?= $pct ?>%)</span></div>
                        <div class="progress"><div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i>Recent Public Complaints</h3>
                <a href="<?= url('/public-complaints') ?>" class="btn btn-sm btn-outline-secondary">Open Module</a>
            </div>
            <div class="card-body list-compact">
                <?php if (!empty($recent['complaints'])): ?>
                    <?php foreach ($recent['complaints'] as $item): ?>
                        <div class="item">
                            <div class="d-flex justify-content-between">
                                <div class="title"><?= sanitize($item['complaint_number'] ?? '-') ?> - <?= sanitize($item['complainant_name'] ?? '-') ?></div>
                                <span class="badge badge-ops"><?= sanitize($item['complaint_status'] ?? '-') ?></span>
                            </div>
                            <div class="meta"><?= sanitize($item['complaint_type'] ?? '-') ?> • <?= !empty($item['created_at']) ? date('M j, Y', strtotime($item['created_at'])) : '-' ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No complaint records available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card module-card h-100">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-ambulance mr-1"></i>Recent Incident Reports</h3>
                <a href="<?= url('/incidents') ?>" class="btn btn-sm btn-outline-secondary">Open Module</a>
            </div>
            <div class="card-body list-compact">
                <?php if (!empty($recent['incidents'])): ?>
                    <?php foreach ($recent['incidents'] as $item): ?>
                        <div class="item">
                            <div class="d-flex justify-content-between">
                                <div class="title"><?= sanitize($item['incident_number'] ?? '-') ?> - <?= sanitize($item['incident_type'] ?? '-') ?></div>
                                <span class="badge badge-ops"><?= sanitize($item['status'] ?? '-') ?></span>
                            </div>
                            <div class="meta"><?= sanitize($item['incident_location'] ?? 'Unknown Location') ?> • <?= !empty($item['created_at']) ? date('M j, Y', strtotime($item['created_at'])) : '-' ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No incident records available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
