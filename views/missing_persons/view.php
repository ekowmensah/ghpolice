<?php
$statusClass = match($person['status']) {
    'Missing' => 'warning',
    'Found Alive' => 'success',
    'Found Deceased' => 'danger',
    'Closed' => 'secondary',
    default => 'secondary'
};

$statusIcon = match($person['status']) {
    'Missing' => 'fa-user-slash',
    'Found Alive' => 'fa-check-circle',
    'Found Deceased' => 'fa-times-circle',
    'Closed' => 'fa-archive',
    default => 'fa-question-circle'
};

$content = '
<div class="row">
    <div class="col-md-12">
        <!-- Header Card -->
        <div class="card card-outline card-' . $statusClass . '">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title mb-0">
                            <i class="fas ' . $statusIcon . '"></i> 
                            ' . htmlspecialchars($person['first_name'] . ' ' . ($person['middle_name'] ? $person['middle_name'] . ' ' : '') . $person['last_name']) . '
                        </h3>
                        <p class="text-muted mb-0 mt-1">
                            <small><i class="fas fa-hashtag"></i> ' . htmlspecialchars($person['report_number']) . ' | 
                            <i class="fas fa-calendar"></i> Reported: ' . date('M j, Y', strtotime($person['created_at'])) . '</small>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge badge-' . $statusClass . ' badge-lg" style="font-size: 1.1em; padding: 8px 15px;">
                            <i class="fas ' . $statusIcon . '"></i> ' . htmlspecialchars($person['status']) . '
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
        <!-- Personal Information Card -->
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-user"></i> Personal Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5"><i class="fas fa-venus-mars text-muted"></i> Gender:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($person['gender'] ?? 'Not specified') . '</dd>
                            
                            <dt class="col-sm-5"><i class="fas fa-birthday-cake text-muted"></i> Date of Birth:</dt>
                            <dd class="col-sm-7">' . ($person['date_of_birth'] ? date('M j, Y', strtotime($person['date_of_birth'])) : 'Not specified') . '</dd>';

if ($person['date_of_birth']) {
    $age = date_diff(date_create($person['date_of_birth']), date_create('now'))->y;
    $content .= '
                            <dt class="col-sm-5"><i class="fas fa-hourglass-half text-muted"></i> Age:</dt>
                            <dd class="col-sm-7"><strong>' . $age . ' years</strong></dd>';
}

$content .= '
                            <dt class="col-sm-5"><i class="fas fa-ruler-vertical text-muted"></i> Height:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($person['height'] ?? 'Not specified') . '</dd>
                            
                            <dt class="col-sm-5"><i class="fas fa-weight text-muted"></i> Weight:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($person['weight'] ?? 'Not specified') . '</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5"><i class="fas fa-palette text-muted"></i> Complexion:</dt>
                            <dd class="col-sm-7">' . htmlspecialchars($person['complexion'] ?? 'Not specified') . '</dd>';

if ($person['distinguishing_marks']) {
    $content .= '
                            <dt class="col-sm-12 mt-2"><i class="fas fa-fingerprint text-muted"></i> Distinguishing Marks:</dt>
                            <dd class="col-sm-12">
                                <div class="alert alert-light mb-0">
                                    ' . nl2br(htmlspecialchars($person['distinguishing_marks'])) . '
                                </div>
                            </dd>';
}

$content .= '
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Seen Information Card -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Last Seen Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Date & Time</span>
                                <span class="info-box-number">' . date('M j, Y', strtotime($person['last_seen_date'])) . '</span>
                                <span class="info-box-text">' . date('g:i A', strtotime($person['last_seen_date'])) . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-danger"><i class="fas fa-location-arrow"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Location</span>
                                <span class="info-box-number" style="font-size: 16px;">' . htmlspecialchars($person['last_seen_location']) . '</span>
                            </div>
                        </div>
                    </div>
                </div>';

if ($person['last_seen_wearing']) {
    $content .= '
                <div class="mt-3">
                    <h5><i class="fas fa-tshirt text-muted"></i> Last Seen Wearing</h5>
                    <div class="alert alert-warning">
                        ' . nl2br(htmlspecialchars($person['last_seen_wearing'])) . '
                    </div>
                </div>';
}

$content .= '
                <div class="mt-3">
                    <h5><i class="fas fa-info-circle text-muted"></i> Circumstances of Disappearance</h5>
                    <div class="alert alert-info">
                        ' . nl2br(htmlspecialchars($person['circumstances'])) . '
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporter Information Card -->
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Reporter Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="fas fa-user-circle fa-3x text-muted mb-2"></i>
                            <h5>' . htmlspecialchars($person['reported_by_name']) . '</h5>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4"><i class="fas fa-phone text-muted"></i> Contact:</dt>
                            <dd class="col-sm-8">' . htmlspecialchars($person['reported_by_contact'] ?? 'Not provided') . '</dd>
                            
                            <dt class="col-sm-4"><i class="fas fa-link text-muted"></i> Relationship:</dt>
                            <dd class="col-sm-8">' . htmlspecialchars($person['relationship_to_missing'] ?? 'Not specified') . '</dd>
                            
                            <dt class="col-sm-4"><i class="fas fa-calendar-check text-muted"></i> Report Date:</dt>
                            <dd class="col-sm-8">' . date('F j, Y g:i A', strtotime($person['created_at'])) . '</dd>
                        </dl>
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
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="' . url('/missing-persons/' . $person['id'] . '/edit') . '" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit Report
                </a>
                <a href="' . url('/missing-persons/' . $person['id'] . '/print') . '" class="btn btn-info btn-block mb-2" target="_blank">
                    <i class="fas fa-print"></i> Print Report
                </a>
                <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#updateStatusModal">
                    <i class="fas fa-sync-alt"></i> Change Status
                </button>';

if ($person['case_id']) {
    $content .= '
                <a href="' . url('/cases/' . $person['case_id']) . '" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-folder-open"></i> View Linked Case
                </a>';
} else {
    $content .= '
                <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#linkCaseModal">
                    <i class="fas fa-link"></i> Link to Case
                </button>';
}

$content .= '
                <a href="' . url('/missing-persons') . '" class="btn btn-default btn-block">
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
                        <span class="bg-' . $statusClass . '">' . htmlspecialchars($person['status']) . '</span>
                    </div>
                    <div>
                        <i class="fas fa-file-alt bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . date('M j, Y g:i A', strtotime($person['created_at'])) . '</span>
                            <h3 class="timeline-header">Report Filed</h3>
                            <div class="timeline-body">
                                Missing person report was officially filed.
                            </div>
                        </div>
                    </div>';

if ($person['found_date']) {
    $content .= '
                    <div>
                        <i class="fas fa-check-circle bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . date('M j, Y', strtotime($person['found_date'])) . '</span>
                            <h3 class="timeline-header">Status Updated</h3>
                            <div class="timeline-body">
                                Person found at: ' . htmlspecialchars($person['found_location'] ?? 'Location not specified') . '
                            </div>
                        </div>
                    </div>';
}

$content .= '
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Station Information Card -->
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title"><i class="fas fa-building"></i> Station Information</h3>
            </div>
            <div class="card-body">
                <p><strong><i class="fas fa-map-pin text-muted"></i> Station:</strong><br>' . htmlspecialchars($person['station_name'] ?? 'Not specified') . '</p>';

if (isset($person['investigating_officer_name'])) {
    $content .= '
                <hr>
                <p><strong><i class="fas fa-user-shield text-muted"></i> Investigating Officer:</strong><br>' . htmlspecialchars($person['investigating_officer_name']) . '</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/missing-persons/' . $person['id'] . '/update-status') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-sync-alt"></i> Change Status</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Current Status: <strong>' . htmlspecialchars($person['status']) . '</strong>
                    </div>
                    <div class="form-group">
                        <label>New Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="Missing" ' . ($person['status'] === 'Missing' ? 'selected' : '') . '>Missing (Active Search)</option>
                            <option value="Found Alive" ' . ($person['status'] === 'Found Alive' ? 'selected' : '') . '>Found Alive</option>
                            <option value="Found Deceased" ' . ($person['status'] === 'Found Deceased' ? 'selected' : '') . '>Found Deceased</option>
                            <option value="Closed" ' . ($person['status'] === 'Closed' ? 'selected' : '') . '>Closed</option>
                        </select>
                        <small class="form-text text-muted">You can reopen a case by selecting "Missing" status</small>
                    </div>
                    <div class="form-group">
                        <label>Date <small class="text-muted">(Optional)</small></label>
                        <input type="date" name="found_date" class="form-control" value="' . ($person['found_date'] ?? '') . '">
                        <small class="form-text text-muted">Date when status changed (e.g., found date)</small>
                    </div>
                    <div class="form-group">
                        <label>Location <small class="text-muted">(Optional)</small></label>
                        <input type="text" name="found_location" class="form-control" placeholder="Enter relevant location" value="' . htmlspecialchars($person['found_location'] ?? '') . '">
                        <small class="form-text text-muted">Location where person was found or last known location</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Link to Case Modal -->
<div class="modal fade" id="linkCaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/missing-persons/' . $person['id'] . '/link-case') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-link"></i> Link to Case</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Case <span class="text-danger">*</span></label>
                        <select name="case_id" class="form-control" required>
                            <option value="">Select a case</option>';

foreach ($openCases as $case) {
    $content .= '<option value="' . $case['id'] . '">' . htmlspecialchars($case['case_number']) . ' - ' . htmlspecialchars(substr($case['description'], 0, 50)) . '...</option>';
}

$content .= '
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Linking this missing person report to a case will help track the investigation progress.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Link to Case</button>
                </div>
            </form>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
