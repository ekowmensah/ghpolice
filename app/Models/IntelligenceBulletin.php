<?php

namespace App\Models;

class IntelligenceBulletin extends BaseModel
{
    protected string $table = 'intelligence_bulletins';
    
    public function getActive(): array
    {
        $sql = "
            SELECT 
                ib.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
            FROM intelligence_bulletins ib
            LEFT JOIN users u ON ib.issued_by = u.id
            WHERE ib.status = 'Active'
            AND (ib.valid_until IS NULL OR ib.valid_until >= CURDATE())
            ORDER BY ib.priority DESC, ib.valid_from DESC
        ";
        
        return $this->query($sql);
    }
    
    public function getByType(string $type): array
    {
        $sql = "
            SELECT 
                ib.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
            FROM intelligence_bulletins ib
            LEFT JOIN users u ON ib.issued_by = u.id
            WHERE ib.bulletin_type = ?
            ORDER BY ib.valid_from DESC
        ";
        
        return $this->query($sql, [$type]);
    }
    
    public function getByPriority(string $priority): array
    {
        $sql = "
            SELECT 
                ib.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
            FROM intelligence_bulletins ib
            LEFT JOIN users u ON ib.issued_by = u.id
            WHERE ib.priority = ? AND ib.status = 'Active'
            ORDER BY ib.valid_from DESC
        ";
        
        return $this->query($sql, [$priority]);
    }
    
    public function expireBulletin(int $id): bool
    {
        return $this->update($id, [
            'status' => 'Expired',
            'valid_until' => date('Y-m-d')
        ]);
    }
    
    public function cancelBulletin(int $id, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'Cancelled',
            'cancellation_reason' => $reason
        ]);
    }
}
