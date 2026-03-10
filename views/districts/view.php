<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">District Information</h3>
            </div>
            <div class="card-body">
                <strong>District Code</strong>
                <p class="text-muted">' . sanitize($district['district_code']) . '</p>

                <strong>District Name</strong>
                <p class="text-muted">' . sanitize($district['district_name']) . '</p>

                <strong>Division</strong>
                <p class="text-muted">' . sanitize($district['division_name'] ?? 'N/A') . '</p>

                <strong>Region</strong>
                <p class="text-muted">' . sanitize($district['region_name'] ?? 'N/A') . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/districts/' . $district['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit District
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Stations in ' . sanitize($district['district_name']) . '</h3>
                <div class="card-tools">
                    <a href="' . url('/stations/create?district=' . $district['id']) . '" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Add Station
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Station listing will appear here</p>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Districts', 'url' => '/districts'],
    ['title' => $district['district_name']]
];

include __DIR__ . '/../layouts/main.php';
?>
