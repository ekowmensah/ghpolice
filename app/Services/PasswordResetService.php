<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class PasswordResetService
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Generate password reset token
     */
    public function generateResetToken(string $email): ?array
    {
        // Check if user exists
        $stmt = $this->db->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return null;
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user['id'], $token, $expires]);
        
        logger("Password reset token generated for user: {$user['email']}");
        
        return [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'token' => $token,
            'expires_at' => $expires
        ];
    }
    
    /**
     * Verify reset token
     */
    public function verifyToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT pr.*, u.email
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? 
            AND pr.expires_at > NOW()
            AND pr.used_at IS NULL
        ");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Reset password
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetData = $this->verifyToken($token);
        
        if (!$resetData) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $resetData['user_id']]);
            
            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_resets SET used_at = NOW() WHERE token = ?");
            $stmt->execute([$token]);
            
            $this->db->commit();
            
            logger("Password reset successful for user ID: {$resetData['user_id']}");
            
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Password reset failed: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Clean expired tokens
     */
    public function cleanExpiredTokens(): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM password_resets
            WHERE expires_at < NOW() OR used_at IS NOT NULL
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
