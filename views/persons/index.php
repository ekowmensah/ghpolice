<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Person Registry</h3>
                <div class="card-tools">
                    <a href="' . url('/persons/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Register New Person
                    </a>
                    <a href="' . url('/persons/search') . '" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/persons') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Quick search..." value="' . sanitize($search ?? '') . '">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="' . url('/persons') . '" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Ghana Card</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Risk Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($persons)) {
    foreach ($persons as $person) {
        $fullName = trim(($person['first_name'] ?? '') . ' ' . ($person['middle_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
        
        $statusBadges = '';
        if ($person['is_wanted']) {
            $statusBadges .= '<span class="badge badge-danger">WANTED</span> ';
        }
        if ($person['has_criminal_record']) {
            $statusBadges .= '<span class="badge badge-warning">Record</span> ';
        }
        
        $riskClass = match($person['risk_level']) {
            'Critical' => 'badge-danger',
            'High' => 'badge-warning',
            'Medium' => 'badge-info',
            'Low' => 'badge-secondary',
            default => 'badge-success'
        };
        
        $content .= '
                        <tr>
                            <td>' . $person['id'] . '</td>
                            <td><strong>' . sanitize($fullName) . '</strong></td>
                            <td>' . sanitize($person['ghana_card_number'] ?? 'N/A') . '</td>
                            <td>' . sanitize($person['contact'] ?? 'N/A') . '</td>
                            <td>' . $statusBadges . '</td>
                            <td><span class="badge ' . $riskClass . '">' . sanitize($person['risk_level']) . '</span></td>
                            <td>
                                <a href="' . url('/persons/' . $person['id']) . '" class="btn btn-sm btn-info" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . url('/persons/' . $person['id'] . '/crime-check') . '" class="btn btn-sm btn-warning" title="Crime Check">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </a>
                                <a href="' . url('/persons/' . $person['id'] . '/edit') . '" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center">No persons found</td>
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
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/persons?page=' . ($pagination['current_page'] - 1)) . '">«</a></li>';
    }
    
    for ($i = 1; $i <= $pagination['last_page']; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $content .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . url('/persons?page=' . $i) . '">' . $i . '</a></li>';
    }
    
    if ($pagination['current_page'] < $pagination['last_page']) {
        $content .= '<li class="page-item"><a class="page-link" href="' . url('/persons?page=' . ($pagination['current_page'] + 1)) . '">»</a></li>';
    }
    
    $content .= '
                </ul>
                <div class="float-left">
                    Showing ' . count($persons) . ' of ' . $pagination['total'] . ' persons
                </div>
            </div>';
}

$content .= '
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Persons']
];

include __DIR__ . '/../layouts/main.php';
?>
