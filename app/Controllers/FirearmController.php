<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class FirearmController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * List firearms
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        
        $firearms = $this->getFirearms($status, $type);
        
        return $this->view('firearms/index', [
            'title' => 'Firearms Registry',
            'firearms' => $firearms,
            'selected_status' => $status,
            'selected_type' => $type
        ]);
    }
    
    /**
     * Register firearm
     */
    public function create(): string
    {
        $stmt = $this->db->prepare("SELECT id, station_name FROM stations ORDER BY station_name");
        $stmt->execute();
        $stations = $stmt->fetchAll();
        
        // Get ammunition types
        $stmt = $this->db->query("SELECT id, type, caliber, description FROM ammunition_types WHERE is_active = TRUE ORDER BY type, caliber");
        $ammunitionTypes = $stmt->fetchAll();
        
        return $this->view('firearms/create', [
            'title' => 'Register Firearm',
            'stations' => $stations,
            'ammunition_types' => $ammunitionTypes
        ]);
    }
    
    /**
     * Store firearm
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/firearms/create');
        }
        
        $stationId = $_POST['station_id'] ?? null;
        
        // Validation
        if (empty($_POST['serial_number']) || empty($_POST['firearm_type']) || empty($stationId)) {
            $this->setFlash('error', 'Serial number, type, and station are required');
            $_SESSION['old'] = $_POST;
            $this->redirect('/firearms/create');
        }
        
        // Get station's organizational hierarchy
        $stmt = $this->db->prepare("
            SELECT s.id, s.district_id, d.division_id, dv.region_id
            FROM stations s
            JOIN districts d ON s.district_id = d.id
            JOIN divisions dv ON d.division_id = dv.id
            WHERE s.id = ?
        ");
        $stmt->execute([$stationId]);
        $stationInfo = $stmt->fetch();
        
        if (!$stationInfo) {
            $this->setFlash('error', 'Invalid station selected');
            $_SESSION['old'] = $_POST;
            $this->redirect('/firearms/create');
        }
        
        $ammunitionTypeId = !empty($_POST['ammunition_type_id']) ? (int)$_POST['ammunition_type_id'] : null;
        
        $data = [
            'serial_number' => $_POST['serial_number'] ?? '',
            'firearm_type' => $_POST['firearm_type'] ?? '',
            'make' => $_POST['make'] ?? null,
            'model' => $_POST['model'] ?? null,
            'caliber' => $_POST['caliber'] ?? null,
            'ammunition_type_id' => $ammunitionTypeId,
            'acquisition_date' => $_POST['acquisition_date'] ?? date('Y-m-d'),
            'acquisition_source' => $_POST['acquisition_source'] ?? null,
            'firearm_status' => 'In Armory',
            'station_id' => $stationInfo['id'],
            'district_id' => $stationInfo['district_id'],
            'division_id' => $stationInfo['division_id'],
            'region_id' => $stationInfo['region_id'],
            'remarks' => $_POST['remarks'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO firearms (
                    serial_number, firearm_type, make, model, caliber, ammunition_type_id,
                    acquisition_date, acquisition_source, firearm_status,
                    station_id, district_id, division_id, region_id, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['serial_number'], $data['firearm_type'],
                $data['make'], $data['model'], $data['caliber'], $data['ammunition_type_id'],
                $data['acquisition_date'], $data['acquisition_source'],
                $data['firearm_status'], $data['station_id'],
                $data['district_id'], $data['division_id'], 
                $data['region_id'], $data['remarks']
            ]);
            
            $firearmId = (int)$this->db->lastInsertId();
            
            logger("Firearm registered: {$data['serial_number']}", 'info');
            
            $this->setFlash('success', 'Firearm registered successfully');
            $this->redirect('/firearms/' . $firearmId);
        } catch (\Exception $e) {
            logger("Error registering firearm: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to register firearm: ' . $e->getMessage());
            $_SESSION['old'] = $_POST;
            $this->redirect('/firearms/create');
        }
    }
    
    /**
     * Issue firearm to officer
     */
    public function issue(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/firearms/' . $id);
        }
        
        $officerId = $_POST['officer_id'] ?? null;
        $purpose = $_POST['purpose'] ?? '';
        $ammoIssued = (int)($_POST['ammunition_issued'] ?? 0);
        
        if (!$officerId) {
            $this->setFlash('error', 'Please select an officer');
            $this->redirect('/firearms/' . $id);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get firearm details
            $stmt = $this->db->prepare("SELECT * FROM firearms WHERE id = ?");
            $stmt->execute([$id]);
            $firearm = $stmt->fetch();
            
            if (!$firearm) {
                throw new \Exception('Firearm not found');
            }
            
            // Check and deduct ammunition if needed
            $ammoStockId = null;
            if ($ammoIssued > 0 && $firearm['ammunition_type_id']) {
                // Find ammunition stock matching the firearm's ammunition type
                $stmt = $this->db->prepare("
                    SELECT ams.id, ams.quantity, at.type as ammunition_type, at.caliber
                    FROM ammunition_stock ams
                    JOIN ammunition_types at ON ams.ammunition_type_id = at.id
                    WHERE ams.station_id = ? 
                    AND ams.ammunition_type_id = ?
                    AND ams.is_pool = FALSE
                    ORDER BY ams.quantity DESC
                    LIMIT 1
                ");
                $stmt->execute([$firearm['station_id'], $firearm['ammunition_type_id']]);
                $ammoStock = $stmt->fetch();
                
                if (!$ammoStock) {
                    $this->db->rollBack();
                    $this->setFlash('error', 'No ammunition stock found for this firearm\'s ammunition type at this station. Please add ammunition stock first.');
                    $this->redirect('/firearms/' . $id);
                    return;
                }
                
                if ($ammoStock['quantity'] < $ammoIssued) {
                    $this->db->rollBack();
                    $this->setFlash('error', 'Insufficient ammunition stock. Available: ' . $ammoStock['quantity'] . ' rounds, Requested: ' . $ammoIssued . ' rounds');
                    $this->redirect('/firearms/' . $id);
                    return;
                }
                
                // Deduct ammunition from stock
                $stmt = $this->db->prepare("
                    UPDATE ammunition_stock 
                    SET quantity = quantity - ? 
                    WHERE id = ?
                ");
                $stmt->execute([$ammoIssued, $ammoStock['id']]);
                $ammoStockId = $ammoStock['id'];
            } elseif ($ammoIssued > 0 && !$firearm['ammunition_type_id']) {
                $this->db->rollBack();
                $this->setFlash('error', 'This firearm does not have a linked ammunition type. Please update the firearm to link it to an ammunition type first.');
                $this->redirect('/firearms/' . $id);
                return;
            }
            
            // Create assignment record
            $stmt = $this->db->prepare("
                INSERT INTO firearm_assignments (
                    firearm_id, officer_id, issued_by, issue_date,
                    purpose, ammunition_issued, condition_on_issue
                ) VALUES (?, ?, ?, NOW(), ?, ?, 'Good')
            ");
            $stmt->execute([$id, $officerId, auth_id(), $purpose, $ammoIssued]);
            $assignmentId = (int)$this->db->lastInsertId();
            
            // Record ammunition transaction if ammunition was issued
            if ($ammoIssued > 0 && $ammoStockId) {
                $stmt = $this->db->prepare("
                    INSERT INTO ammunition_transactions (
                        ammunition_stock_id, transaction_type, quantity, 
                        firearm_assignment_id, officer_id, performed_by, remarks
                    ) VALUES (?, 'Issue', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $ammoStockId, $ammoIssued, $assignmentId, 
                    $officerId, auth_id(), 
                    "Issued with firearm #{$firearm['serial_number']}"
                ]);
            }
            
            // Update firearm status and holder
            $stmt = $this->db->prepare("
                UPDATE firearms 
                SET firearm_status = 'In Service', current_holder_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$officerId, $id]);
            
            $this->db->commit();
            
            logger("Firearm issued: ID {$id} to Officer {$officerId}, Ammo: {$ammoIssued}", 'info');
            
            $this->setFlash('success', 'Firearm issued successfully' . ($ammoIssued > 0 ? " with {$ammoIssued} rounds" : ''));
            $this->redirect('/firearms/' . $id);
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error issuing firearm: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to issue firearm: ' . $e->getMessage());
            $this->redirect('/firearms/' . $id);
        }
    }
    
    /**
     * Return firearm from officer
     */
    public function returnFirearm(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/firearms/' . $id);
        }
        
        $ammoReturned = (int)($_POST['ammunition_returned'] ?? 0);
        $condition = $_POST['condition_on_return'] ?? 'Good';
        $remarks = $_POST['remarks'] ?? '';
        
        try {
            $this->db->beginTransaction();
            
            // Get firearm details
            $stmt = $this->db->prepare("SELECT * FROM firearms WHERE id = ?");
            $stmt->execute([$id]);
            $firearm = $stmt->fetch();
            
            if (!$firearm) {
                throw new \Exception('Firearm not found');
            }
            
            // Get current assignment
            $stmt = $this->db->prepare("
                SELECT * FROM firearm_assignments 
                WHERE firearm_id = ? AND return_date IS NULL
            ");
            $stmt->execute([$id]);
            $assignment = $stmt->fetch();
            
            if (!$assignment) {
                throw new \Exception('No active assignment found');
            }
            
            // Update assignment record
            $stmt = $this->db->prepare("
                UPDATE firearm_assignments 
                SET return_date = NOW(), 
                    ammunition_returned = ?,
                    condition_on_return = ?,
                    remarks = ?
                WHERE id = ?
            ");
            $stmt->execute([$ammoReturned, $condition, $remarks, $assignment['id']]);
            
            // Add returned ammunition back to stock if any
            if ($ammoReturned > 0 && $firearm['ammunition_type_id']) {
                // Find ammunition stock matching the firearm's ammunition type
                $stmt = $this->db->prepare("
                    SELECT id FROM ammunition_stock 
                    WHERE station_id = ? 
                    AND ammunition_type_id = ?
                    AND is_pool = FALSE
                ");
                $stmt->execute([$firearm['station_id'], $firearm['ammunition_type_id']]);
                $ammoStock = $stmt->fetch();
                
                if ($ammoStock) {
                    // Add ammunition back to stock
                    $stmt = $this->db->prepare("
                        UPDATE ammunition_stock 
                        SET quantity = quantity + ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$ammoReturned, $ammoStock['id']]);
                    
                    // Record ammunition transaction
                    $stmt = $this->db->prepare("
                        INSERT INTO ammunition_transactions (
                            ammunition_stock_id, transaction_type, quantity, 
                            firearm_assignment_id, officer_id, performed_by, remarks
                        ) VALUES (?, 'Return', ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $ammoStock['id'], $ammoReturned, $assignment['id'], 
                        $assignment['officer_id'], auth_id(), 
                        "Returned with firearm #{$firearm['serial_number']}"
                    ]);
                }
            }
            
            // Calculate ammunition used
            $ammoUsed = $assignment['ammunition_issued'] - $ammoReturned;
            
            // Update firearm status
            $stmt = $this->db->prepare("
                UPDATE firearms 
                SET firearm_status = 'In Armory', current_holder_id = NULL
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            $this->db->commit();
            
            logger("Firearm returned: ID {$id}, Ammo returned: {$ammoReturned}, Used: {$ammoUsed}", 'info');
            
            $message = 'Firearm returned successfully';
            if ($ammoReturned > 0) {
                $message .= " with {$ammoReturned} rounds returned";
                if ($ammoUsed > 0) {
                    $message .= " ({$ammoUsed} rounds used)";
                }
            }
            
            $this->setFlash('success', $message);
            $this->redirect('/firearms/' . $id);
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error returning firearm: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to return firearm: ' . $e->getMessage());
            $this->redirect('/firearms/' . $id);
        }
    }
    
    /**
     * View firearm details
     */
    public function show(int $id): string
    {
        $firearm = $this->getFirearmDetails($id);
        
        if (!$firearm) {
            $this->setFlash('error', 'Firearm not found');
            $this->redirect('/firearms');
        }
        
        $assignments = $this->getFirearmAssignments($id);
        
        // Get active officers based on hierarchical access level
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        
        $sql = "SELECT o.id, CONCAT_WS(' ', o.first_name, o.last_name) as officer_name, 
                       o.service_number, pr.rank_name
                FROM officers o
                LEFT JOIN police_ranks pr ON o.rank_id = pr.id
                WHERE o.employment_status = 'Active'";
        $params = [];
        
        // Restrict officer selection based on hierarchy
        switch ($accessLevel) {
            case 'Own':
            case 'Unit':
            case 'Station':
                if (!empty($user['station_id'])) {
                    $sql .= " AND o.current_station_id = ?";
                    $params[] = $user['station_id'];
                }
                break;
                
            case 'District':
                if (!empty($user['district_id'])) {
                    $sql .= " AND o.current_district_id = ?";
                    $params[] = $user['district_id'];
                }
                break;
                
            case 'Division':
                if (!empty($user['division_id'])) {
                    $sql .= " AND o.current_division_id = ?";
                    $params[] = $user['division_id'];
                }
                break;
                
            case 'Region':
                if (!empty($user['region_id'])) {
                    $sql .= " AND o.current_region_id = ?";
                    $params[] = $user['region_id'];
                }
                break;
                
            case 'National':
                // No filtering - see all officers
                break;
        }
        
        $sql .= " ORDER BY o.first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $officers = $stmt->fetchAll();
        
        return $this->view('firearms/view', [
            'title' => 'Firearm Details',
            'firearm' => $firearm,
            'assignments' => $assignments,
            'officers' => $officers
        ]);
    }
    
    /**
     * Assign firearm to officer
     */
    public function assign(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $officerId = $_POST['officer_id'] ?? null;
        $purpose = $_POST['purpose'] ?? '';
        
        try {
            $this->db->beginTransaction();
            
            // End previous assignment
            $stmt = $this->db->prepare("
                UPDATE firearm_assignments
                SET return_date = NOW()
                WHERE firearm_id = ? AND return_date IS NULL
            ");
            $stmt->execute([$id]);
            
            // Create new assignment
            $stmt = $this->db->prepare("
                INSERT INTO firearm_assignments (
                    firearm_id, officer_id, assignment_date, purpose
                ) VALUES (?, ?, NOW(), ?)
            ");
            $stmt->execute([$id, $officerId, $purpose]);
            
            // Update firearm status
            $stmt = $this->db->prepare("
                UPDATE firearms
                SET status = 'Assigned', current_holder_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$officerId, $id]);
            
            $this->db->commit();
            
            $this->json(['success' => true, 'message' => 'Firearm assigned successfully']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    
    private function getFirearms(?string $status, ?string $type): array
    {
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        
        $sql = "SELECT f.*, 
                       CONCAT_WS(' ', o.first_name, o.last_name) as holder_name,
                       pr.rank_name as holder_rank, 
                       s.station_name,
                       at.type as ammunition_type,
                       at.caliber as ammunition_caliber
                FROM firearms f
                LEFT JOIN officers o ON f.current_holder_id = o.id
                LEFT JOIN police_ranks pr ON o.rank_id = pr.id
                LEFT JOIN stations s ON f.station_id = s.id
                LEFT JOIN ammunition_types at ON f.ammunition_type_id = at.id
                WHERE 1=1";
        $params = [];
        
        // Apply hierarchical filtering based on access level
        switch ($accessLevel) {
            case 'Own':
            case 'Unit':
            case 'Station':
                if (!empty($user['station_id'])) {
                    $sql .= " AND f.station_id = ?";
                    $params[] = $user['station_id'];
                }
                break;
                
            case 'District':
                if (!empty($user['district_id'])) {
                    $sql .= " AND f.district_id = ?";
                    $params[] = $user['district_id'];
                }
                break;
                
            case 'Division':
                if (!empty($user['division_id'])) {
                    $sql .= " AND f.division_id = ?";
                    $params[] = $user['division_id'];
                }
                break;
                
            case 'Region':
                if (!empty($user['region_id'])) {
                    $sql .= " AND f.region_id = ?";
                    $params[] = $user['region_id'];
                }
                break;
                
            case 'National':
                // No filtering - see all firearms
                break;
        }
        
        if ($status) {
            $sql .= " AND f.firearm_status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $sql .= " AND f.firearm_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY f.serial_number LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getFirearmDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT f.*, CONCAT_WS(' ', o.first_name, o.last_name) as holder_name,
                   pr.rank_name as holder_rank, o.service_number as holder_service_number
            FROM firearms f
            LEFT JOIN officers o ON f.current_holder_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE f.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function getFirearmAssignments(int $firearmId): array
    {
        $stmt = $this->db->prepare("
            SELECT fa.*, CONCAT_WS(' ', o.first_name, o.last_name) as officer_name,
                   pr.rank_name, o.service_number
            FROM firearm_assignments fa
            JOIN officers o ON fa.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE fa.firearm_id = ?
            ORDER BY fa.issue_date DESC
        ");
        $stmt->execute([$firearmId]);
        return $stmt->fetchAll();
    }
}
