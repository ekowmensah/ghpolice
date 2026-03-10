<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Firearm;
use App\Models\Vehicle;
use App\Config\Database;
use PDO;

class AssetService
{
    private Asset $assetModel;
    private Firearm $firearmModel;
    private Vehicle $vehicleModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->assetModel = new Asset();
        $this->firearmModel = new Firearm();
        $this->vehicleModel = new Vehicle();
        $this->db = Database::getConnection();
    }
    
    // ==================== ASSET MANAGEMENT ====================
    
    /**
     * Register asset with initial location
     */
    public function registerAsset(array $assetData): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create asset
            $assetId = $this->assetModel->create($assetData);
            
            // Record initial movement/location
            if (!empty($assetData['current_location'])) {
                $this->recordAssetMovement($assetId, null, $assetData['current_location'], 'Initial Registration', auth_id());
            }
            
            $this->db->commit();
            
            logger("Asset registered: {$assetData['asset_name']} (ID: {$assetId})");
            
            return $assetId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to register asset: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Transfer asset to new location
     */
    public function transferAsset(int $asset_id, string $from_location, string $to_location, string $reason, int $moved_by): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Update asset location
            $this->assetModel->update($asset_id, ['current_location' => $to_location]);
            
            // Record movement
            $this->recordAssetMovement($asset_id, $from_location, $to_location, $reason, $moved_by);
            
            $this->db->commit();
            
            logger("Asset {$asset_id} transferred from {$from_location} to {$to_location}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to transfer asset: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Record asset movement
     */
    private function recordAssetMovement(int $asset_id, ?string $from_location, string $to_location, string $reason, int $moved_by): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO asset_movements (asset_id, moved_from, moved_to, movement_reason, moved_by, movement_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$asset_id, $from_location, $to_location, $reason, $moved_by]);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get asset with complete movement history
     */
    public function getAssetWithHistory(int $asset_id): ?array
    {
        $asset = $this->assetModel->find($asset_id);
        
        if (!$asset) {
            return null;
        }
        
        // Get movement history
        $stmt = $this->db->prepare("
            SELECT 
                am.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as moved_by_name
            FROM asset_movements am
            LEFT JOIN users u ON am.moved_by = u.id
            WHERE am.asset_id = ?
            ORDER BY am.movement_date DESC
        ");
        $stmt->execute([$asset_id]);
        $asset['movement_history'] = $stmt->fetchAll();
        
        return $asset;
    }
    
    /**
     * Get assets by location
     */
    public function getAssetsByLocation(string $location): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                CASE 
                    WHEN a.condition_status = 'Excellent' THEN 1
                    WHEN a.condition_status = 'Good' THEN 2
                    WHEN a.condition_status = 'Fair' THEN 3
                    WHEN a.condition_status = 'Poor' THEN 4
                    ELSE 5
                END as condition_order
            FROM assets a
            WHERE a.current_location = ?
            ORDER BY condition_order, a.asset_name
        ");
        $stmt->execute([$location]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get asset statistics by location
     */
    public function getAssetStatisticsByLocation(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                current_location,
                COUNT(*) as total_assets,
                SUM(CASE WHEN condition_status = 'Excellent' THEN 1 ELSE 0 END) as excellent,
                SUM(CASE WHEN condition_status = 'Good' THEN 1 ELSE 0 END) as good,
                SUM(CASE WHEN condition_status = 'Fair' THEN 1 ELSE 0 END) as fair,
                SUM(CASE WHEN condition_status = 'Poor' THEN 1 ELSE 0 END) as poor,
                SUM(CASE WHEN condition_status = 'Damaged' THEN 1 ELSE 0 END) as damaged
            FROM assets
            GROUP BY current_location
            ORDER BY total_assets DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // ==================== FIREARM MANAGEMENT ====================
    
    /**
     * Assign firearm to officer
     */
    public function assignFirearmToOfficer(int $firearm_id, int $officer_id, string $assignment_date, string $purpose, int $assigned_by): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Check if firearm is already assigned
            $stmt = $this->db->prepare("
                SELECT id FROM firearm_assignments 
                WHERE firearm_id = ? AND return_date IS NULL
            ");
            $stmt->execute([$firearm_id]);
            
            if ($stmt->fetch()) {
                throw new \Exception("Firearm is already assigned to another officer");
            }
            
            // Create assignment
            $stmt = $this->db->prepare("
                INSERT INTO firearm_assignments (firearm_id, officer_id, assignment_date, purpose, assigned_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$firearm_id, $officer_id, $assignment_date, $purpose, $assigned_by]);
            
            // Update firearm status
            $this->firearmModel->update($firearm_id, ['status' => 'Assigned']);
            
            $this->db->commit();
            
            logger("Firearm {$firearm_id} assigned to officer {$officer_id}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to assign firearm: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Return firearm from officer
     */
    public function returnFirearmFromOfficer(int $assignment_id, string $return_date, string $condition_on_return, string $notes = null): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Get assignment details
            $stmt = $this->db->prepare("SELECT firearm_id FROM firearm_assignments WHERE id = ?");
            $stmt->execute([$assignment_id]);
            $assignment = $stmt->fetch();
            
            if (!$assignment) {
                throw new \Exception("Assignment not found");
            }
            
            // Update assignment
            $stmt = $this->db->prepare("
                UPDATE firearm_assignments
                SET return_date = ?, condition_on_return = ?, return_notes = ?
                WHERE id = ?
            ");
            $stmt->execute([$return_date, $condition_on_return, $notes, $assignment_id]);
            
            // Update firearm status
            $this->firearmModel->update($assignment['firearm_id'], ['status' => 'Available']);
            
            $this->db->commit();
            
            logger("Firearm returned from assignment {$assignment_id}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Failed to return firearm: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get firearm assignment history
     */
    public function getFirearmAssignmentHistory(int $firearm_id): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                fa.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                o.service_number,
                pr.rank_name,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as assigned_by_name
            FROM firearm_assignments fa
            INNER JOIN officers o ON fa.officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN users u ON fa.assigned_by = u.id
            WHERE fa.firearm_id = ?
            ORDER BY fa.assignment_date DESC
        ");
        $stmt->execute([$firearm_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get officer's current firearm assignment
     */
    public function getOfficerCurrentFirearm(int $officer_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                f.*,
                fa.assignment_date,
                fa.purpose,
                DATEDIFF(NOW(), fa.assignment_date) as days_assigned
            FROM firearms f
            INNER JOIN firearm_assignments fa ON f.id = fa.firearm_id
            WHERE fa.officer_id = ? AND fa.return_date IS NULL
            LIMIT 1
        ");
        $stmt->execute([$officer_id]);
        return $stmt->fetch() ?: null;
    }
    
    // ==================== VEHICLE MANAGEMENT ====================
    
    /**
     * Assign vehicle to station/officer
     */
    public function assignVehicle(int $vehicle_id, int $station_id = null, int $officer_id = null, string $assignment_type): bool
    {
        try {
            $updateData = ['assignment_status' => 'Assigned'];
            
            if ($station_id) {
                $updateData['assigned_station_id'] = $station_id;
            }
            
            if ($officer_id) {
                $updateData['assigned_officer_id'] = $officer_id;
            }
            
            $this->vehicleModel->update($vehicle_id, $updateData);
            
            logger("Vehicle {$vehicle_id} assigned ({$assignment_type})");
            
            return true;
        } catch (\Exception $e) {
            logger("Failed to assign vehicle: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get vehicle maintenance history
     */
    public function getVehicleMaintenanceHistory(int $vehicle_id): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM vehicle_maintenance
            WHERE vehicle_id = ?
            ORDER BY maintenance_date DESC
        ");
        $stmt->execute([$vehicle_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get vehicles due for maintenance
     */
    public function getVehiclesDueForMaintenance(int $days_threshold = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                v.*,
                s.station_name,
                DATEDIFF(NOW(), v.last_service_date) as days_since_service
            FROM vehicles v
            LEFT JOIN stations s ON v.assigned_station_id = s.id
            WHERE DATEDIFF(NOW(), v.last_service_date) >= ?
               OR v.last_service_date IS NULL
            ORDER BY days_since_service DESC
        ");
        $stmt->execute([$days_threshold]);
        return $stmt->fetchAll();
    }
    
    // ==================== ASSET STATISTICS ====================
    
    /**
     * Get comprehensive asset statistics
     */
    public function getAssetStatistics(): array
    {
        // General assets
        $assetStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_assets,
                SUM(CASE WHEN condition_status = 'Excellent' THEN 1 ELSE 0 END) as excellent_condition,
                SUM(CASE WHEN condition_status = 'Good' THEN 1 ELSE 0 END) as good_condition,
                SUM(CASE WHEN condition_status = 'Fair' THEN 1 ELSE 0 END) as fair_condition,
                SUM(CASE WHEN condition_status IN ('Poor', 'Damaged') THEN 1 ELSE 0 END) as needs_attention,
                COUNT(DISTINCT current_location) as locations
            FROM assets
        ");
        $assetStmt->execute();
        $assetStats = $assetStmt->fetch();
        
        // Firearms
        $firearmStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_firearms,
                SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) as assigned,
                SUM(CASE WHEN status = 'Under Maintenance' THEN 1 ELSE 0 END) as maintenance,
                SUM(CASE WHEN status = 'Decommissioned' THEN 1 ELSE 0 END) as decommissioned
            FROM firearms
        ");
        $firearmStmt->execute();
        $firearmStats = $firearmStmt->fetch();
        
        // Vehicles
        $vehicleStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_vehicles,
                SUM(CASE WHEN operational_status = 'Operational' THEN 1 ELSE 0 END) as operational,
                SUM(CASE WHEN operational_status = 'Under Repair' THEN 1 ELSE 0 END) as under_repair,
                SUM(CASE WHEN operational_status = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service,
                SUM(CASE WHEN assignment_status = 'Assigned' THEN 1 ELSE 0 END) as assigned_vehicles
            FROM vehicles
        ");
        $vehicleStmt->execute();
        $vehicleStats = $vehicleStmt->fetch();
        
        return [
            'assets' => $assetStats,
            'firearms' => $firearmStats,
            'vehicles' => $vehicleStats,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
