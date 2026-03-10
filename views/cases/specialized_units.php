<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Specialized Units Dashboard</h3>
            </div>
            <div class="card-body">
                <!-- Unit Filter Tabs -->
                <ul class="nav nav-tabs" id="unitTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">
                            <i class="fas fa-list"></i> All Units
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dovvsu-tab" data-toggle="tab" href="#dovvsu" role="tab">
                            <i class="fas fa-shield-alt text-danger"></i> DOVVSU
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cid-tab" data-toggle="tab" href="#cid" role="tab">
                            <i class="fas fa-user-secret"></i> CID
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cyber-tab" data-toggle="tab" href="#cyber" role="tab">
                            <i class="fas fa-laptop-code"></i> Cybercrime
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="narco-tab" data-toggle="tab" href="#narco" role="tab">
                            <i class="fas fa-pills"></i> Narcotics
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="unitTabContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel">
                        <div class="row">';

// Get statistics for each specialized unit
$db = \App\Config\Database::getConnection();
$stmt = $db->query("
    SELECT 
        specialized_unit,
        COUNT(*) as total_cases,
        SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN status = 'Under Investigation' THEN 1 ELSE 0 END) as investigating,
        SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_cases,
        SUM(CASE WHEN case_priority = 'Critical' THEN 1 ELSE 0 END) as critical_cases
    FROM cases 
    WHERE specialized_unit IS NOT NULL
    GROUP BY specialized_unit
");
$unitStats = $stmt->fetchAll();

$unitIcons = [
    'DOVVSU' => 'fa-shield-alt text-danger',
    'CID' => 'fa-user-secret text-primary',
    'CYBER' => 'fa-laptop-code text-info',
    'NARCO' => 'fa-pills text-warning',
    'SWAT' => 'fa-crosshairs text-dark',
    'INTEL' => 'fa-eye text-secondary',
    'TRAFFIC' => 'fa-car text-success',
    'FRAUD' => 'fa-money-bill-wave text-danger',
    'HOMICIDE' => 'fa-skull text-dark',
    'ROBBERY' => 'fa-mask text-danger'
];

foreach ($unitStats as $unit) {
    $icon = $unitIcons[$unit['specialized_unit']] ?? 'fa-folder';
    $content .= '
                            <div class="col-md-3">
                                <div class="small-box bg-light">
                                    <div class="inner">
                                        <h3>' . $unit['total_cases'] . '</h3>
                                        <p>' . htmlspecialchars($unit['specialized_unit']) . ' Cases</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas ' . $icon . '"></i>
                                    </div>
                                    <a href="' . url('/cases?unit=' . urlencode($unit['specialized_unit'])) . '" class="small-box-footer">
                                        View Details <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                                <div class="info-box mb-3">
                                    <div class="info-box-content">
                                        <span class="info-box-text">Open: ' . $unit['open_cases'] . '</span>
                                        <span class="info-box-text">Investigating: ' . $unit['investigating'] . '</span>
                                        <span class="info-box-text">Closed: ' . $unit['closed_cases'] . '</span>
                                        <span class="info-box-text text-danger">Critical: ' . $unit['critical_cases'] . '</span>
                                    </div>
                                </div>
                            </div>';
}

$content .= '
                        </div>
                    </div>

                    <!-- DOVVSU Tab -->
                    <div class="tab-pane fade" id="dovvsu" role="tabpanel">
                        <div class="alert alert-danger">
                            <i class="fas fa-shield-alt"></i> <strong>DOVVSU Cases - Special Handling Required</strong>
                            <p class="mb-0">These cases involve domestic violence, sexual offences, and child abuse. Extra privacy and victim protection measures apply.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Active DOVVSU Cases</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Case Number</th>
                                                    <th>Category</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Date Reported</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>';

$stmt = $db->query("
    SELECT c.*, s.station_name 
    FROM cases c
    LEFT JOIN stations s ON c.station_id = s.id
    WHERE c.specialized_unit = 'DOVVSU' OR c.is_dovvsu_case = 1
    ORDER BY c.created_at DESC
    LIMIT 50
");
$dovvsuCases = $stmt->fetchAll();

foreach ($dovvsuCases as $case) {
    $priorityClass = [
        'Low' => 'badge-secondary',
        'Medium' => 'badge-info',
        'High' => 'badge-warning',
        'Critical' => 'badge-danger'
    ][$case['case_priority']] ?? 'badge-secondary';
    
    $statusClass = [
        'Open' => 'badge-primary',
        'Under Investigation' => 'badge-warning',
        'Closed' => 'badge-success',
        'Suspended' => 'badge-secondary'
    ][$case['status']] ?? 'badge-secondary';
    
    $content .= '
                                                <tr>
                                                    <td><a href="' . url('/cases/' . $case['id']) . '">' . htmlspecialchars($case['case_number']) . '</a></td>
                                                    <td>' . htmlspecialchars($case['case_category'] ?? 'N/A') . '</td>
                                                    <td><span class="badge ' . $priorityClass . '">' . htmlspecialchars($case['case_priority']) . '</span></td>
                                                    <td><span class="badge ' . $statusClass . '">' . htmlspecialchars($case['status']) . '</span></td>
                                                    <td>' . date('M d, Y', strtotime($case['created_at'])) . '</td>
                                                    <td>
                                                        <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> View
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
                        </div>
                    </div>

                    <!-- Other unit tabs would follow similar pattern -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unit Performance Chart -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cases by Specialized Unit</h3>
            </div>
            <div class="card-body">
                <canvas id="unitCasesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Case Resolution Rate by Unit</h3>
            </div>
            <div class="card-body">
                <canvas id="unitResolutionChart"></canvas>
            </div>
        </div>
    </div>
</div>
';

$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Cases by Unit Chart
    const unitCasesCtx = document.getElementById("unitCasesChart").getContext("2d");
    new Chart(unitCasesCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column($unitStats, 'specialized_unit')) . ',
            datasets: [{
                label: "Total Cases",
                data: ' . json_encode(array_column($unitStats, 'total_cases')) . ',
                backgroundColor: "rgba(54, 162, 235, 0.5)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Resolution Rate Chart
    const resolutionData = ' . json_encode(array_map(function($u) {
        return $u['total_cases'] > 0 ? round(($u['closed_cases'] / $u['total_cases']) * 100, 1) : 0;
    }, $unitStats)) . ';
    
    const unitResolutionCtx = document.getElementById("unitResolutionChart").getContext("2d");
    new Chart(unitResolutionCtx, {
        type: "doughnut",
        data: {
            labels: ' . json_encode(array_column($unitStats, 'specialized_unit')) . ',
            datasets: [{
                data: resolutionData,
                backgroundColor: [
                    "rgba(255, 99, 132, 0.5)",
                    "rgba(54, 162, 235, 0.5)",
                    "rgba(255, 206, 86, 0.5)",
                    "rgba(75, 192, 192, 0.5)",
                    "rgba(153, 102, 255, 0.5)",
                    "rgba(255, 159, 64, 0.5)"
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
});
</script>
';

include __DIR__ . '/../layouts/main.php';
?>
