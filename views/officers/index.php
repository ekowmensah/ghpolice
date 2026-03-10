<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Officer Management</h3>
                <div class="card-tools">
                    <a href="' . url('/officers/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Register Officer
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/officers') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="rank" class="form-control">
                                <option value="">All Ranks</option>';

foreach ($ranks as $rank) {
    $selected = ($selected_rank ?? '') === $rank['rank_name'] ? 'selected' : '';
    $content .= '<option value="' . sanitize($rank['rank_name']) . '" ' . $selected . '>' . sanitize($rank['rank_name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="station" class="form-control">
                                <option value="">All Stations</option>';

foreach ($stations as $station) {
    $selected = ($selected_station ?? '') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="' . url('/officers') . '" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Service Number</th>
                            <th>Name</th>
                            <th>Rank</th>
                            <th>Badge Number</th>
                            <th>Station</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($officers)) {
    foreach ($officers as $officer) {
        $statusClass = match($officer['employment_status']) {
            'Active' => 'badge-success',
            'On Leave' => 'badge-warning',
            'Retired' => 'badge-secondary',
            'Suspended' => 'badge-danger',
            default => 'badge-info'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($officer['service_number']) . '</strong></td>
                            <td>' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</td>
                            <td><span class="badge badge-primary">' . sanitize($officer['rank_name'] ?? 'N/A') . '</span></td>
                            <td>' . sanitize($officer['badge_number'] ?? 'N/A') . '</td>
                            <td>' . sanitize($officer['station_name'] ?? 'Unassigned') . '</td>
                            <td><span class="badge ' . $statusClass . '">' . sanitize($officer['employment_status']) . '</span></td>
                            <td>
                                <a href="' . url('/officers/' . $officer['id']) . '" class="btn btn-sm btn-info" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/officers/' . $officer['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center">No officers found</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>';

if ($pagination) {
    $content .= '
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">';
    
    if ($pagination['current_page'] > 1) {
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/officers?page=' . ($pagination['current_page'] - 1)) . '">«</a></li>';
    }
    
    for ($i = 1; $i <= $pagination['last_page']; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $content .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . url('/officers?page=' . $i) . '">' . $i . '</a></li>';
    }
    
    if ($pagination['current_page'] < $pagination['last_page']) {
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/officers?page=' . ($pagination['current_page'] + 1)) . '">»</a></li>';
    }
    
    $content .= '
                </ul>
                <div class="float-left">
                    Showing ' . count($officers) . ' of ' . $pagination['total'] . ' officers
                </div>
            </div>';
}

$content .= '
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Officers']
];

include __DIR__ . '/../layouts/main.php';
?>
