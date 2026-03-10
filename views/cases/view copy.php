<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <!-- Case Info Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Case Information</h3>
            </div>
            <div class="card-body">
                <strong>Case Number</strong>
                <p class="text-muted">' . sanitize($case['case_number']) . '</p>

                <strong>Status</strong>
                <p><span class="badge badge-' . match($case['status']) {
                    'Open' => 'warning',
                    'Under Investigation' => 'info',
                    'Closed' => 'success',
                    default => 'secondary'
                } . '">' . sanitize($case['status']) . '</span></p>

                <strong>Priority</strong>
                <p><span class="badge badge-' . match($case['case_priority']) {
                    'High' => 'danger',
                    'Medium' => 'warning',
                    'Low' => 'info',
                    default => 'secondary'
                } . '">' . sanitize($case['case_priority']) . '</span></p>

                <strong>Case Type</strong>
                <p class="text-muted">' . sanitize($case['case_type']) . '</p>

                <strong>Registered</strong>
                <p class="text-muted">' . format_date($case['created_at'], 'd M Y H:i') . '</p>

                <strong>Incident Date</strong>
                <p class="text-muted">' . format_date($case['incident_date'] ?? null, 'd M Y H:i') . '</p>

                <strong>Location</strong>
                <p class="text-muted">' . sanitize($case['location'] ?? 'N/A') . '</p>
            </div>
            <div class="card-footer">
                <a href="' . url('/cases/' . $case['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Case
                </a>
            </div>
        </div>

        <!-- Complainant Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Complainant</h3>
            </div>
            <div class="card-body">';

if ($complainant) {
    $content .= '
                <strong>Name</strong>
                <p class="text-muted">' . sanitize($complainant['full_name']) . '</p>

                <strong>Contact</strong>
                <p class="text-muted">' . sanitize($complainant['contact'] ?? 'N/A') . '</p>

                <strong>Email</strong>
                <p class="text-muted">' . sanitize($complainant['email'] ?? 'N/A') . '</p>

                <strong>Address</strong>
                <p class="text-muted">' . sanitize($complainant['address'] ?? 'N/A') . '</p>

                <a href="' . url('/persons/' . $complainant['person_id']) . '" class="btn btn-sm btn-info">
                    <i class="fas fa-user"></i> View Profile
                </a>';
} else {
    $content .= '<p class="text-muted">No complainant information</p>';
}

$content .= '
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Case Description -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Case Description</h3>
            </div>
            <div class="card-body">
                <p>' . nl2br(sanitize($case['description'])) . '</p>
            </div>
        </div>

        <!-- Suspects -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-secret"></i> Suspects (' . count($suspects) . ')</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addSuspectModal">
                        <i class="fas fa-plus"></i> Add Suspect
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($suspects)) {
    $content .= '
                <table class="table table-striped">
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
        $isUnknown = empty($suspect['person_id']);
        $displayName = $isUnknown 
            ? '<span class="text-warning"><i class="fas fa-user-secret"></i> ' . sanitize($suspect['unknown_description'] ?? 'Unknown Suspect') . '</span>'
            : '<strong>' . sanitize($suspect['full_name']) . '</strong>';
        
        // Get status with fallback
        $status = !empty($suspect['current_status']) ? $suspect['current_status'] : 'Suspect';
        
        // Status badge color based on suspect status
        $statusBadge = match($status) {
            'Suspect' => 'primary',
            'Arrested' => 'warning',
            'Charged' => 'info',
            'Discharged' => 'secondary',
            'Acquitted' => 'success',
            'Convicted' => 'danger',
            'Released' => 'info',
            'Deceased' => 'dark',
            default => 'secondary'
        };
        
        $chatIcon = '';
        if (!$isUnknown) {
            $chatIcon = ' <button type="button" class="btn btn-xs btn-outline-primary ml-2" onclick="openStatementForPerson(\'Suspect\', ' . $suspect['id'] . ', \'' . addslashes($suspect['full_name']) . '\')" title="Add Statement">
                            <i class="fas fa-comment"></i>
                        </button>';
        }
        
        $content .= '
                        <tr>
                            <td>' . $displayName . $chatIcon . '</td>
                            <td>
                                <span class="badge badge-' . $statusBadge . ' status-badge" style="cursor: pointer;" 
                                      data-suspect-id="' . $suspect['id'] . '" 
                                      data-case-id="' . $case['id'] . '"
                                      data-current-status="' . $status . '"
                                      onclick="openStatusModal(' . $suspect['id'] . ', \'' . addslashes($status) . '\', \'' . addslashes($isUnknown ? ($suspect['unknown_description'] ?? 'Unknown') : $suspect['full_name']) . '\')"
                                      title="Click to update status">
                                    ' . sanitize($status) . ' <i class="fas fa-edit ml-1"></i>
                                </span>
                            </td>
                            <td><span class="badge badge-' . match($suspect['risk_level'] ?? 'None') {
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
                                    <button type="button" class="btn btn-sm btn-warning" onclick="viewPersonRelationships(' . $suspect['person_id'] . ', \'' . addslashes($suspect['full_name']) . '\')" title="View Relationships (' . $suspect['relationship_count'] . ')">
                                        <i class="fas fa-users"></i>
                                    </button>';
            }
        }
        
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
        
        $content .= '
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSuspect(' . $suspect['id'] . ', \'' . addslashes($isUnknown ? ($suspect['unknown_description'] ?? 'Unknown') : $suspect['full_name']) . '\')" title="Remove from Case">
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
    $content .= '<p class="text-muted">No suspects identified yet</p>';
}

$content .= '
            </div>
        </div>

        <!-- Witnesses -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Witnesses (' . count($witnesses) . ')</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addWitnessModal">
                        <i class="fas fa-plus"></i> Add Witness
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($witnesses)) {
    $content .= '
                <table class="table table-striped">
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
        $witnessTypeBadge = match($witness['witness_type']) {
            'Eye Witness' => 'primary',
            'Expert Witness' => 'info',
            'Character Witness' => 'success',
            default => 'secondary'
        };
        
        $chatIcon = ' <button type="button" class="btn btn-xs btn-outline-primary ml-2" onclick="openStatementForPerson(\'Witness\', ' . $witness['id'] . ', \'' . addslashes($witness['full_name']) . '\')" title="Add Statement">
                        <i class="fas fa-comment"></i>
                    </button>';
        
        $content .= '
                        <tr>
                            <td><strong>' . sanitize($witness['full_name']) . '</strong>' . $chatIcon . '</td>
                            <td><span class="badge badge-' . $witnessTypeBadge . '">' . sanitize($witness['witness_type']) . '</span></td>
                            <td>' . sanitize($witness['contact'] ?? 'N/A') . '</td>
                            <td>' . format_date($witness['added_date'], 'd M Y') . '</td>
                            <td>
                                <div class="btn-group">
                                    <a href="' . url('/persons/' . $witness['person_id']) . '" class="btn btn-sm btn-info" title="View Person">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeWitness(' . $witness['id'] . ', \'' . addslashes($witness['full_name']) . '\')" title="Remove from Case">
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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Statements (' . count($statements) . ')</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addStatementModal">
                        <i class="fas fa-plus"></i> Record Statement
                    </button>
                </div>
            </div>
            <div class="card-body">';

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
                <div class="card card-outline card-' . $color . ' mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user"></i> ' . $type . ' Statements (' . count($stmts) . ')
                        </h5>
                    </div>
                    <div class="card-body p-0">
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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Evidence (' . count($evidence) . ')</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#uploadEvidenceModal">
                        <i class="fas fa-upload"></i> Upload Evidence
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($evidence)) {
    $content .= '
                <table class="table table-striped">
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
                            <td><span class="badge badge-' . $evidenceTypeBadge . '">' . sanitize($item['evidence_type']) . '</span></td>
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
        $iconClass = match($entry['new_status']) {
            'Open' => 'bg-success',
            'Under Investigation' => 'bg-info',
            'Closed' => 'bg-secondary',
            default => 'bg-primary'
        };
        
        $content .= '
                    <div>
                        <i class="fas fa-circle ' . $iconClass . '"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . format_date($entry['change_date'], 'd M Y H:i') . '</span>
                            <h3 class="timeline-header">Status changed to: <strong>' . sanitize($entry['new_status']) . '</strong></h3>
                            <div class="timeline-body">' . sanitize($entry['remarks'] ?? '') . '</div>
                            <div class="timeline-footer">
                                <small>By: ' . sanitize($entry['changed_by_name']) . '</small>
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
                                        <label class="btn btn-outline-primary active" onclick="toggleSuspectType(\"known\")">
                                            <input type="radio" name="suspect_type" value="known" checked> <i class="fas fa-user-check"></i> Known
                                        </label>
                                        <label class="btn btn-outline-warning" onclick="toggleSuspectType(\"unknown\")">
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
';

$scripts = '
<script>
$(document).ready(function() {
    // Update file input label when file is selected
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\\\").pop();
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
        let fileName = $(this).val().split("\\\\").pop();
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
        ' . json_encode(array_filter(array_map(function($s) {
            if (empty($s['person_id'])) {
                return null; // Filter out unknown suspects
            }
            return [
                'id' => $s['id'],
                'name' => $s['full_name']
            ];
        }, $suspects), function($s) { return $s !== null; })) . '.forEach(function(suspect) {
            html += `<option value="${suspect.id}">${suspect.name}</option>`;
        });
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
        const witnessData = ' . json_encode(array_map(function($w) {
            return [
                'id' => $w['id'] ?? null,
                'witness_id' => $w['witness_id'] ?? null,
                'person_id' => $w['person_id'] ?? null,
                'name' => $w['full_name'],
                'debug' => $w
            ];
        }, $witnesses)) . ';
        console.log("Witness data:", witnessData);
        witnessData.forEach(function(witness) {
            const witnessId = witness.id || witness.witness_id;
            html += `<option value="${witnessId}">${witness.name}</option>`;
        });
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
</script>
';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number']]
];

include __DIR__ . '/../layouts/main.php';
?>
