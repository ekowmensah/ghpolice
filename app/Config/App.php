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

    private static function normalizePath(?string $path): string
    {
        $path = str_replace('\\', '/', trim((string)$path));
        if ($path === '' || $path === '/' || $path === '.') {
            return '';
        }

        return '/' . trim($path, '/');
    }

    public static function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName !== '') {
            $scriptDir = dirname(str_replace('\\', '/', $scriptName));
            $normalizedScriptDir = self::normalizePath($scriptDir);
            if ($normalizedScriptDir !== '') {
                return $normalizedScriptDir;
            }
        }

        $configuredUrl = (string) self::config('app.url', '');
        $configuredPath = parse_url($configuredUrl, PHP_URL_PATH);
        return self::normalizePath($configuredPath);
    }

    private static function baseOrigin(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $scheme = $isHttps ? 'https' : 'http';
            return $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        $configuredUrl = (string) self::config('app.url', '');
        $parts = parse_url($configuredUrl);
        if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
            $origin = $parts['scheme'] . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $origin .= ':' . $parts['port'];
            }
            return $origin;
        }

        return '';
    }
    
    public static function baseUrl(string $path = ''): string
    {
        if ($path !== '' && preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $basePath = self::basePath();
        $origin = self::baseOrigin();

        $base = $origin !== ''
            ? rtrim($origin . $basePath, '/')
            : ($basePath !== '' ? $basePath : '');

        if ($path === '') {
            return $base !== '' ? $base : '/';
        }

        if ($path === '/') {
            return ($base !== '' ? $base : '') . '/';
        }

        return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
    }
}
