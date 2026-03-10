<?php
$content = '
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title"><i class="fas fa-lock"></i> Custody Record Details</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Suspect Name:</dt>
                            <dd class="col-sm-8"><strong>' . sanitize($record['suspect_name'] ?? 'N/A') . '</strong></dd>
                            
                            <dt class="col-sm-4">Case Number:</dt>
                            <dd class="col-sm-8">
                                <a href="' . url('/cases/view/' . $record['case_id']) . '">
                                    ' . sanitize($record['case_number'] ?? 'N/A') . '
                                </a>
                            </dd>
                            
                            <dt class="col-sm-4">Custody Location:</dt>
                            <dd class="col-sm-8">' . sanitize($record['custody_location'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-4">Custody Start:</dt>
                            <dd class="col-sm-8">' . date('d M Y H:i', strtotime($record['custody_start'])) . '</dd>
                            
                            <dt class="col-sm-4">Custody End:</dt>
                            <dd class="col-sm-8">' . ($record['custody_end'] ? date('d M Y H:i', strtotime($record['custody_end'])) : '<span class="badge badge-warning">Still in Custody</span>') . '</dd>
                            
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">';
                            
$statusColors = [
    'In Custody' => 'success',
    'Released' => 'info',
    'Transferred' => 'warning',
    'Escaped' => 'danger'
];
$statusColor = $statusColors[$record['custody_status']] ?? 'secondary';
$content .= '<span class="badge badge-' . $statusColor . '">' . sanitize($record['custody_status']) . '</span>';

$content .= '
                            </dd>
                            
                            <dt class="col-sm-4">Reason:</dt>
                            <dd class="col-sm-8">' . nl2br(sanitize($record['reason'] ?? 'N/A')) . '</dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a href="' . url('/cases/view/' . $record['case_id']) . '" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Case
                        </a>
                        <a href="' . url('/custody') . '" class="btn btn-info">
                            <i class="fas fa-list"></i> All Custody Records
                        </a>';

if ($record['custody_status'] == 'In Custody') {
    $content .= '
                        <button class="btn btn-warning" data-toggle="modal" data-target="#releaseModal">
                            <i class="fas fa-unlock"></i> Release from Custody
                        </button>';
}

$content .= '
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title"><i class="fas fa-user"></i> Suspect Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-6">Ghana Card:</dt>
                            <dd class="col-sm-6">' . sanitize($record['ghana_card_number'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-6">Contact:</dt>
                            <dd class="col-sm-6">' . sanitize($record['contact'] ?? 'N/A') . '</dd>
                            
                            <dt class="col-sm-6">Date of Birth:</dt>
                            <dd class="col-sm-6">' . ($record['date_of_birth'] ? date('d M Y', strtotime($record['date_of_birth'])) : 'N/A') . '</dd>
                        </dl>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Case Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong></p>
                        <p>' . nl2br(sanitize($record['case_description'] ?? 'N/A')) . '</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Release Modal -->
    <div class="modal fade" id="releaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-unlock"></i> Release from Custody</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="' . url('/custody/' . $record['id'] . '/release') . '">
                    ' . csrf_field() . '
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Release Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="custody_end" required>
                        </div>
                        <div class="form-group">
                            <label>Release Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="release_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-unlock"></i> Release
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
';

$breadcrumbs = [
    ['title' => 'Custody Records', 'url' => '/custody'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
