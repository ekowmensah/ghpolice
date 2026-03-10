<?php
$title = 'Court Calendar';

$content = '
<style>
:root {
    --gp-navy: #112c4d;
    --gp-navy-2: #1a406d;
    --gp-navy-3: #243a5a;
    --gp-gold: #c7a13f;
    --gp-gold-2: #d4af37;
    --gp-red: #d94a3a;
    --gp-green: #1f7a3d;
    --gp-teal: #17a2b8;
    --gp-purple: #6f42c1;
    --gp-pink: #f093fb;
    --gp-bg: #eef3f9;
    --gp-text: #1c2630;
    --gp-muted: #607086;
    --gp-border: #d4deea;
    --gp-light: #f5f8fc;
}

.gp-court-header {
    background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-2) 50%, var(--gp-gold) 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(17, 44, 77, 0.3);
}

.gp-court-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 30px 30px;
}

.gp-court-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-align: center;
}

.gp-court-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    text-align: center;
    margin-bottom: 2rem;
}

.gp-court-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
}

.gp-stat-item {
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.gp-stat-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-5px);
}

.gp-stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.gp-stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.gp-section-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid var(--gp-border);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gp-section-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.gp-section-header {
    padding: 1.5rem 2rem;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.gp-section-header::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gp-gold);
}

.gp-section-calendar { background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2)); }
.gp-section-upcoming { background: linear-gradient(135deg, var(--gp-teal), #20c997); }
.gp-section-daily { background: linear-gradient(135deg, var(--gp-purple), #9561e2); }

.gp-section-body {
    padding: 2rem;
}

.gp-form {
    background: var(--gp-light);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--gp-navy);
}

.gp-form-group label {
    font-weight: 600;
    color: var(--gp-navy);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.gp-btn {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(17, 44, 77, 0.2);
}

.gp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(17, 44, 77, 0.3);
}

.gp-btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.gp-btn-success:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.gp-btn-info {
    background: linear-gradient(135deg, var(--gp-teal), #138496);
}

.gp-btn-info:hover {
    box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
}

.gp-btn-primary {
    background: linear-gradient(135deg, var(--gp-purple), #9561e2);
}

.gp-hearing-card {
    background: white;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid var(--gp-border);
    transition: all 0.3s ease;
}

.gp-hearing-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}

.gp-hearing-header {
    background: linear-gradient(135deg, var(--gp-light), #e9ecef);
    padding: 1rem 1.5rem;
    border-bottom: 2px solid var(--gp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.gp-hearing-date {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gp-navy);
}

.gp-hearing-count {
    background: var(--gp-gold);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.gp-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.gp-table th {
    background: var(--gp-light);
    color: var(--gp-navy);
    font-weight: 600;
    padding: 0.75rem;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--gp-border);
}

.gp-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--gp-border);
    color: var(--gp-text);
    font-size: 0.9rem;
}

.gp-table tr:hover {
    background: var(--gp-light);
}

.gp-badge {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.gp-badge-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.gp-badge-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
.gp-badge-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.gp-badge-info { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
.gp-badge-secondary { background: linear-gradient(135deg, #6c757d, #545b62); color: white; }

.gp-alert {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    border: 1px solid #bee5eb;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    color: #0c5460;
}

.gp-alert i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .gp-court-header {
        padding: 2rem 1rem;
    }
    
    .gp-court-title {
        font-size: 2rem;
    }
    
    .gp-court-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .gp-section-body {
        padding: 1.5rem;
    }
}
</style>

<!-- Ghana Police Court Calendar Header -->
<div class="gp-court-header text-center">
    <div class="position-relative" style="z-index: 2;">
        <h1 class="gp-court-title">
            <i class="fas fa-gavel"></i> Court Calendar
        </h1>
        <div class="gp-court-subtitle">
            ' . date('F Y', strtotime($selected_month . '-01')) . '
        </div>
        
        <div class="gp-court-stats">
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (!empty($hearings) ? count($hearings) : 0) . '</span>
                <span class="gp-stat-label">Total Hearings</span>
            </div>
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (!empty($hearings) ? count(array_unique(array_column($hearings, 'court_name'))) : 0) . '</span>
                <span class="gp-stat-label">Courts</span>
            </div>
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (!empty($hearings) ? count(array_filter($hearings, fn($h) => $h['case_priority'] === 'High')) : 0) . '</span>
                <span class="gp-stat-label">High Priority</span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Filter Form -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-calendar">
            <div>
                <i class="fas fa-filter"></i> Filter Hearings
            </div>
            <div>
                <a href="' . url('/court/upcoming') . '" class="btn gp-btn-info btn-sm">
                    <i class="fas fa-calendar-alt"></i> Upcoming Hearings
                </a>
                <a href="' . url('/court-calendar/daily') . '" class="btn gp-btn-primary btn-sm">
                    <i class="fas fa-calendar-day"></i> Daily Schedule
                </a>
            </div>
        </div>
        <div class="gp-section-body">';

$content .= '
                <div class="gp-form">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month</label>
                                    <input type="month" name="month" class="form-control" 
                                           value="' . htmlspecialchars($selected_month) . '">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Court</label>
                                    <select name="court" class="form-control">
                                        <option value="">All Courts</option>';

foreach ($courts as $court) {
    $content .= '
                                        <option value="' . htmlspecialchars($court) . '" ' . 
                                        ($selected_court == $court ? 'selected' : '') . '>' . 
                                        htmlspecialchars($court) . '
                                        </option>';
}

$content .= '
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn gp-btn btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

if (empty($hearings)) {
    $content .= '
            <div class="gp-alert">
                <i class="fas fa-info-circle"></i>
                <strong>No hearings scheduled for this month.</strong>
            </div>';
} else {
    // Group hearings by date
    $hearingsByDate = [];
    foreach ($hearings as $hearing) {
        $date = date('Y-m-d', strtotime($hearing['hearing_date']));
        if (!isset($hearingsByDate[$date])) {
            $hearingsByDate[$date] = [];
        }
        $hearingsByDate[$date][] = $hearing;
    }
    
    foreach ($hearingsByDate as $date => $dayHearings) {
        $content .= '
            <div class="gp-hearing-card">
                <div class="gp-hearing-header">
                    <div class="gp-hearing-date">
                        <i class="fas fa-calendar-day"></i>
                        ' . date('l, F d, Y', strtotime($date)) . '
                    </div>
                    <div class="gp-hearing-count">
                        ' . count($dayHearings) . ' Hearings
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="gp-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Case Number</th>
                                <th>Court</th>
                                <th>Hearing Type</th>
                                <th>Judge</th>
                                <th>Suspect</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($dayHearings as $hearing) {
            $content .= '
                            <tr>
                                <td>' . date('H:i', strtotime($hearing['hearing_date'])) . '</td>
                                <td>
                                    <a href="' . url('/cases/view/' . $hearing['case_id']) . '" class="text-primary font-weight-bold">
                                        ' . htmlspecialchars($hearing['case_number']) . '
                                    </a>
                                </td>
                                <td>' . htmlspecialchars($hearing['court_name']) . '</td>
                                <td>
                                    <span class="gp-badge gp-badge-info">
                                        ' . htmlspecialchars($hearing['hearing_type']) . '
                                    </span>
                                </td>
                                <td>' . htmlspecialchars($hearing['judge_name'] ?? 'TBA') . '</td>
                                <td>' . htmlspecialchars($hearing['suspect_name'] ?? 'N/A') . '</td>
                                <td>
                                    <span class="gp-badge gp-badge-' . 
                                    ($hearing['case_priority'] === 'High' ? 'danger' : 
                                     ($hearing['case_priority'] === 'Medium' ? 'warning' : 'success')) . '">
                                        ' . htmlspecialchars($hearing['case_priority']) . '
                                    </span>
                                </td>
                                <td>
                                    <a href="' . url('/cases/' . $hearing['case_id'] . '/court') . '" 
                                       class="btn gp-btn gp-btn-info btn-xs" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>';
        }

        $content .= '
                        </tbody>
                    </table>
                </div>
            </div>';
    }
}

$content .= '
                </div>
            </div>
        </div>
    </section>
</div>';

$breadcrumbs = [
    ['title' => 'Court Calendar']
];

include __DIR__ . '/../layouts/main.php';
?>
