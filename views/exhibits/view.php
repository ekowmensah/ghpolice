<?php
$badgeClass = match($exhibit['exhibit_status']) {
    'In Custody' => 'success',
    'In Court' => 'warning',
    'Released' => 'info',
    'Destroyed' => 'danger',
    'Missing' => 'dark',
    default => 'secondary'
};

$content = '
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Exhibit Information</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Exhibit Number:</dt>
                    <dd class="col-sm-8"><strong>' . sanitize($exhibit['exhibit_number']) . '</strong></dd>
                    <dt class="col-sm-4">Case Number:</dt>
                    <dd class="col-sm-8">
                        <a href="' . url('/cases/view/' . $exhibit['case_id']) . '">
                            ' . sanitize($exhibit['case_number']) . '
                        </a>
                    </dd>
                    <dt class="col-sm-4">Exhibit Type:</dt>
                    <dd class="col-sm-8">' . sanitize($exhibit['exhibit_type']) . '</dd>
                    <dt class="col-sm-4">Description:</dt>
                    <dd class="col-sm-8">' . nl2br(sanitize($exhibit['description'])) . '</dd>
                    <dt class="col-sm-4">Quantity:</dt>
                    <dd class="col-sm-8">' . $exhibit['quantity'] . '</dd>
                    <dt class="col-sm-4">Seized From:</dt>
                    <dd class="col-sm-8">' . sanitize($exhibit['seized_from'] ?? 'N/A') . '</dd>
                    <dt class="col-sm-4">Seized Date:</dt>
                    <dd class="col-sm-8">' . date('l, d F Y', strtotime($exhibit['seized_date'])) . '</dd>
                    <dt class="col-sm-4">Seized By:</dt>
                    <dd class="col-sm-8">' . sanitize($exhibit['seized_by_name']) . ' (' . sanitize($exhibit['rank_name']) . ')</dd>
                    <dt class="col-sm-4">Current Location:</dt>
                    <dd class="col-sm-8"><strong>' . sanitize($exhibit['current_location']) . '</strong></dd>
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-' . $badgeClass . ' badge-lg">
                            ' . sanitize($exhibit['exhibit_status']) . '
                        </span>
                    </dd>';

if ($exhibit['remarks']) {
    $content .= '
                    <dt class="col-sm-4">Remarks:</dt>
                    <dd class="col-sm-8">' . nl2br(sanitize($exhibit['remarks'])) . '</dd>';
}

$content .= '
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Movement History</h3>
            </div>
            <div class="card-body">
                <div class="timeline">';

foreach ($movements as $movement) {
    $content .= '
                    <div>
                        <i class="fas fa-exchange-alt bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . time_ago($movement['movement_date']) . '</span>
                            <h3 class="timeline-header">
                                Moved by ' . sanitize($movement['moved_by_name']) . '
                            </h3>
                            <div class="timeline-body">
                                <strong>From:</strong> ' . sanitize($movement['moved_from']) . '<br>
                                <strong>To:</strong> ' . sanitize($movement['moved_to']) . '<br>';
    
    if ($movement['received_by_name']) {
        $content .= '
                                <strong>Received By:</strong> ' . sanitize($movement['received_by_name']) . '<br>';
    }
    
    if ($movement['purpose']) {
        $content .= '
                                <strong>Purpose:</strong> ' . sanitize($movement['purpose']);
    }
    
    $content .= '
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
    </div>
    <div class="col-md-4">';

if ($exhibit['photo_path']) {
    $content .= '
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Exhibit Photo</h3>
            </div>
            <div class="card-body text-center">
                <img src="' . url($exhibit['photo_path']) . '" alt="Exhibit Photo" class="img-fluid">
            </div>
        </div>';
}

$content .= '
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <a href="' . url('/exhibits') . '" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <button class="btn btn-warning btn-block" id="moveExhibitBtn">
                    <i class="fas fa-exchange-alt"></i> Record Movement
                </button>
                <button class="btn btn-info btn-block" id="updateStatusBtn">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Exhibits', 'url' => '/exhibits'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
