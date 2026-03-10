<?php

namespace App\Models;

class Asset extends BaseModel
{
    protected string $table = 'assets';
    
    public function getByType(string $type): array
    {
        $sql = "
            SELECT 
                a.*,
                c.case_number
            FROM assets a
            LEFT JOIN cases c ON a.case_id = c.id
            WHERE a.asset_type = ?
            ORDER BY a.asset_name
        ";
        
        return $this->query($sql, [$type]);
    }
    
    public function getByLocation(string $location): array
    {
        $sql = "
            SELECT * FROM assets
            WHERE current_location = ?
            ORDER BY asset_name
        ";
        
        return $this->query($sql, [$location]);
    }
    
    public function getMovementHistory(int $assetId): array
    {
        $sql = "
            SELECT 
                am.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as moved_by_name
            FROM asset_movements am
            JOIN officers o ON am.moved_by = o.id
            WHERE am.asset_id = ?
            ORDER BY am.movement_date DESC
        ";
        
        return $this->query($sql, [$assetId]);
    }
    
    public function recordMovement(int $assetId, array $movementData): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->update($assetId, [
                'current_location' => $movementData['moved_to']
            ]);
            
            $sql = "
                INSERT INTO asset_movements (asset_id, moved_from, moved_to, moved_by, purpose)
                VALUES (?, ?, ?, ?, ?)
            ";
            
            $this->execute($sql, [
                $assetId,
                $movementData['moved_from'],
                $movementData['moved_to'],
                $movementData['moved_by'],
                $movementData['purpose'] ?? null
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
