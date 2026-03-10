<?php
$title = 'Warrant Management';

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

.gp-warrants-header {
    background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-2) 50%, var(--gp-gold) 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(17, 44, 77, 0.3);
}

.gp-warrants-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 30px 30px;
}

.gp-warrants-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-align: center;
}

.gp-warrants-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    text-align: center;
    margin-bottom: 2rem;
}

.gp-warrants-stats {
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

.gp-section-warrants { background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2)); }
.gp-section-active { background: linear-gradient(135deg, var(--gp-red), #e74c3c); }
.gp-section-filter { background: linear-gradient(135deg, var(--gp-teal), #20c997); }

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

.gp-btn-danger {
    background: linear-gradient(135deg, var(--gp-red), #e74c3c);
}

.gp-btn-danger:hover {
    box-shadow: 0 8px 25px rgba(217, 74, 58, 0.3);
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

.gp-warrant-card {
    background: white;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid var(--gp-border);
    transition: all 0.3s ease;
}

.gp-warrant-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
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
    .gp-warrants-header {
        padding: 2rem 1rem;
    }
    
    .gp-warrants-title {
        font-size: 2rem;
    }
    
    .gp-warrants-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .gp-section-body {
        padding: 1.5rem;
    }
}
</style>

<!-- Ghana Police Warrants Header -->
<div class="gp-warrants-header text-center">
    <div class="position-relative" style="z-index: 2;">
        <h1 class="gp-warrants-title">
            <i class="fas fa-shield-alt"></i> Warrant Management
        </h1>
        <div class="gp-warrants-subtitle">
            Judicial Warrant Tracking System
        </div>
        
        <div class="gp-warrants-stats">
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (empty($warrants) ? 0 : count($warrants)) . '</span>
                <span class="gp-stat-label">Total Warrants</span>
            </div>
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['status'] === 'Active')) ) . '</span>
                <span class="gp-stat-label">Active</span>
            </div>
            <div class="gp-stat-item">
                <span class="gp-stat-number">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['status'] === 'Executed')) ) . '</span>
                <span class="gp-stat-label">Executed</span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Quick Actions -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-active">
            <div>
                <i class="fas fa-exclamation-triangle"></i> Quick Actions
            </div>
            <div>
                <a href="' . url('/warrants/active') . '" class="btn gp-btn gp-btn-danger btn-sm">
                    <i class="fas fa-exclamation-triangle"></i> Active Warrants
                </a>
                <a href="' . url('/warrants/create') . '" class="btn gp-btn gp-btn-success btn-sm">
                    <i class="fas fa-plus"></i> New Warrant
                </a>
            </div>
        </div>
        <div class="gp-section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="gp-stat-item">
                        <i class="fas fa-gavel fa-2x"></i>
                        <div class="mt-2">
                            <strong>Arrest Warrants</strong>
                            <div class="text-muted">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['warrant_type'] === 'Arrest'))) . '</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="gp-stat-item">
                        <i class="fas fa-search fa-2x"></i>
                        <div class="mt-2">
                            <strong>Search Warrants</strong>
                            <div class="text-muted">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['warrant_type'] === 'Search'))) . '</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="gp-stat-item">
                        <i class="fas fa-balance-scale fa-2x"></i>
                        <div class="mt-2">
                            <strong>Bench Warrants</strong>
                            <div class="text-muted">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['warrant_type'] === 'Bench'))) . '</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="gp-stat-item">
                        <i class="fas fa-clock fa-2x"></i>
                        <div class="mt-2">
                            <strong>Pending</strong>
                            <div class="text-muted">' . (empty($warrants) ? 0 : count(array_filter($warrants, fn($w) => $w['status'] === 'Active' && $w['warrant_type'] === 'Arrest'))) . '</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Form -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-filter">
            <div>
                <i class="fas fa-filter"></i> Filter Warrants
            </div>
        </div>
        <div class="gp-section-body">';

$content .= '
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="Active" ' . ($selected_status == 'Active' ? 'selected' : '') . '>Active</option>
                                        <option value="Executed" ' . ($selected_status == 'Executed' ? 'selected' : '') . '>Executed</option>
                                        <option value="Cancelled" ' . ($selected_status == 'Cancelled' ? 'selected' : '') . '>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="Arrest Warrant" ' . ($selected_type == 'Arrest Warrant' ? 'selected' : '') . '>Arrest Warrant</option>
                                        <option value="Search Warrant" ' . ($selected_type == 'Search Warrant' ? 'selected' : '') . '>Search Warrant</option>
                                        <option value="Bench Warrant" ' . ($selected_type == 'Bench Warrant' ? 'selected' : '') . '>Bench Warrant</option>
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
        </div>
    </div>';

if (empty($warrants)) {
    $content .= '
            <div class="gp-alert">
                <i class="fas fa-info-circle"></i>
                <strong>No warrants found.</strong>
            </div>';
} else {
    $content .= '
            <div class="gp-section-card">
                <div class="gp-section-header gp-section-warrants">
                    <div>
                        <i class="fas fa-list"></i> Warrant Records
                    </div>
                </div>
                <div class="gp-section-body">
                    <div class="table-responsive">
                        <table class="gp-table">
                            <thead>
                                <tr>
                                    <th>Warrant Type</th>
                                    <th>Case Number</th>
                                    <th>Suspect</th>
                                    <th>Issue Date</th>
                                    <th>Time</th>
                                    <th>Issued By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>';

    foreach ($warrants as $warrant) {
        $content .= '
                                <tr>
                                    <td>
                                        <strong>' . htmlspecialchars($warrant['warrant_type']) . '</strong>
                                    </td>
                                    <td>
                                        <a href="' . url('/cases/view/' . $warrant['case_id']) . '" class="text-primary font-weight-bold">
                                            ' . htmlspecialchars($warrant['case_number']) . '
                                        </a>
                                    </td>
                                    <td>
                                        ' . htmlspecialchars($warrant['suspect_name'] ?? 'N/A') . 
                                        ($warrant['ghana_card_number'] ? '<br><small class="text-muted">' . htmlspecialchars($warrant['ghana_card_number']) . '</small>' : '') . '
                                    </td>
                                    <td>' . date('Y-m-d', strtotime($warrant['issue_date'])) . '</td>
                                    <td>' . date('H:i', strtotime($warrant['issue_date'])) . '</td>
                                    <td>' . htmlspecialchars($warrant['issued_by']) . '</td>
                                    <td>
                                        <span class="gp-badge gp-badge-' . 
                                        ($warrant['status'] === 'Active' ? 'danger' : 
                                         ($warrant['status'] === 'Executed' ? 'success' : 'secondary')) . '">
                                            ' . htmlspecialchars($warrant['status']) . '
                                        </span>
                                    </td>
                                    <td>
                                        <a href="' . url('/warrants/view/' . $warrant['id']) . '" 
                                               class="btn gp-btn gp-btn-info btn-xs" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>';
        
        if ($warrant['status'] == 'Active') {
            $content .= '
                                        <button class="btn gp-btn gp-btn-success btn-xs execute-warrant" 
                                                data-id="' . $warrant['id'] . '" title="Execute">
                                            <i class="fas fa-check"></i>
                                        </button>';
        }
        
        $content .= '
                                    </td>
                                </tr>';
    }

    $content .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>';
}

$content .= '
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Warrants']
];

include __DIR__ . '/../layouts/main.php';
?>
