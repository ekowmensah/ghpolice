<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!is_logged_in()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
            return false;
        }
        
        // Check session timeout
        $timeout = config('session.lifetime') * 60;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            session_destroy();
            redirect('/login?timeout=1');
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
}
