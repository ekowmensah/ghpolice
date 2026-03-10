<?php
$content = '
<!-- Welcome Banner -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-0"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
                        <p class="mb-0 mt-2">Welcome back, <strong>' . htmlspecialchars($_SESSION['user']['first_name'] ?? 'Officer') . '</strong></p>
                        <small class="text-white-50"><i class="fas fa-clock"></i> ' . date('l, F j, Y - g:i A') . '</small>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="text-white">
                            <i class="fas fa-building fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/persons/crime-check') . '" class="btn btn-app btn-danger w-100">
                            <i class="fas fa-exclamation-triangle"></i>
                            Crime Check
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/cases/create') . '" class="btn btn-app btn-primary w-100">
                            <i class="fas fa-plus-circle"></i>
                            New Case
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/persons/create') . '" class="btn btn-app btn-success w-100">
                            <i class="fas fa-user-plus"></i>
                            Register Person
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/evidence') . '" class="btn btn-app btn-info w-100">
                            <i class="fas fa-box"></i>
                            Evidence
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/missing-persons/create') . '" class="btn btn-app btn-warning w-100">
                            <i class="fas fa-user-slash"></i>
                            Missing Person
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <a href="' . url('/vehicles') . '" class="btn btn-app btn-secondary w-100">
                            <i class="fas fa-car"></i>
                            Vehicles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row 1 - Cases & Investigations -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>' . ($stats['total_cases'] ?? 0) . '</h3>
                <p>Total Cases</p>
            </div>
            <div class="icon">
                <i class="fas fa-folder"></i>
            </div>
            <a href="' . url('/cases') . '" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>' . ($stats['open_cases'] ?? 0) . '</h3>
                <p>Open Cases</p>
            </div>
            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <a href="' . url('/cases?status=Open') . '" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>' . ($stats['high_priority_cases'] ?? 0) . '</h3>
                <p>High Priority</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <a href="' . url('/cases?priority=High') . '" class="small-box-footer">
                View Cases <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>' . ($stats['closed_cases'] ?? 0) . '</h3>
                <p>Closed Cases</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="' . url('/cases?status=Closed') . '" class="small-box-footer">
                View Archive <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Statistics Row 2 - Persons & Registry -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>' . ($stats['total_persons'] ?? 0) . '</h3>
                <p>Registered Persons</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="' . url('/persons') . '" class="small-box-footer">
                View Registry <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>' . ($stats['wanted_persons'] ?? 0) . '</h3>
                <p>Wanted Persons</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-secret"></i>
            </div>
            <a href="' . url('/persons?wanted=1') . '" class="small-box-footer">
                View List <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>' . ($stats['missing_persons'] ?? 0) . '</h3>
                <p>Missing Persons</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <a href="' . url('/missing-persons') . '" class="small-box-footer">
                View Reports <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>' . ($stats['total_suspects'] ?? 0) . '</h3>
                <p>Active Suspects</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="' . url('/cases') . '" class="small-box-footer">
                View Cases <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Statistics Row 3 - Evidence & Assets -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>' . ($stats['total_evidence'] ?? 0) . '</h3>
                <p>Evidence Items</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="' . url('/evidence') . '" class="small-box-footer">
                View Evidence <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>' . ($stats['total_vehicles'] ?? 0) . '</h3>
                <p>Registered Vehicles</p>
            </div>
            <div class="icon">
                <i class="fas fa-car"></i>
            </div>
            <a href="' . url('/vehicles') . '" class="small-box-footer">
                View Registry <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>' . ($stats['total_firearms'] ?? 0) . '</h3>
                <p>Firearms Registry</p>
            </div>
            <div class="icon">
                <i class="fas fa-crosshairs"></i>
            </div>
            <a href="' . url('/firearms') . '" class="small-box-footer">
                View Firearms <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>' . ($stats['total_officers'] ?? 0) . '</h3>
                <p>Active Officers</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="' . url('/officers') . '" class="small-box-footer">
                View Officers <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity & Charts -->
<div class="row">
    <!-- Recent Cases -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-folder-open"></i> Recent Cases</h3>
                <div class="card-tools">
                    <a href="' . url('/cases') . '" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($stats['recent_cases'])) {
    foreach (array_slice($stats['recent_cases'], 0, 5) as $case) {
        $statusClass = match($case['status']) {
            'Open' => 'warning',
            'Under Investigation' => 'info',
            'Closed' => 'success',
            default => 'secondary'
        };
        
        $priorityClass = match($case['case_priority']) {
            'Critical' => 'danger',
            'High' => 'warning',
            'Medium' => 'info',
            'Low' => 'secondary',
            default => 'secondary'
        };
        
        $content .= '
                        <tr>
                            <td><a href="' . url('/cases/' . $case['id']) . '"><strong>' . htmlspecialchars($case['case_number']) . '</strong></a></td>
                            <td>' . htmlspecialchars(substr($case['description'], 0, 40)) . '...</td>
                            <td><span class="badge badge-' . $statusClass . '">' . htmlspecialchars($case['status']) . '</span></td>
                            <td><span class="badge badge-' . $priorityClass . '">' . htmlspecialchars($case['case_priority']) . '</span></td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="4" class="text-center text-muted">No recent cases</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Missing Persons -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-user-slash"></i> Recent Missing Persons</h3>
                <div class="card-tools">
                    <a href="' . url('/missing-persons') . '" class="btn btn-sm btn-warning">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Report #</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($stats['recent_missing_persons'])) {
    foreach (array_slice($stats['recent_missing_persons'], 0, 5) as $person) {
        $statusClass = match($person['status']) {
            'Missing' => 'warning',
            'Found Alive' => 'success',
            'Found Deceased' => 'danger',
            'Closed' => 'secondary',
            default => 'secondary'
        };
        
        $content .= '
                        <tr>
                            <td><a href="' . url('/missing-persons/' . $person['id']) . '"><strong>' . htmlspecialchars($person['report_number']) . '</strong></a></td>
                            <td>' . htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) . '</td>
                            <td><span class="badge badge-' . $statusClass . '">' . htmlspecialchars($person['status']) . '</span></td>
                            <td><small>' . date('M j, Y', strtotime($person['last_seen_date'])) . '</small></td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="4" class="text-center text-muted">No recent missing persons reports</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- System Activity & Alerts -->
<div class="row">
    <!-- High Priority Cases -->
    <div class="col-lg-4">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> High Priority Cases</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">';

if (!empty($stats['high_priority_cases_list'])) {
    foreach (array_slice($stats['high_priority_cases_list'], 0, 3) as $case) {
        $content .= '
                    <li class="item">
                        <div class="product-info">
                            <a href="' . url('/cases/' . $case['id']) . '" class="product-title">
                                ' . htmlspecialchars($case['case_number']) . '
                                <span class="badge badge-danger float-right">' . htmlspecialchars($case['case_priority']) . '</span>
                            </a>
                            <span class="product-description">
                                ' . htmlspecialchars(substr($case['description'], 0, 60)) . '...
                            </span>
                        </div>
                    </li>';
    }
} else {
    $content .= '
                    <li class="item">
                        <div class="product-info text-center text-muted py-3">
                            No high priority cases
                        </div>
                    </li>';
}

$content .= '
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="' . url('/cases?priority=High') . '" class="uppercase">View All High Priority Cases</a>
            </div>
        </div>
    </div>
    
    <!-- Wanted Persons -->
    <div class="col-lg-4">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-secret"></i> Wanted Persons</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">';

if (!empty($stats['wanted_persons_list'])) {
    foreach (array_slice($stats['wanted_persons_list'], 0, 3) as $person) {
        $content .= '
                    <li class="item">
                        <div class="product-info">
                            <a href="' . url('/persons/' . $person['id']) . '" class="product-title">
                                ' . htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) . '
                                <span class="badge badge-danger float-right">Wanted</span>
                            </a>
                            <span class="product-description">
                                Risk Level: <strong>' . htmlspecialchars($person['risk_level'] ?? 'Unknown') . '</strong>
                            </span>
                        </div>
                    </li>';
    }
} else {
    $content .= '
                    <li class="item">
                        <div class="product-info text-center text-muted py-3">
                            No wanted persons
                        </div>
                    </li>';
}

$content .= '
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="' . url('/persons?wanted=1') . '" class="uppercase">View All Wanted Persons</a>
            </div>
        </div>
    </div>
    
    <!-- Recent Evidence -->
    <div class="col-lg-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Recent Evidence</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">';

if (!empty($stats['recent_evidence'])) {
    foreach (array_slice($stats['recent_evidence'], 0, 3) as $evidence) {
        $content .= '
                    <li class="item">
                        <div class="product-info">
                            <a href="' . url('/evidence/' . $evidence['id']) . '" class="product-title">
                                ' . htmlspecialchars($evidence['evidence_number']) . '
                                <span class="badge badge-info float-right">' . htmlspecialchars($evidence['evidence_type']) . '</span>
                            </a>
                            <span class="product-description">
                                ' . htmlspecialchars(substr($evidence['description'], 0, 60)) . '...
                            </span>
                        </div>
                    </li>';
    }
} else {
    $content .= '
                    <li class="item">
                        <div class="product-info text-center text-muted py-3">
                            No recent evidence
                        </div>
                    </li>';
}

$content .= '
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="' . url('/evidence') . '" class="uppercase">View All Evidence</a>
            </div>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
