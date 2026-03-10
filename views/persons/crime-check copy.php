<?php
// Initialize result variable
$result = $result ?? null;

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Crime Check System</h3>
            </div>
            <div class="card-body">
                <h4>Search for Person</h4>
                <p class="text-muted">Enter any unique identifier (Ghana Card Number, Phone Number, Passport Number, or Driver\'s License) to perform an instant crime check</p>
                
                <form method="POST" action="' . url('/persons/crime-check') . '">
                    ' . csrf_field() . '
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="identifier">Unique Identifier</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="identifier" name="identifier" 
                                           placeholder="Enter Ghana Card, Phone, Passport, or Driver\'s License..." required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-search"></i> Perform Crime Check
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Accepted formats: Ghana Card (GHA-XXXXXXXXX-X), Phone (0XX XXX XXXX), Passport Number, Driver\'s License
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Quick Actions</label>
                                <div class="btn-group-vertical d-block" style="width: 100%;">
                                    <a href="' . url('/persons') . '" class="btn btn-outline-secondary mb-2">
                                        <i class="fas fa-users"></i> Browse All Persons
                                    </a>
                                    <a href="' . url('/persons/create') . '" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus"></i> Register New Person
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <hr>';

// Crime Check Results
if ($result !== null) {
    if ($result['found']) {
        $personData = $result['person'];
        $fullName = trim(($personData['first_name'] ?? '') . ' ' . ($personData['middle_name'] ?? '') . ' ' . ($personData['last_name'] ?? ''));

        // Alert Status
        if ($personData['is_wanted'] || $personData['has_criminal_record']) {
            $content .= '
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-circle"></i> ⚠️ PERSON FOUND IN SYSTEM</h4>';

            if ($personData['is_wanted']) {
                $content .= '<p class="mb-0"><strong>🚨 ALERT: PERSON IS WANTED</strong></p>';
            }

            if ($personData['has_criminal_record']) {
                $content .= '<p class="mb-0"><strong>⚠️ WARNING: HAS CRIMINAL RECORD</strong></p>';
            }

            $content .= '
                    <p class="mb-0">Risk Level: <span class="badge badge-danger">' . sanitize($personData['risk_level']) . '</span></p>
                </div>';
        } else {
            $content .= '
                <div class="alert alert-info">
                    <h4><i class="fas fa-info-circle"></i> Person Found in System</h4>
                    <p class="mb-0">No criminal record or active alerts</p>
                </div>';
        }

        // Person Details
        $content .= '
                <div class="card card-primary mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Person Details</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Name:</th>
                                <td><strong>' . sanitize($fullName) . '</strong></td>
                            </tr>
                            <tr>
                                <th>Ghana Card:</th>
                                <td>' . sanitize($personData['ghana_card_number'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>' . sanitize($personData['contact'] ?? 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Date of Birth:</th>
                                <td>' . ($personData['date_of_birth'] ? format_date($personData['date_of_birth'], 'd M Y') : 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Gender:</th>
                                <td>' . sanitize($personData['gender'] ?? 'N/A') . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';

        // Active Alerts
        if (!empty($result['alerts'])) {
            $content .= '
                <div class="card card-danger mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bell"></i> Active Alerts (' . count($result['alerts']) . ')</h3>
                    </div>
                    <div class="card-body>';

            foreach ($result['alerts'] as $alert) {
                $priorityClass = match($alert['alert_priority']) {
                    'Critical' => 'danger',
                    'High' => 'warning',
                    'Medium' => 'info',
                    default => 'secondary'
                };

                $content .= '
                        <div class="alert alert-' . $priorityClass . '">
                            <h5><strong>' . sanitize($alert['alert_type']) . '</strong> - Priority: ' . sanitize($alert['alert_priority']) . '</h5>
                            <p>' . sanitize($alert['alert_message']) . '</p>
                            ' . ($alert['alert_details'] ? '<p><small>' . sanitize($alert['alert_details']) . '</small></p>' : '') . '
                            <small>Issued: ' . format_date($alert['issued_date'], 'd M Y') . '</small>
                        </div>';
            }

            $content .= '
                    </div>
                </div>';
        }

        // Criminal History
        if (!empty($result['criminal_history'])) {
            $content .= '
                <div class="card card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Criminal History (' . count($result['criminal_history']) . ' records)</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Case Number</th>
                                    <th>Involvement</th>
                                    <th>Offence</th>
                                    <th>Status</th>
                                    <th>Outcome</th>
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

                $content .= '
                                <tr>
                                    <td>' . format_date($record['case_date'], 'd M Y') . '</td>
                                    <td><a href="' . url('/cases/' . $record['case_id']) . '">' . sanitize($record['case_number']) . '</a></td>
                                    <td><span class="badge ' . $involvementClass . '">' . sanitize($record['involvement_type']) . '</span></td>
                                    <td>' . sanitize($record['offence_category'] ?? 'N/A') . '</td>
                                    <td>' . sanitize($record['case_status']) . '</td>
                                    <td>' . sanitize($record['outcome'] ?? 'Pending') . '</td>
                                </tr>';
            }

            $content .= '
                            </tbody>
                        </table>
                    </div>
                </div>';
        }

        // Current Cases (as suspect)
        if (!empty($result['current_cases'])) {
            $content .= '
                <div class="card card-danger mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-folder-open"></i> Current Cases as Suspect (' . count($result['current_cases']) . ')</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Case Number</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Suspect Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>';

            foreach ($result['current_cases'] as $case) {
                $priorityClass = match($case['case_priority']) {
                    'High' => 'badge-danger',
                    'Medium' => 'badge-warning',
                    default => 'badge-info'
                };

                $content .= '
                                <tr>
                                    <td><strong>' . sanitize($case['case_number']) . '</strong></td>
                                    <td>' . sanitize(substr($case['description'], 0, 50)) . '...</td>
                                    <td>' . sanitize($case['case_status']) . '</td>
                                    <td><span class="badge ' . $priorityClass . '">' . sanitize($case['case_priority']) . '</span></td>
                                    <td><span class="badge badge-warning">' . sanitize($case['current_status']) . '</span></td>
                                    <td><a href="' . url('/cases/' . $case['case_id']) . '" class="btn btn-sm btn-primary">View Case</a></td>
                                </tr>';
            }

            $content .= '
                            </tbody>
                        </table>
                    </div>
                </div>';
        }

    } else {
        $content .= '
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Person Not Found in System</h4>
                    <p class="mb-0">No records found for this identifier in criminal database.</p>
                </div>';
    }
}

$content .= '
            </div>
            <div class="card-footer">
                <a href="' . url('/persons') . '" class="btn btn-secondary">
                    <i class="fas fa-users"></i> All Persons
                </a>
                ' . ($result !== null ? '<button onclick="window.print()" class="btn btn-info"><i class="fas fa-print"></i> Print Report</button>' : '') . '
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
