<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Station Information</h3>
            </div>
            <div class="card-body">
                <strong>Station Code</strong>
                <p class="text-muted">' . sanitize($station['station_code']) . '</p>

                <strong>Station Name</strong>
                <p class="text-muted">' . sanitize($station['station_name']) . '</p>

                <strong>Region</strong>
                <p class="text-muted">' . sanitize($station['region_name'] ?? 'N/A') . '</p>

                <strong>Division</strong>
                <p class="text-muted">' . sanitize($station['division_name'] ?? 'N/A') . '</p>

                <strong>District</strong>
                <p class="text-muted">' . sanitize($station['district_name'] ?? 'N/A') . '</p>

                <strong>Contact Number</strong>
                <p class="text-muted">' . sanitize($station['contact_number'] ?? 'N/A') . '</p>

                <strong>Address</strong>
                <p class="text-muted">' . sanitize($station['address'] ?? 'N/A') . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/stations/' . $station['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Station
                </a>
            </div>
        </div>

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <strong>Total Cases</strong>
                <p class="text-muted">' . ($case_count ?? 0) . '</p>

                <strong>Active Officers</strong>
                <p class="text-muted">' . count($officers) . '</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Station Officers</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service Number</th>
                            <th>Name</th>
                            <th>Rank</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($officers)) {
    foreach ($officers as $officer) {
        $content .= '
                        <tr>
                            <td>' . sanitize($officer['service_number']) . '</td>
                            <td>' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</td>
                            <td><span class="badge badge-primary">' . sanitize($officer['rank_name'] ?? 'N/A') . '</span></td>
                            <td>' . sanitize($officer['phone_number'] ?? 'N/A') . '</td>
                            <td>
                                <a href="' . url('/officers/' . $officer['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="5" class="text-center">No officers assigned to this station</td>
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
    ['title' => 'Stations', 'url' => '/stations'],
    ['title' => $station['station_name']]
];

include __DIR__ . '/../layouts/main.php';
?>
