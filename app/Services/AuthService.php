<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private User $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    public function login(string $username, string $password): array
    {
        $user = $this->userModel->findByUsername($username);
        
        if (!$user) {
            logger("Login attempt failed: User not found - {$username}");
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Check if account is locked
        if ($user['account_locked_until'] && strtotime($user['account_locked_until']) > time()) {
            logger("Login attempt failed: Account locked - {$username}");
            return [
                'success' => false,
                'message' => 'Account is locked. Please try again later or contact administrator.'
            ];
        }
        
        // Check if account is suspended
        if ($user['status'] !== 'Active') {
            logger("Login attempt failed: Account inactive - {$username}");
            return [
                'success' => false,
                'message' => 'Account is not active. Please contact administrator.'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->userModel->incrementFailedAttempts($user['id']);
            
            // Lock account after 5 failed attempts
            if ($user['failed_login_attempts'] >= 4) {
                $this->userModel->lockAccount($user['id']);
                logger("Account locked due to failed login attempts: {$username}");
                return [
                    'success' => false,
                    'message' => 'Account has been locked due to multiple failed login attempts. Please contact administrator.'
                ];
            }
            
            logger("Login attempt failed: Invalid password - {$username}");
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Reset failed attempts on successful login
        $this->userModel->resetFailedAttempts($user['id']);
        $this->userModel->updateLastLogin($user['id']);
        
        // Log successful login
        logger("User logged in successfully: {$username}");
        
        // Prepare user session data
        unset($user['password_hash']);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ];
    }
    
    public function requestPasswordReset(string $email): bool
    {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database (you'll need to create password_resets table)
        // For now, just log it
        logger("Password reset requested for: {$email}, Token: {$token}");
        
        // TODO: Send email with reset link
        
        return true;
    }
}
