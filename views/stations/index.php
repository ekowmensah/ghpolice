<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Station Management</h3>
                <div class="card-tools">
                    <a href="' . url('/stations/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Register Station
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/stations') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <select name="region" class="form-control">
                                <option value="">All Regions</option>';

foreach ($regions as $region) {
    $selected = ($selected_region ?? '') == $region['id'] ? 'selected' : '';
    $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="district" class="form-control">
                                <option value="">All Districts</option>';

foreach ($districts as $district) {
    $selected = ($selected_district ?? '') == $district['id'] ? 'selected' : '';
    $content .= '<option value="' . $district['id'] . '" ' . $selected . '>' . sanitize($district['district_name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Station Code</th>
                            <th>Station Name</th>
                            <th>Region</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($stations)) {
    foreach ($stations as $station) {
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($station['station_code']) . '</strong></td>
                            <td>' . sanitize($station['station_name']) . '</td>
                            <td>' . sanitize($station['region_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($station['division_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($station['district_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($station['contact_number'] ?? 'N/A') . '</td>
                            <td>
                                <a href="' . url('/stations/' . $station['id']) . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/stations/' . $station['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center">No stations found</td>
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
    ['title' => 'Stations']
];

include __DIR__ . '/../layouts/main.php';
?>
