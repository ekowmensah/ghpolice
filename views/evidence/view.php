<?php
ob_start();
$status = $evidence['status'] ?? 'Unknown';
$statusBadge = match($status) {
    'Collected' => 'info',
    'In Storage' => 'primary',
    'In Lab' => 'warning',
    'In Court' => 'success',
    default => 'secondary'
};
?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Evidence Details</h3>
            </div>
            <div class="card-body">
                <strong>Evidence ID</strong>
                <p class="text-muted">#<?= $evidence['id'] ?></p>

                <strong>Type</strong>
                <p class="text-muted"><?= sanitize($evidence['evidence_type'] ?? 'N/A') ?></p>

                <strong>Status</strong>
                <p><span class="badge badge-<?= $statusBadge ?>"><?= sanitize($status) ?></span></p>

                <strong>Collected By</strong>
                <p class="text-muted"><?= sanitize($evidence['collected_by_name'] ?? 'N/A') ?></p>

                <strong>Collection Date</strong>
                <p class="text-muted"><?= isset($evidence['collection_date']) ? date('d M Y H:i', strtotime($evidence['collection_date'])) : 'N/A' ?></p>

                <strong>Collection Location</strong>
                <p class="text-muted"><?= sanitize($evidence['collection_location'] ?? 'N/A') ?></p>

                <strong>Storage Location</strong>
                <p class="text-muted"><?= sanitize($evidence['storage_location'] ?? 'N/A') ?></p>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#transferModal">
                    <i class="fas fa-exchange-alt"></i> Transfer Custody
                </button>
                <button class="btn btn-warning btn-block" data-toggle="modal" data-target="#statusModal">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Evidence Description</h3>
            </div>
            <div class="card-body">
                <p><?= nl2br(sanitize($evidence['description'] ?? $evidence['evidence_description'] ?? 'No description')) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Custody Chain</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if (!empty($custodyChain)): ?>
                        <?php foreach ($custodyChain as $entry): ?>
                            <?php
                            $iconClass = match($entry['action_taken'] ?? 'Unknown') {
                                'Initial Collection' => 'bg-success',
                                'Transfer' => 'bg-info',
                                'Status Change' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                            ?>
                            <div>
                                <i class="fas fa-circle <?= $iconClass ?>"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= isset($entry['custody_start_date']) ? date('d M Y H:i', strtotime($entry['custody_start_date'])) : 'N/A' ?></span>
                                    <h3 class="timeline-header"><?= sanitize($entry['action_taken'] ?? 'Unknown') ?></h3>
                                    <div class="timeline-body">
                                        <strong>Custodian:</strong> <?= sanitize($entry['custodian_name'] ?? 'N/A') ?><br>
                                        <strong>Notes:</strong> <?= sanitize($entry['notes'] ?? 'N/A') ?>
                                    </div>
                                    <?php if (!empty($entry['custody_end_date'])): ?>
                                        <div class="timeline-footer">
                                            <small>Ended: <?= date('d M Y H:i', strtotime($entry['custody_end_date'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No custody chain entries</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Custody Modal -->
<div class="modal fade" id="transferModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transfer Custody</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="transferForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Transfer To (Officer ID)</label>
                        <input type="number" class="form-control" name="transferred_to" required>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea class="form-control" name="transfer_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitTransfer()">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Evidence Status</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="statusForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>New Status</label>
                        <select class="form-control" name="status" required>
                            <option value="Collected">Collected</option>
                            <option value="In Storage">In Storage</option>
                            <option value="In Lab">In Lab</option>
                            <option value="In Court">In Court</option>
                            <option value="Returned">Returned</option>
                            <option value="Destroyed">Destroyed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="submitStatus()">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitTransfer() {
    const formData = new FormData(document.getElementById("transferForm"));
    fetch("<?= url('/evidence/' . $evidence['id'] . '/transfer') ?>", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert(data.message);
          }
      });
}

function submitStatus() {
    const formData = new FormData(document.getElementById("statusForm"));
    fetch("<?= url('/evidence/' . $evidence['id'] . '/status') ?>", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert(data.message);
          }
      });
}
</script>
<?php
$content = ob_get_clean();
$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => 'Evidence #' . $evidence['id']]
];
echo view('layouts/main', ['content' => $content, 'title' => $title ?? 'Evidence Details', 'breadcrumbs' => $breadcrumbs]);
?>
