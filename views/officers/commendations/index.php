<?php
$title = 'Commendations & Awards';

$content = '
<style>
.achievement-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border-left: 4px solid;
    position: relative;
    overflow: hidden;
}

.achievement-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.achievement-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, transparent 50%, rgba(255,215,0,0.1) 50%);
    border-radius: 0 0 0 100%;
}

.achievement-excellence { border-left-color: #ffd700; }
.achievement-bravery { border-left-color: #ff6b6b; }
.achievement-service { border-left-color: #4ecdc4; }
.achievement-merit { border-left-color: #95e1d3; }
.achievement-longevity { border-left-color: #a8e6cf; }

.commendation-stats {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

.achievement-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.achievement-date {
    background: #fff3cd;
    color: #856404;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.achievement-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.icon-excellence { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #b8860b; }
.icon-bravery { background: linear-gradient(135deg, #ff6b6b, #ff8787); color: white; }
.icon-service { background: linear-gradient(135deg, #4ecdc4, #6ee7df); color: white; }
.icon-merit { background: linear-gradient(135deg, #95e1d3, #a8e6cf); color: #2c5f2d; }
.icon-longevity { background: linear-gradient(135deg, #a8e6cf, #c3f0ca); color: #2c5f2d; }

.officer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
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

.certificate-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.recent-achievements {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.recent-item {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding: 0.75rem 0;
}

.recent-item:last-child {
    border-bottom: none;
}

.star-rating {
    color: #ffd700;
    font-size: 0.875rem;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1 class="text-white"><i class="fas fa-trophy"></i> Commendations & Awards</h1>
            </div>
            <div class="col-md-6 text-right">
                <a href="' . url('/officers/commendations/create') . '" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Record Commendation
                </a>
                <button class="btn btn-success ml-2" onclick="exportCommendations()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="commendation-stats mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count($commendations) . '</span>
                        <span class="stat-label">Total Awards</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_unique(array_column($commendations, 'officer_id'))) . '</span>
                        <span class="stat-label">Officers Honored</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($commendations, fn($c) => date('Y', strtotime($c['commendation_date'])) == date('Y'))) . '</span>
                        <span class="stat-label">This Year</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($commendations, fn($c) => $c['commendation_type'] === 'Excellence')) . '</span>
                        <span class="stat-label">Excellence Awards</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="recent-achievements mb-4">
            <h5 class="mb-3"><i class="fas fa-star"></i> Recent Achievements</h5>';

$recent = array_slice($commendations, 0, 3);
foreach ($recent as $commendation) {
    $content .= '
            <div class="recent-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>' . sanitize($commendation['commendation_title']) . '</strong>
                        <div class="small">
                            ' . sanitize($commendation['officer_name']) . ' • 
                            ' . date('d M Y', strtotime($commendation['commendation_date'])) . '
                        </div>
                    </div>
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>';
}

$content .= '
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-control" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="Excellence">Excellence</option>
                        <option value="Bravery">Bravery</option>
                        <option value="Service">Service</option>
                        <option value="Merit">Merit</option>
                        <option value="Longevity">Longevity</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="yearFilter">
                        <option value="">All Years</option>';

$years = array_unique(array_map(fn($c) => date('Y', strtotime($c['commendation_date'])), $commendations));
rsort($years);
foreach ($years as $year) {
    $content .= '<option value="' . $year . '">' . $year . '</option>';
}

$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="rankFilter">
                        <option value="">All Ranks</option>';

$ranks = array_unique(array_column($commendations, 'rank_name'));
sort($ranks);
foreach ($ranks as $rank) {
    $content .= '<option value="' . sanitize($rank) . '">' . sanitize($rank) . '</option>';
}

$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search officer or award...">
                </div>
            </div>
        </div>

        <!-- Achievement Cards Grid -->
        <div class="row" id="achievementGrid">';

foreach ($commendations as $commendation) {
    $content .= '
            <div class="col-lg-6 col-xl-4 mb-4 achievement-item" 
                 data-type="' . sanitize($commendation['commendation_type']) . '"
                 data-year="' . date('Y', strtotime($commendation['commendation_date'])) . '"
                 data-rank="' . sanitize($commendation['rank_name'] ?? '') . '"
                 data-search="' . strtolower(sanitize($commendation['commendation_title'] . ' ' . $commendation['officer_name'])) . '">
                <div class="card achievement-card h-100 achievement-' . strtolower(str_replace(' ', '-', $commendation['commendation_type'])) . '">
                    <div class="card-body">
                        <div class="achievement-icon icon-' . strtolower(str_replace(' ', '-', $commendation['commendation_type'])) . '">
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="officer-avatar mr-2">
                                    ' . substr(sanitize($commendation['officer_name']), 0, 1) . '
                                </div>
                                <div>
                                    <h6 class="card-title mb-0">' . sanitize($commendation['officer_name']) . '</h6>
                                    <small class="text-muted">' . sanitize($commendation['rank_name'] ?? '') . '</small>
                                </div>
                            </div>
                            <span class="achievement-type-badge bg-warning text-dark">
                                ' . sanitize($commendation['commendation_type']) . '
                            </span>
                        </div>

                        <h5 class="mb-3">' . sanitize($commendation['commendation_title']) . '</h5>';

    if (!empty($commendation['commendation_description'])) {
        $content .= '
                        <p class="text-muted small mb-3">
                            ' . substr(sanitize($commendation['commendation_description']), 0, 100) . '...
                        </p>';
    }

    $content .= '
                        <div class="mb-3">
                            <div class="achievement-date mb-2">
                                <i class="far fa-calendar"></i>
                                ' . date('d M Y', strtotime($commendation['commendation_date'])) . '
                            </div>';

    if ($commendation['awarded_by']) {
        $content .= '
                            <div class="text-muted small">
                                <i class="fas fa-user-tie"></i> Awarded by: ' . sanitize($commendation['awarded_by']) . '
                            </div>';
    }

    $content .= '
                        </div>';

    if ($commendation['certificate_number']) {
        $content .= '
                        <div class="mb-3">
                            <span class="certificate-badge">
                                <i class="fas fa-certificate"></i> ' . sanitize($commendation['certificate_number']) . '
                            </span>
                        </div>';
    }

    $content .= '
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewAchievement(' . $commendation['id'] . ')">
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
            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No commendations found</h5>
            <p class="text-muted">Try adjusting your filters or search criteria</p>
        </div>
    </div>
</section>';

$scripts = '
<script>
$(document).ready(function() {
    // Filter functionality
    function applyFilters() {
        const typeFilter = $("#typeFilter").val();
        const yearFilter = $("#yearFilter").val();
        const rankFilter = $("#rankFilter").val();
        const searchFilter = $("#searchFilter").val().toLowerCase();
        
        let visibleCount = 0;
        
        $(".achievement-item").each(function() {
            const $item = $(this);
            const type = $item.data("type");
            const year = $item.data("year").toString();
            const rank = $item.data("rank");
            const search = $item.data("search");
            
            const matchesType = !typeFilter || type === typeFilter;
            const matchesYear = !yearFilter || year === yearFilter;
            const matchesRank = !rankFilter || rank.includes(rankFilter);
            const matchesSearch = !searchFilter || search.includes(searchFilter);
            
            if (matchesType && matchesYear && matchesRank && matchesSearch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        
        $("#noResults").toggle(visibleCount === 0);
    }
    
    $("#typeFilter, #yearFilter, #rankFilter, #searchFilter").on("input change", applyFilters);
    
    // View achievement details
    window.viewAchievement = function(id) {
        window.location.href = "' . url('/officers/commendations/view/') . '" + id;
    };
    
    // Export commendations
    window.exportCommendations = function() {
        // Implement export functionality
        console.log("Export commendations");
    };
});
</script>';

include __DIR__ . '/../../layouts/main.php';
?>
