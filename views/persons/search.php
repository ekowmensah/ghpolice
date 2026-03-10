<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Search Persons</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/persons/search') . '">
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" class="form-control" placeholder="Search by name, Ghana Card, phone, passport, or driver\'s license..." value="' . sanitize($keyword) . '" autofocus>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Enter at least 2 characters to search</small>
                </form>
            </div>
        </div>
    </div>
</div>';

if (!empty($results)) {
    $content .= '
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Search Results (' . count($results) . ' found)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Ghana Card</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($results as $person) {
        $fullName = trim(($person['first_name'] ?? '') . ' ' . ($person['middle_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
        $statusBadges = '';
        
        if ($person['is_wanted']) {
            $statusBadges .= '<span class="badge badge-danger">WANTED</span> ';
        }
        if ($person['has_criminal_record']) {
            $statusBadges .= '<span class="badge badge-warning">Criminal Record</span> ';
        }
        
        $riskClass = '';
        switch ($person['risk_level']) {
            case 'Critical':
                $riskClass = 'badge-danger';
                break;
            case 'High':
                $riskClass = 'badge-warning';
                break;
            case 'Medium':
                $riskClass = 'badge-info';
                break;
            case 'Low':
                $riskClass = 'badge-secondary';
                break;
            default:
                $riskClass = 'badge-success';
        }
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($fullName) . '</strong></td>
                            <td>' . sanitize($person['ghana_card_number'] ?? 'N/A') . '</td>
                            <td>' . sanitize($person['contact'] ?? 'N/A') . '</td>
                            <td>
                                ' . $statusBadges . '
                                <span class="badge ' . $riskClass . '">Risk: ' . sanitize($person['risk_level']) . '</span>
                            </td>
                            <td>
                                <a href="' . url('/persons/' . $person['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="' . url('/persons/' . $person['id'] . '/crime-check') . '" class="btn btn-sm btn-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Crime Check
                                </a>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';
} elseif ($keyword && strlen($keyword) >= 2) {
    $content .= '
<div class="row mt-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No persons found matching "' . sanitize($keyword) . '"
        </div>
    </div>
</div>';
}

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => 'Search']
];

include __DIR__ . '/../layouts/main.php';
?>
