<?php

namespace App\Models;

class Notification extends BaseModel
{
    protected string $table = 'notifications';
    
    public function getByUserId(int $userId, ?bool $unreadOnly = false): array
    {
        $sql = "
            SELECT 
                n.*,
                c.case_number
            FROM notifications n
            LEFT JOIN cases c ON n.case_id = c.id
            WHERE n.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT 50";
        
        return $this->query($sql, $params);
    }
    
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->query($sql, [$userId]);
        return (int)($result[0]['count'] ?? 0);
    }
    
    public function markAsRead(int $id): bool
    {
        return $this->update($id, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = ? WHERE user_id = ? AND is_read = 0";
        return $this->execute($sql, [date('Y-m-d H:i:s'), $userId]);
    }
    
    public function deleteOld(int $days = 30): bool
    {
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->execute($sql, [$days]);
    }
}
