<?php
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i> Transfer Custody - Evidence #<?= $evidence['evidence_number'] ?? $evidence['id'] ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= url('/evidence/custody-chain?evidence_id=' . $evidence['id']) ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Custody Chain
                        </a>
                    </div>
                </div>
                <form method="POST" action="<?= url('/evidence/custody-transfer') ?>" id="transferForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="evidence_id" value="<?= $evidence['id'] ?>">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Current Custodian:</strong>
                            <?= sanitize($current_holder['first_name'] ?? 'N/A') ?> 
                            <?= sanitize($current_holder['last_name'] ?? '') ?>
                            <?php if (!empty($current_holder['service_number'])): ?>
                                (<?= sanitize($current_holder['service_number']) ?>)
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Transfer From (Officer)</label>
                            <select class="form-control" name="transferred_from" required>
                                <option value="">-- Select Officer --</option>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?= $officer['id'] ?>" <?= ($current_holder['id'] ?? null) == $officer['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($officer['first_name'] . ' ' . $officer['last_name']) ?> - <?= sanitize($officer['service_number'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Transfer To (Officer)</label>
                            <select class="form-control" name="transferred_to" required>
                                <option value="">-- Select Officer --</option>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?= $officer['id'] ?>">
                                        <?= sanitize($officer['first_name'] . ' ' . $officer['last_name']) ?> - <?= sanitize($officer['service_number'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Transfer Date & Time</label>
                                <input type="datetime-local" name="transfer_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Central Evidence Locker" value="<?= sanitize($current_holder['location'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Purpose of Transfer</label>
                            <select class="form-control" name="purpose">
                                <option value="Court Presentation">Court Presentation</option>
                                <option value="Laboratory Analysis">Laboratory Analysis</option>
                                <option value="Storage Relocation">Storage Relocation</option>
                                <option value="Investigative Review">Investigative Review</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Enter any details about this transfer"></textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
echo view('layouts/main', ['content' => $content, 'title' => $title ?? 'Record Custody Transfer']);
