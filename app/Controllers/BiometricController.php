<?php

namespace App\Controllers;

use App\Models\PersonBiometric;

class BiometricController extends BaseController
{
    private PersonBiometric $biometricModel;
    
    public function __construct()
    {
        $this->biometricModel = new PersonBiometric();
    }
    
    /**
     * Show biometric capture page for person
     */
    public function capturePersonBiometrics(int $personId): string
    {
        // Get person details
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*,
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name
            FROM persons p
            WHERE p.id = ?
        ");
        $stmt->execute([$personId]);
        $person = $stmt->fetch();
        
        if (!$person) {
            $this->setFlash('error', 'Person not found');
            $this->redirect('/persons');
        }
        
        return $this->view('persons/capture_biometrics', [
            'title' => 'Biometric Data Collection - ' . $person['full_name'],
            'person' => $person
        ]);
    }
    
    /**
     * Show biometric capture page for suspect (legacy - redirects to person)
     */
    public function captureSuspectBiometrics(int $suspectId): void
    {
        // Get person_id from suspect and redirect to person-based URL
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT person_id FROM suspects WHERE id = ?");
        $stmt->execute([$suspectId]);
        $suspect = $stmt->fetch();
        
        if (!$suspect || !$suspect['person_id']) {
            $this->setFlash('error', 'Suspect not found or not linked to a person');
            $this->redirect('/suspects');
            return;
        }
        
        // Redirect to person-based biometrics URL
        $this->redirect('/persons/' . $suspect['person_id'] . '/biometrics');
    }
    
    /**
     * Store captured biometric data for person
     */
    public function storePersonBiometric(int $personId): void
    {
        if (!verify_csrf()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token']);
            return;
        }
        
        try {
            $biometricType = $_POST['biometric_type'] ?? 'Fingerprint';
            $captureQuality = $_POST['capture_quality'] ?? null;
            $captureDevice = $_POST['capture_device'] ?? 'Manual Upload';
            $fingerPosition = $_POST['finger_position'] ?? null;
            $remarks = $_POST['remarks'] ?? '';
            
            // Add finger position to remarks for tracking (only if not already present)
            if ($fingerPosition && strpos($remarks, $fingerPosition) !== 0) {
                $remarks = $fingerPosition . ($remarks ? ' - ' . $remarks : '');
            }
            
            // Validate required fields
            if (!$captureQuality) {
                $this->jsonResponse(['success' => false, 'message' => 'Capture quality is required']);
                return;
            }
            
            // Handle file upload
            $filePath = null;
            $biometricData = null;
            
            if (isset($_FILES['fingerprint_file']) && $_FILES['fingerprint_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['fingerprint_file'];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/bmp', 'image/jpg'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, BMP allowed']);
                    return;
                }
                
                // Validate file size (5MB max)
                if ($file['size'] > 5 * 1024 * 1024) {
                    $this->jsonResponse(['success' => false, 'message' => 'File too large. Maximum 5MB allowed']);
                    return;
                }
                
                // Create upload directory if not exists (in public folder for web access)
                $uploadDir = __DIR__ . '/../../public/storage/biometrics/persons/' . $personId;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $biometricType . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filePath = 'storage/biometrics/persons/' . $personId . '/' . $filename;
                $fullPath = __DIR__ . '/../../public/' . $filePath;
                
                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Failed to save file']);
                    return;
                }
                
                // Read file as binary data for database storage
                $biometricData = file_get_contents($fullPath);
            }
            
            // Save to database
            $biometricId = $this->biometricModel->create([
                'person_id' => $personId,
                'biometric_type' => $biometricType,
                'biometric_data' => $biometricData,
                'file_path' => $filePath,
                'capture_device' => $captureDevice,
                'capture_quality' => $captureQuality,
                'captured_by' => auth_id(),
                'verification_status' => 'Pending',
                'remarks' => $remarks
            ]);
            
            if ($biometricId) {
                // Update person record to mark fingerprint as captured
                if ($biometricType === 'Fingerprint') {
                    $db->prepare("UPDATE persons SET fingerprint_captured = 1 WHERE id = ?")
                       ->execute([$personId]);
                } elseif ($biometricType === 'Face') {
                    $db->prepare("UPDATE persons SET face_captured = 1 WHERE id = ?")
                       ->execute([$personId]);
                }
                
                log_info("Biometric captured for person {$personId}: {$biometricType} - {$captureQuality}");
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Biometric data saved successfully',
                    'biometric_id' => $biometricId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to save biometric data']);
            }
            
        } catch (\Exception $e) {
            logger("Failed to save biometric: " . $e->getMessage(), 'error');
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Store captured biometric data (legacy - for suspect-based calls)
     */
    public function storeSuspectBiometric(int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token']);
            return;
        }
        
        // Get person_id from suspect_id and forward to person-based method
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT person_id FROM suspects WHERE id = ?");
        $stmt->execute([$suspectId]);
        $suspect = $stmt->fetch();
        
        if (!$suspect || !$suspect['person_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Suspect not found or not linked to a person']);
            return;
        }
        
        // Forward to person-based method
        $this->storePersonBiometric($suspect['person_id']);
    }
    
    /**
     * View biometric image
     */
    public function viewBiometric(int $biometricId): void
    {
        $biometric = $this->biometricModel->findById($biometricId);
        
        if (!$biometric) {
            $this->setFlash('error', 'Biometric record not found');
            $this->redirect('/suspects');
            return;
        }
        
        // If file path exists, serve the file
        if ($biometric['file_path'] && file_exists(__DIR__ . '/../../' . $biometric['file_path'])) {
            $filePath = __DIR__ . '/../../' . $biometric['file_path'];
            $mimeType = mime_content_type($filePath);
            
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . basename($biometric['file_path']) . '"');
            readfile($filePath);
            exit;
        }
        
        // Otherwise serve from database blob
        if ($biometric['biometric_data']) {
            header('Content-Type: image/png');
            echo $biometric['biometric_data'];
            exit;
        }
        
        // No data available
        $this->setFlash('error', 'Biometric data not available');
        $this->redirect('/suspects');
    }
    
    /**
     * Delete biometric record
     */
    public function deleteBiometric(int $biometricId): void
    {
        if (!verify_csrf()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token']);
            return;
        }
        
        try {
            $biometric = $this->biometricModel->findById($biometricId);
            
            if (!$biometric) {
                $this->jsonResponse(['success' => false, 'message' => 'Biometric not found']);
                return;
            }
            
            // Delete file if exists
            if ($biometric['file_path']) {
                $filePath = __DIR__ . '/../../' . $biometric['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete database record
            $deleted = $this->biometricModel->delete($biometricId);
            
            if ($deleted) {
                logger("Biometric deleted: ID {$biometricId}");
                $this->jsonResponse(['success' => true, 'message' => 'Biometric deleted successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to delete biometric']);
            }
            
        } catch (\Exception $e) {
            logger("Failed to delete biometric: " . $e->getMessage(), 'error');
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Store bulk biometrics from scanned fingerprint sheets
     */
    public function storeBulkBiometrics(int $suspectId): void
    {
        if (!verify_csrf()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token']);
            return;
        }
        
        try {
            $captureQuality = $_POST['bulk_quality'] ?? null;
            $remarks = $_POST['bulk_remarks'] ?? '';
            
            if (!$captureQuality) {
                $this->jsonResponse(['success' => false, 'message' => 'Quality assessment is required']);
                return;
            }
            
            $savedCount = 0;
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("SELECT person_id FROM suspects WHERE id = ?");
            $stmt->execute([$suspectId]);
            $suspect = $stmt->fetch();
            
            if (!$suspect || !$suspect['person_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Suspect not found or not linked to a person']);
                return;
            }
            
            $personId = $suspect['person_id'];
            $uploadDir = __DIR__ . '/../../public/storage/biometrics/persons/' . $personId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Process left hand sheet
            if (isset($_FILES['left_hand_sheet']) && $_FILES['left_hand_sheet']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['left_hand_sheet'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'LeftHand_' . time() . '.' . $extension;
                $filePath = 'storage/biometrics/persons/' . $personId . '/' . $filename;
                $fullPath = __DIR__ . '/../../public/' . $filePath;
                
                if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                    // Save as single biometric record for left hand
                    $biometricData = file_get_contents($fullPath);
                    $this->biometricModel->create([
                        'person_id' => $personId,
                        'biometric_type' => 'Fingerprint',
                        'biometric_data' => $biometricData,
                        'file_path' => $filePath,
                        'capture_device' => 'Scanned Sheet',
                        'capture_quality' => $captureQuality,
                        'captured_by' => auth_id(),
                        'verification_status' => 'Pending',
                        'remarks' => 'Left Hand (Bulk Upload) - ' . $remarks
                    ]);
                    $savedCount++;
                }
            }
            
            // Process right hand sheet
            if (isset($_FILES['right_hand_sheet']) && $_FILES['right_hand_sheet']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['right_hand_sheet'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'RightHand_' . time() . '.' . $extension;
                $filePath = 'storage/biometrics/persons/' . $personId . '/' . $filename;
                $fullPath = __DIR__ . '/../../public/' . $filePath;
                
                if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                    // Save as single biometric record for right hand
                    $biometricData = file_get_contents($fullPath);
                    $this->biometricModel->create([
                        'person_id' => $personId,
                        'biometric_type' => 'Fingerprint',
                        'biometric_data' => $biometricData,
                        'file_path' => $filePath,
                        'capture_device' => 'Scanned Sheet',
                        'capture_quality' => $captureQuality,
                        'captured_by' => auth_id(),
                        'verification_status' => 'Pending',
                        'remarks' => 'Right Hand (Bulk Upload) - ' . $remarks
                    ]);
                    $savedCount++;
                }
            }
            
            if ($savedCount > 0) {
                // Update persons table to mark biometric as captured
                $stmt = $db->prepare("
                    UPDATE persons p
                    JOIN suspects s ON p.id = s.person_id
                    SET p.fingerprint_captured = 1
                    WHERE s.id = ?
                ");
                $stmt->execute([$suspectId]);
                
                logger("Bulk biometrics uploaded for suspect {$suspectId}: {$savedCount} sheets");
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Fingerprint sheets uploaded successfully',
                    'count' => $savedCount
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'No files were uploaded']);
            }
            
        } catch (\Exception $e) {
            logger("Failed to save bulk biometrics: " . $e->getMessage(), 'error');
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * JSON response helper
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
