<?php
ob_start();

$status = $evidence['status'] ?? 'Unknown';
$statusBadge = match ($status) {
    'Collected' => 'info',
    'In Storage' => 'primary',
    'In Lab' => 'warning',
    'In Court' => 'success',
    default => 'secondary'
};

$transferCount = count($chain ?? []);
$latestTransfer = $transferCount > 0 ? $chain[0] : null;
?>
<style>
    .custody-hero {
        background: linear-gradient(135deg, #0f4c75, #1b262c);
        border-radius: 18px;
        color: #fff;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 76, 117, 0.35);
    }
    .custody-hero::after {
        content: "";
        position: absolute;
        top: -40%;
        right: -10%;
        width: 60%;
        height: 180%;
        background: rgba(255, 255, 255, 0.08);
        transform: rotate(-12deg);
    }
    .custody-hero h1 {
        font-weight: 700;
        letter-spacing: .5px;
    }
    .hero-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    .hero-pill {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        padding: 1rem 1.2rem;
        backdrop-filter: blur(8px);
    }
    .hero-pill span {
        display: block;
        font-size: .85rem;
        opacity: .8;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .hero-pill strong {
        font-size: 1.1rem;
    }
    .card-modern {
        border: none;
        border-radius: 18px;
        box-shadow: 0 18px 35px rgba(15, 76, 117, 0.08);
    }
    .timeline-stream {
        border-left: 2px solid #0f4c75;
        margin-left: 1rem;
        padding-left: 2.5rem;
    }
    .timeline-node {
        position: relative;
        margin-bottom: 2.5rem;
    }
    .timeline-node::before {
        content: "";
        position: absolute;
        left: -2.6rem;
        top: 0.4rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid #fff;
        background: #0f4c75;
        box-shadow: 0 0 0 6px rgba(15, 76, 117, 0.15);
    }
    .timeline-node.latest::before {
        background: #06d6a0;
        box-shadow: 0 0 0 8px rgba(6, 214, 160, 0.25);
    }
    .timeline-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #edf2f4;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .timeline-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(15, 76, 117, 0.12);
    }
    .stat-pill {
        border-radius: 16px;
        padding: 1.5rem;
        color: #fff;
    }
    .stat-pill h6 {
        letter-spacing: .08em;
        font-size: .8rem;
        text-transform: uppercase;
    }
    .stat-pill strong {
        font-size: 1.8rem;
        display: block;
    }
    .action-bar a {
        border-radius: 14px;
        padding: .8rem 1.3rem;
    }
</style>

<div class="container-fluid pb-4">
    <div class="custody-hero mt-2 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
                <span class="badge badge-<?= $statusBadge ?> px-3 py-2 mb-2">Current Status: <?= sanitize($status) ?></span>
                <h1>Evidence #<?= sanitize($evidence['evidence_number'] ?? $evidence['id']) ?></h1>
                <p class="mb-0">Custody chain overview for <?= sanitize($evidence['evidence_type'] ?? 'Unknown Type') ?></p>
            </div>
            <div class="action-bar">
                <a href="<?= url('/evidence/' . $evidence['id']) ?>" class="btn btn-outline-light mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Evidence
                </a>
                <a href="<?= url('/evidence/custody-transfer?evidence_id=' . $evidence['id']) ?>" class="btn btn-light text-primary">
                    <i class="fas fa-exchange-alt mr-1"></i> Transfer Custody
                </a>
            </div>
        </div>
        <div class="hero-meta">
            <div class="hero-pill">
                <span>Case Number</span>
                <strong>
                    <?php if (!empty($evidence['case_id'])): ?>
                        <a href="<?= url('/cases/' . $evidence['case_id']) ?>" class="text-white text-decoration-underline">
                            <?= sanitize($evidence['case_number'] ?? 'N/A') ?>
                        </a>
                    <?php else: ?>
                        <?= sanitize($evidence['case_number'] ?? 'N/A') ?>
                    <?php endif; ?>
                </strong>
            </div>
            <div class="hero-pill">
                <span>Current Holder</span>
                <strong><?= sanitize(trim(($current_holder['first_name'] ?? '') . ' ' . ($current_holder['last_name'] ?? '')) ?: 'N/A') ?></strong>
            </div>
            <div class="hero-pill">
                <span>Total Transfers</span>
                <strong><?= $transferCount ?></strong>
            </div>
            <div class="hero-pill">
                <span>Last Transfer</span>
                <strong><?= $latestTransfer ? date('d M Y • H:i', strtotime($latestTransfer['transfer_date'])) : 'N/A' ?></strong>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-modern card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-stream text-primary mr-2"></i> Custody Timeline</h5>
                        <?php if ($transferCount): ?>
                            <span class="text-muted small">Chronological from newest to oldest</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($transferCount): ?>
                        <div class="timeline-stream">
                            <?php foreach ($chain as $index => $entry): ?>
                                <?php $isLatest = ($index === 0); ?>
                                <div class="timeline-node <?= $isLatest ? 'latest' : '' ?>">
                                    <div class="timeline-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="badge badge-pill badge-<?= $isLatest ? 'success' : 'info' ?>">
                                                    <?= $isLatest ? 'Current Custody' : 'Historical Transfer' ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= isset($entry['transfer_date']) ? date('d M Y • H:i', strtotime($entry['transfer_date'])) : 'N/A' ?>
                                            </small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted small mb-1">Transferred From</label>
                                                <div class="font-weight-semibold">
                                                    <?= sanitize(trim(($entry['from_first'] ?? '') . ' ' . ($entry['from_last'] ?? ''))) ?: 'N/A' ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="text-muted small mb-1">Transferred To</label>
                                                <div class="font-weight-semibold">
                                                    <?= sanitize(trim(($entry['to_first'] ?? '') . ' ' . ($entry['to_last'] ?? ''))) ?: 'N/A' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <?php if (!empty($entry['purpose'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <label class="text-muted small mb-1">Purpose</label>
                                                    <div><?= sanitize($entry['purpose']) ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($entry['location'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <label class="text-muted small mb-1">Location</label>
                                                    <div><?= sanitize($entry['location']) ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($entry['notes'])): ?>
                                            <div class="mt-2 p-3 bg-light rounded">
                                                <label class="text-muted small mb-1 d-block">Notes</label>
                                                <div><?= sanitize($entry['notes']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No custody records have been captured for this evidence item yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-modern card mb-4">
                <div class="card-body">
                    <h5><i class="fas fa-fingerprint text-primary mr-2"></i> Evidence Snapshot</h5>
                    <div class="d-flex flex-column gap-3 mt-3">
                        <div>
                            <small class="text-muted">Evidence Type</small>
                            <div class="font-weight-semibold"><?= sanitize($evidence['evidence_type'] ?? 'N/A') ?></div>
                        </div>
                        <div>
                            <small class="text-muted">Collected By</small>
                            <div><?= sanitize($evidence['collected_by_name'] ?? 'N/A') ?></div>
                        </div>
                        <div>
                            <small class="text-muted">Collection Date</small>
                            <div><?= isset($evidence['collection_date']) ? date('d M Y • H:i', strtotime($evidence['collection_date'])) : 'N/A' ?></div>
                        </div>
                        <div>
                            <small class="text-muted">Storage Location</small>
                            <div><?= sanitize($evidence['storage_location'] ?? 'Not specified') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-pill mb-3" style="background: linear-gradient(135deg, #06d6a0, #1b9aaa);">
                <h6>Total Time in Current Custody</h6>
                <strong>
                    <?php
                    if ($latestTransfer && isset($latestTransfer['transfer_date'])) {
                        $diff = time() - strtotime($latestTransfer['transfer_date']);
                        $days = floor($diff / 86400);
                        $hours = floor(($diff % 86400) / 3600);
                        echo "{$days}d {$hours}h";
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </strong>
                <span class="small d-block opacity-75">Since last recorded transfer</span>
            </div>

            <div class="stat-pill" style="background: linear-gradient(135deg, #f6b93b, #f0932b);">
                <h6>Chain Integrity</h6>
                <strong><?= $transferCount ? 'Verified' : 'Awaiting Data' ?></strong>
                <span class="small d-block opacity-75">
                    <?= $transferCount ? 'All transfers logged with custodians' : 'No custody records yet' ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
echo view('layouts/main', [
    'content' => $content,
    'title' => $title ?? 'Custody Chain'
]);
