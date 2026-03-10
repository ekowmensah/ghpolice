<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> District Management</h3>
                <div class="card-tools">
                    <a href="' . url('/districts/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create District
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/districts') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-10">
                            <select name="division" class="form-control">
                                <option value="">All Divisions</option>';

foreach ($divisions as $division) {
    $selected = ($selected_division ?? '') == $division['id'] ? 'selected' : '';
    $content .= '<option value="' . $division['id'] . '" ' . $selected . '>' . sanitize($division['division_name']) . ' (' . sanitize($division['region_name']) . ')</option>';
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
                            <th>District Code</th>
                            <th>District Name</th>
                            <th>Division</th>
                            <th>Region</th>
                            <th>Stations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($districts)) {
    foreach ($districts as $district) {
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($district['district_code']) . '</strong></td>
                            <td>' . sanitize($district['district_name']) . '</td>
                            <td>' . sanitize($district['division_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($district['region_name'] ?? 'N/A') . '</td>
                            <td>' . ($district['station_count'] ?? 0) . '</td>
                            <td>
                                <a href="' . url('/districts/' . $district['id']) . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/districts/' . $district['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="6" class="text-center">No districts found</td>
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
    ['title' => 'Districts']
];

include __DIR__ . '/../layouts/main.php';
?>
