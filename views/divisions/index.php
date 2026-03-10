<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sitemap"></i> Division Management</h3>
                <div class="card-tools">
                    <a href="' . url('/divisions/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Division
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/divisions') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-10">
                            <select name="region" class="form-control">
                                <option value="">All Regions</option>';

foreach ($regions as $region) {
    $selected = ($selected_region ?? '') == $region['id'] ? 'selected' : '';
    $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
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
                            <th>Division Code</th>
                            <th>Division Name</th>
                            <th>Region</th>
                            <th>Districts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($divisions)) {
    foreach ($divisions as $division) {
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($division['division_code']) . '</strong></td>
                            <td>' . sanitize($division['division_name']) . '</td>
                            <td>' . sanitize($division['region_name'] ?? 'N/A') . '</td>
                            <td>' . ($division['district_count'] ?? 0) . '</td>
                            <td>
                                <a href="' . url('/divisions/' . $division['id']) . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/divisions/' . $division['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="5" class="text-center">No divisions found</td>
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
    ['title' => 'Divisions']
];

include __DIR__ . '/../layouts/main.php';
?>
