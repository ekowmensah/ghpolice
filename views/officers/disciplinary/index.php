<?php
$title = 'Disciplinary Actions';

$content = '
<style>
.disciplinary-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border-left: 4px solid;
    position: relative;
}

.disciplinary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.disciplinary-active { border-left-color: #dc3545; }
.disciplinary-completed { border-left-color: #28a745; }
.disciplinary-investigation { border-left-color: #ffc107; }
.disciplinary-cleared { border-left-color: #17a2b8; }
.disciplinary-appeal { border-left-color: #6f42c1; }

.disciplinary-stats {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
}

.stat-item {
    text-align: center;
    border-right: 1px solid rgba(255,255,255,0.2);
}

.stat-item:last-child {
    border-right: none;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.offence-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.action-badge {
    font-weight: 600;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.875rem;
}

.action-warning { background: #fff3cd; color: #856404; }
.action-suspension { background: #f8d7da; color: #721c24; }
.action-demotion { background: #e2e3e5; color: #383d41; }
.action-dismissal { background: #d1ecf1; color: #0c5460; }
.action-fine { background: #d4edda; color: #155724; }

.incident-date {
    background: #f8d7da;
    color: #721c24;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.severity-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
}

.severity-high { background: #dc3545; }
.severity-medium { background: #ffc107; }
.severity-low { background: #28a745; }

.officer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.875rem;
}

.filter-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.timeline-indicator {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0 0 0 8px;
}

.indicator-active { background: #dc3545; color: white; }
.indicator-completed { background: #28a745; color: white; }
.indicator-investigation { background: #ffc107; color: #212529; }
.indicator-cleared { background: #17a2b8; color: white; }
.indicator-appeal { background: #6f42c1; color: white; }

.alert-section {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.alert-item {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding: 0.75rem 0;
}

.alert-item:last-child {
    border-bottom: none;
}

.duration-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1 class="text-white"><i class="fas fa-exclamation-triangle"></i> Disciplinary Actions</h1>
            </div>
            <div class="col-md-6 text-right">
                <div class="btn-group">
                    <a href="' . url('/officers/disciplinary?status=Active') . '" class="btn btn-danger">
                        <i class="fas fa-exclamation-circle"></i> Active
                    </a>
                    <a href="' . url('/officers/disciplinary?status=Completed') . '" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Completed
                    </a>
                    <a href="' . url('/officers/disciplinary') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="disciplinary-stats mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count($records) . '</span>
                        <span class="stat-label">Total Cases</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($records, fn($r) => $r['status'] === 'Active')) . '</span>
                        <span class="stat-label">Active Cases</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($records, fn($r) => $r['status'] === 'Under Investigation')) . '</span>
                        <span class="stat-label">Under Investigation</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($records, fn($r) => $r['disciplinary_action'] === 'Suspension')) . '</span>
                        <span class="stat-label">Suspensions</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="alert-section mb-4">
            <h5 class="mb-3"><i class="fas fa-bell"></i> Active Disciplinary Alerts</h5>';

$active = array_filter($records, fn($r) => in_array($r['status'], ['Active', 'Under Investigation']));
if (!empty($active)) {
    foreach (array_slice($active, 0, 3) as $record) {
        $content .= '
            <div class="alert-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>' . sanitize($record['offence_type']) . '</strong>
                        <div class="small">
                            ' . sanitize($record['officer_name']) . ' • 
                            ' . date('d M Y', strtotime($record['incident_date'])) . '
                        </div>
                    </div>
                    <span class="duration-badge">
                        ' . sanitize($record['disciplinary_action']) . '
                    </span>
                </div>
            </div>';
    }
} else {
    $content .= '
            <div class="text-center py-2">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <p class="mb-0">No active disciplinary cases</p>
            </div>';
}

$content .= '
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Under Investigation">Under Investigation</option>
                        <option value="Action Taken">Action Taken</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Appeal Pending">Appeal Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="actionFilter">
                        <option value="">All Actions</option>
                        <option value="Warning">Warning</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Demotion">Demotion</option>
                        <option value="Dismissal">Dismissal</option>
                        <option value="Fine">Fine</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="yearFilter">
                        <option value="">All Years</option>';

$years = array_unique(array_map(fn($r) => date('Y', strtotime($r['incident_date'])), $records));
rsort($years);
foreach ($years as $year) {
    $content .= '<option value="' . $year . '">' . $year . '</option>';
}

$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search officer or offence...">
                </div>
            </div>
        </div>

        <!-- Disciplinary Records Grid -->
        <div class="row" id="disciplinaryGrid">';

foreach ($records as $record) {
    $severity = 'low';
    if (in_array($record['disciplinary_action'], ['Dismissal', 'Demotion'])) $severity = 'high';
    elseif (in_array($record['disciplinary_action'], ['Suspension'])) $severity = 'medium';
    
    $content .= '
            <div class="col-lg-6 col-xl-4 mb-4 disciplinary-item" 
                 data-status="' . sanitize($record['status']) . '"
                 data-action="' . sanitize($record['disciplinary_action']) . '"
                 data-year="' . date('Y', strtotime($record['incident_date'])) . '"
                 data-search="' . strtolower(sanitize($record['offence_type'] . ' ' . $record['officer_name'])) . '">
                <div class="card disciplinary-card h-100 disciplinary-' . strtolower(str_replace(' ', '-', $record['status'])) . '">
                    <div class="timeline-indicator indicator-' . strtolower(str_replace(' ', '-', $record['status'])) . '">
                        ' . sanitize($record['status']) . '
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="officer-avatar mr-2">
                                    ' . substr(sanitize($record['officer_name']), 0, 1) . '
                                </div>
                                <div>
                                    <h6 class="card-title mb-0">' . sanitize($record['officer_name']) . '</h6>
                                    <small class="text-muted">' . sanitize($record['rank_name'] ?? '') . '</small>
                                </div>
                            </div>
                            <span class="severity-indicator severity-' . $severity . '"></span>
                        </div>

                        <div class="mb-3">
                            <span class="offence-type-badge bg-danger text-white">
                                ' . sanitize($record['offence_type']) . '
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="incident-date mb-2">
                                <i class="far fa-calendar"></i>
                                ' . date('d M Y', strtotime($record['incident_date'])) . '
                            </div>';

    if ($record['reported_date'] != $record['incident_date']) {
        $content .= '
                            <div class="text-muted small">
                                <i class="fas fa-clock"></i> Reported: ' . date('d M Y', strtotime($record['reported_date'])) . '
                            </div>';
    }

    $content .= '
                        </div>';

    if (!empty($record['offence_description'])) {
        $content .= '
                            <p class="text-muted small mb-3">
                                ' . substr(sanitize($record['offence_description']), 0, 100) . '...
                            </p>';
    }

    $content .= '
                        <div class="mb-3">
                            <span class="action-badge action-' . strtolower(str_replace(' ', '-', $record['disciplinary_action'])) . '">
                                <i class="fas fa-gavel"></i> ' . sanitize($record['disciplinary_action']) . '
                            </span>';

    if ($record['start_date'] && $record['end_date']) {
        $content .= '
                            <div class="text-muted small mt-2">
                                Duration: ' . ((strtotime($record['end_date']) - strtotime($record['start_date'])) / 86400) . ' days
                            </div>';
    }

    $content .= '
                        </div>';

    if (!empty($record['action_details'])) {
        $content .= '
                            <div class="text-muted small mb-3">
                                <i class="fas fa-info-circle"></i> ' . substr(sanitize($record['action_details']), 0, 80) . '...
                            </div>';
    }

    $content .= '
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewDisciplinaryRecord(' . $record['id'] . ')">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>';
}

$content .= '
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No disciplinary records found</h5>
            <p class="text-muted">Try adjusting your filters or search criteria</p>
        </div>
    </div>
</section>';

$scripts = '
<script>
$(document).ready(function() {
    // Filter functionality
    function applyFilters() {
        const statusFilter = $("#statusFilter").val();
        const actionFilter = $("#actionFilter").val();
        const yearFilter = $("#yearFilter").val();
        const searchFilter = $("#searchFilter").val().toLowerCase();
        
        let visibleCount = 0;
        
        $(".disciplinary-item").each(function() {
            const $item = $(this);
            const status = $item.data("status");
            const action = $item.data("action");
            const year = $item.data("year").toString();
            const search = $item.data("search");
            
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesAction = !actionFilter || action === actionFilter;
            const matchesYear = !yearFilter || year === yearFilter;
            const matchesSearch = !searchFilter || search.includes(searchFilter);
            
            if (matchesStatus && matchesAction && matchesYear && matchesSearch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        
        $("#noResults").toggle(visibleCount === 0);
    }
    
    $("#statusFilter, #actionFilter, #yearFilter, #searchFilter").on("input change", applyFilters);
    
    // View disciplinary record
    window.viewDisciplinaryRecord = function(id) {
        window.location.href = "' . url('/officers/disciplinary/view/') . '" + id;
    };
});
</script>';

include __DIR__ . '/../../layouts/main.php';
?>
