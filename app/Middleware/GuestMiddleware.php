<?php

namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): bool
    {
        if (is_logged_in()) {
            redirect('/dashboard');
            return false;
        }
        
        return true;
    }
}
