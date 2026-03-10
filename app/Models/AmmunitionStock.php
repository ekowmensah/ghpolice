<?php

namespace App\Models;

class AmmunitionStock extends BaseModel
{
    protected string $table = 'ammunition_stock';
    
    public function getByStation(int $stationId): array
    {
        $sql = "
            SELECT * FROM ammunition_stock
            WHERE station_id = ?
            ORDER BY ammunition_type, caliber
        ";
        
        return $this->query($sql, [$stationId]);
    }
    
    public function getLowStock(int $stationId): array
    {
        $sql = "
            SELECT * FROM ammunition_stock
            WHERE station_id = ?
            AND quantity <= minimum_threshold
            ORDER BY quantity ASC
        ";
        
        return $this->query($sql, [$stationId]);
    }
    
    public function updateQuantity(int $id, int $quantity, bool $isRestock = false): bool
    {
        $data = ['quantity' => $quantity];
        
        if ($isRestock) {
            $data['last_restocked_date'] = date('Y-m-d');
            $data['last_restocked_quantity'] = $quantity;
        }
        
        return $this->update($id, $data);
    }
    
    public function adjustQuantity(int $id, int $adjustment): bool
    {
        $sql = "UPDATE ammunition_stock SET quantity = quantity + ? WHERE id = ?";
        return $this->execute($sql, [$adjustment, $id]);
    }
}
