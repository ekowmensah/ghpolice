<?php

namespace App\Models;

class PersonAlert extends BaseModel
{
    protected string $table = 'person_alerts';
    
    public function getByPersonId(int $personId): array
    {
        $sql = "
            SELECT 
                pa.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as issued_by_name
            FROM person_alerts pa
            LEFT JOIN users u ON pa.issued_by = u.id
            WHERE pa.person_id = ?
            ORDER BY pa.alert_priority DESC, pa.issued_date DESC
        ";
        
        return $this->query($sql, [$personId]);
    }
    
    public function getActive(int $personId): array
    {
        $sql = "
            SELECT * FROM person_alerts
            WHERE person_id = ?
            AND is_active = 1
            AND (expiry_date IS NULL OR expiry_date >= CURDATE())
            ORDER BY alert_priority DESC, issued_date DESC
        ";
        
        return $this->query($sql, [$personId]);
    }
    
    public function getByPriority(string $priority): array
    {
        $sql = "
            SELECT 
                pa.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as person_name,
                p.ghana_card_number,
                p.contact
            FROM person_alerts pa
            JOIN persons p ON pa.person_id = p.id
            WHERE pa.alert_priority = ?
            AND pa.is_active = 1
            ORDER BY pa.issued_date DESC
        ";
        
        return $this->query($sql, [$priority]);
    }
    
    public function deactivateAlert(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }
}
