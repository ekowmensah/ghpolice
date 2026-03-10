<?php

namespace App\Middleware;

class RoleMiddleware
{
    private array $allowedRoles;
    
    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }
    
    public function handle(): bool
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('/login'));
            exit;
        }
        
        $userRole = $_SESSION['user']['role'] ?? null;
        
        // If no specific roles required, allow all authenticated users
        if (empty($this->allowedRoles)) {
            return true;
        }
        
        // Check if user's role is in allowed roles
        if (!in_array($userRole, $this->allowedRoles)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Access denied. Insufficient permissions.'
            ];
            header('Location: ' . url('/dashboard'));
            exit;
        }
        
        return true;
    }
}
