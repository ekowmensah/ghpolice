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

// Get active alerts count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_alerts WHERE person_id = ? AND is_active = 1");
$stmt->execute([$person['id']]);
$activeAlertsCount = $stmt->fetch()['count'] ?? 0;

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
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 30px;
        margin: -20px -20px 20px -20px;
        border-radius: 0;
    }
    .profile-photo-large {
        width: 180px;
        height: 180px;
        border: 5px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        object-fit: cover;
    }
    .classification-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        padding: 5px 15px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 11px;
        letter-spacing: 1px;
    }
    .stat-card {
        text-align: center;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        margin: 10px 0;
    }
    .stat-label {
        color: #6c757d;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        height: 8px;
        border-radius: 4px;
    }
</style>

<!-- Classification Banner -->
<div class="classification-badge">
    <i class="fas fa-shield-alt"></i> RESTRICTED ACCESS
</div>

<!-- Profile Header -->
<div class="profile-header">
    <div class="row align-items-center">
        <div class="col-md-3 text-center">
            <img src="' . $profilePhoto . '" 
                 class="profile-photo-large rounded" alt="Profile Photo">
            <div class="mt-3">
                <span class="badge badge-' . $riskColor . ' badge-lg" style="font-size: 14px; padding: 8px 15px;">
                    <i class="fas fa-exclamation-triangle"></i> ' . strtoupper($person['risk_level'] ?? 'NONE') . ' RISK
                </span>
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="mb-2" style="font-size: 32px; font-weight: 700;">' . strtoupper(sanitize($fullName)) . '</h1>
         <!--   <p class="mb-1" style="font-size: 16px; opacity: 0.9;">
                <i class="fas fa-id-card mr-2"></i> Person ID: <strong>' . $person['id'] . '</strong>
            </p> -->
            <p class="mb-1" style="font-size: 16px; opacity: 0.9;">
                <i class="fas fa-fingerprint mr-2"></i> Ghana Card: <strong>' . ($person['ghana_card_number'] ?? 'Not Provided') . '</strong>
            </p>
            <p class="mb-3" style="font-size: 16px; opacity: 0.9;">
                <i class="fas fa-calendar mr-2"></i> DOB: <strong>' . format_date($person['date_of_birth'] ?? null, 'd M Y') . '</strong>
                <span class="ml-3"><i class="fas fa-user mr-2"></i> Age: <strong>' . $age . '</strong></span>
            </p>
            
            <!-- Profile Completeness -->
            <div class="mb-2">
                <small style="opacity: 0.8;">Profile Completeness: ' . $completenessPercent . '%</small>
                <div class="progress progress-thin">
                    <div class="progress-bar bg-success" style="width: ' . $completenessPercent . '%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 text-right">
            <div class="mb-2">
                <small style="opacity: 0.8;">Last Updated</small><br>
                <strong>' . date('d M Y, H:i') . '</strong>
            </div>
            <div class="mb-2">
                <small style="opacity: 0.8;">Accessed By</small><br>
                <strong>' . (auth()['username'] ?? 'System') . '</strong>
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
        <div class="stat-card">
            <i class="fas fa-fingerprint text-primary" style="font-size: 32px;"></i>
            <div class="stat-number text-primary">' . $fingerprintCount . '</div>
            <div class="stat-label">Fingerprints</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-folder-open text-warning" style="font-size: 32px;"></i>
            <div class="stat-number text-warning">' . $activeCasesCount . '</div>
            <div class="stat-label">Active Cases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-history text-danger" style="font-size: 32px;"></i>
            <div class="stat-number text-danger">' . $criminalHistoryCount . '</div>
            <div class="stat-label">Criminal Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-bell text-info" style="font-size: 32px;"></i>
            <div class="stat-number text-info">' . $activeAlertsCount . '</div>
            <div class="stat-label">Active Alerts</div>
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
        <button class="btn btn-danger action-btn" onclick="alert(\'Issue Alert feature\')">
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
                <span class="badge badge-' . ($person['has_criminal_record'] ? 'danger' : 'success') . ' badge-lg">
                    ' . ($person['has_criminal_record'] ? 'HAS CRIMINAL RECORD' : 'NO CRIMINAL RECORD') . '
                </span>
            </div>
            
            ' . ($criminalHistoryCount > 0 ? '
            <p class="text-muted">This person has ' . $criminalHistoryCount . ' criminal history record(s) on file.</p>
            <!-- Criminal history records would be listed here -->
            ' : '
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No criminal history records found for this person.
            </div>
            ') . '
        </div>
        
        <!-- Alerts Tab -->
        <div class="tab-pane fade" id="alerts-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-bell"></i> Active Alerts</h5>
                <button class="btn btn-danger btn-sm" onclick="alert(\'Issue new alert\')">
                    <i class="fas fa-plus"></i> Issue Alert
                </button>
            </div>
            
            ' . ($activeAlertsCount > 0 ? '
            <p class="text-muted">This person has ' . $activeAlertsCount . ' active alert(s).</p>
            <!-- Alerts would be listed here -->
            ' : '
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> No active alerts for this person.
            </div>
            ') . '
        </div>
        
        <!-- Cases Tab -->
        <div class="tab-pane fade" id="cases-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-folder-open"></i> Case Involvement</h5>
                <span class="badge badge-' . ($activeCasesCount > 0 ? 'warning' : 'secondary') . ' badge-lg">
                    ' . $activeCasesCount . ' ACTIVE CASE' . ($activeCasesCount != 1 ? 'S' : '') . '
                </span>
            </div>
            
            ' . ($activeCasesCount > 0 ? '
            <p class="text-muted">This person is involved in ' . $activeCasesCount . ' active case(s).</p>
            <!-- Cases would be listed here -->
            ' : '
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No active cases found for this person.
            </div>
            ') . '
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

include __DIR__ . '/../layouts/main.php';
?>
