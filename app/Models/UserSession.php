<?php

namespace App\Models;

use PDO;

/**
 * UserSession Model
 * 
 * Handles user session tracking
 */
class UserSession extends BaseModel
{
    protected $table = 'user_sessions';

    /**
     * Create session
     */
    public function createSession(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, session_token, ip_address, user_agent, login_time, last_activity)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->execute($sql, [
            $data['user_id'],
            $data['session_token'],
            $data['ip_address'],
            $data['user_agent'],
            $data['login_time'] ?? date('Y-m-d H:i:s'),
            $data['last_activity'] ?? date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get active sessions for user
     */
    public function getActiveSessions(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND logout_time IS NULL
                ORDER BY last_activity DESC";
        
        return $this->query($sql, [$userId]);
    }

    /**
     * Update last activity
     */
    public function updateActivity(string $sessionToken): bool
    {
        $sql = "UPDATE {$this->table} 
                SET last_activity = ? 
                WHERE session_token = ?";
        
        return $this->execute($sql, [date('Y-m-d H:i:s'), $sessionToken]) > 0;
    }

    /**
     * End session
     */
    public function endSession(string $sessionToken): bool
    {
        $sql = "UPDATE {$this->table} 
                SET logout_time = ? 
                WHERE session_token = ?";
        
        return $this->execute($sql, [date('Y-m-d H:i:s'), $sessionToken]) > 0;
    }

    /**
     * Clean expired sessions
     */
    public function cleanExpired(int $timeoutMinutes = 30): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? MINUTE) 
                AND logout_time IS NULL";
        
        return $this->execute($sql, [$timeoutMinutes]);
    }
}
