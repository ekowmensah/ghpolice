<?php

namespace App\Services;

use App\Models\CaseAssignment;
use App\Config\Database;
use PDO;

class NotificationService
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Create notification
     */
    public function create(int $userId, string $type, string $title, string $message, ?array $data = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, notification_type, title, message, data)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $type,
            $title,
            $message,
            $data ? json_encode($data) : null
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, bool $unreadOnly = false): array
    {
        $sql = "
            SELECT * FROM notifications
            WHERE user_id = ?
        ";
        
        if ($unreadOnly) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = TRUE, read_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = TRUE, read_at = NOW()
            WHERE user_id = ? AND is_read = FALSE
        ");
        
        return $stmt->execute([$userId]);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ? AND is_read = FALSE
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Notify case assignment
     */
    public function notifyCaseAssignment(int $officerId, int $caseId, string $caseNumber, string $role): int
    {
        return $this->create(
            $officerId,
            'case_assignment',
            'New Case Assignment',
            "You have been assigned to case {$caseNumber} as {$role}",
            ['case_id' => $caseId, 'case_number' => $caseNumber, 'role' => $role]
        );
    }
    
    /**
     * Notify task assignment
     */
    public function notifyTaskAssignment(int $officerId, int $taskId, string $taskTitle): int
    {
        return $this->create(
            $officerId,
            'task_assignment',
            'New Task Assigned',
            "You have been assigned a new task: {$taskTitle}",
            ['task_id' => $taskId]
        );
    }
    
    /**
     * Notify case status change
     */
    public function notifyCaseStatusChange(int $userId, int $caseId, string $caseNumber, string $newStatus): int
    {
        return $this->create(
            $userId,
            'case_status',
            'Case Status Updated',
            "Case {$caseNumber} status changed to: {$newStatus}",
            ['case_id' => $caseId, 'status' => $newStatus]
        );
    }
    
    /**
     * Notify deadline approaching
     */
    public function notifyDeadlineApproaching(int $userId, string $itemType, int $itemId, string $itemTitle, string $deadline): int
    {
        return $this->create(
            $userId,
            'deadline',
            'Deadline Approaching',
            "{$itemType} '{$itemTitle}' is due on {$deadline}",
            ['item_type' => $itemType, 'item_id' => $itemId, 'deadline' => $deadline]
        );
    }
    
    /**
     * Broadcast notification to multiple users
     */
    public function broadcast(array $userIds, string $type, string $title, string $message, ?array $data = null): int
    {
        $count = 0;
        foreach ($userIds as $userId) {
            $this->create($userId, $type, $title, $message, $data);
            $count++;
        }
        return $count;
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOldNotifications(int $daysOld = 30): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM notifications
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND is_read = TRUE
        ");
        
        $stmt->execute([$daysOld]);
        return $stmt->rowCount();
    }
    
    // ==================== PHASE 2 INTEGRATION METHODS ====================
    
    /**
     * Notify all officers assigned to a case
     */
    public function notifyCaseOfficers(int $case_id, string $title, string $message, ?array $data = null): int
    {
        $assignment = new CaseAssignment();
        $officers = $assignment->getByCaseId($case_id);
        
        $count = 0;
        foreach ($officers as $officer) {
            if ($officer['status'] === 'Active') {
                $this->create($officer['assigned_to'], 'case_update', $title, $message, $data);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Notify case reassignment
     */
    public function notifyCaseReassignment(int $old_officer_id, int $new_officer_id, int $case_id, string $case_number): void
    {
        // Notify old officer
        $this->create(
            $old_officer_id,
            'case_reassignment',
            'Case Reassigned',
            "Case {$case_number} has been reassigned to another officer",
            ['case_id' => $case_id, 'case_number' => $case_number]
        );
        
        // Notify new officer
        $this->create(
            $new_officer_id,
            'case_assignment',
            'Case Assigned to You',
            "Case {$case_number} has been reassigned to you",
            ['case_id' => $case_id, 'case_number' => $case_number]
        );
    }
}
