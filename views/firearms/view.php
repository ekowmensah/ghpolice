<?php
$statusClass = match($firearm['firearm_status']) {
    'In Service' => 'success',
    'In Armory' => 'info',
    'Under Repair' => 'warning',
    'Decommissioned' => 'secondary',
    'Lost', 'Stolen' => 'danger',
    default => 'secondary'
};

$statusIcon = match($firearm['firearm_status']) {
    'In Service' => 'fa-check-circle',
    'In Armory' => 'fa-warehouse',
    'Under Repair' => 'fa-wrench',
    'Decommissioned' => 'fa-ban',
    'Lost' => 'fa-question-circle',
    'Stolen' => 'fa-exclamation-triangle',
    default => 'fa-crosshairs'
};

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-' . $statusClass . '">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-crosshairs"></i> Firearm Details
                        </h3>
                        <p class="text-muted mb-0 mt-1">
                            <small><i class="fas fa-barcode"></i> ' . htmlspecialchars($firearm['serial_number']) . ' | 
                            <i class="fas fa-calendar"></i> Registered: ' . date('M j, Y', strtotime($firearm['created_at'])) . '</small>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge badge-' . $statusClass . ' badge-lg" style="font-size: 1.1em; padding: 8px 15px;">
                            <i class="fas ' . $statusIcon . '"></i> ' . htmlspecialchars($firearm['firearm_status']) . '
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Firearm Details Card -->
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Firearm Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Serial Number:</dt>
                            <dd class="col-sm-7"><strong>' . htmlspecialchars($firearm['serial_number']) . '</strong></dd>
                            
                            <dt class="col-sm-5">Type:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['firearm_type']) . '</dd>
                            
                            <dt class="col-sm-5">Make:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['make'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-5">Model:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['model'] ?? 'N/A') . '</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Caliber:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['caliber'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-5">Acquisition Date:</dt>
                            <dd class="col-sm-7">' . ($firearm['acquisition_date'] ? date('M j, Y', strtotime($firearm['acquisition_date'])) : 'N/A') . '</dd>
                            
                            <dt class="col-sm-5">Source:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['acquisition_source'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-5">Station:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($firearm['station_name'] ?? 'N/A') . '</dd>
                        </dl>
                    </div>
                </div>';

if ($firearm['remarks']) {
    $content .= '
                <hr>
                <h6><i class="fas fa-comment text-muted"></i> Remarks</h6>
                <p class="text-muted">' . nl2br(htmlspecialchars($firearm['remarks'])) . '</p>';
}

$content .= '
            </div>
        </div>

        <!-- Current Assignment Card -->
        <div class="card card-' . ($firearm['current_holder_id'] ? 'warning' : 'secondary') . '">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Current Assignment</h3>
            </div>
            <div class="card-body">';

if ($firearm['current_holder_id']) {
    $content .= '
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Currently Issued</h5>
                    <p class="mb-0">
                        <strong>Officer:</strong> ' . htmlspecialchars($firearm['holder_name'] ?? 'Unknown') . '<br>
                        <strong>Rank:</strong> ' . htmlspecialchars($firearm['holder_rank'] ?? 'N/A') . '
                    </p>
                </div>';
} else {
    $content .= '
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> This firearm is currently not assigned to any officer.
                </div>';
}

$content .= '
            </div>
        </div>

        <!-- Assignment History Card -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-history"></i> Assignment History</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Officer</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Ammo Issued</th>
                            <th>Ammo Returned</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($assignments)) {
    foreach ($assignments as $assignment) {
        $content .= '
                        <tr>
                            <td>' . htmlspecialchars($assignment['officer_name']) . '<br><small>' . htmlspecialchars($assignment['service_number']) . '</small></td>
                            <td>' . date('M j, Y', strtotime($assignment['issue_date'])) . '</td>
                            <td>' . ($assignment['return_date'] ? date('M j, Y', strtotime($assignment['return_date'])) : '<span class="badge badge-warning">Active</span>') . '</td>
                            <td>' . htmlspecialchars($assignment['ammunition_issued'] ?? 0) . '</td>
                            <td>' . htmlspecialchars($assignment['ammunition_returned'] ?? '-') . '</td>
                            <td>' . htmlspecialchars($assignment['purpose'] ?? '-') . '</td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="6" class="text-center text-muted">No assignment history</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Actions Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">';

if ($firearm['firearm_status'] === 'In Armory') {
    $content .= '
                <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#issueModal">
                    <i class="fas fa-hand-holding"></i> Issue to Officer
                </button>';
}

if ($firearm['current_holder_id']) {
    $content .= '
                <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#returnModal">
                    <i class="fas fa-undo"></i> Return Firearm
                </button>';
}

$content .= '
                <a href="' . url('/firearms') . '" class="btn btn-default btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/firearms/' . $firearm['id'] . '/issue') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-hand-holding"></i> Issue Firearm</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Officer <span class="text-danger">*</span></label>
                        <select name="officer_id" class="form-control select2" required>
                            <option value="">Select Officer</option>';

foreach ($officers as $officer) {
    $content .= '<option value="' . $officer['id'] . '">' . htmlspecialchars($officer['officer_name']) . ' (' . htmlspecialchars($officer['service_number']) . ') - ' . htmlspecialchars($officer['rank_name'] ?? 'N/A') . '</option>';
}

$content .= '
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Purpose</label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g., Patrol duty, Training">
                    </div>
                    <div class="form-group">
                        <label>Ammunition Issued</label>
                        <input type="number" name="ammunition_issued" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Issue Firearm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/firearms/' . $firearm['id'] . '/return') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-undo"></i> Return Firearm</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Ammunition Returned</label>
                        <input type="number" name="ammunition_returned" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Condition on Return</label>
                        <select name="condition_on_return" class="form-control">
                            <option value="Excellent">Excellent</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Any issues or notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Return Firearm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".select2").select2({
        theme: "bootstrap4",
        width: "100%"
    });
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
