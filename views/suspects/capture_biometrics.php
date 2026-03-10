<?php
// Get suspect details
$suspectId = $suspect['id'] ?? 0;
$personId = $suspect['person_id'] ?? 0;
$suspectName = $suspect['full_name'] ?? 'Unknown';

// Get existing biometrics for this person
$db = \App\Config\Database::getConnection();
$stmt = $db->prepare("
    SELECT * FROM person_biometrics 
    WHERE person_id = ? 
    ORDER BY captured_at DESC
");
$stmt->execute([$personId]);
$biometrics = $stmt->fetchAll();

// Count fingerprints
$fingerprintCount = count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint'));

$content = '
<style>
    .biometric-page {
        background: #f8f9fa;
        min-height: 100vh;
    }
    .biometric-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .capture-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .biometric-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
    }
    .bio-tab {
        padding: 12px 24px;
        border: none;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        font-weight: 500;
        transition: all 0.3s;
    }
    .bio-tab:hover {
        background: #f8f9fa;
        color: #495057;
    }
    .bio-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .hand-display {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin: 40px 0;
    }
    .hand-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .hand-title {
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 25px;
    }
    .finger-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-top: 20px;
    }
    .finger-card {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 15px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }
    .finger-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .finger-card.captured {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #28a745;
        color: white;
    }
    .finger-icon {
        font-size: 36px;
        margin-bottom: 8px;
        color: #6c757d;
    }
    .finger-card.captured .finger-icon {
        color: white;
    }
    .finger-name {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .capture-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #28a745;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .progress-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .progress-bar-custom {
        height: 40px;
        border-radius: 20px;
        background: #e9ecef;
        overflow: hidden;
        position: relative;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        transition: width 0.5s ease;
    }
    .upload-zone {
        border: 3px dashed #dee2e6;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    .upload-zone:hover {
        border-color: #667eea;
        background: #f0f0ff;
    }
    .biometric-list {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .bio-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s;
    }
    .bio-item:hover {
        background: #f8f9fa;
    }
    .bio-item:last-child {
        border-bottom: none;
    }
</style>

<div class="biometric-page">
    <!-- Header -->
    <div class="biometric-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-fingerprint"></i> Biometric Data Collection</h2>
                <p class="mb-0 mt-2" style="opacity: 0.9;">Suspect: <strong>' . htmlspecialchars($suspectName) . '</strong></p>
            </div>
            <div class="text-right">
                <div style="font-size: 48px; font-weight: bold;">' . $fingerprintCount . '/10</div>
                <div style="opacity: 0.9;">Fingerprints Captured</div>
                ' . ($fingerprintCount === 10 ? '<button class="btn btn-success mt-2" onclick="exportFingerprintSheet()">
                    <i class="fas fa-download"></i> Export Fingerprint Sheet
                </button>' : '') . '
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="progress-section">
        <h5 class="mb-3"><i class="fas fa-chart-line"></i> Collection Progress</h5>
        <div class="progress-bar-custom">
            <div class="progress-fill" style="width: ' . ($fingerprintCount * 10) . '%;">
                ' . $fingerprintCount . ' of 10 Fingerprints
            </div>
        </div>
    </div>

    <!-- Biometric Type Tabs -->
    <div class="biometric-tabs">
        <button class="bio-tab active" data-type="fingerprint">
            <i class="fas fa-fingerprint"></i> Fingerprint
        </button>
        <button class="bio-tab" data-type="face">
            <i class="fas fa-user"></i> Face
        </button>
        <button class="bio-tab" data-type="iris">
            <i class="fas fa-eye"></i> Iris
        </button>
        <button class="bio-tab" data-type="palm">
            <i class="fas fa-hand-paper"></i> Palm Print
        </button>
    </div>

    <!-- Fingerprint Section -->
    <div class="bio-section" id="fingerprint-section">
        <!-- Capture Method Selection -->
        <div class="capture-section mb-3">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="fp_method" value="manual" checked> <i class="fas fa-hand-pointer"></i> Manual Select
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="fp_method" value="scanner"> <i class="fas fa-scanner"></i> Fingerprint Scanner
                </label>
            </div>
        </div>
        
        <div class="hand-display" id="manual-fingerprint">
            <!-- Left Hand -->
            <div class="hand-card">
                <div class="hand-title">
                    <i class="fas fa-hand-paper"></i> LEFT HAND
                </div>
                <div class="finger-grid">';

$leftFingers = [
    ['name' => 'Left Thumb', 'short' => 'Thumb'],
    ['name' => 'Left Index', 'short' => 'Index'],
    ['name' => 'Left Middle', 'short' => 'Middle'],
    ['name' => 'Left Ring', 'short' => 'Ring'],
    ['name' => 'Left Little', 'short' => 'Little']
];

foreach ($leftFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        // Check if remarks contains the finger name (handles both exact match and "Finger Name - Bulk Upload")
        if ($bio['biometric_type'] === 'Fingerprint' && 
            (($bio['remarks'] ?? '') === $finger['name'] || 
             strpos($bio['remarks'] ?? '', $finger['name']) === 0)) {
            $captured = true;
            break;
        }
    }
    $capturedClass = $captured ? 'captured' : '';
    $content .= '<div class="finger-card ' . $capturedClass . '" onclick="captureFingerprint(\'' . $finger['name'] . '\')">
                    <i class="fas fa-fingerprint finger-icon"></i>
                    <div class="finger-name">' . $finger['short'] . '</div>
                    ' . ($captured ? '<div class="capture-badge"><i class="fas fa-check"></i></div>' : '') . '
                </div>';
}

$content .= '
                </div>
            </div>

            <!-- Right Hand -->
            <div class="hand-card">
                <div class="hand-title">
                    <i class="fas fa-hand-paper fa-flip-horizontal"></i> RIGHT HAND
                </div>
                <div class="finger-grid">';

$rightFingers = [
    ['name' => 'Right Thumb', 'short' => 'Thumb'],
    ['name' => 'Right Index', 'short' => 'Index'],
    ['name' => 'Right Middle', 'short' => 'Middle'],
    ['name' => 'Right Ring', 'short' => 'Ring'],
    ['name' => 'Right Little', 'short' => 'Little']
];

foreach ($rightFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        // Check if remarks contains the finger name (handles both exact match and "Finger Name - Bulk Upload")
        if ($bio['biometric_type'] === 'Fingerprint' && 
            (($bio['remarks'] ?? '') === $finger['name'] || 
             strpos($bio['remarks'] ?? '', $finger['name']) === 0)) {
            $captured = true;
            break;
        }
    }
    $capturedClass = $captured ? 'captured' : '';
    $content .= '<div class="finger-card ' . $capturedClass . '" onclick="captureFingerprint(\'' . $finger['name'] . '\')">
                    <i class="fas fa-fingerprint finger-icon"></i>
                    <div class="finger-name">' . $finger['short'] . '</div>
                    ' . ($captured ? '<div class="capture-badge"><i class="fas fa-check"></i></div>' : '') . '
                </div>';
}

$content .= '
                </div>
            </div>
        </div>

        <!-- Bulk Upload Option -->
        <div class="capture-section">
            <h5 class="mb-3"><i class="fas fa-upload"></i> Bulk Upload (Traditional Ink Method)</h5>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Upload scanned fingerprint sheet, then mark which print corresponds to which finger
            </div>
            
            <!-- Hidden CSRF token for bulk upload -->
            ' . csrf_field() . '
            
            <!-- Step 1: Upload Image -->
            <div id="bulk-upload-step1">
                <div class="upload-zone" onclick="document.getElementById(\'bulk_sheet_file\').click()">
                    <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                    <h6>Upload Fingerprint Sheet (All 10 Fingers)</h6>
                    <p class="text-muted mb-0">Click to select scanned sheet with all fingerprints</p>
                    <input type="file" id="bulk_sheet_file" style="display: none;" accept="image/*">
                </div>
            </div>
            
            <!-- Step 2: Mark Fingerprints -->
            <div id="bulk-upload-step2" style="display: none;">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-3"><i class="fas fa-info-circle text-info"></i> Click and drag to draw a box around each fingerprint</h6>
                        <div style="position: relative; border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #f8f9fa;">
                            <img id="bulk_sheet_preview" style="width: 100%; display: block;">
                            <canvas id="bulk_marking_canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair; pointer-events: auto;"></canvas>
                        </div>
                        <p class="small text-muted mt-2">
                            <strong>Tip:</strong> Draw boxes that tightly fit each fingerprint for best results
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-3">Finger Mapping</h6>
                        <div class="list-group" id="finger-mapping-list">
                            <div class="list-group-item finger-map-item" data-finger="Left Thumb">
                                <i class="fas fa-circle text-muted"></i> Left Thumb
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Left Index">
                                <i class="fas fa-circle text-muted"></i> Left Index
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Left Middle">
                                <i class="fas fa-circle text-muted"></i> Left Middle
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Left Ring">
                                <i class="fas fa-circle text-muted"></i> Left Ring
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Left Little">
                                <i class="fas fa-circle text-muted"></i> Left Little
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Right Thumb">
                                <i class="fas fa-circle text-muted"></i> Right Thumb
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Right Index">
                                <i class="fas fa-circle text-muted"></i> Right Index
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Right Middle">
                                <i class="fas fa-circle text-muted"></i> Right Middle
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Right Ring">
                                <i class="fas fa-circle text-muted"></i> Right Ring
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                            <div class="list-group-item finger-map-item" data-finger="Right Little">
                                <i class="fas fa-circle text-muted"></i> Right Little
                                <span class="badge badge-secondary float-right">Not marked</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="small text-muted">
                                <strong>Instructions:</strong><br>
                                1. Select finger from list<br>
                                2. Click on corresponding print in image<br>
                                3. Repeat for all 10 fingers
                            </p>
                            <button class="btn btn-success btn-block" onclick="processBulkFingerprints()" id="process-bulk-btn" disabled>
                                <i class="fas fa-check"></i> Process All Fingerprints
                            </button>
                            <button class="btn btn-secondary btn-block" onclick="resetBulkUpload()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scanner Interface (hidden by default) -->
        <div class="capture-section" id="scanner-interface" style="display: none;">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Fingerprint Scanner Instructions:</strong>
                <ol class="mb-0 mt-2">
                    <li>Connect your fingerprint scanner device</li>
                    <li>Place finger on scanner pad</li>
                    <li>Wait for capture confirmation</li>
                    <li>Scanner will automatically upload the fingerprint</li>
                </ol>
            </div>
            <div class="text-center p-5" style="background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-fingerprint fa-5x text-muted mb-3"></i>
                <h5>Waiting for Scanner...</h5>
                <p class="text-muted">Place finger on scanner device</p>
                <button class="btn btn-primary" onclick="initScanner()">
                    <i class="fas fa-sync"></i> Initialize Scanner
                </button>
            </div>
        </div>
    </div>

    <!-- Face Section -->
    <div class="bio-section" id="face-section" style="display: none;">
        <!-- Capture Method Selection -->
        <div class="capture-section mb-3">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="face_method" value="upload" checked> <i class="fas fa-upload"></i> Upload Photo
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="face_method" value="camera"> <i class="fas fa-camera"></i> Use Camera
                </label>
            </div>
        </div>
        
        <div class="capture-section" id="face-upload">
            <h5 class="mb-3"><i class="fas fa-upload"></i> Upload Face Photo</h5>
            <div class="upload-zone" onclick="document.getElementById(\'face_file\').click()">
                <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                <h6>Upload Face Photo</h6>
                <p class="text-muted mb-0">Click to select photo</p>
                <input type="file" id="face_file" style="display: none;" accept="image/*">
            </div>
        </div>
        
        <div class="capture-section" id="face-camera" style="display: none;">
            <h5 class="mb-3"><i class="fas fa-camera"></i> Capture Face Photo</h5>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Position your face within the circular guide
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div style="position: relative; width: 100%;">
                        <video id="face_video" autoplay style="width: 100%; border-radius: 10px; background: #000;"></video>
                        <!-- Face positioning overlay -->
                        <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                            <!-- Dark overlay with cutout -->
                            <defs>
                                <mask id="face-mask">
                                    <rect width="100%" height="100%" fill="white"/>
                                    <circle cx="50%" cy="50%" r="35%" fill="black"/>
                                </mask>
                            </defs>
                            <rect width="100%" height="100%" fill="rgba(0,0,0,0.5)" mask="url(#face-mask)"/>
                            <!-- Guide circle -->
                            <circle cx="50%" cy="50%" r="35%" fill="none" stroke="#28a745" stroke-width="4" stroke-dasharray="10,5"/>
                            <!-- Crosshair -->
                            <line x1="50%" y1="40%" x2="50%" y2="60%" stroke="#28a745" stroke-width="2" opacity="0.5"/>
                            <line x1="40%" y1="50%" x2="60%" y2="50%" stroke="#28a745" stroke-width="2" opacity="0.5"/>
                            <!-- Instructions -->
                            <text x="50%" y="15%" text-anchor="middle" fill="white" font-size="16" font-weight="bold">
                                Position Face Here
                            </text>
                        </svg>
                    </div>
                    <canvas id="face_canvas" style="display: none;"></canvas>
                </div>
                <div class="col-md-4">
                    <div id="face_preview" style="display: none;">
                        <img id="face_captured_img" style="width: 100%; border-radius: 10px;">
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button class="btn btn-success btn-lg" onclick="captureFacePhoto()">
                    <i class="fas fa-camera"></i> Take Photo
                </button>
                <button class="btn btn-warning btn-lg ml-2" onclick="retakeFacePhoto()" style="display: none;" id="face_retake_btn">
                    <i class="fas fa-redo"></i> Retake
                </button>
            </div>
        </div>
    </div>

    <!-- Iris Section -->
    <div class="bio-section" id="iris-section" style="display: none;">
        <!-- Capture Method Selection -->
        <div class="capture-section mb-3">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="iris_method" value="upload" checked> <i class="fas fa-upload"></i> Upload Scan
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="iris_method" value="camera"> <i class="fas fa-camera"></i> Use Camera
                </label>
            </div>
        </div>
        
        <div class="capture-section" id="iris-upload">
            <h5 class="mb-3"><i class="fas fa-eye"></i> Upload Iris Scan</h5>
            <div class="upload-zone" onclick="document.getElementById(\'iris_file\').click()">
                <i class="fas fa-eye fa-4x text-muted mb-3"></i>
                <h6>Upload Iris Scan</h6>
                <p class="text-muted mb-0">Click to select iris scan image</p>
                <input type="file" id="iris_file" style="display: none;" accept="image/*">
            </div>
        </div>
        
        <div class="capture-section" id="iris-camera" style="display: none;">
            <h5 class="mb-3"><i class="fas fa-camera"></i> Capture Iris Scan</h5>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Position eye within the circular guide - Keep eye open and steady
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div style="position: relative; width: 100%;">
                        <video id="iris_video" autoplay style="width: 100%; border-radius: 10px; background: #000;"></video>
                        <!-- Iris positioning overlay -->
                        <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                            <!-- Dark overlay with cutout -->
                            <defs>
                                <mask id="iris-mask">
                                    <rect width="100%" height="100%" fill="white"/>
                                    <circle cx="50%" cy="50%" r="20%" fill="black"/>
                                </mask>
                            </defs>
                            <rect width="100%" height="100%" fill="rgba(0,0,0,0.6)" mask="url(#iris-mask)"/>
                            <!-- Eye guide circle -->
                            <circle cx="50%" cy="50%" r="20%" fill="none" stroke="#ffc107" stroke-width="4" stroke-dasharray="8,4"/>
                            <!-- Inner iris circle -->
                            <circle cx="50%" cy="50%" r="15%" fill="none" stroke="#ffc107" stroke-width="2" stroke-dasharray="5,3" opacity="0.7"/>
                            <!-- Crosshair for centering -->
                            <line x1="50%" y1="35%" x2="50%" y2="65%" stroke="#ffc107" stroke-width="1" opacity="0.5"/>
                            <line x1="35%" y1="50%" x2="65%" y2="50%" stroke="#ffc107" stroke-width="1" opacity="0.5"/>
                            <!-- Corner markers -->
                            <line x1="30%" y1="30%" x2="35%" y2="30%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="30%" y1="30%" x2="30%" y2="35%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="70%" y1="30%" x2="65%" y2="30%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="70%" y1="30%" x2="70%" y2="35%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="30%" y1="70%" x2="35%" y2="70%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="30%" y1="70%" x2="30%" y2="65%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="70%" y1="70%" x2="65%" y2="70%" stroke="#ffc107" stroke-width="3"/>
                            <line x1="70%" y1="70%" x2="70%" y2="65%" stroke="#ffc107" stroke-width="3"/>
                            <!-- Instructions -->
                            <text x="50%" y="12%" text-anchor="middle" fill="white" font-size="14" font-weight="bold">
                                Center Eye in Circle
                            </text>
                            <text x="50%" y="92%" text-anchor="middle" fill="white" font-size="12">
                                Keep eye open and look at center
                            </text>
                        </svg>
                    </div>
                    <canvas id="iris_canvas" style="display: none;"></canvas>
                </div>
                <div class="col-md-4">
                    <div id="iris_preview" style="display: none;">
                        <img id="iris_captured_img" style="width: 100%; border-radius: 10px;">
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button class="btn btn-success btn-lg" onclick="captureIrisPhoto()">
                    <i class="fas fa-camera"></i> Capture Iris
                </button>
                <button class="btn btn-warning btn-lg ml-2" onclick="retakeIrisPhoto()" style="display: none;" id="iris_retake_btn">
                    <i class="fas fa-redo"></i> Retake
                </button>
            </div>
        </div>
    </div>

    <!-- Palm Section -->
    <div class="bio-section" id="palm-section" style="display: none;">
        <!-- Capture Method Selection -->
        <div class="capture-section mb-3">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="palm_method" value="upload" checked> <i class="fas fa-upload"></i> Upload Print
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="palm_method" value="scanner"> <i class="fas fa-scanner"></i> Palm Scanner
                </label>
            </div>
        </div>
        
        <div class="capture-section" id="palm-upload">
            <h5 class="mb-3"><i class="fas fa-hand-paper"></i> Upload Palm Print</h5>
            <div class="upload-zone" onclick="document.getElementById(\'palm_file\').click()">
                <i class="fas fa-hand-paper fa-4x text-muted mb-3"></i>
                <h6>Upload Palm Print</h6>
                <p class="text-muted mb-0">Click to select palm print image</p>
                <input type="file" id="palm_file" style="display: none;" accept="image/*">
            </div>
        </div>
        
        <div class="capture-section" id="palm-scanner" style="display: none;">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Palm Scanner Instructions:</strong>
                <ol class="mb-0 mt-2">
                    <li>Connect your palm scanner device</li>
                    <li>Place palm flat on scanner surface</li>
                    <li>Wait for capture confirmation</li>
                    <li>Scanner will automatically upload the palm print</li>
                </ol>
            </div>
            <div class="text-center p-5" style="background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-hand-paper fa-5x text-muted mb-3"></i>
                <h5>Waiting for Palm Scanner...</h5>
                <p class="text-muted">Place palm on scanner device</p>
                <button class="btn btn-primary" onclick="initPalmScanner()">
                    <i class="fas fa-sync"></i> Initialize Scanner
                </button>
            </div>
        </div>
    </div>

    <!-- Captured Biometrics List -->
    <div class="biometric-list">
        <h5 class="mb-4"><i class="fas fa-list"></i> Captured Biometrics</h5>';

if (!empty($biometrics)) {
    foreach ($biometrics as $bio) {
        $qualityColors = [
            'Excellent' => 'success',
            'Good' => 'info',
            'Fair' => 'warning',
            'Poor' => 'danger'
        ];
        $color = $qualityColors[$bio['capture_quality']] ?? 'secondary';
        
        $content .= '<div class="bio-item">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-fingerprint fa-2x text-primary mr-3"></i>
                            <div>
                                <strong>' . htmlspecialchars($bio['biometric_type']) . '</strong>
                                <div class="text-muted small">' . htmlspecialchars($bio['remarks'] ?? 'No remarks') . '</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-' . $color . '">' . htmlspecialchars($bio['capture_quality']) . '</span>
                            <small class="text-muted">' . date('M d, Y', strtotime($bio['captured_at'])) . '</small>
                            <button class="btn btn-sm btn-info ml-2" onclick="viewBiometric(' . $bio['id'] . ')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBiometric(' . $bio['id'] . ')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>';
    }
} else {
    $content .= '<div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No biometrics captured yet</p>
                </div>';
}

$content .= '
    </div>
</div>

<!-- Capture Modal -->
<div class="modal fade" id="captureModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-fingerprint"></i> Capture Fingerprint</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="captureForm" enctype="multipart/form-data">
                ' . csrf_field() . '
                <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                <input type="hidden" name="biometric_type" value="Fingerprint">
                <input type="hidden" name="finger_position" id="finger_position">
                <input type="hidden" name="cropped_image" id="cropped_image">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Image Upload & Crop Area -->
                            <div id="upload-area">
                                <div class="upload-zone" onclick="document.getElementById(\'modal_file\').click()" style="min-height: 400px;">
                                    <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                                    <h6>Click to Upload Fingerprint Image</h6>
                                    <p class="text-muted mb-0">Supports: JPG, PNG, BMP (Max 5MB)</p>
                                    <input type="file" class="form-control-file" name="fingerprint_file" id="modal_file" accept="image/*" style="display: none;" required>
                                </div>
                            </div>
                            
                            <!-- Crop Area (hidden initially) -->
                            <div id="crop-area" style="display: none;">
                                <div class="mb-3">
                                    <img id="crop-image" style="max-width: 100%;">
                                </div>
                                <div class="btn-toolbar mb-3" role="toolbar">
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropperRotateLeft()">
                                            <i class="fas fa-undo"></i> Rotate Left
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropperRotateRight()">
                                            <i class="fas fa-redo"></i> Rotate Right
                                        </button>
                                    </div>
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropperZoomIn()">
                                            <i class="fas fa-search-plus"></i> Zoom In
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropperZoomOut()">
                                            <i class="fas fa-search-minus"></i> Zoom Out
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="cropperReset()">
                                            <i class="fas fa-times"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Preview & Settings -->
                            <div class="card">
                                <div class="card-header">
                                    <strong>Preview & Settings</strong>
                                </div>
                                <div class="card-body">
                                    <div id="preview-container" class="mb-3" style="display: none;">
                                        <label class="small text-muted">Cropped Preview:</label>
                                        <div id="preview-box" style="width: 100%; height: 200px; border: 2px solid #dee2e6; border-radius: 5px; overflow: hidden; background: #f8f9fa;"></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Quality Assessment</label>
                                        <select class="form-control" name="capture_quality" required>
                                            <option value="">-- Select Quality --</option>
                                            <option value="Excellent">Excellent - Clear ridges</option>
                                            <option value="Good">Good - Mostly clear</option>
                                            <option value="Fair">Fair - Usable</option>
                                            <option value="Poor">Poor - Needs recapture</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea class="form-control" name="remarks" rows="3" placeholder="Any notes about the capture..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="crop-btn" style="display: none;" onclick="applyCrop()">
                        <i class="fas fa-crop"></i> Apply Crop
                    </button>
                    <button type="submit" class="btn btn-primary" id="save-btn" disabled>
                        <i class="fas fa-save"></i> Save Fingerprint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
';

$scripts = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper = null;

$(document).ready(function() {
    // Tab switching
    $(".bio-tab").click(function() {
        $(".bio-tab").removeClass("active");
        $(this).addClass("active");
        
        const type = $(this).data("type");
        $(".bio-section").hide();
        $("#" + type + "-section").show();
    });

    // File upload handler with cropper
    $("#modal_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert("File too large. Maximum 5MB allowed.");
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                // Hide upload area, show crop area
                $("#upload-area").hide();
                $("#crop-area").show();
                $("#preview-container").show();
                $("#crop-btn").show();
                
                // Initialize cropper
                const image = document.getElementById("crop-image");
                image.src = event.target.result;
                
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(image, {
                    aspectRatio: NaN, // Free aspect ratio
                    viewMode: 1,
                    dragMode: "move",
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    preview: "#preview-box"
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Capture form submission
    $("#captureForm").submit(function(e) {
        e.preventDefault();
        
        // Get cropped image data
        const croppedData = $("#cropped_image").val();
        if (!croppedData) {
            alert("Please apply crop before saving");
            return;
        }
        
        // Convert base64 to blob
        fetch(croppedData)
            .then(res => res.blob())
            .then(blob => {
                const formData = new FormData();
                formData.append("_token", $("input[name=_token]").val());
                formData.append("suspect_id", $("input[name=suspect_id]").val());
                formData.append("biometric_type", $("input[name=biometric_type]").val());
                formData.append("finger_position", $("#finger_position").val());
                formData.append("capture_quality", $("select[name=capture_quality]").val());
                formData.append("remarks", $("#finger_position").val() + " - " + $("textarea[name=remarks]").val());
                formData.append("fingerprint_file", blob, "fingerprint.png");
                
                $.ajax({
                    url: "' . url('/suspects/' . $suspectId . '/biometrics') . '",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert("Fingerprint saved successfully!");
                            location.reload();
                        } else {
                            alert("Error: " + (response.message || "Failed to save"));
                        }
                    },
                    error: function() {
                        alert("Failed to save fingerprint");
                    }
                });
            });
    });
    
    // Reset modal on close
    $("#captureModal").on("hidden.bs.modal", function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $("#upload-area").show();
        $("#crop-area").hide();
        $("#preview-container").hide();
        $("#crop-btn").hide();
        $("#save-btn").prop("disabled", true);
        $("#cropped_image").val("");
        $("#modal_file").val("");
    });
    
    // Method switching for fingerprint
    $("input[name=fp_method]").on("change", function() {
        const method = $(this).val();
        if (method === "manual") {
            $("#manual-fingerprint").show();
            $("#scanner-interface").hide();
        } else {
            $("#manual-fingerprint").hide();
            $("#scanner-interface").show();
        }
    });
    
    // Method switching for face
    $("input[name=face_method]").on("change", function() {
        const method = $(this).val();
        if (method === "upload") {
            $("#face-upload").show();
            $("#face-camera").hide();
            stopFaceCamera();
        } else {
            $("#face-upload").hide();
            $("#face-camera").show();
            startFaceCamera();
        }
    });
    
    // Method switching for iris
    $("input[name=iris_method]").on("change", function() {
        const method = $(this).val();
        if (method === "upload") {
            $("#iris-upload").show();
            $("#iris-camera").hide();
            stopIrisCamera();
        } else {
            $("#iris-upload").hide();
            $("#iris-camera").show();
            startIrisCamera();
        }
    });
    
    // Method switching for palm
    $("input[name=palm_method]").on("change", function() {
        const method = $(this).val();
        if (method === "upload") {
            $("#palm-upload").show();
            $("#palm-scanner").hide();
        } else {
            $("#palm-upload").hide();
            $("#palm-scanner").show();
        }
    });
    
    // Bulk fingerprint upload handler
    $("#bulk_sheet_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById("bulk_sheet_preview");
                img.src = event.target.result;
                img.onload = function() {
                    // Show step 2 first
                    $("#bulk-upload-step1").hide();
                    $("#bulk-upload-step2").show();
                    
                    // Setup canvas overlay - wait for DOM to update
                    setTimeout(function() {
                        const canvas = document.getElementById("bulk_marking_canvas");
                        const img = document.getElementById("bulk_sheet_preview");
                        
                        // Wait for image to have dimensions
                        if (img.offsetWidth === 0 || img.offsetHeight === 0) {
                            console.log("Image not ready, waiting...");
                            setTimeout(arguments.callee, 50);
                            return;
                        }
                        
                        // Set canvas dimensions to match displayed image
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        canvas.style.width = img.offsetWidth + "px";
                        canvas.style.height = img.offsetHeight + "px";
                        
                        console.log("Canvas setup:", {
                            canvasWidth: canvas.width,
                            canvasHeight: canvas.height,
                            styleWidth: canvas.style.width,
                            styleHeight: canvas.style.height,
                            imgWidth: img.offsetWidth,
                            imgHeight: img.offsetHeight,
                            naturalWidth: img.naturalWidth,
                            naturalHeight: img.naturalHeight
                        });
                    }, 200);
                };
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Finger selection for marking
    $(".finger-map-item").click(function() {
        $(".finger-map-item").removeClass("active");
        $(this).addClass("active");
        window.selectedFinger = $(this).data("finger");
        console.log("Selected finger:", window.selectedFinger);
    });
    
    // Canvas drag-to-draw box for fingerprint selection
    let isDrawing = false;
    let startX, startY;
    
    $(document).on("mousedown", "#bulk_marking_canvas", function(e) {
        e.preventDefault();
        console.log("Mousedown on canvas");
        
        if (!window.selectedFinger) {
            alert("Please select a finger from the list first");
            return;
        }
        
        const canvas = this;
        const rect = canvas.getBoundingClientRect();
        
        console.log("Canvas dimensions:", {
            width: canvas.width,
            height: canvas.height,
            offsetWidth: canvas.offsetWidth,
            offsetHeight: canvas.offsetHeight,
            rectWidth: rect.width,
            rectHeight: rect.height
        });
        
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        isDrawing = true;
        startX = (e.clientX - rect.left) * scaleX;
        startY = (e.clientY - rect.top) * scaleY;
        
        console.log("Started drawing box at:", startX, startY, "Scale:", scaleX, scaleY);
    });
    
    $(document).on("mousemove", "#bulk_marking_canvas", function(e) {
        if (!isDrawing) return;
        
        const canvas = this;
        const ctx = canvas.getContext("2d");
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const currentX = (e.clientX - rect.left) * scaleX;
        const currentY = (e.clientY - rect.top) * scaleY;
        
        // Redraw image and existing boxes
        redrawCanvas();
        
        // Draw current box being drawn
        const width = currentX - startX;
        const height = currentY - startY;
        
        ctx.strokeStyle = "#ffc107";
        ctx.lineWidth = 3;
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(startX, startY, width, height);
        ctx.setLineDash([]);
        
        // Draw semi-transparent fill
        ctx.fillStyle = "rgba(255, 193, 7, 0.2)";
        ctx.fillRect(startX, startY, width, height);
    });
    
    $(document).on("mouseup", "#bulk_marking_canvas", function(e) {
        if (!isDrawing) return;
        
        const canvas = this;
        const ctx = canvas.getContext("2d");
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const endX = (e.clientX - rect.left) * scaleX;
        const endY = (e.clientY - rect.top) * scaleY;
        
        isDrawing = false;
        
        // Calculate box dimensions
        const x = Math.min(startX, endX);
        const y = Math.min(startY, endY);
        const width = Math.abs(endX - startX);
        const height = Math.abs(endY - startY);
        
        // Minimum box size check
        if (width < 20 || height < 20) {
            alert("Box too small. Please draw a larger box around the fingerprint.");
            redrawCanvas();
            return;
        }
        
        console.log("Box drawn:", { x, y, width, height, finger: window.selectedFinger });
        
        // Check if this finger already has a box (redrawing)
        const wasAlreadyMarked = window.fingerMarks.hasOwnProperty(window.selectedFinger);
        
        // Store box coordinates (replaces old box if exists)
        window.fingerMarks[window.selectedFinger] = { x, y, width, height };
        
        // Redraw all boxes
        redrawCanvas();
        
        // Update list (only if not already marked)
        if (!wasAlreadyMarked) {
            const item = $(".finger-map-item[data-finger=\'" + window.selectedFinger + "\']");
            item.find("i").removeClass("text-muted").addClass("text-success");
            item.find(".badge").removeClass("badge-secondary").addClass("badge-success").text("Marked");
            
            // Check if all marked
            if (Object.keys(window.fingerMarks).length === 10) {
                $("#process-bulk-btn").prop("disabled", false);
            }
            
            // Auto-select next finger (only for new marks)
            const nextItem = item.next(".finger-map-item");
            if (nextItem.length) {
                nextItem.click();
            }
        } else {
            console.log("Redrew box for:", window.selectedFinger);
        }
    });
    
    // Cancel drawing if mouse leaves canvas
    $(document).on("mouseleave", "#bulk_marking_canvas", function() {
        if (isDrawing) {
            isDrawing = false;
            redrawCanvas();
        }
    });
});

// Bulk fingerprint mapping - global scope
window.fingerMarks = {};
window.selectedFinger = null;

// Redraw canvas with all marked boxes
function redrawCanvas() {
    const canvas = document.getElementById("bulk_marking_canvas");
    if (!canvas) return;
    
    const ctx = canvas.getContext("2d");
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw all marked boxes
    for (const [finger, box] of Object.entries(window.fingerMarks)) {
        // Draw box
        ctx.strokeStyle = "#28a745";
        ctx.lineWidth = 3;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
        
        // Draw semi-transparent fill
        ctx.fillStyle = "rgba(40, 167, 69, 0.2)";
        ctx.fillRect(box.x, box.y, box.width, box.height);
        
        // Draw label
        ctx.fillStyle = "#28a745";
        ctx.font = "bold 16px Arial";
        const label = finger.split(" ")[1][0]; // First letter of finger name
        ctx.fillText(label, box.x + 5, box.y + 20);
    }
}

// Camera streams
let faceStream = null;
let irisStream = null;

// Face camera functions
function startFaceCamera() {
    navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
        .then(function(stream) {
            faceStream = stream;
            document.getElementById("face_video").srcObject = stream;
        })
        .catch(function(err) {
            alert("Camera access denied: " + err.message);
        });
}

function stopFaceCamera() {
    if (faceStream) {
        faceStream.getTracks().forEach(track => track.stop());
        faceStream = null;
    }
}

function captureFacePhoto() {
    const video = document.getElementById("face_video");
    const canvas = document.getElementById("face_canvas");
    const context = canvas.getContext("2d");
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    const imageData = canvas.toDataURL("image/png");
    document.getElementById("face_captured_img").src = imageData;
    $("#face_preview").show();
    $("#face_retake_btn").show();
    
    // TODO: Save to form or upload
    alert("Face photo captured! Implement save functionality.");
}

function retakeFacePhoto() {
    $("#face_preview").hide();
    $("#face_retake_btn").hide();
}

// Iris camera functions
function startIrisCamera() {
    navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
        .then(function(stream) {
            irisStream = stream;
            document.getElementById("iris_video").srcObject = stream;
        })
        .catch(function(err) {
            alert("Camera access denied: " + err.message);
        });
}

function stopIrisCamera() {
    if (irisStream) {
        irisStream.getTracks().forEach(track => track.stop());
        irisStream = null;
    }
}

function captureIrisPhoto() {
    const video = document.getElementById("iris_video");
    const canvas = document.getElementById("iris_canvas");
    const context = canvas.getContext("2d");
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    const imageData = canvas.toDataURL("image/png");
    document.getElementById("iris_captured_img").src = imageData;
    $("#iris_preview").show();
    $("#iris_retake_btn").show();
    
    // TODO: Save to form or upload
    alert("Iris scan captured! Implement save functionality.");
}

function retakeIrisPhoto() {
    $("#iris_preview").hide();
    $("#iris_retake_btn").hide();
}

// Process bulk fingerprints
function processBulkFingerprints() {
    if (Object.keys(window.fingerMarks).length !== 10) {
        alert("Please mark all 10 fingerprints before processing");
        return;
    }
    
    const img = document.getElementById("bulk_sheet_preview");
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    
    console.log("Starting bulk processing with", Object.keys(window.fingerMarks).length, "fingerprints");
    
    // Show progress
    $("#process-bulk-btn").prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Processing...");
    
    // Process each marked fingerprint
    let processed = 0;
    let errors = 0;
    
    for (const [finger, box] of Object.entries(window.fingerMarks)) {
        console.log("Processing", finger, "with box:", box);
        
        // Extract fingerprint region from drawn box
        canvas.width = box.width;
        canvas.height = box.height;
        
        // Draw the selected region from the image
        ctx.drawImage(img, box.x, box.y, box.width, box.height, 0, 0, box.width, box.height);
        
        // Convert to blob and upload
        canvas.toBlob(function(blob) {
            console.log("Created blob for", finger, "size:", blob.size);
            
            // Get CSRF token - correct name is csrf_token
            let csrfToken = $("input[name=csrf_token]").first().val();
            if (!csrfToken) {
                csrfToken = $("input[name=_token]").first().val();
            }
            
            console.log("CSRF token found:", csrfToken ? "Yes" : "No", "Length:", csrfToken ? csrfToken.length : 0);
            
            const formData = new FormData();
            formData.append("csrf_token", csrfToken);
            formData.append("suspect_id", "' . $suspectId . '");
            formData.append("biometric_type", "Fingerprint");
            formData.append("finger_position", finger);
            formData.append("capture_quality", "Good");
            formData.append("remarks", finger + " - Bulk Upload");
            formData.append("fingerprint_file", blob, finger.replace(" ", "_") + ".png");
            
            $.ajax({
                url: "' . url('/suspects/' . $suspectId . '/biometrics') . '",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log("Success response for", finger, ":", response);
                    processed++;
                    $("#process-bulk-btn").html("<i class=\"fas fa-spinner fa-spin\"></i> Processing " + processed + "/10...");
                    if (processed + errors === 10) {
                        console.log("Final results - Processed:", processed, "Errors:", errors);
                        setTimeout(function() {
                            if (errors > 0) {
                                alert("Processing complete! " + processed + " succeeded, " + errors + " failed. Check console for details before clicking OK.");
                            } else {
                                alert("All 10 fingerprints processed successfully!");
                            }
                            location.reload();
                        }, 500);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Failed to process", finger, ":", status, error);
                    console.error("Response text:", xhr.responseText);
                    console.error("Status code:", xhr.status);
                    errors++;
                    if (processed + errors === 10) {
                        console.log("Final results - Processed:", processed, "Errors:", errors);
                        setTimeout(function() {
                            alert("Processing complete! " + processed + " succeeded, " + errors + " failed. Check console for details before clicking OK.");
                            location.reload();
                        }, 500);
                    }
                }
            });
        }, "image/png");
    }
}

// Reset bulk upload
function resetBulkUpload() {
    fingerMarks = {};
    $("#bulk-upload-step1").show();
    $("#bulk-upload-step2").hide();
    $("#bulk_sheet_file").val("");
    $("#process-bulk-btn").prop("disabled", true);
    
    // Reset list
    $(".finger-map-item").each(function() {
        $(this).find("i").removeClass("text-success").addClass("text-muted");
        $(this).find(".badge").removeClass("badge-success").addClass("badge-secondary").text("Not marked");
    });
}

// Scanner functions (placeholder for hardware integration)
function initScanner() {
    alert("Fingerprint scanner initialization...\n\nThis requires hardware integration with your fingerprint scanner device.\nContact your system administrator for scanner setup.");
}

function initPalmScanner() {
    alert("Palm scanner initialization...\n\nThis requires hardware integration with your palm scanner device.\nContact your system administrator for scanner setup.");
}

function captureFingerprint(fingerName) {
    $("#finger_position").val(fingerName);
    $("#captureModal .modal-title").html("<i class=\"fas fa-fingerprint\"></i> Capture " + fingerName);
    $("#captureModal").modal("show");
}

// Cropper control functions
function cropperRotateLeft() {
    if (cropper) cropper.rotate(-90);
}

function cropperRotateRight() {
    if (cropper) cropper.rotate(90);
}

function cropperZoomIn() {
    if (cropper) cropper.zoom(0.1);
}

function cropperZoomOut() {
    if (cropper) cropper.zoom(-0.1);
}

function cropperReset() {
    if (cropper) {
        cropper.reset();
    }
}

function applyCrop() {
    if (!cropper) return;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        width: 800,
        height: 800,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: "high"
    });
    
    // Convert to base64
    const croppedImage = canvas.toDataURL("image/png");
    $("#cropped_image").val(croppedImage);
    
    // Enable save button
    $("#save-btn").prop("disabled", false);
    
    // Show success message
    alert("Crop applied! You can now save the fingerprint.");
}

function captureFingerprint(fingerName) {
    $("#finger_position").val(fingerName);
    $("#captureModal .modal-title").html("<i class=\"fas fa-fingerprint\"></i> Capture " + fingerName);
    $("#captureModal").modal("show");
}

function viewBiometric(id) {
    window.open("' . url('/biometrics/') . '" + id, "_blank");
}

function deleteBiometric(id) {
    if (confirm("Delete this biometric record?")) {
        $.post("' . url('/biometrics/') . '" + id + "/delete", {
            csrf_token: $("input[name=csrf_token]").first().val()
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert("Failed to delete");
            }
        });
    }
}
</script>
';

// Pass biometrics data to JavaScript safely
// Remove binary data that can't be JSON encoded
$biometricsForExport = array_map(function($bio) {
    // Remove binary data field
    unset($bio['biometric_data']);
    return $bio;
}, $biometrics);

$biometricsJson = json_encode($biometricsForExport, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($biometricsJson === false) {
    error_log("JSON encoding failed: " . json_last_error_msg());
    $biometricsJson = '[]';
}

$content .= '<script>
// Biometrics data from PHP
var biometricsData = ' . $biometricsJson . ';
console.log("Biometrics data loaded:", biometricsData);
console.log("PHP says there are ' . count($biometrics) . ' biometrics");

// Export fingerprint sheet with all 10 fingerprints
function exportFingerprintSheet() {
    const suspectName = "' . htmlspecialchars($suspectName) . '";
    const suspectId = "' . $suspectId . '";
    
    console.log("Exporting fingerprints:", biometricsData);
    
    // Create canvas for fingerprint sheet (A4 size at 96 DPI)
    const canvas = document.createElement("canvas");
    canvas.width = 794;  // A4 width in pixels at 96 DPI
    canvas.height = 1123; // A4 height in pixels at 96 DPI
    const ctx = canvas.getContext("2d");
    
    // White background
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Header
    ctx.fillStyle = "#000000";
    ctx.font = "bold 24px Arial";
    ctx.textAlign = "center";
    ctx.fillText("FINGERPRINT RECORD SHEET", canvas.width / 2, 40);
    
    ctx.font = "16px Arial";
    ctx.fillText("Ghana Police Service - GHPIMS", canvas.width / 2, 70);
    
    // Suspect info
    ctx.font = "14px Arial";
    ctx.textAlign = "left";
    ctx.fillText("Suspect: " + suspectName, 50, 110);
    ctx.fillText("Date: " + new Date().toLocaleDateString(), 50, 135);
    ctx.fillText("Suspect ID: " + suspectId, 450, 110);
    
    // Border
    ctx.strokeStyle = "#000000";
    ctx.lineWidth = 2;
    ctx.strokeRect(30, 90, canvas.width - 60, canvas.height - 120);
    
    // Use biometrics data from PHP
    const fingerprints = biometricsData;
    console.log("Total fingerprints in data:", fingerprints.length);
    console.log("Fingerprints:", fingerprints);
            
    // Define fingerprint positions (2 rows of 5)
    const positions = [
                // Top row - Left hand
                {name: "Left Thumb", x: 60, y: 180},
                {name: "Left Index", x: 200, y: 180},
                {name: "Left Middle", x: 340, y: 180},
                {name: "Left Ring", x: 480, y: 180},
                {name: "Left Little", x: 620, y: 180},
                // Bottom row - Right hand
                {name: "Right Thumb", x: 60, y: 580},
                {name: "Right Index", x: 200, y: 580},
                {name: "Right Middle", x: 340, y: 580},
                {name: "Right Ring", x: 480, y: 580},
                {name: "Right Little", x: 620, y: 580}
            ];
            
            let loaded = 0;
            const total = positions.length;
            
            positions.forEach(pos => {
                // Find matching fingerprint
                console.log("Looking for:", pos.name);
                const fp = fingerprints.find(f => {
                    console.log("Checking:", f.biometric_type, f.remarks);
                    return f.biometric_type === "Fingerprint" && 
                           (f.remarks === pos.name || (f.remarks && f.remarks.startsWith(pos.name)));
                });
                
                console.log("Found for", pos.name, ":", fp ? "Yes" : "No", fp);
                
                if (fp && fp.file_path) {
                    const img = new Image();
                    img.crossOrigin = "anonymous";
                    img.onload = function() {
                        // Draw fingerprint box
                        ctx.strokeStyle = "#333333";
                        ctx.lineWidth = 1;
                        ctx.strokeRect(pos.x, pos.y, 120, 150);
                        
                        // Draw fingerprint image (scaled to fit)
                        const scale = Math.min(115 / img.width, 115 / img.height);
                        const w = img.width * scale;
                        const h = img.height * scale;
                        const offsetX = (120 - w) / 2;
                        const offsetY = (150 - h - 25) / 2;
                        
                        ctx.drawImage(img, pos.x + offsetX, pos.y + offsetY, w, h);
                        
                        // Draw label
                        ctx.fillStyle = "#000000";
                        ctx.font = "bold 10px Arial";
                        ctx.textAlign = "center";
                        ctx.fillText(pos.name, pos.x + 60, pos.y + 140);
                        
                        loaded++;
                        if (loaded === total) {
                            downloadCanvas();
                        }
                    };
                    img.onerror = function() {
                        // Draw placeholder if image fails
                        ctx.strokeStyle = "#cccccc";
                        ctx.strokeRect(pos.x, pos.y, 120, 150);
                        ctx.fillStyle = "#999999";
                        ctx.font = "12px Arial";
                        ctx.textAlign = "center";
                        ctx.fillText("No Image", pos.x + 60, pos.y + 75);
                        ctx.font = "bold 10px Arial";
                        ctx.fillText(pos.name, pos.x + 60, pos.y + 140);
                        
                        loaded++;
                        if (loaded === total) {
                            downloadCanvas();
                        }
                    };
                    // Construct correct image path
                    const baseUrl = "' . url('/') . '";
                    // Remove /public from the URL if present
                    const cleanBaseUrl = baseUrl.replace("/public", "");
                    img.src = cleanBaseUrl + fp.file_path;
                } else {
                    // Draw empty box
                    ctx.strokeStyle = "#cccccc";
                    ctx.strokeRect(pos.x, pos.y, 120, 150);
                    ctx.fillStyle = "#999999";
                    ctx.font = "12px Arial";
                    ctx.textAlign = "center";
                    ctx.fillText("Not Captured", pos.x + 60, pos.y + 75);
                    ctx.font = "bold 10px Arial";
                    ctx.fillText(pos.name, pos.x + 60, pos.y + 140);
                    
                    loaded++;
                    if (loaded === total) {
                        downloadCanvas();
                    }
                }
            });
            
    function downloadCanvas() {
        // Add footer
        ctx.fillStyle = "#666666";
        ctx.font = "10px Arial";
        ctx.textAlign = "center";
        ctx.fillText("Generated by GHPIMS on " + new Date().toLocaleString(), canvas.width / 2, canvas.height - 20);
        
        // Download
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "Fingerprints_" + suspectName.replace(/\s+/g, "_") + "_" + Date.now() + ".png";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, "image/png");
    }
}
</script>
';

include __DIR__ . '/../layouts/main.php';
?>
