<?php

namespace App\Controllers;

use App\Models\Notification;

/**
 * NotificationController
 * 
 * Handles user notifications
 */
class NotificationController extends BaseController
{
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
    }

    /**
     * Get user notifications
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getByUser($userId);

        $this->view('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $notifications
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $userId = $_SESSION['user_id'];
        $count = $this->notificationModel->getUnreadCount($userId);

        return $this->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $this->notificationModel->markAsRead($id);

            return $this->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to mark as read']);
        }
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $userId = $_SESSION['user_id'];
            $this->notificationModel->markAllAsRead($userId);

            return $this->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to mark all as read']);
        }
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        if (!verify_csrf()) {
            return $this->json(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $this->notificationModel->delete($id);

            return $this->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to delete notification']);
        }
    }

    /**
     * Get recent notifications (AJAX)
     */
    public function getRecent()
    {
        $userId = $_SESSION['user_id'];
        $limit = $_GET['limit'] ?? 10;
        
        $notifications = $this->notificationModel->getByUser($userId, $limit);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        return $this->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
}
