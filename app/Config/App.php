<?php

namespace App\Config;

class App
{
    public static function config(string $key, $default = null)
    {
        $config = [
            'app.name' => $_ENV['APP_NAME'] ?? 'GHPIMS',
            'app.env' => $_ENV['APP_ENV'] ?? 'production',
            'app.debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'app.url' => $_ENV['APP_URL'] ?? 'http://localhost',
            'app.timezone' => 'Africa/Accra',
            
            'session.lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 30),
            'session.name' => 'GHPIMS_SESSION',
            
            'csrf.token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token',
            
            'upload.max_size' => (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760),
            'upload.allowed_extensions' => explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,pdf,doc,docx'),
            
            'pagination.per_page' => (int)($_ENV['ITEMS_PER_PAGE'] ?? 25),
            
            'log.level' => $_ENV['LOG_LEVEL'] ?? 'error',
            'log.file' => $_ENV['LOG_FILE'] ?? 'storage/logs/app.log',
        ];
        
        return $config[$key] ?? $default;
    }
    
    public static function isDebug(): bool
    {
        return self::config('app.debug', false);
    }
    
    public static function baseUrl(string $path = ''): string
    {
        $url = rtrim(self::config('app.url'), '/');
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}
