<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users-cog"></i> Unit Management</h3>
                <div class="card-tools">
                    <a href="' . url('/units/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Unit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/units') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-10">
                            <select name="station" class="form-control">
                                <option value="">All Stations</option>';

foreach ($stations as $station) {
    $selected = ($selected_station ?? '') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
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
                            <th>Unit Code</th>
                            <th>Unit Name</th>
                            <th>Type</th>
                            <th>Station</th>
                            <th>Officers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($units)) {
    foreach ($units as $unit) {
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($unit['unit_code']) . '</strong></td>
                            <td>' . sanitize($unit['unit_name']) . '</td>
                            <td>' . sanitize($unit['unit_type']) . '</td>
                            <td>' . sanitize($unit['station_name'] ?? 'N/A') . '</td>
                            <td>' . ($unit['officer_count'] ?? 0) . '</td>
                            <td>
                                <a href="' . url('/units/' . $unit['id']) . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/units/' . $unit['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="6" class="text-center">No units found</td>
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
    ['title' => 'Units']
];

include __DIR__ . '/../layouts/main.php';
?>
