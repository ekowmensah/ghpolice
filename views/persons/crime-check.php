<?php
// Initialize result variable
$result = $result ?? null;
$user = $_SESSION['user'] ?? [];
$accessLevel = $user['access_level'] ?? 'Station';

$content = '
<style>
.threat-level-critical { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
.threat-level-high { background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); color: white; }
.threat-level-medium { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #000; }
.threat-level-low { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; }
.threat-level-none { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
.biometric-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 5px; }
.biometric-captured { background: #28a745; }
.biometric-missing { background: #dc3545; }
.timeline-item { position: relative; padding-left: 30px; margin-bottom: 20px; }
.timeline-item:before { content: ""; position: absolute; left: 0; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #007bff; }
.risk-badge { font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; }
.stat-card { border-left: 4px solid; }
.search-advanced { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.notification-bar { 
    position: fixed; 
    top: 60px; 
    left: 0; 
    right: 0; 
    z-index: 9999; 
    animation: slideDown 0.5s ease-out;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

';

// Add notification bar if search was performed
if ($result !== null) {
    if ($result['found']) {
        $personData = $result['person'];
        $fullName = trim(($personData['first_name'] ?? '') . ' ' . ($personData['middle_name'] ?? '') . ' ' . ($personData['last_name'] ?? ''));
        $activeAlertsCount = count($result['alerts'] ?? []);
        
        if ($personData['is_wanted'] || $personData['has_criminal_record'] || $activeAlertsCount > 0) {
            $content .= '
<div class="notification-bar">
    <div class="alert alert-danger mb-0" style="border-radius: 0; border-left: 5px solid #721c24; margin: 0;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <div class="col-md-9">
                    <h4 class="mb-1"><strong>⚠️ SUBJECT FOUND IN CRIMINAL DATABASE</strong></h4>
                    <p class="mb-0">
                        <strong>Name:</strong> ' . strtoupper(sanitize($fullName)) . ' | 
                        <strong>Status:</strong> ' . ($personData['is_wanted'] ? '<span class="badge badge-light">WANTED</span>' : '') . ' 
                        ' . ($personData['has_criminal_record'] ? '<span class="badge badge-light">CRIMINAL RECORD</span>' : '') . '
                        ' . ($activeAlertsCount > 0 ? '<span class="badge badge-light">' . $activeAlertsCount . ' ACTIVE ALERT' . ($activeAlertsCount > 1 ? 'S' : '') . '</span>' : '') . '
                    </p>
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" class="close text-white" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>';
        } else {
            $content .= '
<div class="notification-bar">
    <div class="alert alert-success mb-0" style="border-radius: 0; border-left: 5px solid #155724; margin: 0;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <div class="col-md-9">
                    <h4 class="mb-1"><strong>✓ SUBJECT FOUND - NO ACTIVE ALERTS</strong></h4>
                    <p class="mb-0">
                        <strong>Name:</strong> ' . strtoupper(sanitize($fullName)) . ' | 
                        <strong>Status:</strong> <span class="badge badge-light">CLEAR - NO CRIMINAL RECORD</span>
                    </p>
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" class="close" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>';
        }
    } else {
        $content .= '
<div class="notification-bar">
    <div class="alert alert-info mb-0" style="border-radius: 0; border-left: 5px solid #0c5460; margin: 0;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <i class="fas fa-user-slash fa-3x"></i>
                </div>
                <div class="col-md-7">
                    <h4 class="mb-1"><strong>ℹ️ NO RECORDS FOUND - PERSON NOT REGISTERED</strong></h4>
                    <p class="mb-0">
                        Subject is not in the national database. Click "Register Person" to add them to the system.
                    </p>
                </div>
                <div class="col-md-3 text-right">
                    <a href="' . url('/persons/create') . '" class="btn btn-light btn-sm mr-2">
                        <i class="fas fa-user-plus"></i> Register Person
                    </a>
                </div>
                <div class="col-md-1 text-right">
                    <button type="button" class="close" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>';
    }
}

$content .= '
<div class="row">
    <div class="col-md-12">
        <!-- Header Section -->
        <div class="card card-dark">
            <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i> 
                    <strong>NATIONAL CRIMINAL DATABASE QUERY SYSTEM</strong>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">
                        <i class="fas fa-user-shield"></i> ' . htmlspecialchars($user['rank'] ?? 'Officer') . ' ' . htmlspecialchars($user['first_name'] ?? '') . '
                    </span>
                </div>
            </div>
            <div class="card-body">';

// Only show search button if no results yet
if ($result === null) {
    $content .= '
                <div class="text-center py-5">
                    <i class="fas fa-search fa-5x text-muted mb-4"></i>
                    <h3 class="mb-3">National Criminal Database Query</h3>
                    <p class="text-muted mb-4">Click the button below to search for a subject in the criminal records database</p>
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#searchModal" style="padding: 15px 50px;">
                        <i class="fas fa-search"></i> SEARCH PERSON
                    </button>
                </div>';
} else {
    // Show new search button when results are displayed
    $content .= '
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchModal">
                        <i class="fas fa-search"></i> New Search
                    </button>
                </div>
                <hr class="my-4">';
}

// Crime Check Results
if ($result !== null) {
    if ($result['found']) {
        $personData = $result['person'];
        $fullName = trim(($personData['first_name'] ?? '') . ' ' . ($personData['middle_name'] ?? '') . ' ' . ($personData['last_name'] ?? ''));
        
        // Determine Threat Level
        $threatLevel = 'NONE';
        $threatClass = 'threat-level-none';
        if ($personData['is_wanted']) {
            $threatLevel = 'CRITICAL';
            $threatClass = 'threat-level-critical';
        } elseif (!empty($result['current_cases'])) {
            $threatLevel = 'HIGH';
            $threatClass = 'threat-level-high';
        } elseif ($personData['has_criminal_record']) {
            $threatLevel = 'MEDIUM';
            $threatClass = 'threat-level-medium';
        } elseif (!empty($result['criminal_history'])) {
            $threatLevel = 'LOW';
            $threatClass = 'threat-level-low';
        }
        
        // Critical Alert Banner
        if ($personData['is_wanted'] || $personData['has_criminal_record']) {
            $content .= '
                <div class="alert alert-danger" style="border-left: 5px solid #dc3545;">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-2">
                                <i class="fas fa-exclamation-triangle fa-2x" style="vertical-align: middle;"></i>
                                <strong>SUBJECT IDENTIFIED IN CRIMINAL DATABASE</strong>
                            </h3>';
            
            if ($personData['is_wanted']) {
                $content .= '
                            <div class="alert alert-danger mb-2" style="background: #721c24; color: white; border: 2px solid #f5c6cb;">
                                <h4 class="mb-0">
                                    <i class="fas fa-bullhorn"></i> 
                                    <strong>⚠️ ACTIVE WARRANT - SUBJECT IS WANTED</strong>
                                </h4>
                                <p class="mb-0 mt-2">CAUTION: Approach with extreme care. Follow departmental protocols for wanted subjects.</p>
                            </div>';
            }
            
            if ($personData['has_criminal_record']) {
                $content .= '
                            <div class="alert alert-warning mb-0">
                                <strong><i class="fas fa-file-alt"></i> CRIMINAL HISTORY ON FILE</strong> - Subject has prior convictions or arrests.
                            </div>';
            }
            
            $content .= '
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="' . $threatClass . '" style="padding: 20px; border-radius: 10px;">
                                <h5 class="mb-1">THREAT ASSESSMENT</h5>
                                <h2 class="mb-0"><strong>' . $threatLevel . '</strong></h2>
                                <small>Risk Level: ' . sanitize($personData['risk_level'] ?? 'Unknown') . '</small>
                            </div>
                        </div>
                    </div>
                </div>';
        } else {
            $content .= '
                <div class="alert alert-success" style="border-left: 5px solid #28a745;">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><i class="fas fa-check-circle"></i> <strong>SUBJECT LOCATED - NO ACTIVE ALERTS</strong></h4>
                            <p class="mb-0">Subject identified in database with no criminal record or outstanding warrants.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="' . $threatClass . '" style="padding: 20px; border-radius: 10px;">
                                <h5 class="mb-1">THREAT ASSESSMENT</h5>
                                <h2 class="mb-0"><strong>CLEAR</strong></h2>
                            </div>
                        </div>
                    </div>
                </div>';
        }

        // Subject Profile
        $age = $personData['age'] ?? ($personData['date_of_birth'] ? date_diff(date_create($personData['date_of_birth']), date_create('today'))->y : 'Unknown');
        
        $content .= '
                <div class="row mt-4">
                    <!-- Subject Profile -->
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                                <h3 class="card-title"><i class="fas fa-user-circle"></i> <strong>SUBJECT PROFILE</strong></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="150" class="text-muted">FULL NAME:</th>
                                                <td><strong style="font-size: 1.1rem;">' . strtoupper(sanitize($fullName)) . '</strong></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">GENDER:</th>
                                                <td>' . sanitize($personData['gender'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">DATE OF BIRTH:</th>
                                                <td>' . ($personData['date_of_birth'] ? format_date($personData['date_of_birth'], 'd M Y') : 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">AGE:</th>
                                                <td>' . $age . ' years</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="150" class="text-muted">GHANA CARD:</th>
                                                <td><code>' . sanitize($personData['ghana_card_number'] ?? 'N/A') . '</code></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">CONTACT:</th>
                                                <td>' . sanitize($personData['contact'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">EMAIL:</th>
                                                <td>' . sanitize($personData['email'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">ADDRESS:</th>
                                                <td>' . sanitize($personData['address'] ?? 'N/A') . '</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6 class="text-muted mb-2"><i class="fas fa-fingerprint"></i> BIOMETRIC STATUS</h6>
                                        <div>
                                            <span class="biometric-indicator ' . ($personData['fingerprint_captured'] ? 'biometric-captured' : 'biometric-missing') . '"></span>
                                            <strong>Fingerprints:</strong> ' . ($personData['fingerprint_captured'] ? '<span class="text-success">CAPTURED</span>' : '<span class="text-danger">NOT ON FILE</span>') . '
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <span class="biometric-indicator ' . ($personData['face_captured'] ? 'biometric-captured' : 'biometric-missing') . '"></span>
                                            <strong>Facial Recognition:</strong> ' . ($personData['face_captured'] ? '<span class="text-success">CAPTURED</span>' : '<span class="text-danger">NOT ON FILE</span>') . '
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <span class="biometric-indicator ' . ($personData['photo_path'] ? 'biometric-captured' : 'biometric-missing') . '"></span>
                                            <strong>Photo:</strong> ' . ($personData['photo_path'] ? '<span class="text-success">ON FILE</span>' : '<span class="text-danger">NOT AVAILABLE</span>') . '
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Summary -->
                    <div class="col-md-4">
                        <div class="card stat-card" style="border-left-color: #dc3545;">
                            <div class="card-body">
                                <h3 class="mb-0">' . count($result['alerts'] ?? []) . '</h3>
                                <p class="text-muted mb-0"><i class="fas fa-bell"></i> Active Alerts</p>
                            </div>
                        </div>
                        <div class="card stat-card mt-2" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <h3 class="mb-0">' . count($result['criminal_history'] ?? []) . '</h3>
                                <p class="text-muted mb-0"><i class="fas fa-history"></i> Criminal Records</p>
                            </div>
                        </div>
                        <div class="card stat-card mt-2" style="border-left-color: #17a2b8;">
                            <div class="card-body">
                                <h3 class="mb-0">' . count($result['current_cases'] ?? []) . '</h3>
                                <p class="text-muted mb-0"><i class="fas fa-folder-open"></i> Active Cases</p>
                            </div>
                        </div>
                        <div class="card stat-card mt-2" style="border-left-color: ' . ($personData['is_wanted'] ? '#dc3545' : '#28a745') . ';">
                            <div class="card-body text-center">
                                <h5 class="mb-1">WARRANT STATUS</h5>
                                <h4 class="mb-0"><strong>' . ($personData['is_wanted'] ? '<span class="text-danger">ACTIVE</span>' : '<span class="text-success">CLEAR</span>') . '</strong></h4>
                            </div>
                        </div>
                    </div>
                </div>';

        // Active Alerts
        if (!empty($result['alerts'])) {
            $content .= '
                <div class="card card-danger mt-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <h3 class="card-title"><i class="fas fa-bell"></i> <strong>ACTIVE ALERTS & WARNINGS</strong></h3>
                        <div class="card-tools">
                            <span class="badge badge-light">' . count($result['alerts']) . ' Alert(s)</span>
                        </div>
                    </div>
                    <div class="card-body">';

            foreach ($result['alerts'] as $alert) {
                $priorityClass = match($alert['alert_priority']) {
                    'Critical' => 'danger',
                    'High' => 'warning',
                    'Medium' => 'info',
                    default => 'secondary'
                };
                
                $priorityIcon = match($alert['alert_priority']) {
                    'Critical' => 'fa-exclamation-circle',
                    'High' => 'fa-exclamation-triangle',
                    'Medium' => 'fa-info-circle',
                    default => 'fa-bell'
                };

                $content .= '
                        <div class="alert alert-' . $priorityClass . '" style="border-left: 5px solid;">
                            <div class="row">
                                <div class="col-md-9">
                                    <h5><i class="fas ' . $priorityIcon . '"></i> <strong>' . strtoupper(sanitize($alert['alert_type'])) . '</strong></h5>
                                    <p class="mb-2">' . sanitize($alert['alert_message']) . '</p>
                                    ' . ($alert['alert_details'] ? '<p class="mb-0"><small><strong>Details:</strong> ' . sanitize($alert['alert_details']) . '</small></p>' : '') . '
                                </div>
                                <div class="col-md-3 text-right">
                                    <span class="badge badge-' . $priorityClass . '" style="font-size: 1rem; padding: 0.5rem 1rem;">' . strtoupper(sanitize($alert['alert_priority'])) . '</span>
                                    <br><small class="text-muted mt-2 d-block">Issued: ' . format_date($alert['issued_date'], 'd M Y') . '</small>
                                </div>
                            </div>
                        </div>';
            }

            $content .= '
                    </div>
                </div>';
        }

        // Criminal History
        if (!empty($result['criminal_history'])) {
            $content .= '
                <div class="card card-warning mt-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                        <h3 class="card-title"><i class="fas fa-history"></i> <strong>CRIMINAL HISTORY RECORD</strong></h3>
                        <div class="card-tools">
                            <span class="badge badge-dark">' . count($result['criminal_history']) . ' Record(s)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="100">DATE</th>
                                        <th width="150">CASE NUMBER</th>
                                        <th width="120">INVOLVEMENT</th>
                                        <th>OFFENCE CATEGORY</th>
                                        <th width="120">CASE STATUS</th>
                                        <th width="120">OUTCOME</th>
                                        <th width="80">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>';

            foreach ($result['criminal_history'] as $record) {
                $involvementClass = match($record['involvement_type']) {
                    'Convicted' => 'badge-danger',
                    'Charged' => 'badge-warning',
                    'Arrested' => 'badge-info',
                    default => 'badge-secondary'
                };
                
                $statusClass = match($record['case_status']) {
                    'Closed' => 'badge-secondary',
                    'Open' => 'badge-primary',
                    'Under Investigation' => 'badge-info',
                    default => 'badge-secondary'
                };

                $content .= '
                                <tr>
                                    <td><strong>' . format_date($record['case_date'], 'd M Y') . '</strong></td>
                                    <td><code>' . sanitize($record['case_number']) . '</code></td>
                                    <td><span class="badge ' . $involvementClass . '" style="font-size: 0.85rem;">' . strtoupper(sanitize($record['involvement_type'])) . '</span></td>
                                    <td>' . sanitize($record['offence_category'] ?? 'N/A') . '</td>
                                    <td><span class="badge ' . $statusClass . '">' . sanitize($record['case_status']) . '</span></td>
                                    <td>' . sanitize($record['outcome'] ?? '<span class="text-muted">Pending</span>') . '</td>
                                    <td><a href="' . url('/cases/' . $record['case_id']) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>';
            }

            $content .= '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>';
        }

        // Current Cases (as suspect)
        if (!empty($result['current_cases'])) {
            $content .= '
                <div class="card card-danger mt-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <h3 class="card-title"><i class="fas fa-folder-open"></i> <strong>ACTIVE CASES - SUBJECT AS SUSPECT</strong></h3>
                        <div class="card-tools">
                            <span class="badge badge-light">' . count($result['current_cases']) . ' Case(s)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="150">CASE NUMBER</th>
                                        <th>DESCRIPTION</th>
                                        <th width="120">CASE STATUS</th>
                                        <th width="100">PRIORITY</th>
                                        <th width="120">SUSPECT STATUS</th>
                                        <th width="100">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>';

            foreach ($result['current_cases'] as $case) {
                $priorityClass = match($case['case_priority']) {
                    'Critical' => 'badge-danger',
                    'High' => 'badge-danger',
                    'Medium' => 'badge-warning',
                    default => 'badge-info'
                };
                
                $statusClass = match($case['current_status']) {
                    'Arrested' => 'badge-danger',
                    'Charged' => 'badge-warning',
                    'Suspect' => 'badge-info',
                    default => 'badge-secondary'
                };

                $content .= '
                                <tr>
                                    <td><strong><code>' . sanitize($case['case_number']) . '</code></strong></td>
                                    <td>' . sanitize(substr($case['description'], 0, 80)) . '...</td>
                                    <td><span class="badge badge-primary">' . sanitize($case['case_status']) . '</span></td>
                                    <td><span class="badge ' . $priorityClass . '" style="font-size: 0.85rem;">' . strtoupper(sanitize($case['case_priority'])) . '</span></td>
                                    <td><span class="badge ' . $statusClass . '" style="font-size: 0.85rem;">' . strtoupper(sanitize($case['current_status'])) . '</span></td>
                                    <td><a href="' . url('/cases/' . $case['case_id']) . '" class="btn btn-sm btn-primary"><i class="fas fa-folder-open"></i> View</a></td>
                                </tr>';
            }

            $content .= '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>';
        }

    } else {
        $content .= '
                <div class="alert alert-info" style="border-left: 5px solid #17a2b8; padding: 30px;">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <i class="fas fa-user-slash" style="font-size: 4rem; color: #17a2b8;"></i>
                        </div>
                        <div class="col-md-10">
                            <h3><strong>NO RECORDS FOUND - PERSON NOT REGISTERED</strong></h3>
                            <p class="mb-2">The search parameters provided did not match any records in the national criminal database.</p>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Search Status:</strong> <span class="badge badge-info">NOT IN SYSTEM</span><br>
                                        <strong>Database Status:</strong> Subject has not been registered in the national database.<br>
                                        <strong>Recommendation:</strong> Register person if required for case investigation or documentation.
                                    </p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="card" style="background: #f8f9fa; border: 2px dashed #17a2b8;">
                                        <div class="card-body text-center">
                                            <h5 class="mb-3"><i class="fas fa-user-plus"></i> Person Not in Database</h5>
                                            <p class="text-muted mb-3">Click below to register this person into the national database</p>
                                            <a href="' . url('/persons/create') . '" class="btn btn-primary btn-lg btn-block">
                                                <i class="fas fa-user-plus"></i> REGISTER PERSON
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
    }
}

$content .= '
            </div>
            <div class="card-footer" style="background: #f8f9fa;">
                <div class="row">
                    <div class="col-md-6">
                        <a href="' . url('/persons') . '" class="btn btn-secondary">
                            <i class="fas fa-users"></i> Browse All Persons
                        </a>
                        <a href="' . url('/persons/create') . '" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register New Person
                        </a>
                    </div>
                    <div class="col-md-6 text-right">';

if ($result !== null && $result['found']) {
    $content .= '
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button onclick="exportToPDF()" class="btn btn-warning">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <a href="' . url('/persons/' . $result['person']['id']) . '" class="btn btn-success">
                            <i class="fas fa-user"></i> View Full Profile
                        </a>';
}

$content .= '
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportToPDF() {
    alert("PDF export functionality - integrate with server-side PDF generator");
}
</script>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h4 class="modal-title" id="searchModalLabel">
                    <i class="fas fa-search"></i> Subject Identification Search
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Enter any unique identifier to query national criminal records database. System automatically detects identifier type.</p>
                
                <form method="POST" action="' . url('/persons/crime-check') . '" id="crimeCheckForm">
                    ' . csrf_field() . '
                    
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-fingerprint"></i> SUBJECT IDENTIFIER
                        </label>
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control form-control-lg" name="identifier" 
                                   placeholder="Enter Ghana Card, Phone Number, Passport, or Driver\'s License..." 
                                   required
                                   style="font-size: 1.1rem; height: 60px;">
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Accepted Formats:</strong><br>
                            <span class="badge badge-secondary mr-1"><i class="fas fa-id-card"></i> Ghana Card</span> GHA-XXXXXXXXX-X
                            &nbsp;|&nbsp;
                            <span class="badge badge-secondary mr-1"><i class="fas fa-phone"></i> Phone</span> 0XX XXX XXXX
                            &nbsp;|&nbsp;
                            <span class="badge badge-secondary mr-1"><i class="fas fa-passport"></i> Passport</span> Any format
                            &nbsp;|&nbsp;
                            <span class="badge badge-secondary mr-1"><i class="fas fa-car"></i> License</span> Any format
                        </small>
                    </div>
                    
                    <div class="alert alert-info" style="border-left: 4px solid #17a2b8;">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <i class="fas fa-shield-alt" style="font-size: 2rem; color: #17a2b8;"></i>
                            </div>
                            <div class="col-md-10">
                                <strong><i class="fas fa-lock"></i> SEARCH PROTOCOL:</strong><br>
                                System will automatically detect identifier type and cross-reference all national databases.
                                Results are classified and access-controlled based on user clearance level.
                                All queries are logged for audit purposes.
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 40px;">
                            <i class="fas fa-search"></i> EXECUTE SEARCH
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => 'Crime Check']
];

include __DIR__ . '/../layouts/main.php';
?>
