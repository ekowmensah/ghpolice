<?php
// Get suspect details
$suspectId = $suspect['id'] ?? 0;
$personId = $suspect['person_id'] ?? 0;
$suspectName = $suspect['full_name'] ?? 'Unknown';

// Get existing biometrics
$db = \App\Config\Database::getConnection();
$stmt = $db->prepare("
    SELECT * FROM suspect_biometrics 
    WHERE suspect_id = ? 
    ORDER BY captured_at DESC
");
$stmt->execute([$suspectId]);
$biometrics = $stmt->fetchAll();

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-fingerprint"></i> Biometric Data Collection - ' . htmlspecialchars($suspectName) . '</h3>
            </div>
            <div class="card-body">
                <!-- Biometric Type Selection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-primary active">
                                <input type="radio" name="biometric_type" value="Fingerprint" checked> 
                                <i class="fas fa-fingerprint"></i> Fingerprint
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="biometric_type" value="Face"> 
                                <i class="fas fa-user"></i> Face
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="biometric_type" value="Iris"> 
                                <i class="fas fa-eye"></i> Iris
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="biometric_type" value="Palm Print"> 
                                <i class="fas fa-hand-paper"></i> Palm Print
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Fingerprint Capture Section -->
                <div id="fingerprint_section" class="biometric-section" style="display: none;">
                
                <!-- Face Capture Section -->
                <div id="face_section" class="biometric-section" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-camera"></i> Use camera or upload photo for facial recognition
                    </div>
                    <form id="faceForm" enctype="multipart/form-data">
                        ' . csrf_field() . '
                        <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                        <input type="hidden" name="biometric_type" value="Face">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="face_file" name="fingerprint_file" accept="image/*" required>
                                    <label class="custom-file-label" for="face_file">Choose photo...</label>
                                </div>
                                <div id="face_preview" class="mt-3" style="display: none;">
                                    <img id="face_img" src="" style="max-width: 100%; border-radius: 10px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control mb-2" name="capture_quality" required>
                                    <option value="">Quality</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                                <input type="hidden" name="finger_position" value="Face Photo">
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save"></i> Save Face Photo</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Iris Capture Section -->
                <div id="iris_section" class="biometric-section" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-eye"></i> Capture iris scan for biometric identification
                    </div>
                    <form id="irisForm" enctype="multipart/form-data">
                        ' . csrf_field() . '
                        <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                        <input type="hidden" name="biometric_type" value="Iris">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control mb-2" name="finger_position" required>
                                    <option value="">Select Eye</option>
                                    <option value="Left Eye">Left Eye</option>
                                    <option value="Right Eye">Right Eye</option>
                                    <option value="Both Eyes">Both Eyes</option>
                                </select>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="iris_file" name="fingerprint_file" accept="image/*" required>
                                    <label class="custom-file-label" for="iris_file">Choose scan...</label>
                                </div>
                                <div id="iris_preview" class="mt-3" style="display: none;">
                                    <img id="iris_img" src="" style="max-width: 100%; border-radius: 10px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control mb-2" name="capture_quality" required>
                                    <option value="">Quality</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save"></i> Save Iris Scan</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Palm Print Capture Section -->
                <div id="palmprint_section" class="biometric-section" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-hand-paper"></i> Capture palm print for identification
                    </div>
                    <form id="palmprintForm" enctype="multipart/form-data">
                        ' . csrf_field() . '
                        <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                        <input type="hidden" name="biometric_type" value="Palm Print">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control mb-2" name="finger_position" required>
                                    <option value="">Select Palm</option>
                                    <option value="Left Palm">Left Palm</option>
                                    <option value="Right Palm">Right Palm</option>
                                </select>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="palm_file" name="fingerprint_file" accept="image/*" required>
                                    <label class="custom-file-label" for="palm_file">Choose scan...</label>
                                </div>
                                <div id="palm_preview" class="mt-3" style="display: none;">
                                    <img id="palm_img" src="" style="max-width: 100%; border-radius: 10px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control mb-2" name="capture_quality" required>
                                    <option value="">Quality</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save"></i> Save Palm Print</button>
                            </div>
                        </div>
                    </form>
                </div>
                    <!-- Capture Method Tabs -->
                    <ul class="nav nav-tabs mb-3" id="captureMethodTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="single-tab" data-toggle="tab" href="#single-capture" role="tab">
                                <i class="fas fa-hand-pointer"></i> Single Finger
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bulk-tab" data-toggle="tab" href="#bulk-capture" role="tab">
                                <i class="fas fa-hands"></i> Bulk Upload (10 Fingers)
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="captureMethodTabContent">
                        <!-- Single Finger Capture Tab -->
                        <div class="tab-pane fade show active" id="single-capture" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-hand-pointer"></i> Capture Fingerprints</h3>
                                </div>
                                <div class="card-body">
                                    <form id="fingerprintForm" enctype="multipart/form-data">
                                        ' . csrf_field() . '
                                        <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                                        <input type="hidden" name="person_id" value="' . $personId . '">
                                        <input type="hidden" name="biometric_type" value="Fingerprint">
                                        
                                        <!-- Finger Selection -->
                                        <div class="form-group">
                                            <label>Select Finger <span class="text-danger">*</span></label>
                                            <select class="form-control" name="finger_position" required>
                                                <option value="">-- Select Finger --</option>
                                                <optgroup label="Right Hand">
                                                    <option value="Right Thumb">Right Thumb</option>
                                                    <option value="Right Index">Right Index Finger</option>
                                                    <option value="Right Middle">Right Middle Finger</option>
                                                    <option value="Right Ring">Right Ring Finger</option>
                                                    <option value="Right Little">Right Little Finger</option>
                                                </optgroup>
                                                <optgroup label="Left Hand">
                                                    <option value="Left Thumb">Left Thumb</option>
                                                    <option value="Left Index">Left Index Finger</option>
                                                    <option value="Left Middle">Left Middle Finger</option>
                                                    <option value="Left Ring">Left Ring Finger</option>
                                                    <option value="Left Little">Left Little Finger</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        <!-- Capture Method -->
                                        <div class="form-group">
                                            <label>Capture Method</label>
                                            <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                                <label class="btn btn-outline-secondary active">
                                                    <input type="radio" name="capture_method" value="scanner" checked> 
                                                    <i class="fas fa-scanner"></i> Scanner
                                                </label>
                                                <label class="btn btn-outline-secondary">
                                                    <input type="radio" name="capture_method" value="upload"> 
                                                    <i class="fas fa-upload"></i> Upload Image
                                                </label>
                                                <label class="btn btn-outline-secondary">
                                                    <input type="radio" name="capture_method" value="camera"> 
                                                    <i class="fas fa-camera"></i> Camera
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Scanner Interface -->
                                        <div id="scanner_interface" class="capture-interface">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> <strong>Scanner Instructions:</strong>
                                                <ol class="mb-0 mt-2">
                                                    <li>Ensure fingerprint scanner is connected</li>
                                                    <li>Clean the scanner surface</li>
                                                    <li>Place finger firmly on scanner</li>
                                                    <li>Click "Capture from Scanner" button</li>
                                                </ol>
                                            </div>
                                            <div class="form-group">
                                                <label>Scanner Device</label>
                                                <select class="form-control" name="capture_device">
                                                    <option value="Digital Persona U.are.U 4500">Digital Persona U.are.U 4500</option>
                                                    <option value="Suprema RealScan-G10">Suprema RealScan-G10</option>
                                                    <option value="Futronic FS88">Futronic FS88</option>
                                                    <option value="Other">Other Scanner</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-block" onclick="captureFromScanner()">
                                                <i class="fas fa-fingerprint"></i> Capture from Scanner
                                            </button>
                                        </div>

                                        <!-- Upload Interface -->
                                        <div id="upload_interface" class="capture-interface" style="display: none;">
                                            <div class="form-group">
                                                <label>Upload Fingerprint Image</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="fingerprint_file" name="fingerprint_file" accept="image/*,.bmp,.png,.jpg,.jpeg">
                                                    <label class="custom-file-label" for="fingerprint_file">Choose file...</label>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Accepted formats: BMP, PNG, JPG (Max 5MB)
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Camera Interface -->
                                        <div id="camera_interface" class="capture-interface" style="display: none;">
                                            <div class="form-group">
                                                <video id="camera_preview" width="100%" height="300" autoplay style="border: 2px solid #ccc; border-radius: 5px;"></video>
                                                <canvas id="camera_canvas" style="display: none;"></canvas>
                                            </div>
                                            <button type="button" class="btn btn-success btn-block" onclick="captureFromCamera()">
                                                <i class="fas fa-camera"></i> Take Photo
                                            </button>
                                        </div>

                                        <!-- Preview Area -->
                                        <div id="preview_area" style="display: none;" class="mt-3">
                                            <label>Preview</label>
                                            <div class="text-center p-3" style="border: 2px dashed #ccc; border-radius: 5px;">
                                                <img id="fingerprint_preview" src="" alt="Fingerprint Preview" style="max-width: 100%; max-height: 300px;">
                                            </div>
                                        </div>

                                        <!-- Quality Assessment -->
                                        <div class="form-group mt-3">
                                            <label>Capture Quality <span class="text-danger">*</span></label>
                                            <select class="form-control" name="capture_quality" required>
                                                <option value="">-- Assess Quality --</option>
                                                <option value="Excellent">Excellent - Clear ridges, no smudging</option>
                                                <option value="Good">Good - Mostly clear, minor issues</option>
                                                <option value="Fair">Fair - Usable but not ideal</option>
                                                <option value="Poor">Poor - Requires recapture</option>
                                            </select>
                                        </div>

                                        <!-- Remarks -->
                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Any notes about the capture..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-save"></i> Save Fingerprint
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Captured Biometrics List -->
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-list"></i> Captured Biometrics</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Quality</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

foreach ($biometrics as $bio) {
    $qualityClass = [
        'Excellent' => 'success',
        'Good' => 'info',
        'Fair' => 'warning',
        'Poor' => 'danger'
    ][$bio['capture_quality']] ?? 'secondary';
    
    $content .= '<tr>
                    <td><i class="fas fa-fingerprint"></i> ' . htmlspecialchars($bio['biometric_type']) . '</td>
                    <td><span class="badge badge-' . $qualityClass . '">' . htmlspecialchars($bio['capture_quality']) . '</span></td>
                    <td>' . date('M d, Y', strtotime($bio['captured_at'])) . '</td>
                    <td>
                        <button class="btn btn-xs btn-info" onclick="viewBiometric(' . $bio['id'] . ')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-xs btn-danger" onclick="deleteBiometric(' . $bio['id'] . ')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>';
}

if (empty($biometrics)) {
    $content .= '<tr><td colspan="4" class="text-center text-muted">No biometrics captured yet</td></tr>';
}

$content .= '
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>
                        
                        <!-- Bulk Upload Tab -->
                        <div class="tab-pane fade" id="bulk-capture" role="tabpanel">
                                    <style>
                                        .finger-icon {
                                            font-size: 48px;
                                            cursor: pointer;
                                            transition: all 0.3s;
                                            display: inline-block;
                                            margin: 5px;
                                        }
                                        .finger-icon.captured {
                                            color: #28a745;
                                            text-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
                                        }
                                        .finger-icon.not-captured {
                                            color: #6c757d;
                                            opacity: 0.4;
                                        }
                                        .finger-icon:hover {
                                            transform: scale(1.1);
                                        }
                                        .hand-container {
                                            text-align: center;
                                            padding: 20px;
                                        }
                                        .finger-label {
                                            font-size: 10px;
                                            display: block;
                                            margin-top: -10px;
                                        }
                                    </style>
                                    
                                    <div class="row">
                                        <div class="col-md-6 hand-container">
                                            <h5><i class="fas fa-hand-paper"></i> Left Hand</h5>
                                            <div class="d-flex justify-content-center align-items-end" style="height: 200px;">';

$leftFingers = [
    ['name' => 'Left Thumb', 'icon' => '👍', 'height' => '140px'],
    ['name' => 'Left Index', 'icon' => '☝️', 'height' => '160px'],
    ['name' => 'Left Middle', 'icon' => '🖕', 'height' => '170px'],
    ['name' => 'Left Ring', 'icon' => '💍', 'height' => '150px'],
    ['name' => 'Left Little', 'icon' => '🤙', 'height' => '120px']
];

foreach ($leftFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        if (($bio['remarks'] ?? '') === $finger['name'] && $bio['biometric_type'] === 'Fingerprint') {
            $captured = true;
            break;
        }
    }
    $class = $captured ? 'captured' : 'not-captured';
    $checkIcon = $captured ? '✓' : '';
    $content .= '<div style="height: ' . $finger['height'] . '; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                    <div class="finger-icon ' . $class . '" title="' . $finger['name'] . '">
                        <i class="fas fa-fingerprint"></i>
                        <span style="position: absolute; font-size: 20px; margin-left: -15px; margin-top: -10px;">' . $checkIcon . '</span>
                    </div>
                    <span class="finger-label">' . str_replace('Left ', '', $finger['name']) . '</span>
                </div>';
}

$content .= '
                                            </div>
                                        </div>
                                        <div class="col-md-6 hand-container">
                                            <h5><i class="fas fa-hand-paper fa-flip-horizontal"></i> Right Hand</h5>
                                            <div class="d-flex justify-content-center align-items-end" style="height: 200px;">';

$rightFingers = [
    ['name' => 'Right Thumb', 'icon' => '👍', 'height' => '140px'],
    ['name' => 'Right Index', 'icon' => '☝️', 'height' => '160px'],
    ['name' => 'Right Middle', 'icon' => '🖕', 'height' => '170px'],
    ['name' => 'Right Ring', 'icon' => '💍', 'height' => '150px'],
    ['name' => 'Right Little', 'icon' => '🤙', 'height' => '120px']
];

foreach ($rightFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        if (($bio['remarks'] ?? '') === $finger['name'] && $bio['biometric_type'] === 'Fingerprint') {
            $captured = true;
            break;
        }
    }
    $class = $captured ? 'captured' : 'not-captured';
    $checkIcon = $captured ? '✓' : '';
    $content .= '<div style="height: ' . $finger['height'] . '; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                    <div class="finger-icon ' . $class . '" title="' . $finger['name'] . '">
                        <i class="fas fa-fingerprint"></i>
                        <span style="position: absolute; font-size: 20px; margin-left: -15px; margin-top: -10px;">' . $checkIcon . '</span>
                    </div>
                    <span class="finger-label">' . str_replace('Right ', '', $finger['name']) . '</span>
                </div>';
}

$content .= '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: ' . (count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint')) / 10 * 100) . '%">
                                            ' . count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint')) . '/10 Captured
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>
                        
                        <!-- Bulk Upload Tab -->
                        <div class="tab-pane fade" id="bulk-capture" role="tabpanel">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-hands"></i> Bulk Upload - Scanned Fingerprint Sheet</h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> <strong>Traditional Ink Method:</strong>
                                        <ol class="mb-0 mt-2">
                                            <li>Use fingerprint ink pad and paper to capture all 10 fingers</li>
                                            <li>Scan the completed fingerprint sheet (both hands)</li>
                                            <li>Upload the scanned image below</li>
                                            <li>System will extract individual fingerprints automatically</li>
                                        </ol>
                                    </div>

                                    <form id="bulkFingerprintForm" enctype="multipart/form-data">
                                        ' . csrf_field() . '
                                        <input type="hidden" name="suspect_id" value="' . $suspectId . '">
                                        <input type="hidden" name="bulk_upload" value="1">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Upload Left Hand Sheet</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="left_hand_sheet" name="left_hand_sheet" accept="image/*" required>
                                                        <label class="custom-file-label" for="left_hand_sheet">Choose file...</label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Scan showing all 5 fingers of left hand
                                                    </small>
                                                </div>
                                                <div id="left_hand_preview" class="mt-2" style="display: none;">
                                                    <img id="left_hand_img" src="" alt="Left Hand Preview" style="max-width: 100%; border: 2px solid #ccc; border-radius: 5px;">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Upload Right Hand Sheet</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="right_hand_sheet" name="right_hand_sheet" accept="image/*" required>
                                                        <label class="custom-file-label" for="right_hand_sheet">Choose file...</label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Scan showing all 5 fingers of right hand
                                                    </small>
                                                </div>
                                                <div id="right_hand_preview" class="mt-2" style="display: none;">
                                                    <img id="right_hand_img" src="" alt="Right Hand Preview" style="max-width: 100%; border: 2px solid #ccc; border-radius: 5px;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> <strong>Quality Requirements:</strong>
                                                    <ul class="mb-0">
                                                        <li>High resolution scan (minimum 300 DPI)</li>
                                                        <li>Clear, non-smudged fingerprints</li>
                                                        <li>All fingers visible and properly labeled</li>
                                                        <li>Good contrast between ink and paper</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Overall Quality Assessment</label>
                                            <select class="form-control" name="bulk_quality" required>
                                                <option value="">-- Assess Quality --</option>
                                                <option value="Excellent">Excellent - All prints clear</option>
                                                <option value="Good">Good - Most prints clear</option>
                                                <option value="Fair">Fair - Some prints unclear</option>
                                                <option value="Poor">Poor - Requires recapture</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea class="form-control" name="bulk_remarks" rows="2" placeholder="Any notes about the scanned sheets..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-warning btn-block btn-lg">
                                            <i class="fas fa-upload"></i> Upload & Process Fingerprint Sheets
                                        </button>
                                    </form>

                                    <div class="mt-4">
                                        <h5><i class="fas fa-question-circle"></i> How to Prepare Fingerprint Sheet:</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="border rounded p-4 bg-light text-center" style="min-height: 300px;">
                                                    <h3 class="text-muted mb-4">LEFT HAND</h3>
                                                    <div class="d-flex justify-content-around">
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Thumb</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Index</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Middle</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Ring</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Little</small></div>
                                                    </div>
                                                    <p class="mt-4 text-muted"><i class="fas fa-arrow-up"></i> Roll each finger from left to right</p>
                                                </div>
                                                <p class="text-center mt-2"><small>Left Hand - Thumb to Little Finger</small></p>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-4 bg-light text-center" style="min-height: 300px;">
                                                    <h3 class="text-muted mb-4">RIGHT HAND</h3>
                                                    <div class="d-flex justify-content-around">
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Thumb</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Index</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Middle</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Ring</small></div>
                                                        <div><i class="fas fa-fingerprint fa-3x text-secondary"></i><br><small>Little</small></div>
                                                    </div>
                                                    <p class="mt-4 text-muted"><i class="fas fa-arrow-up"></i> Roll each finger from left to right</p>
                                                </div>
                                                <p class="text-center mt-2"><small>Right Hand - Thumb to Little Finger</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Width Fingerprint Collection Status -->
<div class="row mt-4" style="margin-left: -15px; margin-right: -15px;">
    <div class="col-md-12" style="padding-left: 15px; padding-right: 15px;">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hands"></i> Fingerprint Collection Status - 10-Print Card</h3>
                <div class="card-tools">
                    <span class="badge badge-success">' . count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint')) . '/10 Captured</span>
                </div>
            </div>
            <div class="card-body">
                <style>
                    .tenprint-card {
                        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                        border-radius: 10px;
                        padding: 30px;
                    }
                    .hand-outline-container {
                        display: flex;
                        justify-content: space-around;
                        align-items: flex-start;
                        gap: 20px;
                        margin: 30px 0;
                        flex-wrap: wrap;
                    }
                    .hand-outline {
                        position: relative;
                        width: 450px;
                        height: 380px;
                        flex: 0 0 45%;
                    }
                    .hand-svg {
                        width: 100%;
                        height: 100%;
                    }
                    .finger-indicator {
                        position: absolute;
                        width: 50px;
                        height: 60px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: all 0.3s;
                    }
                    .finger-indicator:hover {
                        transform: scale(1.1);
                    }
                    .finger-box {
                        width: 40px;
                        height: 50px;
                        border: 2px solid #6c757d;
                        border-radius: 5px;
                        background: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .finger-indicator.captured .finger-box {
                        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                        border-color: #28a745;
                    }
                    .finger-icon {
                        font-size: 24px;
                        color: #6c757d;
                    }
                    .finger-indicator.captured .finger-icon {
                        color: white;
                    }
                    .check-mark {
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background: #28a745;
                        color: white;
                        border-radius: 50%;
                        width: 20px;
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 10px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    }
                    .finger-label {
                        font-size: 9px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin-top: 3px;
                        color: #495057;
                    }
                    /* Finger positions for left hand */
                    .left-thumb { top: 200px; left: 50px; }
                    .left-index { top: 60px; left: 125px; }
                    .left-middle { top: 30px; left: 180px; }
                    .left-ring { top: 50px; left: 235px; }
                    .left-little { top: 90px; left: 295px; }
                    
                    /* Finger positions for right hand */
                    .right-thumb { top: 200px; right: 50px; }
                    .right-index { top: 60px; right: 125px; }
                    .right-middle { top: 30px; right: 180px; }
                    .right-ring { top: 50px; right: 235px; }
                    .right-little { top: 90px; right: 295px; }
                </style>
                
                <div class="tenprint-card">
                    <div class="hand-outline-container">
                        <div class="hand-outline">
                            <h4 class="text-center mb-4"><i class="fas fa-hand-paper"></i> LEFT HAND</h4>
                            <!-- Simplified Hand Representation -->
                            <svg class="hand-svg" viewBox="0 0 400 350" xmlns="http://www.w3.org/2000/svg">
                                <!-- Palm -->
                                <ellipse cx="200" cy="260" rx="90" ry="70" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Thumb -->
                                <ellipse cx="80" cy="240" rx="25" ry="50" fill="#e9ecef" stroke="#adb5bd" stroke-width="2" transform="rotate(-30 80 240)"/>
                                
                                <!-- Index Finger -->
                                <rect x="130" y="80" width="35" height="120" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Middle Finger -->
                                <rect x="185" y="50" width="35" height="140" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Ring Finger -->
                                <rect x="240" y="70" width="35" height="130" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Little Finger -->
                                <rect x="295" y="110" width="30" height="100" rx="15" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                            </svg>
                            
                            <!-- Finger Indicators -->';

$leftFingers = [
    ['name' => 'Left Thumb', 'short' => 'T', 'class' => 'left-thumb'],
    ['name' => 'Left Index', 'short' => 'I', 'class' => 'left-index'],
    ['name' => 'Left Middle', 'short' => 'M', 'class' => 'left-middle'],
    ['name' => 'Left Ring', 'short' => 'R', 'class' => 'left-ring'],
    ['name' => 'Left Little', 'short' => 'L', 'class' => 'left-little']
];

foreach ($leftFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        if (($bio['remarks'] ?? '') === $finger['name'] && $bio['biometric_type'] === 'Fingerprint') {
            $captured = true;
            break;
        }
    }
    $capturedClass = $captured ? 'captured' : '';
    $content .= '<div class="finger-indicator ' . $capturedClass . ' ' . $finger['class'] . '">
                    <div class="finger-box">
                        <i class="fas fa-fingerprint finger-icon"></i>
                        ' . ($captured ? '<div class="check-mark"><i class="fas fa-check"></i></div>' : '') . '
                    </div>
                    <div class="finger-label">' . $finger['short'] . '</div>
                </div>';
}

$content .= '
                        </div>
                        
                        <div class="hand-outline">
                            <h4 class="text-center mb-4"><i class="fas fa-hand-paper fa-flip-horizontal"></i> RIGHT HAND</h4>
                            <!-- Simplified Hand Representation (mirrored) -->
                            <svg class="hand-svg" viewBox="0 0 400 350" xmlns="http://www.w3.org/2000/svg">
                                <!-- Palm -->
                                <ellipse cx="200" cy="260" rx="90" ry="70" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Thumb (mirrored) -->
                                <ellipse cx="320" cy="240" rx="25" ry="50" fill="#e9ecef" stroke="#adb5bd" stroke-width="2" transform="rotate(30 320 240)"/>
                                
                                <!-- Index Finger -->
                                <rect x="235" y="80" width="35" height="120" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Middle Finger -->
                                <rect x="180" y="50" width="35" height="140" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Ring Finger -->
                                <rect x="125" y="70" width="35" height="130" rx="17" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                                
                                <!-- Little Finger -->
                                <rect x="75" y="110" width="30" height="100" rx="15" fill="#e9ecef" stroke="#adb5bd" stroke-width="2"/>
                            </svg>
                            
                            <!-- Finger Indicators -->';

$rightFingers = [
    ['name' => 'Right Thumb', 'short' => 'T', 'class' => 'right-thumb'],
    ['name' => 'Right Index', 'short' => 'I', 'class' => 'right-index'],
    ['name' => 'Right Middle', 'short' => 'M', 'class' => 'right-middle'],
    ['name' => 'Right Ring', 'short' => 'R', 'class' => 'right-ring'],
    ['name' => 'Right Little', 'short' => 'L', 'class' => 'right-little']
];

foreach ($rightFingers as $finger) {
    $captured = false;
    foreach ($biometrics as $bio) {
        if (($bio['remarks'] ?? '') === $finger['name'] && $bio['biometric_type'] === 'Fingerprint') {
            $captured = true;
            break;
        }
    }
    $capturedClass = $captured ? 'captured' : '';
    $content .= '<div class="finger-indicator ' . $capturedClass . ' ' . $finger['class'] . '">
                    <div class="finger-box">
                        <i class="fas fa-fingerprint finger-icon"></i>
                        ' . ($captured ? '<div class="check-mark"><i class="fas fa-check"></i></div>' : '') . '
                    </div>
                    <div class="finger-label">' . $finger['short'] . '</div>
                </div>';
}

$content .= '
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mt-4" style="height: 30px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' . (count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint')) / 10 * 100) . '%">
                            <strong>' . count(array_filter($biometrics, fn($b) => $b['biometric_type'] === 'Fingerprint')) . ' of 10 Fingerprints Captured</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

$scripts = '
<script>
$(document).ready(function() {
    let cameraStream = null;

    // Biometric type toggle
    $("input[name=biometric_type]").on("change", function() {
        const type = $(this).val();
        $(".biometric-section").hide();
        
        if (type === "Fingerprint") {
            $("#fingerprint_section").show();
        } else if (type === "Face") {
            $("#face_section").show();
        } else if (type === "Iris") {
            $("#iris_section").show();
        } else if (type === "Palm Print") {
            $("#palmprint_section").show();
        }
    });
    
    // Initialize - show fingerprint by default
    $("#fingerprint_section").show();

    // Capture method toggle
    $("input[name=capture_method]").on("change", function() {
        $(".capture-interface").hide();
        const method = $(this).val();
        $("#" + method + "_interface").show();
        
        if (method === "camera") {
            startCamera();
        } else {
            stopCamera();
        }
    });

    // File upload preview (single finger)
    $("#fingerprint_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#fingerprint_preview").attr("src", e.target.result);
                $("#preview_area").show();
            };
            reader.readAsDataURL(file);
            $(".custom-file-label").text(file.name);
        }
    });

    // Bulk upload preview (left hand)
    $("#left_hand_sheet").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#left_hand_img").attr("src", e.target.result);
                $("#left_hand_preview").show();
            };
            reader.readAsDataURL(file);
            $(this).next(".custom-file-label").text(file.name);
        }
    });

    // Bulk upload preview (right hand)
    $("#right_hand_sheet").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#right_hand_img").attr("src", e.target.result);
                $("#right_hand_preview").show();
            };
            reader.readAsDataURL(file);
            $(this).next(".custom-file-label").text(file.name);
        }
    });

    // Face upload preview
    $("#face_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#face_img").attr("src", e.target.result);
                $("#face_preview").show();
            };
            reader.readAsDataURL(file);
            $(this).next(".custom-file-label").text(file.name);
        }
    });

    // Iris upload preview
    $("#iris_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#iris_img").attr("src", e.target.result);
                $("#iris_preview").show();
            };
            reader.readAsDataURL(file);
            $(this).next(".custom-file-label").text(file.name);
        }
    });

    // Palm print upload preview
    $("#palm_file").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#palm_img").attr("src", e.target.result);
                $("#palm_preview").show();
            };
            reader.readAsDataURL(file);
            $(this).next(".custom-file-label").text(file.name);
        }
    });

    // Generic form submission handler for all biometric types
    function submitBiometricForm(formId) {
        $("#" + formId).on("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: "' . url('/suspects/' . $suspectId . '/biometrics') . '",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert("Biometric saved successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + (response.message || "Failed to save biometric"));
                    }
                },
                error: function() {
                    alert("Failed to save biometric. Please try again.");
                }
            });
        });
    }

    // Form submission
    $("#fingerprintForm").on("submit", function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
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
                    alert("Error: " + (response.message || "Failed to save fingerprint"));
                }
            },
            error: function() {
                alert("Failed to save fingerprint. Please try again.");
            }
        });
    });

    // Register form submissions for other biometric types
    submitBiometricForm("faceForm");
    submitBiometricForm("irisForm");
    submitBiometricForm("palmprintForm");

    // Bulk upload form submission
    $("#bulkFingerprintForm").on("submit", function(e) {
        e.preventDefault();
        
        if (!confirm("Upload and process fingerprint sheets for all 10 fingers?")) {
            return;
        }
        
        const formData = new FormData(this);
        
        // Show loading
        const btn = $(this).find("button[type=submit]");
        const originalText = btn.html();
        btn.html("<i class=\"fas fa-spinner fa-spin\"></i> Processing...").prop("disabled", true);
        
        $.ajax({
            url: "' . url('/suspects/' . $suspectId . '/biometrics/bulk') . '",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert("Fingerprint sheets processed successfully! " + response.count + " fingerprints saved.");
                    location.reload();
                } else {
                    alert("Error: " + (response.message || "Failed to process fingerprint sheets"));
                    btn.html(originalText).prop("disabled", false);
                }
            },
            error: function() {
                alert("Failed to process fingerprint sheets. Please try again.");
                btn.html(originalText).prop("disabled", false);
            }
        });
    });

    // Camera functions
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                cameraStream = stream;
                $("#camera_preview")[0].srcObject = stream;
            })
            .catch(function(err) {
                alert("Camera access denied: " + err.message);
            });
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
    }

    window.captureFromCamera = function() {
        const video = $("#camera_preview")[0];
        const canvas = $("#camera_canvas")[0];
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext("2d").drawImage(video, 0, 0);
        
        canvas.toBlob(function(blob) {
            const file = new File([blob], "fingerprint.png", { type: "image/png" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            $("#fingerprint_file")[0].files = dataTransfer.files;
            
            $("#fingerprint_preview").attr("src", canvas.toDataURL());
            $("#preview_area").show();
        });
    };

    window.captureFromScanner = function() {
        alert("Scanner integration requires specialized hardware SDK. Please use Upload or Camera method for now.");
        // TODO: Integrate with fingerprint scanner SDK
    };

    window.viewBiometric = function(id) {
        window.open("' . url('/biometrics/') . '" + id, "_blank");
    };

    window.deleteBiometric = function(id) {
        if (confirm("Are you sure you want to delete this biometric record?")) {
            $.post("' . url('/biometrics/') . '" + id + "/delete", {
                _token: $("input[name=_token]").val()
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert("Failed to delete biometric");
                }
            });
        }
    };
});
</script>
';

include __DIR__ . '/../layouts/main.php';
?>
