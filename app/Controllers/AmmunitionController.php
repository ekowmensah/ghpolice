<?php

namespace App\Controllers;

use App\Config\Database;

class AmmunitionController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function index(): string
    {
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        
        // Get filter parameters
        $filterLevel = $_GET['level'] ?? null;
        $filterType = $_GET['type'] ?? null;
        $filterLocationId = $_GET['location_id'] ?? null;
        
        $sql = "SELECT ams.*, 
                       at.type as ammunition_type,
                       at.caliber,
                       s.station_name,
                       r.region_name,
                       dv.division_name,
                       d.district_name
                FROM ammunition_stock ams
                LEFT JOIN ammunition_types at ON ams.ammunition_type_id = at.id
                LEFT JOIN stations s ON ams.station_id = s.id
                LEFT JOIN regions r ON ams.region_id = r.id
                LEFT JOIN divisions dv ON ams.division_id = dv.id
                LEFT JOIN districts d ON ams.district_id = d.id
                WHERE 1=1";
        $params = [];
        
        // Apply access-based filtering (user can only see their level and below)
        switch ($accessLevel) {
            case 'National':
                // National users see everything
                break;
            case 'Region':
                if (!empty($user['region_id'])) {
                    $sql .= " AND (ams.region_id = ? OR ams.region_id IS NULL)";
                    $params[] = $user['region_id'];
                }
                break;
            case 'Division':
                if (!empty($user['division_id'])) {
                    $sql .= " AND (ams.division_id = ? OR (ams.region_id = ? AND ams.division_id IS NULL) OR (ams.region_id IS NULL AND ams.division_id IS NULL))";
                    $params[] = $user['division_id'];
                    $params[] = $user['region_id'];
                }
                break;
            case 'District':
                if (!empty($user['district_id'])) {
                    $sql .= " AND (ams.district_id = ? OR (ams.division_id = ? AND ams.district_id IS NULL) OR (ams.region_id = ? AND ams.division_id IS NULL AND ams.district_id IS NULL) OR (ams.region_id IS NULL AND ams.division_id IS NULL AND ams.district_id IS NULL))";
                    $params[] = $user['district_id'];
                    $params[] = $user['division_id'];
                    $params[] = $user['region_id'];
                }
                break;
            case 'Own':
            case 'Unit':
            case 'Station':
                if (!empty($user['station_id'])) {
                    $sql .= " AND (ams.station_id = ? OR (ams.district_id = ? AND ams.station_id IS NULL) OR (ams.division_id = ? AND ams.district_id IS NULL AND ams.station_id IS NULL) OR (ams.region_id = ? AND ams.division_id IS NULL AND ams.district_id IS NULL AND ams.station_id IS NULL) OR (ams.region_id IS NULL AND ams.division_id IS NULL AND ams.district_id IS NULL AND ams.station_id IS NULL))";
                    $params[] = $user['station_id'];
                    $params[] = $user['district_id'];
                    $params[] = $user['division_id'];
                    $params[] = $user['region_id'];
                }
                break;
        }
        
        // Apply user-selected filters
        if ($filterLevel) {
            $sql .= " AND ams.stock_level = ?";
            $params[] = $filterLevel;
        }
        
        if ($filterType) {
            $sql .= " AND ams.ammunition_type = ?";
            $params[] = $filterType;
        }
        
        if ($filterLocationId && $filterLevel) {
            switch ($filterLevel) {
                case 'Region':
                    $sql .= " AND ams.region_id = ?";
                    $params[] = $filterLocationId;
                    break;
                case 'Division':
                    $sql .= " AND ams.division_id = ?";
                    $params[] = $filterLocationId;
                    break;
                case 'District':
                    $sql .= " AND ams.district_id = ?";
                    $params[] = $filterLocationId;
                    break;
                case 'Station':
                    $sql .= " AND ams.station_id = ?";
                    $params[] = $filterLocationId;
                    break;
            }
        }
        
        $sql .= " ORDER BY 
                  CASE ams.stock_level 
                    WHEN 'National' THEN 1 
                    WHEN 'Region' THEN 2 
                    WHEN 'Division' THEN 3 
                    WHEN 'District' THEN 4 
                    WHEN 'Station' THEN 5 
                  END,
                  ams.is_pool DESC, 
                  COALESCE(r.region_name, dv.division_name, d.district_name, s.station_name), 
                  at.type, at.caliber";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ammunition = $stmt->fetchAll();
        
        // Get low stock items (only station-level operational stock)
        $lowStockSql = "SELECT ams.*, s.station_name
                        FROM ammunition_stock ams
                        LEFT JOIN stations s ON ams.station_id = s.id
                        WHERE ams.quantity <= ams.minimum_threshold
                        AND ams.is_pool = FALSE";
        $lowStockParams = [];
        
        // Apply same access-based filtering for low stock
        switch ($accessLevel) {
            case 'National':
                break;
            case 'Region':
                if (!empty($user['region_id'])) {
                    $lowStockSql .= " AND ams.region_id = ?";
                    $lowStockParams[] = $user['region_id'];
                }
                break;
            case 'Division':
                if (!empty($user['division_id'])) {
                    $lowStockSql .= " AND ams.division_id = ?";
                    $lowStockParams[] = $user['division_id'];
                }
                break;
            case 'District':
                if (!empty($user['district_id'])) {
                    $lowStockSql .= " AND ams.district_id = ?";
                    $lowStockParams[] = $user['district_id'];
                }
                break;
            case 'Own':
            case 'Unit':
            case 'Station':
                if (!empty($user['station_id'])) {
                    $lowStockSql .= " AND ams.station_id = ?";
                    $lowStockParams[] = $user['station_id'];
                }
                break;
        }
        
        $stmt = $this->db->prepare($lowStockSql);
        $stmt->execute($lowStockParams);
        $lowStock = $stmt->fetchAll();
        
        // Get filter options based on user access level
        $regions = [];
        $divisions = [];
        $districts = [];
        $stations = [];
        $ammunitionTypes = ['Pistol', 'Rifle', 'Shotgun', 'Submachine Gun', 'Other'];
        
        if ($accessLevel === 'National') {
            $stmt = $this->db->query("SELECT id, region_name FROM regions ORDER BY region_name");
            $regions = $stmt->fetchAll();
        }
        
        if (in_array($accessLevel, ['National', 'Region'])) {
            $sql = "SELECT id, division_name, region_id FROM divisions";
            if ($accessLevel === 'Region' && !empty($user['region_id'])) {
                $sql .= " WHERE region_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$user['region_id']]);
            } else {
                $stmt = $this->db->query($sql . " ORDER BY division_name");
            }
            $divisions = $stmt->fetchAll();
        }
        
        if (in_array($accessLevel, ['National', 'Region', 'Division'])) {
            $sql = "SELECT id, district_name, division_id FROM districts";
            if ($accessLevel === 'Division' && !empty($user['division_id'])) {
                $sql .= " WHERE division_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$user['division_id']]);
            } else {
                $stmt = $this->db->query($sql . " ORDER BY district_name");
            }
            $districts = $stmt->fetchAll();
        }
        
        $sql = "SELECT id, station_name, district_id FROM stations";
        if ($accessLevel === 'District' && !empty($user['district_id'])) {
            $sql .= " WHERE district_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user['district_id']]);
        } else {
            $stmt = $this->db->query($sql . " ORDER BY station_name");
        }
        $stations = $stmt->fetchAll();
        
        return $this->view('ammunition/index', [
            'title' => 'Ammunition Stock',
            'ammunition' => $ammunition,
            'low_stock' => $lowStock,
            'regions' => $regions,
            'divisions' => $divisions,
            'districts' => $districts,
            'stations' => $stations,
            'ammunition_types' => $ammunitionTypes,
            'filter_level' => $filterLevel,
            'filter_type' => $filterType,
            'filter_location_id' => $filterLocationId,
            'access_level' => $accessLevel,
            'user' => $user
        ]);
    }
    
    public function create(): string
    {
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        
        // Get locations based on access level
        $locations = [];
        
        switch ($accessLevel) {
            case 'Region':
                // Can add to region pool OR any division/district/station in their region
                $stmt = $this->db->prepare("
                    SELECT id, station_name, district_id, division_id, region_id 
                    FROM stations 
                    WHERE region_id = ? 
                    ORDER BY station_name
                ");
                $stmt->execute([$user['region_id']]);
                $locations = $stmt->fetchAll();
                break;
                
            case 'Division':
                // Can add to division pool OR any district/station in their division
                $stmt = $this->db->prepare("
                    SELECT id, station_name, district_id, division_id, region_id 
                    FROM stations 
                    WHERE division_id = ? 
                    ORDER BY station_name
                ");
                $stmt->execute([$user['division_id']]);
                $locations = $stmt->fetchAll();
                break;
                
            case 'District':
                // Can add to district pool OR any station in their district
                $stmt = $this->db->prepare("
                    SELECT id, station_name, district_id, division_id, region_id 
                    FROM stations 
                    WHERE district_id = ? 
                    ORDER BY station_name
                ");
                $stmt->execute([$user['district_id']]);
                $locations = $stmt->fetchAll();
                break;
                
            case 'National':
                // Can add anywhere
                $stmt = $this->db->query("
                    SELECT id, station_name, district_id, division_id, region_id 
                    FROM stations 
                    ORDER BY station_name
                ");
                $locations = $stmt->fetchAll();
                break;
                
            default: // Station, Unit, Own
                // Can only add to their own station
                if (!empty($user['station_id'])) {
                    $stmt = $this->db->prepare("
                        SELECT id, station_name, district_id, division_id, region_id 
                        FROM stations 
                        WHERE id = ?
                    ");
                    $stmt->execute([$user['station_id']]);
                    $locations = $stmt->fetchAll();
                }
                break;
        }
        
        // Get regions, divisions, districts for cascading filters (National/Region/Division users)
        $regions = [];
        $divisions = [];
        $districts = [];
        
        if (in_array($accessLevel, ['National', 'Region', 'Division'])) {
            if ($accessLevel === 'National') {
                $stmt = $this->db->query("SELECT id, region_name FROM regions ORDER BY region_name");
                $regions = $stmt->fetchAll();
            } elseif ($accessLevel === 'Region') {
                $stmt = $this->db->prepare("SELECT id, division_name FROM divisions WHERE region_id = ? ORDER BY division_name");
                $stmt->execute([$user['region_id']]);
                $divisions = $stmt->fetchAll();
            } elseif ($accessLevel === 'Division') {
                $stmt = $this->db->prepare("SELECT id, district_name FROM districts WHERE division_id = ? ORDER BY district_name");
                $stmt->execute([$user['division_id']]);
                $districts = $stmt->fetchAll();
            }
        }
        
        // Get ammunition types
        $stmt = $this->db->query("SELECT id, type, caliber, description FROM ammunition_types WHERE is_active = TRUE ORDER BY type, caliber");
        $ammunitionTypes = $stmt->fetchAll();
        
        return $this->view('ammunition/create', [
            'title' => 'Add Ammunition Stock',
            'stations' => $locations,
            'regions' => $regions,
            'divisions' => $divisions,
            'districts' => $districts,
            'ammunition_types' => $ammunitionTypes,
            'access_level' => $accessLevel,
            'user' => $user
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/ammunition/create');
        }
        
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        $addToPool = isset($_POST['add_to_pool']) && $_POST['add_to_pool'] == '1';
        $stationId = $_POST['station_id'] ?? null;
        
        if (empty($_POST['ammunition_type_id']) || empty($_POST['quantity'])) {
            $this->setFlash('error', 'Ammunition type and quantity are required');
            $_SESSION['old'] = $_POST;
            $this->redirect('/ammunition/create');
        }
        
        $ammunitionTypeId = (int)$_POST['ammunition_type_id'];
        
        // Determine stock level and location IDs
        $stockLevel = 'Station';
        $regionId = null;
        $divisionId = null;
        $districtId = null;
        $finalStationId = null;
        
        if ($addToPool) {
            // Adding to organizational pool
            switch ($accessLevel) {
                case 'National':
                    $stockLevel = 'National';
                    // National pool has no location IDs (all NULL)
                    break;
                case 'Region':
                    $stockLevel = 'Region';
                    $regionId = $user['region_id'];
                    break;
                case 'Division':
                    $stockLevel = 'Division';
                    $regionId = $user['region_id'];
                    $divisionId = $user['division_id'];
                    break;
                case 'District':
                    $stockLevel = 'District';
                    $regionId = $user['region_id'];
                    $divisionId = $user['division_id'];
                    $districtId = $user['district_id'];
                    break;
                default:
                    $this->setFlash('error', 'Cannot create pool at your access level');
                    $_SESSION['old'] = $_POST;
                    $this->redirect('/ammunition/create');
                    return;
            }
        } else {
            // Adding to specific station
            if (empty($stationId)) {
                $this->setFlash('error', 'Station is required when not adding to pool');
                $_SESSION['old'] = $_POST;
                $this->redirect('/ammunition/create');
                return;
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
                $this->redirect('/ammunition/create');
                return;
            }
            
            $stockLevel = 'Station';
            $finalStationId = $stationInfo['id'];
            $districtId = $stationInfo['district_id'];
            $divisionId = $stationInfo['division_id'];
            $regionId = $stationInfo['region_id'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Insert ammunition stock
            $stmt = $this->db->prepare("
                INSERT INTO ammunition_stock (
                    ammunition_type_id, station_id, district_id, division_id, region_id,
                    quantity, minimum_threshold,
                    last_restocked_date, last_restocked_quantity,
                    stock_level, is_pool
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $quantity = (int)$_POST['quantity'];
            $threshold = (int)($_POST['minimum_threshold'] ?? 100);
            
            $stmt->execute([
                $ammunitionTypeId, $finalStationId, $districtId, $divisionId, $regionId,
                $quantity, $threshold, date('Y-m-d'), $quantity,
                $stockLevel, $addToPool ? 1 : 0
            ]);
            
            $ammoId = (int)$this->db->lastInsertId();
            
            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO ammunition_transactions (
                    ammunition_stock_id, transaction_type, quantity, performed_by, remarks
                ) VALUES (?, 'Restock', ?, ?, ?)
            ");
            $remarks = $addToPool ? "Initial {$stockLevel} pool stock" : 'Initial station stock';
            $stmt->execute([$ammoId, $quantity, auth_id(), $remarks]);
            
            $this->db->commit();
            
            logger("Ammunition stock added: ID {$ammoId}, Level: {$stockLevel}, Pool: " . ($addToPool ? 'Yes' : 'No'), 'info');
            
            $message = $addToPool 
                ? "Ammunition added to {$stockLevel} pool successfully" 
                : 'Ammunition stock added successfully';
            
            $this->setFlash('success', $message);
            $this->redirect('/ammunition');
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error adding ammunition stock: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to add ammunition stock: ' . $e->getMessage());
            $_SESSION['old'] = $_POST;
            $this->redirect('/ammunition/create');
        }
    }
    
    public function getDivisions(): void
    {
        $regionId = $_GET['region_id'] ?? null;
        
        if (!$regionId) {
            $this->json(['success' => false, 'message' => 'Region ID required'], 400);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, division_name 
                FROM divisions 
                WHERE region_id = ? 
                ORDER BY division_name
            ");
            $stmt->execute([$regionId]);
            $divisions = $stmt->fetchAll();
            
            $this->json(['success' => true, 'divisions' => $divisions]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getDistricts(): void
    {
        $divisionId = $_GET['division_id'] ?? null;
        
        if (!$divisionId) {
            $this->json(['success' => false, 'message' => 'Division ID required'], 400);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, district_name 
                FROM districts 
                WHERE division_id = ? 
                ORDER BY district_name
            ");
            $stmt->execute([$divisionId]);
            $districts = $stmt->fetchAll();
            
            $this->json(['success' => true, 'districts' => $districts]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function allocate(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/ammunition');
        }
        
        $user = $_SESSION['user'] ?? [];
        $accessLevel = $user['access_level'] ?? 'Station';
        
        $toLevel = $_POST['to_level'] ?? null;
        $toLocationId = $_POST['to_location_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';
        
        if (empty($toLevel) || empty($toLocationId) || $quantity <= 0) {
            $this->setFlash('error', 'Allocation level, location, and quantity are required');
            $this->redirect('/ammunition');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get source ammunition stock
            $stmt = $this->db->prepare("SELECT * FROM ammunition_stock WHERE id = ?");
            $stmt->execute([$id]);
            $sourceStock = $stmt->fetch();
            
            if (!$sourceStock) {
                throw new \Exception('Source ammunition stock not found');
            }
            
            if (!$sourceStock['is_pool']) {
                throw new \Exception('Can only allocate from pool stock');
            }
            
            if ($sourceStock['quantity'] < $quantity) {
                throw new \Exception('Insufficient quantity in pool. Available: ' . $sourceStock['quantity']);
            }
            
            // Get destination location details
            $destRegionId = null;
            $destDivisionId = null;
            $destDistrictId = null;
            $destStationId = null;
            $destIsPool = false;
            
            switch ($toLevel) {
                case 'Region':
                    $stmt = $this->db->prepare("SELECT id, region_name FROM regions WHERE id = ?");
                    $stmt->execute([$toLocationId]);
                    $location = $stmt->fetch();
                    if (!$location) throw new \Exception('Region not found');
                    $destRegionId = $location['id'];
                    $destIsPool = true;
                    break;
                    
                case 'Division':
                    $stmt = $this->db->prepare("SELECT id, division_name, region_id FROM divisions WHERE id = ?");
                    $stmt->execute([$toLocationId]);
                    $location = $stmt->fetch();
                    if (!$location) throw new \Exception('Division not found');
                    $destDivisionId = $location['id'];
                    $destRegionId = $location['region_id'];
                    $destIsPool = true;
                    break;
                    
                case 'District':
                    $stmt = $this->db->prepare("
                        SELECT d.id, d.district_name, d.division_id, dv.region_id
                        FROM districts d
                        JOIN divisions dv ON d.division_id = dv.id
                        WHERE d.id = ?
                    ");
                    $stmt->execute([$toLocationId]);
                    $location = $stmt->fetch();
                    if (!$location) throw new \Exception('District not found');
                    $destDistrictId = $location['id'];
                    $destDivisionId = $location['division_id'];
                    $destRegionId = $location['region_id'];
                    $destIsPool = true;
                    break;
                    
                case 'Station':
                    $stmt = $this->db->prepare("
                        SELECT s.id, s.station_name, s.district_id, d.division_id, dv.region_id
                        FROM stations s
                        JOIN districts d ON s.district_id = d.id
                        JOIN divisions dv ON d.division_id = dv.id
                        WHERE s.id = ?
                    ");
                    $stmt->execute([$toLocationId]);
                    $location = $stmt->fetch();
                    if (!$location) throw new \Exception('Station not found');
                    $destStationId = $location['id'];
                    $destDistrictId = $location['district_id'];
                    $destDivisionId = $location['division_id'];
                    $destRegionId = $location['region_id'];
                    $destIsPool = false; // Station stock is operational, not pool
                    break;
                    
                default:
                    throw new \Exception('Invalid allocation level');
            }
            
            // Deduct from source pool
            $stmt = $this->db->prepare("
                UPDATE ammunition_stock 
                SET quantity = quantity - ? 
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $id]);
            
            // Check if destination stock already exists
            $stmt = $this->db->prepare("
                SELECT id FROM ammunition_stock 
                WHERE ammunition_type = ? 
                AND caliber = ? 
                AND stock_level = ?
                AND is_pool = ?
                AND " . ($destStationId ? "station_id = ?" : "station_id IS NULL") . "
                AND " . ($destDistrictId ? "district_id = ?" : "district_id IS NULL") . "
                AND " . ($destDivisionId ? "division_id = ?" : "division_id IS NULL") . "
                AND " . ($destRegionId ? "region_id = ?" : "region_id IS NULL")
            );
            
            $params = [$sourceStock['ammunition_type'], $sourceStock['caliber'], $toLevel, $destIsPool ? 1 : 0];
            if ($destStationId) $params[] = $destStationId;
            if ($destDistrictId) $params[] = $destDistrictId;
            if ($destDivisionId) $params[] = $destDivisionId;
            if ($destRegionId) $params[] = $destRegionId;
            
            $stmt->execute($params);
            $existingStock = $stmt->fetch();
            
            if ($existingStock) {
                // Add to existing stock
                $stmt = $this->db->prepare("
                    UPDATE ammunition_stock 
                    SET quantity = quantity + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$quantity, $existingStock['id']]);
                $destStockId = $existingStock['id'];
            } else {
                // Create new stock entry
                $stmt = $this->db->prepare("
                    INSERT INTO ammunition_stock (
                        station_id, district_id, division_id, region_id,
                        ammunition_type, caliber, quantity, minimum_threshold,
                        stock_level, is_pool, last_restocked_date, last_restocked_quantity
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $destStationId, $destDistrictId, $destDivisionId, $destRegionId,
                    $sourceStock['ammunition_type'], $sourceStock['caliber'],
                    $quantity, $sourceStock['minimum_threshold'],
                    $toLevel, $destIsPool ? 1 : 0,
                    date('Y-m-d'), $quantity
                ]);
                $destStockId = (int)$this->db->lastInsertId();
            }
            
            // Record allocation
            $stmt = $this->db->prepare("
                INSERT INTO ammunition_allocations (
                    ammunition_stock_id, from_level, to_level,
                    from_location_id, to_location_id, quantity,
                    allocated_by, status, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Approved', ?)
            ");
            $stmt->execute([
                $id, $sourceStock['stock_level'], $toLevel,
                $sourceStock['station_id'] ?? $sourceStock['district_id'] ?? $sourceStock['division_id'] ?? $sourceStock['region_id'],
                $toLocationId, $quantity, auth_id(), $remarks
            ]);
            
            // Record transactions
            $stmt = $this->db->prepare("
                INSERT INTO ammunition_transactions (
                    ammunition_stock_id, transaction_type, quantity, performed_by, remarks
                ) VALUES (?, 'Transfer', ?, ?, ?)
            ");
            $stmt->execute([$id, -$quantity, auth_id(), "Allocated to {$toLevel}"]);
            $stmt->execute([$destStockId, $quantity, auth_id(), "Received from {$sourceStock['stock_level']} pool"]);
            
            $this->db->commit();
            
            logger("Ammunition allocated: {$quantity} rounds from {$sourceStock['stock_level']} to {$toLevel}", 'info');
            
            $this->setFlash('success', "Successfully allocated {$quantity} rounds to {$toLevel}");
            $this->redirect('/ammunition');
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error allocating ammunition: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to allocate ammunition: ' . $e->getMessage());
            $this->redirect('/ammunition');
        }
    }
    
    public function restock(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/ammunition');
        }
        
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        if ($quantity <= 0) {
            $this->setFlash('error', 'Invalid quantity');
            $this->redirect('/ammunition');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get current stock
            $stmt = $this->db->prepare("SELECT * FROM ammunition_stock WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            
            if (!$current) {
                $this->setFlash('error', 'Ammunition stock not found');
                $this->redirect('/ammunition');
            }
            
            $newQuantity = $current['quantity'] + $quantity;
            
            // Update stock
            $stmt = $this->db->prepare("
                UPDATE ammunition_stock 
                SET quantity = ?, 
                    last_restocked_date = ?,
                    last_restocked_quantity = ?
                WHERE id = ?
            ");
            $stmt->execute([$newQuantity, date('Y-m-d'), $quantity, $id]);
            
            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO ammunition_transactions (
                    ammunition_stock_id, transaction_type, quantity, performed_by, remarks
                ) VALUES (?, 'Restock', ?, ?, ?)
            ");
            $stmt->execute([$id, $quantity, auth_id(), $_POST['remarks'] ?? null]);
            
            $this->db->commit();
            
            logger("Ammunition restocked: ID {$id}, Added {$quantity} rounds", 'info');
            
            $this->setFlash('success', 'Ammunition restocked successfully');
            $this->redirect('/ammunition');
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error restocking ammunition: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to restock ammunition');
            $this->redirect('/ammunition');
        }
    }
}
