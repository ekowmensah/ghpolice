<?php
$title = 'Training Management';

$content = '
<style>
.training-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border-left: 4px solid #007bff;
}

.training-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.15);
}

.training-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.training-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.filter-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.training-institution {
    color: #6c757d;
    font-size: 0.875rem;
}

.training-duration {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.grade-badge {
    font-weight: bold;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
}

.grade-excellent { background: #d4edda; color: #155724; }
.grade-good { background: #cce5ff; color: #004085; }
.grade-average { background: #fff3cd; color: #856404; }
.grade-poor { background: #f8d7da; color: #721c24; }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1 class="text-white"><i class="fas fa-graduation-cap"></i> Training Management</h1>
            </div>
            <div class="col-md-6 text-right">
                <a href="' . url('/officers/training/create') . '" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Record Training
                </a>
                <a href="' . url('/officers/training/upcoming') . '" class="btn btn-info ml-2">
                    <i class="fas fa-calendar-alt"></i> Upcoming
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Officer Information (if specific officer) -->
';

if (isset($officer)) {
    $content .= '
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-user-shield fa-3x text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge badge-dark">' . sanitize($officer['rank_name']) . '</span>
                            <span class="ml-2">Service No: ' . sanitize($officer['service_number']) . '</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>';
}

$content .= '
        <!-- Statistics Cards -->
        <div class="training-stats mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count($trainings) . '</span>
                        <span class="stat-label">Total Training</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($trainings, fn($t) => $t['training_type'] === 'Basic Training')) . '</span>
                        <span class="stat-label">Basic Training</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($trainings, fn($t) => $t['training_type'] === 'Advanced Training')) . '</span>
                        <span class="stat-label">Advanced</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($trainings, fn($t) => !empty($t['grade_score']))) . '</span>
                        <span class="stat-label">With Grades</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-control" id="typeFilter">
                        <option value="">All Training Types</option>
                        <option value="Basic Training">Basic Training</option>
                        <option value="Advanced Training">Advanced Training</option>
                        <option value="Specialized Course">Specialized Course</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Certification">Certification</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-control" id="yearFilter">
                        <option value="">All Years</option>';

$years = array_unique(array_map(fn($t) => date('Y', strtotime($t['start_date'])), $trainings));
rsort($years);
foreach ($years as $year) {
    $content .= '<option value="' . $year . '">' . $year . '</option>';
}

$content .= '
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search training or officer...">
                </div>
            </div>
        </div>

        <!-- Training Records Grid -->
        <div class="row" id="trainingGrid">';

foreach ($trainings as $training) {
    $content .= '
            <div class="col-lg-6 col-xl-4 mb-4 training-item" 
                 data-type="' . sanitize($training['training_type']) . '"
                 data-year="' . date('Y', strtotime($training['start_date'])) . '"
                 data-search="' . strtolower(sanitize($training['training_name'] . ' ' . ($training['officer_name'] ?? ''))) . '">
                <div class="card training-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title mb-1">' . sanitize($training['training_name']) . '</h6>
                                <div class="training-institution">
                                    <i class="fas fa-university"></i> ' . sanitize($training['training_institution'] ?? 'N/A') . '
                                </div>
                            </div>
                            <span class="training-type-badge bg-primary text-white">
                                ' . sanitize($training['training_type']) . '
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="training-duration mb-2">
                                <i class="far fa-calendar"></i>
                                ' . date('d M Y', strtotime($training['start_date'])) . ' - 
                                ' . date('d M Y', strtotime($training['end_date'])) . '
                                <span class="ml-2">
                                    (' . ((strtotime($training['end_date']) - strtotime($training['start_date'])) / 86400) . ' days)
                                </span>
                            </div>
                        </div>';

    if (isset($training['officer_name'])) {
        $content .= '
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-user"></i>
                                ' . sanitize($training['officer_name']) . ' 
                                (' . sanitize($training['rank_name'] ?? '') . ')
                            </small>
                        </div>';
    }

    if ($training['grade_score']) {
        $grade = strtoupper($training['grade_score']);
        $gradeClass = 'grade-poor';
        if (in_array($grade, ['A', 'EXCELLENT', 'DISTINCTION'])) $gradeClass = 'grade-excellent';
        elseif (in_array($grade, ['B', 'GOOD', 'MERIT'])) $gradeClass = 'grade-good';
        elseif (in_array($grade, ['C', 'AVERAGE', 'PASS'])) $gradeClass = 'grade-average';
        
        $content .= '
                        <div class="mb-2">
                            <span class="grade-badge ' . $gradeClass . '">
                                <i class="fas fa-award"></i> ' . sanitize($training['grade_score']) . '
                            </span>
                        </div>';
    }

    if ($training['certificate_number']) {
        $content .= '
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-certificate"></i> Cert: ' . sanitize($training['certificate_number']) . '
                            </small>
                        </div>';
    }

    $content .= '
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewTrainingDetails(' . $training['id'] . ')">
                                <i class="fas fa-eye"></i> Details
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
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No training records found</h5>
            <p class="text-muted">Try adjusting your filters or search criteria</p>
        </div>
    </div>
</section>';

$scripts = '
<script>
$(document).ready(function() {
    // Filter functionality
    function applyFilters() {
        const typeFilter = $("#typeFilter").val().toLowerCase();
        const yearFilter = $("#yearFilter").val();
        const searchFilter = $("#searchFilter").val().toLowerCase();
        
        let visibleCount = 0;
        
        $(".training-item").each(function() {
            const $item = $(this);
            const type = $item.data("type").toLowerCase();
            const year = $item.data("year").toString();
            const search = $item.data("search");
            
            const matchesType = !typeFilter || type.includes(typeFilter);
            const matchesYear = !yearFilter || year === yearFilter;
            const matchesSearch = !searchFilter || search.includes(searchFilter);
            
            if (matchesType && matchesYear && matchesSearch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        
        $("#noResults").toggle(visibleCount === 0);
    }
    
    $("#typeFilter, #yearFilter, #searchFilter").on("input change", applyFilters);
    
    // View training details
    window.viewTrainingDetails = function(id) {
        // This would typically open a modal or navigate to detail page
        console.log("View training details for ID:", id);
    };
});
</script>';

include __DIR__ . '/../../layouts/main.php';
?>
