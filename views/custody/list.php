<?php
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Custody Chain Management</h3>
                    <div class="card-tools">
                        <a href="<?= url('/evidence') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-box"></i> View All Evidence
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Select evidence below to view or manage its custody chain.
                    </div>
                    
                    <table class="table table-bordered table-striped" id="custodyTable">
                        <thead>
                            <tr>
                                <th>Evidence #</th>
                                <th>Case Number</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Current Holder</th>
                                <th>Last Transfer</th>
                                <th>Status</th>
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
                                        <td><?= sanitize(substr($item['description'] ?? '', 0, 40)) ?>...</td>
                                        <td>
                                            <strong><?= sanitize($item['current_holder_name'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td><?= isset($item['last_transfer_date']) ? date('d M Y H:i', strtotime($item['last_transfer_date'])) : 'N/A' ?></td>
                                        <td>
                                            <span class="badge badge-<?= ($item['status'] ?? '') === 'In Custody' ? 'success' : 'warning' ?>">
                                                <?= sanitize($item['status'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= url('/evidence/custody-chain?evidence_id=' . ($item['id'] ?? '')) ?>" class="btn btn-info btn-sm" title="View Chain">
                                                <i class="fas fa-link"></i> Chain
                                            </a>
                                            <a href="<?= url('/evidence/custody-transfer?evidence_id=' . ($item['id'] ?? '')) ?>" class="btn btn-warning btn-sm" title="Transfer Custody">
                                                <i class="fas fa-exchange-alt"></i> Transfer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No evidence found in the system</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#custodyTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[5, "desc"]]
    });
});
</script>
<?php
$content = ob_get_clean();
echo view('layouts/main', ['content' => $content, 'title' => $title ?? 'Custody Chain Management']);
