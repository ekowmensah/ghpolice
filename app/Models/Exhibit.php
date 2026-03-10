<?php

namespace App\Models;

class Exhibit extends BaseModel
{
    protected string $table = 'exhibits';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                e.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as seized_by_name,
                pr.rank_name,
                o.service_number
            FROM exhibits e
            JOIN cases c ON e.case_id = c.id
            JOIN officers o ON e.seized_by = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE e.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                e.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as seized_by_name
            FROM exhibits e
            JOIN officers o ON e.seized_by = o.id
            WHERE e.case_id = ?
            ORDER BY e.seized_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getByStatus(string $status): array
    {
        $sql = "
            SELECT 
                e.*,
                c.case_number,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as seized_by_name
            FROM exhibits e
            JOIN cases c ON e.case_id = c.id
            JOIN officers o ON e.seized_by = o.id
            WHERE e.exhibit_status = ?
            ORDER BY e.seized_date DESC
        ";
        
        return $this->query($sql, [$status]);
    }
    
    public function updateStatus(int $id, string $status, ?string $remarks = null): bool
    {
        $data = ['exhibit_status' => $status];
        if ($remarks) {
            $data['remarks'] = $remarks;
        }
        return $this->update($id, $data);
    }
    
    public function recordMovement(int $exhibitId, array $movementData): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->update($exhibitId, [
                'current_location' => $movementData['moved_to']
            ]);
            
            $sql = "
                INSERT INTO exhibit_movements 
                (exhibit_id, moved_from, moved_to, moved_by, received_by, movement_date, purpose, condition_notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $this->execute($sql, [
                $exhibitId,
                $movementData['moved_from'],
                $movementData['moved_to'],
                $movementData['moved_by'],
                $movementData['received_by'] ?? null,
                $movementData['movement_date'] ?? date('Y-m-d H:i:s'),
                $movementData['purpose'] ?? null,
                $movementData['condition_notes'] ?? null
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getMovementHistory(int $exhibitId): array
    {
        $sql = "
            SELECT 
                em.*,
                CONCAT_WS(' ', o1.first_name, o1.middle_name, o1.last_name) as moved_by_name,
                CONCAT_WS(' ', o2.first_name, o2.middle_name, o2.last_name) as received_by_name
            FROM exhibit_movements em
            JOIN officers o1 ON em.moved_by = o1.id
            LEFT JOIN officers o2 ON em.received_by = o2.id
            WHERE em.exhibit_id = ?
            ORDER BY em.movement_date DESC
        ";
        
        return $this->query($sql, [$exhibitId]);
    }
    
    // ==================== RELATIONSHIP METHODS ====================
    
    /**
     * Get current location of exhibit
     */
    public function getCurrentLocation(int $exhibit_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT em.*, 
                   CONCAT_WS(' ', o1.first_name, o1.middle_name, o1.last_name) as moved_by_name,
                   CONCAT_WS(' ', o2.first_name, o2.middle_name, o2.last_name) as received_by_name
            FROM exhibit_movements em
            LEFT JOIN officers o1 ON em.moved_by = o1.id
            LEFT JOIN officers o2 ON em.received_by = o2.id
            WHERE em.exhibit_id = ?
            ORDER BY em.movement_date DESC
            LIMIT 1
        ");
        $stmt->execute([$exhibit_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get case associated with this exhibit
     */
    public function getCase(int $exhibit_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
            FROM exhibits ex
            INNER JOIN cases c ON ex.case_id = c.id
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN users u ON c.created_by = u.id
            WHERE ex.id = ?
        ");
        $stmt->execute([$exhibit_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get seized by officer details
     */
    public function getSeizedByOfficer(int $exhibit_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, pr.rank_name, s.station_name
            FROM exhibits ex
            INNER JOIN officers o ON ex.seized_by = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE ex.id = ?
        ");
        $stmt->execute([$exhibit_id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Verify movement history integrity
     */
    public function verifyMovementHistory(int $exhibit_id): array
    {
        $movements = $this->getMovementHistory($exhibit_id);
        $exhibit = $this->find($exhibit_id);
        
        $verification = [
            'is_valid' => true,
            'total_movements' => count($movements),
            'current_location' => $exhibit['current_location'] ?? null,
            'issues' => [],
            'movements' => $movements
        ];
        
        // Check if current location matches last movement
        if (!empty($movements) && $exhibit) {
            $lastMovement = $movements[0];
            if ($exhibit['current_location'] != $lastMovement['moved_to']) {
                $verification['is_valid'] = false;
                $verification['issues'][] = [
                    'type' => 'location_mismatch',
                    'message' => 'Current location does not match last recorded movement',
                    'expected' => $lastMovement['moved_to'],
                    'actual' => $exhibit['current_location']
                ];
            }
        }
        
        // Check for missing information in movements
        foreach ($movements as $index => $movement) {
            if (!$movement['moved_by']) {
                $verification['issues'][] = [
                    'type' => 'missing_moved_by',
                    'message' => 'Officer who moved exhibit not recorded',
                    'movement_index' => $index
                ];
            }
            
            if (!$movement['purpose']) {
                $verification['issues'][] = [
                    'type' => 'missing_purpose',
                    'message' => 'Movement purpose not documented',
                    'movement_index' => $index
                ];
            }
        }
        
        return $verification;
    }
    
    /**
     * Get full exhibit details with all relationships
     */
    public function getFullDetails(int $exhibit_id): ?array
    {
        $exhibit = $this->find($exhibit_id);
        if (!$exhibit) {
            return null;
        }
        
        // Add relationships
        $exhibit['case'] = $this->getCase($exhibit_id);
        $exhibit['seized_by_officer'] = $this->getSeizedByOfficer($exhibit_id);
        $exhibit['movement_history'] = $this->getMovementHistory($exhibit_id);
        $exhibit['current_location_details'] = $this->getCurrentLocation($exhibit_id);
        $exhibit['movement_verification'] = $this->verifyMovementHistory($exhibit_id);
        
        return $exhibit;
    }
}
