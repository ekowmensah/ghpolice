<?php

namespace App\Middleware;

use App\Config\Database;
use PDO;

class AuditMiddleware
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function handle(): bool
    {
        // Log the request
        $this->logRequest();
        return true;
    }
    
    private function logRequest(): void
    {
        if (!isset($_SESSION['user'])) {
            return;
        }
        
        $userId = $_SESSION['user']['id'] ?? null;
        $action = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $action, $ipAddress, $userAgent]);
        } catch (\Exception $e) {
            error_log("Audit logging failed: " . $e->getMessage());
        }
    }
}
