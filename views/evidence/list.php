<?php
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Evidence Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="evidenceTable">
                        <thead>
                            <tr>
                                <th>Evidence #</th>
                                <th>Case Number</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Collection Date</th>
                                <th>Status</th>
                                <th>Current Holder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($evidence)): ?>
                                <?php foreach ($evidence as $item): ?>
                                    <tr>
                                        <td><?= sanitize($item['evidence_number'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="<?= url('/cases/' . ($item['case_id'] ?? '')) ?>">
                                                <?= sanitize($item['case_number'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td><?= sanitize($item['evidence_type'] ?? 'N/A') ?></td>
                                        <td><?= sanitize(substr($item['description'] ?? '', 0, 50)) ?>...</td>
                                        <td><?= date('d M Y', strtotime($item['collection_date'] ?? 'now')) ?></td>
                                        <td>
                                            <span class="badge badge-<?= ($item['status'] ?? '') === 'In Custody' ? 'success' : 'warning' ?>">
                                                <?= sanitize($item['status'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td><?= sanitize($item['current_holder_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="<?= url('/evidence/' . ($item['id'] ?? '')) ?>" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/evidence/custody-chain?evidence_id=' . ($item['id'] ?? '')) ?>" class="btn btn-warning btn-sm" title="Custody Chain">
                                                <i class="fas fa-link"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No evidence found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
echo view('layouts/main', ['content' => $content, 'title' => $title ?? 'Evidence Management']);
