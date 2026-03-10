<?php
$title = 'Officer Profile - ' . sanitize($officer['service_number']);

$content = '
<style>
.ghana-police-header {
    background: linear-gradient(135deg, #112c4d 0%, #1a406d 50%, #c7a13f 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.ghana-police-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.05)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 20px 20px;
}

.officer-profile-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 2px solid #112c4d;
    margin-bottom: 1.5rem;
}

.officer-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #c7a13f;
    background: linear-gradient(135deg, #112c4d, #1a406d);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.rank-badge {
    background: linear-gradient(135deg, #c7a13f, #d4af37);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 0.875rem;
    display: inline-block;
    margin-bottom: 1rem;
}

.service-number {
    background: #112c4d;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 1.1rem;
    display: inline-block;
    margin-bottom: 0.5rem;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-active { background: #28a745; color: white; }
.status-onleave { background: #ffc107; color: #212529; }
.status-retired { background: #6c757d; color: white; }
.status-suspended { background: #dc3545; color: white; }

.info-section {
    background: #f8f9fa;
    border-left: 4px solid #112c4d;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.info-section h6 {
    color: #112c4d;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.info-section p {
    margin-bottom: 0;
    color: #495057;
}

.performance-card {
    background: linear-gradient(135deg, #112c4d, #1a406d);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.performance-metric {
    text-align: center;
    padding: 1rem;
}

.performance-number {
    font-size: 2.5rem;
    font-weight: bold;
    display: block;
    margin-bottom: 0.5rem;
}

.performance-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.timeline-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.timeline-card .card-header {
    background: linear-gradient(135deg, #112c4d, #1a406d);
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
    padding: 1rem 1.5rem;
}

.timeline-item {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 1.5rem;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0.5rem;
    bottom: -1.5rem;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child::before {
    bottom: 0.5rem;
}

.timeline-dot {
    position: absolute;
    left: -6px;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #112c4d;
    border: 2px solid white;
}

.timeline-dot.current {
    background: #c7a13f;
    box-shadow: 0 0 0 4px rgba(199, 161, 63, 0.2);
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 3px solid #112c4d;
}

.timeline-content.current {
    background: #fff3cd;
    border-left-color: #c7a13f;
}

.promotion-table {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.promotion-table .card-header {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
    padding: 1rem 1.5rem;
}

.rank-badge-old {
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
}

.rank-badge-new {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
}

.cases-table {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.cases-table .card-header {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
    padding: 1rem 1.5rem;
}

.priority-high { background: #dc3545; color: white; }
.priority-medium { background: #ffc107; color: #212529; }
.priority-low { background: #17a2b8; color: white; }

.btn-ghana-police {
    background: linear-gradient(135deg, #112c4d, #1a406d);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-ghana-police:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(17, 44, 77, 0.3);
}

.btn-ghana-police-success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.btn-ghana-police-success:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #112c4d, #1a406d);
    color: white;
    border: none;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.enlistment-date {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    display: inline-block;
    margin-top: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.contact-item i {
    width: 30px;
    height: 30px;
    background: #112c4d;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 0.875rem;
}

.contact-item span {
    color: #495057;
    font-weight: 500;
}
</style>

<!-- Ghana Police Header -->
<div class="ghana-police-header text-center">
    <div class="position-relative">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="officer-avatar">
                    ' . substr(sanitize($officer['first_name'] . ' ' . $officer['last_name']), 0, 1) . '
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="mb-2">' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</h1>
                <div class="rank-badge">' . sanitize($officer['rank_name'] ?? 'Officer') . '</div>
                <div class="service-number">' . sanitize($officer['service_number']) . '</div>
                <div class="enlistment-date">
                    <i class="fas fa-calendar"></i> Enlisted: ' . ($officer['date_of_enlistment'] ? format_date($officer['date_of_enlistment'], 'd M Y') : 'N/A') . '
                </div>
            </div>
            <div class="col-md-3">
                <div class="status-badge status-' . strtolower(str_replace(' ', '-', $officer['employment_status'] ?? 'active')) . '">
                    ' . sanitize($officer['employment_status'] ?? 'Active') . '
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Officer Details Card -->
        <div class="officer-profile-card">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="fas fa-user-shield"></i> Service Details
                </h5>
                
                <div class="info-section">
                    <h6><i class="fas fa-id-card"></i> Badge Number</h6>
                    <p>' . sanitize($officer['badge_number'] ?? 'N/A') . '</p>
                </div>
                
                <div class="info-section">
                    <h6><i class="fas fa-birthday-cake"></i> Date of Birth</h6>
                    <p>' . ($officer['date_of_birth'] ? format_date($officer['date_of_birth'], 'd M Y') : 'N/A') . '</p>
                </div>
                
                <div class="info-section">
                    <h6><i class="fas fa-venus-mars"></i> Gender</h6>
                    <p>' . sanitize($officer['gender'] ?? 'N/A') . '</p>
                </div>
                
                <div class="info-section">
                    <h6><i class="fas fa-id-card"></i> Ghana Card Number</h6>
                    <p>' . sanitize($officer['ghana_card_number'] ?? 'N/A') . '</p>
                </div>
                
                <a href="' . url('/officers/' . $officer['id'] . '/edit') . '" class="btn btn-ghana-police btn-block">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="officer-profile-card">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="fas fa-address-book"></i> Contact Information
                </h5>
                
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>' . sanitize($officer['contact'] ?? 'N/A') . '</span>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>' . sanitize($officer['email'] ?? 'N/A') . '</span>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>' . sanitize($officer['address'] ?? 'N/A') . '</span>
                </div>
                
                <div class="info-section mt-3">
                    <h6><i class="fas fa-building"></i> Current Assignment</h6>
                    <p class="mb-1"><strong>Station:</strong> ' . sanitize($officer['station_name'] ?? 'Unassigned') . '</p>
                    <p class="mb-1"><strong>District:</strong> ' . sanitize($officer['district_name'] ?? 'N/A') . '</p>
                    <p class="mb-0"><strong>Region:</strong> ' . sanitize($officer['region_name'] ?? 'N/A') . '</p>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="performance-card">
            <h5 class="card-title mb-4">
                <i class="fas fa-chart-line"></i> Performance Overview
            </h5>
            <div class="row">
                <div class="col-4">
                    <div class="performance-metric">
                        <span class="performance-number">' . ($performance['total_cases'] ?? 0) . '</span>
                        <span class="performance-label">Total Cases</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="performance-metric">
                        <span class="performance-number">' . ($performance['closed_cases'] ?? 0) . '</span>
                        <span class="performance-label">Closed</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="performance-metric">
                        <span class="performance-number">' . ($performance['active_cases'] ?? 0) . '</span>
                        <span class="performance-label">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Posting History -->
        <div class="timeline-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marked-alt"></i> Posting History
                </h5>
                <div class="card-tools">
                    <button class="btn btn-sm btn-ghana-police" data-toggle="modal" data-target="#transferModal">
                        <i class="fas fa-exchange-alt"></i> Transfer Officer
                    </button>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($postings)) {
    $content .= '
                <div class="timeline">';
    
    foreach ($postings as $posting) {
        $isCurrent = empty($posting['end_date']);
        $dotClass = $isCurrent ? 'current' : '';
        
        $content .= '
                    <div class="timeline-item">
                        <div class="timeline-dot ' . $dotClass . '"></div>
                        <div class="timeline-content ' . ($isCurrent ? 'current' : '') . '">
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
        <div class="promotion-table">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-arrow-up"></i> Promotion History
                </h5>
                <div class="card-tools">
                    <button class="btn btn-sm btn-ghana-police-success" data-toggle="modal" data-target="#promoteModal">
                        <i class="fas fa-star"></i> Promote Officer
                    </button>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($promotions)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td><span class="rank-badge-old">' . sanitize($promotion['old_rank']) . '</span></td>
                                <td><span class="rank-badge-new">' . sanitize($promotion['new_rank']) . '</span></td>
                                <td>' . sanitize($promotion['notes'] ?? '') . '</td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '<p class="text-muted">No promotion history</p>';
}

$content .= '
            </div>
        </div>

        <!-- Recent Case Assignments -->
        <div class="cases-table">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-folder"></i> Recent Case Assignments
                </h5>
            </div>
            <div class="card-body p-4">';

if (!empty($assignments)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
        $priorityClass = 'priority-' . strtolower(str_replace(' ', '', $assignment['case_priority'] ?? 'low'));
        
        $content .= '
                            <tr>
                                <td><a href="' . url('/cases/' . $assignment['case_id']) . '" class="text-primary font-weight-bold">' . sanitize($assignment['case_number']) . '</a></td>
                                <td>' . sanitize($assignment['role']) . '</td>
                                <td><span class="badge badge-info">' . sanitize($assignment['case_status']) . '</span></td>
                                <td><span class="badge ' . $priorityClass . '">' . sanitize($assignment['case_priority']) . '</span></td>
                                <td>' . format_date($assignment['assigned_at'], 'd M Y') . '</td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '<p class="text-muted">No case assignments</p>';
}

$content .= '
            </div>
        </div>

        <!-- Training Records -->
        <div class="training-table" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #e9ecef; margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #6f42c1, #9561e2); color: white; border: none; border-radius: 15px 15px 0 0; padding: 1rem 1.5rem;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-graduation-cap"></i> Training Records
                </h5>
                <div class="card-tools">
                    <a href="' . url('/officers/training/create?officer_id=' . $officer['id']) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Add Training
                    </a>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($training)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td><span class="badge badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '<p class="text-muted">No training records</p>';
}

$content .= '
            </div>
        </div>

        <!-- Leave Records -->
        <div class="leave-table" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #e9ecef; margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #17a2b8, #20c997); color: white; border: none; border-radius: 15px 15px 0 0; padding: 1rem 1.5rem;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt"></i> Leave Records
                </h5>
                <div class="card-tools">
                    <a href="' . url('/officers/leave/create?officer_id=' . $officer['id']) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Request Leave
                    </a>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($leave)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td><span class="badge badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '<p class="text-muted">No leave records</p>';
}

$content .= '
            </div>
        </div>

        <!-- Commendations -->
        <div class="commendations-table" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #e9ecef; margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; border: none; border-radius: 15px 15px 0 0; padding: 1rem 1.5rem;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy"></i> Commendations & Awards
                </h5>
                <div class="card-tools">
                    <a href="' . url('/officers/commendations/create?officer_id=' . $officer['id']) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Award Commendation
                    </a>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($commendations)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td><span class="badge badge-warning">' . sanitize($commendation['commendation_type']) . '</span></td>
                                <td>' . format_date($commendation['award_date'], 'd M Y') . '</td>
                                <td>' . sanitize($commendation['awarded_by_name'] ?? 'N/A') . '</td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '<p class="text-muted">No commendations</p>';
}

$content .= '
            </div>
        </div>

        <!-- Disciplinary Records -->
        <div class="disciplinary-table" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #e9ecef; margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; border: none; border-radius: 15px 15px 0 0; padding: 1rem 1.5rem;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Disciplinary Records
                </h5>
                <div class="card-tools">
                    <a href="' . url('/officers/disciplinary/create?officer_id=' . $officer['id']) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Record Action
                    </a>
                </div>
            </div>
            <div class="card-body p-4">';

if (!empty($disciplinary)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td><span class="badge badge-danger">' . sanitize($record['disciplinary_action']) . '</span></td>
                                <td>' . format_date($record['incident_date'], 'd M Y') . '</td>
                                <td><span class="badge badge-' . $statusClass . '">' . sanitize($record['status']) . '</span></td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
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
            <div class="modal-header">
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
                <button type="button" class="btn btn-ghana-police" onclick="submitTransfer()">Transfer</button>
            </div>
        </div>
    </div>
</div>

<!-- Promote Modal -->
<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
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
                <button type="button" class="btn btn-ghana-police-success" onclick="submitPromotion()">Promote</button>
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
