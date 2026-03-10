<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-slash"></i> Missing Persons Registry</h3>
                <div class="card-tools">
                    <a href="' . url('/missing-persons/create') . '" class="btn btn-success">
                        <i class="fas fa-plus"></i> Report Missing Person
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/missing-persons') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="Missing" ' . (($selected_status ?? '') === 'Missing' ? 'selected' : '') . '>Missing</option>
                                <option value="Found Alive" ' . (($selected_status ?? '') === 'Found Alive' ? 'selected' : '') . '>Found Alive</option>
                                <option value="Found Deceased" ' . (($selected_status ?? '') === 'Found Deceased' ? 'selected' : '') . '>Found Deceased</option>
                                <option value="Closed" ' . (($selected_status ?? '') === 'Closed' ? 'selected' : '') . '>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="age_group" class="form-control">
                                <option value="">All Ages</option>
                                <option value="Child" ' . (($selected_age_group ?? '') === 'Child' ? 'selected' : '') . '>Child (0-12)</option>
                                <option value="Teenager" ' . (($selected_age_group ?? '') === 'Teenager' ? 'selected' : '') . '>Teenager (13-17)</option>
                                <option value="Adult" ' . (($selected_age_group ?? '') === 'Adult' ? 'selected' : '') . '>Adult (18-59)</option>
                                <option value="Senior" ' . (($selected_age_group ?? '') === 'Senior' ? 'selected' : '') . '>Senior (60+)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="' . url('/missing-persons') . '" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>';

if (empty($persons)) {
    $content .= '
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No missing persons found.
                </div>';
} else {
    $content .= '
                <div class="row">';
    
    foreach ($persons as $person) {
        $statusClass = match($person['status']) {
            'Missing' => 'warning',
            'Found Alive' => 'success', 
            'Found Deceased' => 'danger',
            'Closed' => 'secondary',
            default => 'secondary'
        };
        
        $content .= '
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>' . htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) . '</h5>
                                <p class="text-muted mb-2">
                                    <strong>' . htmlspecialchars($person['report_number']) . '</strong>
                                </p>
                                <p class="mb-1">
                                    <strong>Age:</strong> ';
        
        if (!empty($person['date_of_birth'])) {
            $content .= date_diff(date_create($person['date_of_birth']), date_create('now'))->y . ' years';
        } else {
            $content .= 'Unknown';
        }
        
        $content .= '
                                </p>
                                <p class="mb-1">
                                    <strong>Last Seen:</strong> ' . htmlspecialchars($person['last_seen_location']) . '
                                </p>
                                <p class="mb-2">
                                    <strong>Status:</strong> 
                                    <span class="badge badge-' . $statusClass . '">
                                        ' . htmlspecialchars($person['status']) . '
                                    </span>
                                </p>
                                <a href="' . url('/missing-persons/' . $person['id']) . '" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                </div>';
}

$content .= '
            </div>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
