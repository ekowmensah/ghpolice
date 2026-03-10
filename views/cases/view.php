<?php
$safePersonName = static function (?array $record, string $fallback = 'Unknown Person'): string {
    if (!$record) {
        return $fallback;
    }
    $name = trim((string)($record['full_name'] ?? ''));
    if (!$name) {
        $first = trim((string)($record['first_name'] ?? ''));
        $last = trim((string)($record['last_name'] ?? ''));
        $name = trim($first . ' ' . $last);
    }
    return $name ?: $fallback;
};

$jsEscape = static function ($value, string $fallback = ''): string {
    $string = (string)($value ?? $fallback);
    return addslashes($string);
};

$content = '
<style>
    /* Ghana Police Service Theme - Professional & Modern */
    .gps-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .gps-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .gps-card-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 15px 20px;
        border: none;
    }
    .gps-card-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .gps-card-body {
        padding: 20px;
    }
    .gps-info-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    .gps-info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .gps-info-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .gps-info-value {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 500;
    }
    .gps-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    .gps-badge-primary { background: #3498db; color: white; }
    .gps-badge-success { background: #27ae60; color: white; }
    .gps-badge-warning { background: #f39c12; color: white; }
    .gps-badge-danger { background: #e74c3c; color: white; }
    .gps-badge-info { background: #16a085; color: white; }
    .gps-badge-dark { background: #2c3e50; color: white; }
    .gps-badge-secondary { background: #95a5a6; color: white; }
    
    .gps-btn {
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }
    .gps-btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }
    .gps-btn-primary:hover {
        background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
    }
    .gps-btn-dark {
        background: linear-gradient(135deg, #2c3e50 0%, #1a1a1a 100%);
        color: white;
    }
    .gps-btn-dark:hover {
        background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
        transform: translateY(-2px);
    }
    
    .gps-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .gps-table thead th {
        background: #2c3e50;
        color: white;
        padding: 12px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
    }
    .gps-table thead th:first-child { border-radius: 6px 0 0 0; }
    .gps-table thead th:last-child { border-radius: 0 6px 0 0; }
    .gps-table tbody tr {
        transition: all 0.2s ease;
    }
    .gps-table tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
    }
    .gps-table tbody td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    @media (max-width: 768px) {
        .gps-card-header h3 { font-size: 1rem; }
        .gps-info-label { font-size: 0.75rem; }
        .gps-info-value { font-size: 0.9rem; }
        .gps-table { font-size: 0.85rem; }
        .gps-table thead th { padding: 8px; font-size: 0.75rem; }
        .gps-table tbody td { padding: 10px 8px; }
    }
</style>

<div class="row">
    <div class="col-lg-4 col-md-12">
        <!-- Case Info Card -->
        <div class="gps-card">
            <div class="gps-card-header">
                <h3><i class="fas fa-folder-open"></i> Case Information</h3>
            </div>
            <div class="gps-card-body">
                <div class="gps-info-item">
                    <div class="gps-info-label">Case Number</div>
                    <div class="gps-info-value">' . sanitize($case['case_number']) . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Status</div>
                    <div class="gps-info-value">
                        <span class="gps-badge gps-badge-' . match($case['status']) {
                            'Open' => 'warning',
                            'Under Investigation' => 'info',
                            'Closed' => 'success',
                            default => 'secondary'
                        } . '">' . sanitize($case['status']) . '</span>
                    </div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Priority</div>
                    <div class="gps-info-value">
                        <span class="gps-badge gps-badge-' . match($case['case_priority']) {
                            'High' => 'danger',
                            'Medium' => 'warning',
                            'Low' => 'info',
                            default => 'secondary'
                        } . '">' . sanitize($case['case_priority']) . '</span>
                    </div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Case Type</div>
                    <div class="gps-info-value">' . sanitize($case['case_type']) . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Registered</div>
                    <div class="gps-info-value">' . format_date($case['created_at'], 'd M Y H:i') . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Incident Date</div>
                    <div class="gps-info-value">' . format_date($case['incident_date'] ?? null, 'd M Y H:i') . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Location</div>
                    <div class="gps-info-value">' . sanitize($case['location'] ?? 'N/A') . '</div>
                </div>
            </div>
            <div class="card-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef; padding: 15px 20px;">
                <a href="' . url('/investigations/' . $case['id']) . '" class="gps-btn gps-btn-dark btn-block mb-2">
                    <i class="fas fa-search"></i> Investigation Management
                </a>
                <button class="gps-btn gps-btn-warning btn-block mb-2" data-toggle="modal" data-target="#referCaseModal">
                    <i class="fas fa-share"></i> Refer Case
                </button>
                <a href="' . url('/cases/' . $case['id'] . '/edit') . '" class="gps-btn gps-btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Case
                </a>
            </div>
        </div>

        <!-- Complainant Card -->
        <div class="gps-card">
            <div class="gps-card-header">
                <h3><i class="fas fa-user-tie"></i> Complainant</h3>
            </div>
            <div class="gps-card-body">';

if ($complainant) {
    $content .= '
                <div class="gps-info-item">
                    <div class="gps-info-label">Name</div>
                    <div class="gps-info-value">' . sanitize($complainant['full_name'] ?? trim(($complainant['first_name'] ?? '') . ' ' . ($complainant['last_name'] ?? '')) ?: 'N/A') . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Contact</div>
                    <div class="gps-info-value">' . sanitize($complainant['contact'] ?? 'N/A') . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Email</div>
                    <div class="gps-info-value">' . sanitize($complainant['email'] ?? 'N/A') . '</div>
                </div>

                <div class="gps-info-item">
                    <div class="gps-info-label">Address</div>
                    <div class="gps-info-value">' . sanitize($complainant['address'] ?? 'N/A') . '</div>
                </div>

                <a href="' . url('/persons/' . $complainant['person_id']) . '" class="gps-btn gps-btn-dark btn-block mt-3">
                    <i class="fas fa-user"></i> View Profile
                </a>';
} else {
    $content .= '<p class="text-muted">No complainant information</p>';
}

$content .= '
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-12">
        <!-- Case Description -->
        <div class="gps-card">
            <div class="gps-card-header">
                <h3><i class="fas fa-file-alt"></i> Case Description</h3>
            </div>
            <div class="gps-card-body">
                <p style="line-height: 1.8; color: #2c3e50;">' . nl2br(sanitize($case['description'])) . '</p>
            </div>
        </div>

        <!-- Assigned Officers -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-user-shield"></i> Assigned Officers (' . count($assigned_officers) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#assignOfficerModal">
                    <i class="fas fa-plus"></i> Assign Officer
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($assigned_officers)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Service #</th>
                            <th>Officer Name</th>
                            <th>Rank</th>
                            <th>Station</th>
                            <th>Role</th>
                            <th>Assigned Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($assigned_officers as $officer) {
        $statusBadge = match($officer['status'] ?? 'Active') {
            'Active' => 'success',
            'Completed' => 'info',
            'Reassigned' => 'warning',
            default => 'secondary'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($officer['service_number'] ?? 'N/A') . '</strong></td>
                            <td>' . sanitize($officer['officer_name'] ?? 'Unknown') . '</td>
                            <td>' . sanitize($officer['rank_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($officer['station_name'] ?? 'N/A') . '</td>
                            <td><span class="gps-badge gps-badge-primary">' . sanitize($officer['role'] ?? 'Investigator') . '</span></td>
                            <td>' . format_date($officer['assignment_date'], 'd M Y') . '</td>
                            <td><span class="gps-badge gps-badge-' . $statusBadge . '">' . sanitize($officer['status'] ?? 'Active') . '</span></td>
                            <td>
                                <div class="btn-group">';
        
        if (($officer['status'] ?? 'Active') === 'Active') {
            $content .= '
                                    <button type="button" class="btn btn-sm btn-warning" onclick="reassignOfficer(' . $officer['id'] . ', \'' . addslashes($officer['officer_name']) . '\')" title="Reassign">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" onclick="completeAssignment(' . $officer['id'] . ')" title="Mark Complete">
                                        <i class="fas fa-check"></i>
                                    </button>';
        }
        
        $content .= '
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No officers assigned yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Crimes -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-gavel"></i> Crime Categories (' . count($crimes) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#addCrimeModal">
                    <i class="fas fa-plus"></i> Add Crime
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($crimes)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Crime Category</th>
                            <th>Severity</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($crimes as $crime) {
        $severityBadge = match($crime['severity_level'] ?? 'Medium') {
            'Critical' => 'danger',
            'High' => 'warning',
            'Medium' => 'info',
            'Low' => 'secondary',
            default => 'secondary'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($crime['category_name']) . '</strong></td>
                            <td><span class="gps-badge gps-badge-' . $severityBadge . '">' . sanitize($crime['severity_level'] ?? 'Medium') . '</span></td>
                            <td>' . sanitize($crime['crime_description'] ?? 'N/A') . '</td>
                            <td>' . format_date($crime['crime_date'] ?? null, 'd M Y') . '</td>
                            <td>' . sanitize($crime['crime_location'] ?? 'N/A') . '</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCrime(' . $crime['id'] . ', \'' . addslashes($crime['category_name']) . '\')" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No crime categories added yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Suspects -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-user-secret"></i> Suspects (' . count($suspects) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#addSuspectModal">
                    <i class="fas fa-plus"></i> Add Suspect
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($suspects)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Risk Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($suspects as $suspect) {
        // Check if suspect was removed from case
        $isRemoved = !empty($suspect['removed_at']);
        
        $isUnknown = empty($suspect['person_id']);
        $suspectNameRaw = trim($suspect['full_name'] ?? (($suspect['first_name'] ?? '') . ' ' . ($suspect['last_name'] ?? '')));
        if (!$suspectNameRaw) {
            $suspectNameRaw = trim($suspect['unknown_description'] ?? 'Unknown Suspect');
        }
        $suspectNameForJs = $jsEscape($suspectNameRaw, 'Unknown Suspect');
        $unknownDesc = $suspect['unknown_description'] ?? 'Unknown Suspect';
        $unknownDescJs = $jsEscape($unknownDesc, 'Unknown Suspect');
        
        // Add removed indicator to name
        $displayName = $isUnknown 
            ? '<span class="text-warning"><i class="fas fa-user-secret"></i> ' . sanitize($suspect['unknown_description'] ?? 'Unknown Suspect') . '</span>'
            : '<strong>' . sanitize($suspectNameRaw) . '</strong>';
        
        if ($isRemoved) {
            $displayName = '<span style="text-decoration: line-through; opacity: 0.6;">' . $displayName . '</span>';
        }
        
        // Get status with fallback
        $status = !empty($suspect['current_status']) ? $suspect['current_status'] : 'Suspect';
        
        // Status badge color based on suspect status
        $statusBadge = match($status) {
            'Suspect' => 'primary',
            'Arrested' => 'warning',
            'On Bail' => 'info',
            'Charged' => 'info',
            'Discharged' => 'secondary',
            'Acquitted' => 'success',
            'Convicted' => 'danger',
            'Released' => 'info',
            'Deceased' => 'dark',
            default => 'secondary'
        };
        
        $chatIcon = '';
        if (!$isUnknown && !$isRemoved) {
            $chatIcon = ' <button type="button" class="btn btn-xs btn-outline-primary ml-2" onclick="openStatementForPerson(\'Suspect\', ' . $suspect['id'] . ', \'' . $suspectNameForJs . '\')" title="Add Statement">
                            <i class="fas fa-comment"></i>
                        </button>';
        }
        
        $content .= '
                        <tr' . ($isRemoved ? ' style="opacity: 0.6;"' : '') . '>
                            <td>' . $displayName . $chatIcon;
        
        // Show removed badge if applicable
        if ($isRemoved) {
            $removedDate = format_date($suspect['removed_at'], 'd M Y H:i');
            $content .= ' <span class="gps-badge gps-badge-dark" title="Removed by ' . sanitize($suspect['removed_by_name'] ?? 'Unknown') . ' on ' . $removedDate . '">Removed from Case</span>';
        }
        
        $content .= '</td>
                            <td>';
        
        if (!$isRemoved) {
            $content .= '<span class="badge badge-' . $statusBadge . ' status-badge" style="cursor: pointer;" 
                                      data-suspect-id="' . $suspect['id'] . '" 
                                      data-case-id="' . $case['id'] . '"
                                      data-current-status="' . $status . '"
                                      onclick="openStatusModal(' . $suspect['id'] . ', \'' . $jsEscape($status, 'Suspect') . '\', \'' . ($isUnknown ? $unknownDescJs : $suspectNameForJs) . '\')"
                                      title="Click to update status">
                                    ' . sanitize($status) . ' <i class="fas fa-edit ml-1"></i>
                                </span>';
        } else {
            $content .= '<span class="badge badge-' . $statusBadge . '">' . sanitize($status) . '</span>';
        }
        
        $content .= '</td>
                            <td><span class="gps-badge gps-badge-' . match($suspect['risk_level'] ?? 'None') {
                                'Critical' => 'danger',
                                'High' => 'warning',
                                'Medium' => 'info',
                                default => 'secondary'
                            } . '">' . sanitize($suspect['risk_level'] ?? 'None') . '</span></td>
                            <td>
                                <div class="btn-group">';
        
        if (!$isUnknown) {
            $content .= '
                                    <a href="' . url('/persons/' . $suspect['person_id']) . '" class="btn btn-sm btn-info" title="View Person">
                                        <i class="fas fa-eye"></i>
                                    </a>';
            
            // Only show relationships button if suspect has linked persons
            if (!empty($suspect['relationship_count']) && $suspect['relationship_count'] > 0) {
                $content .= '
                                    <button type="button" class="btn btn-sm btn-warning" onclick="viewPersonRelationships(' . $suspect['person_id'] . ', \'' . $suspectNameForJs . '\')" title="View Relationships (' . $suspect['relationship_count'] . ')">
                                        <i class="fas fa-users"></i>
                                    </button>';
            }
        }
        
        // Biometrics button (only for known suspects)
        if (!$isUnknown) {
            // Check if biometrics exist in person_biometrics table
            $fingerprintCount = (int)($suspect['fingerprint_count'] ?? 0);
            $faceCount = (int)($suspect['face_count'] ?? 0);
            $hasBiometrics = ($fingerprintCount > 0) || ($faceCount > 0);
            
            if ($hasBiometrics) {
                // Show "View Biometrics" with green checkmark and count
                $biometricInfo = [];
                if ($fingerprintCount > 0) $biometricInfo[] = $fingerprintCount . ' fingerprint' . ($fingerprintCount > 1 ? 's' : '');
                if ($faceCount > 0) $biometricInfo[] = $faceCount . ' face';
                $tooltip = 'View Biometrics (' . implode(', ', $biometricInfo) . ')';
                
                $content .= '
                                    <button type="button" class="btn btn-sm btn-success" onclick="window.location.href=\'' . url('/persons/' . $suspect['person_id'] . '/biometrics') . '\'" title="' . $tooltip . '">
                                        <i class="fas fa-check-circle"></i>
                                    </button>';
            } else {
                // Show "Capture Biometrics" 
                $content .= '
                                    <button type="button" class="btn btn-sm btn-info" onclick="window.location.href=\'' . url('/persons/' . $suspect['person_id'] . '/biometrics') . '\'" title="Capture Biometrics">
                                        <i class="fas fa-fingerprint"></i>
                                    </button>';
            }
        }
        
        // Only show action buttons for active (non-removed) suspects
        if (!$isRemoved) {
            // Edit button for all suspects
            $content .= '
                                    <button type="button" class="btn btn-sm btn-success" onclick="window.location.href=\'' . url('/cases/' . $case['id'] . '/suspects/' . $suspect['id'] . '/edit') . '\'" title="Edit Suspect Details">
                                        <i class="fas fa-edit"></i>
                                    </button>';
            
            // Upgrade button only for unknown suspects
            if ($isUnknown) {
                $content .= '
                                    <button type="button" class="btn btn-sm btn-primary" onclick="editSuspect(' . $suspect['id'] . ', true)" title="Upgrade to Known Person">
                                        <i class="fas fa-user-plus"></i>
                                    </button>';
            }
            
            // Show Record Arrest button for suspects who can be arrested/re-arrested
            // Allow arrest for: Suspect, On Bail (re-arrest)
            // Don't allow for final statuses: Discharged, Acquitted, Convicted, Released, Deceased
            $canBeArrested = in_array($status, ['Suspect', 'On Bail']);
            
            if ($canBeArrested) {
                $buttonText = ($status === 'On Bail') ? 'Re-Arrest' : 'Record Arrest';
                $content .= '
                                    <button type="button" class="btn btn-sm btn-danger record-arrest-btn" 
                                            data-suspect-id="' . $suspect['id'] . '" 
                                            data-suspect-name="' . htmlspecialchars($suspectNameRaw) . '" 
                                            title="' . $buttonText . '">
                                        <i class="fas fa-user-lock"></i>
                                    </button>';
            }
            
            $content .= '
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSuspect(' . $suspect['id'] . ', \'' . ($isUnknown ? $unknownDescJs : $suspectNameForJs) . '\')" title="Remove from Case">
                                        <i class="fas fa-trash"></i>
                                    </button>';
        } else {
            // For removed suspects, show a restore option or just info
            $content .= '<span class="text-muted"><i class="fas fa-info-circle"></i> No actions available</span>';
        }
        
        $content .= '
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No suspects identified yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Arrests (only show if there are arrests) -->';

if (!empty($arrests)) {
    $content .= '
        <div class="gps-card">
            <div class="gps-card-header">
                <h3><i class="fas fa-handcuffs"></i> Arrests (' . count($arrests) . ')</h3>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';
    
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Suspect</th>
                            <th>Arrest Date</th>
                            <th>Location</th>
                            <th>Arresting Officer</th>
                            <th>Warrant #</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($arrests as $arrest) {
        $suspectName = $safePersonName($arrest, 'Unknown Suspect');
        $arrestDate = format_date($arrest['arrest_date'] ?? null, 'd M Y H:i');
        $warrantNum = !empty($arrest['warrant_number']) ? sanitize($arrest['warrant_number']) : '<span class="text-muted">No Warrant</span>';
        
        // Check if suspect is in custody
        $inCustody = false;
        foreach ($custody_records as $custody) {
            if ($custody['suspect_id'] == $arrest['suspect_id'] && $custody['custody_status'] == 'In Custody') {
                $inCustody = true;
                break;
            }
        }
        
        // Check if suspect has active bail
        $onBail = false;
        foreach ($bail_records as $bail) {
            if ($bail['suspect_id'] == $arrest['suspect_id'] && $bail['bail_status'] == 'Granted') {
                $onBail = true;
                break;
            }
        }
        
        // Get custody record for this suspect
        $custodyRecord = null;
        foreach ($custody_records as $custody) {
            if ($custody['suspect_id'] == $arrest['suspect_id']) {
                $custodyRecord = $custody;
                break;
            }
        }
        
        // Get bail record for this suspect
        $bailRecord = null;
        foreach ($bail_records as $bail) {
            if ($bail['suspect_id'] == $arrest['suspect_id'] && $bail['bail_status'] == 'Granted') {
                $bailRecord = $bail;
                break;
            }
        }
        
        // Determine status badge - make clickable if on bail
        if ($onBail && $bailRecord) {
            $statusBadge = '<span class="gps-badge gps-badge-info view-bail-btn" style="cursor: pointer;" data-bail-id="' . $bailRecord['id'] . '" title="Click to view bail details">On Bail <i class="fas fa-info-circle"></i></span>';
        } elseif ($inCustody) {
            $statusBadge = '<span class="gps-badge gps-badge-success">In Custody</span>';
        } else {
            $statusBadge = '<span class="gps-badge gps-badge-warning">Arrested</span>';
        }
        
        $content .= '
                        <tr>
                            <td><strong>' . $suspectName . '</strong></td>
                            <td>' . $arrestDate . '</td>
                            <td>' . sanitize($arrest['arrest_location'] ?? 'N/A') . '</td>
                            <td>' . sanitize($arrest['arresting_officer_name'] ?? 'N/A') . '</td>
                            <td>' . $warrantNum . '</td>
                            <td>' . $statusBadge . '</td>
                            <td>
                                <div class="btn-group">
                                    <a href="' . url('/arrests/view/' . $arrest['id']) . '" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>';
        
        // Check if suspect has active bail
        $hasActiveBail = false;
        foreach ($bail_records as $bail) {
            if ($bail['suspect_id'] == $arrest['suspect_id'] && $bail['bail_status'] == 'Granted') {
                $hasActiveBail = true;
                break;
            }
        }
        
        // Custody button with dynamic color based on custody status
        if ($custodyRecord) {
            // Has custody record - show view button with color based on status
            $custodyBtnColor = match($custodyRecord['custody_status'] ?? 'Released') {
                'In Custody' => 'success',
                'Released' => 'secondary',
                'Transferred' => 'warning',
                'Escaped' => 'dark',
                default => 'secondary'
            };
            $custodyIcon = ($custodyRecord['custody_status'] === 'In Custody') ? 'lock' : 'unlock';
            $content .= '
                                    <button class="btn btn-sm btn-' . $custodyBtnColor . ' view-custody-btn" 
                                            data-custody-id="' . $custodyRecord['id'] . '" 
                                            data-suspect-name="' . htmlspecialchars($suspectName) . '" 
                                            title="View Custody Details">
                                        <i class="fas fa-' . $custodyIcon . '"></i> Custody
                                    </button>';
        } else {
            // No custody record - show place in custody button
            if (!$hasActiveBail) {
                $content .= '
                                    <button class="btn btn-sm btn-warning place-in-custody-btn" 
                                            data-arrest-id="' . $arrest['id'] . '" 
                                            data-suspect-id="' . $arrest['suspect_id'] . '" 
                                            data-suspect-name="' . htmlspecialchars($suspectName) . '" 
                                            title="Place in Custody">
                                        <i class="fas fa-lock"></i> Custody
                                    </button>';
            }
        }
        
        // Show Grant Bail button for arrested suspects (not on bail)
        if (!$hasActiveBail) {
            $content .= '
                                    <button class="btn btn-sm btn-success grant-bail-btn" 
                                            data-arrest-id="' . $arrest['id'] . '" 
                                            data-suspect-id="' . $arrest['suspect_id'] . '" 
                                            data-suspect-name="' . htmlspecialchars($suspectName) . '" 
                                            title="Grant Bail">
                                        <i class="fas fa-gavel"></i> Bail
                                    </button>';
        }
        
        $content .= '
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>
            </div>
        </div>';
}

$content .= '

        <!-- Charges -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-gavel"></i> Charges (' . count($charges) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#fileChargesModal">
                    <i class="fas fa-plus"></i> File Charges
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($charges)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Suspect</th>
                            <th>Offence</th>
                            <th>Law Section</th>
                            <th>Charge Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($charges as $charge) {
        $suspectName = $safePersonName($charge, 'Unknown Suspect');
        $chargeDate = format_date($charge['charge_date'] ?? null, 'd M Y');
        
        $statusBadge = match($charge['charge_status'] ?? 'Pending') {
            'Filed' => 'success',
            'Pending' => 'warning',
            'Withdrawn' => 'secondary',
            'Dismissed' => 'danger',
            default => 'info'
        };
        
        $content .= '
                        <tr>
                            <td><strong>' . $suspectName . '</strong></td>
                            <td>' . sanitize($charge['offence_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($charge['law_section'] ?? 'N/A') . '</td>
                            <td>' . $chargeDate . '</td>
                            <td><span class="gps-badge gps-badge-' . $statusBadge . '">' . sanitize($charge['charge_status'] ?? 'Pending') . '</span></td>
                            <td>
                                <div class="btn-group">
                                    <a href="' . url('/charges/view/' . $charge['id']) . '" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="' . url('/charges/edit/' . $charge['id']) . '" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No charges filed yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Court Proceedings -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-balance-scale"></i> Court Proceedings (' . count($court_proceedings) . ')</h3>
                <a href="' . url('/court-calendar?case_id=' . $case['id']) . '" class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;">
                    <i class="fas fa-plus"></i> Schedule Hearing
                </a>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($court_proceedings)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Court Date</th>
                            <th>Court Name</th>
                            <th>Hearing Type</th>
                            <th>Judge</th>
                            <th>Outcome</th>
                            <th>Next Hearing</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($court_proceedings as $proceeding) {
        $courtDate = format_date($proceeding['court_date'] ?? null, 'd M Y H:i');
        $nextHearing = !empty($proceeding['next_hearing_date']) ? format_date($proceeding['next_hearing_date'], 'd M Y') : '<span class="text-muted">Not Set</span>';
        
        $hearingTypeBadge = match($proceeding['hearing_type'] ?? '') {
            'Arraignment' => 'primary',
            'Hearing' => 'info',
            'Verdict' => 'success',
            'Sentencing' => 'warning',
            'Appeal' => 'danger',
            default => 'secondary'
        };
        
        $content .= '
                        <tr>
                            <td>' . $courtDate . '</td>
                            <td>' . sanitize($proceeding['court_name'] ?? 'N/A') . '</td>
                            <td><span class="gps-badge gps-badge-' . $hearingTypeBadge . '">' . sanitize($proceeding['hearing_type'] ?? 'N/A') . '</span></td>
                            <td>' . sanitize($proceeding['judge_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($proceeding['outcome'] ?? 'Pending') . '</td>
                            <td>' . $nextHearing . '</td>
                            <td>
                                <a href="' . url('/court-calendar?proceeding_id=' . $proceeding['id']) . '" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No court proceedings scheduled yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Bail Records (REMOVED - Now shown in Arrests table) -->
        <!-- Custody Records (REMOVED - Now shown in Arrests table) -->

        <!-- Witnesses -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-users"></i> Witnesses (' . count($witnesses) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#addWitnessModal">
                    <i class="fas fa-plus"></i> Add Witness
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($witnesses)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($witnesses as $witness) {
        $witnessNameRaw = trim($witness['full_name'] ?? (($witness['first_name'] ?? '') . ' ' . ($witness['last_name'] ?? '')));
        if (!$witnessNameRaw) {
            $witnessNameRaw = 'Witness';
        }
        $witnessNameJs = $jsEscape($witnessNameRaw, 'Witness');
        $witnessTypeBadge = match($witness['witness_type']) {
            'Eye Witness' => 'primary',
            'Expert Witness' => 'info',
            'Character Witness' => 'success',
            default => 'secondary'
        };
        
        $chatIcon = ' <button type="button" class="btn btn-xs btn-outline-primary ml-2" onclick="openStatementForPerson(\'Witness\', ' . ($witness['id'] ?? 0) . ', \'' . $witnessNameJs . '\')" title="Add Statement">
                        <i class="fas fa-comment"></i>
                    </button>';
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($witnessNameRaw) . '</strong>' . $chatIcon . '</td>
                            <td><span class="gps-badge gps-badge-' . $witnessTypeBadge . '">' . sanitize($witness['witness_type']) . '</span></td>
                            <td>' . sanitize($witness['contact'] ?? 'N/A') . '</td>
                            <td>' . format_date($witness['added_date'], 'd M Y') . '</td>
                            <td>
                                <div class="btn-group">
                                    <a href="' . url('/persons/' . $witness['person_id']) . '" class="btn btn-sm btn-info" title="View Person">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeWitness(' . ($witness['id'] ?? 0) . ', \'' . $witnessNameJs . '\')" title="Remove from Case">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No witnesses added yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Statements -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-file-signature"></i> Statements (' . count($statements) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#addStatementModal">
                    <i class="fas fa-plus"></i> Record Statement
                </button>
            </div>
            <div class="gps-card-body">';

if (!empty($statements)) {
    // Group statements by type
    $groupedStatements = [
        'Complainant' => [],
        'Suspect' => [],
        'Witness' => []
    ];
    
    foreach ($statements as $statement) {
        $groupedStatements[$statement['statement_type']][] = $statement;
    }
    
    // Display grouped statements
    foreach ($groupedStatements as $type => $stmts) {
        if (empty($stmts)) continue;
        
        $typeColors = [
            'Complainant' => 'info',
            'Suspect' => 'danger',
            'Witness' => 'success'
        ];
        $color = $typeColors[$type];
        
        $content .= '
                <div class="gps-card" style="margin-bottom: 15px; border-left: 4px solid ' . match($color) {
                    'info' => '#16a085',
                    'danger' => '#e74c3c',
                    'success' => '#27ae60',
                    default => '#95a5a6'
                } . ';">
                    <div style="background: #f8f9fa; padding: 12px 20px; border-bottom: 1px solid #e9ecef;">
                        <h5 style="margin: 0; color: #2c3e50; font-weight: 600;">
                            <i class="fas fa-user"></i> ' . $type . ' Statements (' . count($stmts) . ')
                        </h5>
                    </div>
                    <div style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40%">Preview</th>
                                        <th width="20%">Person</th>
                                        <th width="15%">Recorded By</th>
                                        <th width="12%">Date</th>
                                        <th width="8%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>';
        
        foreach ($stmts as $statement) {
            $status = $statement['status'] ?? 'active';
            $statusBadge = '';
            
            if ($status === 'cancelled') {
                $statusBadge = '<span class="badge badge-danger ml-2">Cancelled</span>';
            } elseif ($status === 'superseded') {
                $statusBadge = '<span class="badge badge-warning ml-2">Re-written</span>';
            }
            
            // Get preview (first 150 characters)
            $preview = strip_tags($statement['statement_text']);
            $preview = strlen($preview) > 150 ? substr($preview, 0, 150) . '...' : $preview;
            
            $strikethrough = ($status !== 'active') ? 'style="text-decoration: line-through;"' : '';
            
            // Format person details
            $personDetails = '<strong>' . sanitize($statement['person_name'] ?? 'N/A') . '</strong>';
            if (!empty($statement['person_contact'])) {
                $personDetails .= '<br><small class="text-muted"><i class="fas fa-phone"></i> ' . sanitize($statement['person_contact']) . '</small>';
            }
            
            $content .= '
                                    <tr class="' . ($status !== 'active' ? 'text-muted' : '') . '">
                                        <td>
                                            <div class="statement-preview" ' . $strikethrough . '>
                                                ' . nl2br(sanitize($preview)) . '
                                                ' . $statusBadge . '
                                            </div>
                                        </td>
                                        <td>' . $personDetails . '</td>
                                        <td>' . sanitize($statement['recorded_by_name']) . '</td>
                                        <td>' . format_date($statement['recorded_at'], 'd M Y') . '</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="viewStatement(' . $statement['id'] . ')" title="View Full Statement">
                                                <i class="fas fa-eye"></i>
                                            </button>
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
} else {
    $content .= '<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> No statements recorded yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Evidence -->
        <div class="gps-card">
            <div class="gps-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3><i class="fas fa-archive"></i> Evidence (' . count($evidence) . ')</h3>
                <button class="gps-btn gps-btn-primary" style="padding: 8px 16px; margin-top: 5px;" data-toggle="modal" data-target="#uploadEvidenceModal">
                    <i class="fas fa-upload"></i> Upload Evidence
                </button>
            </div>
            <div class="gps-card-body" style="padding: 0; overflow-x: auto;">';

if (!empty($evidence)) {
    $content .= '
                <table class="gps-table">
                    <thead>
                        <tr>
                            <th>Evidence #</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Collection Date</th>
                            <th>File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($evidence as $item) {
        $evidenceTypeBadge = match($item['evidence_type']) {
            'Physical' => 'primary',
            'Digital' => 'info',
            'Documentary' => 'success',
            'Forensic' => 'danger',
            default => 'secondary'
        };
        
        $fileSize = $item['file_size'] ? round($item['file_size'] / 1024, 2) . ' KB' : 'N/A';
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($item['evidence_number']) . '</strong></td>
                            <td><span class="gps-badge gps-badge-' . $evidenceTypeBadge . '">' . sanitize($item['evidence_type']) . '</span></td>
                            <td>' . sanitize($item['description'] ?? 'N/A') . '</td>
                            <td>' . format_date($item['collection_date'], 'd M Y') . '</td>
                            <td><small class="text-muted">' . $fileSize . '</small></td>
                            <td>
                                <div class="btn-group">
                                    <a href="' . url('/cases/' . $case['id'] . '/evidence/' . $item['id'] . '/download') . '" class="btn btn-sm btn-info" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteEvidence(' . $item['id'] . ', \'' . addslashes($item['evidence_number']) . '\')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No evidence uploaded yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Case Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline">';

if (!empty($timeline)) {
    foreach ($timeline as $entry) {
        $newStatus = $entry['new_status'] ?? $entry['status'] ?? 'Update';
        $iconClass = match($newStatus) {
            'Open' => 'bg-success',
            'Under Investigation' => 'bg-info',
            'Closed' => 'bg-secondary',
            default => 'bg-primary'
        };
        
        $content .= '
                    <div>
                        <i class="fas fa-circle ' . $iconClass . '"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . format_date($entry['change_date'] ?? $entry['created_at'] ?? date('Y-m-d H:i:s'), 'd M Y H:i') . '</span>
                            <h3 class="timeline-header">Status changed to: <strong>' . sanitize($newStatus) . '</strong></h3>
                            <div class="timeline-body">' . sanitize($entry['remarks'] ?? $entry['description'] ?? '') . '</div>
                            <div class="timeline-footer">
                                <small>By: ' . sanitize($entry['changed_by_name'] ?? $entry['user_name'] ?? 'System') . '</small>
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>';
} else {
    $content .= '<p class="text-muted">No timeline entries</p>';
}

$content .= '
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Suspect Modal -->
<div class="modal fade" id="addSuspectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-user-secret"></i> Add Suspect to Case</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/suspects') . '" id="addSuspectForm">
                ' . csrf_field() . '
                <div class="modal-body">
                    <!-- Suspect Type Selection - Always Visible -->
                    <div class="card card-outline card-info mb-3" style="border-width: 2px;">
                        <div class="card-body bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <h5 class="mb-1"><i class="fas fa-info-circle text-info"></i> <strong>Suspect Identification Status</strong></h5>
                                    <p class="mb-0 text-muted small">
                                        <strong>Known Person:</strong> You have at least their phone number or Ghana Card details<br>
                                        <strong>Unknown:</strong> Only physical description available (no identifying documents)
                                    </p>
                                </div>
                                <div class="col-md-5">
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active">
                                            <input type="radio" name="suspect_type" value="known" checked> <i class="fas fa-user-check"></i> Known
                                        </label>
                                        <label class="btn btn-outline-warning">
                                            <input type="radio" name="suspect_type" value="unknown"> <i class="fas fa-user-secret"></i> Unknown
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Known Person Section -->
                    <div id="knownPersonSection" class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-check"></i> Known Person - Identify Suspect</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Search Person <span class="text-danger" id="person_required">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="suspect_search" placeholder="Search by name, Ghana Card, or phone number..." autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#suspectPersonModal">
                                                    <i class="fas fa-user-plus"></i> New Person
                                                </button>
                                            </div>
                                        </div>
                                        <div id="suspect_results" class="list-group mt-2" style="position: absolute; z-index: 1000; max-height: 250px; overflow-y: auto; width: 95%; display: none;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Suspect Status <span class="text-danger">*</span></label>
                                        <select class="form-control" name="current_status" id="suspect_status" required>
                                            <option value="Suspect">Suspect</option>
                                            <option value="Arrested">Arrested</option>
                                            <option value="Charged">Charged</option>
                                            <option value="Discharged">Discharged</option>
                                            <option value="Acquitted">Acquitted</option>
                                            <option value="Convicted">Convicted</option>
                                            <option value="Released">Released</option>
                                            <option value="Deceased">Deceased</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="selected_suspect_info" class="alert alert-success" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong>Selected Person:</strong>
                                        <div id="suspect_display" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="clearSuspectSelection()">
                                            <i class="fas fa-times"></i> Change
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="person_id" id="suspect_person_id">
                        </div>
                    </div>

                    <!-- Unknown Suspect Section -->
                    <div id="unknownSuspectSection" class="card card-outline card-warning mb-3" style="display: none;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-secret"></i> Unknown Suspect - Descriptive Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Unidentified Suspect</strong>
                                <p class="mb-0 small">Provide as much descriptive information as possible to aid in identification</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Description/Nickname <span class="text-danger" id="desc_required">*</span></label>
                                        <input type="text" class="form-control" name="unknown_description" id="unknown_description" placeholder="e.g., Tall man, Scar face, etc.">
                                        <small class="text-muted">Brief identifier for this unknown suspect</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estimated Age</label>
                                        <input type="text" class="form-control" name="estimated_age" placeholder="e.g., 25-30 years">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select class="form-control" name="unknown_gender">
                                            <option value="">Unknown</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Height/Build</label>
                                        <input type="text" class="form-control" name="height_build" placeholder="e.g., Tall, Medium build">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Complexion</label>
                                        <input type="text" class="form-control" name="complexion" placeholder="e.g., Dark, Fair, Light">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Last Seen Wearing</label>
                                        <input type="text" class="form-control" name="clothing" placeholder="Clothing description">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suspect Details Section -->
                    <div class="card card-outline card-warning mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Suspect Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Alias/Nickname</label>
                                        <input type="text" class="form-control" name="alias" placeholder="Known aliases">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Last Known Location</label>
                                        <input type="text" class="form-control" name="last_known_location" placeholder="Address or area">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Arrest Date</label>
                                        <input type="date" class="form-control" name="arrest_date">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Identifying Marks/Features</label>
                                        <textarea class="form-control" name="identifying_marks" rows="2" placeholder="Scars, tattoos, physical characteristics..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Notes/Observations</label>
                                        <textarea class="form-control" name="notes" rows="2" placeholder="Additional information about the suspect..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-user-plus"></i> Add Suspect to Case
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Statement Modal -->
<div class="modal fade" id="addStatementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-file-signature"></i> Record Statement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/statements') . '" enctype="multipart/form-data" id="addStatementForm">
                ' . csrf_field() . '
                <div class="modal-body">
                    <!-- Statement Type & Source -->
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-tag"></i> Statement Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Statement Type <span class="text-danger">*</span></label>
                                        <select class="form-control" name="statement_type" id="statement_type" required onchange="updatePersonDropdown()">
                                            <option value="">Select Type</option>
                                            <option value="Complainant">Complainant Statement</option>
                                            <option value="Suspect">Suspect Statement</option>
                                            <option value="Witness">Witness Statement</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Select Person <span class="text-danger">*</span></label>
                                        <select class="form-control" id="person_select" required>
                                            <option value="">Select statement type first</option>
                                        </select>
                                        <input type="hidden" name="complainant_id" id="complainant_id">
                                        <input type="hidden" name="suspect_id" id="suspect_id">
                                        <input type="hidden" name="witness_id" id="witness_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Statement Date & Time</label>
                                        <input type="datetime-local" class="form-control" name="statement_datetime" value="' . date('Y-m-d\TH:i') . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Location Recorded</label>
                                        <input type="text" class="form-control" name="location_recorded" placeholder="Station, office, or location">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statement Content -->
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit"></i> Statement Content</h3>
                            <div class="card-tools">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-primary active" id="typeBtn" onclick="switchStatementMode(\'type\')">
                                        <i class="fas fa-keyboard"></i> Type
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="uploadBtn" onclick="switchStatementMode(\'upload\')">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Type Mode -->
                            <div id="typeMode">
                                <div class="form-group">
                                    <label>Statement Text <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="statement_text" id="statement_text" rows="10" placeholder="Enter the complete statement as provided by the person...

Include:
- Full account of events
- Dates, times, and locations
- Names of other persons involved
- Any evidence or witnesses mentioned
- Signature or verbal confirmation"></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Record the statement verbatim as much as possible
                                    </small>
                                </div>
                            </div>

                            <!-- Upload Mode -->
                            <div id="uploadMode" style="display: none;">
                                <div class="form-group">
                                    <label>Upload Scanned Statement <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="scanned_copy" name="scanned_copy" accept=".pdf,.jpg,.jpeg,.png">
                                        <label class="custom-file-label" for="scanned_copy">Choose file...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Accepted formats: PDF, JPG, PNG (Max 5MB)
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>Brief Summary</label>
                                    <textarea class="form-control" name="statement_summary" rows="3" placeholder="Brief summary of the uploaded statement..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Section - REQUIRED -->
                    <div class="card card-outline card-warning">
                        <div class="card-header bg-warning">
                            <h3 class="card-title text-white">
                                <i class="fas fa-exclamation-triangle"></i> Statement Verification 
                                <span class="badge badge-danger ml-2">REQUIRED</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> <strong>Important:</strong> You must confirm at least ONE verification method before saving the statement.
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox custom-control-lg">
                                            <input type="checkbox" class="custom-control-input" id="verified" name="verified" value="1">
                                            <label class="custom-control-label font-weight-bold" for="verified">
                                                <i class="fas fa-check-circle text-success"></i> Statement read back and confirmed by person
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox custom-control-lg">
                                            <input type="checkbox" class="custom-control-input" id="signed" name="signed" value="1">
                                            <label class="custom-control-label font-weight-bold" for="signed">
                                                <i class="fas fa-pen-fancy text-primary"></i> Statement signed or thumb-printed
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Interpreter Used</label>
                                        <input type="text" class="form-control" name="interpreter" placeholder="Name if interpreter was used">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Recording Officer Notes</label>
                                        <textarea class="form-control" name="officer_notes" rows="2" placeholder="Any observations or notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Save Statement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Person Creation Modal for Suspect -->
<div class="modal fade" id="suspectPersonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Register New Person</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="suspectPersonForm">
                    ' . csrf_field() . '
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ghana Card Number</label>
                                <input type="text" class="form-control" name="ghana_card_number" placeholder="GHA-XXXXXXXXX-X">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveSuspectPerson()">
                    <i class="fas fa-save"></i> Save Person
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-edit"></i> Update Suspect Status</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Suspect:</strong> <span id="modal-suspect-name"></span>
                </div>
                <div class="form-group">
                    <label>Current Status</label>
                    <input type="text" class="form-control" id="modal-current-status" readonly>
                </div>
                <div class="form-group">
                    <label>New Status <span class="text-danger">*</span></label>
                    <select class="form-control" id="modal-new-status">
                        <option value="Suspect">Suspect</option>
                        <option value="Arrested">Arrested</option>
                        <option value="On Bail">On Bail</option>
                        <option value="Charged">Charged</option>
                        <option value="Discharged">Discharged</option>
                        <option value="Acquitted">Acquitted</option>
                        <option value="Convicted">Convicted</option>
                        <option value="Released">Released</option>
                        <option value="Deceased">Deceased</option>
                    </select>
                </div>
                <input type="hidden" id="modal-suspect-id">
                <input type="hidden" id="modal-case-id" value="' . $case['id'] . '">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateSuspectStatus()">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Witness Modal -->
<div class="modal fade" id="addWitnessModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-users"></i> Add Witness to Case</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/witnesses') . '" id="addWitnessForm">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="card card-outline card-success mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-search"></i> Search Person</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Search Person <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="witness_search" placeholder="Search by name, Ghana Card, or phone number..." autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#witnessPersonModal">
                                            <i class="fas fa-user-plus"></i> New Person
                                        </button>
                                    </div>
                                </div>
                                <div id="witness_results" class="list-group mt-2" style="position: absolute; z-index: 1000; max-height: 250px; overflow-y: auto; width: 95%; display: none;"></div>
                            </div>
                            
                            <div id="selected_witness_info" class="alert alert-success" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong>Selected Person:</strong>
                                        <div id="witness_display" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="clearWitnessSelection()">
                                            <i class="fas fa-times"></i> Change
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="person_id" id="witness_person_id" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Witness Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="witness_type" required>
                            <option value="Eye Witness">Eye Witness</option>
                            <option value="Expert Witness">Expert Witness</option>
                            <option value="Character Witness">Character Witness</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitWitnessBtn">
                        <i class="fas fa-save"></i> Add Witness
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Witness Person Creation Modal -->
<div class="modal fade" id="witnessPersonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Register New Person</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="witnessPersonForm">
                    ' . csrf_field() . '
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ghana Card Number</label>
                                <input type="text" class="form-control" name="ghana_card_number" placeholder="GHA-XXXXXXXXX-X">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveWitnessPerson()">
                    <i class="fas fa-save"></i> Save Person
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Statement Modal -->
<div class="modal fade" id="viewStatementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-file-alt"></i> Statement Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="statementModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading statement...</p>
                </div>
            </div>
            <div class="modal-footer" id="statementModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Statement Modal -->
<div class="modal fade" id="cancelStatementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-ban"></i> Cancel Statement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="cancelStatementForm">
                ' . csrf_field() . '
                <input type="hidden" name="statement_id" id="cancel_statement_id">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This statement will be marked as cancelled but will remain in the system for record-keeping purposes.
                    </div>
                    <div class="form-group">
                        <label>Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="cancellation_reason" rows="3" required placeholder="Explain why this statement is being cancelled..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Cancel Statement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Person Relationships Modal -->
<div class="modal fade" id="personRelationshipsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="fas fa-users"></i> <span id="relationship_person_name"></span> - Relationships</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="relationships_loading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading relationships...</p>
                </div>
                <div id="relationships_content" style="display: none;">
                    <!-- Relationships will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Evidence Modal -->
<div class="modal fade" id="uploadEvidenceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="fas fa-upload"></i> Upload Evidence</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/evidence') . '" enctype="multipart/form-data">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Evidence Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="evidence_type" required>
                            <option value="Physical">Physical Evidence</option>
                            <option value="Digital">Digital Evidence</option>
                            <option value="Documentary">Documentary Evidence</option>
                            <option value="Forensic">Forensic Evidence</option>
                            <option value="Testimonial">Testimonial Evidence</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required placeholder="Describe the evidence..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Collection Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="collection_date" value="' . date('Y-m-d') . '" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Collection Location</label>
                                <input type="text" class="form-control" name="collection_location" placeholder="Where was it collected?">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Evidence File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="evidence_file" name="evidence_file" required>
                            <label class="custom-file-label" for="evidence_file">Choose file...</label>
                        </div>
                        <small class="form-text text-muted">
                            Supported formats: Images, Documents, Videos, Audio files. Max size: 50MB
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-upload"></i> Upload Evidence
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Officer Modal -->
<div class="modal fade" id="assignOfficerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-user-shield"></i> Assign Officer to Case</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/assignments') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Officer <span class="text-danger">*</span></label>
                        <select class="form-control" name="officer_id" required>
                            <option value="">-- Select Officer --</option>';

// Get all active officers for assignment
$db = \App\Config\Database::getConnection();
$stmt = $db->prepare("
    SELECT o.id, o.service_number, 
           CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
           pr.rank_name, s.station_name
    FROM officers o
    LEFT JOIN police_ranks pr ON o.rank_id = pr.id
    LEFT JOIN stations s ON o.current_station_id = s.id
    WHERE o.employment_status = 'Active'
    ORDER BY pr.rank_level DESC, o.first_name
");
$stmt->execute();
$availableOfficers = $stmt->fetchAll();

foreach ($availableOfficers as $officer) {
    $content .= '
                            <option value="' . $officer['id'] . '">' . 
                                sanitize($officer['service_number']) . ' - ' . 
                                sanitize($officer['officer_name']) . ' (' . 
                                sanitize($officer['rank_name'] ?? 'N/A') . ')' .
                            '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select class="form-control" name="role" required>
                            <option value="Lead Investigator">Lead Investigator</option>
                            <option value="Investigator" selected>Investigator</option>
                            <option value="Assisting Officer">Assisting Officer</option>
                            <option value="Evidence Officer">Evidence Officer</option>
                            <option value="Interviewing Officer">Interviewing Officer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Assignment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Assign Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Crime Modal - Modern Design with Select2 -->
<div class="modal fade" id="addCrimeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 12px 12px 0 0; padding: 20px 30px;">
                <h5 class="modal-title text-white" style="font-weight: 600; font-size: 1.25rem;">
                    <i class="fas fa-gavel"></i> Add Crime Categories to Case
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1; text-shadow: none;">
                    <span style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            <form method="POST" id="addCrimeForm" action="' . url('/cases/' . $case['id'] . '/crimes') . '">
                ' . csrf_field() . '
                <div class="modal-body" style="padding: 30px;">
                    <div class="alert alert-info" style="border-left: 4px solid #17a2b8; background: #e7f6f8; border-radius: 8px;">
                        <i class="fas fa-info-circle"></i> <strong>Tip:</strong> You can select multiple crime categories at once. Use the search box to quickly find crimes.
                    </div>
                    
                    <div class="form-group">
                        <label style="font-weight: 600; color: #333; margin-bottom: 10px;">
                            Crime Categories <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select2-crimes" name="crime_category_ids[]" id="crime_category_ids" multiple="multiple" required style="width: 100%;">
                            <option value="">-- Select Crime Categories --</option>';

// Get all crime categories grouped by severity
$stmt = $db->prepare("SELECT id, category_name, severity_level FROM crime_categories ORDER BY severity_level DESC, category_name");
$stmt->execute();
$crimeCategories = $stmt->fetchAll();

// Group by severity
$groupedCrimes = [];
foreach ($crimeCategories as $category) {
    $severity = $category['severity_level'];
    if (!isset($groupedCrimes[$severity])) {
        $groupedCrimes[$severity] = [];
    }
    $groupedCrimes[$severity][] = $category;
}

// Output grouped options
foreach (['High', 'Medium', 'Low'] as $severity) {
    if (isset($groupedCrimes[$severity])) {
        $content .= '<optgroup label="' . $severity . ' Severity">';
        foreach ($groupedCrimes[$severity] as $category) {
            $content .= '
                                <option value="' . $category['id'] . '">' . 
                                    sanitize($category['category_name']) .
                                '</option>';
        }
        $content .= '</optgroup>';
    }
}

$content .= '
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-search"></i> Start typing to search for specific crimes
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label style="font-weight: 600; color: #333; margin-bottom: 10px;">Additional Details</label>
                        <textarea class="form-control" name="crime_description" rows="3" placeholder="Provide any additional details about these crimes..." style="border-radius: 8px; border: 1px solid #ddd;"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 600; color: #333; margin-bottom: 10px;">
                                    <i class="fas fa-calendar"></i> Crime Date/Time
                                </label>
                                <input type="datetime-local" class="form-control" name="crime_date" style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 600; color: #333; margin-bottom: 10px;">
                                    <i class="fas fa-map-marker-alt"></i> Crime Location
                                </label>
                                <input type="text" class="form-control" name="crime_location" placeholder="Specific location..." style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-radius: 0 0 12px 12px; padding: 20px 30px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 10px 24px;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 8px; padding: 10px 24px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none;">
                        <i class="fas fa-plus"></i> Add Selected Crimes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refer Case Modal -->
<div class="modal fade" id="referCaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-share"></i> Refer Case to Another Station/Unit</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/referrals') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Current Station:</strong> ' . sanitize($case['station_name'] ?? 'N/A') . '
                    </div>
                    
                    <div class="form-group">
                        <label>Refer To Level <span class="text-danger">*</span></label>
                        <select class="form-control" name="to_level" id="referral_level" required>
                            <option value="">-- Select Level --</option>
                            <option value="Station">Station</option>
                            <option value="District">District HQ</option>
                            <option value="Division">Divisional HQ</option>
                            <option value="Region">Regional HQ</option>
                            <option value="CID">CID</option>
                            <option value="DOVVSU">DOVVSU</option>
                            <option value="Cybercrime">Cybercrime Unit</option>
                            <option value="Special Unit">Special Unit</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="station_select_group">
                        <label>Refer To Station <span class="text-danger">*</span></label>
                        <select class="form-control" name="to_station_id" id="referral_station">
                            <option value="">-- Select Station --</option>';

// Get all stations
$stmt = $db->prepare("
    SELECT s.id, s.station_name, s.station_code, d.district_name 
    FROM stations s
    LEFT JOIN districts d ON s.district_id = d.id
    ORDER BY s.station_name
");
$stmt->execute();
$allStations = $stmt->fetchAll();

foreach ($allStations as $station) {
    $content .= '
                            <option value="' . $station['id'] . '">' . 
                                sanitize($station['station_name']) . ' [' . 
                                sanitize($station['station_code']) . '] - ' .
                                sanitize($station['district_name'] ?? 'N/A') .
                            '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason for Referral <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="remarks" rows="4" required placeholder="Explain why this case should be referred..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-share"></i> Refer Case
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Officer Modal -->
<div class="modal fade" id="reassignOfficerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-exchange-alt"></i> Reassign Case Officer</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="reassignOfficerForm">
                ' . csrf_field() . '
                <input type="hidden" name="assignment_id" id="reassign_assignment_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Current Officer:</strong> <span id="reassign_current_officer"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>New Officer <span class="text-danger">*</span></label>
                        <select class="form-control" name="new_officer_id" id="reassign_new_officer" required>
                            <option value="">-- Select New Officer --</option>';

// Get currently assigned officer IDs for this case
$assignedOfficerIds = array_map(function($ao) { return $ao['assigned_to'] ?? $ao['officer_id']; }, $assigned_officers);

foreach ($availableOfficers as $officer) {
    // Skip officers already assigned to this case
    if (in_array($officer['id'], $assignedOfficerIds)) {
        continue;
    }
    
    $content .= '
                            <option value="' . $officer['id'] . '">' . 
                                sanitize($officer['service_number']) . ' - ' . 
                                sanitize($officer['officer_name']) . ' (' . 
                                sanitize($officer['rank_name'] ?? 'N/A') . ')' .
                            '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason for Reassignment</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Why is this case being reassigned?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exchange-alt"></i> Reassign Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
';

// Pre-process suspect data for JavaScript
$suspectDataForJS = array_values(array_filter(array_map(function($s) use ($safePersonName) {
    if (empty($s['person_id'])) {
        return null;
    }
    return [
        'id' => $s['id'],
        'name' => $safePersonName($s, 'Unknown Suspect')
    ];
}, $suspects), function($s) { return $s !== null; }));

// Pre-process witness data for JavaScript
$witnessDataForJS = array_values(array_map(function($w) use ($safePersonName) {
    return [
        'id' => $w['id'] ?? null,
        'witness_id' => $w['witness_id'] ?? null,
        'person_id' => $w['person_id'] ?? null,
        'name' => $safePersonName($w, 'Witness')
    ];
}, $witnesses));

$scripts = '
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for crime categories with modern styling
    $(".select2-crimes").select2({
        placeholder: "Search and select crime categories...",
        allowClear: true,
        width: "100%",
        theme: "bootstrap4",
        closeOnSelect: false,
        templateResult: function(data) {
            if (!data.id) return data.text;
            var $result = $("<div class=\"select2-result-item\">" +
                "<i class=\"fas fa-gavel text-danger\"></i> " +
                "<span>" + data.text + "</span>" +
                "</div>");
            return $result;
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return "<i class=\"fas fa-gavel\"></i> " + data.text;
        }
    });
    
    // Clear modal when closed
    $("#addCrimeModal").on("hidden.bs.modal", function() {
        $(".select2-crimes").val(null).trigger("change");
        $("#addCrimeForm")[0].reset();
        $("#addCrimeModal .alert-danger, #addCrimeModal .alert-success").remove();
    });
    
    // Update file input label when file is selected
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split(/[\\\/]/).pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    
    // Suspect search with enhanced display
    let searchTimeout;
    $("#suspect_search").on("keyup", function() {
        clearTimeout(searchTimeout);
        let keyword = $(this).val().trim();
        
        if (keyword.length < 2) {
            $("#suspect_results").hide();
            return;
        }
        
        var loadingDiv = $("<div>").addClass("list-group-item");
        loadingDiv.html("<i class=\"fas fa-spinner fa-spin\"></i> Searching...");
        $("#suspect_results").empty().append(loadingDiv).show();
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "' . url('/persons/search') . '",
                data: { q: keyword },
                success: function(response) {
                    $("#suspect_results").empty();
                    
                    if (response.success && response.persons.length > 0) {
                        response.persons.forEach(function(person) {
                            var ghanaCard = person.ghana_card_number || "N/A";
                            var phoneNumber = person.phone_number || "N/A";
                            
                            var item = $("<a>")
                                .attr("href", "#")
                                .addClass("list-group-item list-group-item-action suspect-select-item")
                                .data("id", person.id)
                                .data("name", person.full_name)
                                .data("ghana", ghanaCard)
                                .data("phone", phoneNumber);
                            
                            var content = $("<div>").addClass("d-flex justify-content-between");
                            var leftDiv = $("<div>");
                            leftDiv.html("<strong>" + person.full_name + "</strong><br><small class=\"text-muted\"><i class=\"fas fa-id-card\"></i> " + ghanaCard + " | <i class=\"fas fa-phone\"></i> " + phoneNumber + "</small>");
                            
                            var rightDiv = $("<div>").addClass("text-right");
                            rightDiv.html("<i class=\"fas fa-arrow-right text-primary\"></i>");
                            
                            content.append(leftDiv).append(rightDiv);
                            item.append(content);
                            $("#suspect_results").append(item);
                        });
                        $("#suspect_results").show();
                    } else {
                        var noResultDiv = $("<div>").addClass("list-group-item text-center");
                        noResultDiv.html("<i class=\"fas fa-user-slash\"></i> No persons found. Click \"New Person\" to register.");
                        $("#suspect_results").append(noResultDiv).show();
                    }
                },
                error: function() {
                    var errorDiv = $("<div>").addClass("list-group-item text-danger");
                    errorDiv.html("<i class=\"fas fa-exclamation-triangle\"></i> Search failed. Please try again.");
                    $("#suspect_results").empty().append(errorDiv).show();
                }
            });
        }, 300);
    });
    
    // Handle suspect selection with event delegation
    $(document).on("click", ".suspect-select-item", function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        var name = $(this).data("name");
        var ghanaCard = $(this).data("ghana");
        var phone = $(this).data("phone");
        selectSuspect(id, name, ghanaCard, phone);
    });
    
    // Hide results when clicking outside
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#suspect_search, #suspect_results").length) {
            $("#suspect_results").hide();
        }
    });
    
    // File input label update
    $(".custom-file-input").on("change", function() {
        let fileName = $(this).val().split(/[\\\/]/).pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    
    // Statement mode validation
    $("#addStatementForm").on("submit", function(e) {
        let mode = $("#typeMode").is(":visible") ? "type" : "upload";
        
        if (mode === "type") {
            let text = $("#statement_text").val().trim();
            if (!text) {
                e.preventDefault();
                var errorAlert = $("<div>").addClass("alert alert-danger alert-dismissible fade show").attr("role", "alert");
                errorAlert.html("<i class=\\"fas fa-exclamation-circle\\"></i> Please enter the statement text<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
                $("#addStatementModal .modal-body").prepend(errorAlert);
                $("#statement_text").focus();
                return false;
            }
        } else {
            let file = $("#scanned_copy")[0].files[0];
            if (!file) {
                e.preventDefault();
                var errorAlert = $("<div>").addClass("alert alert-danger alert-dismissible fade show").attr("role", "alert");
                errorAlert.html("<i class=\\"fas fa-exclamation-circle\\"></i> Please upload a scanned statement file<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
                $("#addStatementModal .modal-body").prepend(errorAlert);
                $("#scanned_copy").focus();
                return false;
            }
            // Check file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                var errorAlert = $("<div>").addClass("alert alert-danger alert-dismissible fade show").attr("role", "alert");
                errorAlert.html("<i class=\\"fas fa-exclamation-circle\\"></i> File size must be less than 5MB<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
                $("#addStatementModal .modal-body").prepend(errorAlert);
                $("#scanned_copy").val("");
                $(".custom-file-label").html("Choose file...");
                return false;
            }
        }
        
        // Check verification checkboxes
        let verified = $("#verified").is(":checked");
        let signed = $("#signed").is(":checked");
        
        if (!verified && !signed) {
            e.preventDefault();
            
            // Scroll to verification section
            $(".card-warning")[0].scrollIntoView({ behavior: "smooth", block: "center" });
            
            // Highlight the verification card
            $(".card-warning").addClass("border-danger").css("box-shadow", "0 0 20px rgba(220, 53, 69, 0.5)");
            setTimeout(function() {
                $(".card-warning").removeClass("border-danger").css("box-shadow", "");
            }, 3000);
            
            return false;
        }
    });
});

function selectSuspect(id, name, ghanaCard, phone) {
    $("#suspect_person_id").val(id);
    $("#suspect_display").html(`
        <div class="media">
            <div class="media-body">
                <h5 class="mt-0">${name}</h5>
                <p class="mb-0">
                    <i class="fas fa-id-card text-primary"></i> <strong>Ghana Card:</strong> ${ghanaCard}<br>
                    <i class="fas fa-phone text-success"></i> <strong>Phone:</strong> ${phone}
                </p>
            </div>
        </div>
    `);
    $("#selected_suspect_info").show();
    $("#suspect_search").val("");
    $("#suspect_results").hide();
}

function clearSuspectSelection() {
    $("#suspect_person_id").val("");
    $("#selected_suspect_info").hide();
    $("#suspect_search").val("").focus();
}

function switchStatementMode(mode) {
    if (mode === "type") {
        $("#typeMode").show();
        $("#uploadMode").hide();
        $("#typeBtn").addClass("active").removeClass("btn-outline-primary").addClass("btn-primary");
        $("#uploadBtn").removeClass("active").removeClass("btn-primary").addClass("btn-outline-primary");
        $("#statement_text").prop("required", true);
        $("#scanned_copy").prop("required", false);
    } else {
        $("#typeMode").hide();
        $("#uploadMode").show();
        $("#uploadBtn").addClass("active").removeClass("btn-outline-primary").addClass("btn-primary");
        $("#typeBtn").removeClass("active").removeClass("btn-primary").addClass("btn-outline-primary");
        $("#statement_text").prop("required", false);
        $("#scanned_copy").prop("required", true);
    }
}

function saveSuspectPerson() {
    const formData = $("#suspectPersonForm").serialize();
    
    $.ajax({
        url: "' . url('/persons/ajax-create') . '",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                var ghanaCard = response.person.ghana_card_number || "N/A";
                var phone = response.person.phone_number || response.person.contact || "N/A";
                selectSuspect(response.person.id, response.person.full_name, ghanaCard, phone);
                $("#suspectPersonModal").modal("hide");
                $("#suspectPersonForm")[0].reset();
                
                var alertDiv = $("<div>").addClass("alert alert-success alert-dismissible fade show").attr("role", "alert");
                alertDiv.html("<i class=\"fas fa-check-circle\"></i> Person registered successfully and selected as suspect!<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>");
                $("#addSuspectModal .modal-body").prepend(alertDiv);
            } else {
                alert("Error: " + (response.message || "Failed to register person"));
            }
        },
        error: function() {
            alert("Failed to register person. Please try again.");
        }
    });
}

// Witness search
let witnessSearchTimeout;
$("#witness_search").on("keyup", function() {
    clearTimeout(witnessSearchTimeout);
    let keyword = $(this).val().trim();
    
    if (keyword.length < 2) {
        $("#witness_results").hide();
        return;
    }
    
    var loadingDiv = $("<div>").addClass("list-group-item");
    loadingDiv.html("<i class=\"fas fa-spinner fa-spin\"></i> Searching...");
    $("#witness_results").empty().append(loadingDiv).show();
    
    witnessSearchTimeout = setTimeout(function() {
        $.ajax({
            url: "' . url('/persons/search') . '",
            data: { q: keyword },
            success: function(response) {
                $("#witness_results").empty();
                
                if (response.success && response.persons.length > 0) {
                    response.persons.forEach(function(person) {
                        var ghanaCard = person.ghana_card_number || "N/A";
                        var phoneNumber = person.phone_number || "N/A";
                        
                        var item = $("<a>")
                            .attr("href", "#")
                            .addClass("list-group-item list-group-item-action witness-select-item")
                            .data("id", person.id)
                            .data("name", person.full_name)
                            .data("ghana", ghanaCard)
                            .data("phone", phoneNumber);
                        
                        var content = $("<div>").addClass("d-flex justify-content-between");
                        var leftDiv = $("<div>");
                        leftDiv.html("<strong>" + person.full_name + "</strong><br><small class=\"text-muted\"><i class=\"fas fa-id-card\"></i> " + ghanaCard + " | <i class=\"fas fa-phone\"></i> " + phoneNumber + "</small>");
                        
                        var rightDiv = $("<div>").addClass("text-right");
                        rightDiv.html("<i class=\"fas fa-arrow-right text-success\"></i>");
                        
                        content.append(leftDiv).append(rightDiv);
                        item.append(content);
                        $("#witness_results").append(item);
                    });
                    $("#witness_results").show();
                } else {
                    var noResultDiv = $("<div>").addClass("list-group-item text-center");
                    noResultDiv.html("<i class=\"fas fa-user-slash\"></i> No persons found. Click \"New Person\" to register.");
                    $("#witness_results").append(noResultDiv).show();
                }
            },
            error: function() {
                var errorDiv = $("<div>").addClass("list-group-item text-danger");
                errorDiv.html("<i class=\"fas fa-exclamation-triangle\"></i> Search failed. Please try again.");
                $("#witness_results").empty().append(errorDiv).show();
            }
        });
    }, 300);
});

// Handle witness selection with event delegation
$(document).on("click", ".witness-select-item", function(e) {
    e.preventDefault();
    const id = $(this).data("id");
    const name = $(this).data("name");
    const ghanaCard = $(this).data("ghana");
    const phone = $(this).data("phone");
    selectWitness(id, name, ghanaCard, phone);
});

// Witness form validation
$("#addWitnessForm").on("submit", function(e) {
    const personId = $("#witness_person_id").val();
    if (!personId) {
        e.preventDefault();
        alert("Please select a person from the search results");
        $("#witness_search").focus();
        return false;
    }
});

function selectWitness(id, name, ghanaCard, phone) {
    $("#witness_person_id").val(id);
    $("#witness_display").html(`
        <div class="media">
            <div class="media-body">
                <h5 class="mt-0">${name}</h5>
                <p class="mb-0">
                    <i class="fas fa-id-card text-primary"></i> <strong>Ghana Card:</strong> ${ghanaCard}<br>
                    <i class="fas fa-phone text-success"></i> <strong>Phone:</strong> ${phone}
                </p>
            </div>
        </div>
    `);
    $("#selected_witness_info").show();
    $("#witness_search").val("");
    $("#witness_results").hide();
}

function clearWitnessSelection() {
    $("#witness_person_id").val("");
    $("#selected_witness_info").hide();
    $("#witness_search").val("").focus();
}

function saveWitnessPerson() {
    const formData = $("#witnessPersonForm").serialize();
    
    // Clear previous alerts
    $("#witnessPersonModal .alert").remove();
    
    $.ajax({
        url: "' . url('/persons/ajax-create') . '",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                const ghanaCard = response.person.ghana_card_number || "N/A";
                const phone = response.person.phone_number || response.person.contact || "N/A";
                selectWitness(response.person.id, response.person.full_name, ghanaCard, phone);
                $("#witnessPersonModal").modal("hide");
                $("#witnessPersonForm")[0].reset();
                
                // Show success message in witness modal
                var successAlert = $("<div>").addClass("alert alert-success alert-dismissible fade show").attr("role", "alert");
                successAlert.html("<i class=\\"fas fa-check-circle\\"></i> Person registered successfully and selected as witness!<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
                $("#addWitnessModal .modal-body").prepend(successAlert);
            } else {
                var errorAlert = $("<div>").addClass("alert alert-danger alert-dismissible fade show").attr("role", "alert");
                errorAlert.html("<i class=\\"fas fa-exclamation-circle\\"></i> " + (response.message || "Failed to register person") + "<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
                $("#witnessPersonModal .modal-body").prepend(errorAlert);
            }
        },
        error: function(xhr) {
            var errorMsg = "Failed to register person. Please try again.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            var errorAlert = $("<div>").addClass("alert alert-danger alert-dismissible fade show").attr("role", "alert");
            errorAlert.html("<i class=\\"fas fa-exclamation-circle\\"></i> " + errorMsg + "<button type=\\"button\\" class=\\"close\\" data-dismiss=\\"alert\\">&times;</button>");
            $("#witnessPersonModal .modal-body").prepend(errorAlert);
        }
    });
}

function removeWitness(witnessId, witnessName) {
    if (confirm(`Are you sure you want to remove "${witnessName}" from this case?\n\nThis action cannot be undone.`)) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/cases/' . $case['id'] . '/witnesses/') . '" + witnessId + "/remove";
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteEvidence(evidenceId, evidenceNumber) {
    if (confirm(`Are you sure you want to delete evidence "${evidenceNumber}"?\n\nThis will permanently delete the file and cannot be undone.`)) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/cases/' . $case['id'] . '/evidence/') . '" + evidenceId + "/delete";
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// View full statement details
function viewStatement(statementId) {
    $("#viewStatementModal").modal("show");
    
    $.ajax({
        url: "' . url('/cases/' . $case['id'] . '/statements/') . '" + statementId,
        method: "GET",
        success: function(response) {
            if (response.success) {
                const stmt = response.statement;
                const status = stmt.status || "active";
                
                let statusBadge = "";
                if (status === "cancelled") {
                    statusBadge = "<span class=\"badge badge-danger\">Cancelled</span>";
                } else if (status === "superseded") {
                    statusBadge = "<span class=\"badge badge-warning\">Re-written</span>";
                } else {
                    statusBadge = "<span class=\"badge badge-success\">Active</span>";
                }
                
                let html = "<div class=\"statement-details\">";
                html += "<div class=\"row mb-3\">";
                html += "<div class=\"col-md-6\"><strong>Type:</strong> " + stmt.statement_type + " Statement</div>";
                html += "<div class=\"col-md-6\"><strong>Status:</strong> " + statusBadge + "</div>";
                html += "</div>";
                
                html += "<div class=\"card card-outline card-info mb-3\">";
                html += "<div class=\"card-header\"><h6 class=\"mb-0\"><i class=\"fas fa-user\"></i> Statement Owner</h6></div>";
                html += "<div class=\"card-body\">";
                html += "<div class=\"row\">";
                html += "<div class=\"col-md-6\"><strong>Name:</strong> " + (stmt.person_name || "N/A") + "</div>";
                html += "<div class=\"col-md-6\"><strong>Contact:</strong> " + (stmt.person_contact || "N/A") + "</div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
                
                if (stmt.version > 1) {
                    html += "<div class=\"alert alert-info\"><i class=\"fas fa-info-circle\"></i> This is version " + stmt.version + " of this statement</div>";
                }
                
                html += "<div class=\"card card-outline card-secondary mb-3\">";
                html += "<div class=\"card-header\"><h5 class=\"mb-0\"><i class=\"fas fa-file-alt\"></i> Statement Content</h5></div>";
                html += "<div class=\"card-body\"><p style=\"white-space: pre-wrap;\">" + stmt.statement_text + "</p></div>";
                html += "</div>";
                
                html += "<div class=\"row\">";
                html += "<div class=\"col-md-6\"><strong>Recorded By:</strong> " + stmt.recorded_by_name + "</div>";
                html += "<div class=\"col-md-6\"><strong>Date:</strong> " + stmt.recorded_at + "</div>";
                html += "</div>";
                
                if (status === "cancelled") {
                    html += "<div class=\"alert alert-danger mt-3\">";
                    html += "<strong><i class=\"fas fa-ban\"></i> Cancellation Details:</strong><br>";
                    html += "<strong>Cancelled By:</strong> " + (stmt.cancelled_by_name || "N/A") + "<br>";
                    html += "<strong>Cancelled At:</strong> " + (stmt.cancelled_at || "N/A") + "<br>";
                    html += "<strong>Reason:</strong> " + (stmt.cancellation_reason || "N/A");
                    html += "</div>";
                } else if (status === "superseded" && stmt.child_statement_id) {
                    html += "<div class=\"alert alert-warning alert-dismissible fade show mt-3\" role=\"alert\">";
                    html += "<strong><i class=\"fas fa-info-circle\"></i> This statement has been re-written.</strong><br>";
                    html += "<button type=\"button\" class=\"btn btn-sm btn-primary mt-2\" onclick=\"viewStatement(" + stmt.child_statement_id + ")\">";
                    html += "<i class=\"fas fa-arrow-right\"></i> View Newer Version";
                    html += "</button>";
                    html += "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">";
                    html += "<span aria-hidden=\"true\">&times;</span>";
                    html += "</button>";
                    html += "</div>";
                }
                
                html += "</div>";
                
                $("#statementModalBody").html(html);
                
                // Update footer with action buttons
                let footerHtml = "<button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>";
                
                if (status === "active") {
                    footerHtml += "<button type=\"button\" class=\"btn btn-warning\" onclick=\"rewriteStatement(" + statementId + ")\"><i class=\"fas fa-edit\"></i> Rewrite Statement</button>";
                    footerHtml += "<button type=\"button\" class=\"btn btn-danger\" onclick=\"openCancelStatement(" + statementId + ")\"><i class=\"fas fa-ban\"></i> Cancel Statement</button>";
                }
                
                $("#statementModalFooter").html(footerHtml);
            }
        },
        error: function() {
            $("#statementModalBody").html("<div class=\"alert alert-danger\"><i class=\"fas fa-exclamation-circle\"></i> Failed to load statement details</div>");
        }
    });
}

// Open cancel statement modal
function openCancelStatement(statementId) {
    $("#viewStatementModal").modal("hide");
    $("#cancel_statement_id").val(statementId);
    $("#cancelStatementModal").modal("show");
}

// Handle cancel statement form submission
$("#cancelStatementForm").on("submit", function(e) {
    e.preventDefault();
    
    const statementId = $("#cancel_statement_id").val();
    const formData = $(this).serialize();
    
    $.ajax({
        url: "' . url('/cases/' . $case['id'] . '/statements/') . '" + statementId + "/cancel",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                $("#cancelStatementModal").modal("hide");
                location.reload();
            } else {
                alert("Error: " + (response.message || "Failed to cancel statement"));
            }
        },
        error: function() {
            alert("Failed to cancel statement. Please try again.");
        }
    });
});

// Rewrite statement (opens new statement modal with pre-filled data)
function rewriteStatement(statementId) {
    // Close view modal and wait for it to fully close before opening add modal
    $("#viewStatementModal").modal("hide");
    
    // Wait for modal to fully close (Bootstrap animation)
    $("#viewStatementModal").on("hidden.bs.modal", function(e) {
        // Remove this handler to prevent multiple triggers
        $(this).off("hidden.bs.modal");
        
        $.ajax({
            url: "' . url('/cases/' . $case['id'] . '/statements/') . '" + statementId,
            method: "GET",
            success: function(response) {
                if (response.success) {
                    const stmt = response.statement;
                    
                    // Clear any previous alerts and hidden fields
                    $("#addStatementModal .alert").remove();
                    $("input[name=parent_statement_id]").remove();
                    $("input[name=statement_type_override]").remove();
                    
                    // Set the statement type and disable it (read-only)
                    $("#statement_type").val(stmt.statement_type).prop("disabled", true).trigger("change");
                    
                    // Wait for person dropdown to populate, then disable it
                    setTimeout(function() {
                        $("#person_select").prop("disabled", true);
                        
                        // Set the appropriate person ID based on statement type
                        if (stmt.statement_type === "Complainant") {
                            $("#complainant_id").val(stmt.complainant_id);
                        } else if (stmt.statement_type === "Suspect") {
                            $("#suspect_id").val(stmt.suspect_id);
                            $("#person_select").val(stmt.suspect_id);
                        } else if (stmt.statement_type === "Witness") {
                            $("#witness_id").val(stmt.witness_id);
                            $("#person_select").val(stmt.witness_id);
                        }
                    }, 300);
                    
                    // Pre-fill the statement text
                    $("#statement_text").val(stmt.statement_text);
                    
                    // Store parent statement ID for versioning
                    $("#addStatementForm").append("<input type=\"hidden\" name=\"parent_statement_id\" value=\"" + statementId + "\">");
                    
                    // Add hidden field to preserve statement type since it is disabled
                    $("#addStatementForm").append("<input type=\"hidden\" name=\"statement_type_override\" value=\"" + stmt.statement_type + "\">");
                    
                    // Show info message at the top
                    const infoAlert = $("<div>").addClass("alert alert-warning alert-dismissible fade show").attr("role", "alert");
                    infoAlert.html("<i class=\"fas fa-exclamation-triangle\"></i> <strong>Rewriting Statement:</strong> You are creating a new version of this statement. The statement type and person cannot be changed. Only edit the statement text below. The previous version will be marked as re-written.<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>");
                    $("#addStatementModal .modal-body").prepend(infoAlert);
                    
                    // Open the add statement modal after a short delay
                    setTimeout(function() {
                        $("#addStatementModal").modal("show");
                        
                        // Focus on statement text after modal opens
                        $("#addStatementModal").on("shown.bs.modal", function() {
                            $(this).off("shown.bs.modal");
                            $("#statement_text").focus();
                        });
                    }, 300);
                }
            }
        });
    });
}

// Reset statement form when modal is closed
$("#addStatementModal").on("hidden.bs.modal", function() {
    // Re-enable fields
    $("#statement_type").prop("disabled", false);
    $("#person_select").prop("disabled", false);
    
    // Remove rewrite-specific elements
    $("input[name=parent_statement_id]").remove();
    $("input[name=statement_type_override]").remove();
    $("#addStatementModal .alert").remove();
    
    // Reset form
    $("#addStatementForm")[0].reset();
});

// Initialize suspect modal when it opens
$("#addSuspectModal").on(\'shown.bs.modal\', function() {
    // Handle suspect type toggle
    $(\'input[name="suspect_type"]\').off(\'change\').on(\'change\', function() {
        toggleSuspectType($(this).val());
    });
    
    // Initialize to known section by default
    toggleSuspectType(\'known\');
});

function toggleSuspectType(type) {
    if (type === "known") {
        $("#knownPersonSection").show();
        $("#unknownSuspectSection").hide();
        $("#suspect_person_id").prop("required", true);
        $("#unknown_description").prop("required", false);
        $("#person_required").show();
        $("#desc_required").hide();
        $("#suspect_status").val("Identified");
    } else {
        $("#knownPersonSection").hide();
        $("#unknownSuspectSection").show();
        $("#suspect_person_id").prop("required", false);
        $("#unknown_description").prop("required", true);
        $("#person_required").hide();
        $("#desc_required").show();
        $("#suspect_status").val("At Large");
        // Clear person selection
        $("#suspect_person_id").val("");
        $("#selected_suspect_info").hide();
    }
}

// Update person dropdown based on statement type
function updatePersonDropdown() {
    const statementType = $("#statement_type").val();
    const personSelect = $("#person_select");
    
    // Clear previous selections
    $("#complainant_id").val("");
    $("#suspect_id").val("");
    $("#witness_id").val("");
    
    personSelect.html("<option value=\"\">Loading...</option>");
    
    if (statementType === "Complainant") {
        personSelect.html("<option value=\"' . ($complainant['id'] ?? '') . '\">' . sanitize($complainant['full_name'] ?? 'Complainant') . '</option>");
        personSelect.on("change", function() {
            $("#complainant_id").val($(this).val());
        });
        personSelect.trigger("change");
    } else if (statementType === "Suspect") {
        let html = "<option value=\"\">Select Suspect</option>";
        // Only include known suspects (those with person_id) - unknown suspects cannot give statements
        const suspectData = ' . json_encode($suspectDataForJS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
        if (Array.isArray(suspectData)) {
            suspectData.forEach(function(suspect) {
                html += `<option value="${suspect.id}">${suspect.name}</option>`;
            });
        }
        personSelect.html(html);
        
        // Show message if no known suspects available
        if (html === "<option value=\"\">Select Suspect</option>") {
            personSelect.html("<option value=\"\">No known suspects available</option>");
        }
        
        personSelect.on("change", function() {
            $("#suspect_id").val($(this).val());
        });
    } else if (statementType === "Witness") {
        let html = "<option value=\"\">Select Witness</option>";
        const witnessData = ' . json_encode($witnessDataForJS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
        if (Array.isArray(witnessData)) {
            witnessData.forEach(function(witness) {
                const witnessId = witness.id || witness.witness_id;
                html += `<option value="${witnessId}">${witness.name}</option>`;
            });
        }
        personSelect.html(html);
        personSelect.on("change", function() {
            $("#witness_id").val($(this).val());
        });
    } else {
        personSelect.html("<option value=\"\">Select statement type first</option>");
    }
}

// Open statement modal with pre-selected person
function openStatementForPerson(type, personId, personName) {
    // Clear any previous state
    $("#addStatementModal .alert").remove();
    $("input[name=parent_statement_id]").remove();
    $("input[name=statement_type_override]").remove();
    
    // Reset form
    $("#addStatementForm")[0].reset();
    
    // Re-enable fields (in case they were disabled from rewrite)
    $("#statement_type").prop("disabled", false);
    $("#person_select").prop("disabled", false);
    
    // Set statement type
    $("#statement_type").val(type).trigger("change");
    
    // Wait for dropdown to populate, then select the person
    setTimeout(function() {
        $("#person_select").val(personId).trigger("change");
        
        // Show info message
        const infoAlert = $("<div>").addClass("alert alert-info alert-dismissible fade show").attr("role", "alert");
        infoAlert.html("<i class=\"fas fa-info-circle\"></i> <strong>Recording statement for:</strong> " + personName + " (" + type + ")<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>");
        $("#addStatementModal .modal-body").prepend(infoAlert);
    }, 500);
    
    // Open modal
    $("#addStatementModal").modal("show");
}

function switchStatementMode(mode) {
    if (mode === "type") {
        $("#typeMode").show();
        $("#uploadMode").hide();
        $("#typeBtn").addClass("active").removeClass("btn-outline-primary").addClass("btn-primary");
        $("#uploadBtn").removeClass("active").removeClass("btn-primary").addClass("btn-outline-primary");
        $("#statement_text").prop("required", true);
        $("#scanned_copy").prop("required", false);
    } else {
        $("#typeMode").hide();
        $("#uploadMode").show();
        $("#uploadBtn").addClass("active").removeClass("btn-outline-primary").addClass("btn-primary");
        $("#typeBtn").removeClass("active").removeClass("btn-primary").addClass("btn-outline-primary");
        $("#statement_text").prop("required", false);
        $("#scanned_copy").prop("required", true);
    }
}

// Form validation for suspect form
$("#addSuspectForm").on("submit", function(e) {
    const suspectType = $("input[name=suspect_type]:checked").val();
    
    if (suspectType === "known") {
        const personId = $("#suspect_person_id").val();
        if (!personId) {
            e.preventDefault();
            alert("Please select a person or switch to Unknown Suspect");
            $("#suspect_search").focus();
            return false;
        }
    } else {
        const description = $("#unknown_description").val().trim();
        if (!description) {
            e.preventDefault();
            alert("Please provide a description for the unknown suspect");
            $("#unknown_description").focus();
            return false;
        }
    }
});

function openStatusModal(suspectId, currentStatus, suspectName) {
    $("#modal-suspect-id").val(suspectId);
    $("#modal-suspect-name").text(suspectName);
    $("#modal-current-status").val(currentStatus);
    $("#modal-new-status").val(currentStatus);
    $("#statusUpdateModal").modal("show");
}

// View person relationships
function viewPersonRelationships(personId, personName) {
    $("#relationship_person_name").text(personName);
    $("#relationships_loading").show();
    $("#relationships_content").hide().empty();
    $("#personRelationshipsModal").modal("show");
    
    // Fetch relationships via AJAX
    $.ajax({
        url: "' . url('/persons/') . '" + personId,
        method: "GET",
        success: function(response) {
            // Parse the HTML response to extract relationships data
            const parser = new DOMParser();
            const doc = parser.parseFromString(response, "text/html");
            const relationshipsTable = doc.querySelector(".card:has(.fa-users) table");
            
            $("#relationships_loading").hide();
            
            if (relationshipsTable) {
                const tableHtml = relationshipsTable.outerHTML;
                $("#relationships_content").html(tableHtml).show();
            } else {
                $("#relationships_content").html("<p class=\"text-muted text-center py-4\"><i class=\"fas fa-info-circle\"></i> No relationships found for this person</p>").show();
            }
        },
        error: function() {
            $("#relationships_loading").hide();
            $("#relationships_content").html("<p class=\"text-danger text-center py-4\"><i class=\"fas fa-exclamation-circle\"></i> Failed to load relationships. Please try again.</p>").show();
        }
    });
}

function updateSuspectStatus() {
    const suspectId = $("#modal-suspect-id").val();
    const caseId = $("#modal-case-id").val();
    const newStatus = $("#modal-new-status").val();
    const currentStatus = $("#modal-current-status").val();
    
    if (newStatus === currentStatus) {
        alert("Please select a different status");
        return;
    }
    
    $.ajax({
        url: "' . url('/cases/') . '" + caseId + "/suspects/" + suspectId + "/status",
        method: "POST",
        data: {
            csrf_token: "' . csrf_token() . '",
            status: newStatus
        },
        success: function(response) {
            if (response.success) {
                $("#statusUpdateModal").modal("hide");
                
                // Show success message
                const alertHtml = `<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 70px; right: 20px; z-index: 9999;">
                    <i class="fas fa-check-circle"></i> Status updated to ${newStatus}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`;
                $("body").append(alertHtml);
                setTimeout(function() {
                    $(".alert").remove();
                }, 2000);
                
                // Reload page after short delay to show updated status
                setTimeout(function() {
                    location.reload();
                }, 2500);
            } else {
                alert("Failed to update status: " + response.message);
            }
        },
        error: function() {
            alert("Failed to update status. Please try again.");
        }
    });
}

function removeSuspect(suspectId, suspectName) {
    if (confirm(`Are you sure you want to remove "${suspectName}" from this case?\n\nThis action cannot be undone.`)) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/cases/' . $case['id'] . '/suspects/') . '" + suspectId + "/remove";
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function editSuspect(suspectId, isUnknown) {
    if (isUnknown) {
        if (confirm("This suspect is currently unidentified.\n\nDo you want to upgrade them to a known person?\n\nYou will be able to link them to a person record.")) {
            window.location.href = "' . url('/cases/' . $case['id'] . '/suspects/') . '" + suspectId + "/upgrade";
        }
    } else {
        window.location.href = "' . url('/cases/' . $case['id'] . '/suspects/') . '" + suspectId + "/edit";
    }
}

// Delete crime from case
function deleteCrime(crimeId, crimeName) {
    if (confirm(`Are you sure you want to remove "${crimeName}" from this case?\n\nThis action cannot be undone.`)) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/cases/' . $case['id'] . '/crimes/') . '" + crimeId + "/delete";
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Reassign officer
function reassignOfficer(assignmentId, officerName) {
    document.getElementById("reassign_assignment_id").value = assignmentId;
    document.getElementById("reassign_current_officer").textContent = officerName;
    document.getElementById("reassign_new_officer").value = "";
    $("#reassignOfficerModal").modal("show");
}

// Handle reassign officer form submission
document.getElementById("reassignOfficerForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    // Remove any existing error/success messages (but keep info banner)
    $("#reassignOfficerModal .alert-danger, #reassignOfficerModal .alert-success").remove();
    
    const assignmentId = document.getElementById("reassign_assignment_id").value;
    const formData = new FormData(this);
    const submitBtn = $(this).find("button[type=submit]");
    
    // Disable submit button
    submitBtn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Reassigning...");
    
    fetch("' . url('/cases/' . $case['id'] . '/assignments/') . '" + assignmentId + "/reassign", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: new URLSearchParams({
            new_officer_id: formData.get("new_officer_id"),
            remarks: formData.get("remarks"),
            csrf_token: "' . csrf_token() . '"
        })
    })
    .then(response => {
        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers);
        return response.text().then(text => {
            console.log("Response text:", text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Failed to parse JSON:", e);
                throw new Error("Server returned invalid JSON: " + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log("Parsed data:", data);
        if (data.success) {
            // Show success message
            const successAlert = `<div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> ${data.message || "Officer reassigned successfully"}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>`;
            $("#reassignOfficerModal .modal-body").prepend(successAlert);
            
            // Reload after short delay
            setTimeout(function() {
                location.reload();
            }, 1500);
        } else {
            // Show error message
            const errorAlert = `<div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> ${data.message || "Failed to reassign officer"}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>`;
            $("#reassignOfficerModal .modal-body").prepend(errorAlert);
            
            // Re-enable submit button
            submitBtn.prop("disabled", false).html("<i class=\"fas fa-exchange-alt\"></i> Reassign Officer");
        }
    })
    .catch(err => {
        console.error("Failed to reassign officer:", err);
        
        // Show error message
        const errorAlert = `<div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> Failed to reassign officer. Please check your connection and try again.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>`;
        $("#reassignOfficerModal .modal-body").prepend(errorAlert);
        
        // Re-enable submit button
        submitBtn.prop("disabled", false).html("<i class=\"fas fa-exchange-alt\"></i> Reassign Officer");
    });
});

// Complete assignment
function completeAssignment(assignmentId) {
    if (confirm("Mark this assignment as completed?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/cases/' . $case['id'] . '/assignments/') . '" + assignmentId + "/complete";
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Load officers for arrest and charge modals
$(document).ready(function() {
    // Initialize Select2 when modals open
    $("#recordArrestModal, #fileChargesModal").on("shown.bs.modal", function() {
        var modal = $(this);
        
        // Initialize select2 for all selects in the modal
        modal.find(".select2").select2({
            dropdownParent: modal,
            width: "100%"
        });
        
        // Load officers
        $.ajax({
            url: "' . url('/api/officers/active') . '",
            method: "GET",
            dataType: "json",
            success: function(officers) {
                console.log("Officers loaded:", officers);
                
                var arrestSelect = $("#recordArrestModal select[name=arresting_officer_id]");
                var chargeSelect = $("#fileChargesModal select[name=charged_by]");
                
                arrestSelect.find("option:not(:first)").remove();
                chargeSelect.find("option:not(:first)").remove();
                
                if (officers && officers.length > 0) {
                    officers.forEach(function(officer) {
                        arrestSelect.append($("<option>").val(officer.id).text(officer.officer_name));
                        chargeSelect.append($("<option>").val(officer.id).text(officer.officer_name));
                    });
                    console.log("Added " + officers.length + " officers to dropdowns");
                } else {
                    console.warn("No officers returned from API");
                }
            },
            error: function(xhr, status, error) {
                console.error("Failed to load officers:", status, error);
                console.error("Response:", xhr.responseText);
            }
        });
    });
    
    // Reset forms when modals close
    $("#recordArrestModal, #fileChargesModal").on("hidden.bs.modal", function() {
        $(this).find("form")[0].reset();
        $(this).find(".select2").val(null).trigger("change");
        $("#warrantNumberGroup").hide();
    });
    
    // Arrest type change handler
    $(document).on("change", "#recordArrestModal select[name=arrest_type]", function() {
        if ($(this).val() === "With Warrant") {
            $("#warrantNumberGroup").show();
            $("#warrantNumberGroup input").attr("required", true);
        } else {
            $("#warrantNumberGroup").hide();
            $("#warrantNumberGroup input").attr("required", false).val("");
        }
    });
    
    // Arrest form submission - using click handler instead of submit
    $(document).on("click", "#arrestForm button[type=submit]", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var form = $("#arrestForm");
        
        // Basic validation
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }
        
        // Disable submit button
        $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Saving...");
        
        $.ajax({
            url: "' . url('/arrests/store') . '",
            method: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#recordArrestModal").modal("hide");
                    Swal.fire("Success", response.message, "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to record arrest", "error");
                    $("#arrestForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-save\"></i> Record Arrest");
                }
            },
            error: function(xhr) {
                var errorMsg = "Failed to record arrest";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire("Error", errorMsg, "error");
                $("#arrestForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-save\"></i> Record Arrest");
            }
        });
        
        return false;
    });
    
    // Charge form submission - using click handler
    $(document).on("click", "#chargeForm button[type=submit]", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var form = $("#chargeForm");
        
        // Basic validation
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }
        
        // Disable submit button
        $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Saving...");
        
        $.ajax({
            url: "' . url('/charges/store') . '",
            method: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#fileChargesModal").modal("hide");
                    Swal.fire("Success", response.message, "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to file charges", "error");
                    $("#chargeForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-gavel\"></i> File Charges");
                }
            },
            error: function(xhr) {
                var errorMsg = "Failed to file charges";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire("Error", errorMsg, "error");
                $("#chargeForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-gavel\"></i> File Charges");
            }
        });
        
        return false;
    });
    
    // Place in Custody button handler
    $(document).on("click", ".place-in-custody-btn", function() {
        var arrestId = $(this).data("arrest-id");
        var suspectId = $(this).data("suspect-id");
        var suspectName = $(this).data("suspect-name");
        
        // Populate modal fields
        $("#custody_arrest_id").val(arrestId);
        $("#custody_suspect_id").val(suspectId);
        $("#custody_suspect_name").text(suspectName);
        
        // Set default custody start time to now
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $("input[name=custody_start]").val(now.toISOString().slice(0,16));
        
        // Open modal
        $("#placeInCustodyModal").modal("show");
    });
    
    // Custody form submission
    $(document).on("click", "#custodyForm button[type=submit]", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var form = $("#custodyForm");
        
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }
        
        $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Saving...");
        
        $.ajax({
            url: "' . url('/custody/store') . '",
            method: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#placeInCustodyModal").modal("hide");
                    Swal.fire("Success", "Suspect placed in custody successfully", "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to place in custody", "error");
                    $("#custodyForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-lock\"></i> Place in Custody");
                }
            },
            error: function(xhr) {
                var errorMsg = "Failed to place in custody";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire("Error", errorMsg, "error");
                $("#custodyForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-lock\"></i> Place in Custody");
            }
        });
        
        return false;
    });
    
    // Record Arrest button handler (from suspects table)
    $(document).on("click", ".record-arrest-btn", function() {
        var suspectId = $(this).data("suspect-id");
        var suspectName = $(this).data("suspect-name");
        
        // Pre-populate modal fields
        $("#arrestForm select[name=suspect_id]").val(suspectId).trigger("change");
        $("#arrestForm input[name=arrest_date]").val(new Date().toISOString().split("T")[0]);
        
        // Open modal
        $("#recordArrestModal").modal("show");
    });
    
    // Grant Bail button handler (from arrests table)
    $(document).on("click", ".grant-bail-btn", function() {
        var arrestId = $(this).data("arrest-id");
        var suspectId = $(this).data("suspect-id");
        var suspectName = $(this).data("suspect-name");
        
        // Pre-populate modal fields
        $("#bailForm select[name=suspect_id]").val(suspectId).trigger("change");
        var today = new Date().toISOString().split("T")[0];
        $("#bailForm input[name=bail_date]").val(today);
        
        // Open modal
        $("#grantBailModal").modal("show");
    });
    
    // Bail status change handler - hide/show amount and conditions
    $(document).on("change", "#grantBailModal select[name=bail_status]", function() {
        if ($(this).val() === "Granted") {
            $("#bailAmountGroup, #bailConditionsGroup").show();
        } else {
            $("#bailAmountGroup, #bailConditionsGroup").hide();
            $("#bailAmountGroup input, #bailConditionsGroup textarea").val("");
        }
    });
    
    // Bail form submission
    $(document).on("click", "#bailForm button[type=submit]", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var form = $("#bailForm");
        
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }
        
        $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Saving...");
        
        $.ajax({
            url: "' . url('/bail/store') . '",
            method: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#grantBailModal").modal("hide");
                    Swal.fire("Success", "Bail record created successfully", "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to grant bail", "error");
                    $("#bailForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-gavel\"></i> Grant Bail");
                }
            },
            error: function(xhr) {
                var errorMsg = "Failed to grant bail";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire("Error", errorMsg, "error");
                $("#bailForm button[type=submit]").prop("disabled", false).html("<i class=\"fas fa-gavel\"></i> Grant Bail");
            }
        });
        
        return false;
    });
    
    // View Custody Details handler
    $(document).on("click", ".view-custody-btn", function() {
        var custodyId = $(this).data("custody-id");
        
        $("#viewCustodyModal").modal("show");
        $("#custodyDetailsContent").html("<div class=\"text-center py-4\"><i class=\"fas fa-spinner fa-spin fa-2x\"></i><p class=\"mt-2\">Loading custody details...</p></div>");
        
        // Fetch custody record data and build HTML
        var custodyRecord = null;
        ' . json_encode($custody_records) . '.forEach(function(record) {
            if (record.id == custodyId) {
                custodyRecord = record;
            }
        });
        
        if (custodyRecord) {
            var html = "<div class=\"p-3\">";
            html += "<dl class=\"row\">";
            html += "<dt class=\"col-sm-4\">Suspect:</dt><dd class=\"col-sm-8\">" + (custodyRecord.first_name || "") + " " + (custodyRecord.last_name || "") + "</dd>";
            html += "<dt class=\"col-sm-4\">Custody Location:</dt><dd class=\"col-sm-8\">" + (custodyRecord.custody_location_name || "N/A") + "</dd>";
            html += "<dt class=\"col-sm-4\">Custody Start:</dt><dd class=\"col-sm-8\">" + (custodyRecord.custody_start || "N/A") + "</dd>";
            html += "<dt class=\"col-sm-4\">Custody End:</dt><dd class=\"col-sm-8\">" + (custodyRecord.custody_end || "Ongoing") + "</dd>";
            html += "<dt class=\"col-sm-4\">Status:</dt><dd class=\"col-sm-8\"><span class=\"badge badge-" + (custodyRecord.custody_status === "In Custody" ? "danger" : "success") + "\">" + (custodyRecord.custody_status || "N/A") + "</span></dd>";
            html += "<dt class=\"col-sm-4\">Reason:</dt><dd class=\"col-sm-8\">" + (custodyRecord.reason || "N/A") + "</dd>";
            html += "</dl></div>";
            $("#custodyDetailsContent").html(html);
        } else {
            $("#custodyDetailsContent").html("<p class=\"text-danger\">Custody record not found</p>");
        }
    });
    
    // View Bail Details handler
    $(document).on("click", ".view-bail-btn", function() {
        var bailId = $(this).data("bail-id");
        
        $("#viewBailModal").modal("show");
        $("#bailDetailsContent").html("<div class=\"text-center py-4\"><i class=\"fas fa-spinner fa-spin fa-2x\"></i><p class=\"mt-2\">Loading bail details...</p></div>");
        $("#revokeBailFromModal").hide().data("bail-id", bailId);
        
        // Fetch bail record data and build HTML
        var bailRecord = null;
        ' . json_encode($bail_records) . '.forEach(function(record) {
            if (record.id == bailId) {
                bailRecord = record;
            }
        });
        
        if (bailRecord) {
            var html = "<div class=\"p-3\">";
            html += "<dl class=\"row\">";
            html += "<dt class=\"col-sm-4\">Suspect:</dt><dd class=\"col-sm-8\">" + (bailRecord.first_name || "") + " " + (bailRecord.last_name || "") + "</dd>";
            html += "<dt class=\"col-sm-4\">Bail Status:</dt><dd class=\"col-sm-8\"><span class=\"badge badge-" + (bailRecord.bail_status === "Granted" ? "success" : bailRecord.bail_status === "Denied" ? "danger" : "dark") + "\">" + (bailRecord.bail_status || "N/A") + "</span></dd>";
            html += "<dt class=\"col-sm-4\">Bail Amount:</dt><dd class=\"col-sm-8\">" + (bailRecord.bail_amount ? "GH₵ " + parseFloat(bailRecord.bail_amount).toLocaleString() : "N/A") + "</dd>";
            html += "<dt class=\"col-sm-4\">Bail Conditions:</dt><dd class=\"col-sm-8\">" + (bailRecord.bail_conditions || "None") + "</dd>";
            html += "<dt class=\"col-sm-4\">Bail Date:</dt><dd class=\"col-sm-8\">" + (bailRecord.bail_date || "N/A") + "</dd>";
            html += "<dt class=\"col-sm-4\">Approved By:</dt><dd class=\"col-sm-8\">" + (bailRecord.approved_by_name || "N/A") + "</dd>";
            html += "</dl></div>";
            $("#bailDetailsContent").html(html);
            
            // Show revoke button if bail is granted
            if (bailRecord.bail_status === "Granted") {
                $("#revokeBailFromModal").show();
            }
        } else {
            $("#bailDetailsContent").html("<p class=\"text-danger\">Bail record not found</p>");
        }
    });
    
    // Revoke bail from modal
    $(document).on("click", "#revokeBailFromModal", function() {
        var bailId = $(this).data("bail-id");
        
        // Show revoke reason modal
        $("#revokeBailReasonModal").modal("show");
        $("#revokeBailReasonModal").data("bail-id", bailId);
        $("#revocationReason").val("");
    });
    
    // Submit revoke bail with reason
    $(document).on("click", "#submitRevokeBail", function() {
        var bailId = $("#revokeBailReasonModal").data("bail-id");
        var reason = $("#revocationReason").val().trim();
        
        if (!reason) {
            Swal.fire("Error", "Please enter a reason for revoking bail", "error");
            return;
        }
        
        $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Revoking...");
        
        $.ajax({
            url: "' . url('/bail/revoke') . '/" + bailId,
            method: "POST",
            data: {
                csrf_token: $("input[name=csrf_token]").val(),
                reason: reason
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#revokeBailReasonModal").modal("hide");
                    $("#viewBailModal").modal("hide");
                    Swal.fire("Success", "Bail revoked successfully", "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to revoke bail", "error");
                    $("#submitRevokeBail").prop("disabled", false).html("<i class=\"fas fa-ban\"></i> Revoke Bail");
                }
            },
            error: function(xhr) {
                Swal.fire("Error", "Failed to revoke bail", "error");
                $("#submitRevokeBail").prop("disabled", false).html("<i class=\"fas fa-ban\"></i> Revoke Bail");
            }
        });
    });
});
</script>
';

$content .= '
<!-- Record Arrest Modal -->
<div class="modal fade" id="recordArrestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-handcuffs"></i> Record Arrest</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="arrestForm" onsubmit="return false;">
                ' . csrf_field() . '
                <input type="hidden" name="case_id" value="' . $case['id'] . '">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Suspect <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="suspect_id" required style="width: 100%;">
                            <option value="">-- Select Suspect --</option>';

// Get list of already arrested suspect IDs
$arrestedSuspectIds = array_column($arrests, 'suspect_id');

foreach ($suspects as $s) {
    // Skip suspects who are already arrested
    if (in_array($s['id'], $arrestedSuspectIds)) {
        continue;
    }
    
    $suspectName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
    $content .= '
                            <option value="' . $s['id'] . '">' . sanitize($suspectName) . '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Arrest Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="arrest_date" required>
                    </div>

                    <div class="form-group">
                        <label>Arrest Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="arrest_location" required placeholder="Enter arrest location">
                    </div>

                    <div class="form-group">
                        <label>Arresting Officer <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="arresting_officer_id" required style="width: 100%;">
                            <option value="">Select Officer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Arrest Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="arrest_type" required>
                            <option value="With Warrant">With Warrant</option>
                            <option value="Without Warrant">Without Warrant</option>
                        </select>
                    </div>

                    <div class="form-group" id="warrantNumberGroup" style="display:none;">
                        <label>Warrant Number</label>
                        <input type="text" class="form-control" name="warrant_number" placeholder="Enter warrant number">
                    </div>

                    <div class="form-group">
                        <label>Reason for Arrest <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="4" required placeholder="Enter reason for arrest"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Record Arrest</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- File Charges Modal -->
<div class="modal fade" id="fileChargesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title"><i class="fas fa-gavel"></i> File Charges</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="chargeForm" onsubmit="return false;">
                ' . csrf_field() . '
                <input type="hidden" name="case_id" value="' . $case['id'] . '">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Suspect <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="suspect_id" required style="width: 100%;">';

foreach ($suspects as $s) {
    $suspectName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
    $content .= '
                            <option value="' . $s['id'] . '">' . sanitize($suspectName) . '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Offence Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="offence_name" required placeholder="Enter offence name">
                    </div>

                    <div class="form-group">
                        <label>Law Section</label>
                        <input type="text" class="form-control" name="law_section" placeholder="e.g., Section 123 of Criminal Code">
                    </div>

                    <div class="form-group">
                        <label>Charge Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="charge_date" required>
                    </div>

                    <div class="form-group">
                        <label>Charged By <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="charged_by" required style="width: 100%;">
                            <option value="">Select Officer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-gavel"></i> File Charges</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Place in Custody Modal -->
<div class="modal fade" id="placeInCustodyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-lock"></i> Place Suspect in Custody</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="custodyForm" onsubmit="return false;">
                ' . csrf_field() . '
                <input type="hidden" name="case_id" value="' . $case['id'] . '">
                <input type="hidden" name="arrest_id" id="custody_arrest_id">
                <input type="hidden" name="suspect_id" id="custody_suspect_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Recording custody for: <strong id="custody_suspect_name"></strong>
                    </div>
                    
                    <div class="form-group">
                        <label>Custody Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="custody_location" required placeholder="e.g., Station Cell Block A">
                    </div>

                    <div class="form-group">
                        <label>Custody Start Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="custody_start" required>
                    </div>

                    <div class="form-group">
                        <label>Personal Items Inventory</label>
                        <textarea class="form-control" name="personal_items" rows="4" placeholder="List items taken from suspect (e.g., Phone, Wallet, Keys, etc.)"></textarea>
                        <small class="form-text text-muted">Record all personal belongings taken into custody</small>
                    </div>

                    <div class="form-group">
                        <label>Reason for Custody <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="Enter reason for placing suspect in custody"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-lock"></i> Place in Custody</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Grant Bail Modal -->
<div class="modal fade" id="grantBailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-gavel"></i> Grant Bail</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="bailForm" onsubmit="return false;">
                ' . csrf_field() . '
                <input type="hidden" name="case_id" value="' . $case['id'] . '">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Suspect <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="suspect_id" required style="width: 100%;">
                            <option value="">-- Select Suspect --</option>';

// Only show arrested/charged suspects for bail
foreach ($suspects as $s) {
    $suspectName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
    $content .= '
                            <option value="' . $s['id'] . '">' . sanitize($suspectName) . '</option>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Bail Status <span class="text-danger">*</span></label>
                        <select class="form-control" name="bail_status" required>
                            <option value="Granted">Granted</option>
                            <option value="Denied">Denied</option>
                        </select>
                    </div>

                    <div class="form-group" id="bailAmountGroup">
                        <label>Bail Amount (GH₵)</label>
                        <input type="number" class="form-control" name="bail_amount" step="0.01" placeholder="Enter bail amount">
                    </div>

                    <div class="form-group" id="bailConditionsGroup">
                        <label>Bail Conditions</label>
                        <textarea class="form-control" name="bail_conditions" rows="4" placeholder="Enter bail conditions (e.g., surrender passport, report weekly, etc.)"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Bail Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="bail_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-gavel"></i> Grant Bail</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Custody Details Modal -->
<div class="modal fade" id="viewCustodyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-lock"></i> Custody Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="custodyDetailsContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading custody details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Bail Details Modal -->
<div class="modal fade" id="viewBailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-gavel"></i> Bail Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="bailDetailsContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading bail details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="revokeBailFromModal" style="display:none;">
                    <i class="fas fa-ban"></i> Revoke Bail
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Revoke Bail Reason Modal -->
<div class="modal fade" id="revokeBailReasonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-ban"></i> Revoke Bail - Enter Reason</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="revocationReason">Reason for Revoking Bail <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="revocationReason" rows="4" placeholder="Enter the reason for revoking bail..." required></textarea>
                    <small class="form-text text-muted">Please provide a detailed reason for revoking this bail.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitRevokeBail">
                    <i class="fas fa-ban"></i> Revoke Bail
                </button>
            </div>
        </div>
    </div>
</div>
';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number']]
];

include __DIR__ . '/../layouts/main.php';
?>
