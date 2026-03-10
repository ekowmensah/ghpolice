<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Division Information</h3>
            </div>
            <div class="card-body">
                <strong>Division Code</strong>
                <p class="text-muted">' . sanitize($division['division_code']) . '</p>

                <strong>Division Name</strong>
                <p class="text-muted">' . sanitize($division['division_name']) . '</p>

                <strong>Region</strong>
                <p class="text-muted">' . sanitize($division['region_name'] ?? 'N/A') . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/divisions/' . $division['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Division
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> Districts in ' . sanitize($division['division_name']) . '</h3>
                <div class="card-tools">
                    <a href="' . url('/districts/create?division=' . $division['id']) . '" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Add District
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">District listing will appear here</p>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Divisions', 'url' => '/divisions'],
    ['title' => $division['division_name']]
];

include __DIR__ . '/../layouts/main.php';
?>
