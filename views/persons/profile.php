<?php
$fullName = trim(($person['first_name'] ?? '') . ' ' . ($person['middle_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));

// Calculate age from date of birth
$age = 'N/A';
if (!empty($person['date_of_birth'])) {
    $dob = new DateTime($person['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
}

// Determine profile photo based on gender
if (!empty($person['photo_path'])) {
    $profilePhoto = url('/' . $person['photo_path']);
} else {
    // Use gender-specific dummy images from storage folder
    $gender = strtolower($person['gender'] ?? '');
    if ($gender === 'female') {
        $profilePhoto = url('/storage/female_profile.jpg'); // Female profile
    } elseif ($gender === 'male') {
        $profilePhoto = url('/storage/male_profile.jpg'); // Male profile
    } else {
        $profilePhoto = url('/storage/male_profile.jpg'); // Default
    }
}

// Get actual biometric counts from person_biometrics table
$db = \App\Config\Database::getConnection();
$stmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN biometric_type = 'Fingerprint' THEN 1 ELSE 0 END) as fingerprint_count,
        SUM(CASE WHEN biometric_type = 'Face' THEN 1 ELSE 0 END) as face_count,
        SUM(CASE WHEN biometric_type = 'Iris' THEN 1 ELSE 0 END) as iris_count,
        SUM(CASE WHEN biometric_type = 'Palm Print' THEN 1 ELSE 0 END) as palm_count
    FROM person_biometrics 
    WHERE person_id = ?
");
$stmt->execute([$person['id']]);
$biometricCounts = $stmt->fetch();
$fingerprintCount = (int)($biometricCounts['fingerprint_count'] ?? 0);
$faceCount = (int)($biometricCounts['face_count'] ?? 0);
$irisCount = (int)($biometricCounts['iris_count'] ?? 0);
$palmCount = (int)($biometricCounts['palm_count'] ?? 0);

// Get criminal history count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_criminal_history WHERE person_id = ?");
$stmt->execute([$person['id']]);
$criminalHistoryCount = $stmt->fetch()['count'] ?? 0;

// Get detailed criminal history records
$stmt = $db->prepare("
    SELECT pch.*, c.case_number, c.description as case_description
    FROM person_criminal_history pch
    LEFT JOIN cases c ON pch.case_id = c.id
    WHERE pch.person_id = ?
    ORDER BY pch.case_date DESC
");
$stmt->execute([$person['id']]);
$criminalHistoryRecords = $stmt->fetchAll();

// Get active cases count
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT c.id) as count 
    FROM cases c
    INNER JOIN case_suspects cs ON c.id = cs.case_id
    INNER JOIN suspects s ON cs.suspect_id = s.id
    WHERE s.person_id = ? AND c.status IN ('Open', 'Under Investigation')
");
$stmt->execute([$person['id']]);
$activeCasesCount = $stmt->fetch()['count'] ?? 0;

// Get all cases for this person (as suspect, witness, or complainant)
$stmt = $db->prepare("
    SELECT DISTINCT
        c.id,
        c.case_number,
        c.case_type,
        c.case_priority,
        c.status,
        c.description,
        c.incident_date,
        c.created_at,
        s.current_status as involvement_status,
        'Suspect' as involvement_type
    FROM cases c
    INNER JOIN case_suspects cs ON c.id = cs.case_id
    INNER JOIN suspects s ON cs.suspect_id = s.id
    WHERE s.person_id = ?
    
    UNION
    
    SELECT DISTINCT
        c.id,
        c.case_number,
        c.case_type,
        c.case_priority,
        c.status,
        c.description,
        c.incident_date,
        c.created_at,
        w.witness_type as involvement_status,
        'Witness' as involvement_type
    FROM cases c
    INNER JOIN case_witnesses cw ON c.id = cw.case_id
    INNER JOIN witnesses w ON cw.witness_id = w.id
    WHERE w.person_id = ?
    
    UNION
    
    SELECT DISTINCT
        c.id,
        c.case_number,
        c.case_type,
        c.case_priority,
        c.status,
        c.description,
        c.incident_date,
        c.created_at,
        comp.complainant_type as involvement_status,
        'Complainant' as involvement_type
    FROM cases c
    INNER JOIN complainants comp ON c.complainant_id = comp.id
    WHERE comp.person_id = ?
    
    ORDER BY incident_date DESC
");
$stmt->execute([$person['id'], $person['id'], $person['id']]);
$allCases = $stmt->fetchAll();
$totalCasesCount = count($allCases);

// Get active alerts count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_alerts WHERE person_id = ? AND is_active = 1");
$stmt->execute([$person['id']]);
$activeAlertsCount = $stmt->fetch()['count'] ?? 0;

// Get detailed active alerts
$stmt = $db->prepare("
    SELECT pa.*, 
           CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
    FROM person_alerts pa
    LEFT JOIN users u ON pa.issued_by = u.id
    WHERE pa.person_id = ? AND pa.is_active = 1
    ORDER BY pa.alert_priority DESC, pa.issued_date DESC
");
$stmt->execute([$person['id']]);
$activeAlerts = $stmt->fetchAll();

// Get relationships count (only where current person is person_id_1)
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM person_relationships 
    WHERE person_id_1 = ?
");
$stmt->execute([$person['id']]);
$relationshipsCount = $stmt->fetch()['count'] ?? 0;

// Get relationships details (only where current person is person_id_1)
$stmt = $db->prepare("
    SELECT pr.id,
           pr.relationship_type,
           pr.notes,
           pr.created_at,
           pr.person_id_2 as related_person_id,
           CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as related_person_name
    FROM person_relationships pr
    LEFT JOIN persons p ON pr.person_id_2 = p.id
    WHERE pr.person_id_1 = ?
    ORDER BY pr.created_at DESC
");
$stmt->execute([$person['id']]);
$relationships = $stmt->fetchAll();

// Calculate total biometric records
$totalBiometrics = $fingerprintCount + $faceCount + $irisCount + $palmCount;

// Risk level colors
$riskColors = [
    'None' => 'success',
    'Low' => 'info',
    'Medium' => 'warning',
    'High' => 'danger',
    'Critical' => 'dark'
];
$riskColor = $riskColors[$person['risk_level'] ?? 'None'] ?? 'secondary';

// Calculate profile completeness
$completeness = 0;
$totalFields = 15;
if (!empty($person['first_name'])) $completeness++;
if (!empty($person['last_name'])) $completeness++;
if (!empty($person['date_of_birth'])) $completeness++;
if (!empty($person['gender'])) $completeness++;
if (!empty($person['contact'])) $completeness++;
if (!empty($person['email'])) $completeness++;
if (!empty($person['address'])) $completeness++;
if (!empty($person['ghana_card_number'])) $completeness++;
if (!empty($person['photo_path'])) $completeness++;
if ($fingerprintCount > 0) $completeness++;
if ($faceCount > 0) $completeness++;
if (!empty($person['passport_number'])) $completeness++;
if (!empty($person['drivers_license'])) $completeness++;
if (!empty($person['alternative_contact'])) $completeness++;
if (!empty($person['middle_name'])) $completeness++;
$completenessPercent = round(($completeness / $totalFields) * 100);

$content = '
<style>
    .profile-header {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        color: white;
        padding: 40px;
        margin: -20px -20px 20px -20px;
        border-radius: 0;
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .profile-photo-large {
        width: 200px;
        height: 200px;
        border: 6px solid white;
        box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        object-fit: cover;
    }
    .classification-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 12px;
        letter-spacing: 2px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .threat-matrix {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .threat-indicator {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .threat-critical { background: #dc3545; }
    .threat-high { background: #fd7e14; }
    .threat-medium { background: #ffc107; }
    .threat-low { background: #28a745; }
    .threat-none { background: #6c757d; }
    .stat-card {
        text-align: center;
        padding: 25px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        transition: all 0.3s;
        border-left: 4px solid;
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }
    .stat-number {
        font-size: 42px;
        font-weight: 800;
        margin: 10px 0;
        font-family: Arial Black, sans-serif;
    }
    .stat-label {
        color: #6c757d;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    .identification-matrix {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }
    .id-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border-left: 3px solid #007bff;
    }
    .id-item.verified {
        border-left-color: #28a745;
        background: #d4edda;
    }
    .id-item.missing {
        border-left-color: #dc3545;
        background: #f8d7da;
    }
    .info-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .info-section-header {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
        margin-bottom: 15px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 180px;
        display: flex;
        align-items: center;
    }
    .info-label i {
        margin-right: 8px;
        width: 20px;
        text-align: center;
        color: #6c757d;
    }
    .info-value {
        color: #212529;
        flex: 1;
    }
    .alert-banner {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .danger-banner {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
    }
    .action-btn-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .action-btn {
        flex: 1;
        min-width: 150px;
    }
    .timeline-item {
        border-left: 3px solid #e9ecef;
        padding-left: 20px;
        padding-bottom: 20px;
        position: relative;
    }
    .timeline-item:before {
        content: "";
        width: 12px;
        height: 12px;
        background: #007bff;
        border: 3px solid white;
        border-radius: 50%;
        position: absolute;
        left: -8px;
        top: 0;
        box-shadow: 0 0 0 3px #e9ecef;
    }
    .biometric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .biometric-item {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .biometric-item:hover {
        background: #e9ecef;
    }
    .biometric-item i {
        font-size: 32px;
        margin-bottom: 8px;
    }
    .biometric-item.captured i {
        color: #28a745;
    }
    .biometric-item.not-captured i {
        color: #6c757d;
    }
    .nav-tabs-custom {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 15px 25px;
    }
    .nav-tabs-custom .nav-link.active {
        color: #007bff;
        border-bottom: 3px solid #007bff;
        background: transparent;
    }
    .progress-thin {
        height: 10px;
        border-radius: 5px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }
    .section-header-fbi {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: 700;
        font-size: 16px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: -20px -20px 20px -20px;
    }
    .physical-descriptor {
        display: flex;
        justify-content: space-between;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 3px solid #17a2b8;
    }
    .descriptor-label {
        font-weight: 600;
        color: #495057;
    }
    .descriptor-value {
        color: #212529;
        font-weight: 500;
    }
    .status-indicator {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .status-active { background: #28a745; color: white; }
    .status-wanted { background: #dc3545; color: white; }
    .status-clear { background: #6c757d; color: white; }
</style>

<!-- Classification Banner -->
<div class="classification-badge">
    <i class="fas fa-shield-alt"></i> RESTRICTED ACCESS
</div>

<!-- Profile Header -->
<div class="profile-header">
    <div class="row align-items-center">
        <div class="col-md-2 text-center">
            <img src="' . $profilePhoto . '" 
                 class="profile-photo-large rounded" alt="Profile Photo">
        </div>
        <div class="col-md-5">
            <h1 class="mb-3" style="font-size: 36px; font-weight: 800; letter-spacing: 1px;">' . strtoupper(sanitize($fullName)) . '</h1>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <small style="opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">National ID</small><br>
                    <strong style="font-size: 15px;"><i class="fas fa-id-card mr-2"></i>' . ($person['ghana_card_number'] ?? 'NOT PROVIDED') . '</strong>
                </div>
                <div class="col-md-6">
                    <small style="opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Date of Birth</small><br>
                    <strong style="font-size: 15px;"><i class="fas fa-calendar mr-2"></i>' . format_date($person['date_of_birth'] ?? null, 'd M Y') . '</strong>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <small style="opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Gender</small><br>
                    <strong style="font-size: 15px;"><i class="fas fa-venus-mars mr-2"></i>' . strtoupper($person['gender'] ?? 'N/A') . '</strong>
                </div>
                <div class="col-md-6">
                    <small style="opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Age</small><br>
                    <strong style="font-size: 15px;"><i class="fas fa-user mr-2"></i>' . $age . ' YEARS</strong>
                </div>
            </div>
            
            <!-- Profile Completeness -->
            <div class="mb-2">
                <small style="opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Data Completeness: ' . $completenessPercent . '%</small>
                <div class="progress progress-thin" style="background: rgba(255,255,255,0.2);">
                    <div class="progress-bar" style="width: ' . $completenessPercent . '%; background: linear-gradient(90deg, #28a745 0%, #20c997 100%);"></div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <!-- Threat Assessment Matrix -->
            <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                <h5 class="mb-3" style="font-size: 14px; letter-spacing: 1px; text-transform: uppercase; opacity: 0.9;">
                    <i class="fas fa-shield-alt"></i> Threat Assessment Matrix
                </h5>
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="threat-indicator threat-' . strtolower($person['risk_level'] ?? 'none') . '"></span>
                            <div>
                                <small style="opacity: 0.7; font-size: 10px;">RISK LEVEL</small><br>
                                <strong style="font-size: 16px;">' . strtoupper($person['risk_level'] ?? 'NONE') . '</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="status-indicator ' . ($person['is_wanted'] ? 'status-wanted' : 'status-clear') . '">' . ($person['is_wanted'] ? 'WANTED' : 'CLEAR') . '</span>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <small style="opacity: 0.7; font-size: 10px;">CRIMINAL RECORDS</small><br>
                        <strong style="font-size: 14px;">' . ($criminalHistoryCount > 0 ? '<span class="text-danger">' . $criminalHistoryCount . '</span>' : '<span class="text-success">0</span>') . '</strong>
                    </div>
                    <div class="col-6 mb-2">
                        <small style="opacity: 0.7; font-size: 10px;">ACTIVE ALERTS</small><br>
                        <strong style="font-size: 14px;">' . $activeAlertsCount . '</strong>
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <div class="row">
                    <div class="col-6">
                        <small style="opacity: 0.7; font-size: 10px;">LAST UPDATED</small><br>
                        <strong style="font-size: 12px;">' . date('d M Y, H:i') . '</strong>
                    </div>
                    <div class="col-6 text-right">
                        <small style="opacity: 0.7; font-size: 10px;">ACCESSED BY</small><br>
                        <strong style="font-size: 12px;">' . strtoupper(auth()['username'] ?? 'SYSTEM') . '</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Banners -->';

if ($person['is_wanted']) {
    $content .= '
<div class="alert-banner danger-banner">
    <h5 class="mb-2"><i class="fas fa-exclamation-circle"></i> <strong>WANTED PERSON</strong></h5>
    <p class="mb-0">This person is currently wanted by law enforcement. Exercise caution and follow proper protocols.</p>
</div>';
}

if ($activeAlertsCount > 0) {
    $content .= '
<div class="alert-banner">
    <h5 class="mb-2"><i class="fas fa-bell"></i> <strong>' . $activeAlertsCount . ' ACTIVE ALERT' . ($activeAlertsCount > 1 ? 'S' : '') . '</strong></h5>
    <p class="mb-0">This person has active alerts. <a href="#alerts-tab" onclick="$(\'a[href=\\\'#alerts-tab\\\']\').tab(\'show\')">View alerts</a></p>
</div>';
}

$content .= '

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #007bff;">
            <i class="fas fa-fingerprint text-primary" style="font-size: 40px;"></i>
            <div class="stat-number text-primary">' . $totalBiometrics . '</div>
            <div class="stat-label">Biometric Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #ffc107;">
            <i class="fas fa-folder-open text-warning" style="font-size: 40px;"></i>
            <div class="stat-number text-warning">' . $activeCasesCount . '</div>
            <div class="stat-label">Active Cases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #dc3545;">
            <i class="fas fa-history text-danger" style="font-size: 40px;"></i>
            <div class="stat-number text-danger">' . $criminalHistoryCount . '</div>
            <div class="stat-label">Criminal Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #17a2b8;">
            <i class="fas fa-bell text-info" style="font-size: 40px;"></i>
            <div class="stat-number text-info">' . $activeAlertsCount . '</div>
            <div class="stat-label">Active Alerts</div>
        </div>
    </div>
</div>

<!-- Identification Verification Matrix -->
<div class="threat-matrix">
    <div class="section-header-fbi">
        <i class="fas fa-id-card-alt"></i> Identification Verification Matrix
    </div>
    <div class="identification-matrix">
        <div class="id-item ' . (!empty($person['ghana_card_number']) ? 'verified' : 'missing') . '">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-id-card"></i> Ghana Card</strong><br>
                    <small>' . ($person['ghana_card_number'] ?? 'NOT PROVIDED') . '</small>
                </div>
                <div>
                    ' . (!empty($person['ghana_card_number']) ? '<i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>' : '<i class="fas fa-times-circle text-danger" style="font-size: 24px;"></i>') . '
                </div>
            </div>
        </div>
        <div class="id-item ' . (!empty($person['passport_number']) ? 'verified' : 'missing') . '">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-passport"></i> Passport</strong><br>
                    <small>' . ($person['passport_number'] ?? 'NOT PROVIDED') . '</small>
                </div>
                <div>
                    ' . (!empty($person['passport_number']) ? '<i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>' : '<i class="fas fa-times-circle text-danger" style="font-size: 24px;"></i>') . '
                </div>
            </div>
        </div>
        <div class="id-item ' . (!empty($person['drivers_license']) ? 'verified' : 'missing') . '">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-id-card-alt"></i> Driver\'s License</strong><br>
                    <small>' . ($person['drivers_license'] ?? 'NOT PROVIDED') . '</small>
                </div>
                <div>
                    ' . (!empty($person['drivers_license']) ? '<i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>' : '<i class="fas fa-times-circle text-danger" style="font-size: 24px;"></i>') . '
                </div>
            </div>
        </div>
        <div class="id-item ' . (!empty($person['contact']) ? 'verified' : 'missing') . '">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-phone"></i> Contact Number</strong><br>
                    <small>' . ($person['contact'] ?? 'NOT PROVIDED') . '</small>
                </div>
                <div>
                    ' . (!empty($person['contact']) ? '<i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>' : '<i class="fas fa-times-circle text-danger" style="font-size: 24px;"></i>') . '
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="info-section">
    <div class="info-section-header">
        <span><i class="fas fa-bolt"></i> Quick Actions</span>
    </div>
    <div class="action-btn-group">
        <a href="' . url('/persons/' . $person['id'] . '/biometrics') . '" class="btn btn-primary action-btn">
            <i class="fas fa-fingerprint"></i> Biometrics
        </a>
        <a href="' . url('/persons/' . $person['id'] . '/crime-check') . '" class="btn btn-warning action-btn">
            <i class="fas fa-search"></i> Crime Check
        </a>
        <a href="' . url('/persons/' . $person['id'] . '/edit') . '" class="btn btn-info action-btn">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
        <button class="btn btn-success action-btn" onclick="alert(\'Add to Case feature\')">
            <i class="fas fa-plus-circle"></i> Add to Case
        </button>
        <button class="btn btn-danger action-btn" data-toggle="modal" data-target="#issueAlertModal">
            <i class="fas fa-exclamation-triangle"></i> Issue Alert
        </button>
    </div>
</div>

<!-- Tabbed Content -->
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#personal-info-tab">
                <i class="fas fa-user"></i> Personal Information
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#biometrics-tab">
                <i class="fas fa-fingerprint"></i> Biometrics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#identification-tab">
                <i class="fas fa-id-card"></i> Identification
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#criminal-history-tab">
                <i class="fas fa-history"></i> Criminal History
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#alerts-tab">
                <i class="fas fa-bell"></i> Alerts
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#cases-tab">
                <i class="fas fa-folder-open"></i> Cases
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#relationships-tab">
                <i class="fas fa-users"></i> Relationships
            </a>
        </li>
    </ul>
    
    <div class="tab-content p-4">
        <!-- Personal Information Tab -->
        <div class="tab-pane fade show active" id="personal-info-tab">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-user-circle"></i> Basic Information</h5>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user"></i> Full Name</div>
                        <div class="info-value">' . sanitize($fullName) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-venus-mars"></i> Gender</div>
                        <div class="info-value">' . sanitize($person['gender'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-birthday-cake"></i> Date of Birth</div>
                        <div class="info-value">' . format_date($person['date_of_birth'] ?? null, 'd M Y') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar"></i> Age</div>
                        <div class="info-value">' . $age . ' years</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-phone"></i> Contact Information</h5>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-phone"></i> Primary Phone</div>
                        <div class="info-value">' . sanitize($person['contact'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-phone-alt"></i> Alternative Phone</div>
                        <div class="info-value">' . sanitize($person['alternative_contact'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value">' . sanitize($person['email'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Address</div>
                        <div class="info-value">' . sanitize($person['address'] ?? 'N/A') . '</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biometrics Tab -->
        <div class="tab-pane fade" id="biometrics-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-fingerprint"></i> Biometric Data</h5>
                <a href="' . url('/persons/' . $person['id'] . '/biometrics') . '" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Capture Biometrics
                </a>
            </div>
            
            <div class="biometric-grid">
                <div class="biometric-item ' . ($fingerprintCount > 0 ? 'captured' : 'not-captured') . '">
                    <i class="fas fa-fingerprint"></i>
                    <div class="mt-2"><strong>' . $fingerprintCount . '</strong></div>
                    <small>Fingerprints</small>
                </div>
                <div class="biometric-item ' . ($faceCount > 0 ? 'captured' : 'not-captured') . '">
                    <i class="fas fa-user-circle"></i>
                    <div class="mt-2"><strong>' . $faceCount . '</strong></div>
                    <small>Face</small>
                </div>
                <div class="biometric-item ' . ($irisCount > 0 ? 'captured' : 'not-captured') . '">
                    <i class="fas fa-eye"></i>
                    <div class="mt-2"><strong>' . $irisCount . '</strong></div>
                    <small>Iris</small>
                </div>
                <div class="biometric-item ' . ($palmCount > 0 ? 'captured' : 'not-captured') . '">
                    <i class="fas fa-hand-paper"></i>
                    <div class="mt-2"><strong>' . $palmCount . '</strong></div>
                    <small>Palm Print</small>
                </div>
            </div>
            
            ' . (($fingerprintCount + $faceCount + $irisCount + $palmCount) > 0 ? '
            <div class="alert alert-success mt-4">
                <i class="fas fa-check-circle"></i> <strong>Biometric data available.</strong> This person can be identified through biometric matching.
            </div>
            ' : '
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i> <strong>No biometric data captured.</strong> Consider capturing biometrics for positive identification.
            </div>
            ') . '
        </div>
        
        <!-- Identification Tab -->
        <div class="tab-pane fade" id="identification-tab">
            <h5 class="mb-3"><i class="fas fa-id-card"></i> Official Identification Documents</h5>
            <div class="info-row">
                <div class="info-label"><i class="fas fa-id-card"></i> Ghana Card Number</div>
                <div class="info-value">' . sanitize($person['ghana_card_number'] ?? 'Not Provided') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fas fa-passport"></i> Passport Number</div>
                <div class="info-value">' . sanitize($person['passport_number'] ?? 'Not Provided') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fas fa-id-card-alt"></i> Driver\'s License</div>
                <div class="info-value">' . sanitize($person['drivers_license'] ?? 'Not Provided') . '</div>
            </div>
        </div>
        
        <!-- Criminal History Tab -->
        <div class="tab-pane fade" id="criminal-history-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-history"></i> Criminal History Records</h5>
                <span class="badge badge-' . ($criminalHistoryCount > 0 ? 'danger' : 'success') . ' badge-lg">
                    ' . ($criminalHistoryCount > 0 ? 'HAS CRIMINAL RECORD' : 'NO CRIMINAL RECORD') . '
                </span>
            </div>
            ';
            
            if ($criminalHistoryCount > 0) {
                $content .= '
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Notice:</strong> This person has ' . $criminalHistoryCount . ' criminal history record(s) on file.
            </div>
            
            <div class="timeline">';
                
                foreach ($criminalHistoryRecords as $record) {
                    $involvementClass = match($record['involvement_type']) {
                        'Convicted' => 'danger',
                        'Charged' => 'warning',
                        'Arrested' => 'info',
                        'Acquitted' => 'secondary',
                        default => 'primary'
                    };
                    
                    $involvementIcon = match($record['involvement_type']) {
                        'Convicted' => 'fa-gavel',
                        'Charged' => 'fa-file-alt',
                        'Arrested' => 'fa-handcuffs',
                        'Acquitted' => 'fa-balance-scale',
                        default => 'fa-exclamation-circle'
                    };
                    
                    $content .= '
                <div>
                    <i class="fas ' . $involvementIcon . ' bg-' . $involvementClass . '"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fas fa-clock"></i> ' . format_date($record['case_date'], 'd M Y') . '</span>
                        <h3 class="timeline-header">
                            <span class="badge badge-' . $involvementClass . '">' . sanitize($record['involvement_type']) . '</span>
                            ' . ($record['case_number'] ? '<a href="' . url('/cases/' . $record['case_id']) . '">' . sanitize($record['case_number']) . '</a>' : 'N/A') . '
                        </h3>
                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-tag"></i> Offence Category:</strong><br>
                                    <span class="text-muted">' . sanitize($record['offence_category'] ?? 'Not specified') . '</span>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-info-circle"></i> Case Status:</strong><br>
                                    <span class="badge badge-secondary">' . sanitize($record['case_status']) . '</span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-clipboard-check"></i> Outcome:</strong><br>
                                    <span class="text-muted">' . sanitize($record['outcome'] ?? 'No outcome recorded') . '</span>
                                </div>
                            </div>
                            ' . ($record['case_description'] ? '
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-file-alt"></i> Case Description:</strong><br>
                                    <span class="text-muted">' . sanitize($record['case_description']) . '</span>
                                </div>
                            </div>
                            ' : '') . '
                        </div>
                        <div class="timeline-footer">
                            ' . ($record['case_id'] ? '<a href="' . url('/cases/' . $record['case_id']) . '" class="btn btn-sm btn-primary"><i class="fas fa-folder-open"></i> View Case Details</a>' : '') . '
                        </div>
                    </div>
                </div>';
                }
                
                $content .= '
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>';
            } else {
                $content .= '
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No criminal history records found for this person.
            </div>';
            }
            
            $content .= '
        </div>
        
        <!-- Alerts Tab -->
        <div class="tab-pane fade" id="alerts-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-bell"></i> Active Alerts</h5>
                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#issueAlertModal">
                    <i class="fas fa-plus"></i> Issue Alert
                </button>
            </div>
            ';
            
            if ($activeAlertsCount > 0) {
                $content .= '
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Message</th>
                            <th>Details</th>
                            <th>Issued By</th>
                            <th>Issued Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                foreach ($activeAlerts as $alert) {
                    $priorityClass = match($alert['alert_priority']) {
                        'Critical' => 'danger',
                        'High' => 'warning',
                        'Medium' => 'info',
                        'Low' => 'secondary',
                        default => 'secondary'
                    };
                    
                    $typeClass = match($alert['alert_type']) {
                        'Wanted' => 'danger',
                        'Dangerous' => 'danger',
                        'Flight Risk' => 'warning',
                        'Repeat Offender' => 'warning',
                        'Missing' => 'info',
                        default => 'secondary'
                    };
                    
                    $isExpired = !empty($alert['expiry_date']) && strtotime($alert['expiry_date']) < time();
                    
                    $content .= '
                        <tr' . ($isExpired ? ' class="table-secondary"' : '') . '>
                            <td><span class="badge badge-' . $typeClass . '">' . sanitize($alert['alert_type']) . '</span></td>
                            <td><span class="badge badge-' . $priorityClass . '">' . sanitize($alert['alert_priority']) . '</span></td>
                            <td><strong>' . sanitize($alert['alert_message']) . '</strong></td>
                            <td>
                                <small class="text-muted">' . (strlen($alert['alert_details'] ?? '') > 50 ? substr(sanitize($alert['alert_details']), 0, 50) . '...' : sanitize($alert['alert_details'] ?? 'No details')) . '</small>
                            </td>
                            <td><small>' . sanitize($alert['issued_by_name'] ?? 'System') . '</small></td>
                            <td>' . format_date($alert['issued_date'], 'd M Y') . '</td>
                            <td>' . ($alert['expiry_date'] ? format_date($alert['expiry_date'], 'd M Y') : '<span class="text-muted">No expiry</span>') . '</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editAlert(' . $alert['id'] . ')" title="Edit Alert">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deactivateAlert(' . $alert['id'] . ')" title="Deactivate Alert">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </td>
                        </tr>';
                }
                
                $content .= '
                    </tbody>
                </table>
            </div>';
            } else {
                $content .= '
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> No active alerts for this person.
            </div>';
            }
            
            $content .= '
        </div>
        
        <!-- Cases Tab -->
        <div class="tab-pane fade" id="cases-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-folder-open"></i> Case Involvement</h5>
                <div>
                    <span class="badge badge-warning badge-lg mr-2">
                        ' . $activeCasesCount . ' ACTIVE
                    </span>
                    <span class="badge badge-secondary badge-lg">
                        ' . $totalCasesCount . ' TOTAL
                    </span>
                </div>
            </div>
            ';
            
            if ($totalCasesCount > 0) {
                $content .= '
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Case Number</th>
                            <th>Type</th>
                            <th>Involvement</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Incident Date</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                foreach ($allCases as $case) {
                    $statusClass = match($case['status']) {
                        'Open' => 'primary',
                        'Under Investigation' => 'warning',
                        'Referred' => 'info',
                        'Closed' => 'secondary',
                        'Archived' => 'dark',
                        default => 'secondary'
                    };
                    
                    $priorityClass = match($case['case_priority']) {
                        'Critical' => 'danger',
                        'High' => 'warning',
                        'Medium' => 'info',
                        'Low' => 'secondary',
                        default => 'secondary'
                    };
                    
                    $involvementClass = match($case['involvement_type']) {
                        'Suspect' => 'danger',
                        'Witness' => 'info',
                        'Complainant' => 'success',
                        default => 'secondary'
                    };
                    
                    $content .= '
                        <tr>
                            <td>
                                <a href="' . url('/cases/' . $case['id']) . '">
                                    <strong>' . sanitize($case['case_number']) . '</strong>
                                </a>
                            </td>
                            <td><span class="badge badge-secondary">' . sanitize($case['case_type']) . '</span></td>
                            <td>
                                <span class="badge badge-' . $involvementClass . '">' . sanitize($case['involvement_type']) . '</span><br>
                                <small class="text-muted">' . sanitize($case['involvement_status']) . '</small>
                            </td>
                            <td><span class="badge badge-' . $statusClass . '">' . sanitize($case['status']) . '</span></td>
                            <td><span class="badge badge-' . $priorityClass . '">' . sanitize($case['case_priority']) . '</span></td>
                            <td>' . format_date($case['incident_date'], 'd M Y') . '</td>
                            <td>
                                <small class="text-muted">' . (strlen($case['description']) > 60 ? substr(sanitize($case['description']), 0, 60) . '...' : sanitize($case['description'])) . '</small>
                            </td>
                            <td>
                                <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-outline-primary" title="View Case">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>';
                }
                
                $content .= '
                    </tbody>
                </table>
            </div>';
            } else {
                $content .= '
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No cases found for this person.
            </div>';
            }
            
            $content .= '
        </div>
        
        <!-- Relationships Tab -->
        <div class="tab-pane fade" id="relationships-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-users"></i> Known Relationships</h5>
                <div>
                    <span class="badge badge-' . ($relationshipsCount > 0 ? 'primary' : 'secondary') . ' badge-lg mr-2">
                        ' . $relationshipsCount . ' RELATIONSHIP' . ($relationshipsCount != 1 ? 'S' : '') . '
                    </span>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addRelationshipModal">
                        <i class="fas fa-plus"></i> Add Relationship
                    </button>
                </div>
            </div>
            
            ';
            
            if ($relationshipsCount > 0) {
                $content .= '
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Related Person</th>
                            <th>Relationship Type</th>
                            <th>Notes</th>
                            <th>Recorded Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                foreach ($relationships as $rel) {
                    $content .= '
                        <tr>
                            <td>
                                <a href="' . url('/persons/' . $rel['related_person_id']) . '">
                                    <strong>' . sanitize($rel['related_person_name']) . '</strong>
                                </a>
                            </td>
                            <td><span class="badge badge-info">' . sanitize($rel['relationship_type']) . '</span></td>
                            <td>' . sanitize($rel['notes'] ?? 'No notes') . '</td>
                            <td>' . format_date($rel['created_at'], 'd M Y') . '</td>
                            <td>
                                <a href="' . url('/persons/' . $rel['related_person_id']) . '" class="btn btn-sm btn-outline-primary" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRelationship(' . $rel['id'] . ')" title="Delete Relationship">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }
                
                $content .= '
                    </tbody>
                </table>
            </div>';
            } else {
                $content .= '
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No known relationships recorded for this person.
            </div>';
            }
            
            $content .= '
        </div>
    </div>
</div>

<!-- Access Log Footer -->
<div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 8px; font-size: 12px; color: #6c757d;">
    <div class="row">
        <div class="col-md-6">
            <i class="fas fa-shield-alt"></i> <strong>Security Classification:</strong> RESTRICTED
        </div>
        <div class="col-md-6 text-right">
            <i class="fas fa-clock"></i> <strong>Access Logged:</strong> ' . date('Y-m-d H:i:s') . ' by ' . (auth()['username'] ?? 'System') . '
        </div>
    </div>
</div>
';

// Get relationship types for modal
$relationship_types = [
    'Family' => ['Parent', 'Child', 'Sibling', 'Spouse', 'Grandparent', 'Grandchild', 'Uncle/Aunt', 'Nephew/Niece', 'Cousin', 'In-Law'],
    'Professional' => ['Colleague', 'Employer', 'Employee', 'Business Partner', 'Client', 'Supplier'],
    'Social' => ['Friend', 'Neighbor', 'Acquaintance', 'Roommate'],
    'Criminal' => ['Known Associate', 'Gang Member', 'Accomplice', 'Victim', 'Witness'],
    'Other' => ['Other']
];

$content .= '

<!-- Issue Alert Modal -->
<div class="modal fade" id="issueAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Issue Alert</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="issueAlertForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Person:</strong> ' . sanitize($person['first_name'] . ' ' . $person['last_name']) . '
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alert Type <span class="text-danger">*</span></label>
                                <select name="alert_type" class="form-control" required>
                                    <option value="">Select Alert Type</option>
                                    <option value="Wanted">Wanted</option>
                                    <option value="Dangerous">Dangerous</option>
                                    <option value="Flight Risk">Flight Risk</option>
                                    <option value="Repeat Offender">Repeat Offender</option>
                                    <option value="Missing">Missing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select name="alert_priority" class="form-control" required>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alert Message <span class="text-danger">*</span></label>
                        <input type="text" name="alert_message" class="form-control" placeholder="Brief summary of the alert" required maxlength="255">
                        <small class="form-text text-muted">Short description that will appear in alert lists</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Alert Details (Optional)</label>
                        <textarea name="alert_details" class="form-control" rows="4" placeholder="Additional details, instructions, or context for this alert"></textarea>
                        <small class="form-text text-muted">Provide comprehensive information about why this alert is being issued</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control" min="' . date('Y-m-d') . '">
                        <small class="form-text text-muted">Leave blank for alerts that don\'t expire</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> This alert will be visible to all officers with access to this person\'s profile. The person\'s risk level may be automatically updated based on this alert.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-exclamation-triangle"></i> Issue Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Alert Modal -->
<div class="modal fade" id="editAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Alert</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="editAlertForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                    <input type="hidden" id="edit_alert_id" name="alert_id">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Person:</strong> ' . sanitize($person['first_name'] . ' ' . $person['last_name']) . '
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alert Type <span class="text-danger">*</span></label>
                                <select name="alert_type" id="edit_alert_type" class="form-control" required>
                                    <option value="">Select Alert Type</option>
                                    <option value="Wanted">Wanted</option>
                                    <option value="Dangerous">Dangerous</option>
                                    <option value="Flight Risk">Flight Risk</option>
                                    <option value="Repeat Offender">Repeat Offender</option>
                                    <option value="Missing">Missing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select name="alert_priority" id="edit_alert_priority" class="form-control" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alert Message <span class="text-danger">*</span></label>
                        <input type="text" name="alert_message" id="edit_alert_message" class="form-control" placeholder="Brief summary of the alert" required maxlength="255">
                        <small class="form-text text-muted">Short description that will appear in alert lists</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Alert Details (Optional)</label>
                        <textarea name="alert_details" id="edit_alert_details" class="form-control" rows="4" placeholder="Additional details, instructions, or context for this alert"></textarea>
                        <small class="form-text text-muted">Provide comprehensive information about why this alert is being issued</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" min="' . date('Y-m-d') . '">
                        <small class="form-text text-muted">Leave blank for alerts that don\'t expire</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Relationship Modal -->
<div class="modal fade" id="addRelationshipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-users"></i> Add Relationship</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addRelationshipForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                    
                    <div class="form-group">
                        <label>Search Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="relationship_person_search" placeholder="Search by name, Ghana Card, or phone number...">
                        <input type="hidden" name="related_person_id" id="related_person_id" required>
                        <div id="relationship_search_results" class="mt-2 list-group"></div>
                        <small class="form-text text-muted">Type at least 2 characters to search</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Relationship Type <span class="text-danger">*</span></label>
                        <select name="relationship_type" class="form-control" required>
                            <option value="">Select Relationship</option>';

foreach ($relationship_types as $category => $types) {
    $content .= '
                            <optgroup label="' . $category . '">';
    foreach ($types as $type) {
        $content .= '
                                <option value="' . $type . '">' . $type . '</option>';
    }
    $content .= '
                            </optgroup>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional information about this relationship"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> This will create a bidirectional relationship record in the database.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => $fullName]
];

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
    // Realtime search for person to add relationship
    let relationshipSearchTimeout;
    const currentPersonId = <?= $person['id'] ?>;

    $("#relationship_person_search").on("keyup", function() {
        clearTimeout(relationshipSearchTimeout);
        const keyword = $(this).val().trim();
        
        if (keyword.length < 2) {
            $("#relationship_search_results").hide().empty();
            return;
        }
        
        // Show loading indicator
        $("#relationship_search_results").html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Searching...</div>').show();
        
        // Debounce search
        relationshipSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "<?= url('/persons/search') ?>",
                method: "GET",
                data: { q: keyword },
                success: function(response) {
                    $("#relationship_search_results").empty();
                    
                    if (response.success && response.persons && response.persons.length > 0) {
                        response.persons.forEach(function(person) {
                            // Skip current person
                            if (person.id === currentPersonId) {
                                return;
                            }
                            
                            const ghanaCard = person.ghana_card_number || "N/A";
                            const phoneNumber = person.phone_number || person.contact || "N/A";
                            
                            const item = $("<a>")
                                .attr("href", "#")
                                .addClass("list-group-item list-group-item-action")
                                .on("click", function(e) {
                                    e.preventDefault();
                                    selectRelatedPerson(person.id, person.full_name);
                                });
                            
                            item.html('<strong>' + person.full_name + '</strong><br><small class="text-muted"><i class="fas fa-id-card"></i> ' + ghanaCard + ' | <i class="fas fa-phone"></i> ' + phoneNumber + '</small>');
                            $("#relationship_search_results").append(item);
                        });
                        $("#relationship_search_results").show();
                    } else {
                        $("#relationship_search_results").html('<div class="list-group-item text-muted"><i class="fas fa-info-circle"></i> No persons found</div>').show();
                    }
                },
                error: function() {
                    $("#relationship_search_results").html('<div class="list-group-item text-danger"><i class="fas fa-exclamation-circle"></i> Search failed. Please try again.</div>').show();
                }
            });
        }, 300);
    });

    function selectRelatedPerson(personId, personName) {
        $("#related_person_id").val(personId);
        $("#relationship_person_search").val(personName);
        $("#relationship_search_results").empty().hide();
    }
    window.selectRelatedPerson = selectRelatedPerson;

    // Submit relationship form
    $("#addRelationshipForm").on("submit", function(e) {
        e.preventDefault();
        
        const relatedPersonId = $("#related_person_id").val();
        if (!relatedPersonId) {
            alert("Please search and select a person first");
            return;
        }
        
        $.ajax({
            url: "<?= url('/persons/' . $person['id'] . '/relationships') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to create relationship");
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "Failed to create relationship. Please try again.";
                alert(message);
            }
        });
    });

    // Delete relationship
    window.deleteRelationship = function(relationshipId) {
        if (!confirm("Are you sure you want to delete this relationship?")) {
            return;
        }
        
        $.ajax({
            url: "<?= url('/persons/relationships/') ?>" + relationshipId + "/delete",
            method: "POST",
            data: { csrf_token: "<?= csrf_token() ?>" },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to delete relationship");
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "Failed to delete relationship. Please try again.";
                alert(message);
            }
        });
    };

    // Submit Issue Alert form
    $("#issueAlertForm").on("submit", function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Issuing Alert...');
        
        $.ajax({
            url: "<?= url('/persons/' . $person['id'] . '/alerts') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert("Alert issued successfully!");
                    location.reload();
                } else {
                    alert(response.message || "Failed to issue alert");
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "Failed to issue alert. Please try again.";
                alert(message);
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Clear Issue Alert form when modal is closed
    $('#issueAlertModal').on('hidden.bs.modal', function () {
        $('#issueAlertForm')[0].reset();
    });

    // Edit alert function
    window.editAlert = function(alertId) {
        // Fetch alert data
        $.ajax({
            url: "<?= url('/persons/alerts/') ?>" + alertId,
            method: "GET",
            success: function(response) {
                if (response.success && response.alert) {
                    const alert = response.alert;
                    
                    // Populate form fields
                    $('#edit_alert_id').val(alert.id);
                    $('#edit_alert_type').val(alert.alert_type);
                    $('#edit_alert_priority').val(alert.alert_priority);
                    $('#edit_alert_message').val(alert.alert_message);
                    $('#edit_alert_details').val(alert.alert_details || '');
                    $('#edit_expiry_date').val(alert.expiry_date || '');
                    
                    // Show modal
                    $('#editAlertModal').modal('show');
                } else {
                    alert("Failed to load alert details");
                }
            },
            error: function() {
                alert("Failed to load alert details. Please try again.");
            }
        });
    };

    // Submit Edit Alert form
    $("#editAlertForm").on("submit", function(e) {
        e.preventDefault();
        
        const alertId = $("#edit_alert_id").val();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: "<?= url('/persons/alerts/') ?>" + alertId + "/update",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert("Alert updated successfully!");
                    location.reload();
                } else {
                    alert(response.message || "Failed to update alert");
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "Failed to update alert. Please try again.";
                alert(message);
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Deactivate alert function
    window.deactivateAlert = function(alertId) {
        if (!confirm("Are you sure you want to deactivate this alert? This action cannot be undone.")) {
            return;
        }
        
        $.ajax({
            url: "<?= url('/persons/alerts/') ?>" + alertId + "/deactivate",
            method: "POST",
            data: { csrf_token: "<?= csrf_token() ?>" },
            success: function(response) {
                if (response.success) {
                    alert("Alert deactivated successfully!");
                    location.reload();
                } else {
                    alert(response.message || "Failed to deactivate alert");
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "Failed to deactivate alert. Please try again.";
                alert(message);
            }
        });
    };

    // Clear Edit Alert form when modal is closed
    $('#editAlertModal').on('hidden.bs.modal', function () {
        $('#editAlertForm')[0].reset();
    });
});
</script>
