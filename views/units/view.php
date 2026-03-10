<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Unit Information</h3>
            </div>
            <div class="card-body">
                <strong>Unit Code</strong>
                <p class="text-muted">' . sanitize($unit['unit_code']) . '</p>

                <strong>Unit Name</strong>
                <p class="text-muted">' . sanitize($unit['unit_name']) . '</p>

                <strong>Unit Type</strong>
                <p class="text-muted">' . sanitize($unit['unit_type']) . '</p>

                <strong>Station</strong>
                <p class="text-muted">' . sanitize($unit['station_name'] ?? 'N/A') . '</p>

                <strong>Description</strong>
                <p class="text-muted">' . sanitize($unit['description'] ?? 'N/A') . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/units/' . $unit['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Unit
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Unit Officers</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Officer listing will appear here</p>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Units', 'url' => '/units'],
    ['title' => $unit['unit_name']]
];

include __DIR__ . '/../layouts/main.php';
?>
