<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Region Information</h3>
            </div>
            <div class="card-body">
                <strong>Region Code</strong>
                <p class="text-muted">' . sanitize($region['region_code']) . '</p>

                <strong>Region Name</strong>
                <p class="text-muted">' . sanitize($region['region_name']) . '</p>

                <strong>Total Divisions</strong>
                <p class="text-muted">' . count($region['divisions'] ?? []) . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/regions/' . $region['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Region
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> Divisions in ' . sanitize($region['region_name']) . '</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Division Code</th>
                            <th>Division Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($region['divisions'])) {
    foreach ($region['divisions'] as $division) {
        $content .= '
                        <tr>
                            <td>' . sanitize($division['division_code']) . '</td>
                            <td>' . sanitize($division['division_name']) . '</td>
                            <td>
                                <span class="badge badge-info">View divisions feature coming soon</span>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="3" class="text-center">No divisions in this region</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Regions', 'url' => '/regions'],
    ['title' => $region['region_name']]
];

include __DIR__ . '/../layouts/main.php';
?>
