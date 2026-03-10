<?php
$statusClass = match($complaint['complaint_status']) {
    'Received' => 'info',
    'Under Investigation' => 'warning',
    'Resolved' => 'success',
    'Dismissed' => 'secondary',
    'Referred to CHRAJ' => 'danger',
    default => 'secondary'
};

$statusIcon = match($complaint['complaint_status']) {
    'Received' => 'fa-inbox',
    'Under Investigation' => 'fa-search',
    'Resolved' => 'fa-check-circle',
    'Dismissed' => 'fa-times-circle',
    'Referred to CHRAJ' => 'fa-share',
    default => 'fa-question-circle'
};

$content = '
<div class="row">
    <div class="col-md-12">';

if (isset($_SESSION['success'])) {
    $content .= '
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success']) . '
        </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $content .= '
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['error']) . '
        </div>';
    unset($_SESSION['error']);
}

$content .= '
        <!-- Header Card -->
        <div class="card card-outline card-' . $statusClass . '">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-comments"></i> 
                            Public Complaint Details
                        </h3>
                        <p class="text-muted mb-0 mt-1">
                            <small><i class="fas fa-hashtag"></i> ' . htmlspecialchars($complaint['complaint_number']) . ' | 
                            <i class="fas fa-calendar"></i> Filed: ' . date('M j, Y', strtotime($complaint['created_at'])) . '</small>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge badge-' . $statusClass . ' badge-lg" style="font-size: 1.1em; padding: 8px 15px;">
                            <i class="fas ' . $statusIcon . '"></i> ' . htmlspecialchars($complaint['complaint_status']) . '
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column - Main Information -->
    <div class="col-md-8">
        <!-- Complainant Information Card -->
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-user"></i> Complainant Information</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4"><i class="fas fa-user-circle text-muted"></i> Name:</dt>
                    <dd class="col-sm-8">' . htmlspecialchars($complaint['complainant_name']) . '</dd>
                </dl>
            </div>
        </div>

        <!-- Complaint Details Card -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Complaint Details</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Type</span>
                                <span class="info-box-number" style="font-size: 16px;">' . htmlspecialchars($complaint['complaint_type']) . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Station</span>
                                <span class="info-box-number" style="font-size: 16px;">' . htmlspecialchars($complaint['station_name']) . '</span>
                            </div>
                        </div>
                    </div>
                </div>';

if ($complaint['officer_complained_against']) {
    $content .= '
                <div class="card bg-danger" style="color: white;">
                    <div class="card-body">
                        <h5><i class="fas fa-user-shield"></i> Officer Complained Against</h5>
                        <p class="mb-0">
                            <strong>' . htmlspecialchars($complaint['officer_name'] ?? 'Unknown Officer') . '</strong><br>
                            <small>Service Number: ' . htmlspecialchars($complaint['service_number'] ?? 'N/A') . '</small><br>
                            <small>Rank: ' . htmlspecialchars($complaint['rank_name'] ?? 'N/A') . '</small>
                        </p>
                    </div>
                </div>';
}

$content .= '
                <div class="mt-3">
                    <h5><i class="fas fa-file-alt text-muted"></i> Complaint Description</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            ' . nl2br(htmlspecialchars($complaint['complaint_details'])) . '
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Actions & Status -->
    <div class="col-md-4">
        <!-- Quick Actions Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Actions</h3>
            </div>
            <div class="card-body">';

if ($complaint['complaint_status'] === 'Received') {
    $content .= '
                <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#investigateModal">
                    <i class="fas fa-search"></i> Start Investigation
                </button>';
}

if (in_array($complaint['complaint_status'], ['Received', 'Under Investigation'])) {
    $content .= '
                <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#resolveModal">
                    <i class="fas fa-check"></i> Resolve Complaint
                </button>
                <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#dismissModal">
                    <i class="fas fa-times"></i> Dismiss Complaint
                </button>';
}

$content .= '
                <a href="' . url('/public-complaints') . '" class="btn btn-default btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Status Timeline Card -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-history"></i> Status Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-' . $statusClass . '">' . htmlspecialchars($complaint['complaint_status']) . '</span>
                    </div>
                    <div>
                        <i class="fas fa-file-alt bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . date('M j, Y g:i A', strtotime($complaint['created_at'])) . '</span>
                            <h3 class="timeline-header">Complaint Filed</h3>
                            <div class="timeline-body">
                                Complaint was officially filed and received.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Investigate Modal -->
<div class="modal fade" id="investigateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/public-complaints/' . $complaint['id'] . '/investigate') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-search"></i> Start Investigation</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to start investigating this complaint?</p>
                    <div class="form-group">
                        <label>Investigation Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter initial investigation notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Start Investigation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/public-complaints/' . $complaint['id'] . '/resolve') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <input type="hidden" name="status" value="Resolved">
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Resolve Complaint</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Resolution Details <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" required placeholder="Enter resolution details"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Resolve Complaint</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dismiss Modal -->
<div class="modal fade" id="dismissModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/public-complaints/' . $complaint['id'] . '/resolve') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <input type="hidden" name="status" value="Dismissed">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title"><i class="fas fa-times-circle"></i> Dismiss Complaint</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This action will dismiss the complaint.
                    </div>
                    <div class="form-group">
                        <label>Reason for Dismissal <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" required placeholder="Enter reason for dismissal"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Dismiss Complaint</button>
                </div>
            </form>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
