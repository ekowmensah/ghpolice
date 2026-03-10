<?php
$title = 'Officer Profile - ' . sanitize($officer['service_number']);

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

.gp-profile-header {
    background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-2) 50%, var(--gp-gold) 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(17, 44, 77, 0.3);
}

.gp-profile-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 30px 30px;
}

.gp-officer-avatar {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 5px solid var(--gp-gold);
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3.5rem;
    font-weight: bold;
    margin: 0 auto 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
}

.gp-rank-insignia {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    background: var(--gp-gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gp-navy);
    font-size: 1.2rem;
    font-weight: bold;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.gp-officer-name {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.gp-rank-badge {
    background: linear-gradient(135deg, var(--gp-gold), var(--gp-gold-2));
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1rem;
    display: inline-block;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(199, 161, 63, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.gp-service-number {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-family: "Courier New", monospace;
    font-size: 1.2rem;
    display: inline-block;
    margin-bottom: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.gp-status-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.gp-status-active { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.gp-status-onleave { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
.gp-status-retired { background: linear-gradient(135deg, #6c757d, #545b62); color: white; }
.gp-status-suspended { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }

.gp-enlistment-info {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    font-size: 0.9rem;
    display: inline-block;
    margin-top: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.gp-info-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 1px solid var(--gp-border);
    margin-bottom: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gp-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.gp-card-header {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    color: white;
    padding: 1.5rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-bottom: 3px solid var(--gp-gold);
}

.gp-card-body {
    padding: 2rem;
}

.gp-info-section {
    background: var(--gp-light);
    border-left: 4px solid var(--gp-navy);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    transition: all 0.3s ease;
}

.gp-info-section:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.gp-info-section h6 {
    color: var(--gp-navy);
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.gp-info-section p {
    margin-bottom: 0;
    color: var(--gp-text);
    font-size: 1rem;
    line-height: 1.6;
}

.gp-contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.gp-contact-item:hover {
    background: var(--gp-light);
    transform: translateX(5px);
}

.gp-contact-item i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.25rem;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(17, 44, 77, 0.2);
}

.gp-contact-item span {
    color: var(--gp-text);
    font-weight: 500;
    font-size: 1rem;
}

.gp-performance-card {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    color: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 15px 35px rgba(17, 44, 77, 0.3);
    position: relative;
    overflow: hidden;
}

.gp-performance-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 0L100 0L100 100L0 100Z\'/%3E%3C/svg%3E");
    background-size: 50px 50px;
}

.gp-performance-metric {
    text-align: center;
    padding: 1.5rem;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.gp-performance-metric:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-5px);
}

.gp-performance-number {
    font-size: 3rem;
    font-weight: 700;
    display: block;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.gp-performance-label {
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

.gp-section-training { background: linear-gradient(135deg, var(--gp-purple), #9561e2); }
.gp-section-leave { background: linear-gradient(135deg, var(--gp-teal), #20c997); }
.gp-section-commendations { background: linear-gradient(135deg, var(--gp-pink), #f5576c); }
.gp-section-disciplinary { background: linear-gradient(135deg, var(--gp-red), #ee5a6f); }
.gp-section-promotions { background: linear-gradient(135deg, #28a745, #20c997); }
.gp-section-postings { background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2)); }

.gp-section-body {
    padding: 2rem;
}

.gp-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.gp-table th {
    background: var(--gp-light);
    color: var(--gp-navy);
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--gp-border);
}

.gp-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--gp-border);
    color: var(--gp-text);
    font-size: 0.95rem;
}

.gp-table tr:hover {
    background: var(--gp-light);
}

.gp-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.gp-badge-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.gp-badge-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
.gp-badge-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.gp-badge-info { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
.gp-badge-secondary { background: linear-gradient(135deg, #6c757d, #545b62); color: white; }

.gp-timeline {
    position: relative;
    padding-left: 2rem;
    margin-top: 1.5rem;
}

.gp-timeline::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--gp-border);
}

.gp-timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 15px;
    border: 1px solid var(--gp-border);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.gp-timeline-item:hover {
    transform: translateX(10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}

.gp-timeline-dot {
    position: absolute;
    left: -2.5rem;
    top: 2rem;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--gp-navy);
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.gp-timeline-dot.current {
    background: var(--gp-gold);
    box-shadow: 0 0 0 6px rgba(199, 161, 63, 0.2);
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

.gp-btn-light {
    background: rgba(255, 255, 255, 0.9);
    color: var(--gp-navy);
}

.gp-btn-light:hover {
    background: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.gp-modal-header {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
}

.gp-modal-header .close {
    color: white;
    opacity: 0.8;
}

.gp-modal-header .close:hover {
    opacity: 1;
}

@media (max-width: 768px) {
    .gp-profile-header {
        padding: 2rem 1rem;
    }
    
    .gp-officer-name {
        font-size: 2rem;
    }
    
    .gp-officer-avatar {
        width: 120px;
        height: 120px;
        font-size: 3rem;
    }
    
    .gp-card-body, .gp-section-body {
        padding: 1.5rem;
    }
}
</style>

<!-- Ghana Police Profile Header -->
<div class="gp-profile-header text-center">
    <div class="position-relative" style="z-index: 2;">
        <div class="row align-items-center justify-content-center">
            <div class="col-md-3">
                <div class="gp-officer-avatar">
                    ' . substr(sanitize($officer['first_name'] . ' ' . $officer['last_name']), 0, 1) . '
                    <div class="gp-rank-insignia">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="gp-officer-name">' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</h1>
                <div class="gp-rank-badge">' . sanitize($officer['rank_name'] ?? 'Officer') . '</div>
                <div class="gp-service-number">Service No: ' . sanitize($officer['service_number']) . '</div>
                <div class="gp-enlistment-info">
                    <i class="fas fa-calendar"></i> Enlisted: ' . ($officer['date_of_enlistment'] ? format_date($officer['date_of_enlistment'], 'd M Y') : 'N/A') . '
                </div>
            </div>
            <div class="col-md-3">
                <div class="gp-status-badge gp-status-' . strtolower(str_replace(' ', '-', $officer['employment_status'] ?? 'active')) . '">
                    ' . sanitize($officer['employment_status'] ?? 'Active') . '
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Service Details Card -->
        <div class="gp-info-card">
            <div class="gp-card-header">
                <i class="fas fa-user-shield"></i> Service Details
            </div>
            <div class="gp-card-body">
                <div class="gp-info-section">
                    <h6><i class="fas fa-id-card"></i> Badge Number</h6>
                    <p>' . sanitize($officer['badge_number'] ?? 'N/A') . '</p>
                </div>
                
                <div class="gp-info-section">
                    <h6><i class="fas fa-birthday-cake"></i> Date of Birth</h6>
                    <p>' . ($officer['date_of_birth'] ? format_date($officer['date_of_birth'], 'd M Y') : 'N/A') . '</p>
                </div>
                
                <div class="gp-info-section">
                    <h6><i class="fas fa-venus-mars"></i> Gender</h6>
                    <p>' . sanitize($officer['gender'] ?? 'N/A') . '</p>
                </div>
                
                <div class="gp-info-section">
                    <h6><i class="fas fa-id-card"></i> Ghana Card Number</h6>
                    <p>' . sanitize($officer['ghana_card_number'] ?? 'N/A') . '</p>
                </div>
                
                <a href="' . url('/officers/' . $officer['id'] . '/edit') . '" class="btn gp-btn btn-block">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="gp-info-card">
            <div class="gp-card-header">
                <i class="fas fa-address-book"></i> Contact Information
            </div>
            <div class="gp-card-body">
                <div class="gp-contact-item">
                    <i class="fas fa-phone"></i>
                    <span>' . sanitize($officer['contact'] ?? 'N/A') . '</span>
                </div>
                
                <div class="gp-contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>' . sanitize($officer['email'] ?? 'N/A') . '</span>
                </div>
                
                <div class="gp-contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>' . sanitize($officer['address'] ?? 'N/A') . '</span>
                </div>
                
                <div class="gp-info-section mt-3">
                    <h6><i class="fas fa-building"></i> Current Assignment</h6>
                    <p class="mb-2"><strong>Station:</strong> ' . sanitize($officer['station_name'] ?? 'Unassigned') . '</p>
                    <p class="mb-2"><strong>District:</strong> ' . sanitize($officer['district_name'] ?? 'N/A') . '</p>
                    <p class="mb-0"><strong>Region:</strong> ' . sanitize($officer['region_name'] ?? 'N/A') . '</p>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="gp-performance-card">
            <h5 class="text-center mb-4">
                <i class="fas fa-chart-line"></i> Performance Overview
            </h5>
            <div class="row">
                <div class="col-4">
                    <div class="gp-performance-metric">
                        <span class="gp-performance-number">' . ($performance['total_cases'] ?? 0) . '</span>
                        <span class="gp-performance-label">Total Cases</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="gp-performance-metric">
                        <span class="gp-performance-number">' . ($performance['closed_cases'] ?? 0) . '</span>
                        <span class="gp-performance-label">Closed</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="gp-performance-metric">
                        <span class="gp-performance-number">' . ($performance['active_cases'] ?? 0) . '</span>
                        <span class="gp-performance-label">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Posting History -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-postings">
                <div>
                    <i class="fas fa-map-marked-alt"></i> Posting History
                </div>
                <div>
                    <button class="btn gp-btn-light btn-sm" data-toggle="modal" data-target="#transferModal">
                        <i class="fas fa-exchange-alt"></i> Transfer Officer
                    </button>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($postings)) {
    $content .= '
                <div class="gp-timeline">';
    
    foreach ($postings as $posting) {
        $isCurrent = empty($posting['end_date']);
        
        $content .= '
                    <div class="gp-timeline-item">
                        <div class="gp-timeline-dot ' . ($isCurrent ? 'current' : '') . '"></div>
                        <h6 class="mb-2">' . sanitize($posting['station_name']) . '</h6>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-clock"></i> ' . format_date($posting['start_date'], 'd M Y') . 
                            ($posting['end_date'] ? ' - ' . format_date($posting['end_date'], 'd M Y') : ' - Present') . '
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <small><strong>Code:</strong> ' . sanitize($posting['station_code']) . '</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>District:</strong> ' . sanitize($posting['district_name'] ?? 'N/A') . '</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>Region:</strong> ' . sanitize($posting['region_name'] ?? 'N/A') . '</small>
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                </div>';
} else {
    $content .= '<p class="text-muted">No posting history available</p>';
}

$content .= '
            </div>
        </div>

        <!-- Promotion History -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-promotions">
                <div>
                    <i class="fas fa-arrow-up"></i> Promotion History
                </div>
                <div>
                    <button class="btn gp-btn-light btn-sm" data-toggle="modal" data-target="#promoteModal">
                        <i class="fas fa-star"></i> Promote Officer
                    </button>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($promotions)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From Rank</th>
                            <th>To Rank</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($promotions as $promotion) {
        $content .= '
                        <tr>
                            <td>' . format_date($promotion['promotion_date'], 'd M Y') . '</td>
                            <td><span class="gp-badge gp-badge-secondary">' . sanitize($promotion['old_rank']) . '</span></td>
                            <td><span class="gp-badge gp-badge-success">' . sanitize($promotion['new_rank']) . '</span></td>
                            <td>' . sanitize($promotion['notes'] ?? '') . '</td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No promotion history</p>';
}

$content .= '
            </div>
        </div>

        <!-- Recent Case Assignments -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-postings">
                <div>
                    <i class="fas fa-folder"></i> Recent Case Assignments
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($assignments)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($assignments as $assignment) {
        $priorityClass = 'gp-badge-' . strtolower(str_replace(' ', '', $assignment['case_priority'] ?? 'low'));
        
        $content .= '
                        <tr>
                            <td><a href="' . url('/cases/' . $assignment['case_id']) . '" class="text-primary font-weight-bold">' . sanitize($assignment['case_number']) . '</a></td>
                            <td>' . sanitize($assignment['role']) . '</td>
                            <td><span class="gp-badge gp-badge-info">' . sanitize($assignment['case_status']) . '</span></td>
                            <td><span class="gp-badge ' . $priorityClass . '">' . sanitize($assignment['case_priority']) . '</span></td>
                            <td>' . format_date($assignment['assigned_at'], 'd M Y') . '</td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No case assignments</p>';
}

$content .= '
            </div>
        </div>

        <!-- Training Records -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-training">
                <div>
                    <i class="fas fa-graduation-cap"></i> Training Records
                </div>
                <div>
                    <a href="' . url('/officers/training/create?officer_id=' . $officer['id']) . '" class="btn gp-btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add Training
                    </a>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($training)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Training Type</th>
                            <th>Institution</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($training as $record) {
        $statusClass = $record['status'] === 'Completed' ? 'success' : ($record['status'] === 'Ongoing' ? 'warning' : 'info');
        
        $content .= '
                        <tr>
                            <td>' . sanitize($record['training_type']) . '</td>
                            <td>' . sanitize($record['institution'] ?? 'N/A') . '</td>
                            <td>' . format_date($record['start_date'], 'd M Y') . '</td>
                            <td>' . ($record['end_date'] ? format_date($record['end_date'], 'd M Y') : 'Ongoing') . '</td>
                            <td><span class="gp-badge gp-badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No training records</p>';
}

$content .= '
            </div>
        </div>

        <!-- Leave Records -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-leave">
                <div>
                    <i class="fas fa-calendar-alt"></i> Leave Records
                </div>
                <div>
                    <a href="' . url('/officers/leave/create?officer_id=' . $officer['id']) . '" class="btn gp-btn-light btn-sm">
                        <i class="fas fa-plus"></i> Request Leave
                    </a>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($leave)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($leave as $record) {
        $statusClass = $record['status'] === 'Approved' ? 'success' : ($record['status'] === 'Pending' ? 'warning' : ($record['status'] === 'Rejected' ? 'danger' : 'info'));
        $days = $record['start_date'] && $record['end_date'] ? ((strtotime($record['end_date']) - strtotime($record['start_date'])) / 86400 + 1) : 0;
        
        $content .= '
                        <tr>
                            <td>' . sanitize($record['leave_type']) . '</td>
                            <td>' . format_date($record['start_date'], 'd M Y') . '</td>
                            <td>' . ($record['end_date'] ? format_date($record['end_date'], 'd M Y') : 'Ongoing') . '</td>
                            <td>' . $days . '</td>
                            <td><span class="gp-badge gp-badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No leave records</p>';
}

$content .= '
            </div>
        </div>

        <!-- Commendations -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-commendations">
                <div>
                    <i class="fas fa-trophy"></i> Commendations & Awards
                </div>
                <div>
                    <a href="' . url('/officers/commendations/create?officer_id=' . $officer['id']) . '" class="btn gp-btn-light btn-sm">
                        <i class="fas fa-plus"></i> Award Commendation
                    </a>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($commendations)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Commendation</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Awarded By</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($commendations as $commendation) {
        $content .= '
                        <tr>
                            <td>
                                <strong>' . sanitize($commendation['title']) . '</strong>
                                ' . ($commendation['description'] ? '<br><small class="text-muted">' . substr(sanitize($commendation['description']), 0, 80) . '...</small>' : '') . '
                            </td>
                            <td><span class="gp-badge gp-badge-warning">' . sanitize($commendation['commendation_type']) . '</span></td>
                            <td>' . format_date($commendation['award_date'], 'd M Y') . '</td>
                            <td>' . sanitize($commendation['awarded_by_name'] ?? 'N/A') . '</td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No commendations</p>';
}

$content .= '
            </div>
        </div>

        <!-- Disciplinary Records -->
        <div class="gp-section-card">
            <div class="gp-section-header gp-section-disciplinary">
                <div>
                    <i class="fas fa-exclamation-triangle"></i> Disciplinary Records
                </div>
                <div>
                    <a href="' . url('/officers/disciplinary/create?officer_id=' . $officer['id']) . '" class="btn gp-btn-light btn-sm">
                        <i class="fas fa-plus"></i> Record Action
                    </a>
                </div>
            </div>
            <div class="gp-section-body">';

if (!empty($disciplinary)) {
    $content .= '
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Offence</th>
                            <th>Action</th>
                            <th>Incident Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($disciplinary as $record) {
        $statusClass = $record['status'] === 'Active' ? 'danger' : ($record['status'] === 'Resolved' ? 'success' : 'warning');
        
        $content .= '
                        <tr>
                            <td>
                                <strong>' . sanitize($record['offence_type']) . '</strong>
                                ' . ($record['offence_description'] ? '<br><small class="text-muted">' . substr(sanitize($record['offence_description']), 0, 60) . '...</small>' : '') . '
                            </td>
                            <td><span class="gp-badge gp-badge-danger">' . sanitize($record['disciplinary_action']) . '</span></td>
                            <td>' . format_date($record['incident_date'], 'd M Y') . '</td>
                            <td><span class="gp-badge gp-badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No disciplinary records</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gp-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Transfer Officer
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="transferForm">
                    ' . csrf_field() . '
                    <div class="form-group">
                        <label class="form-label">New Station</label>
                        <select class="form-control" name="station_id" required>
                            <option value="">Select Station</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Effective Date</label>
                        <input type="date" class="form-control" name="effective_date" value="' . date('Y-m-d') . '" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn gp-btn" onclick="submitTransfer()">Transfer</button>
            </div>
        </div>
    </div>
</div>

<!-- Promote Modal -->
<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gp-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-star"></i> Promote Officer
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="promoteForm">
                    ' . csrf_field() . '
                    <div class="form-group">
                        <label class="form-label">New Rank</label>
                        <select class="form-control" name="new_rank" required>
                            <option value="">Select Rank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Effective Date</label>
                        <input type="date" class="form-control" name="effective_date" value="' . date('Y-m-d') . '" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn gp-btn-success" onclick="submitPromotion()">Promote</button>
            </div>
        </div>
    </div>
</div>';

$scripts = '
<script>
function submitTransfer() {
    const formData = new FormData(document.getElementById("transferForm"));
    fetch("' . url('/officers/' . $officer['id'] . '/transfer') . '", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              Swal.fire({
                  icon: "success",
                  title: "Transfer Successful",
                  text: "Officer has been transferred successfully.",
                  timer: 2000,
                  showConfirmButton: false
              }).then(() => {
                  location.reload();
              });
          } else {
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: data.message
              });
          }
      }).catch(error => {
          Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while transferring the officer."
          });
      });
}

function submitPromotion() {
    const formData = new FormData(document.getElementById("promoteForm"));
    fetch("' . url('/officers/' . $officer['id'] . '/promote') . '", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              Swal.fire({
                  icon: "success",
                  title: "Promotion Successful",
                  text: "Officer has been promoted successfully.",
                  timer: 2000,
                  showConfirmButton: false
              }).then(() => {
                  location.reload();
              });
          } else {
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: data.message
              });
          }
      }).catch(error => {
          Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while promoting the officer."
          });
      });
}
</script>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => $officer['service_number']]
];

include __DIR__ . '/../layouts/main.php';
?>