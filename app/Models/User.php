<?php

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';
    
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.role_name, r.access_level,
                   r.can_manage_cases, r.can_manage_officers, r.can_manage_evidence,
                   r.can_manage_firearms, r.can_view_intelligence, r.can_approve_operations,
                   r.can_manage_users, r.can_view_reports, r.can_export_data, r.is_system_admin
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = ? AND u.status = 'Active'
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function updateLastLogin(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function incrementFailedAttempts(int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_login_attempts = failed_login_attempts + 1
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }
    
    public function resetFailedAttempts(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = 0 WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function lockAccount(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET account_locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
