<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Region Management</h3>
                <div class="card-tools">
                    <a href="' . url('/regions/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Region
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Region Code</th>
                            <th>Region Name</th>
                            <th>Districts</th>
                            <th>Stations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($regions)) {
    foreach ($regions as $region) {
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($region['region_code']) . '</strong></td>
                            <td>' . sanitize($region['region_name']) . '</td>
                            <td>' . ($region['district_count'] ?? 0) . '</td>
                            <td>' . ($region['station_count'] ?? 0) . '</td>
                            <td>
                                <a href="' . url('/regions/' . $region['id']) . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/regions/' . $region['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="5" class="text-center">No regions found</td>
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
    ['title' => 'Regions']
];

include __DIR__ . '/../layouts/main.php';
?>
